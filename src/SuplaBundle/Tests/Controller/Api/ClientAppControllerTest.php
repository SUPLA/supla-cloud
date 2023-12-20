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

namespace SuplaBundle\Tests\Controller\Api;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Controller\Api\ClientAppController;
use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Supla\SuplaServer;
use Symfony\Component\HttpFoundation\Request;

class ClientAppControllerTest extends TestCase {
    /** @var  EntityManagerInterface|MockObject */
    private $entityManager;
    /** @var ClientAppController|MockObject */
    private $controller;
    /** @var SuplaServer|MockObject */
    private $suplaServer;

    protected function setUp(): void {
        $this->controller = $this->getMockBuilder(ClientAppController::class)
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->suplaServer = $this->createMock(SuplaServer::class);
        $this->controller->setSuplaServer($this->suplaServer);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->controller->setEntityManager($this->entityManager);
        $this->entityManager->method('wrapInTransaction')->willReturnCallback(function (callable $closure) {
            return $closure($this->entityManager);
        });
    }

    public function testDeletingClientApp() {
        $clientApp = new ClientApp();
        $this->entityManager->expects($this->once())->method('remove')->with($clientApp);
        $response = $this->controller->deleteClientAppAction($clientApp);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testClientAppsWithConnectedGroup() {
        $user = $this->createMock(User::class);
        $this->controller->method('getUser')->willReturn($user);
        $clientApps = [new ClientApp(), new ClientApp(), new ClientApp()];
        $user->method('getClientApps')->willReturn(new ArrayCollection($clientApps));
        $request = $this->createMock(Request::class);
        $request->method('get')->willReturnOnConsecutiveCalls('connected', 'v2.3.0');
        $this->suplaServer->method('isClientAppConnected')->with($clientApps[0])->willReturn(true);
        $this->suplaServer->method('isClientAppConnected')->with($clientApps[1])->willReturn(false);
        $this->suplaServer->method('isClientAppConnected')->with($clientApps[2])->willReturn(true);
        /** @var View $response */
        $response = $this->controller->getClientAppsAction($request);
        $this->assertContains('basic', $response->getContext()->getGroups());
        $this->assertContains('connected', $response->getContext()->getGroups());
        $this->assertCount(3, $response->getData());
        $this->assertContains($clientApps[0], $response->getData());
        $this->assertContains($clientApps[1], $response->getData());
        $this->assertContains($clientApps[2], $response->getData());
    }

    public function testUpdatingClientAppCaption() {
        $request = new Request([], ['caption' => 'New Caption']);
        $clientApp = new ClientApp();
        $this->suplaServer->expects($this->never())->method('clientReconnect');
        $this->controller->putClientAppAction($request, $clientApp);
        $this->assertEquals('New Caption', $clientApp->getCaption());
    }

    public function testReconnectingClientWhenEnabling() {
        $request = new Request([], ['enabled' => true]);
        $clientApp = new ClientApp();
        $this->suplaServer->expects($this->once())->method('clientReconnect');
        $this->controller->putClientAppAction($request, $clientApp);
        $this->assertTrue($clientApp->getEnabled());
    }

    public function testNotReconnectingClientIfEnabledStateDidNotChange() {
        $request = new Request([], ['enabled' => true]);
        $clientApp = new ClientApp();
        $clientApp->setEnabled(true);
        $this->suplaServer->expects($this->never())->method('clientReconnect');
        $this->controller->putClientAppAction($request, $clientApp);
        $this->assertTrue($clientApp->getEnabled());
    }
}
