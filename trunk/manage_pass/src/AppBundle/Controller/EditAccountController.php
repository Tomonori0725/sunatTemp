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
use AppBundle\Service\ManageFunction;

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
        if (!$account) {
            return $this->redirectToRoute('accountList');
        }

        //サービスコンテナを読み込む
        $em = $this->getDoctrine()->getManager();
        $manageFunc = new ManageFunction($this->container, $em);

        //確認画面から戻ってきた時にフォームに値をいれる。
        $session = $request->getSession();
        if ($session->has('account_edit')) {
            $acc_ss = $session->get('account_edit');
            $decode_pass = $acc_ss->getPassword();
            $memo = $acc_ss->getMemo();
        } else {
            //パスワードを復号化
            $decode_pass = $manageFunc->decPassword($account->getPassword());
            $memo = $account->getMemo();
        }

        //もしパスワードがなければ
        if (!$decode_pass) {
            $decode_pass = $this->container->getParameter('DUMMY_PASS');
        }

        $form = $this->createForm(AccountType::class, $account, ['method' => 'PUT'])
            ->add('password', PasswordType::class,[
                'always_empty' => false,
                'empty_data' => $decode_pass,
                'attr' => ['value' => $decode_pass]
            ])
            ->add('memo', TextareaType::class, [
                'data' => $memo
            ])
            ->add('confirm', SubmitType::class);

        $form->handleRequest($request);

        //入力→確認
        if ($form->get('confirm')->isClicked() && $form->isValid()) {
            if (strcmp($decode_pass, $this->container->getParameter('DUMMY_PASS')) == 0 ) {
                $account->setPassword('');
            }
            //セッションに挿入
            $session->set('account_edit', $account);
            /** 確認画面にリダイレクト */
            return $this->redirectToRoute('account_edit_confirm', ['id' => $id]);
        }
        
        return $this->render('account/edit/input.html.twig',[
            'account' => $account,
            'form' => $form->createView()
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
        if ($form_finish->get('finish')->isClicked()) {
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

        //サービスコンテナを読み込む
        $em = $this->getDoctrine()->getManager();
        $manageFunc = new ManageFunction($this->container, $em);

        //パスワードを暗号化
        $pass = $account_ss->getPassword();
        if ($pass) {
            $encPass = $manageFunc->encPassword($pass);
        } else {
            $encPass = '';
        }
        //パスワードをハッシュ化
        $hashPass = $manageFunc->hashPassword($account_ss->getPassword());

        //DBに書き込む
        $account->setPassword($encPass);
        $account->setHashPass($hashPass);
        $account->setMemo($account_ss->getMemo());

        $em->flush();

        //.htpasswdに書き込む
        $manageFunc->writePassword();
            
        //記事一覧にリダイレクト
        return $this->redirectToRoute('accountList');

    }

}
