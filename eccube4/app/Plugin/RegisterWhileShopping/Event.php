<?php

namespace Plugin\RegisterWhileShopping;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;

/*use Eccube\Entity\BaseInfo;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Eccube\Request\Context;*/

class Event implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopping/confirm.twig' => 'index',
            EccubeEvents::FRONT_SHOPPING_CONFIRM_INITIALIZE => 'onResponse',
        ];
    }

    /**
     * @param TemplateEvent $event
     */
    public function index(TemplateEvent $event)
    {
        $event->addSnippet('@RegisterWhileShopping/default/Shopping/confirm.twig');
    }

    public function onResponse(EventArgs $event)
    {
        echo 'Hello World';
    }
}
