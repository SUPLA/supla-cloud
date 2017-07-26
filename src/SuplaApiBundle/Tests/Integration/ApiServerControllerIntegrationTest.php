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
        $token = $this->authenticateApiUser();
        $client = self::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/api/server-info', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token->access_token]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals($this->container->getParameter('supla_server'), $content->data->address);
        $this->assertNotEmpty($content->data->time);
    }

    private function authenticateApiUser() {
        $client = self::createClient();
        $client->request('POST', '/oauth/v2/token', [
            'client_id' => $this->apiClient->getPublicId(),
            'client_secret' => $this->apiClient->getSecret(),
            'grant_type' => 'password',
            'username' => $this->apiUser->getUsername(),
            'password' => '123',
        ]);
        $response = $client->getResponse();
        $token = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        return $token;
    }
}
