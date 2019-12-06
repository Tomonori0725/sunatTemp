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

use Eccube\Entity\BaseInfo;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Entity\Order;
use Eccube\Entity\Master\Pref;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\DeliveryTimeRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\PrefRepository;
use Eccube\Service\MailService;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Service\CartService;
use Eccube\Service\OrderHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConfirmRegisterShoppingController extends AbstractController
{

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;
    
    /**
     * @var CustomerRepository 
     */
    private $customerRepository;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var MailService
     */
    protected $mailService;


    /**
     * ConfirmRegisterShoppingController constructor.
     * 
     * @param BaseInfoRepository $baseInfoRepository
     * @param MailService $mailService
     * 
     */
    public function __construct(
        BaseInfoRepository $baseInfoRepository,
        CustomerRepository $customerRepository,
        MailService $mailService,
        EncoderFactoryInterface $encoderFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->mailService = $mailService;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->customerRepository = $customerRepository;
        $this->encoderFactory = $encoderFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * 会員登録する.
     *
     * @Route("/ajax", name="regist_customer")
     * 
     * @param Request $request
     * @return Response
     * 
     */
    public function registCustomerAction(Request $request)
    {
        // 会員情報を配列に挿入する
        $customer = array();
        foreach ($_POST as $key => $value) {
            $customer[$key] = $value;
        }

        // バリデーションチェック
        $error = $this->checkPassword($customer);
        // エラーがあれば内容を返す
        if ($error) {
            return new Response(json_encode($error));
        }

        log_info('会員登録を開始する');

        // 都道府県を取得する
        $em = $this->getDoctrine()->getManager();            
        $query = 'SELECT id FROM mtb_pref as pref WHERE pref.name = :pref';
        $statement = $em->getConnection()->prepare($query);
        $statement->bindValue('pref', $customer['customer_pref']);
        $statement->execute();
        $results = $statement->fetchAll();
        $pref = $results[0];
        $pref = $this->getDoctrine()->getRepository('Eccube\\Entity\\Master\\Pref')->find($results[0]['id']);

        // 会員登録をする
        $new_customer = $this->getDoctrine()->getRepository(Customer::class)->newCustomer();
        $new_customer
            ->setName01($customer['customer_name01'])
            ->setName02($customer['customer_name02'])
            ->setKana01($customer['customer_kana01'])
            ->setKana02($customer['customer_kana02'])
            ->setCompanyName($customer['customer_company_name'])
            ->setEmail($customer['email'])
            ->setPhonenumber($customer['customer_phone_number'])
            ->setPostalcode($customer['customer_postal_code'])
            ->setPref($pref)
            ->setAddr01($customer['customer_addr01'])
            ->setAddr02($customer['customer_addr02'])
            ->setPassword($customer['password']);

        log_info('パスワード暗号化');
        // パスワードを暗号化
        $encoder = $this->encoderFactory->getEncoder($new_customer);
        $salt = $encoder->createSalt();
        $password = $encoder->encodePassword($new_customer->getPassword(), $salt);
        $secretKey = $this->customerRepository->getUniqueSecretKey();

        // 暗号化したパスワードをセット
        $new_customer
            ->setSalt($salt)
            ->setPassword($password)
            ->setSecretKey($secretKey)
            ->setPoint(0);

        log_info('DBに追加');
        $this->entityManager->persist($new_customer);
        $this->entityManager->flush();
        log_info('値を返す');


        $activateUrl = $this->generateUrl('entry_activate', ['secret_key' => $new_customer->getSecretKey()], UrlGeneratorInterface::ABSOLUTE_URL);
        $activateFlg = $this->BaseInfo->isOptionCustomerActivate();
        // 仮会員設定が有効な場合は、確認メールを送信し完了画面表示.
        if ($activateFlg) {
            // メール送信
            $this->mailService->sendCustomerConfirmMail($new_customer, $activateUrl);
            log_info('仮会員登録完了画面へリダイレクト');
        }

        return new Response(null);
    }


    public function checkPassword($customer){
        $errorList = '';

        // 半角英数記号8～32文字
        if (!preg_match("/^[!-~]{8,32}$/", $customer['password'])) {
            $errorList .= '<p class="error">半角英数記号8〜32文字で入力してください。</p>';
        }

        // メールアドレスの重複チェック
        $is_email = $this->entityManager->createQuery(
            'SELECT c.id FROM ECCUBE\Entity\Customer c WHERE c.email=:email')
            ->setParameter('email', $customer['email'])
            ->getResult();
        if (count($is_email) > 0) {
            $errorList .= '<p class="error">既に登録されたメールアドレスです。</p>';
        }

        //エラーがあれば内容を返す
        if ($errorList !== '') {
            return $errorList;
        } 

        return false;
    }


}
