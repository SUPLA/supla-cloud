<?php

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\SuplaApiHelper;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;

class DebugOAuthFlowIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    public function testConsentFormCarriesS256ParametersAfterLogin(): void {
        $clientManager = self::getContainer()->get(ClientManagerInterface::class);
        $oauthClient = $clientManager->createClient();
        $oauthClient->setRedirectUris(['https://unicorns.pl']);
        $oauthClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $clientManager->updateClient($oauthClient);
        $this->createConfirmedUser();

        $verifier = str_repeat('diagnostic_verifier_', 3);
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $client = $this->createHttpsClient();
        $client->followRedirects();
        $client->request('GET', '/oauth/v2/auth?' . http_build_query([
            'client_id' => $oauthClient->getPublicId(),
            'redirect_uri' => 'https://unicorns.pl',
            'response_type' => 'code',
            'scope' => 'account_r',
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ]));

        $crawler = $client->apiRequest('POST', '/oauth/v2/auth_login', [
            '_username' => 'supler@supla.org',
            '_password' => 'supla123',
        ]);
        $challengeInput = $crawler->filterXPath('//input[contains(@name, "[code_challenge]")]');
        $methodInput = $crawler->filterXPath('//input[contains(@name, "[code_challenge_method]")]');
        $this->assertCount(1, $challengeInput);
        $this->assertSame($challenge, $challengeInput->attr('value'));
        $this->assertCount(1, $methodInput);
        $this->assertSame('S256', $methodInput->attr('value'));
    }
}
