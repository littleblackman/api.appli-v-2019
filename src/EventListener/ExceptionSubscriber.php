<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * ExceptionSubscriber class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $response = new JsonResponse(array(
            'error' => method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 'No ErrorCode',
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
        ));
        $response->headers->set('Content-Type', 'application/problem+json');

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }
}
