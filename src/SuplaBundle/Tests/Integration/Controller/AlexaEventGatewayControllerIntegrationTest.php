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

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\AlexaEventGatewayCredentials;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlexaEventGatewayControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;

    /** @var Client $client */
    private $client;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $this->client = $this->createAuthenticatedClient($this->user);
        $this->getEntityManager()->flush();
    }

    private function getCredentials() : AlexaEventGatewayCredentials {
        return $this->getDoctrine()
            ->getRepository(AlexaEventGatewayCredentials::class)->findForUser($this->user);
    }

    public function testNonexistingCredentials() {
        $this->expectException(NotFoundHttpException::class);
        $this->getCredentials();
    }

    private function assertUpdatingCredentials(array $params) {
        $this->client->apiRequestV23('PUT', '/api/alexa-event-gateway-credentials', $params);
        $response = $this->client->getResponse();
        $this->assertStatusCode('2xx', $response);

        $credentials = $this->getCredentials();
        $this->assertEquals($params['aeg_access_token'], $credentials->getAccessToken());
        $this->assertEquals($params['aeg_refresh_token'], $credentials->getRefreshToken());

        $now = new \DateTime();
        $expiresIn = intval(@$params['aeg_expires_in']);

        if ($expiresIn == 0) {
            $interval = new \DateInterval('P20Y');
            $now->add($interval);
        }

        $diff = $credentials->getExpiresAt()->getTimestamp() - $now->getTimestamp();

        $this->assertGreaterThanOrEqual($expiresIn-2, $diff);
        $this->assertLessThanOrEqual($expiresIn+2, $diff);

        $this->getEntityManager()->detach($credentials);
    }

    public function testUpdatingCredentials() {
        $params = ['aeg_access_token' => 'abcd',
            'aeg_expires_in' => 3600,
            'aeg_refresh_token' => 'xyz'];

        $this->assertUpdatingCredentials($params);

        $params = ['aeg_access_token' => 'efgh',
            'aeg_expires_in' => 600,
            'aeg_refresh_token' => 'vbn'];

        $this->assertUpdatingCredentials($params);

        $params = ['aeg_access_token' => 'ujn',
            'aeg_refresh_token' => 'mnb'];

        $this->assertUpdatingCredentials($params);
    }

    private function assertUpdatingWithIncompleteData(array $params) {
        $this->client->apiRequestV23('PUT', '/api/alexa-event-gateway-credentials', $params);
        $response = $this->client->getResponse();
        $this->assertStatusCode('4xx', $response);
    }

    public function testUpdatingWithIncompleteData() {
        $params = ['aeg_expires_in' => 600,
            'aeg_refresh_token' => 'vbn'];

        $this->assertUpdatingWithIncompleteData($params);

        $params = ['aeg_access_token' => 'efgh',
            'aeg_expires_in' => 600];

        $this->assertUpdatingWithIncompleteData($params);
    }
}
