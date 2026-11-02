<?php

namespace App\Tests\Integration\Auth;

use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\OAuth\ApiClientAuthorization;
use App\Entity\Main\User;
use App\Supla\SuplaAutodiscoverMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\OAuthHelper;
use App\Tests\Integration\Traits\UserFixtures;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

/** @small */
class OAuthAuthorizationRequestIntegrationTest extends IntegrationTestCase {
    use OAuthHelper;
    use UserFixtures;

    public function testPresentationReturnsValidatedApplicationAndNormalizedScopesWithoutChangingUrl(): void {
        SuplaAutodiscoverMock::$publicClients['100_public'] = [
            'name' => 'Permission test application',
            'description' => 'A test application description',
            'redirectUris' => ['http://suplascripts.local/auth'],
        ];
        $query = 'client_id=100_public&redirect_uri=http%3A%2F%2Fsuplascripts.local%2Fauth'
            . '&response_type=code&scope=channels_rw%20account_r&state=keep%2Bthis%3D%26that';

        $browser = $this->createHttpsClient(false);
        $browser->request('GET', '/api/oauth/authorization-request?' . $query);

        $response = $browser->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('/api/oauth/authorization-request?' . $query, $browser->getRequest()->getRequestUri());
        self::assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $body = json_decode($response->getContent(), true);
        self::assertSame('Permission test application', $body['application']['name']);
        self::assertSame('A test application description', $body['application']['description']);
        self::assertSame(['channels_rw', 'account_r'], $body['scopes']['requested']);
        self::assertSame(['channels_r'], $body['scopes']['implicit']);
        self::assertSame('unauthenticated', $body['status']);
        self::assertArrayNotHasKey('email', $body);
        self::assertFalse($body['canAutoAuthorize']);
    }

    public function testPresentationReturnsEmptyScopeListsWhenScopeIsOmittedOrEmpty(): void {
        SuplaAutodiscoverMock::$publicClients['100_public'] = [
            'name' => 'Permission test application',
            'redirectUris' => ['http://suplascripts.local/auth'],
        ];

        foreach (['', '&scope='] as $scopeParameter) {
            $browser = $this->createHttpsClient(false);
            $browser->request('GET', '/api/oauth/authorization-request?client_id=100_public'
                . '&redirect_uri=http%3A%2F%2Fsuplascripts.local%2Fauth&response_type=code' . $scopeParameter);

            self::assertSame(200, $browser->getResponse()->getStatusCode());
            $body = json_decode($browser->getResponse()->getContent(), true);
            self::assertSame([], $body['scopes']['requested']);
            self::assertSame([], $body['scopes']['implicit']);
        }
    }

    public function testPresentationRejectsAnInvalidFullAuthorizationRequest(): void {
        SuplaAutodiscoverMock::$publicClients['100_public'] = [
            'name' => 'Permission test application',
            'redirectUris' => ['http://suplascripts.local/auth'],
        ];

        $browser = $this->createHttpsClient(false);
        $browser->request('GET', '/api/oauth/authorization-request?client_id=100_public'
            . '&redirect_uri=http%3A%2F%2Fsuplascripts.local%2Fauth&response_type=code&scope=not_a_scope');

        self::assertSame(400, $browser->getResponse()->getStatusCode());
        self::assertSame('invalid_scope', json_decode($browser->getResponse()->getContent(), true)['error']);
    }

    public function testPresentationRejectsDuplicateEncodedAuthorizationParametersBeforeResolvingMetadata(): void {
        $browser = $this->createHttpsClient(false);
        $browser->request('GET', '/api/oauth/authorization-request?client_id=missing&client_id=100_public'
            . '&redirect_uri=http%3A%2F%2Fsuplascripts.local%2Fauth&response_type=code'
            . '&%73cope=channels_r&scope=channels_rw');

        self::assertSame(400, $browser->getResponse()->getStatusCode());
        self::assertSame(
            ['error' => 'invalid_request'],
            json_decode($browser->getResponse()->getContent(), true)
        );
    }

    public function testVerifiedWebappIdentityGetsDistinctConsentAndExistingGrantStates(): void {
        $manager = self::getContainer()->get(ClientManagerInterface::class);
        /** @var ApiClient $client */
        $client = $manager->createClient();
        $client->setPublicClientId('100_public');
        $client->setRedirectUris(['http://suplascripts.local/auth']);
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]);
        $manager->updateClient($client);
        $clientId = $client->getId();
        SuplaAutodiscoverMock::$publicClients['100_public'] = [
            'name' => 'Permission test application',
            'redirectUris' => ['http://suplascripts.local/auth'],
        ];
        $user = $this->createConfirmedUser();
        $userId = $user->getId();
        $requestUrl = '/api/oauth/authorization-request?client_id=' . rawurlencode($client->getPublicId())
            . '&redirect_uri=' . rawurlencode('http://suplascripts.local/auth')
            . '&response_type=code&scope=channels_rw';

        $browser = $this->createHttpsClient(false);
        $browser->request('GET', $requestUrl, [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . base64_encode($user->getEmail())]);
        $consent = json_decode($browser->getResponse()->getContent(), true);
        self::assertSame(200, $browser->getResponse()->getStatusCode());
        self::assertSame('consent_required', $consent['status']);
        self::assertSame($user->getEmail(), $consent['email']);
        self::assertFalse($consent['canAutoAuthorize']);
        $user = $this->getEntityManager()->find(User::class, $userId);
        self::assertNotNull($user);
        $client = $this->getEntityManager()->find(ApiClient::class, $clientId);
        self::assertNotNull($client);
        self::assertNull($this->getEntityManager()->getRepository(ApiClientAuthorization::class)
            ->findOneByUserAndApiClient($user, $client));

        $this->createApiClientAuthorization($client, $user, 'channels_rw');
        $browser = $this->createHttpsClient(false);
        $browser->request('GET', $requestUrl, [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . base64_encode($user->getEmail())]);
        $authorized = json_decode($browser->getResponse()->getContent(), true);
        self::assertSame(200, $browser->getResponse()->getStatusCode());
        self::assertSame('already_authorized', $authorized['status']);
        self::assertTrue($authorized['canAutoAuthorize']);
    }
}
