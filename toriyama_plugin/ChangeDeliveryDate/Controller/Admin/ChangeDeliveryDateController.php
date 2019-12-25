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

        // 送信されたら...
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 文字列を配列に変換.
            foreach ($delivConfig as $name => $type) {
                $delivInputDate[$name] = $this->deliveryDateService->stringToArray($request->get($name));
            }

            // バリデーション.
            $error = $this->deliveryDateService->validateData($delivInputDate, $delivConfig);
            // エラーがあれば.
            if ($error) {
                $isError = true;
            }
            
            // エラーがなければDBに登録.
            if (!$isError) {
                foreach ($delivConfig as $name => $type) {
                    // フォーマットに変換する.
                    $delivInputDate[$name] = $this->deliveryDateService->formatData($delivInputDate[$name], $type);
        
                    // DBに追加する.
                    foreach ($delivInputDate[$name] as $list) {
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

                // 改めて日付を取得.
                $delivDate = $this->deliveryDateRepository->getDeliveryDate($delivConfig);
                // 表示用に整形.
                $delivStringsDate = $this->deliveryDateService->arrayToString($delivDate, $delivConfig);
            } else {
                // エラーあれば.
                foreach ($delivConfig as $name => $type) {
                    // 文字列を配列にする.
                    $delivStringsDate[$name]['date'] = $request->get($name);
                    if (array_key_exists($name, $error)) {
                        // エラー文を挿入.
                        $delivStringsDate[$name]['error'] = $error[$name];
                    }
                }
            }
        } else {
            // アクセス日以降の登録データを取得する.
            $delivDate = $this->deliveryDateRepository->getDeliveryDate($delivConfig);
            // 表示用に整形.
            $delivStringsDate = $this->deliveryDateService->arrayToString($delivDate, $delivConfig);
        }

        return ['delivDate' => $delivStringsDate];

    }




}
