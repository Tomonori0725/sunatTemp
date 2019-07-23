<?php

namespace Plugin\RegisterWhileShopping\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RegisterWhileShoppingExtension.
 */
class EventListener implements EventSubscriberInterface
{

    public function onResponse(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (!is_array($controller)) {
            return;
        }
        $controller = $controller[0];
        
        if (method_exists($controller, 'before')) {
            $controller->before($request);
        }

        //var_dump($request);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onResponse',
        ];
    }
}
