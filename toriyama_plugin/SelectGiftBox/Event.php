<?php

namespace Plugin\SelectGiftBox;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eccube\Event\TemplateEvent;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Repository\ShippingRepository;
use Eccube\Repository\OrderItemRepository;
use Eccube\Repository\Master\SelectGiftBoxTypeRepository;
use Plugin\SelectGiftBox\Entity\Config;
use Plugin\SelectGiftBox\Repository\ConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Event\EventArgs;
use Eccube\Event\EccubeEvents;

class Event implements EventSubscriberInterface
{

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /** @var ShippingRepository */
    protected $shippingRepository;

    /** @var OrderItemRepository */
    protected $orderItemRepository;

    /** @var SelectGiftBoxTypeRepository */
    protected $selectGiftBoxTypeRepository;

    /**
     * DeliveryFeePreprocessor constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Config $config,
        ConfigRepository $configRepository,
        ShippingRepository $shippingRepository,
        OrderItemRepository $orderItemRepository,
        SelectGiftBoxTypeRepository $selectGiftBoxTypeRepository
    ) {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->configRepository = $configRepository;
        $this->shippingRepository = $shippingRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->selectGiftBoxTypeRepository = $selectGiftBoxTypeRepository;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopping/index.twig'    => 'index',
            'Shopping/confirm.twig'  => 'confirm',
            '@admin/Order/edit.twig' => 'updateTaxType',
            '@admin/Order/order_item_type.twig' => 'addOrderItemType',
            EccubeEvents::MAIL_ORDER => 'orderMail',
        ];
    }

    /**
     * 注文手続き 箱代の入力欄を追加.
     * 
     * @param TemplateEvent $event
     */
    public function index(TemplateEvent $event)
    {
        $event->addSnippet('@SelectGiftBox/default/shipping_gift_box_radio.twig');
    }

    /**
     * 注文手続き確認画面 注文明細に追加.
     * 
     * @param TemplateEvent $event
     */
    public function confirm(TemplateEvent $event)
    {
        // 注文・配送情報を取得.
        $order = $event->getParameter('Order');
        $shippings = $order->getShippings();

        // 箱代の合計.
        $boxTotal = 0;

        // 入力された値を整理する.
        $orderGiftBox = array();
        foreach ($_POST["_shopping_order"]["Shippings"] as $orderShipping) {
            $orderGiftBox[$orderShipping['shipping_id']] = $orderShipping['gift_box'];
        }

        // OrderItemに必要な情報を取得.
        $GiftBoxType = $this->entityManager
            ->find(OrderItemType::class, 100);
        $TaxInclude = $this->entityManager
            ->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);
        $Taxation = $this->entityManager
            ->find(TaxType::class, TaxType::TAXATION);

        // プラグインの設定を取得.
        $pluginConfig = $this->configRepository->get();
        $parameters = $event->getParameters();

        // 配送情報(Shipping)に追加する.
        foreach ($shippings as $shipping) {
            $selectGiftBox = $this->selectGiftBoxTypeRepository->findBy(array('id' => $orderGiftBox[$shipping->getId()]));

            if ($selectGiftBox) {
                $shipping->setGiftBoxId($selectGiftBox[0]);
            }

            // 注文番号と配送番号から注文詳細を取得する.
            $orderItem = $this->orderItemRepository->findBy(
                array(
                    'Order'         => $order,
                    'Shipping'      => $shipping,
                    'OrderItemType' => $GiftBoxType
                )
            );

            // 箱代を決める.
            $price = 0;
            if ($orderGiftBox[$shipping->getId()] != 1) {
                if (!is_null($pluginConfig->getPrice())) {
                    $price = $pluginConfig->getPrice();
                }
            }

            if ($orderItem) {
                // 更新の場合.
                $orderItem[0]->setPrice($price);
            } else {
                // 新規登録の場合.
                $OrderItem = new OrderItem();
                $OrderItem->setProductName('箱代')
                    ->setPrice($price)
                    ->setQuantity(1)
                    ->setOrderItemType($GiftBoxType)
                    ->setShipping($shipping)
                    ->setOrder($order)
                    ->setTaxDisplayType($TaxInclude)
                    ->setTaxType($Taxation)
                    ->setProcessorName(Plugin\SelectGiftBox\Event::class);
                $this->entityManager->persist($OrderItem);
            }
            // 箱代の合計を計算する.
            $boxTotal += $price;
            // DBに反映.
            $this->entityManager->flush();
        }

