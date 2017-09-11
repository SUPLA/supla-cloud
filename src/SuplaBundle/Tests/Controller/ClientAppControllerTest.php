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

namespace SuplaBundle\Tests\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Controller\ClientAppController;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Entity\User;
use SuplaBundle\Supla\SuplaServer;
use Symfony\Component\HttpFoundation\Request;

class ClientAppControllerTest extends \PHPUnit_Framework_TestCase {
    /** @var  EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;
    /** @var ClientAppController|\PHPUnit_Framework_MockObject_MockObject */
    private $controller;
    /** @var SuplaServer|\PHPUnit_Framework_MockObject_MockObject */
    private $suplaServer;

    protected function setUp() {
        $this->controller = $this->getMockBuilder(ClientAppController::class)
            ->setMethods(['getUser', 'expectsJsonResponse', 'jsonResponse'])
            ->getMock();
        $this->suplaServer = $this->createMock(SuplaServer::class);
        $this->controller->setSuplaServer($this->suplaServer);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->controller->setEntityManager($this->entityManager);
        $this->entityManager->method('transactional')->willReturnCallback(function (callable $closure) {
            return $closure($this->entityManager);
        });
    }

    public function testDeletingClientApp() {
        $clientApp = new ClientApp();
        $this->entityManager->expects($this->once())->method('remove')->with($clientApp);
        $response = $this->controller->deleteAction($clientApp);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testGettingOnlyConnectedClientApps() {
        $user = $this->createMock(User::class);
        $this->controller->method('getUser')->willReturn($user);
        $this->controller->method('expectsJsonResponse')->willReturn(true);
        $this->controller->method('jsonResponse')->willReturnArgument(0);
        $clientApps = [new ClientApp(), new ClientApp(), new ClientApp()];
        $user->method('getClientApps')->willReturn(new ArrayCollection($clientApps));
        $request = $this->createMock(Request::class);
        $request->method('get')->with('onlyConnected')->willReturn(true);
        $this->suplaServer->method('getOnlyConnectedClientApps')->willReturn([$clientApps[0], $clientApps[2]]);
        $response = $this->controller->clientAppsListAction($request);
        $this->assertCount(2, $response);
        $this->assertSame($clientApps[0], $response[0]);
        $this->assertSame($clientApps[2], $response[1]);
    }

    public function testUpdatingClientAppName() {
        $request = new Request([], ['name' => 'New Name']);
        $clientApp = new ClientApp();
        $this->suplaServer->expects($this->never())->method('clientReconnect');
        $this->controller->editAction($clientApp, $request);
        $this->assertEquals('New Name', $clientApp->getName());
    }

    public function testReconnectingClientWhenEnabling() {
        $request = new Request([], ['enabled' => true]);
        $clientApp = new ClientApp();
        $this->suplaServer->expects($this->once())->method('clientReconnect');
        $this->controller->editAction($clientApp, $request);
        $this->assertTrue($clientApp->getEnabled());
    }

    public function testNotReconnectingClientIfEnabledStateDidNotChange() {
        $request = new Request([], ['enabled' => true]);
        $clientApp = new ClientApp();
        $clientApp->setEnabled(true);
        $this->suplaServer->expects($this->never())->method('clientReconnect');
        $this->controller->editAction($clientApp, $request);
        $this->assertTrue($clientApp->getEnabled());
    }
}
