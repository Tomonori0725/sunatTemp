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

namespace Plugin\ChangeDeliveryDate\Form\Extension;

use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractTypeExtension;
use Plugin\ChangeDeliveryDate\Repository\DeliveryDateRepository;
use Eccube\Form\Type\Shopping\ShippingType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ShippingTypeExtension.
 */
class ShippingTypeExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var DeliveryDateRepository
     */
    protected $DeliveryDateRepository;

    /**
     * ShippingType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        EccubeConfig $eccubeConfig,
        DeliveryDateRepository $DeliveryDateRepository
    ){
        $this->eccubeConfig = $eccubeConfig;
        $this->DeliveryDateRepository = $DeliveryDateRepository;
    }


    /**
     * buildForm.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // お届け日のプルダウンを生成.
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $Shipping = $event->getData();
                if (is_null($Shipping) || !$Shipping->getId()) {
                    return;
                }

                // お届け日の設定.
                $minDate = 0;
                $deliveryDurationFlag = false;

                // 配送時に最大となる商品日数を取得.
                foreach ($Shipping->getOrderItems() as $detail) {
                    $ProductClass = $detail->getProductClass();
                    if (is_null($ProductClass)) {
                        continue;
                    }
                    $deliveryDuration = $ProductClass->getDeliveryDuration();
                    if (is_null($deliveryDuration)) {
                        continue;
                    }
                    if ($deliveryDuration->getDuration() < 0) {
                        // 配送日数がマイナスの場合はお取り寄せなのでスキップする.
                        $deliveryDurationFlag = false;
                        break;
                    }

                    if ($minDate < $deliveryDuration->getDuration()) {
                        $minDate = $deliveryDuration->getDuration();
                    }


                    // 最短お届け日の設定を取得.
                    $delivDate = $this->DeliveryDateRepository->getTodayDeliveryDate();
                    if ($delivDate) {
                        $minDate = $delivDate[0]->getDuration();
                    }
                    
                    // 配送日数が設定されている.
                    $deliveryDurationFlag = true;
                }

                // 配達最大日数期間を設定.
                $deliveryDurations = [];

                // 配送日数が設定されている.
                if ($deliveryDurationFlag) {

                    // お届け不可日を取得する.
                    $maxDate = $minDate + $this->eccubeConfig['eccube_deliv_date_end_max'];
                    $impossibleDate = $this->DeliveryDateRepository->getImpossibleDate($minDate, $maxDate);
                    $delivDateTotal = $maxDate - $minDate - count($impossibleDate);

                    while ($delivDateTotal <= $this->eccubeConfig['eccube_deliv_date_end_max']) {
                        // 最長日を不可日の日数だけ増やす.
                        $maxDate = $maxDate + count($impossibleDate);
                        // お届け不可日を取得.
                        $impossibleDate = $this->DeliveryDateRepository->getImpossibleDate($minDate, $maxDate);
                        // 不可日を抜いたお届け日の合計を取得.
                        $delivDateTotal = $maxDate - $minDate - count($impossibleDate);
                        // 表示数になったかをチェック.
                        if ($delivDateTotal <= $this->eccubeConfig['eccube_deliv_date_end_max']) {
                            break;
                        }
                    }
                    // 日付をキーにする.
                    $impossibleDate = array_flip($impossibleDate);

                    $period = new \DatePeriod(
                        new \DateTime($minDate.' day'),
                        new \DateInterval('P1D'),
                        new \DateTime($maxDate.' day')
                    );

                    // 曜日設定用.
                    $dateFormatter = \IntlDateFormatter::create(
                        'ja_JP@calendar=japanese',
                        \IntlDateFormatter::FULL,
                        \IntlDateFormatter::FULL,
                        'Asia/Tokyo',
                        \IntlDateFormatter::TRADITIONAL,
                        'E'
                    );

                    foreach ($period as $day) {
                        if (!array_key_exists($day->format('Y/m/d'), $impossibleDate)) {
                            $deliveryDurations[$day->format('Y/m/d')] = $day->format('Y/m/d').'('.$dateFormatter->format($day).')';
                        }
                    }

                }

                $form = $event->getForm();
                $form
                    ->add(
                        'shipping_delivery_date',
                        ChoiceType::class,
                        [
                            'choices' => array_flip($deliveryDurations),
                            'required' => false,
                            'placeholder' => 'common.select__unspecified',
                            'mapped' => false,
                            'data' => $Shipping->getShippingDeliveryDate() ? $Shipping->getShippingDeliveryDate()->format('Y/m/d') : null,
                        ]
                    );
            }
        );

    }

    public function getExtendedType()
    {
        return ShippingType::class;
    }

}
