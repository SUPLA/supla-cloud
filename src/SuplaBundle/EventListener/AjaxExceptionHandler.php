<?php

namespace SuplaBundle\EventListener;

use Assert\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AjaxExceptionHandler implements EventSubscriberInterface {
    private $isDebug;

    public function __construct($isDebug) {
        $this->isDebug = $isDebug;
    }

    public function onException(GetResponseForExceptionEvent $event) {
        $request = $event->getRequest();
        if (!preg_match('/^(\/app_dev\.php)?\/api\//', $request->getRequestUri())
            && ($request->get('_format') == 'json' || in_array('application/json', $request->getAcceptableContentTypes()))) {
            $errorResponse = $this->createErrorResponse($event->getException());
            $event->setResponse($errorResponse);
        }
    }

    public function createErrorResponse(\Exception $e) {
        if ($e instanceof InvalidArgumentException) {
            return new JsonResponse([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        } else {
            return new JsonResponse([
                'status' => 500,
                'message' => $this->isDebug ? $e->getMessage() : 'Internal server error',
                'info' => $this->isDebug ? $e->getTraceAsString() : '',
            ], 500);
        }
    }

    public static function getSubscribedEvents() {
        return [KernelEvents::EXCEPTION => 'onException'];
    }
}
