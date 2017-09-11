<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

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
