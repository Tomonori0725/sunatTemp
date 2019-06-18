<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

class ManageFunction
{
    protected $container;
    protected $em;
    protected $method = '';
    protected $key = '';
    protected $options = 0;

    public function __construct(Container $container, EntityManager $entitymanager)
    {
        $this->container = $container;
        $this->em = $entitymanager;
        $this->method = $container->getParameter('ENC_METHOD');
        $this->key = $container->getParameter('ENC_KEY');
    }

    /**
     * パスワードの暗号化(DB)
     */
    public function encPassword($date)
    {
        //iv作成
        $iv_size = openssl_cipher_iv_length($this->method);
        $iv = openssl_random_pseudo_bytes($iv_size);
        $encPass = openssl_encrypt($date, $this->method, $this->key, $this->options, $iv);

        return base64_encode($iv . $encPass);
    }

    /**
     * パスワードの復号化(DB)
     */
    public function decPassword($date)
    {
        //base64を復号化
        $ssl_encode = base64_decode($date);

        //ivとパスワードを切り離し
        $iv_size = openssl_cipher_iv_length($this->method);
        $iv = substr($ssl_encode, 0, $iv_size);
        $encPass = substr($ssl_encode, $iv_size);
        $decPass = openssl_decrypt($encPass, $this->method, $this->key, $this->options, $iv);

        return $decPass;
    }

    /**
     * パスワードをハッシュ化する
     */
    public function hashPassword($pass)
    {
        return password_hash($pass, PASSWORD_BCRYPT);
    }

    /**
     * .htpasswdに書き出し
     */
    public function writePassword()
    {
        $htpasswd = '';
        $query = $this->em->createQuery('SELECT a.name, a.hashPass FROM AppBundle:Account a');
        $dates = $query->getResult();
        $path = $this->container->getParameter('HT_PATH');

        //DBからアカウント情報を持ってくる
        foreach ($dates as $date) {
            $htpasswd .= $date['name'] . ':' . $date['hashPass'] . "\n";
        }
        //.htpasswdに書き込み
        file_put_contents($path, $htpasswd, LOCK_EX);
    }

}
