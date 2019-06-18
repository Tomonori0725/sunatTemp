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
            $acc_ss = $session->get('account_add');
            $form = $this->createFormBuilder($account)
            ->add('name', TextType::class,[
                'attr' => ['value' => $acc_ss->getName()]
            ])
            ->add('password', PasswordType::class,[
                'attr' => ['value' => $acc_ss->getPassword()],
                'constraints' => [
                    new NotBlank(['message' => 'パスワードを入力してください。']),
                ],
                'always_empty' => false
            ])
            ->add('memo', TextareaType::class,[
                'required' => false,
                'data' => $acc_ss->getMemo()
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
                'required' => false
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
        $account = $session->get('account_add');
        $form_finish = $this->createFormBuilder($account)
            ->add('finish', SubmitType::class)
            ->getForm();

        //Form送信のハンドリング
        $form_finish->handleRequest($request);
        //パスワードを伏字にする
        $secret_pass = str_repeat('●', mb_strlen($account->getPassword(), 'UTF8'));

        //確認→完了
        if ($form_finish->get('finish')->isClicked()) {
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

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $manageFunc = new ManageFunction($this->container, $em);
        
        //パスワード暗号化
        $encPass = $manageFunc->encPassword($account->getPassword());
        //パスワードハッシュ化
        $hashPass = $manageFunc->hashPassword($account->getPassword());

        //DBに書き込む
        $account->setPassword($encPass);
        $account->setHashPass($hashPass);
        $em->persist($account);
        $em->flush();

        //.htpasswdに書き込む
        $manageFunc->writePassword();

        /** 記事一覧にリダイレクト */
        return $this->redirectToRoute('accountList');
    }

}
