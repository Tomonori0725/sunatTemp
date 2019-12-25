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
     * StringをDateTimeにする.
     *
     * @param string $string_date
     *
     * @return datetime
     */
    public function arrayToString($delivDate, $config) {
        $delivDateStrings = array();
        foreach ($config as $name => $type) {
            $delivDateStrings[$name]['date'] = array_map(function($date){
                $line = $date->getDate()->modify('-9 hours')->format('Y/m/d');
                if ($date->getDuration()) {
                    $line .= ',' . $date->getDuration();
                }
                $line .= "\n";
                return $line;
            }, $delivDate[$name]);

            $delivDateStrings[$name]['date'] = implode('', $delivDateStrings[$name]['date']);
        }
        
        return $delivDateStrings;
    }

    /**
     * 日付のバリデーション.
     *
     * @param array $array_data
     *
     * @return $error
     */
    public function validateData($delivDate, $configs) {
        $error = array();

        foreach ($configs as $name => $type) {
            // 入力フォーマットチェック.
            if ($this->checkFormat($delivDate[$name], $type)) {
                $error[$name]['format'] = $this->checkFormat($delivDate[$name], $type);
                return $error;
            }

            // 正しい日付かをチェック.
            if ($this->checkCorrect($delivDate[$name])) {
                $error[$name]['correct'] = $this->checkCorrect($delivDate[$name]);
            }

            // 重複チェック.
            if ($this->checkSingleDuplicate($delivDate[$name])) {
                $error[$name]['duplicate'] = $this->checkSingleDuplicate($delivDate[$name]);
            }
        }

        if ($error) {
            return $error;
        }

        return false;
    }


    /**
     * フォーマットをチェックする.
     *
     * @param array $delivDate
     *
     * @return array
     */
    private function checkFormat($delivDates, $type) {
        $error = array();

        // フォーマット.
        foreach ($delivDates as $data) {
            if (0 === $type) {
                if (1 !== preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}(,\d+)$/', $data)) {
                    $error[] = $this->translated->trans(
                        'delivery_date.admin.validate.format',
                        ['%data%' => $data]
                    );
                }
            }
            elseif (1 === $type) {
                if (1 !== preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $data)) {
                    $error[] = $this->translated->trans(
                        'delivery_date.admin.validate.format',
                        ['%data%' => $data]
                    );
                }
            }
        }

        return $error;
    }

    /**
     * 日付が正しいかをチェック.
     *
     * @param array $delivDate
     *
     * @return array
     */
    private function checkCorrect($delivDates) {
        $error = array();

        foreach ($delivDates as $data) {
            $currentDate = $data;
            $data = explode(',', $data);
            $data = explode('/', $data[0]);
            if (!checkdate($data[1], $data[2], $data[0])) {
                $error[] = $this->translated->trans(
                    'delivery_date.admin.validate.correct',
                    ['%data%' => $currentDate]
                );
            }
        }

        return $error;
    }

    /**
     * 重複をチェック.
     *
     * @param array $delivDate
     *
     * @return array
     */
    private function checkSingleDuplicate($delivDates) {
        $error = array();

        $delivDatesList = array_map(function($data){
            return explode(',', $data);
        }, $delivDates);

        for ($current=0; $current<count($delivDatesList); $current++) {
            for ($next=$current+1; $next<count($delivDatesList); $next++) {
                if ($delivDatesList[$current][0] === $delivDatesList[$next][0]) {
                    $error[] = $this->translated->trans(
                        'delivery_date.admin.validate.single.duplicate',
                        ['%data%' => $delivDates[$current], '%data2%' => $delivDates[$next]]
                    );
                }
            }
        }

        return $error;
    }


    /**
     * 日付を文字列から配列にする.
     *
     * @param array $delivDate
     *
     * @return array
     */
    public function stringToArray($delivDate) {
        $delivDateList = array();

        // 各データをテキストから配列にする.
        $delivDateList = explode("\n", $delivDate);
        // 各行にtrimをかける.
        $delivDateList = array_map('trim', $delivDateList);
        // 空の配列を削除する.
        $delivDateList = array_filter($delivDateList, 'strlen');
        // 日付と発送日目安が重複している.
        $delivDateList = array_unique($delivDateList, SORT_STRING);
        // キーの振り直し.
        $delivDateList = array_values($delivDateList);

        return $delivDateList;
    }
    
    /**
     * 日付をオブジェクトから配列にする.
     *
     * @param array $delivDate
     *
     * @return array
     */
    public function objectToArray($delivDate, $delivConfig) {
        $delivList = array();

        foreach ($delivConfig as $name => $type) {
            $delivList[$name] = array_map(function($dateList){
                $list['date'] = $dateList->getDate()->format('Y/m/d');
                if ($dateList->getDuration()) {
                    $list['duration'] =  $dateList->getDuration();
                } else {
                    $list['duration'] = null;
                }
                return $list;
            }, $delivDate[$name]);
        }

        return $delivList;
    }

}
