<?php

namespace App\Tests\EventListener;

use App\EventListener\UnavailableInMaintenanceRequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UnavailableInMaintenanceRequestListenerTest extends TestCase {
    public function testBlocksMethodAttributeInMaintenanceMode(): void {
        $event = $this->controllerEvent([new MaintenanceMethodController(), 'blocked']);
        (new UnavailableInMaintenanceRequestListener(true))->onKernelController($event);

        $response = ($event->getController())();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame('maintenance', json_decode($response->getContent(), true)['error']);
    }

    public function testBlocksClassAttributeInMaintenanceMode(): void {
        $event = $this->controllerEvent([new MaintenanceClassController(), 'action']);
        (new UnavailableInMaintenanceRequestListener(true))->onKernelController($event);

        $this->assertSame(503, ($event->getController())()->getStatusCode());
    }

    public function testAllowsUnmarkedMethodInMaintenanceMode(): void {
        $controller = [new MaintenanceMethodController(), 'allowed'];
        $event = $this->controllerEvent($controller);
        (new UnavailableInMaintenanceRequestListener(true))->onKernelController($event);

        $this->assertSame($controller, $event->getController());
    }

    public function testAllowsMarkedMethodWhenMaintenanceIsDisabled(): void {
        $controller = [new MaintenanceMethodController(), 'blocked'];
        $event = $this->controllerEvent($controller);
        (new UnavailableInMaintenanceRequestListener(false))->onKernelController($event);

        $this->assertSame($controller, $event->getController());
    }

    private function controllerEvent(callable $controller): ControllerEvent {
        return new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            $controller,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
