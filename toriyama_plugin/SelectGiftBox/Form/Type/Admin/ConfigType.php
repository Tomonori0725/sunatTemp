<?php

namespace Plugin\SelectGiftBox\Form\Type\Admin;

use Plugin\SelectGiftBox\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Eccube\Form\Type\PriceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['max' => 255]),
            ],
            ])
            ->add('price', PriceType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 0]),
                ]
            ])
            ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
