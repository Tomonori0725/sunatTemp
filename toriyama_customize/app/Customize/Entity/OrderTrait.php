<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Customize\Service\PurchaseFlow\ItemCollection;

/**
  * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_fee_discount_total", type="decimal", precision=12, scale=2, options={"default":0})
     */
    private $delivery_fee_discount_total = 0;

    /**
     * Get deliveryFeeDiscountTotal.
     *
     * @return string
     */
    public function getDeliveryFeeDiscountTotal()
    {
        return $this->delivery_fee_discount_total;
    }

    /**
     * Set deliveryFeeDiscountTotal.
     *
     * @param string $deliveryFeeDiscountTotal
     *
     * @return Order
     */
    public function setDeliveryFeeDiscountTotal($deliveryFeeDiscountTotal)
    {
        $this->delivery_fee_discount_total = $deliveryFeeDiscountTotal;

        return $this;
    }

    /**
     * Get price IncTax
     *
     * @return string
     */
    public function getPriceIncTax()
    {
        // 税表示区分が税込の場合は, priceに税込金額が入っている.
        if ($this->TaxDisplayType && $this->TaxDisplayType->getId() == TaxDisplayType::INCLUDED) {
            return $this->price;
        }

        return $this->price + $this->tax;
    }

    /**
     * Sorted to getOrderItems()
     *
     * @return ItemCollection
     */
    public function getCustomItems()
    {
        return (new ItemCollection($this->getOrderItems()))->sort();
    }
}
