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

use SuplaBundle\Entity\Main\GoogleHome;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class GoogleHomeControllerIntegrationTest extends IntegrationTestCase {
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

    private function getGoogleHome(): GoogleHome {
        return $this->getDoctrine()
            ->getRepository(GoogleHome::class)->findForUser($this->user);
    }

    private function assertUpdatingCredentials(string $accessToken) {
        $this->client->apiRequestV23('PUT', '/api/integrations/google-home', ['gh_access_token' => $accessToken]);
        $response = $this->client->getResponse();
        $this->assertStatusCode('2xx', $response);

        $gh = $this->getGoogleHome();
        $this->assertEquals($accessToken ?? '', $gh->getAccessToken() ?? '');

        $this->getEntityManager()->detach($gh);
    }

    public function testSetAccessToken() {
        $this->assertUpdatingCredentials('abcd');
    }

    public function testClearAccessToken() {
        $this->assertUpdatingCredentials('');
    }
}
