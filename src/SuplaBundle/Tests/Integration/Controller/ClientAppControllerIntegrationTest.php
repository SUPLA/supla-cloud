<?php

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Tests\AnyFieldSetter;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

class ClientAppControllerIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use UserFixtures;

    /** @var ClientApp */
    private $clientApp;

    protected function setUp() {
        $user = $this->createConfirmedUser();
        $this->clientApp = new ClientApp();
        AnyFieldSetter::set($this->clientApp, ['guid' => 'abcd', 'regDate' => new \DateTime(), 'lastAccessDate' => new \DateTime(),
            'softwareVersion' => '1.4', 'protocolVersion' => 22, 'user' => $user, 'name' => 'iPhone 6']);
        $this->getEntityManager()->persist($this->clientApp);
        $this->getEntityManager()->flush();
    }

    public function testGetClientAppList() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/client-apps');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals('iPhone 6', $content[0]['name']);
    }

    public function testDeletingClientApp() {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/client-apps/' . $this->clientApp->getId());
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $existingApps = $this->container->get('doctrine')->getRepository('SuplaBundle:ClientApp')->findAll();
        $this->assertCount(0, $existingApps);
    }
}
