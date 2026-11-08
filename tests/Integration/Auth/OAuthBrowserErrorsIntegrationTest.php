<?php

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\IntegrationTestCase;
use App\Supla\SuplaAutodiscoverMock;
use App\Model\LocalSuplaCloud;
use App\Tests\Integration\Traits\SuplaApiHelper;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

/** @small */
class OAuthBrowserErrorsIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @dataProvider invalidAuthorizationRequests */
    public function testRejectsInvalidAuthorizationBeforeLogin(string $kind, bool $publicApplication): void {
        $clientId = '100_public';
        $redirectUri = 'http://suplascripts.local/auth';
        SuplaAutodiscoverMock::$publicClients[$clientId] = [
            'name' => 'Test application',
            'redirectUris' => [$redirectUri],
        ];
        if (!$publicApplication) {
            $manager = self::getContainer()->get(ClientManagerInterface::class);
            $client = $manager->createClient();
            $client->setRedirectUris([$redirectUri]);
            $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]);
            $manager->updateClient($client);
            $clientId = $client->getPublicId();
        }
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'account_r',
            'state' => 'original-state',
        ];
        if ($kind === 'invalid_client') {
            $params['client_id'] = 'unknown_client';
            $params['scope'] = 'unknown_scope';
        } elseif ($kind === 'redirect_uri_mismatch') {
            $params['redirect_uri'] = 'https://unregistered.example/callback';
            $params['scope'] = 'unknown_scope';
        } elseif ($kind === 'invalid_scope') {
            $params['scope'] = 'unknown_scope';
        }
        if ($kind === 'mapped_scope' || $kind === 'mapped_valid') {
            $localAddress = self::getContainer()->get(LocalSuplaCloud::class)->getAddress();
            SuplaAutodiscoverMock::$clientMapping[$localAddress][$clientId] = ['clientId' => '100_mapped'];
            $params['client_id'] = '100_mapped';
            if ($kind === 'mapped_scope') {
                $params['scope'] = 'unknown_scope';
            }
        }
        $browser = $this->createHttpsClient(false);
        $browser->request('GET', '/oauth/v2/auth?' . http_build_query($params));
        $response = $browser->getResponse();
        if ($kind === 'valid' || $kind === 'mapped_valid') {
            $this->assertSame(302, $response->getStatusCode());
            $this->assertStringEndsWith('/oauth-authorize', $response->headers->get('Location'));
        } elseif ($kind === 'invalid_scope' || $kind === 'mapped_scope') {
            $this->assertSame(302, $response->getStatusCode());
            $location = $response->headers->get('Location');
            $this->assertStringStartsWith($redirectUri . '?', $location);
            parse_str(parse_url($location, PHP_URL_QUERY), $query);
            $this->assertSame('invalid_scope', $query['error']);
            $this->assertSame('original-state', $query['state']);
        } else {
            $this->assertSame(400, $response->getStatusCode());
            $this->assertFalse($response->isRedirect());
            $this->assertStringContainsString($kind, $response->getContent());
            $this->assertStringContainsString('window.errorDetails', $response->getContent());
            $this->assertStringNotContainsString('window.oauthAuthorizeRequest', $response->getContent());
        }
    }

    public static function invalidAuthorizationRequests(): array {
        return [
            'AD scope' => ['invalid_scope', true],
            'AD client' => ['invalid_client', true],
            'AD callback' => ['redirect_uri_mismatch', true],
            'local scope' => ['invalid_scope', false],
            'local client' => ['invalid_client', false],
            'local callback' => ['redirect_uri_mismatch', false],
            'AD valid' => ['valid', true],
            'local valid' => ['valid', false],
            'mapped scope' => ['mapped_scope', true],
            'mapped valid' => ['mapped_valid', true],
        ];
    }
}
