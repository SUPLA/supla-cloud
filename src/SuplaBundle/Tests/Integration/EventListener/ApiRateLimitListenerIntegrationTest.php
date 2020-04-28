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

namespace SuplaBundle\Tests\Integration\EventListener;

use enform\models\Company;
use Monolog\Handler\TestHandler;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Auth\SuplaOAuth2;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitRule;
use SuplaBundle\EventListener\ApiRateLimit\GlobalApiRateLimit;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

/** @small */
class ApiRateLimitListenerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var AccessToken */
    private $token;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->token = $this->container->get(SuplaOAuth2::class)
            ->createPersonalAccessToken($this->user, 'TEST', new OAuthScope(OAuthScope::getSupportedScopes()));
        $this->getEntityManager()->persist($this->token);
        $this->getEntityManager()->flush();
        $this->executeCommand('cache:pool:clear api_rate_limit');
    }

    /** @after */
    protected function restoreGlobalRateLimit() {
        $this->changeUserApiRateLimit(null);
        $this->executeCommand('cache:pool:clear api_rate_limit');
    }

    public function testWebappTokenIgnoresApiQuotaGlobal() {
        $client = $this->createAuthenticatedClient($this->user, true);
        $client->getContainer()->set(GlobalApiRateLimit::class, new GlobalApiRateLimit('5/1000'));
        for ($i = 0; $i < 10; $i++) {
            $client->apiRequestV24('GET', '/api/locations');
            $response = $client->getResponse();
            $this->assertStatusCode(200, $response);
        }
    }

    public function testTooManyRequestsGlobal() {
        $client = $this->getClientWithPersonalToken();
        $client->getContainer()->set(GlobalApiRateLimit::class, new GlobalApiRateLimit('5/1000'));
        for ($i = 0; $i < 5; $i++) {
            $client->apiRequestV24('GET', '/api/locations');
            $response = $client->getResponse();
            $this->assertStatusCode(200, $response);
        }
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(Response::HTTP_TOO_MANY_REQUESTS, $response);
    }

    public function testCanReadServerStatusIfTooManyRequestsGlobal() {
        $client = $this->getClientWithPersonalToken();
        $client->getContainer()->set(GlobalApiRateLimit::class, new GlobalApiRateLimit('5/1000'));
        for ($i = 0; $i < 5; $i++) {
            $client->apiRequestV24('GET', '/api/locations');
            $response = $client->getResponse();
            $this->assertStatusCode(200, $response);
        }
        $client->apiRequestV24('GET', '/api/server-status');
        $response = $client->getResponse();
        $this->assertStatusCode(Response::HTTP_OK, $response);
    }

    public function testCanReadHomePageIfTooManyRequestsGlobal() {
        $client = $this->getClientWithPersonalToken();
        $client->getContainer()->set(GlobalApiRateLimit::class, new GlobalApiRateLimit('5/1000'));
        for ($i = 0; $i < 5; $i++) {
            $client->apiRequestV24('GET', '/api/locations');
            $response = $client->getResponse();
            $this->assertStatusCode(200, $response);
        }
        $client->apiRequestV24('GET', '/');
        $response = $client->getResponse();
        $this->assertStatusCode(Response::HTTP_OK, $response);
    }

    public function testOkIfFitsInLimits() {
        $client = $this->createAuthenticatedClient($this->user, true);
        for ($i = 0; $i < 10; $i++) {
            $client->apiRequestV24('GET', '/api/locations');
            $response = $client->getResponse();
            $this->assertStatusCode(200, $response);
        }
    }

    public function testTooManyRequestsPerUser() {
        $this->changeUserApiRateLimit();
        $client = $this->getClientWithPersonalToken();
        for ($i = 0; $i < 5; $i++) {
            $client->apiRequestV24('GET', '/api/locations');
            $response = $client->getResponse();
            $this->assertStatusCode(200, $response);
        }
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(Response::HTTP_TOO_MANY_REQUESTS, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $content['status']);
    }

    public function testWebappTokenDoesNotRaisesApiRateLimit() {
        $this->changeUserApiRateLimit();
        $client = $this->createAuthenticatedClient($this->user);
        for ($i = 0; $i < 10; $i++) {
            $client->apiRequestV24('GET', '/api/locations');
            $response = $client->getResponse();
            $this->assertStatusCode(200, $response);
        }
    }

    public function testSendingRateLimitHeaders() {
        $this->changeUserApiRateLimit();
        $client = $this->getClientWithPersonalToken();
        $now = time();
        TestTimeProvider::setTime($now);
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertEquals(5, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(4, $response->headers->get('X-RateLimit-Remaining'));
        $this->assertEquals($now + 10, $response->headers->get('X-RateLimit-Reset'));
        TestTimeProvider::setTime($now + 3);
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertEquals(5, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(3, $response->headers->get('X-RateLimit-Remaining'));
        $this->assertEquals($now + 10, $response->headers->get('X-RateLimit-Reset'));
    }

    public function testResettingRateLimit() {
        $this->changeUserApiRateLimit();
        $client = $this->getClientWithPersonalToken();
        $now = time();
        TestTimeProvider::setTime($now - 11);
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertEquals(5, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(4, $response->headers->get('X-RateLimit-Remaining'));
        $this->assertEquals($now + 10 - 11, $response->headers->get('X-RateLimit-Reset'));
        TestTimeProvider::setTime($now);
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertEquals(5, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(4, $response->headers->get('X-RateLimit-Remaining'));
        $this->assertEquals($now + 10, $response->headers->get('X-RateLimit-Reset'));
    }

    public function testLoggingRateLimitExcess() {
        $this->changeUserApiRateLimit();
        $client = $this->getClientWithPersonalToken();
        $now = time();
        TestTimeProvider::setTime($now - 11);
        $client->apiRequestV24('GET', '/api/locations');
        $client->apiRequestV24('GET', '/api/locations');
        $client->apiRequestV24('GET', '/api/locations');
        $client->apiRequestV24('GET', '/api/locations');
        $client->apiRequestV24('GET', '/api/locations');
        $client->apiRequestV24('GET', '/api/locations');
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(429, $response);
        TestTimeProvider::setTime($now);
        $client->apiRequestV24('GET', '/api/locations');
        /** @var TestHandler $logger */
        $logger = $client->getContainer()->get('monolog.handler.test_handler');
        $this->assertTrue($logger->hasWarningThatContains('exceeded API rate limit'));
        $this->assertTrue($logger->hasWarningThatPasses(function ($log) {
            $context = $log['context'] ?? [];
            return ($context['limit'] ?? 0) === 5 && ($context['excess'] ?? 0) === 2 && ($context['key'] ?? 0) === 'user_1';
        }));
    }

    public function testLimitsOfOneUserDoesNotInfluenceOtherUser() {
        $anotherUser = $this->createConfirmedUser('another@supla.org');
        $token = $this->container->get(SuplaOAuth2::class)
            ->createPersonalAccessToken($anotherUser, 'TEST', new OAuthScope(OAuthScope::getSupportedScopes()));
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
        $client = $this->getClientWithPersonalToken();
        $client->apiRequestV24('GET', '/api/locations');
        $client->apiRequestV24('GET', '/api/locations');
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertEquals($response->headers->get('X-RateLimit-Limit') - 3, $response->headers->get('X-RateLimit-Remaining'));
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $token->getToken());
        $client->request('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertEquals($response->headers->get('X-RateLimit-Limit') - 1, $response->headers->get('X-RateLimit-Remaining'));
    }

    public function testChangingLimitForUserIsAppliedImmediately() {
        $client = $this->getClientWithPersonalToken();
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertGreaterThan(500, $response->headers->get('X-RateLimit-Limit'));
        $this->assertGreaterThan(500, $response->headers->get('X-RateLimit-Remaining'));

        $command = $this->application->find('supla:user:change-limits');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['', '', '', '', '', '', '', '5/10']); // first n: no backup, second: no initial user
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername()]);
        $this->assertEquals(0, $exitCode);

        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertEquals(5, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(4, $response->headers->get('X-RateLimit-Remaining'));
    }

    public function testDirectLinksUsesLimitOfOwner() {
        $this->changeUserApiRateLimit();
        $device = $this->createDeviceSonoff($this->createLocation($this->user));
        $directLink = new DirectLink($device->getChannels()[0]);
        $directLink->setAllowedActions([ChannelFunctionAction::READ()]);
        $slug = $directLink->generateSlug(new BCryptPasswordEncoder(4));
        $this->getEntityManager()->persist($directLink);
        $this->getEntityManager()->flush();
        $client = $this->createClient();
        $client->request('GET', "/direct/{$directLink->getId()}/$slug/read");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertEquals(5, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(4, $response->headers->get('X-RateLimit-Remaining'));
        $client->enableProfiler();
        $client->request('GET', "/direct/{$directLink->getId()}/$slug/read");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertEquals(5, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(3, $response->headers->get('X-RateLimit-Remaining'));
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
        $this->assertContains('<direct-link-execution-result', $response->getContent());
        return $slug;
    }

    /** @depends testDirectLinksUsesLimitOfOwner */
    public function testDoesNotContactsSuplaServerWhenUsingDirectLinkAndApiLimitReached(string $slug) {
        $this->changeUserApiRateLimit('1/10');
        $client = $this->createClient();
        $client->request('GET', "/direct/1/$slug/read");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertEquals(1, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(0, $response->headers->get('X-RateLimit-Remaining'));
        $client->enableProfiler();
        $client->request('GET', "/direct/1/$slug/read");
        $response = $client->getResponse();
        $this->assertStatusCode(429, $response);
        $this->assertNotContains('<direct-link-execution-result', $response->getContent());
        $this->assertEquals(1, $response->headers->get('X-RateLimit-Limit'));
        $this->assertEquals(0, $response->headers->get('X-RateLimit-Remaining'));
        $commands = $this->getSuplaServerCommands($client);
        $this->assertEmpty($commands);
    }

    private function changeUserApiRateLimit($rateLimit = '5/10') {
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->user->setApiRateLimit($rateLimit ? new ApiRateLimitRule($rateLimit) : null);
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
    }

    private function getClientWithPersonalToken(AccessToken $token = null): TestClient {
        $token = $token ?: $this->token;
        return self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token->getToken(), 'HTTPS' => true]);
    }
}
