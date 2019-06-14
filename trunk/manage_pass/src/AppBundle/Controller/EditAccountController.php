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

class EditAccountController extends Controller
{
    /**
     * アカウントの編集
     * 
     * @Route("/edit/{id}", name="account_edit")
     * @Method({"GET","PUT"})
     * 
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Account::class);
        $account = $repository->find($id);
        if (! $account ) {
            return $this->redirectToRoute('accountList');
        }

        //パスワードを復号化
        $decode_pass = $this->decPassword($account->getPassword());

        $form = $this->createForm(AccountType::class, $account, ['method' => 'PUT'])
            ->add('password', PasswordType::class,[
                'always_empty' => false,
                'empty_data' => $decode_pass,
                'attr' => ['value' => $decode_pass]
            ])
            ->add('memo', TextareaType::class)
            ->add('confirm', SubmitType::class);

        $form->handleRequest($request);
        $session = $request->getSession();

        //入力→確認
        if ( $form->get('confirm')->isClicked() && $form->isValid() ) {
            //セッションに挿入
            $session->set('account_edit', $account);
            /** 確認画面にリダイレクト */
            return $this->redirectToRoute('account_edit_confirm', ['id' => $id]);
        }

        $created_date = $account->getCreatedDate()->format('Y-m-d H:i');
        $modifid_date = $account->getModifiedDate()->format('Y-m-d H:i');
        
        return $this->render('account/edit/input.html.twig',[
            'name' => $account->getName(),
            'form' => $form->createView(),
            'created' => $created_date,
            'modifid' => $modifid_date
        ]);
    }

    /**
     * アカウントの編集　確認画面
     * 
     * @Route("/edit/confirm/{id}", name="account_edit_confirm")
     * @Method({"GET","PUT"})
     * 
     * @param Request $request
     * @return Response
     */
    public function editConfirmAction(Request $request, $id)
    {
        $session = $request->getSession();
        $account = $session->get('account_edit');
        $secret_pass = str_repeat('●', mb_strlen($account->getPassword(), 'UTF8'));
        $form_finish = $this->createFormBuilder()
        ->setMethod('PUT')
        ->add('finish', SubmitType::class)
        ->getForm();
        $form_finish->handleRequest($request);

        //確認→完了
        if ( $form_finish->get('finish')->isClicked() ) {
            /** 確認画面にリダイレクト */
            return $this->redirectToRoute('account_edit_thanks', ['id' => $id]);
        }

        return $this->render('account/edit/confirm.html.twig', [
            'form_finish' => $form_finish->createView(),
            'account' => $account,
            'secret_pass' => $secret_pass
        ]);
    }

    /**
     * アカウントの編集　完了画面
     * 
     * @Route("/edit/thanks/{id}", name="account_edit_thanks")
     * @Method({"GET","PUT"})
     * 
     * @param Request $request
     * @return Response
     */
    public function editThanksAction(Request $request, $id)
    {
        //セッションから内容を取り出し
        $session = $request->getSession();
        $account_ss = $session->get('account_edit');
        $repository = $this->getDoctrine()->getRepository(Account::class);
        $account = $repository->find($id);

        //パスワードを暗号化
        $encPass = $this->encPassword($account_ss->getPassword());

        //DBに書き込む
        $account->setPassword($encPass);
        $account->setMemo($account_ss->getMemo());
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        //.htpasswdに書き込む
        $this->writePassword();
            
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