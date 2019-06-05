<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use \Datetime;

/**
 * Account
 *
 * @ORM\Table(name="account")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AccountRepository")
 */
class Account
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *  @ORM\Column(name="name", type="string", unique=true)
     *  @Assert\NotBlank(message = "名前を入力してください。")
     *  @Assert\Length(
     *      max = 16,
     *      maxMessage = "16文字以下にしてください。"
     * )
     *  @Assert\Regex("/^[a-z]+[a-z0-9_]$/", message = "a-zで始まるa-z0-9_で入力してください。")
     */

    private $name;

    /**
     *  @ORM\Column(name="password", type="string", unique=true)
     *  @Assert\NotBlank(message = "パスワードを入力してください。")
     *  @Assert\Length(
     *      min = 8,
     *      max = 16,
     *      maxMessage = "8文字以上16文字以下にしてください。"
     *  )
     *  @Assert\Regex("/[a-zA-Z0-9]{8,16}/", message = "英数字で入力してください。")
     */

    private $password;

    /**
     *  @ORM\Column(name="memo", type="text")
     */

    private $memo;

    /**
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $created_date;

    /**
     * @ORM\Column(name="modified_date", type="datetime", nullable=true)
     */
    private $modified_date;

    //デフォルト
    public function __construct()
    {
        $current = new DateTime();
        $this->created_date = $current;
        $this->modified_date = $current;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Account
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set memo
     *
     * @param string $memo
     *
     * @return Account
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;

        return $this;
    }

    /**
     * Get memo
     *
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Account
     */
    public function setCreatedDate($createdDate)
    {
        $this->created_date = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     *
     * @return Account
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modified_date = $modifiedDate;

        return $this;
    }

    /**
     * Get modifiedDate
     *
     * @return \DateTime
     */
    public function getModifiedDate()
    {
        return $this->modified_date;
    }
}
