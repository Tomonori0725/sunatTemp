<?php

namespace Plugin\RegisterWhileShopping\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\RegisterWhileShopping\Form\Type\Admin\ConfigType;
use Plugin\RegisterWhileShopping\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/register_while_shopping/config", name="register_while_shopping_admin_config")
     * @Template("@RegisterWhileShopping/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);
            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('register_while_shopping_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
