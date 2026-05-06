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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\OAuthHelper;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/**
 * Pins down the contract for non-expiring access tokens after the migration
 * from FOSOAuthServerBundle to klapaudius/oauth-server-bundle. Legacy code
 * stores these as `expires_at IS NULL` and that semantic stays — klapaudius
 * keeps the column nullable in the Doctrine mapping, even though the PHP
 * property is a typed `int`. The fix lives in the entity (defensive isset()
 * in hasExpired/getExpiresAt) so that NULL hydrates without crashing and is
 * treated as "never expires".
 *
 * Cleanup behaviour relies on SQL semantics: `WHERE expires_at < :time` does
 * not match NULL rows (NULL comparison yields UNKNOWN), so the periodic
 * `supla:clean:obsolete-oauth-tokens` command leaves them alone with no need
 * to override deleteExpired().
 *
 * @small
 */
class AccessTokenLifetimeIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use OAuthHelper;

    /** @var User */
    private $user;
    /** @var ApiClient */
    private $apiClient;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->apiClient = $this->createApiClient();
    }

    private function setExpiresAtRaw(string $tokenCode, ?int $expiresAt): void {
        $this->getEntityManager()->getConnection()->executeStatement(
            'UPDATE supla_oauth_access_tokens SET expires_at = :exp WHERE token = :tok',
            ['exp' => $expiresAt, 'tok' => $tokenCode]
        );
    }

    private function findToken(string $tokenCode): ?AccessToken {
        $this->getEntityManager()->clear();
        return $this->getEntityManager()->getRepository(AccessToken::class)
            ->findOneBy(['token' => $tokenCode]);
    }

    public function testNonExpiringTokenAuthenticatesNormally() {
        $this->createAccessToken($this->apiClient, $this->user, 'account_r', 'TKN_FOREVER');
        $this->setExpiresAtRaw('TKN_FOREVER', null);

        self::ensureKernelShutdown();
        $client = self::createClient(
            ['debug' => false],
            ['HTTP_AUTHORIZATION' => 'Bearer TKN_FOREVER', 'HTTPS' => true]
        );
        $client->request('GET', '/api/users/current');
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testLegacyNullExpiryTokenSurvivesCleanupCommand() {
        // Legacy tokens (created before klapaudius migration) have NULL.
        $this->createAccessToken($this->apiClient, $this->user, 'account_r', 'TKN_LEGACY_NULL');
        $this->setExpiresAtRaw('TKN_LEGACY_NULL', null);

        $this->executeCommand('supla:clean:obsolete-oauth-tokens');

        $this->assertNotNull(
            $this->findToken('TKN_LEGACY_NULL'),
            'Legacy non-expiring token (expires_at IS NULL) was wiped by supla:clean:obsolete-oauth-tokens.'
        );
    }

    public function testFreshPersonalTokenWithZeroExpirySurvivesCleanupCommand() {
        // Tokens freshly created via AccessToken's constructor have expires_at = 0
        // (typed `int $expiresAt` cannot be uninitialised at persist time).
        // Without the deleteExpired() override, `0 < time()` would wipe them.
        $this->createAccessToken($this->apiClient, $this->user, 'account_r', 'TKN_PERSONAL_ZERO');
        $this->setExpiresAtRaw('TKN_PERSONAL_ZERO', 0);

        $this->executeCommand('supla:clean:obsolete-oauth-tokens');

        $this->assertNotNull(
            $this->findToken('TKN_PERSONAL_ZERO'),
            'Personal token (expires_at = 0) was wiped by supla:clean:obsolete-oauth-tokens.'
        );
    }

    public function testExpiredTokenIsRemovedByCleanupCommand() {
        $this->createAccessToken($this->apiClient, $this->user, 'account_r', 'TKN_EXPIRED');
        $this->setExpiresAtRaw('TKN_EXPIRED', strtotime('-1 hour'));

        $this->executeCommand('supla:clean:obsolete-oauth-tokens');

        $this->assertNull(
            $this->findToken('TKN_EXPIRED'),
            'Expired token (expires_at in the past) was not removed by supla:clean:obsolete-oauth-tokens.'
        );
    }

    public function testFutureTokenSurvivesCleanupCommand() {
        $this->createAccessToken($this->apiClient, $this->user, 'account_r', 'TKN_FUTURE');
        $this->setExpiresAtRaw('TKN_FUTURE', strtotime('+1 hour'));

        $this->executeCommand('supla:clean:obsolete-oauth-tokens');

        $this->assertNotNull(
            $this->findToken('TKN_FUTURE'),
            'Token expiring in the future was wiped by supla:clean:obsolete-oauth-tokens.'
        );
    }

    public function testHasExpiredReturnsFalseForZeroExpiry() {
        $token = new AccessToken();
        EntityUtils::setField($token, 'expiresAt', 0);
        $this->assertFalse($token->hasExpired(), 'Token with expires_at = 0 must not be considered expired.');
    }

    public function testHasExpiredReturnsFalseForUninitializedExpiry() {
        // Doctrine bypasses the constructor when hydrating, so a NULL row leaves
        // the typed `int $expiresAt` uninitialized. hasExpired() must handle that.
        $token = (new \ReflectionClass(AccessToken::class))->newInstanceWithoutConstructor();
        $this->assertFalse($token->hasExpired());
    }

    public function testHasExpiredReturnsTrueForPastExpiry() {
        $token = new AccessToken();
        EntityUtils::setField($token, 'expiresAt', strtotime('-1 minute'));
        $this->assertTrue($token->hasExpired());
    }

    public function testHasExpiredReturnsFalseForFutureExpiry() {
        $token = new AccessToken();
        EntityUtils::setField($token, 'expiresAt', strtotime('+1 hour'));
        $this->assertFalse($token->hasExpired());
    }
}
