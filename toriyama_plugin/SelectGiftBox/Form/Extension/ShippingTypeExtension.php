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

namespace Plugin\SelectGiftBox\Form\Extension;

use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractTypeExtension;
use Eccube\Entity\Master\SelectGiftBoxType;
use Eccube\Repository\Master\SelectGiftBoxTypeRepository;
use Eccube\Form\Type\Shopping\ShippingType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

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
     * @var SelectGiftBoxType
     */
    protected $SelectGiftBoxType;

    /**
     * @var SelectGiftBoxTypeRepository
     */
    protected $SelectGiftBoxTypeRepository;

    /**
     * ShippingType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     * @param SelectGiftBoxType $SelectGiftBoxType
     * @param SelectGiftBoxTypeRepository $SelectGiftBoxTypeRepository
     */
    public function __construct(
        EccubeConfig $eccubeConfig,
        EccubeConfig $SelectGiftBoxType,
        SelectGiftBoxTypeRepository $SelectGiftBoxTypeRepository
    ){
        $this->eccubeConfig = $eccubeConfig;
        $this->SelectGiftBoxType = $SelectGiftBoxType;
        $this->SelectGiftBoxTypeRepository = $SelectGiftBoxTypeRepository;
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
                $giftBoxChoice = array();

                // マスターのデータを取得.
                $giftBoxTypes = $this->SelectGiftBoxTypeRepository->findAll();
                foreach($giftBoxTypes as $giftBoxType) {
                    $giftBoxChoice[$giftBoxType->getName()] = $giftBoxType->getId();
                }

                // フォームを作成.
                $form = $event->getForm();
                $form->add('gift_box', ChoiceType::class, [
                    'choices'  => $giftBoxChoice,
                    'expanded' => true,
                    'multiple' => false,
                    'mapped'   => false,
                    'data'     => 1,
                ]);

            }
        );
    }

    public function getExtendedType()
    {
        return ShippingType::class;
    }

}
