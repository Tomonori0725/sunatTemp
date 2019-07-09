<?php

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Eccube\Entity\BaseInfo as BaseInfo;
use Eccube\Repository\BaseInfoRepository as BaseInfoRepository;

use Customize\Entity\Carriage;
use Customize\Repository\CarriageRepository;
use Customize\Form\Type\Admin\CarriageType;

class CarriageController extends AbstractController
{

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var CarriageRepository
     */
    protected $carriageRepository;

    /**
     * CarriageRepository constructor.
     *
     * @param CarriageRepository $carriageRepository
     */
    public function __construct(CarriageRepository $carriageRepository)
    {
        $this->carriageRepository = $carriageRepository;
    }

    /**
     * 
     * 代引き手数料の追加・編集
     * 
     * @Method({"GET", "POST"})
     * @Route("/%eccube_admin_route%/setting/shop/{id}/carriage", name="admin_setting_shop_payment_carriage")
     * @Route("/%eccube_admin_route%/setting/shop/carriage/new", name="admin_setting_shop_payment_carriage_new")
     * @Template("@admin/Setting/Shop/carriage.twig")
     */
    public function indexAction(Request $request, $id)
    {
        $carriage = new \Customize\Entity\Carriage();
        $builder = $this->formFactory
            ->createBuilder(CarriageType::class, $carriage);

        $form = $builder->getForm();

        $mode = $request->get('mode');
        if ($mode != 'edit_inline') {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $carriage->setPaymentId($id);
                $this->entityManager->persist($carriage);
                $this->entityManager->flush();

                $this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute('admin_setting_shop_payment_carriage', ['id' => $id]);
            }
        }

        //手数料の一覧を取得
        $carriageRepository = $this->getDoctrine()->getRepository(Carriage::class);
        $carriages = $carriageRepository->getList($id);

        // 手数料編集
        $forms = [];
        $errors = [];
        foreach ($carriages as $carriage) {
            $builder = $this->formFactory->createBuilder(CarriageType::class, $carriage);
            $editCarriageForm = $builder->getForm();

            // error number
            $error = 0;
            if ($mode == 'edit_inline'
                && $request->getMethod() === 'POST'
                && (string) $carriage->getId() === $request->get('carriage_id')
            ) {
                $editCarriageForm->handleRequest($request);
                if ($editCarriageForm->isValid()) {
                    $editCarriageData = $editCarriageForm->getData();

                    $this->entityManager->persist($editCarriageData);
                    $this->entityManager->flush();

                    $this->addSuccess('admin.common.save_complete', 'admin');

                    return $this->redirectToRoute('admin_setting_shop_payment_carriage', ['id' => $id]);
                }
                $error = count($editCarriageForm->getErrors(true));
            }

            $forms[$carriage->getId()] = $editCarriageForm->createView();
            $errors[$carriage->getId()] = $error;
        }

        return [
            'carriages' => $carriages,
            'payment_id' => $id,
            'form' => $form->createView(),
            'forms' => $forms,
            'errors' => $errors,
        ];
    }

    /**
     * 代引き手数料の削除
     *
     * @Route("/%eccube_admin_route%/setting/shop/{id}/carriage/delete", requirements={"id" = "\d+"}, name="admin_setting_shop_payment_carriage_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Carriage $carriage)
    {
        $id = $carriage->getPaymentId();
        $this->isTokenValid();

        $this->carriageRepository->delete($carriage);
        $this->addSuccess('admin.common.delete_complete', 'admin');

        return $this->redirectToRoute('admin_setting_shop_payment_carriage', ['id' => $id]);
    }



}
