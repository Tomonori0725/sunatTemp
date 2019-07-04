<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

#拡張をする対象エンティティの指定
/**
* @Eccube\EntityExtension("Eccube\Entity\Customer")
*/

trait CustomerTrait
{
    /**
     * @ORM\Column(name="department", type="string", nullable=true)
     * @Eccube\FormAppend(
     *  auto_render=true,
     *  options={
     *      "required": false,
     *      "label": "部署名その2"
     *  }
     * )
     */
    public $department;
}