<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Auth;

use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\OAuth\ApiClientAuthorization;
use App\Entity\Main\OAuth\AuthCode;
use App\Entity\Main\User;
use App\Exception\ApiException;
use App\Supla\SuplaAutodiscoverMock;
use App\Supla\SuplaServerMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\TestClient;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
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

    public function testBrokerCanAuthorizeAndExchangeCodeBoundToS256Challenge(): void {
        $verifier = str_repeat('verifier_', 8);
        $challenge = $this->s256Challenge($verifier);
        $client = $this->authorize($this->authorizationRequest([
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ]));

        $this->assertStatusCode(200, $client->getResponse());
        parse_str(parse_url($client->getResponseBody()['redirectUrl'], PHP_URL_QUERY), $query);
        $authCode = $this->getDoctrine()->getRepository(AuthCode::class)->findOneBy(['token' => $query['code']]);
        $this->assertSame($challenge, $authCode->getCodeChallenge());
        $this->assertSame('S256', $authCode->getCodeChallengeMethod());

        $tokenRequest = $this->tokenRequest($query['code']);
        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', $tokenRequest);
        $this->assertStatusCode(400, $tokenClient->getResponse());
        $this->assertSame('invalid_grant', $tokenClient->getResponseBody()['error']);

        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', $tokenRequest + ['code_verifier' => $verifier]);
        $this->assertStatusCode(200, $tokenClient->getResponse());
        $this->assertArrayHasKey('access_token', $tokenClient->getResponseBody());

        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', $tokenRequest + ['code_verifier' => $verifier]);
        $this->assertStatusCode(400, $tokenClient->getResponse());
        $this->assertSame('invalid_grant', $tokenClient->getResponseBody()['error']);
    }

    /** @dataProvider invalidVerifierProvider */
    public function testS256CodeRejectsInvalidVerifier(string $verifier, string $expectedError): void {
        $validVerifier = str_repeat('valid_verifier_', 4);
        $client = $this->authorize($this->authorizationRequest([
            'code_challenge' => $this->s256Challenge($validVerifier),
            'code_challenge_method' => 'S256',
        ]));
        parse_str(parse_url($client->getResponseBody()['redirectUrl'], PHP_URL_QUERY), $query);

        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', $this->tokenRequest($query['code']) + [
            'code_verifier' => $verifier,
        ]);
        $this->assertStatusCode(400, $tokenClient->getResponse());
        $this->assertSame($expectedError, $tokenClient->getResponseBody()['error']);
    }

    public static function invalidVerifierProvider(): array {
        return [
            'incorrect' => [str_repeat('wrong_verifier_', 4), 'invalid_grant'],
            'malformed' => ['has spaces and is not a verifier', 'invalid_grant'],
        ];
    }

    public function testS256CodeRemainsBoundToItsClientAndRedirectUri(): void {
        $verifier = str_repeat('bound_verifier_', 4);
        $client = $this->authorize($this->authorizationRequest([
            'code_challenge' => $this->s256Challenge($verifier),
            'code_challenge_method' => 'S256',
        ]));
        parse_str(parse_url($client->getResponseBody()['redirectUrl'], PHP_URL_QUERY), $query);
        $otherClient = self::getContainer()->get(ClientManagerInterface::class)->createClient();
        $otherClient->setRedirectUris(['https://unicorns.pl/callback']);
        $otherClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]);
        self::getContainer()->get(ClientManagerInterface::class)->updateClient($otherClient);

        $tokenRequest = $this->tokenRequest($query['code']) + ['code_verifier' => $verifier];
        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', array_merge($tokenRequest, [
            'redirect_uri' => 'https://unicorns.pl/callback/attacker',
        ]));
        $this->assertStatusCode(400, $tokenClient->getResponse());
        $this->assertSame('redirect_uri_mismatch', $tokenClient->getResponseBody()['error']);

        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', array_merge($tokenRequest, [
            'client_id' => $otherClient->getPublicId(),
            'client_secret' => $otherClient->getSecret(),
        ]));
        $this->assertStatusCode(400, $tokenClient->getResponse());
        $this->assertSame('invalid_grant', $tokenClient->getResponseBody()['error']);

        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', $tokenRequest);
        $this->assertStatusCode(200, $tokenClient->getResponse());
    }

    public function testConcurrentS256CodeExchangeSucceedsExactlyOnce(): void {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('The pcntl extension is required to exercise concurrent exchanges.');
        }
        $verifier = str_repeat('concurrent_verifier_', 3);
        $client = $this->authorize($this->authorizationRequest([
            'code_challenge' => $this->s256Challenge($verifier),
            'code_challenge_method' => 'S256',
        ]));
        parse_str(parse_url($client->getResponseBody()['redirectUrl'], PHP_URL_QUERY), $query);
        $tokenRequest = $this->tokenRequest($query['code']) + ['code_verifier' => $verifier];
        self::ensureKernelShutdown();

        [$parentSocket, $childSocket] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
        $pid = pcntl_fork();
        $this->assertNotSame(-1, $pid, 'Could not fork the concurrent token request.');
        if ($pid === 0) {
            fclose($parentSocket);
            fread($childSocket, 1);
            $client = $this->createClient();
            $client->apiRequest('POST', '/oauth/v2/token', $tokenRequest);
            fwrite($childSocket, $client->getResponse()->getStatusCode() . '|' . $client->getResponse()->getContent() . "\n");
            fclose($childSocket);
            exit(0);
        }

        fclose($childSocket);
        fwrite($parentSocket, 'x');
        $parentClient = $this->createClient();
        $parentClient->apiRequest('POST', '/oauth/v2/token', $tokenRequest);
        $parentStatus = $parentClient->getResponse()->getStatusCode();
        $parentBody = $parentClient->getResponse()->getContent();
        $childResponse = stream_get_line($parentSocket, 4096, "\n");
        [$childStatus, $childBody] = explode('|', $childResponse, 2);
        fclose($parentSocket);
        pcntl_waitpid($pid, $childStatusCode);

        $this->assertSame(0, pcntl_wexitstatus($childStatusCode));
        $this->assertEqualsCanonicalizing(
            [200, 400],
            [$parentStatus, (int)$childStatus],
            sprintf('Parent status %d; child status %d and body %s', $parentStatus, $childStatus, $childBody)
        );
        $responses = [
            [$parentStatus, json_decode($parentBody, true)],
            [(int)$childStatus, json_decode($childBody, true)],
        ];
        foreach ($responses as [$status, $body]) {
            $this->assertSame($status === 200 ? 'access_token' : 'invalid_grant', $status === 200
                ? (isset($body['access_token']) ? 'access_token' : null)
                : ($body['error'] ?? null));
        }
    }

    public function testVerifierWithoutChallengeIsRejectedAsDowngrade(): void {
        $client = $this->authorize($this->authorizationRequest());
        parse_str(parse_url($client->getResponseBody()['redirectUrl'], PHP_URL_QUERY), $query);
        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->request('GET', '/oauth/v2/token?' . http_build_query($this->tokenRequest($query['code']) + [
            'code_verifier' => str_repeat('verifier_', 8),
        ]));
        $this->assertStatusCode(400, $tokenClient->getResponse());
        $this->assertSame('invalid_request', $tokenClient->getResponseBody()['error']);
    }

    /** @dataProvider invalidChallengeProvider */
    public function testAuthorizationRejectsUnsupportedOrMalformedPkce(?string $challenge, ?string $method): void {
        $request = [];
        if ($challenge !== null) {
            $request['code_challenge'] = $challenge;
        }
        if ($method !== null) {
            $request['code_challenge_method'] = $method;
        }
        $client = $this->authorize($this->authorizationRequest($request));
        $this->assertStatusCode(400, $client->getResponse());
        $response = $client->getResponseBody();
        $this->assertSame('invalid_request', $response['errorCode']);
        parse_str(parse_url($response['redirectUrl'], PHP_URL_QUERY), $query);
        $this->assertSame('invalid_request', $query['error']);
        $this->assertSame('original-state', $query['state']);
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
    }

    public static function invalidChallengeProvider(): array {
        $canonicalChallenge = rtrim(strtr(
            base64_encode(hash('sha256', str_repeat('valid_verifier_', 4), true)),
            '+/',
            '-_'
        ), '=');
        return [
            'plain' => [$canonicalChallenge, 'plain'],
            'unsupported method' => [$canonicalChallenge, 'S512'],
            'missing method' => [$canonicalChallenge, null],
            'short challenge' => ['short', 'S256'],
            'noncanonical S256 challenge' => [str_repeat('a', 43), 'S256'],
            'method without challenge' => [null, 'S256'],
        ];
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

    private function authorize(array $authorizationRequest): TestClient {
        self::ensureKernelShutdown();
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $authorizationRequest, [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        return $client;
    }

    private function tokenRequest(string $code): array {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->oauthClient->getPublicId(),
            'client_secret' => $this->oauthClient->getSecret(),
            'redirect_uri' => 'https://unicorns.pl/callback',
            'code' => $code,
        ];
    }

    private function s256Challenge(string $verifier): string {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
}
