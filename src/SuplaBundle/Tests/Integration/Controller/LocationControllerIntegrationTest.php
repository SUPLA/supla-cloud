<?php

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

class LocationControllerIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use UserFixtures;

    /** @var User */
    private $user;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $this->createLocation($this->user);
    }

    public function testGetLocationList() {
        $client = self::createClient([], [
            'PHP_AUTH_USER' => $this->user->getEmail(),
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
