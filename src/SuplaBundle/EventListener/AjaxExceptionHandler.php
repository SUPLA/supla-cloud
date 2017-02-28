<?php
namespace SuplaBundle\EventListener;

use Assert\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AjaxExceptionHandler implements EventSubscriberInterface
{
    private $isDebug;

    public function __construct($isDebug)
    {
        $this->isDebug = $isDebug;
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        if (in_array('application/json', $event->getRequest()->getAcceptableContentTypes())) {
            $errorResponse = $this->createErrorResponse($event->getException());
            $event->setResponse($errorResponse);
        }
    }

    public function createErrorResponse(\Exception $e)
    {
        if ($e instanceof InvalidArgumentException) {
            return new JsonResponse([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        } else {
            return new JsonResponse([
                'status' => 500,
                'message' => $this->isDebug ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }
}

