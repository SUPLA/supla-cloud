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
        $client = self::createClient([], [
            'PHP_AUTH_USER' => $this->user->getEmail(),
            'PHP_AUTH_PW' => 'supla123',
        ]);
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/iodev');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertCount(1, $crawler->filter('.device.enabled'));
        $deviceId = $this->user->getIODevices()->first()->getId();
        $this->assertCount(1, $crawler->filter("a[data-id=$deviceId]"));
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
