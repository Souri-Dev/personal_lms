<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class NgrokHeaderListener
{
    #[AsEventListener(event: 'kernel.response')]
    public function onResponseEvent(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('ngrok-skip-browser-warning', 'true');
    }
}
