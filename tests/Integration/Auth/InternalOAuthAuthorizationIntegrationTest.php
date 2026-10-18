<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Auth;

use App\Entity\Main\OAuth\AccessToken;
use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\OAuth\ApiClientAuthorization;
use App\Entity\Main\OAuth\AuthCode;
use App\Entity\Main\User;
use App\Exception\ApiException;
use App\Supla\SuplaAutodiscoverMock;
use App\Supla\SuplaServerMock;
use App\Tests\Integration\IntegrationTestCase;
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

    public function testVerifiedTargetTokenReissuesSingleUseCodeForCompleteGrant(): void {
        $this->user->addApiClientAuthorization($this->oauthClient, 'account_r offline_access');
        $this->persist($this->user);

        self::ensureKernelShutdown();
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->targetTokenRequest(), [], [], [
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
        $this->exchangeCode($query['code']);

        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->oauthClient->getPublicId(),
            'client_secret' => $this->oauthClient->getSecret(),
            'redirect_uri' => 'https://unicorns.pl/callback',
            'code' => $query['code'],
        ]);
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

    /** @dataProvider invalidTargetTokenProvider */
    public function testUnverifiableTargetTokensCannotAuthorize(string $case): void {
        $this->user->addApiClientAuthorization($this->oauthClient, 'account_r offline_access');
        $this->persist($this->user);
        $tokenString = base64_encode($this->user->getEmail());
        $token = $this->getDoctrine()->getRepository(AccessToken::class)->findOneBy(['token' => $tokenString]);
        $this->assertNotNull($token);
        switch ($case) {
            case 'expired':
                $this->getEntityManager()->getConnection()->executeQuery(
                    'UPDATE supla_oauth_access_tokens SET expires_at = :exp WHERE token = :token',
                    ['exp' => time() - 3600, 'token' => $tokenString]
                );
                break;
            case 'revoked':
                $this->getEntityManager()->remove($token);
                $this->flush();
                break;
        }

        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->targetTokenRequest([
            'target_token' => $case === 'unknown' ? 'TKN_other-instance-token' : $tokenString,
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(401, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'invalid_token'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $authorizations = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll();
        $this->assertCount(1, $authorizations);
        $this->assertSame('account_r offline_access', $authorizations[0]->getScope());
    }

    public static function invalidTargetTokenProvider(): array {
        return [
            'unknown' => ['unknown'],
            'expired' => ['expired'],
            'revoked' => ['revoked'],
        ];
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
            'target_token array' => [['target_token' => ['supler@supla.org']]],
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

    public function testTargetTokenOfDifferentUserCannotAuthorizeOtherAccount(): void {
        $other = $this->createConfirmedUser('other@supla.org');
        $other->addApiClientAuthorization($this->oauthClient, 'account_r offline_access');
        $this->persist($other);

        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->targetTokenRequest([
            'username' => $this->user->getEmail(),
            'target_token' => base64_encode('other@supla.org'),
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(401, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'invalid_token'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertNull($this->getDoctrine()->getRepository(ApiClientAuthorization::class)
            ->findOneByUserAndApiClient($this->user, $this->oauthClient));
        $otherAuthorization = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)
            ->findOneByUserAndApiClient($other, $this->oauthClient);
        $this->assertNotNull($otherAuthorization);
        $this->assertSame('account_r offline_access', $otherAuthorization->getScope());
    }

    public function testDisabledUserTargetTokenIsRejected(): void {
        $this->user->addApiClientAuthorization($this->oauthClient, 'account_r offline_access');
        $this->user->setEnabled(false);
        $this->persist($this->user);

        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->targetTokenRequest(), [], [], [
            'HTTP_SUPLA-Broker-Token' => 'Bearer BROKER',
        ]);
        $this->assertStatusCode(409, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'disabled'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $this->assertCount(1, $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll());
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

    public function testPasswordAndTargetTokenEvidenceIsRejectedAsAmbiguous(): void {
        $this->user->addApiClientAuthorization($this->oauthClient, 'account_r offline_access');
        $this->persist($this->user);

        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->targetTokenRequest([
            'password' => 'supla123',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'invalid_request'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $authorizations = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll();
        $this->assertCount(1, $authorizations);
        $this->assertSame('account_r offline_access', $authorizations[0]->getScope());
    }

    public function testRepeatedTargetTokenAuthorizationsKeepOneGrantAndSingleMqttEffect(): void {
        $this->user->addApiClientAuthorization($this->oauthClient, 'account_r');
        $this->persist($this->user);

        $codes = [];
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $client = $this->createHttpsClient(false);
            $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->targetTokenRequest([
                'scope' => 'account_r mqtt_broker',
            ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
            $this->assertStatusCode(401, $client->getResponse());
            $this->assertSame(['outcome' => 'error', 'errorCode' => 'password_required'], $client->getResponseBody());
        }

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $client = $this->createHttpsClient(false);
            $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->targetTokenRequest([
                'scope' => 'account_r',
            ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
            $this->assertStatusCode(200, $client->getResponse());
            $this->assertSame('authorized', $client->getResponseBody()['outcome']);
            parse_str(parse_url($client->getResponseBody()['redirectUrl'], PHP_URL_QUERY), $query);
            $codes[] = $query['code'];
        }

        $this->assertCount(3, array_unique($codes));
        $user = $this->freshEntity($this->user);
        $this->assertTrue($user->isMqttBrokerEnabled());
        $authorizations = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll();
        $this->assertCount(1, $authorizations);
        $this->assertSame('account_r', $authorizations[0]->getScope());
        $commands = array_filter(SuplaServerMock::$executedCommands, fn($command) => str_contains($command, 'ON-MQTT-SETTINGS-CHANGED'));
        $this->assertSame([], array_values($commands));
        foreach ($codes as $code) {
            $this->exchangeCode($code);
        }
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

    public function testPartialGrantCoverageRequiresPasswordThenMergesScopes(): void {
        $this->user->addApiClientAuthorization($this->oauthClient, 'account_r');
        $this->persist($this->user);

        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->targetTokenRequest([
            'scope' => 'account_r account_rw',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(401, $client->getResponse());
        $this->assertSame(['outcome' => 'error', 'errorCode' => 'password_required'], $client->getResponseBody());
        $this->assertCount(0, $this->getDoctrine()->getRepository(AuthCode::class)->findAll());
        $authorizations = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll();
        $this->assertCount(1, $authorizations);
        $this->assertSame('account_r', $authorizations[0]->getScope());

        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/api/internal/oauth/authorize', $this->authorizationRequest([
            'scope' => 'account_r account_rw',
        ]), [], [], ['HTTP_SUPLA-Broker-Token' => 'Bearer BROKER']);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame('authorized', $client->getResponseBody()['outcome']);
        $authorizations = $this->getDoctrine()->getRepository(ApiClientAuthorization::class)->findAll();
        $this->assertCount(1, $authorizations);
        $this->assertSame('account_r account_rw', $authorizations[0]->getScope());
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

    private function targetTokenRequest(array $overrides = []): array {
        return array_merge([
            'client_id' => $this->oauthClient->getPublicId(),
            'redirect_uri' => 'https://unicorns.pl/callback',
            'response_type' => 'code',
            'scope' => 'account_r offline_access',
            'state' => 'original-state',
            'target_token' => base64_encode($this->user->getEmail()),
            'decision' => 'approve',
        ], $overrides);
    }

    private function exchangeCode(string $code): void {
        self::ensureKernelShutdown();
        $tokenClient = $this->createClient();
        $tokenClient->apiRequest('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->oauthClient->getPublicId(),
            'client_secret' => $this->oauthClient->getSecret(),
            'redirect_uri' => 'https://unicorns.pl/callback',
            'code' => $code,
        ]);
        $this->assertStatusCode(200, $tokenClient->getResponse());
        $this->assertArrayHasKey('access_token', $tokenClient->getResponseBody());
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
