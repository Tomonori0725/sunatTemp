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

namespace Plugin\ChangeDeliveryDate\Service;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\Constant;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Plugin\ChangeDeliveryDate\Entity\DeliveryDate;
use Plugin\ChangeDeliveryDate\Repository\DeliveryDateRepository;
use Symfony\Component\Translation\Translator;

/**
 * Class DeliveryDateService.
 */
class DeliveryDateService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DeliveryDateRepository
     */
    private $deliveryDateRepository;

    /**
     * @var Translator
     */
    private $translated;

    /**
     * DeliveryDateService constructor.
     *
     * @param ContainerInterface $container
     * @param EntityManagerInterface $entityManager
     * @param DeliveryDateRepository $deliveryDateRepository
     * @param Translator $translated
     */
    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        DeliveryDateRepository $deliveryDateRepository,
        Translator $translated
    ) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->deliveryDateRepository = $deliveryDateRepository;
        $this->translated = $translated;
    }

    /**
     * 日付フォーマットにする.
     *
     * @param string $data
     * @param integer $type
     *
     * @return string
     */
    public function formatData($dataList, $type = 0)
    {
        // 注文日とお届け最短日を分ける.
        $deliveryDate = array();

        if (is_array($dataList)) {
            foreach ($dataList as $list) {
                if ($type === 0) {
                    $list = explode(',', $list);
                    list($date, $duration) = $list;
                } else {
                    // お届け最短日がなければ...
                    $date = $list;
                    $duration = '';
                }

                $deliveryDate[] = array(
                    'date'     => $this->stringToDatetime(trim($date)),
                    'duration' => trim($duration)
                );
            }
        }

        return $deliveryDate;
    }

    /**
     * StringをDateTimeにする.
     *
     * @param string $string_date
     *
     * @return datetime
     */
    private function stringToDatetime($string_date) {
        $date = explode('/', $string_date);
        $date_time = new \DateTime();
        return $date_time->setDate($date[0], $date[1], $date[2])->setTime(23, 59, 59)->modify('+9 hours');
    }

    /**
     * 日付のバリデーション.
     *
     * @param array $array_data
     *
     * @return $error
     */
    public function validateData($array_data, $type = 0) {
        $error = array();

        foreach ($array_data as $data) {
            // フォーマット
            if ($type === 0) {
                if (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}(,\d+)$/', $data) !== 1) {
                    $error['format'][] = $this->translated->trans(
                        'delivery_date.admin.validate.format',
                        ['%data%' => $data]
                    );
                }
            }
            elseif ($type === 1) {
                if (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $data) !== 1) {
                    $error['format'][] = $this->translated->trans(
                        'delivery_date.admin.validate.format',
                        ['%data%' => $data]
                    );
                }
            }

            


        }

        if ($error) {
            return $error;
        }

        return false;
    }


    /**
     * 日付を文字列から配列にする.
     *
     * @param array $delivDate
     *
     * @return array
     */
    public function StringToArray($delivDate) {
        $delivDateList = array();

        // 各データをテキストから配列にする.
        $delivDateList = explode("\n", $delivDate);
        // 各行にtrimをかける.
        $delivDateList = array_map('trim', $delivDateList);
        // 空の配列を削除する.
        $delivDateList = array_filter($delivDateList, 'strlen');
        // キーの振り直し.
        $delivDateList = array_values($delivDateList);

        return $delivDateList;
    }
    


}
