<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
  /**
   * @var string
   *
   * @ORM\Column(name="delivery_include_fee", type="integer", nullable=true)
   */
  public $delivery_include_fee;

  /**
   * @return string
   */
  public function getDeliveryIncludeFee()
  {
      return $this->delivery_include_fee;
  }

  /**
   * @param string $delivery_include_fee
   *
   * @return $this
   */
  public function setDeliveryIncludeFee($delivery_include_fee)
  {
      $this->delivery_include_fee = $delivery_include_fee;

      return $this;
  }
  
}
