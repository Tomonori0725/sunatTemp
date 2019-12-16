<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ConfirmRegister\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CustomerStatus;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Session\Session;

class ConfirmRegisterShoppingController extends AbstractController
{

    /**
     * ConfirmRegisterShoppingController constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * 会員登録する.
     *
     * @Route("/ajax", name="check_customer")
     * 
     * @param Request $request
     * @return Response
     * 
     */
    public function checkCustomerAction(Request $request)
    {

        // 会員情報を配列に挿入する.
        $customer = array();
        foreach ($_POST as $key => $value) {
            $customer[$key] = $value;
        }

        // バリデーションチェック.
        $error = $this->checkValidaton($customer);

        // エラーがあれば内容を返す.
        if ($error) {
            return new Response(json_encode($error));
        }
        // エラーがなければセッションに登録
        $_SESSION['regist_password'] = $customer['password'];

        return new Response(null);
    }

    /**
     * バリデーションチェック.
     */
    public function checkValidaton($customer){
        $errorList = '';

        // 半角英数記号8～32文字.
        if (!preg_match("/^[!-~]{8,32}$/", $customer['password'])) {
            $errorList .= '<p class="error">半角英数記号8〜32文字で入力してください。</p>';
        }

        // メールアドレスの重複チェック.
        $is_email = $this->entityManager->createQuery(
            'SELECT c.id FROM ECCUBE\Entity\Customer c WHERE c.email=:email')
            ->setParameter('email', $customer['email'])
            ->getResult();
        if (count($is_email) > 0) {
            $errorList .= '<p class="error">既に登録されたメールアドレスです。</p>';
        }

        //エラーがあれば内容を返す.
        if ($errorList !== '') {
            return $errorList;
        } 

        return false;
    }
}
