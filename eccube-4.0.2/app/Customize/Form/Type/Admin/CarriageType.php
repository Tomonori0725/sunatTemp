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
                ],
            ])
            ->add('rule_min', PriceType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0]),
                ]
            ]);

        #$builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
        #    /** @var TaxRule $TaxRule */
        #    $TaxRule = $event->getData();
        #    $qb = $this->taxRuleRepository->createQueryBuilder('t');
        #    $qb
        #        ->select('count(t.id)')
        #        ->where('t.apply_date = :apply_date')
        #        ->setParameter('apply_date', $TaxRule->getApplyDate());
        #    
        #    if ($TaxRule->getId()) {
        #        $qb
        #            ->andWhere('t.id <> :id')
        #            ->setParameter('id', $TaxRule->getId());
        #    }
        #    $count = $qb->getQuery()
        #        ->getSingleScalarResult();
        #    if ($count > 0) {
        #        $form = $event->getForm();
        #        $form['apply_date']->addError(new FormError(trans('taxrule.text.error.date_not_available')));
        #    }
        #});
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
