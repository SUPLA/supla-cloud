<?php

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\SuplaApiHelper;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

class DebugOAuthFlowIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    public function testStatelessAuthorizationBindsS256ParametersToTheIssuedCode(): void {
        $clientManager = self::getContainer()->get(ClientManagerInterface::class);
        $oauthClient = $clientManager->createClient();
        $oauthClient->setRedirectUris(['https://unicorns.pl']);
        $oauthClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $clientManager->updateClient($oauthClient);
        $this->createConfirmedUser();

        $verifier = str_repeat('diagnostic_verifier_', 3);
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $authorization = [
            'client_id' => $oauthClient->getPublicId(),
            'redirect_uri' => 'https://unicorns.pl',
            'response_type' => 'code',
            'scope' => 'account_r',
            'state' => 'stateless-state',
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ];
        $client = $this->createHttpsClient(false);
        $authorizationUrl = '/oauth/v2/auth?' . http_build_query($authorization);
        $client->request('GET', $authorizationUrl);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame($authorizationUrl, $client->getRequest()->getRequestUri());
        $this->assertFalse($client->getResponse()->headers->has('Set-Cookie'));

        $client->apiRequest('POST', '/api/oauth/authorize', $authorization + [
            'decision' => 'approve',
            'username' => 'supler@supla.org',
            'password' => 'supla123',
        ]);
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('authorized', $result['outcome']);
        $this->assertStringContainsString('state=stateless-state', $result['redirectUrl']);
        $authCodes = self::getContainer()->get('doctrine')->getRepository(\App\Entity\Main\OAuth\AuthCode::class)->findByClient($oauthClient);
        $this->assertCount(1, $authCodes);
        $this->assertSame($challenge, $authCodes[0]->getCodeChallenge());
        $this->assertSame('S256', $authCodes[0]->getCodeChallengeMethod());
    }
}
