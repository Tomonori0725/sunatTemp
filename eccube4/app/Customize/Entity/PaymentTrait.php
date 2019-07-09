<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager as entityManager;
use Eccube\Annotation\EntityExtension;
use Customize\Entity\Carriage;

#拡張をする対象エンティティの指定
/**
* @EntityExtension("Eccube\Entity\Payment")
*/
trait PaymentTrait
{

    public function isCarriage(){
        $carriage = new Carriage();
        $repository = $entityManager->getRepository(Carriage::class);
        $item = $repository->findBy(['id' => $this->getId()]);

    }

}