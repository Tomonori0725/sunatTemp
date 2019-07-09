<?php

namespace Plugin\MultiChargePlugin\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiChargePlugin\Entity\Carriage;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * CarriageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CarriageRepository extends AbstractRepository
{

    /**
     * CarriageRepository constructor.
     */
    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, Carriage::class);
    }

    /**
     * 一覧を取得する　getList
     *
     * @return array|null
     */
    public function getList($id, $orderBy = 'ASC')
    {
        $query = $this->createQueryBuilder('c')
            ->orderBy('c.rule_min', $orderBy)
            ->where('c.payment_id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        $carriages = $query->getResult();

        return $carriages;
    }

    /**
     * 手数料が指定されているかどうか　isCarriage
     *
     * @return boolean|null
     */
    public function isCarriage($id)
    {
        $query = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.payment_id = :id')
            ->setParameter('id', $id)
            ->getQuery();
        $count = $query->getResult();

        return $count[0][1]!==0;
    }

    /**
     * 金額にあった手数料を返す
     *
     * @return integer|null
     */
    public function getCharge($id, $total)
    {
        $charges = $this->getList($id);
        foreach($charges as $data){
            if($total > $data['rule_min']){
                $charge = $data['charge'];
            }else{
                return $charge;
            }
        }
        return $charge;
    }

    /**
     * 代引手数料の削除.
     *
     * @param  int|\Plugin\MultiChargePlugin\Entity\Carriage $carriage 税規約
     *
     * @throws NoResultException
     */
    public function delete($carriage)
    {
        if (!$carriage instanceof \Plugin\MultiChargePlugin\Entity\Carriage) {
            $carriage = $this->find($carriage);
        }
        if (!$carriage) {
            throw new NoResultException();
        }
        $em = $this->getEntityManager();
        $em->remove($carriage);
        $em->flush();
    }


}
