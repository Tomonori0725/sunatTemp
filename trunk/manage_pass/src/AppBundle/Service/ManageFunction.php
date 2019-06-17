<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManager;

class ManageFunction
{

    protected $container;
    /** @var EntityManager $em */
    protected $em;

    public function __construct(Container $container, EntityManager $entitymanager){
        $this->container = $container;
        $this->em = $entitymanager;
    }

    /**
     * パスワードの暗号化(DB)
     */
    public function encPassword($date)
    {
        $method = $this->container->getParameter('enc_method');
        $key = $this->container->getParameter('enc_key');
        $options = 0;

        //iv作成
        $iv_size = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_size);
        $encPass = openssl_encrypt($date, $method, $key, $options, $iv);

        return base64_encode($iv . $encPass);
    }

    /**
     * パスワードの復号化(DB)
     */
    public function decPassword($date)
    {
        $ssl_encode = base64_decode($date);

        $method = $this->container->getParameter('enc_method');
        $key = $this->container->getParameter('enc_key');
        $options = 0;

        //ivとパスワードを切り離し
        $iv_size = openssl_cipher_iv_length($method);
        $iv = substr($ssl_encode, 0, $iv_size);
        $encPass = substr($ssl_encode, $iv_size);

        $decPass = openssl_decrypt($encPass, $method, $key, $options, $iv);

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
        $path = $this->container->getParameter('ht_path');

        //DBからアカウント情報を持ってくる
        foreach($dates as $date){
            $htpasswd .= $date['name'] . ':' . $date['hashPass'] . "\n";
        }
        //.htpasswdに書き込み
        file_put_contents($path, $htpasswd, LOCK_EX);
    }

}
