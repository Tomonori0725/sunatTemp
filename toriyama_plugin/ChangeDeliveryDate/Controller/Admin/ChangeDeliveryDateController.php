<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ChangeDeliveryDate\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\Constant;
use Eccube\Form\Type\Admin\SearchProductType;
use Plugin\ChangeDeliveryDate\Entity\DeliveryDate;
use Plugin\ChangeDeliveryDate\Repository\DeliveryDateRepository;
use Plugin\ChangeDeliveryDate\Service\DeliveryDateService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Controller\AbstractController;

/**
 * Class ChangeDeliveryDateController
 */
class ChangeDeliveryDateController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var DeliveryDateService
     */
    private $deliveryDateService;

    /**
     * @var DeliveryDateRepository
     */
    private $deliveryDateRepository;

    /**
     * CouponShoppingController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param DeliveryDateService $deliveryDateService
     * @param DeliveryDateRepository $deliveryDateRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        DeliveryDateService $deliveryDateService,
        DeliveryDateRepository $deliveryDateRepository
    ) {
        $this->entityManager = $entityManager;
        $this->deliveryDateService = $deliveryDateService;
        $this->deliveryDateRepository = $deliveryDateRepository;
    }



    /**
     * @param Request $request
     *
     * @return array
     * @Route("/%eccube_admin_route%/plugin/delivery_date", name="delivery_date_list")
     * @Template("@ChangeDeliveryDate/admin/index.twig")
     */
    public function index(Request $request)
    {
        $isError = false;

        // POSTから値を取得.
        $delivConfig = array(
            'shortest' =>  0,
            'impossible' => 1
        );

        // アクセス日以降の登録データを取得する.
        foreach ($delivConfig as $name => $type) {
            $delivDate[$name]['date'] = $this->deliveryDateRepository->getDeliveryDate($type);
        }

        // 送信されたら...
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($delivConfig as $name => $type) {
                // 日付を文字列から1行ごとの配列に変換する.
                $delivDate[$name]['date'] = $this->deliveryDateService->StringToArray($request->get($name));
                // バリデーション.
                $delivDate[$name]['error'] = $this->deliveryDateService->validateData($delivDate[$name]['date'], $type);
                if ($delivDate[$name]['error']) {
                    $isError = true;
                }
            }

            // エラーがなければDBに登録.
            if (!$isError) {
                foreach ($delivConfig as $name => $type) {
                    // フォーマットに変換する.
                    $delivDate[$name]['date'] = $this->deliveryDateService->formatData($delivDate[$name]['date'], $type);

                    // DBに追加する.
                    foreach ($delivDate[$name]['date'] as $list) {
                        $delivery_date = new DeliveryDate();
                        if ($list['duration']) {
                            $delivery_date->setDuration($list['duration']);
                        } else {
                            $delivery_date->setDuration(null);
                        }
                        $delivery_date->setType($type);
                        $delivery_date->setDate($list['date']);
                        $this->entityManager->persist($delivery_date);
                    }
                    // DBを削除する.
                    $this->deliveryDateRepository->deleteDelivDate($type);
                }
                // DBに反映.
                $this->entityManager->flush();

                foreach ($delivConfig as $name => $type) {
                    // 改めて日付を取得.
                    $delivDate[$name]['date'] = $this->deliveryDateRepository->getDeliveryDate($type);
                }
            } else {
                foreach ($delivConfig as $name => $type) {
                    $delivDate[$name]['date'] = $request->get($name);
                }
            }
        }

        // var_dump($delivDate);

        return [
            'delivDate' => $delivDate
        ];

    }




}