        // テンプレートに反映する.
        foreach ($_POST["_shopping_order"]["Shippings"] as $orderShipping) {
            // 配送情報と箱の種類をセッションに保存.
            $_SESSION['select_gift_box'][$orderShipping["shipping_id"]] = $orderShipping["gift_box"];

            // テンプレートに渡す.
            $giftBox = $this->selectGiftBoxTypeRepository->findBy(array('id' => $orderShipping["gift_box"]));
            $parameters['giftBox'][$orderShipping["shipping_id"]] = $giftBox[0]->getName();
        }
        // 箱代合計をテンプレートに反映する.
        $parameters['giftBoxTotal'] = $boxTotal;
        // テンプレートに反映.
        $event->setParameters($parameters);
        // テンプレートを追加.
        $event->addSnippet('@SelectGiftBox/default/shipping_gift_box_radio_confirm.twig');
    }

    /**
     * 注文手続き 箱代の入力欄を追加.
     * 
     * @param TemplateEvent $event
     */
    public function updateTaxType(TemplateEvent $event)
    {
        $order = $event->getParameter('Order');
        $parameters = $event->getParameters();
        $giftBoxType = $this->entityManager->find(OrderItemType::class, 100);
        $taxDisplayType = $this->entityManager->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);

        // 注文詳細の箱代を取得.
        $orderItem = $this->orderItemRepository->findBy(
            array(
                'Order'         => $order,
                'OrderItemType' => $giftBoxType
            )
        );

        // 箱代を税込にして、合計を計算する.
        $parameters['giftBoxTotal'] = 0;
        if ($_POST) {
            foreach ($_POST['order']['OrderItems'] as $item) {
                if ($item['order_item_type'] === '100') {
                    $parameters['giftBoxTotal'] += $item['price'];
                }
            }
        } else {
            foreach ($orderItem as $item) {
                if ($item->getTaxDisplayType()->getId() !== 2) {
                    $item->setTaxDisplayType($taxDisplayType);
                    $this->entityManager->flush();
                }
                $parameters['giftBoxTotal'] += $item->getPrice();
            }
        }

        // パラメータに追加.
        $event->setParameters($parameters);

        // 箱代の合計を表示.
        $event->addSnippet('@SelectGiftBox/admin/order_edit.twig');

    }
    

    /**
     * @param TemplateEvent $event
     */
    public function addOrderItemType(TemplateEvent $event)
    {
        $parameters = $event->getParameters();
        
        $giftBox = $this->entityManager->find(OrderItemType::class, 100);
        $Taxation = $this->entityManager->find(TaxType::class, TaxType::TAXATION);

        $parameters['OrderItemTypes'][] = array(
            'OrderItemType' => $giftBox,
            'TaxType'       => $Taxation
        );
        $event->setParameters($parameters);

    }


    /**
     * メールに箱代を追加する.
     * 
     * @param TemplateEvent $event
     */
    public function orderMail(EventArgs $event)
    {

        // $message = $event->getArgument("message");
        // $Order = $event->getArgument("Order");

        // $Shippings = $Order->getShippings();
        // foreach ($Shippings as $shipping) {
        //     var_dump($shipping->getGiftBoxId());
        // }
        // exit;

        // log_info('完了メールに箱代を挿入するイベントに入りました.', [$Order->getId()]);

        // $body = $message->getBody();
        // var_dump($body);
        // exit;

        // $message->setBody($body, 'text/plain');



        // foreach ($Order->getOrderItems() as $item) {
        //     var_dump($item->getOrderItemType()->getId());
        //     var_dump($item->getProductName());
        //     var_dump($item->getPrice());
        //     echo "<br>";
        // }
        // exit;
}

}
