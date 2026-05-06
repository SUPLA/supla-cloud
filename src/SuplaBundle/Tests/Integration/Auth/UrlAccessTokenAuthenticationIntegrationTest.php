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

namespace SuplaBundle\Tests\Integration\Auth;

use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Entity\Main\UserIcon;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\OAuthHelper;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/**
 * Verifies that bearer tokens can be transported via the Authorization header
 * and via the `access_token` query string parameter (RFC 6750 §2.1 and §2.3).
 * For URL transport we additionally enforce that only ROLE_CHANNELS_FILES is
 * granted, regardless of the token's full scope, because URL tokens leak into
 * server logs, browser history and Referer headers.
 *
 * @small
 */
class UrlAccessTokenAuthenticationIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use OAuthHelper;

    private const IMAGE_DATA = 'IMAGE-CONTENT';

    /** @var User */
    private $user;
    /** @var ApiClient */
    private $apiClient;
    /** @var int */
    private $iconId;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->apiClient = $this->createApiClient();

        $icon = new UserIcon($this->user, ChannelFunction::THERMOMETER());
        $icon->setImage(self::IMAGE_DATA, 1);
        $this->getEntityManager()->persist($icon);
        $this->getEntityManager()->flush();
        $this->iconId = $icon->getId();

        $this->createAccessToken($this->apiClient, $this->user, 'channels_files', 'TKN_FILES_HEADER');
        $this->createAccessToken($this->apiClient, $this->user, 'channels_files', 'TKN_FILES_URL');
        $this->createAccessToken($this->apiClient, $this->user, 'channels_r', 'TKN_R_HEADER');
        $this->createAccessToken($this->apiClient, $this->user, 'channels_r', 'TKN_R_URL');
        $this->createAccessToken($this->apiClient, $this->user, 'channels_r channels_files', 'TKN_R_FILES_URL');
    }

    private function newClient(array $server = []): TestClient {
        self::ensureKernelShutdown();
        return self::createClient(['debug' => false], array_merge(['HTTPS' => true], $server));
    }

    private function iconImageUrl(int $imageIndex = 0, ?string $accessToken = null): string {
        $url = '/api/user-icons/' . $this->iconId . '/' . $imageIndex;
        if ($accessToken !== null) {
            $url .= '?access_token=' . $accessToken;
        }
        return $url;
    }

    public function testHeaderBearerWithChannelsFilesScopeReturnsImage() {
        $client = $this->newClient(['HTTP_AUTHORIZATION' => 'Bearer TKN_FILES_HEADER']);
        $client->request('GET', $this->iconImageUrl());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertEquals('image/png', $response->headers->get('Content-Type'));
        $this->assertSame(self::IMAGE_DATA, $response->getContent());
    }

    public function testUrlAccessTokenWithChannelsFilesScopeReturnsImage() {
        // The exact regression covered by this test: GET /api/user-icons/{id}/{n}?access_token=…
        // used to fall through to a 401 because the custom authenticator only read the header.
        $client = $this->newClient();
        $client->request('GET', $this->iconImageUrl(0, 'TKN_FILES_URL'));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertSame(self::IMAGE_DATA, $response->getContent());
    }

    public function testUrlAccessTokenWithoutChannelsFilesScopeIsRejected() {
        $client = $this->newClient();
        $client->request('GET', $this->iconImageUrl(0, 'TKN_R_URL'));
        $this->assertStatusCode(401, $client->getResponse());
    }

    public function testUrlAccessTokenCannotReachNonFilesEndpointEvenIfTokenHasReadScope() {
        // Token scope contains channels_r AND channels_files, but URL transport
        // grants only ROLE_CHANNELS_FILES — /api/channels needs ROLE_CHANNELS_R.
        $client = $this->newClient();
        $client->request('GET', '/api/channels?access_token=TKN_R_FILES_URL');
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testHeaderBearerWithReadScopeStillReachesNonFilesEndpoint() {
        // Sanity check that the URL-transport restriction does not regress
        // the standard Authorization header flow.
        $client = $this->newClient(['HTTP_AUTHORIZATION' => 'Bearer TKN_R_HEADER']);
        $client->request('GET', '/api/channels');
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testRequestWithoutAnyTokenIsRejected() {
        $client = $this->newClient();
        $client->request('GET', $this->iconImageUrl());
        $this->assertStatusCode(401, $client->getResponse());
    }

    public function testUnknownAccessTokenInUrlIsRejected() {
        $client = $this->newClient();
        $client->request('GET', $this->iconImageUrl(0, 'definitely-not-a-real-token'));
        $this->assertStatusCode(401, $client->getResponse());
    }
}
