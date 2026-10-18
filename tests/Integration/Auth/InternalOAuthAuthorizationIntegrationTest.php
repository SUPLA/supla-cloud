<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Auth;

use App\Auth\MappedApiClientResolver;
use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\OAuth\ApiClientAuthorization;
use App\Entity\Main\OAuth\AuthCode;
use App\Entity\Main\User;
use App\Enums\ApiClientType;
use App\Exception\ApiException;
use App\Supla\SuplaAutodiscoverMock;
use App\Supla\SuplaServerMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

class InternalOAuthAuthorizationIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    private $oauthClient;
    private $user;

    /** @before */
    public function init(): void {
        $clientManager = self::getContainer()->get(ClientManagerInterface::class);
        $this->oauthClient = $clientManager->createClient();
        $this->oauthClient->setRedirectUris(['https://unicorns.pl/callback']);
        $this->oauthClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $clientManager->updateClient($this->oauthClient);
        $this->user = $this->createConfirmedUser();
    }

    public function testBrokerCanAuthorizeAndExchangeCodeForLocalClient(): void {
        self::ensureKernelShutdown();
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest(), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);

        $this->assertStatusCode(200, $client->getResponse());
        $response = $client->getResponseBody();
        $this->assertSame('authorized', $response['outcome']);
        $this->assertEqualsCanonicalizing(['outcome', 'redirectUrl'], array_keys($response));
        $this->assertTrue($client->getResponse()->headers->hasCacheControlDirective('no-store'));
        $this->assertFalse($client->getResponse()->headers->has('Set-Cookie'));
        $this->assertStringContainsString('https://unicorns.pl/callback?', $response['redirectUrl']);
        $this->assertStringContainsString('state=original-state', $response['redirectUrl']);

        $authorization = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)
            ->findOneByUserAndApiClient($this->user, $this->oauthClient);
        $this->assertNotNull($authorization);
        $this->assertSame('account_r offline_access', $authorization->getScope());

        parse_str(parse_url($response['redirectUrl'], PHP_URL_QUERY), $query);

        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenRequest = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->oauthClient->getPublicId(),
            'client_secret' => $this->oauthClient->getSecret(),
            'redirect_uri' => 'https://unicorns.pl/callback',
            'code' => $query['code'],
        ];
        $tokenClient->apiRequest('POST', '/oauth/v2/token', array_merge($tokenRequest, [
            'redirect_uri' => 'https://attacker.invalid/callback',
        ]));
        $this->assertStatusCode(400, $tokenClient->getResponse());
        $tokenClient->apiRequest('POST', '/oauth/v2/token', $tokenRequest);
        $this->assertStatusCode(200, $tokenClient->getResponse());
        $this->assertSame('account_r offline_access', $tokenClient->getResponseBody()['scope']);
        $this->assertArrayHasKey('access_token', $tokenClient->getResponseBody());

        $tokenClient->apiRequest('POST', '/oauth/v2/token', $tokenRequest);
        $this->assertStatusCode(400, $tokenClient->getResponse());
        $this->assertSame('invalid_grant', $tokenClient->getResponseBody()['error']);
    }

    public function testBrowserAndInvalidBrokerRequestsCannotAuthorize(): void {
        self::ensureKernelShutdown();
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest());
        $this->assertStatusCode(401, $client->getResponse());

        self::ensureKernelShutdown();
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest(), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer TARGET',
        ]);
        $this->assertStatusCode(401, $client->getResponse());

        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testInvalidCredentialsAndAuthorizationRequestDoNotCreateGrantOrCode(): void {
        self::ensureKernelShutdown();
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest(['password' => 'incorrect']), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(401, $client->getResponse());

        self::ensureKernelShutdown();
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'redirect_uri' => 'https://attacker.invalid/callback',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());

        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testUnsupportedScopeReturnsStructuredErrorWithValidatedCallback(): void {
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'scope' => 'not_a_scope',
            'state' => 'opaque +/&=state',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);

        $this->assertStatusCode(400, $client->getResponse());
        $response = $client->getResponseBody();
        $this->assertSame('error', $response['outcome']);
        $this->assertSame('invalid_scope', $response['errorCode']);
        $this->assertStringStartsWith('https://unicorns.pl/callback?', $response['redirectUrl']);
        parse_str(parse_url($response['redirectUrl'], PHP_URL_QUERY), $query);
        $this->assertSame('invalid_scope', $query['error']);
        $this->assertSame('opaque +/&=state', $query['state']);
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testCallbackMustMatchEntireRegisteredUri(): void {
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'redirect_uri' => 'https://unicorns.pl/callback/attacker',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'redirect_uri_mismatch'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testBrokerCanDenyValidatedRequestWithoutPasswordAndPreserveZeroState(): void {
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'decision' => 'deny', 'username' => '', 'password' => '', 'state' => '0',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(200, $client->getResponse());
        $response = $client->getResponseBody();
        $this->assertSame('denied', $response['outcome']);
        parse_str(parse_url($response['redirectUrl'], PHP_URL_QUERY), $query);
        $this->assertSame('access_denied', $query['error']);
        $this->assertSame('0', $query['state']);
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testInternalOperationOnlyIssuesCodes(): void {
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'response_type' => 'token',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame('unsupported_response_type', $client->getResponseBody()['errorCode']);
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    /** @dataProvider malformedRequestProvider */
    public function testMalformedCredentialsAreStructuredErrorsInsteadOfServerErrors(array $overrides): void {
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest($overrides), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'invalid_request'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public static function malformedRequestProvider(): array {
        return [
            'username array' => [['username' => ['supler@supla.org']]],
            'client array' => [['client_id' => ['local-client']]],
            'callback array' => [['redirect_uri' => ['https://unicorns.pl/callback']]],
            'scope boolean' => [['scope' => true]],
            'state array' => [['state' => ['opaque']]],
            'unsupported bearer evidence' => [['bearerToken' => 'unverified']],
            'unsupported PKCE evidence' => [['code_challenge' => 'challenge', 'code_challenge_method' => 'S256']],
        ];
    }

    public function testPasswordFailuresUseExistingAccountLockout(): void {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $client = $this->createHttpsClient(false);
            $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
                'password' => 'incorrect',
            ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
            $this->assertStatusCode(401, $client->getResponse());
            $this->assertSame('invalid_credentials', $client->getResponseBody()['errorCode']);
        }
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest(), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(429, $client->getResponse());
        $this->assertSame('locked', $client->getResponseBody()['errorCode']);
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    /** @dataProvider invalidScopeProvider */
    public function testScopeMustBeSupportedForConsent(string $scope): void {
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'scope' => $scope,
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame('invalid_scope', $client->getResponseBody()['errorCode']);
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public static function invalidScopeProvider(): array {
        return [['restapi'], ['']];
    }

    public function testClientMustAllowAuthorizationCodeGrant(): void {
        $this->oauthClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS]);
        $this->persist($this->oauthClient);
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest(), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame('unauthorized_client', $client->getResponseBody()['errorCode']);
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testScopeExpansionMergesConsentAndEnablesMqttOnlyOnce(): void {
        foreach (['account_r', 'account_rw mqtt_broker', 'mqtt_broker'] as $scope) {
            $client = $this->createHttpsClient(false);
            $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
                'scope' => $scope,
            ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
            $this->assertStatusCode(200, $client->getResponse());
            $this->assertSame('authorized', $client->getResponseBody()['outcome']);
            $this->assertFalse($client->getResponse()->headers->has('Set-Cookie'));
        }
        $user = $this->freshEntity($this->user);
        $this->assertTrue($user->isMqttBrokerEnabled());
        $authorizations = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll();
        $this->assertCount(1, $authorizations);
        $this->assertSame('account_r account_rw mqtt_broker', $authorizations[0]->getScope());
        $commands = array_filter(SuplaServerMock::$executedCommands, fn($command) => str_contains($command, 'ON-MQTT-SETTINGS-CHANGED'));
        $this->assertSame(['USER-ON-MQTT-SETTINGS-CHANGED:' . $user->getId()], array_values($commands));
    }

    public function testDisabledUserCannotGrantEvenWithCorrectPassword(): void {
        $this->user->setEnabled(false);
        $this->persist($this->user);
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest(), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(409, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'disabled'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testFailedExpansionAndDenialNeverModifyPriorConsent(): void {
        $this->user->addApiClientAuthorization($this->oauthClient, 'account_r');
        $this->persist($this->user);
        foreach ([['decision' => 'approve', 'password' => 'wrong'], ['decision' => 'deny']] as $decision) {
            $client = $this->createHttpsClient(false);
            $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest(array_merge([
                'scope' => 'account_rw mqtt_broker',
            ], $decision)), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
            $this->assertStatusCode($decision['decision'] === 'deny' ? 200 : 401, $client->getResponse());
            $authorizations = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll();
            $this->assertCount(1, $authorizations);
            $this->assertSame('account_r', $authorizations[0]->getScope());
            $this->assertFalse($this->freshEntity($this->user)->isMqttBrokerEnabled());
            $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        }
    }

    public function testUnknownClientIsNotResolvedThroughAutodiscover(): void {
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => 'unknown-public-app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'invalid_client'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testProvisionsTargetLocalClientForMappedPublicClientOnFirstAuthorization(): void {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'unicorn',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => '1_local',
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);

        $this->assertStatusCode(200, $client->getResponse());
        $response = $client->getResponseBody();
        $this->assertSame('authorized', $response['outcome']);
        $this->assertStringContainsString('https://cool.app?', $response['redirectUrl']);
        $this->assertStringContainsString('state=original-state', $response['redirectUrl']);

        /** @var ApiClient $provisioned */
        $provisioned = self::getContainer()->get(ClientManagerInterface::class)->findClientBy(['publicClientId' => '1_public']);
        $this->assertNotNull($provisioned, 'The mapped public client must be provisioned target-locally.');
        $this->assertSame('unicorn', $provisioned->getName());
        $this->assertSame('Cool app', $provisioned->getDescription());
        $this->assertSame(['https://cool.app'], $provisioned->getRedirectUris());
        $this->assertSame(ApiClientType::BROKER()->getValue(), $provisioned->getType()->getValue());
        $credentialUpdate = array_values(array_filter(
            SuplaAutodiscoverMock::$requests,
            fn(array $request) => $request['endpoint'] === '/mapped-client-credentials/1_local'
        ));
        $this->assertCount(1, $credentialUpdate);
        $this->assertSame($provisioned->getPublicId(), $credentialUpdate[0]['post']['clientId']);
        $this->assertSame($provisioned->getSecret(), $credentialUpdate[0]['post']['secret']);
        $this->assertNotNull(
            $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findOneByUserAndApiClient($this->user, $provisioned)
        );
    }

    public function testRepeatedAuthorizationIsIdempotentForProvisionedMappedClient(): void {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'unicorn',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        $first = $this->createHttpsClient(false);
        $first->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => '1_local',
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(200, $first->getResponse());

        /** @var ApiClient $provisioned */
        $provisioned = self::getContainer()->get(ClientManagerInterface::class)->findClientBy(['publicClientId' => '1_public']);
        $this->assertNotNull($provisioned);

        // A repeated authorization addressed to the now-provisioned target-local
        // client id must resolve the existing client and not create a duplicate.
        $second = $this->createHttpsClient(false);
        $second->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => $provisioned->getPublicId(),
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(200, $second->getResponse());
        $this->assertSame('authorized', $second->getResponseBody()['outcome']);

        $this->assertCount(1, $this->getDoctrine()->getRepository(ApiClient::class)->findBy(['publicClientId' => '1_public']));
    }

    public function testRepeatedOriginalMappedIdReusesProvisionedClient(): void {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'unicorn',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $client = $this->createHttpsClient(false);
            $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
                'client_id' => '1_local',
                'redirect_uri' => 'https://cool.app',
            ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
            $this->assertStatusCode(200, $client->getResponse());
        }

        $this->assertCount(1, $this->getDoctrine()->getRepository(ApiClient::class)->findBy(['publicClientId' => '1_public']));
    }

    public function testConcurrentMappedClientCreationReturnsRetryThenReusesCanonicalClient(): void {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'unicorn',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];

        $client = $this->createHttpsClient(false);
        $clientManager = self::getContainer()->get(ClientManagerInterface::class);
        $racingClientManager = new class ($clientManager) implements ClientManagerInterface {
            public bool $insertedCompetingClient = false;

            public function __construct(private ClientManagerInterface $inner) {
            }

            public function createClient(): ClientInterface {
                return $this->inner->createClient();
            }

            public function getClass(): string {
                return $this->inner->getClass();
            }

            public function findClientBy(array $criteria): ?ClientInterface {
                $client = $this->inner->findClientBy($criteria);
                if ($client === null && isset($criteria['publicClientId']) && !$this->insertedCompetingClient) {
                    // Model another worker committing after this request's
                    // lookup but before its insert. The stale miss is returned
                    // so the real database UNIQUE constraint arbitrates the race.
                    $this->insertedCompetingClient = true;
                    $winner = $this->inner->createClient();
                    $winner->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
                    $winner->setType(ApiClientType::BROKER());
                    $winner->setPublicClientId($criteria['publicClientId']);
                    $winner->updateDataFromAutodiscover([
                        'name' => 'unicorn',
                        'description' => 'Cool app',
                        'redirectUris' => ['https://cool.app'],
                    ]);
                    $this->inner->updateClient($winner);
                }
                return $client;
            }

            public function findClientByPublicId($publicId): ?ClientInterface {
                return $this->inner->findClientByPublicId($publicId);
            }

            public function updateClient(ClientInterface $client) {
                return $this->inner->updateClient($client);
            }

            public function deleteClient(ClientInterface $client) {
                return $this->inner->deleteClient($client);
            }
        };
        self::getContainer()->set(MappedApiClientResolver::class, new MappedApiClientResolver(
            $racingClientManager,
            self::getContainer()->get(\App\Supla\SuplaAutodiscover::class)
        ));

        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => '1_local',
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertTrue($racingClientManager->insertedCompetingClient, 'The simulated concurrent insert should win the stale lookup.');
        $this->assertStatusCode(409, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'client_mapping_retry'], $client->getResponseBody());

        // The failed insert closes Doctrine's EntityManager. Restart the kernel
        // as a new request would, then verify the retry resolves the committed
        // winner rather than attempting another local identity.
        self::ensureKernelShutdown();
        $retry = $this->createHttpsClient(false);
        /** @var ApiClient $canonicalClient */
        $canonicalClient = $this->getDoctrine()->getRepository(ApiClient::class)->findOneBy(['publicClientId' => '1_public']);
        $this->assertNotNull($canonicalClient);
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
        $retry->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => '1_local',
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(200, $retry->getResponse());
        $this->assertSame('authorized', $retry->getResponseBody()['outcome']);
        $reusedClient = $this->getDoctrine()->getRepository(ApiClient::class)->findOneBy(['publicClientId' => '1_public']);
        $this->assertSame($canonicalClient->getId(), $reusedClient->getId());
    }

    public function testRefreshedMetadataDoesNotAuthorizeAnUnregisteredCallback(): void {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'unicorn',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => '1_local',
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(200, $client->getResponse());

        // Simulate refreshed AD metadata: the prior callback is no longer
        // registered. The request must be checked against current metadata.
        SuplaAutodiscoverMock::$publicClients['1_public']['redirectUris'] = ['https://new.cool.app'];
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => $this->getDoctrine()->getRepository(ApiClient::class)->findOneBy(['publicClientId' => '1_public'])->getPublicId(),
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame('redirect_uri_mismatch', $client->getResponseBody()['errorCode']);
        $this->assertSame(
            ['https://new.cool.app'],
            $this->getDoctrine()->getRepository(ApiClient::class)->findOneBy(['publicClientId' => '1_public'])->getRedirectUris()
        );
        $this->assertCount(1, $this->getDoctrine()->getRepository(ApiClient::class)->findBy(['publicClientId' => '1_public']));
    }

    public function testInconsistentAutodiscoverMappingDoesNotCreateClientOrRedirect(): void {
        // AD maps this target-local id to a public id that is absent from its
        // current public-client catalogue.
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => '1_local',
            'redirect_uri' => 'https://attacker.invalid/callback',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'invalid_client'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClient::class)->findBy(['publicClientId' => '1_public']));
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testPublicClientNotMappedToThisTargetIsNotImported(): void {
        // 2_public is a known AD public client, but it is not mapped to this
        // target, so it must not be silently provisioned here.
        SuplaAutodiscoverMock::$publicClients['2_public'] = [
            'name' => 'other app',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => '2_public',
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'invalid_client'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClient::class)->findBy(['publicClientId' => '2_public']));
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testAutodiscoverOutageFailsSafelyWithoutProvisioning(): void {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$mockedResponses['/mapped-client-public-id/'] = [
            'method' => 'GET',
            'responseStatus' => 503,
            'response' => new ApiException('Autodiscover unavailable', 503),
        ];
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'client_id' => '1_local',
            'redirect_uri' => 'https://cool.app',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(503, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'target_unavailable'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClient::class)->findBy(['publicClientId' => '1_public']));
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testIssuanceStorageFailureRollsBackConsentAndCodeWithSafeError(): void {
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            // Each scope is supported, but the value exceeds the storage limit.
            'scope' => trim(str_repeat('account_r ', 250)),
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(500, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'server_error'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testUnavailableBrokerVerificationFailsClosedWithStructuredError(): void {
        SuplaAutodiscoverMock::$mockedResponses['/about'] = [
            'method' => 'GET', 'responseStatus' => 503,
            'response' => new ApiException('Service temporarily unavailable', 503),
        ];
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest(), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(503, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'target_unavailable'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(0, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
    }

    public function testCallbackQueryAndEmptyStateArePreservedForApprovalAndDenial(): void {
        $callback = 'https://unicorns.pl/callback?existing=keep%2Bvalue';
        $this->oauthClient->setRedirectUris([$callback]);
        $this->persist($this->oauthClient);
        foreach (['approve' => 'authorized', 'deny' => 'denied'] as $decision => $outcome) {
            $client = $this->createHttpsClient(false);
            $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
                'redirect_uri' => $callback, 'decision' => $decision, 'state' => '',
            ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
            $this->assertStatusCode(200, $client->getResponse());
            $this->assertSame($outcome, $client->getResponseBody()['outcome']);
            parse_str(parse_url($client->getResponseBody()['redirectUrl'], PHP_URL_QUERY), $query);
            $this->assertSame('keep+value', $query['existing']);
            $this->assertSame('', $query['state']);
        }
    }

    private function authorizationRequest(array $overrides = []): array {
        return array_merge([
            'client_id' => $this->oauthClient->getPublicId(),
            'redirect_uri' => 'https://unicorns.pl/callback',
            'response_type' => 'code',
            'scope' => 'account_r offline_access',
            'state' => 'original-state',
            'username' => $this->user->getEmail(),
            'password' => 'supla123',
            'decision' => 'approve',
        ], $overrides);
    }
}
