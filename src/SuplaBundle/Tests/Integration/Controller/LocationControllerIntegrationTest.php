<?php

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\Assertions\ResponseAssertions;
use SuplaBundle\Tests\Integration\IntegrationTestCase;

class LocationControllerIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;

    /** @var User */
    private $user;

    protected function setUp() {
        $userManager = $this->container->get('user_manager');
        $this->user = new User();
        $this->user->setEmail('supler@supla.org');
        $userManager->create($this->user);
        $userManager->setPassword('supla123', $this->user, true);
        $userManager->confirm($this->user->getToken());
        $this->container->get('location_manager')->createLocation($this->user);
    }

    public function testGetLocationList() {
        $client = self::createClient([], [
            'PHP_AUTH_USER' => 'supler@supla.org',
            'PHP_AUTH_PW' => 'supla123',
        ]);
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/loc');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertCount(1, $crawler->filter('.item.new'));
        $this->assertCount(1, $crawler->filter('.item.selected'));
        $locationId = $this->user->getLocations()->first()->getId();
        $this->assertCount(1, $crawler->filter("a[data-id=$locationId]"));
    }
}
