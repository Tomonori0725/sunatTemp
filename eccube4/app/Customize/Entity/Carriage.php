<?php

namespace Customize\Entity;

use Eccube\Entity\AbstractEntity;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

    /**
     * Carriage
     *
     * @ORM\Table(name="dtb_carriage")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Customize\Repository\CarriageRepository")
     * @UniqueEntity(
     *     fields={"rule_min", "payment_id"},
     *     message="このお買い上げ金額は、既に設定されています。"
     * )
     */
    class Carriage extends AbstractEntity
    {

        /**
         * @var integer
         */
        const DEFAULT_CARRIAGE_ID = 1;
        
        /**
         * @return string
         */
        public function __toString()
        {
            return (string) $this->getMethod();
        }


        /**
         * @var int
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;


        /**
         * @var string|null
         *
         * @ORM\Column(name="charge", type="decimal", precision=12, scale=2, nullable=true, options={"unsigned":true,"default":0})
         */
        private $charge = 0;


        /**
         * @var string|null
         *
         * @ORM\Column(name="rule_min", type="decimal", precision=12, scale=2, nullable=true, options={"unsigned":true})
         */
        private $rule_min;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz")
         */
        private $create_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz")
         */
        private $update_date;

        /**
         * @var \Customize\Entity\Payment
         *
         * @ORM\Column(name="payment_id", type="integer")
         */
        private $payment_id;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * is default
     *
     * @return bool
     */
    public function isDefaultCarriage()
    {
        return self::DEFAULT_CARRIAGE_ID === $this->getId();
    }

    /**
     * Set charge.
     *
     * @param string|null $charge
     *
     * @return Carriage
     */
    public function setCharge($charge = null)
    {
        $this->charge = $charge;

        return $this;
    }

    /**
     * Get charge.
     *
     * @return string|null
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * Set ruleMin.
     *
     * @param string|null $ruleMin
     *
     * @return Carriage
     */
    public function setRuleMin($ruleMin = null)
    {
        $this->rule_min = $ruleMin;

        return $this;
    }

    /**
     * Get ruleMin.
     *
     * @return string|null
     */
    public function getRuleMin()
    {
        return $this->rule_min;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return Carriage
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set updateDate.
     *
     * @param \DateTime $updateDate
     *
     * @return Carriage
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get updateDate.
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * Set paymentId.
     *
     * @param int $paymentId
     *
     * @return Carriage
     */
    public function setPaymentId($paymentId)
    {
        $this->payment_id = $paymentId;

        return $this;
    }

    /**
     * Get paymentId.
     *
     * @return int
     */
    public function getPaymentId()
    {
        return $this->payment_id;
    }
}
