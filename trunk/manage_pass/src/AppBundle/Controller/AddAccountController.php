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
use Symfony\Component\Validator\Constraints\NotBlank;

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
        $session = $request->getSession();

        if ($session->has('account_add')) {
            //2回目
            $session_account = $session->get('account_add');
            $form = $this->createFormBuilder($account)
            ->add('name', TextType::class,[
                'attr' => ['value' => $session_account->getName()]
            ])
            ->add('password', PasswordType::class,[
                'attr' => ['value' => $session_account->getPassword()],
                'constraints' => [
                    new NotBlank(['message' => 'パスワードを入力してください。']),
                ],
                'always_empty' => false
            ])
            ->add('memo', TextareaType::class,[
                'required' => false,
                'data' => $session_account->getMemo(),
                'trim' => false
            ])
            ->add('confirm', SubmitType::class)
            ->getForm();
        } else {
            //初めて開いた時
            $form = $this->createFormBuilder($account)
            ->add('name', TextType::class)
            ->add('password', PasswordType::class,[
                'constraints' => [
                    new NotBlank(['message' => 'パスワードを入力してください。']),
                ],
                'always_empty' => false
            ])
            ->add('memo', TextareaType::class,[
                'required' => false,
                'trim' => false
            ])
            ->add('confirm', SubmitType::class)
            ->getForm();
        }

        //Form送信のハンドリング
        $form->handleRequest($request);
        
        //入力→確認
        if ($form->get('confirm')->isClicked() && $form->isValid()) {
            //セッションに挿入
            $session->set('account_add', $account);
            //確認画面にリダイレクト
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
        $session_account = $session->get('account_add');
        $form_finish = $this->createFormBuilder($session_account)
            ->add('finish', SubmitType::class)
            ->getForm();

        //Form送信のハンドリング
        $form_finish->handleRequest($request);
        //パスワードを伏字にする
        $secret_password = str_repeat('●', mb_strlen($session_account->getPassword(), 'UTF8'));

        //確認→完了
        if ($form_finish->get('finish')->isClicked()) {
            /** 完了画面にリダイレクト */
            return $this->redirectToRoute('account_add_thanks');
        }

        return $this->render('account/add/confirm.html.twig', [
            'form_finish' => $form_finish->createView(),
            'account' => $session_account,
            'secret_password' => $secret_password
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
        $session_account = $session->get('account_add');

        /** @var EntityManager $entity_manager */
        $entity_manager = $this->getDoctrine()->getManager();
        $manage_function = $this->get('app.manage_function');
        
        //パスワード暗号化
        $encPass = $manage_function->encPassword($session_account->getPassword());
        //パスワードハッシュ化
        $hashPass = $manage_function->hashPassword($session_account->getPassword());

        //DBに書き込む
        $session_account->setPassword($encPass);
        $session_account->setHashPass($hashPass);
        $entity_manager->persist($session_account);
        $entity_manager->flush();

        //.htpasswdに書き込む
        $manage_function->writePassword();

        /** 記事一覧にリダイレクト */
        return $this->redirectToRoute('accountList');
    }

}
