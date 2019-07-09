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

namespace Eccube\Service\PurchaseFlow\Processor;

use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Entity\OrderItem;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Eccube\Repository\Master\TaxDisplayTypeRepository;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Order;
use Eccube\Entity\Payment;
use Eccube\Repository\Master\TaxTypeRepository;
use Eccube\Entity\Master\TaxType;

use Customize\Entity\Carriage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Customize\Repository\CarriageRepository;

class PaymentChargePreprocessor implements ItemHolderPreprocessor
{
    /**
     * @var OrderItemTypeRepository
     */
    protected $orderItemTypeRepository;

    /**
     * @var TaxDisplayTypeRepository
     */
    protected $taxDisplayTypeRepository;

    /**
     * @var TaxTypeRepository
     */
    protected $taxTypeRepository;

    /**
     * PaymentChargePreprocessor constructor.
     *
     * @param OrderItemTypeRepository $orderItemTypeRepository
     * @param TaxDisplayTypeRepository $taxDisplayTypeRepository
     * @param TaxTypeRepository $taxTypeRepository
     */
    public function __construct(
        OrderItemTypeRepository $orderItemTypeRepository,
        TaxDisplayTypeRepository $taxDisplayTypeRepository,
        TaxTypeRepository $taxTypeRepository,
        EntityManager $entitymanager
    ) {
        $this->orderItemTypeRepository = $orderItemTypeRepository;
        $this->taxDisplayTypeRepository = $taxDisplayTypeRepository;
        $this->taxTypeRepository = $taxTypeRepository;
        $this->entitymanager = $entitymanager;
    }

    /**
     * {@inheritdoc}
     *
     * @param ItemHolderInterface $itemHolder
     * @param PurchaseContext $context
     */
    public function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        if (!$itemHolder instanceof Order) {
            return;
        }
        if (!$itemHolder->getPayment() instanceof Payment || !$itemHolder->getPayment()->getId()) {
            return;
        }

        foreach ($itemHolder->getItems() as $item) {
            if ($item->getProcessorName() == PaymentChargePreprocessor::class) {

                $carriage_repository = $this->entitymanager->getRepository('Customize\Entity\Carriage');

                if ($carriage_repository->isCarriage($itemHolder->getPayment()->getId())) {
                    $id = $itemHolder->getPayment()->getId();
                    $total = $itemHolder->getSubtotal() + $itemHolder->getDeliveryFeeTotal() + $itemHolder->getDiscount();
                    $item->setPrice($carriage_repository->getCharge($id, $total));
                } else {
                    $item->setPrice($itemHolder->getPayment()->getCharge());
                }
                
                return;
            }
        }

        $this->addChargeItem($itemHolder);
    }

    /**
     * Add charge item to item holder
     *
     * @param ItemHolderInterface $itemHolder
     */
    protected function addChargeItem(ItemHolderInterface $itemHolder)
    {
        /** @var Order $itemHolder */
        $OrderItemType = $this->orderItemTypeRepository->find(OrderItemType::CHARGE);
        $TaxDisplayType = $this->taxDisplayTypeRepository->find(TaxDisplayType::INCLUDED);
        $Taxation = $this->taxTypeRepository->find(TaxType::TAXATION);
        $item = new OrderItem();
        $item->setProductName($OrderItemType->getName())
            ->setQuantity(1)
            ->setPrice($itemHolder->getPayment()->getCharge())
            ->setOrderItemType($OrderItemType)
            ->setOrder($itemHolder)
            ->setTaxDisplayType($TaxDisplayType)
            ->setTaxType($Taxation)
            ->setProcessorName(PaymentChargePreprocessor::class);
        $itemHolder->addItem($item);
    }
}
