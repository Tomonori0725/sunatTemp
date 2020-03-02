<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Form\Extension;

use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractTypeExtension;
use Eccube\Entity\Category;
use Eccube\Entity\Master\DeliveryIncludeFeeClass;
use Customize\Form\Type\Master\DeliveryFeeType;
use Eccube\Form\Type\Admin\ProductType;
use Eccube\Form\Type\Master\ProductStatusType;
use Eccube\Form\Validator\TwigLint;
use Eccube\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProductType.
 */
class ProductTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // 送料区分
            ->add('deliveryFee', EntityType::class, [
                'class'        => 'Eccube\Entity\Master\DeliveryIncludeFeeClass',
                'choice_value' => 'id',
                'choice_label' => 'name',
                'placeholder'  => 'common.select',
                'mapped'       => false
            ]);
    }

    public function getExtendedType()
    {
        return ProductType::class;
    }
}
