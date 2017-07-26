<?php

namespace SuplaApiBundle\Tests\Integration;

use SuplaApiBundle\Entity\Client;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

class ApiServerControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var User */
    private $user;

    protected function setUp() {
        $this->user = $this->createConfirmedUserWithApiAccess();
    }

    public function testGettingServerInfoWithoutAuthentication() {
        $client = self::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/api/server-info');
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testGettingServerInfo() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->request('GET', '/api/server-info');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals($this->container->getParameter('supla_server'), $content->data->address);
        $this->assertNotEmpty($content->data->time);
    }
}
