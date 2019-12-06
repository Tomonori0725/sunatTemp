<?php

namespace Plugin\ConfirmRegister;

use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class Event implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopping/confirm.twig' => 'index'
        ];
    }

    /**
     * @param TemplateEvent $event
     */
    public function index(TemplateEvent $event)
    {
        $event->addSnippet('@ConfirmRegister/default/confirm_register_shopping_item.twig');
    }

}
