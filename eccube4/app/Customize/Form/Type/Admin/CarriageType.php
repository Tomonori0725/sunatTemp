<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\Carriage;
use Eccube\Form\Type\Master\RoundingTypeType;
use Customize\Repository\CarriageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Eccube\Form\Type\PriceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CarriageType
 */
class CarriageType extends AbstractType
{
    protected $carriageRepository;

    public function __construct(CarriageRepository $carriageRepository)
    {
        $this->carriageRepository = $carriageRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('charge', PriceType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0]),
                    new Assert\Length([
                        'max' => 12,
                        'maxMessage' => '12桁以下で入力してください。',
                    ]),
                ],
            ])
            ->add('rule_min', PriceType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0]),
                    new Assert\Length([
                        'max' => 12,
                        'maxMessage' => '12桁以下で入力してください。',
                    ]),
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Carriage::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'tax_rule';
    }
}
