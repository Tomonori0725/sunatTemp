<?php

namespace Plugin\ConfirmRegister;
use Psr\Container\ContainerInterface;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\Pref;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\Master\CustomerStatusRepository;
use Eccube\Repository\Master\PrefRepository;
use Eccube\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Event\TemplateEvent;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Event implements EventSubscriberInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var BaseInfo */
    private $BaseInfo;

    /** @var CustomerStatusRepository */
    protected $customerStatusRepository;

    /** @var PrefStatusRepository */
    protected $prefRepository;

    /** @var CustomerRepository */
    private $customerRepository;
    
    /** @var OrderRepository */
    private $orderRepository;
    
    /** @var MailService */
    private $mailService;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    public function __construct(
        ContainerInterface $container,
        BaseInfoRepository $baseInfoRepository,
        CustomerRepository $customerRepository,
        CustomerStatusRepository $customerStatusRepository,
        PrefRepository $prefRepository,
        OrderRepository $orderRepository,
        EncoderFactoryInterface $encoderFactory,
        EntityManagerInterface $entityManager,
        MailService $mailService
    ) {
        $this->container = $container;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->customerRepository = $customerRepository;
        $this->customerStatusRepository = $customerStatusRepository;
        $this->prefRepository = $prefRepository;
        $this->orderRepository = $orderRepository;
        $this->encoderFactory = $encoderFactory;
        $this->entityManager = $entityManager;
        $this->mailService = $mailService;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopping/confirm.twig' => 'index',
            EccubeEvents::FRONT_SHOPPING_COMPLETE_INITIALIZE => 'onFrontShoppingCompleteInitialize',
        ];
    }

    /**
     * @param TemplateEvent $event
     */
    public function index(TemplateEvent $event)
    {
        $event->addSnippet('@ConfirmRegister/default/confirm_register_shopping_item.twig');
    }

    /**
     * 注文完了画面のevent hock.
     * 会員登録をし、メールを送信する.
     *
     * @param TemplateEvent $event
     */
    public function onFrontShoppingCompleteInitialize(EventArgs $event)
    {
        if (!array_key_exists('regist_password', $_SESSION)) {
            return;
        }

        // 仮会員がオフかどうか.
        $activateFlg = $this->BaseInfo->isOptionCustomerActivate();

        // 都道府県を取得する.
        $pref = $this->prefRepository->findBy(array('id' => $_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['Pref']['id']));

        // 会員登録をする.
        $new_customer = new Customer();
        $new_customer
            ->setName01($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['name01'])
            ->setName02($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['name02'])
            ->setKana01($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['kana01'])
            ->setKana02($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['kana02'])
            ->setCompanyName($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['company_name'])
            ->setEmail($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['email'])
            ->setPhonenumber($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['phone_number'])
            ->setPostalcode($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['postal_code'])
            ->setPref($pref[0])
            ->setAddr01($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['addr01'])
            ->setAddr02($_SESSION["_sf2_attributes"]['eccube.front.shopping.nonmember']['addr02'])
            ->setPassword($_SESSION['regist_password']);

        // 仮会員がオフの時に本会員にする.
        if (!$activateFlg) {
            $customerStatus = $this->customerStatusRepository->find(CustomerStatus::REGULAR);
            $new_customer->setStatus($customerStatus);
        } else {
            $customerStatus = $this->customerStatusRepository->find(CustomerStatus::NONACTIVE);
            $new_customer->setStatus($customerStatus);
        }

        // パスワードを暗号化.
        $encoder = $this->encoderFactory->getEncoder($new_customer);
        $salt = $encoder->createSalt();
        $password = $encoder->encodePassword($new_customer->getPassword(), $salt);
        $secretKey = $this->customerRepository->getUniqueSecretKey();

        // 暗号化したパスワードをセット.
        $new_customer
            ->setSalt($salt)
            ->setPassword($password)
            ->setSecretKey($secretKey)
            ->setPoint(0);

        // DBに追加する.
        $this->entityManager->persist($new_customer);
        $this->entityManager->flush();

        // 注文履歴に会員履歴を紐づける.
        $order = $this->orderRepository->findOneById(array('id' => $_SESSION["_sf2_attributes"]["eccube.front.shopping.order.id"]));
        $order->setCustomer($new_customer);
        $this->entityManager->flush();

        // 仮会員用のメールを送信する.
        $activateUrl = $this->container->get('router')->generate('entry_activate', ['secret_key' => $new_customer->getSecretKey()], UrlGeneratorInterface::ABSOLUTE_URL);

        // メールを送信する.
        if ($activateFlg) {
            // 仮会員メールを送信.
            $this->mailService->sendCustomerConfirmMail($new_customer, $activateUrl);
        } else {
            // 会員登録完了メールを送信.
            $this->mailService->sendCustomerCompleteMail($new_customer, $activateUrl);
        }

        // パスワードを削除
        unset($_SESSION['regist_password']);

    }

}
