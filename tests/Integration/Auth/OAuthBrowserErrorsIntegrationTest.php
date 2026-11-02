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

    public function testValidAuthorizationRequestCanServeTheSpaWithoutChangingItsUrl(): void {
        $clientId = '100_public';
        $redirectUri = 'http://suplascripts.local/auth';
        SuplaAutodiscoverMock::$publicClients[$clientId] = [
            'name' => 'Test application',
            'redirectUris' => [$redirectUri],
        ];
        $query = 'client_id=100_public&redirect_uri=' . rawurlencode($redirectUri)
            . '&response_type=code&scope=account_r&state=a%2Bb%3D%26c';
        $path = '/oauth/v2/auth?' . $query;

        $browser = $this->createHttpsClient(false);
        $browser->request('GET', $path);
        $response = $browser->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($path, $browser->getRequest()->getRequestUri());
        $this->assertStringContainsString('<div id="vue-container"', $response->getContent());
        $this->assertStringNotContainsString('window.oauthAuthorizeRequest', $response->getContent());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->headers->has('Set-Cookie'));
    }

    public function testLegacyOAuthLoginFormsAreRetiredWithoutCreatingASession(): void {
        foreach (['/oauth-authorize', '/oauth/v2/auth_login', '/oauth/v2/broker_login'] as $path) {
            $browser = $this->createHttpsClient(false);
            $browser->request('POST', $path, ['_username' => 'user@example.test', '_password' => 'secret']);

            $response = $browser->getResponse();
            $this->assertSame(410, $response->getStatusCode(), $path);
            $this->assertFalse($response->headers->has('Set-Cookie'), $path);
        }
    }

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
        } elseif ($kind === 'callback_prefix_mismatch') {
            $params['redirect_uri'] = $redirectUri . '/unregistered';
            $params['scope'] = 'unknown_scope';
        } elseif ($kind === 'state_zero') {
            $params['response_type'] = 'token';
            $params['state'] = '0';
        } elseif ($kind === 'invalid_scope') {
            $params['scope'] = 'unknown_scope';
        } elseif ($kind === 'unsupported_response_type') {
            $params['response_type'] = 'token';
        } elseif ($kind === 'invalid_pkce') {
            $params['code_challenge'] = 'not-a-valid-challenge';
            $params['code_challenge_method'] = 'S256';
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
            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringContainsString('<div id="vue-container"', $response->getContent());
            $this->assertSame('/oauth/v2/auth?' . http_build_query($params), $browser->getRequest()->getRequestUri());
            $this->assertFalse($response->headers->has('Set-Cookie'));
        } elseif (in_array($kind, ['invalid_scope', 'mapped_scope', 'unsupported_response_type', 'invalid_pkce', 'state_zero'], true)) {
            $this->assertSame(302, $response->getStatusCode());
            $location = $response->headers->get('Location');
            $this->assertStringStartsWith($redirectUri . '?', $location);
            parse_str(parse_url($location, PHP_URL_QUERY), $query);
            $expectedError = match ($kind) {
                'unsupported_response_type', 'state_zero' => 'unsupported_response_type',
                'invalid_pkce' => 'invalid_request',
                default => 'invalid_scope',
            };
            $this->assertSame($expectedError, $query['error']);
            $this->assertSame($kind === 'state_zero' ? '0' : 'original-state', $query['state']);
        } else {
            $this->assertSame(400, $response->getStatusCode());
            $this->assertFalse($response->isRedirect());
            $expectedError = $kind === 'callback_prefix_mismatch' ? 'redirect_uri_mismatch' : $kind;
            $this->assertStringContainsString($expectedError, $response->getContent());
            $this->assertStringContainsString('window.errorDetails', $response->getContent());
            $this->assertStringNotContainsString('window.oauthAuthorizeRequest', $response->getContent());
        }
    }

    public static function invalidAuthorizationRequests(): array {
        return [
            'AD scope' => ['invalid_scope', true],
            'AD response type' => ['unsupported_response_type', true],
            'AD PKCE' => ['invalid_pkce', true],
            'AD client' => ['invalid_client', true],
            'AD callback' => ['redirect_uri_mismatch', true],
            'AD callback prefix' => ['callback_prefix_mismatch', true],
            'local scope' => ['invalid_scope', false],
            'local client' => ['invalid_client', false],
            'local callback' => ['redirect_uri_mismatch', false],
            'local callback prefix' => ['callback_prefix_mismatch', false],
            'falsey state' => ['state_zero', false],
            'AD valid' => ['valid', true],
            'local valid' => ['valid', false],
            'mapped scope' => ['mapped_scope', true],
            'mapped valid' => ['mapped_valid', true],
        ];
    }

    public function testRejectsDuplicateAuthorizationParametersWithoutRedirecting(): void {
        $browser = $this->createHttpsClient(false);
        $browser->request(
            'GET',
            '/oauth/v2/auth?client_id=first&client_id=second&redirect_uri=https%3A%2F%2Fexample.test%2Fcallback&response_type=code&state=original'
        );

        $response = $browser->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->headers->get('Location'));
    }

    public function testAutodiscoverFailureReturnsControlledUnavailableResponse(): void {
        SuplaAutodiscoverMock::$mockedResponses['/public-clients'] = [
            'method' => 'GET',
            'responseStatus' => 503,
            'response' => [],
        ];
        $browser = $this->createHttpsClient(false);
        $browser->request('GET', '/oauth/v2/auth?client_id=100_public&redirect_uri=http%3A%2F%2Fsuplascripts.local%2Fauth&response_type=code&scope=account_r');

        $response = $browser->getResponse();
        $this->assertSame(503, $response->getStatusCode());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->headers->get('Location'));
    }
}
