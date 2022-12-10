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

use SuplaBundle\Entity\Main\AmazonAlexa;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @small */
class AmazonAlexaControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    /** @var Client $client */
    private $client;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->client = $this->createAuthenticatedClient($this->user);
    }

    private function getAmazonAlexa(): AmazonAlexa {
        return $this->getDoctrine()
            ->getRepository(AmazonAlexa::class)->findForUser($this->user);
    }

    public function testNonexistingCredentials() {
        $this->expectException(NotFoundHttpException::class);
        $this->getAmazonAlexa();
    }

    private function assertUpdatingCredentials(array $params) {
        $this->client->apiRequestV23('PUT', '/api/integrations/amazon-alexa', $params);
        $response = $this->client->getResponse();
        $this->assertStatusCode('2xx', $response);

        $amazonAlexa = $this->getAmazonAlexa();
        $this->assertEquals($params['aeg_access_token'], $amazonAlexa->getAccessToken());
        $this->assertEquals($params['aeg_refresh_token'], $amazonAlexa->getRefreshToken());
        $this->assertEquals($params['aeg_region'], $amazonAlexa->getRegion());

        $now = new \DateTime();
        $expiresIn = intval(@$params['aeg_expires_in']);

        if ($expiresIn == 0) {
            $interval = new \DateInterval('P20Y');
            $now->add($interval);
        }

        $diff = $amazonAlexa->getExpiresAt()->getTimestamp() - $now->getTimestamp();

        $this->assertGreaterThanOrEqual($expiresIn - 2, $diff);
        $this->assertLessThanOrEqual($expiresIn + 2, $diff);

        $this->getEntityManager()->detach($amazonAlexa);
    }

    public function testUpdatingCredentials() {
        $params = ['aeg_access_token' => 'abcd',
            'aeg_expires_in' => 3600,
            'aeg_refresh_token' => 'xyz',
            'aeg_region' => 'eu'];

        $this->assertUpdatingCredentials($params);

        $params = ['aeg_access_token' => 'efgh',
            'aeg_expires_in' => 600,
            'aeg_refresh_token' => 'vbn',
            'aeg_region' => ''];

        $this->assertUpdatingCredentials($params);

        $params = ['aeg_access_token' => 'ujn',
            'aeg_refresh_token' => 'mnb',
            'aeg_region' => 'fe'];

        $this->assertUpdatingCredentials($params);
    }

    private function assertUpdatingWithIncompleteData(array $params) {
        $this->client->apiRequestV23('PUT', '/api/amazon-alexa', $params);
        $response = $this->client->getResponse();
        $this->assertStatusCode('4xx', $response);
    }

    public function testUpdatingWithIncompleteData() {
        $params = ['aeg_expires_in' => 600,
            'aeg_refresh_token' => 'vbn',
            'aeg_region' => 'eu'];

        $this->assertUpdatingWithIncompleteData($params);

        $params = ['aeg_access_token' => 'efgh',
            'aeg_expires_in' => 600,
            'aeg_region' => 'eu',];

        $this->assertUpdatingWithIncompleteData($params);
    }
}
