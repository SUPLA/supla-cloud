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

namespace App\Tests\Integration\Auth;

use App\Entity\Main\OAuth\AuthCode;
use App\Entity\Main\User;
use App\Kernel;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Supla\SuplaAutodiscover;
use App\Supla\SuplaAutodiscoverMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\TestClient;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use App\Tests\Integration\Traits\TestSuplaHttpClient;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class OAuthBrokerAuthorizationIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    /** @var SuplaAutodiscoverMock */
    private $autodiscover;

    /** @var ClientManagerInterface */
    private $clientManager;
    /** @var User */
    private $user;

    protected function tearDown(): void {
        TargetSuplaCloudRequestForwarder::$requestExecutor = null;
        parent::tearDown();
    }

    public function initializeDatabaseForTests() {
        $this->autodiscover = self::getContainer()->get(SuplaAutodiscover::class);
        $this->clientManager = self::getContainer()->get(ClientManagerInterface::class);
        $this->user = $this->createConfirmedUser();
    }

    public function testForwardsFreshPasswordAuthorizationToTrustedTargetAndReturnsStructuredResult() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = 'target.cloud';
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_mapped';
        $forwarded = null;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function ($origin, $endpoint, $data) use (&$forwarded) {
            if ($endpoint === 'server-info' || str_ends_with($endpoint, '/server-info')) {
                return [['oauthAuthorizationApiVersion' => 1], Response::HTTP_OK];
            }
            $forwarded = [$origin, $endpoint, $data];
            return [[
                'outcome' => 'authorized',
                'redirectUrl' => 'https://cool.app/callback?code=target-code&state=original-state',
            ], Response::HTTP_OK];
        };

        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', [
            'client_id' => '1_public',
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r',
            'state' => 'original-state',
            'decision' => 'approve',
            'username' => 'remote@supla.org',
            'password' => 'fresh-password',
        ]);

        $this->assertStatusCode(Response::HTTP_OK, $client->getResponse());
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('authorized', $result['outcome']);
        $this->assertSame('https://cool.app/callback?code=target-code&state=original-state', $result['redirectUrl']);
        $this->assertSame('https://target.cloud', $forwarded[0]);
        $this->assertSame('/api/internal/oauth/authorize', $forwarded[1]);
        $this->assertSame('1_mapped', $forwarded[2]['client_id']);
        $this->assertSame('fresh-password', $forwarded[2]['password']);
        $this->assertSame('original-state', $forwarded[2]['state']);
        $this->assertFalse($client->getResponse()->isRedirect());
    }

    public function testDoesNotForwardAuthorizationForUnknownTargetOrClientMapping() {
        $forwarded = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use (&$forwarded) {
            $forwarded = true;
            return [[], Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', [
            'client_id' => '1_public',
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r',
            'decision' => 'approve',
            'username' => 'not-registered@supla.org',
            'password' => 'fresh-password',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('unknown_target', $result['errorCode']);
        $this->assertFalse($forwarded);
    }

    public function testDoesNotProbeTargetWhenAutodiscoverHasNoMappedClient() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = 'target.cloud';
        $requests = 0;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use (&$requests) {
            $requests++;
            return [[], Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', [
            'client_id' => 'unmapped-public',
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r',
            'decision' => 'approve',
            'username' => 'remote@supla.org',
            'password' => 'fresh-password',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame('invalid_client', json_decode($client->getResponse()->getContent(), true)['errorCode']);
        $this->assertSame(0, $requests);
    }

    public function testForwardsTargetTokenEvidenceWithoutUsingItsSuffixForRouting() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = 'target.cloud';
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_mapped';
        $forwarded = null;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function ($origin, $endpoint, $data) use (&$forwarded) {
            if ($endpoint === 'server-info' || str_ends_with($endpoint, '/server-info')) {
                return [['oauthAuthorizationApiVersion' => 1], Response::HTTP_OK];
            }
            $forwarded = [$origin, $endpoint, $data];
            return [['outcome' => 'error', 'errorCode' => 'password_required'], Response::HTTP_UNAUTHORIZED];
        };

        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', [
            'client_id' => '1_public',
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r',
            'decision' => 'approve',
            'username' => 'remote@supla.org',
            'target_token' => 'opaque-webapp-token',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertSame('https://target.cloud', $forwarded[0]);
        $this->assertSame('opaque-webapp-token', $forwarded[2]['target_token']);
        $this->assertArrayNotHasKey('password', $forwarded[2]);
    }

    public function testRejectsMixedPasswordAndTargetTokenEvidenceBeforeDiscovery() {
        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', [
            'client_id' => '1_public',
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r',
            'decision' => 'approve',
            'username' => 'remote@supla.org',
            'password' => 'fresh-password',
            'target_token' => 'opaque-webapp-token',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertSame('invalid_request', json_decode($client->getResponse()->getContent(), true)['errorCode']);
        $this->assertSame(0, count(SuplaAutodiscoverMock::$requests));
    }

    public function testForwardsPkceChallengeFieldsUnchanged() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = 'target.cloud';
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_mapped';
        $forwarded = null;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function ($origin, $endpoint, $data) use (&$forwarded) {
            if ($endpoint === 'server-info' || str_ends_with($endpoint, '/server-info')) {
                return [['oauthAuthorizationApiVersion' => 1], Response::HTTP_OK];
            }
            $forwarded = [$origin, $endpoint, $data];
            return [[
                'outcome' => 'authorized',
                'redirectUrl' => 'https://cool.app/callback?code=target-code&state=original-state',
            ], Response::HTTP_OK];
        };

        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', [
            'client_id' => '1_public',
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r',
            'state' => 'original-state',
            'code_challenge' => 'challenge-value',
            'code_challenge_method' => 'S256',
            'decision' => 'approve',
            'username' => 'remote@supla.org',
            'password' => 'fresh-password',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSame('challenge-value', $forwarded[2]['code_challenge']);
        $this->assertSame('S256', $forwarded[2]['code_challenge_method']);
    }

    public function testReturnsUnsupportedAuthorizationApiWhenTargetLacksCapability() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = 'target.cloud';
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_mapped';
        $endpoints = [];
        TargetSuplaCloudRequestForwarder::$requestExecutor = function ($origin, $endpoint, $data) use (&$endpoints) {
            $endpoints[] = $endpoint;
            return [['cloudVersion' => '2.3.0'], Response::HTTP_OK];
        };

        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', $this->brokerAuthorizationRequest());

        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertSame('unsupported_authorization_api', json_decode($client->getResponse()->getContent(), true)['errorCode']);
        $this->assertSame(['server-info'], $endpoints);
    }

    public function testReturnsTargetUnavailableWhenCapabilityProbeFails() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = 'target.cloud';
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_mapped';
        $endpoints = [];
        TargetSuplaCloudRequestForwarder::$requestExecutor = function ($origin, $endpoint, $data) use (&$endpoints) {
            $endpoints[] = $endpoint;
            return [null, Response::HTTP_SERVICE_UNAVAILABLE];
        };

        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', $this->brokerAuthorizationRequest());

        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertSame('target_unavailable', json_decode($client->getResponse()->getContent(), true)['errorCode']);
        $this->assertSame(['server-info'], $endpoints);
    }

    public function testDoesNotContactTargetWhenAutodiscoverResolvesPrivateAddress() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = '127.0.0.1:8443';
        SuplaAutodiscoverMock::$clientMapping['https://127.0.0.1:8443']['1_public']['clientId'] = '1_mapped';
        $requests = 0;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use (&$requests) {
            $requests++;
            return [[], Response::HTTP_OK];
        };

        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', $this->brokerAuthorizationRequest());

        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertSame('target_unavailable', json_decode($client->getResponse()->getContent(), true)['errorCode']);
        $this->assertSame(0, $requests);
    }

    public function testPassesThroughClientMappingRetryForBoundedFreshRetry() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = 'target.cloud';
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_mapped';
        $authorizeCalls = 0;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function ($origin, $endpoint, $data) use (&$authorizeCalls) {
            if ($endpoint === 'server-info' || str_ends_with($endpoint, '/server-info')) {
                return [['oauthAuthorizationApiVersion' => 1], Response::HTTP_OK];
            }
            $authorizeCalls++;
            return [
                ['outcome' => 'error', 'errorCode' => 'client_mapping_retry'],
                Response::HTTP_CONFLICT,
            ];
        };

        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', $this->brokerAuthorizationRequest());

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $this->assertSame(
            ['outcome' => 'error', 'errorCode' => 'client_mapping_retry'],
            json_decode($client->getResponse()->getContent(), true)
        );
        $this->assertSame(1, $authorizeCalls);
    }

    public function testExchangesBrokerIssuedSuffixedCodeAtPublicTokenEndpoint() {
        SuplaAutodiscoverMock::$userMapping['remote@supla.org'] = 'target.cloud';
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_mapped', 'secret' => 'target-secret'];
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        $suffixedCode = 'target-issued-code.' . base64_encode('https://target.cloud');

        TargetSuplaCloudRequestForwarder::$requestExecutor = function (string $address, string $endpoint, ?array $data) use ($suffixedCode) {
            if ($endpoint === 'server-info' || str_ends_with($endpoint, '/server-info')) {
                return [['oauthAuthorizationApiVersion' => 1], Response::HTTP_OK];
            }
            $this->assertSame('https://target.cloud', $address);
            $this->assertSame('/api/internal/oauth/authorize', $endpoint);
            return [[
                'outcome' => 'authorized',
                'redirectUrl' => 'https://cool.app/callback?code=' . $suffixedCode . '&state=original-state',
            ], Response::HTTP_OK];
        };

        $client = $this->createHttpsClient();
        $client->request('POST', '/api/oauth/authorize', $this->brokerAuthorizationRequest());
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $redirectUrl = json_decode($client->getResponse()->getContent(), true)['redirectUrl'];
        parse_str((string) parse_url($redirectUrl, PHP_URL_QUERY), $redirectQuery);

        $tokenEndpoints = [];
        TargetSuplaCloudRequestForwarder::$requestExecutor = function (string $address, string $endpoint, array $data) use ($suffixedCode, &$tokenEndpoints) {
            $tokenEndpoints[] = $endpoint;
            $this->assertSame('https://target.cloud', $address);
            $this->assertSame('/oauth/v2/token', $endpoint);
            $this->assertSame($suffixedCode, $data['code']);
            $this->assertSame('1_mapped', $data['client_id']);
            $this->assertSame('target-secret', $data['client_secret']);
            return [['access_token' => 'target-access-token', 'token_type' => 'Bearer'], Response::HTTP_OK];
        };

        $tokenClient = $this->createHttpsClient(false);
        $tokenClient->apiRequest('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app/callback',
            'code' => $redirectQuery['code'],
            'state' => $redirectQuery['state'],
        ]);

        $this->assertSame(['/oauth/v2/token'], $tokenEndpoints);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSame('target-access-token', json_decode($tokenClient->getResponse()->getContent(), true)['access_token']);
    }

    public function testExchangesActualTargetIssuedPkceCodeThroughBrokerAndTargetEnforcesVerifier(): void {
        $targetClientEntity = $this->clientManager->createClient();
        $targetClientEntity->setRedirectUris(['https://cool.app/callback']);
        $targetClientEntity->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $this->clientManager->updateClient($targetClientEntity);
        $this->flush();
        $targetClientId = $targetClientEntity->getPublicId();
        $targetClientSecret = $targetClientEntity->getSecret();

        $targetKernel = new Kernel('test', false);
        $targetBrowser = new TestClient($targetKernel, [
            'HTTPS' => true,
            'HTTP_Accept' => 'application/json',
            'REMOTE_ADDR' => '1.2.3.4',
        ]);
        $verifier = str_repeat('BrokerVerifier_0123456789-._~', 2);
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $targetBrowser->apiRequest('POST', '/api/internal/oauth/authorize', [
            'client_id' => $targetClientId,
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r',
            'state' => 'brokered-pkce-state',
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
            'decision' => 'approve',
            'username' => $this->user->getUsername(),
            'password' => 'supla123',
        ], [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $targetBrowser->getResponse());
        $callback = $targetBrowser->getResponseBody()['redirectUrl'];
        parse_str((string) parse_url($callback, PHP_URL_QUERY), $callbackParams);
        $code = $callbackParams['code'];
        $this->assertSame('brokered-pkce-state', $callbackParams['state']);
        $this->assertSame('https://supla.local', base64_decode(substr($code, strrpos($code, '.') + 1)));

        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public'] = [
            'clientId' => $targetClientEntity->getPublicId(),
            'secret' => $targetClientEntity->getSecret(),
        ];

        $exchange = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app/callback',
            'code' => $code,
        ];
        $targetExchange = array_merge($exchange, [
            'client_id' => $targetClientId,
            'client_secret' => $targetClientSecret,
        ]);
        $targetBrowser->request('GET', '/oauth/v2/token?' . http_build_query($targetExchange + [
            'code_verifier' => $verifier,
        ]));
        $this->assertQueryVerifierRejected($targetBrowser, $verifier);

        $targetBrowser->apiRequest(
            'POST',
            '/oauth/v2/token?' . http_build_query(['code_verifier' => $verifier]),
            $targetExchange + ['code_verifier' => $verifier]
        );
        $this->assertQueryVerifierRejected($targetBrowser, $verifier);

        /** @var array<string, array<string, mixed>> $forwardedRequests */
        $forwardedRequests = [];
        $forwardedRequestName = null;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function (
            string $address,
            string $endpoint,
            array $data
        ) use (
            $targetBrowser,
            &$forwardedRequests,
            &$forwardedRequestName
        ) {
            $this->assertSame('https://supla.local', $address);
            $this->assertSame('/oauth/v2/token', $endpoint);
            $forwardedRequests[$forwardedRequestName] = $data;
            $targetBrowser->apiRequest('POST', $endpoint, $data);
            return [$targetBrowser->getResponseBody(), $targetBrowser->getResponse()->getStatusCode()];
        };

        $brokerBrowser = $this->createHttpsClient(false);
        $brokerBrowser->apiRequest(
            'GET',
            '/oauth/v2/token?' . http_build_query($exchange + ['code_verifier' => $verifier])
        );
        $this->assertQueryVerifierRejected($brokerBrowser, $verifier);
        $this->assertCount(0, $forwardedRequests);

        $brokerBrowser->apiRequest(
            'POST',
            '/oauth/v2/token?' . http_build_query(['code_verifier' => $verifier]),
            $exchange + ['code_verifier' => $verifier]
        );
        $this->assertQueryVerifierRejected($brokerBrowser, $verifier);
        $this->assertCount(0, $forwardedRequests);

        $forwardedRequestName = 'incorrect-proof';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', $exchange + ['code_verifier' => str_repeat('wrong', 10)]);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $brokerBrowser->getResponse());
        $this->assertTokenError($brokerBrowser, 'invalid_grant');

        $forwardedRequestName = 'missing-proof';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', $exchange);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $brokerBrowser->getResponse());
        $this->assertTokenError($brokerBrowser, 'invalid_grant');

        $forwardedRequestName = 'malformed-proof';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', $exchange + ['code_verifier' => 'malformed verifier']);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $brokerBrowser->getResponse());
        $this->assertTokenError($brokerBrowser, 'invalid_grant');

        $forwardedRequestName = 'changed-callback';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', array_merge($exchange, [
            'redirect_uri' => 'https://cool.app/changed-callback',
            'code_verifier' => $verifier,
        ]));
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $brokerBrowser->getResponse());
        $this->assertTokenError($brokerBrowser, 'redirect_uri_mismatch');

        $otherTargetClient = $this->clientManager->createClient();
        $otherTargetClient->setRedirectUris(['https://cool.app/callback']);
        $otherTargetClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $this->clientManager->updateClient($otherTargetClient);
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public'] = [
            'clientId' => $otherTargetClient->getPublicId(),
            'secret' => $otherTargetClient->getSecret(),
        ];
        $forwardedRequestName = 'mapped-client-mismatch';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', $exchange + ['code_verifier' => $verifier]);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $brokerBrowser->getResponse());
        $this->assertTokenError($brokerBrowser, 'invalid_grant');
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public'] = [
            'clientId' => $targetClientId,
            'secret' => $targetClientSecret,
        ];

        $forwardedRequestName = 'valid-proof';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', $exchange + ['code_verifier' => $verifier]);
        $this->assertStatusCode(Response::HTTP_OK, $brokerBrowser->getResponse());
        $this->assertArrayHasKey('access_token', $brokerBrowser->getResponseBody());

        $forwardedRequestName = 'replay';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', $exchange + ['code_verifier' => $verifier]);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $brokerBrowser->getResponse());
        $this->assertTokenError($brokerBrowser, 'invalid_grant');

        $this->assertSame(str_repeat('wrong', 10), $forwardedRequests['incorrect-proof']['code_verifier']);
        $this->assertArrayNotHasKey('code_verifier', $forwardedRequests['missing-proof']);
        $this->assertSame('malformed verifier', $forwardedRequests['malformed-proof']['code_verifier']);
        $this->assertSame($targetClientId, $forwardedRequests['incorrect-proof']['client_id']);
        $this->assertSame($targetClientSecret, $forwardedRequests['incorrect-proof']['client_secret']);
        $this->assertNotSame($targetClientId, $forwardedRequests['mapped-client-mismatch']['client_id']);
        $this->assertSame($verifier, $forwardedRequests['mapped-client-mismatch']['code_verifier']);
        $this->assertSame($verifier, $forwardedRequests['valid-proof']['code_verifier']);
        $this->assertSame($verifier, $forwardedRequests['replay']['code_verifier']);

        // A legacy non-PKCE grant still exchanges through the same broker and
        // keeps refresh-token behavior intact.
        $targetBrowser->apiRequest('POST', '/api/internal/oauth/authorize', [
            'client_id' => $targetClientId,
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r offline_access',
            'state' => 'legacy-broker-state',
            'decision' => 'approve',
            'username' => $this->user->getUsername(),
            'password' => 'supla123',
        ], [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $targetBrowser->getResponse());
        parse_str((string) parse_url($targetBrowser->getResponseBody()['redirectUrl'], PHP_URL_QUERY), $legacyCallbackParams);
        $legacyExchange = $exchange;
        $legacyExchange['code'] = $legacyCallbackParams['code'];
        $forwardedRequestName = 'legacy-downgrade';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', $legacyExchange + ['code_verifier' => $verifier]);
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $brokerBrowser->getResponse());
        $this->assertTokenError($brokerBrowser, 'invalid_request');

        $forwardedRequestName = 'legacy-non-pkce';
        $brokerBrowser->apiRequest('GET', '/oauth/v2/token?' . http_build_query($legacyExchange));
        $this->assertStatusCode(Response::HTTP_OK, $brokerBrowser->getResponse());
        $legacyTokenResponse = $brokerBrowser->getResponseBody();
        $this->assertArrayHasKey('refresh_token', $legacyTokenResponse);
        $this->assertSame($verifier, $forwardedRequests['legacy-downgrade']['code_verifier']);
        $this->assertArrayNotHasKey('code_verifier', $forwardedRequests['legacy-non-pkce']);

        $forwardedRequestName = 'refresh-token';
        $brokerBrowser->apiRequest('POST', '/oauth/v2/token', [
            'grant_type' => 'refresh_token',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'refresh_token' => $legacyTokenResponse['refresh_token'],
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $brokerBrowser->getResponse());
        $this->assertArrayHasKey('access_token', $brokerBrowser->getResponseBody());
        $this->assertSame('refresh_token', $forwardedRequests['refresh-token']['grant_type']);
        $targetKernel->shutdown();
    }

    public function testDisplaysNormalLoginFormIfLocalClientExists() {
        $localClient = $this->clientManager->createClient();
        $localClient->setName('Local App');
        $this->clientManager->updateClient($localClient);
        $client = $this->createHttpsInsulatedClient();
        $client->request('GET', $this->oauthAuthorizeUrl($localClient->getPublicId()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('askForTargetCloud: false', $response->getContent());
    }

    public function testDisplaysBrokerLoginFormIfLocalClientDoesNotExist() {
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('askForTargetCloud: true', $response->getContent());
    }

    public function testDisplaysNormalLoginFormIfLocalClientDoesNotExistButCloudIsNotBroker() {
        SuplaAutodiscoverMock::$isBroker = false;
        self::ensureKernelShutdown();
        $client = self::createClient(['debug' => false], ['HTTPS' => true, 'HTTP_Accept' => 'text/html']);
        $client->followRedirects();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
        $this->assertStringContainsString('statusCode: 404', $response->getContent());
    }

    public function testRedirectsToGivenTargetCloudIfAutodiscoverKnowsIt() {
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_local';
        $client = $this->createHttpsClient(false);
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->followRedirect();
        TestSuplaHttpClient::mockHttpRequest('https://target.cloud/.+/server-info', ['version' => '2.3.0']);
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'target.cloud']);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://target.cloud/oauth/v2/auth?', $targetUrl);
        $this->assertStringContainsString('client_id=1_local', $targetUrl);
        $this->assertStringContainsString('scope=account_r', $targetUrl);
        $this->assertStringContainsString('ad_username=ala%40supla.org', $targetUrl);
        $this->assertStringContainsString('state=some-state', $targetUrl);
    }

    public function testTriesToBrokerLoginWithInvalidSession() {
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_local';
        $client = $this->createHttpsClient(false);
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->followRedirect();
        $client = $this->createHttpsClient(false);
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'target.cloud']);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDoesNotRedirectToGivenTargetCloudIfItDoesNotWork() {
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_local';
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function () {
                return [null, Response::HTTP_SERVICE_UNAVAILABLE];
            };
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'target.cloud']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('error: "private_cloud_fail"', $response->getContent());
        $this->assertStringContainsString('lastTargetCloud: "target.cloud"', $response->getContent());
        $this->assertStringContainsString('lastUsername: "ala@supla.org"', $response->getContent());
    }

    public function testRedirectsToTargetCloudByAutodiscoveredUsername() {
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$userMapping['ala@supla.org'] = 'target.cloud';
        $client = $this->createHttpsClient(false);
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->followRedirect();
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org']);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://target.cloud/oauth/v2/auth?', $targetUrl);
        $this->assertStringContainsString('client_id=1_local', $targetUrl);
        $this->assertStringContainsString('scope=account_r', $targetUrl);
        $this->assertStringContainsString('ad_username=ala%40supla.org', $targetUrl);
        $this->assertStringContainsString('state=some-state', $targetUrl);
    }

    public function testDisplaysErrorIfTargetCloudIsNotRegisteredInAutodiscover() {
        $client = $this->createHttpsInsulatedClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'target.cloud']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('error: "autodiscover_fail"', $response->getContent());
        $this->assertStringContainsString('lastTargetCloud: "target.cloud"', $response->getContent());
        $this->assertStringContainsString('lastUsername: "ala@supla.org"', $response->getContent());
    }

    public function testDisplaysErrorIfTargetCloudUrlIsWrong() {
        $client = $this->createHttpsInsulatedClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'xxx']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('error: "autodiscover_fail"', $response->getContent());
        $this->assertStringContainsString('lastTargetCloud: "xxx"', $response->getContent());
        $this->assertStringContainsString('lastUsername: "ala@supla.org"', $response->getContent());
    }

    public function testEscapesTargetCloudUrl() {
        $client = $this->createHttpsInsulatedClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'x"x\'x']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('error: "autodiscover_fail"', $response->getContent());
        $this->assertStringContainsString('lastTargetCloud: "x\\"x\'x"', $response->getContent());
        $this->assertStringContainsString('lastUsername: "ala@supla.org"', $response->getContent());
    }

    public function testDisplaysErrorIfUserCannotBeAutodiscovered() {
        $client = $this->createHttpsInsulatedClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('askForTargetCloud: true', $response->getContent());
        $this->assertStringContainsString('error: "autodiscover_fail"', $response->getContent());
        $this->assertStringContainsString('lastTargetCloud: null', $response->getContent());
        $this->assertStringContainsString('lastUsername: "ala@supla.org"', $response->getContent());
    }

    public function testCreatesNewApiClientForPublicClientInTargetCloudDuringTheFirstRequest() {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'unicorn',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        $client = $this->createHttpsClient(false);
        $client->request('GET', $this->oauthAuthorizeUrl('1_local'));
        $client->followRedirect(); // to login form
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect()); // to new client id
        $targetUrl = $response->headers->get('Location');
        /** @var \App\Entity\Main\OAuth\ApiClient $createdClient */
        $createdClient = $this->clientManager->findClientBy(['name' => 'unicorn']);
        $this->assertStringContainsString('https://supla.local/oauth/v2/auth?', $targetUrl);
        $this->assertStringContainsString('client_id=' . $createdClient->getPublicId(), $targetUrl);
        $this->assertEquals('Cool app', $createdClient->getDescription());
        $this->assertEquals('1_public', $createdClient->getPublicClientId());
        $this->assertEquals(['https://cool.app'], $createdClient->getRedirectUris());
    }

    public function testForwardsIssueTokenRequestBasedOnAuthCode() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://target.cloud'),
            'state' => 'unicorn',
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint, array $data) use ($params, &$targetCalled) {
                $this->assertEquals('https://target.cloud', $address);
                $this->assertEquals('/oauth/v2/token', $endpoint);
                $this->assertEquals('1_local', $data['client_id']);
                $this->assertEquals('target-secret', $data['client_secret']);
                $this->assertEquals($params['code'], $data['code']);
                $this->assertEquals($params['redirect_uri'], $data['redirect_uri']);
                $this->assertEquals($params['state'], $data['state']);
                $targetCalled = true;
                return ['OK', Response::HTTP_OK];
            };
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertTrue($targetCalled);
    }

    public function testMapsPublicIdToTargetItWhenTheTargetIsAlreadyHit() {
        $client = $this->clientManager->createClient();
        $client->setRedirectUris(['https://unicorns.pl']);
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $this->clientManager->updateClient($client);
        $authCode = new AuthCode();
        $authCode->setClient($client);
        $authCode->setUser($this->user);
        $authCode->setScope('account_r');
        $authCode->setExpiresAt(time() + 60);
        $authCode->setRedirectUri('https://unicorns.pl');
        $authCode->setToken('abcd.' . base64_encode('https://supla.local'));
        $this->getEntityManager()->persist($authCode);

        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public'] =
            ['clientId' => $client->getPublicId(), 'secret' => $client->getSecret()];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => $authCode->getRedirectUri(),
            'code' => $authCode->getToken(),
            'state' => 'unicorn',
        ];

        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint, array $data) use ($authCode, $client, $params, &$targetCalled) {
                $this->assertEquals('https://supla.local', $address);
                $this->assertEquals('/oauth/v2/token', $endpoint);
                $this->assertEquals($client->getPublicId(), $data['client_id']);
                $this->assertEquals($client->getSecret(), $data['client_secret']);
                $this->assertEquals($authCode->getToken(), $data['code']);
                $this->assertEquals($authCode->getRedirectUri(), $data['redirect_uri']);
                $this->assertEquals($params['state'], $data['state']);
                $targetCalled = true;
                return ['OK', Response::HTTP_OK];
            };
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertTrue($targetCalled);
    }

    public function testReturnsErrorForInvalidClientId() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '2_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://unicorns.pl',
            'code' => 'ABC.' . base64_encode('http://supla.local'),
        ];
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testDoesNotForwardAuthAnywhereIfNotBroker() {
        $this->expectException(ExpectationFailedException::class);
        SuplaAutodiscoverMock::$isBroker = false;
        $this->testForwardsIssueTokenRequestBasedOnAuthCode();
    }

    public function testReturnsErrorIfAutodiscoverDoesNotKnowTargetCloudGivenInAuthCode() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use ($params, &$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertFalse($targetCalled);
        $this->assertFalse($response->isSuccessful());
    }

    public function testDoesNotForwardTokenSuffixWithProhibitedAddressEvenIfItHasAnAdMapping() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://127.0.0.1']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use (&$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://127.0.0.1'),
        ]);

        $this->assertFalse($targetCalled);
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testReturnsErrorIfPublicSecretDoesNotMatch() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'wrong-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use ($params, &$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertFalse($targetCalled);
        $this->assertFalse($response->isSuccessful());
    }

    public function testReturnsErrorIfPublicClientIdDoesNotExist() {
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use ($params, &$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertFalse($targetCalled);
        $this->assertFalse($response->isSuccessful());
    }

    public function testReturnsErrorForInvalidSyntaxOfAuthCode() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC',
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use ($params, &$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertFalse($targetCalled);
        $this->assertFalse($response->isSuccessful());
    }

    public function testForwardsIssueTokenRequestBasedOnRefreshToken() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'refresh_token' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint, array $data) use ($params, &$targetCalled) {
                $this->assertEquals('https://target.cloud', $address);
                $this->assertEquals('/oauth/v2/token', $endpoint);
                $this->assertEquals('1_local', $data['client_id']);
                $this->assertEquals('target-secret', $data['client_secret']);
                $this->assertEquals($params['grant_type'], 'refresh_token');
                $this->assertEquals($params['refresh_token'], $data['refresh_token']);
                $targetCalled = true;
                return ['OK', Response::HTTP_OK];
            };
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertTrue($targetCalled);
    }

    public function testForwardsIssueTokenRequestErrorBasedOnRefreshToken() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'refresh_token' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function () use ($params, &$targetCalled) {
                return [null, Response::HTTP_SERVICE_UNAVAILABLE];
            };
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertStatusCode(503, $response);
    }

    /** @large */
    public function testForcesReauthorizationIfUserIsAlreadyLoggedInButHitsPublicId() {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'Unicorn App',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_local'));
        $client->apiRequest('POST', '/oauth/v2/auth_login', ['_username' => 'supler@supla.org', '_password' => 'supla123']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('desiredScopes: "account_r"', $response->getContent());
        $this->assertStringContainsString('clientName: "Unicorn App"', $response->getContent());
        // now, try again with the public id
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        // the broker login form should be displayed
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('askForTargetCloud: true', $response->getContent());
        $this->assertStringContainsString('clientName: "Unicorn App"', $response->getContent());
    }

    /** @large */
    public function testForcesReauthrozatoinIfUserIsAlreadyLoggedInButHitsIdNotMappedYet() {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['2_public']['clientId'] = '2_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'Unicorn App',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        SuplaAutodiscoverMock::$publicClients['2_public'] = SuplaAutodiscoverMock::$publicClients['1_public'];
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_local'));
        $client->apiRequest('POST', '/oauth/v2/auth_login', ['_username' => 'supler@supla.org', '_password' => 'supla123']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('desiredScopes: "account_r"', $response->getContent());
        $this->assertStringContainsString('clientName: "Unicorn App"', $response->getContent());
        // now, try again with the not-mapped-yet client id
        $client->request('GET', $this->oauthAuthorizeUrl('2_local'));
        $this->assertStatusCode(200, $client->getResponse());
        // the normal login form with newely mapped client should be displayed
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertStringContainsString('askForTargetCloud: false', $response->getContent());
        $this->assertStringContainsString('clientName: "Unicorn App"', $response->getContent());
    }

    /** @large */
    public function testUpdatesMappedClientNameAndDescriptionIfUpdatedInAd() {
        $localClient = $this->clientManager->createClient();
        $localClient->setName('Local App');
        $localClient->setPublicClientId('1_public');
        $this->clientManager->updateClient($localClient);
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = $localClient->getPublicId();
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'butterfly',
            'description' => ['pl' => 'Spoko apka', 'en' => 'Cooler app'],
            'redirectUris' => ['https://cooler.app'],
        ];
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl($localClient->getPublicId()));
        $client->apiRequest('POST', '/oauth/v2/auth_login', ['_username' => 'supler@supla.org', '_password' => 'supla123']);
        $createdClient = $this->clientManager->findClientByPublicId($localClient->getPublicId());
        $this->assertNotNull($createdClient);
        $this->assertEquals('butterfly', $createdClient->getName());
        $this->assertEquals(['pl' => 'Spoko apka', 'en' => 'Cooler app'], $createdClient->getDescription());
        $this->assertEquals(['https://cooler.app'], $createdClient->getRedirectUris());
    }

    private function oauthAuthorizeUrl($clientId, $redirectUri = 'https://app.com/auth', $scope = 'account_r', $responseType = 'code') {
        return '/oauth/v2/auth?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'response_type' => $responseType,
                'state' => 'some-state',
            ]);
    }

    private function brokerAuthorizationRequest() {
        return [
            'client_id' => '1_public',
            'redirect_uri' => 'https://cool.app/callback',
            'response_type' => 'code',
            'scope' => 'account_r',
            'decision' => 'approve',
            'username' => 'remote@supla.org',
            'password' => 'fresh-password',
        ];
    }

    private function assertTokenError(TestClient $client, string $error): void {
        $this->assertSame($error, $client->getResponseBody()['error']);
    }

    private function assertQueryVerifierRejected(TestClient $client, string $verifier): void {
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client->getResponse());
        $this->assertTokenError($client, 'invalid_request');
        $this->assertStringNotContainsString($verifier, $client->getResponse()->getContent());
    }
}
