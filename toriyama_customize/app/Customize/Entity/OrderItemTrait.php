<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\OrderItem")
 */
trait OrderItemTrait
{
    /**
     * 送料明細かどうか.
     *
     * @return boolean 送料明細の場合 true
     */
    public function isDeliveryFeeDiscount()
    {
        // return $this->getOrderItemTypeId() === OrderItemType::DELIVERY_FEE_DISCOUNT;
        return $this->getOrderItemTypeId() === 7;
    }
}
