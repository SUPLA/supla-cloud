<?php

namespace App\Tests\Controller;

use App\Controller\OAuth\AuthorizeOAuthController;
use App\Model\LocalSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Supla\SuplaAutodiscover;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthorizeOAuthControllerTest extends TestCase {
    public function testTargetCloudRegistrationIsUnavailableOnNonBrokerBeforeProbe(): void {
        $autodiscover = $this->createMock(SuplaAutodiscover::class);
        $autodiscover->method('isBroker')->willReturn(false);
        $forwarder = $this->createMock(TargetSuplaCloudRequestForwarder::class);
        $forwarder->expects($this->never())->method('getRegistrationInfo');
        $controller = new AuthorizeOAuthController(
            $autodiscover,
            $this->createMock(LocalSuplaCloud::class),
            $forwarder,
            null,
            null,
        );

        $this->expectException(NotFoundHttpException::class);
        $controller->registerTargetCloudAction(new Request());
    }
}
