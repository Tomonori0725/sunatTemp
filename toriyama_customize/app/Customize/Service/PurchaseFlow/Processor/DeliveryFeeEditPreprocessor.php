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

namespace Customize\Service\PurchaseFlow\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\DeliveryFee;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\DeliveryFeeRepository;
use Eccube\Repository\TaxRuleRepository;
use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Service\PurchaseFlow\PurchaseContext;

use Eccube\Annotation\ShoppingFlow;
use Eccube\Service\PurchaseFlow\Processor\DeliveryFeePreprocessor;
use Eccube\Entity\Product;
use Eccube\Repository\ProductRepository;

/**
 * @ShoppingFlow
 */
class DeliveryFeeEditPreprocessor extends DeliveryFeePreprocessor
{
    /** @var BaseInfo */
    protected $BaseInfo;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var TaxRuleRepository
     */
    protected $taxRuleRepository;

    /**
     * @var DeliveryFeeRepository
     */
    protected $deliveryFeeRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * DeliveryFeePreprocessor constructor.
     *
     * @param BaseInfoRepository $baseInfoRepository
     * @param EntityManagerInterface $entityManager
     * @param TaxRuleRepository $taxRuleRepository
     * @param DeliveryFeeRepository $deliveryFeeRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        BaseInfoRepository $baseInfoRepository,
        EntityManagerInterface $entityManager,
        TaxRuleRepository $taxRuleRepository,
        DeliveryFeeRepository $deliveryFeeRepository,
        ProductRepository $productRepository
    ) {
        $this->BaseInfo = $baseInfoRepository->get();
        $this->entityManager = $entityManager;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->deliveryFeeRepository = $deliveryFeeRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param ItemHolderInterface $itemHolder
     * @param PurchaseContext $context
     *
     * @throws \Doctrine\ORM\NoResultException
     */
    public function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        $this->removeDeliveryFeeItem($itemHolder);
        $this->saveDeliveryFeeItem($itemHolder);
    }

    private function removeDeliveryFeeItem(ItemHolderInterface $itemHolder)
    {
        foreach ($itemHolder->getShippings() as $Shipping) {
            foreach ($Shipping->getOrderItems() as $item) {
                if ($item->getProcessorName() == DeliveryFeePreprocessor::class) {
                    $Shipping->removeOrderItem($item);
                    $itemHolder->removeOrderItem($item);
                    $this->entityManager->remove($item);
                }
            }
        }
    }

    /**
     * @param ItemHolderInterface $itemHolder
     *
     * @throws \Doctrine\ORM\NoResultException
     */
    private function saveDeliveryFeeItem(ItemHolderInterface $itemHolder)
    {
        $DeliveryFeeType = $this->entityManager
            ->find(OrderItemType::class, OrderItemType::DELIVERY_FEE);
        $DiscountType = $this->entityManager
            ->find(OrderItemType::class, 7);
        $TaxInclude = $this->entityManager
            ->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);
        $Taxation = $this->entityManager
            ->find(TaxType::class, TaxType::TAXATION);

        /** @var Order $Order */
        $Order = $itemHolder;

        /* @var Shipping $Shipping */
        foreach ($Order->getShippings() as $Shipping) {
            // 送料の計算
            $deliveryFeeProduct = 0;
            if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                /** @var OrderItem $item */
                foreach ($Shipping->getOrderItems() as $item) {
                    if (!$item->isProduct()) {
                        continue;
                    }
                    $deliveryFeeProduct += $item->getProductClass()->getDeliveryFee() * $item->getQuantity();
                }
            }

            /** @var DeliveryFee $DeliveryFee */
            $DeliveryFee = $this->deliveryFeeRepository->findOneBy([
                'Delivery' => $Shipping->getDelivery(),
                'Pref' => $Shipping->getPref(),
            ]);

            // カスタム送料.
            $productsInfo = array();
            // order_itemを取得.
            $items = $itemHolder->getProductOrderItems();

            foreach ($items as $item) {
                if ($Shipping->getId() === $item->getShipping()->getId()) {
                    // productを取得.
                    $deliveryFeeClass = $this->productRepository->findOneBy([
                        'id' => $item->getProduct()->getId()
                    ]);
                    // productから送料区分を取得.
                    $productsInfo['deliveryFee'][] = array(
                        'class' => $deliveryFeeClass->getDeliveryIncludeFee(),
                        'quantity' => $item->getQuantity()
                    );
                }
            }

            $productsInfo['pref'] = $Shipping->getPref();
            $productsInfo['deliveryFeeProduct'] = $DeliveryFee->getFee() + $deliveryFeeProduct;

            $deliveryFeeList = $this->getCustomDeliveryFee($productsInfo);
    
            // 送料 登録
            $OrderItem = new OrderItem();
            $OrderItem->setProductName($DeliveryFeeType->getName())
                ->setPrice($deliveryFeeList['sum'])
                ->setQuantity(1)
                ->setOrderItemType($DeliveryFeeType)
                ->setShipping($Shipping)
                ->setOrder($itemHolder)
                ->setTaxDisplayType($TaxInclude)
                ->setTaxType($Taxation)
                ->setProcessorName(DeliveryFeePreprocessor::class);

            $itemHolder->addItem($OrderItem);
            $Shipping->addOrderItem($OrderItem);
            
            // 送料値引き 登録
            if (array_key_exists('discount', $deliveryFeeList)) {
                $DiscountItem = new OrderItem();
                $DiscountItem->setProductName($DiscountType->getName())
                    ->setPrice($deliveryFeeList['discount'])
                    ->setQuantity(1)
                    ->setOrderItemType($DiscountType)
                    ->setShipping($Shipping)
                    ->setOrder($itemHolder)
                    ->setTaxDisplayType($TaxInclude)
                    ->setTaxType($Taxation)
                    ->setProcessorName(DeliveryFeePreprocessor::class);
                        
                $itemHolder->addItem($DiscountItem);
                $Shipping->addOrderItem($DiscountItem);
            }
        }
    }

    /**
     * @param $productsInfo
     *    
     *
     * @throws \Doctrine\ORM\NoResultException
     */
    private function getCustomDeliveryFee($productsInfo)
    {    
        // 北海道・沖縄は別途料金
        $prefFlag = false;
        if ($productsInfo['pref']->getId() === 1 
        || $productsInfo['pref']->getId() === 47) {
            $prefFlag = true;
        }

        $sendFeeType = array(
            'free'         => false,
            'free_300'     => false,
            'free_500'     => false,
            'discount_500' => 0
        );
        $deliveryFee['sum'] = 0;

        foreach ($productsInfo['deliveryFee'] as $class) {
            switch ($class['class']) {
                case 1:
                    // 送料別.
                    $deliveryFee['sum'] = $deliveryFee['sum'] + $productsInfo['deliveryFeeProduct'];
                    break;
                case 2:
                    // 送料込み（全国送料無料）.
                    $sendFeeType['free'] = true;
                    break;
                case 3:
                    // 送料込み（北海道・沖縄は300円）.
                    $sendFeeType['free_300'] = true;
                    break;
                case 4:
                    // 送料込み（北海道・沖縄は500円）.
                    $sendFeeType['free_500'] = true;
                    break;
                case 5:
                    // 送料込み（北海道・沖縄は500円。複数購入された場合は、2個目以降1個当たり500円を返金。）.
                    $sendFeeType['discount_500'] = $sendFeeType['discount_500'] + $class['quantity'];
                    break;
                default:
                    
            }
        }
        
        // 送料込み(北海道・沖縄は追加料金)の時に追加.
        if (!$sendFeeType['free'] && $prefFlag) {
            if ($sendFeeType['free_300']) {
                $deliveryFee['sum'] = $deliveryFee['sum'] + 300;
            }
            elseif ($sendFeeType['free_500']) {
                $deliveryFee['sum'] = $deliveryFee['sum'] + 500;
            }
            elseif ($sendFeeType['discount_500'] > 0) {
                $deliveryFee['sum'] = $deliveryFee['sum'] + 500;
            }
        }

        // 料金値引きがある場合.
        if ($sendFeeType['free'] || $sendFeeType['free_300']) {
            $sendFeeType['discount_500'] = $sendFeeType['discount_500'] + 1;
        }
        if ($sendFeeType['discount_500'] > 1) {
            $deliveryFee['discount'] = 500 * ($sendFeeType['discount_500'] - 1) * -1;
        }
        
        return $deliveryFee;
    }
}
