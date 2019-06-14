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

class AddAccountController extends Controller
{
    /**
     * アカウントの新規登録
     * 
     * @Route("/add", name="account_add")
     * @Method({"GET", "POST"})
     * 
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $account = new Account();
        $form = $this->createFormBuilder($account)
            ->add('name', TextType::class)
            ->add('password', PasswordType::class,[
                'always_empty' => false
            ])
            ->add('memo', TextareaType::class, array('required' => false))
            ->add('confirm', SubmitType::class)
            ->getForm();


        //Form送信のハンドリング
        $form->handleRequest($request);
        $session = $request->getSession();
        
        //入力→確認
        if ( $form->get('confirm')->isClicked() && $form->isValid() ) {
            //セッションに挿入
            $session->set('account_add', $account);

            /** 確認画面にリダイレクト */
            return $this->redirectToRoute('account_add_confirm');
        }

        return $this->render('account/add/input.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * アカウントの新規登録 確認
     * 
     * @Route("/add/confirm", name="account_add_confirm")
     * @Method({"GET", "POST"})
     * 
     * @param Request $request
     * @return Response
     */
    public function addConfirmAction(Request $request)
    {
        $session = $request->getSession();
        $account = $session->get('account_add');
        $form_finish = $this->createFormBuilder($account)
            ->add('finish', SubmitType::class)
            ->getForm();

        //Form送信のハンドリング
        $form_finish->handleRequest($request);

        $secret_pass = str_repeat('●', mb_strlen($account->getPassword(), 'UTF8'));

        //確認→完了
        if ( $form_finish->get('finish')->isClicked() ) {
            /** 完了画面にリダイレクト */
            return $this->redirectToRoute('account_add_thanks');
        }

        return $this->render('account/add/confirm.html.twig', [
            'form_finish' => $form_finish->createView(),
            'account' => $account,
            'secret_pass' => $secret_pass
        ]);
    }

    /**
     * アカウントの新規登録 完了画面
     * 
     * @Route("/add/thanks", name="account_add_thanks")
     * @Method({"GET", "POST"})
     * 
     * @param Request $request
     * @return Response
     */
    public function addThanksAction(Request $request)
    {
        // DBに挿入
        //セッションから内容を取り出し
        //Form送信のハンドリング
        $session = $request->getSession();
        $account = $session->get('account_add');

        //パスワード暗号化
        $encPass = $this->encPassword($account->getPassword());

        //DBに書き込む
        $account->setPassword($encPass);
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->persist($account);
        $em->flush();

        //.htpasswdに書き込む
        $this->writePassword();

        $session->remove('account_add');

        /** 記事一覧にリダイレクト */
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
