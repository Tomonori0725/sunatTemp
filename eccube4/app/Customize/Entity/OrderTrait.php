<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

#拡張をする対象エンティティの指定
/**
* @Eccube\EntityExtension("Eccube\Entity\Order")
*/

trait OrderTrait
{
    /**
     * @ORM\Column(name="packaging", type="boolean", nullable=true)
     * @Eccube\FormAppend
     */
    public $packaging;
}