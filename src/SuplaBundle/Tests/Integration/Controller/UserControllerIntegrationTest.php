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

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\Main\AuditEntry;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Message\UserOptOutNotifications;
use SuplaBundle\Repository\AuditEntryRepository;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\TestMailer;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

class UserControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->createDeviceSonoff($location);
    }

    public function testDeletingUserAccountWithInvalidPasswordFails() {
        SuplaAutodiscoverMock::clear(false);
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('PATCH', '/api/users/current', ['action' => 'delete', 'password' => 'xxx']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->user->getId()));
        $this->assertEmpty(TestMailer::getMessages());
    }

    /** @depends testDeletingUserAccountWithInvalidPasswordFails */
    public function testDeletingUserAccountWithNoPasswordFails() {
        SuplaAutodiscoverMock::clear(false);
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('PATCH', '/api/users/current', ['action' => 'delete']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->user->getId()));
        $this->assertEmpty(TestMailer::getMessages());
    }

    /** @depends testDeletingUserAccountWithNoPasswordFails */
    public function testDeletingUserAccount() {
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('PATCH', '/api/users/current', ['action' => 'delete', 'password' => 'supla123']);
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
        $this->assertNotNull($this->user = $this->getEntityManager()->find(User::class, $this->user->getId()));
        $this->flushMessagesQueue($client);
        $this->assertNotEmpty(TestMailer::getMessages());
        $confirmationMessage = TestMailer::getMessages()[0];
        $this->assertEquals($this->user->getEmail(), $confirmationMessage->getTo()[0]->getAddress());
        $this->assertStringContainsString('Removal', $confirmationMessage->getSubject());
        $this->assertStringContainsString($this->user->getToken(), $confirmationMessage->getHtmlBody());
    }

    /** @depends testDeletingUserAccount */
    public function testCheckingIfTokenExistsBadToken() {
        $client = $this->createHttpsClient();
        $client->apiRequest('GET', 'api/account-deletion/aslkjfdalskdjflkasdflkjalsjflaksdjflkajsdfjlkasndfkansdlj');
        $this->assertStatusCode(404, $client->getResponse());
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->user->getId()));
    }

    /** @depends testDeletingUserAccount */
    public function testCheckingIfTokenExists() {
        $client = $this->createHttpsClient();
        $client->apiRequest('GET', 'api/account-deletion/' . $this->user->getToken());
        $this->assertStatusCode(200, $client->getResponse());
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(['exists' => true], $body);
    }

    /** @depends testDeletingUserAccount */
    public function testDeletingWithBadToken() {
        $client = $this->createHttpsClient();
        $client->apiRequest(
            'PATCH',
            'api/account-deletion',
            ['token' => 'asdf', 'username' => 'supler@supla.org', 'password' => 'supla123']
        );
        $this->assertStatusCode(404, $client->getResponse());
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->user->getId()));
    }

    /** @depends testDeletingUserAccount */
    public function testDeletingWithBadPassword() {
        $client = $this->createHttpsClient();
        $client->apiRequest(
            'PATCH',
            'api/account-deletion',
            ['token' => $this->user->getToken(), 'username' => 'supler@supla.org', 'password' => 'xxx']
        );
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->user->getId()));
    }

    /** @depends testDeletingUserAccount */
    public function testDeletingWithBadUsername() {
        $client = $this->createHttpsClient();
        $client->apiRequest(
            'PATCH',
            'api/account-deletion',
            ['token' => $this->user->getToken(), 'username' => 'bad@supla.org', 'password' => 'supla123']
        );
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->user->getId()));
    }

    /** @depends testDeletingWithBadToken */
    public function testDeletingWithGoodToken() {
        $client = $this->createHttpsClient();
        $userId = $this->user->getId();
        $this->user = $this->getEntityManager()->find(User::class, $userId);
        $client->apiRequest(
            'PATCH',
            'api/account-deletion',
            ['token' => $this->user->getToken(), 'username' => 'supler@supla.org', 'password' => 'supla123']
        );
        $this->assertStatusCode(204, $client->getResponse());
        $this->getDoctrine()->resetManager();
        $this->assertNull($this->getEntityManager()->find(User::class, $userId));
    }

    /** @depends testDeletingWithGoodToken */
    public function testDeletingUserAccountReconnectsSuplaServer() {
        $this->assertContains('USER-RECONNECT:1', SuplaServerMock::$executedCommands);
    }

    /** @depends testDeletingWithGoodToken */
    public function testDeletingUserAccountEventIsSavedInAudit() {
        /** @var AuditEntry $lastEntry */
        $entries = self::$container->get(AuditEntryRepository::class)->findAll();
        $lastEntry = end($entries);
        $this->assertEquals(AuditedEvent::USER_ACCOUNT_DELETED, $lastEntry->getEvent()->getId());
        $this->assertEquals(1, $lastEntry->getIntParam());
        $this->assertEquals('supler@supla.org', $lastEntry->getTextParam());
    }

    public function testCannotDeleteAfterOneHour() {
        $this->testDeletingUserAccount();
        TestTimeProvider::setTime('+61 minutes');
        $client = $this->createHttpsClient();
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $client->apiRequest(
            'PATCH',
            'api/account-deletion',
            ['token' => $this->user->getToken(), 'username' => 'supler@supla.org', 'password' => 'supla123']
        );
        $this->assertStatusCode(404, $client->getResponse());
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->user->getId()));
    }

    public function testRequestingDeletionWithoutLogin() {
        $client = $this->createHttpsClient();
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $client->apiRequest(
            'PUT',
            'api/account-deletion',
            ['username' => 'supler@supla.org', 'password' => 'supla123']
        );
        $this->assertStatusCode(204, $client->getResponse());
        $this->flushMessagesQueue($client);
        $this->assertNotEmpty(TestMailer::getMessages());
        $confirmationMessage = TestMailer::getMessages()[0];
        $this->assertEquals($this->user->getEmail(), $confirmationMessage->getTo()[0]->getAddress());
        $this->assertStringContainsString('Removal', $confirmationMessage->getSubject());
        $this->assertStringContainsString($this->user->getToken(), $confirmationMessage->getHtmlBody());
    }

    /** @small */
    public function testGettingUserWithLongUniqueId() {
        SuplaAutodiscoverMock::clear(false);
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('GET', '/api/users/current?include=longUniqueId');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('longUniqueId', $body);
    }

    /** @small */
    public function testGettingUserWithLimitsAndRelationsCount() {
        SuplaAutodiscoverMock::clear(false);
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequestV24('GET', '/api/users/current?include=limits,relationsCount');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('relationsCount', $body);
        $this->assertArrayHasKey('apiRateLimit', $body);
        $this->assertArrayHasKey('limits', $body);
        $relationsCount = $body['relationsCount'];
        $this->assertEquals(2, $relationsCount['locations']);
        $this->assertEquals(1, $relationsCount['accessIds']);
        $this->assertEquals(1, $relationsCount['ioDevices']);
        $this->assertEquals(0, $relationsCount['schedules']);
        $this->assertEquals(3, $relationsCount['channels']);
        $limits = $body['limits'];
        $this->assertArrayHasKey('pushNotificationsPerHour', $limits);
        $this->assertArrayHasKey('left', $limits['pushNotificationsPerHour']);
        $this->assertEquals(100, $limits['pushNotificationsPerHour']['limit']);
    }

    /** @small */
    public function testGettingUserWithSunsetAndSunriseTime() {
        $this->user->setTimezone('Europe/Warsaw');
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequestV24('GET', '/api/users/current?include=sun');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('closestSunset', $body);
        $this->assertArrayHasKey('closestSunrise', $body);
        $this->assertIsInt($body['closestSunset']);
        $this->assertIsInt($body['closestSunrise']);
        $this->assertGreaterThanOrEqual(time() - 86400, $body['closestSunset']);
        $this->assertGreaterThanOrEqual(time() - 86400, $body['closestSunrise']);
        $this->assertNotEquals($body['closestSunset'], $body['closestSunrise']);
    }

    /** @small */
    public function testGettingUserWithSunsetAndSunriseTimeInPolarNight() {
        $this->testNoSunriseSunsetDuringPolarDayOrNight('2023-12-22');
    }

    /** @small */
    public function testGettingUserWithSunsetAndSunriseTimeInPolarDay() {
        $this->testNoSunriseSunsetDuringPolarDayOrNight('2023-06-22');
    }

    private function testNoSunriseSunsetDuringPolarDayOrNight(string $date): void {
        TestTimeProvider::setTime($date);
        $this->user->setTimezone('Arctic/Longyearbyen');
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequestV24('GET', '/api/users/current?include=sun');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('closestSunset', $body);
        $this->assertArrayHasKey('closestSunrise', $body);
        $this->assertNull($body['closestSunset']);
        $this->assertNull($body['closestSunrise']);
    }

    /** @small */
    public function testChangingOptOutNotifications() {
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest(
            'PATCH',
            '/api/users/current',
            ['action' => 'change:optOutNotifications', 'optOutNotifications' => [UserOptOutNotifications::FAILED_AUTH_ATTEMPT]]
        );
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $user = $this->freshEntity($this->user);
        $optOutNotifications = $user->getPreference('optOutNotifications');
        $this->assertEquals([UserOptOutNotifications::FAILED_AUTH_ATTEMPT], $optOutNotifications);
    }

    /** @small */
    public function testChangingOptOutNotificationsToInvalid() {
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest(
            'PATCH',
            '/api/users/current',
            ['action' => 'change:optOutNotifications', 'optOutNotifications' => [UserOptOutNotifications::FAILED_AUTH_ATTEMPT, 'unicorn']]
        );
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    /** @small */
    public function testChangingUserTimezone() {
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest(
            'PATCH',
            '/api/users/current',
            ['action' => 'change:userTimezone', 'timezone' => 'Africa/Ndjamena']
        );
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertContains('USER-ON-SETTINGS-CHANGED:1', SuplaServerMock::$executedCommands);
        $user = $this->freshEntity($this->user);
        $this->assertEquals('Africa/Ndjamena', $user->getTimezone());
        $this->assertEqualsWithDelta(12.11666, $user->getHomeLatitude(), 0.0001);
        $this->assertEqualsWithDelta(15.05, $user->getHomeLongitude(), 0.01);
    }

    /** @small */
    public function testChangingUserPassword() {
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest(
            'PATCH',
            '/api/users/current',
            ['action' => 'change:password', 'oldPassword' => 'supla123', 'newPassword' => 'Gb;Bq?8V#}WkX"2f']
        );
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
    }

    /**
     * @small
     * @depends testChangingUserPassword
     */
    public function testChangingUserPasswordClearsWebappTokens() {
        $this->assertEquals(0, $this->getEntityManager()->getRepository(AccessToken::class)->count([]));
    }
}
