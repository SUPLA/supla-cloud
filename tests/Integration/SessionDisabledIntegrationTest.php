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

namespace App\Tests\Integration;

use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class SessionDisabledIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    protected function initializeDatabaseForTests() {
        $this->createConfirmedUser();
    }

    public function testPublicAndWebappApiRequestsDoNotCreateSessionCookies() {
        $client = $this->createHttpsClient();
        $client->request('GET', '/api/server-info');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertFalse($client->getResponse()->headers->has('Set-Cookie'));

        $client->request('POST', '/api/webapp-tokens', [
            'username' => 'supler@supla.org',
            'password' => 'supla123',
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertFalse($response->headers->has('Set-Cookie'));
        $token = json_decode($response->getContent(), true)['access_token'];

        $authenticatedClient = $this->createHttpsClient();
        $authenticatedClient->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $token);
        $authenticatedClient->request('GET', '/api/server-info');
        $response = $authenticatedClient->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertFalse($response->headers->has('Set-Cookie'));

        $authenticatedClient->request('POST', '/api/logout');
        $response = $authenticatedClient->getResponse();
        $this->assertStatusCode(204, $response);
        $this->assertFalse($response->headers->has('Set-Cookie'));
    }
}
