<?php

namespace Plugin\SelectGiftBox\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Shipping")
 */
trait ShippingTrait
{
  /**
   * @var string
   *
   * @ORM\Column(name="gift_box_id", type="integer", nullable=true)
   */
  public $gift_box_id;

  /**
   * @return string
   */
  public function getGiftBoxId()
  {
      return $this->gift_box_id;
  }

  /**
   * @param string $gift_box_id
   *
   * @return $this
   */
  public function setGiftBoxId($gift_box_id)
  {
      $this->gift_box_id = $gift_box_id;

      return $this;
  }
  
}
