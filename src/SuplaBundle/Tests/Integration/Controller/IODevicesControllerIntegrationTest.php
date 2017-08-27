<?php

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

class IODevicesControllerIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use UserFixtures;

    /** @var User */
    private $user;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->createDeviceSonoff($location);
        $this->getEntityManager()->refresh($this->user);
    }

    public function testGetDevicesList() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/iodev');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertCount(1, $body);
        $deviceId = $this->user->getIODevices()->first()->getId();
        $this->assertEquals($deviceId, $body[0]['id']);
    }

    public function testGetDeviceDetails() {
        $client = self::createClient([], [
            'PHP_AUTH_USER' => $this->user->getEmail(),
            'PHP_AUTH_PW' => 'supla123',
        ]);
        $client->followRedirects(true);
        $deviceId = $this->user->getIODevices()->first()->getId();
        $crawler = $client->request('GET', "/iodev/$deviceId/view");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertCount(2, $crawler->filter('.channels .element-item'));
    }
}
