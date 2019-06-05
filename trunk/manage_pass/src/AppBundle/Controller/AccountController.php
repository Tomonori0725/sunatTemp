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

class AccountController extends Controller
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
    public function addAction(Request $request)
    {
        $account = new Account();
        $form = $this->createFormBuilder($account)
            ->add('name', TextType::class)
            ->add('password', PasswordType::class)
            ->add('memo', TextareaType::class, array('required' => false))
            ->add('confirm', SubmitType::class)
            ->getForm();

        $form_finish = $this->createFormBuilder($account)
            ->add('finish', SubmitType::class)
            ->getForm();

        //Form送信のハンドリング
        $form->handleRequest($request);
        $form_finish->handleRequest($request);
        $session = $request->getSession();
        
        //入力→確認
        if($form->get('confirm')->isClicked() && $form->isValid()){
            //セッションに挿入
            $session->set('account_add', $account);
            return $this->render('account/add/confirm.html.twig', [
                'form_finish' => $form_finish->createView(),
                'account' => $account
            ]);
        }
        //確認→完了
        if($form_finish->get('finish')->isClicked()){
            // DBに挿入
            //セッションから内容を取り出し
            $account = $session->get('account_add');

            //パスワード生成
            $date = $account->getPassword();
            $password = 'sunat';
            $method = 'AES-256-CBC';
            $iv = openssl_random_pseudo_bytes(16);
            $options = 0;
            $encPass = openssl_encrypt($date, $method, $password, $options, $iv);
            $account->setPassword(base64_encode($encPass));

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();

            /** 記事一覧にリダイレクト */
            return $this->redirectToRoute('accountList');
        }

        return $this->render('account/add/input.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * アカウントの編集
     * 
     * @Route("/edit/{id}", name="account_edit")
     * @Method({"GET","PUT"})
     * 
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Account::class);
        $account = $repository->find($id);

        $ssl_encode = base64_decode($account->getPassword());
        $password = 'sunat';
        $method = 'AES-256-CBC';
        $iv = '0123456789abcdef';
        $options = 0;

        //var_dump($ssl_encode);
        //var_dump(openssl_decrypt($ssl_encode, $method, $password, $options, $iv));
        $decode_pass = openssl_decrypt($ssl_encode, $method, $password, $options, $iv);

        if(!$account){
            throw $this->createNotFoundException('No account found for id' .$id);
        }

        $form = $this->createForm(AccountType::class, $account, ['method' => 'PUT'])
            ->add('password', PasswordType::class,[
                'always_empty' => false,
                'empty_data' => $decode_pass,
                'attr' => ['value' => $decode_pass]
            ])
            ->add('memo', TextareaType::class)
            ->add('confirm', SubmitType::class);

        $form_finish = $this->createFormBuilder()
            ->setMethod('PUT')
            ->add('finish', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        $form_finish->handleRequest($request);
        $session = $request->getSession();

        //入力→確認
        if($form->get('confirm')->isClicked() && $form->isValid()){
            //セッションに挿入
            $session->set('account_edit', $account);
            return $this->render('account/edit/confirm.html.twig', [
                'form_finish' => $form_finish->createView(),
                'account' => $account,
                'old_password' => $decode_pass,
            ]);
        }
        //確認→完了
        if($form_finish->get('finish')->isClicked()){
            //セッションから内容を取り出し
            $account_ss = $session->get('account_edit');
            //パスワード生成
            $date = $account_ss->getPassword();
            $password = 'sunat';
            $method = 'AES-256-CBC';
            $iv = '0123456789abcdef';
            $options = 0;
            $encPass = openssl_encrypt($date, $method, $password, $options, $iv);
            $account->setPassword(base64_encode($encPass));
            $account->setMemo($account_ss->getMemo());

            $em = $this->getDoctrine()->getManager();
            /** データベースに保存 */
            $em->flush();
            /** 記事一覧にリダイレクト */
            return $this->redirectToRoute('accountList');
        }

        $created_date = $account->getCreatedDate()->format('Y-m-d H:i');
        $modifid_date = $account->getModifiedDate()->format('Y-m-d H:i');
        return $this->render('account/edit/edit.html.twig',[
            'form' => $form->createView(),
            'created' => $created_date,
            'modifid' => $modifid_date
        ]);
    }

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

        if(!$account){
            throw $this->createNotFoundException('No account found for id' .$id);
        }
        if($this->isCsrfTokenValid('account', $request->get('_token'))){
            $em = $this->getDoctrine()->getManager();
            $em->remove($account);
            $em->flush();
        }

        return $this->redirectToRoute('accountList');
    }
    
}
