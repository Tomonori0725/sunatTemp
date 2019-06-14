<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Entity\Account;
use AppBundle\Form\AccountType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class AccountController extends Controller
{

    /**
     * アカウントの削除
     * 
     * @Route("/delete/{id}", name="account_delete")
     * @Method({"DELETE"})
     * 
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Account::class);
        $account = $repository->find($id);

        if ( ! $account ) {
            throw $this->createNotFoundException('No account found for id' .$id);
        }
        if ( $this->isCsrfTokenValid('account', $request->get('_token')) ) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($account);
            $em->flush();
            //.htaccessに書き込む
            $this->writePassword();
        }

        return $this->redirectToRoute('accountList');
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
     * .htpasswdに書き出し
     */
    public function writePassword()
    {
        $htpasswd = '';
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT a.name, a.password FROM AppBundle:Account a');
        $dates = $query->getResult();
        $path = $this->container->getParameter('ht_path');

        //書き込み可能かどうか
        if ( !is_writable($path) ) {
            chmod($path, 0666);
        }

        //DBからアカウント情報を持ってくる
        foreach($dates as $date){
            $htpasswd .= $date['name'] . ':' . password_hash($this->decPassword($date['password']), PASSWORD_BCRYPT) . "\n";
        }
        //.htpasswdに書き込み
        file_put_contents($path, $htpasswd, LOCK_EX);
    }

}
