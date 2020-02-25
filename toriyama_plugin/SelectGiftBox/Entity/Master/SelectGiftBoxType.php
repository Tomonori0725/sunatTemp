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

namespace Eccube\Entity\Master;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeliveryIncludeFeeClass
 *
 * @ORM\Table(name="mtb_select_gift_box_type")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Eccube\Repository\Master\SelectGiftBoxTypeRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class SelectGiftBoxType extends \Eccube\Entity\Master\AbstractMasterEntity
{
    /**
     * 自宅用.
     *
     * @deprecated
     */
    const HOME = 1;

    /**
     * 贈答用(有償).
     *
     * @deprecated
     */
    const GIFT = 2;
    
}
