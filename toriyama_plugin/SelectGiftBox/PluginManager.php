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

namespace Plugin\SelectGiftBox;

use Eccube\Entity\Layout;
use Eccube\Entity\Page;
use Eccube\Plugin\AbstractPluginManager;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Master\SelectGiftBoxType;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Repository\LayoutRepository;
use Eccube\Repository\PageLayoutRepository;
use Eccube\Repository\PageRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManager;

/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    private $entityDir = __DIR__.'/Entity/Master/';
    private $repositoryDir = __DIR__.'/Repository/Master/';
    private $masterEntity = 'SelectGiftBoxType.php';
    private $masterRepository = 'SelectGiftBoxTypeRepository.php';

    /**
     * install the plugin.
     * 
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function install(array $meta, ContainerInterface $container)
    {
        // Entity・Repositoryをsrcに移動.
        $this->copyMasterEntity($container);

        // テーブルを作成.
        $em = $container->get('doctrine.orm.entity_manager');
        $sql =  'CREATE TABLE mtb_select_gift_box_type (id smallint(5) UNSIGNED NOT NULL, name varchar(255) NOT NULL, sort_no smallint(5) UNSIGNED NOT NULL, discriminator_type varchar(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();

        // order_item_typeに箱代を追加する.
        $orderItemType = new OrderItemType();
        $orderItemType->setId(100);
        $orderItemType->setName('箱代');
        $orderItemType->setSortNo(100);
        $em->persist($orderItemType);
        $em->flush();

        // // 初期データ
        // $initDatas[] = array(
        //     'id'     => 1,
        //     'name'   => '自宅用',
        //     'sortNo' => 0
        // );
        // $initDatas[] = array(
        //     'id'     => 2,
        //     'name'   => '贈答用',
        //     'sortNo' => 1
        // );

        // // 初期データを挿入
        // $em = $container->get('doctrine.orm.entity_manager');
        // foreach ($initDatas as $initData) {
        //     $giftBoxType = $container->get(SelectGiftBoxType::class);
        //     $giftBoxType->setId($initData['id']);
        //     $giftBoxType->setName($initData['name']);
        //     $giftBoxType->setSortNo($initData['sortNo']);
        //     $em->persist($giftBoxType);
        // }
        // $em->flush();
    }

    /**
     * Uninstall the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
        $this->removeMasterEntity($container);
        // テーブルを削除.
        $em = $container->get('doctrine.orm.entity_manager');
        $sql =  'DROP TABLE mtb_select_gift_box_type';
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
    }

    /**
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function enable(array $meta, ContainerInterface $container)
    {
    }

    /**
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function disable(array $meta, ContainerInterface $container)
    {
    }

    /**
     * Copy block template.
     *
     * @param ContainerInterface $container
     */
    private function copyMasterEntity(ContainerInterface $container)
    {
        $templateDir = $container->getParameter('kernel.project_dir');
        // ファイルコピー.
        $file = new Filesystem();
        // Master/Entity Repositoryをコピー・削除
        if (!$file->exists($templateDir.'/src/Eccube/Entity/Master/'.$this->masterEntity)) {
            $file->copy($this->entityDir.$this->masterEntity, $templateDir.'/src/Eccube/Entity/Master/'.$this->masterEntity);
            $file->remove($this->entityDir.$this->masterEntity);
        }
        if (!$file->exists($templateDir.'/src/Eccube/Repository/Master/'.$this->masterRepository)) {
            $file->copy($this->repositoryDir.$this->masterRepository, $templateDir.'/src/Eccube/Repository/Master/'.$this->masterRepository);
            $file->remove($this->repositoryDir.$this->masterRepository);
        }
    }

    /**
     * Remove block template.
     *
     * @param ContainerInterface $container
     */
    private function removeMasterEntity(ContainerInterface $container)
    {
        $templateDir = $container->getParameter('kernel.project_dir');
        $file = new Filesystem();
        // プラグインにコピーして、マスターのEntityを削除.
        if ($file->exists($templateDir.'/src/Eccube/Entity/Master/'.$this->masterEntity)) {
            $file->remove($templateDir.'/src/Eccube/Entity/Master/'.$this->masterEntity);
        }
        if ($file->exists($templateDir.'/src/Eccube/Repository/Master/'.$this->masterRepository)) {
            $file->remove($templateDir.'/src/Eccube/Repository/Master/'.$this->masterRepository);
        }
    }

}
