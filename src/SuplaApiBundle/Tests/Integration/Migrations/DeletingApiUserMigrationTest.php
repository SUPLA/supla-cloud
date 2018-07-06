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

namespace SuplaApiBundle\Tests\Integration\Migrations;

use SuplaApiBundle\Model\ApiVersions;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use Symfony\Component\BrowserKit\Client;

/**
 * @see Version20180518131234
 */
class DeletingApiUserMigrationTest extends DatabaseMigrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    /** @before */
    public function prepare() {
        $this->loadDumpV22();
        $this->migrate();
    }

    public function testCompatFieldsAreSet() {
        $user = $this->getEntityManager()->find(User::class, 1);
        $user->setOAuthOldApiCompatEnabled();
        $this->assertEquals('api_1', $user->getUsername());
        $this->assertEquals('$2y$04$0ydWylMOTNDnSA/GNhl.nulSldoCVbKCo4AyT3wrXnZwncnA2iqaa', $user->getPassword());
    }

    public function testCanStillAuthenticateInApiWithOldCredentials() {
        $client = self::createClient([], ['HTTPS' => true]);
        $client->request('POST', '/oauth/v2/token', [
            'client_id' => '1_69uz20cdx6w4wg8sk0kow4ckwgsgco08s0gccwgwc4sw4kck80',
            'client_secret' => '661l0f9ql7wokkk84wg888gww80w8gko4cs0gcc4gs44gooowg',
            'grant_type' => 'password',
            'username' => 'api_1',
            'password' => 'pass',
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        return json_decode($response->getContent());
    }

    public function testCanStillMakeAuthorizedRequestWithTokenReceivedForOldCredentials() {
        $token = $this->testCanStillAuthenticateInApiWithOldCredentials();
        /** @var Client $client */
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token->access_token, 'HTTPS' => true]);
        $client->followRedirects();
        $client->request('GET', '/api/server-info', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertTrue($content->authenticated);
    }
}
