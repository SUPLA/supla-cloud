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

namespace SuplaApiBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Tests\AnyFieldSetter;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

class ApiClientAppControllerIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use UserFixtures;

    /** @var ClientApp */
    private $clientApp;

    protected function setUp() {
        $user = $this->createConfirmedUser();
        $this->clientApp = new ClientApp();
        $this->clientApp->setEnabled(true);
        AnyFieldSetter::set($this->clientApp, ['guid' => 'abcd', 'regDate' => new \DateTime(), 'lastAccessDate' => new \DateTime(),
            'softwareVersion' => '1.4', 'protocolVersion' => 22, 'user' => $user, 'name' => 'iPhone 6']);
        $this->getEntityManager()->persist($this->clientApp);
        $this->getEntityManager()->flush();
    }

    public function testGetClientAppList() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/web-api/client-apps');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals('iPhone 6', $content[0]['name']);
    }

    public function testGetClientAppListWithConnectedStatus() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/web-api/client-apps?include=connected');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals('iPhone 6', $content[0]['name']);
        $this->assertTrue($content[0]['connected']);
    }

    public function testDeletingClientApp() {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/web-api/client-apps/' . $this->clientApp->getId());
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $existingApps = $this->container->get('doctrine')->getRepository('SuplaBundle:ClientApp')->findAll();
        $this->assertCount(0, $existingApps);
    }
}
