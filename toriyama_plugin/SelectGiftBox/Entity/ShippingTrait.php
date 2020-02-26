<?php

namespace Plugin\SelectGiftBox\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\Master\SelectGiftBoxType;

/**
  * @EntityExtension("Eccube\Entity\Shipping")
 */
trait ShippingTrait
{
  /**
   * @var string
   *
   * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\SelectGiftBoxType")
   * @ORM\JoinColumns({
   *   @ORM\JoinColumn(name="gift_box_id", referencedColumnName="id")
   * })
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
   * @param SelectGiftBoxType $gift_box_id
   *
   * @return $this
   */
  public function setGiftBoxId(SelectGiftBoxType $gift_box_id)
  {
      $this->gift_box_id = $gift_box_id;

      return $this;
  }

}
