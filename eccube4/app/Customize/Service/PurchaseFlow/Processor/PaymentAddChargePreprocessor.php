<?php

namespace Customize\Service\PurchaseFlow\Processor;

use Eccube\Annotation\ShoppingFlow;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Payment;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Eccube\Repository\Master\TaxDisplayTypeRepository;
use Eccube\Repository\Master\TaxTypeRepository;
use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Service\PurchaseFlow\PurchaseContext;

use Customize\Entity\Carriage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Customize\Repository\CarriageRepository;

/**
 * @ShoppingFlow
 */
class PaymentAddChargePreprocessor implements ItemHolderPreprocessor
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
     * PaymentAddChargePreprocessor constructor.
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
            if ($item->getProcessorName() == PaymentAddChargePreprocessor::class) {
                //CarriageRepositoryを取得
                $carriage_repository = $this->entitymanager->getRepository('Customize\Entity\Carriage');

                //代引き手数料が個別に入っているか...
                if ($carriage_repository->isCarriage($itemHolder->getPayment()->getId())) {
                    //カスタマイズの手数料を使う
                    $id = $itemHolder->getPayment()->getId();
                    $total = $itemHolder->getSubtotal() + $itemHolder->getDeliveryFeeTotal() + $itemHolder->getDiscount();
                    var_dump($carriage_repository->getCharge($id, $total));
                    echo 'id:' . $id . '<br>total:' . $total;
                    $item->setPrice($carriage_repository->getCharge($id, $total));
                } else {
                    //デフォルトの手数料を使う
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
            ->setProcessorName(PaymentAddChargePreprocessor::class);
        $itemHolder->addItem($item);
    }
}
