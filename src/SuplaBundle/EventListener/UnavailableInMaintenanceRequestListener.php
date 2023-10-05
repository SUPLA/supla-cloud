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

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class UnavailableInMaintenanceRequestListener {
    /** @var Reader */
    private $reader;
    /** @var bool */
    private $maintenanceMode;

    public function __construct(Reader $annotationsReader, bool $maintenanceMode) {
        $this->reader = $annotationsReader;
        $this->maintenanceMode = $maintenanceMode;
    }

    public function onKernelController(ControllerEvent $event) {
        if (!$this->maintenanceMode) {
            return;
        }
        if (!is_array($controllers = $event->getController())) {
            return;
        }
        [$controller, $methodName] = $controllers;
        $reflectionClass = new \ReflectionClass($controller);
        $classAnnotation = $this->reader->getClassAnnotation($reflectionClass, UnavailableInMaintenance::class);
        $reflectionObject = new \ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        $methodAnnotation = $this->reader->getMethodAnnotation($reflectionMethod, UnavailableInMaintenance::class);
        if ($classAnnotation || $methodAnnotation) {
            return $event->setController(
                function () {
                    $message = 'Maintenance mode active. You cannot modify any data now.'; // i18n
                    return new JsonResponse(
                        ['error' => 'maintenance', 'message' => $message],
                        Response::HTTP_SERVICE_UNAVAILABLE
                    );
                }
            );
        }
    }
}
