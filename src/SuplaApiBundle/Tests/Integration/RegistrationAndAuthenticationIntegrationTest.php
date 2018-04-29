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

namespace SuplaApiBundle\Tests\Integration;

use SuplaApiBundle\Model\Audit\Audit;
use SuplaBundle\Entity\AuditEntry;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\AuthenticationFailureReason;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

class RegistrationAndAuthenticationIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;

    const EMAIL = 'cooltester@supla.org';
    const PASSWORD = 'alamakota';

    /** @var User */
    private $createdUser;

    /** @var Audit */
    private $audit;

    /** @before */
    public function initAudit() {
        $this->audit = $this->container->get(Audit::class);
    }

    public function testCannotLoginIfDoesNotExist() {
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client);
        $entry = $this->getLatestAuditEntry();
        $this->assertEquals(AuditedEvent::AUTHENTICATION_FAILURE(), $entry->getEvent());
        $this->assertEquals(self::EMAIL, $entry->getTextParam());
        $this->assertNull($entry->getUser());
        $this->assertEquals(AuthenticationFailureReason::NOT_EXISTS, $entry->getIntParam());
        $this->assertEmpty(TestMailer::getMessages());
    }

    public function testCreatingUser() {
        $userData = [
            'email' => self::EMAIL,
            'regulationsAgreed' => true,
            'password' => self::PASSWORD,
            'timezone' => 'Europe/Warsaw',
        ];
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/web-api/register', $userData);
        $this->assertStatusCode(201, $client->getResponse());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->createdUser = $this->getDoctrine()->getRepository(User::class)->findOneByEmail(self::EMAIL);
        $this->assertEquals($this->createdUser->getId(), $data['id']);
        $this->assertEquals($this->createdUser->getEmail(), $data['email']);
        $this->assertNotNull($this->createdUser);
        $this->assertFalse($this->createdUser->isEnabled());
    }

    public function testCannotLoginIfNotConfirmed() {
        $this->testCreatingUser();
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client, self::EMAIL, self::PASSWORD);
    }

    public function testNoFailedAttemptNotificationIfAccountNotConfirmed() {
        $this->testCreatingUser();
        TestMailer::reset();
        $this->assertFailedLoginRequest($this->createHttpsClient(), self::EMAIL, self::PASSWORD);
        $this->assertEmpty(TestMailer::getMessages());
    }

    public function testSavesIncorrectLoginAttemptInAudit() {
        $this->testCannotLoginIfNotConfirmed();
        $entry = $this->getLatestAuditEntry();
        $this->assertEquals(AuditedEvent::AUTHENTICATION_FAILURE(), $entry->getEvent());
        $this->assertEquals($this->createdUser->getUsername(), $entry->getTextParam());
        $this->assertEquals(AuthenticationFailureReason::DISABLED, $entry->getIntParam());
        $this->assertNotNull($entry->getUser());
        $this->assertEquals($this->createdUser->getId(), $entry->getUser()->getId());
    }

    public function testSendsEmailWithConfirmationToken() {
        $this->testCreatingUser();
        $messages = TestMailer::getMessages();
        $this->assertCount(1, $messages);
        $confirmationMessage = end($messages);
        $this->assertArrayHasKey(self::EMAIL, $confirmationMessage->getTo());
        $this->assertContains('Activation', $confirmationMessage->getSubject());
        $this->assertContains($this->createdUser->getToken(), $confirmationMessage->getBody());
    }

    public function testConfirmingWithBadToken() {
        $this->testCreatingUser();
        $client = $this->createHttpsClient();
        $client->request('PATCH', '/web-api/confirm/aslkjfdalskdjflkasdflkjalsjflaksdjflkajsdfjlkasndfkansdljf');
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testConfirmingWithGoodToken() {
        $this->testCreatingUser();
        $client = $this->createHttpsClient();
        $client->request('PATCH', '/web-api/confirm/' . $this->createdUser->getToken());
        $this->assertStatusCode('2XX', $client->getResponse());
        $this->getDoctrine()->getManager()->refresh($this->createdUser);
        $this->assertTrue($this->createdUser->isEnabled());
        $this->assertEmpty($this->createdUser->getToken());
    }

    public function testConfirmingTwiceWithGoodTokenIsForbidden() {
        $this->testCreatingUser();
        $client = $this->createHttpsClient();
        $client->request('PATCH', '/web-api/confirm/' . $this->createdUser->getToken());
        $this->assertStatusCode('2XX', $client->getResponse());
        $client->request('PATCH', '/web-api/confirm/' . $this->createdUser->getToken());
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCanLoginIfConfirmed() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $this->assertSuccessfulLoginRequest($client);
    }

    public function testSavesCorrectLoginAttemptInAudit() {
        $this->testCanLoginIfConfirmed();
        $entry = $this->getLatestAuditEntry();
        $this->assertEquals(AuditedEvent::AUTHENTICATION_SUCCESS(), $entry->getEvent());
        $this->assertEquals($this->createdUser->getUsername(), $entry->getTextParam());
        $this->assertNotNull($entry->getUser());
        $this->assertEquals($this->createdUser->getId(), $entry->getUser()->getId());
    }

    public function testCannotLoginWithInvalidPassword() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client);
        $entry = $this->getLatestAuditEntry();
        $this->assertEquals(AuditedEvent::AUTHENTICATION_FAILURE(), $entry->getEvent());
        $this->assertEquals($this->createdUser->getUsername(), $entry->getTextParam());
        $this->assertNotNull($entry->getUser());
        $this->assertEquals($this->createdUser->getId(), $entry->getUser()->getId());
        $this->assertEquals(AuthenticationFailureReason::BAD_CREDENTIALS, $entry->getIntParam());
    }

    public function testSendsInvalidAuthenticationWarningByEmail() {
        $this->testCannotLoginWithInvalidPassword();
        $messages = TestMailer::getMessages();
        $this->assertGreaterThanOrEqual(1, count($messages));
        $warnMessage = end($messages);
        $this->assertArrayHasKey(self::EMAIL, $warnMessage->getTo());
        $this->assertContains('unsuccessful login', $warnMessage->getSubject());
        $this->assertContains('1.2.3.4', $warnMessage->getBody());
    }

    public function testPasswordResetForUnknownUserFailsSilently() {
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/web-api/forgotten-password', ['email' => 'zamel@supla.org']);
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testGeneratesForgottenPasswordTokenForValidUser() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/web-api/forgotten-password', ['email' => self::EMAIL]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->getDoctrine()->getManager()->refresh($this->createdUser);
        $this->assertNotEmpty($this->createdUser->getToken());
    }

    public function testSendsEmailWithResetPasswordToken() {
        $this->testGeneratesForgottenPasswordTokenForValidUser();
        $messages = TestMailer::getMessages();
        $message = end($messages);
        $this->assertArrayHasKey(self::EMAIL, $message->getTo());
        $this->assertContains('Password reset', $message->getSubject());
        $this->assertContains($this->createdUser->getToken(), $message->getBody());
    }

    public function testCanLoginAfterPasswordResetAsked() {
        $this->testGeneratesForgottenPasswordTokenForValidUser();
        $client = $this->createHttpsClient();
        $this->assertSuccessfulLoginRequest($client);
    }

    public function testCannotResetPasswordWithInvalidToken() {
        $this->testGeneratesForgottenPasswordTokenForValidUser();
        $client = $this->createHttpsClient();
        $client->apiRequest('PUT', '/web-api/forgotten-password/asdfasdfasdfasdf', ['password' => 'alamapsa']);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCanResetPasswordWithValidToken() {
        $this->testGeneratesForgottenPasswordTokenForValidUser();
        $client = $this->createHttpsClient();
        $client->apiRequest('PUT', '/web-api/forgotten-password/' . $this->createdUser->getToken(), ['password' => 'alamapsa']);
        $this->assertStatusCode(200, $client->getResponse());
        $oldPassword = $this->createdUser->getPassword();
        $this->getDoctrine()->getManager()->refresh($this->createdUser);
        $this->assertNotEquals($oldPassword, $this->createdUser->getPassword());
        $this->assertEmpty($this->createdUser->getToken());
    }

    public function testCannotResetPasswordTwiceWithTheSameToken() {
        $this->testGeneratesForgottenPasswordTokenForValidUser();
        $client = $this->createHttpsClient();
        $client->apiRequest('PUT', '/web-api/forgotten-password/' . $this->createdUser->getToken(), ['password' => 'alamapsa']);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequest('PUT', '/web-api/forgotten-password/' . $this->createdUser->getToken(), ['password' => 'alamapsa']);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCanLoginWithResetPassword() {
        $this->testCanResetPasswordWithValidToken();
        $client = $this->createHttpsClient();
        $this->assertSuccessfulLoginRequest($client, self::EMAIL, 'alamapsa');
    }

    public function testCannotLoginWithOldPasswordAfterReset() {
        $this->testCanResetPasswordWithValidToken();
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client, self::EMAIL, self::PASSWORD);
    }

    public function testCanLoginWithInvalidAndThenValidPassword() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client);
        $this->assertSuccessfulLoginRequest($client);
        $this->assertCount(2, $this->audit->getRepository()->findAll());
    }

    public function testAccountIsBlockedAfterThreeUnsuccessfulLogins() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client, self::EMAIL, self::PASSWORD);
        $this->assertCount(4, $this->audit->getRepository()->findAll());
        $latestEntry = $this->getLatestAuditEntry();
        $this->assertEquals(AuthenticationFailureReason::BLOCKED, $latestEntry->getIntParam());
    }

    public function testAccountIsBlockedAfterThreeSuccessfulAndUnsuccessfulLogins() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $this->assertSuccessfulLoginRequest($client);
        $this->assertSuccessfulLoginRequest($client);
        $this->assertSuccessfulLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client, self::EMAIL, self::PASSWORD);
        $this->assertCount(7, $this->audit->getRepository()->findAll());
        $latestEntry = $this->getLatestAuditEntry();
        $this->assertEquals(AuthenticationFailureReason::BLOCKED, $latestEntry->getIntParam());
    }

    public function testIsNotBlockedIfSuccessfulLoginInMeantime() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client);
        $this->assertSuccessfulLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertSuccessfulLoginRequest($client);
    }

    public function testBlockingNotExistingUser() {
        $client = $this->createHttpsClient();
        $email = 'other@supla.org';
        $this->assertFailedLoginRequest($client, $email);
        $this->assertFailedLoginRequest($client, $email);
        $this->assertFailedLoginRequest($client, $email);
        $this->assertEquals(AuthenticationFailureReason::NOT_EXISTS, $this->getLatestAuditEntry()->getIntParam());
        $this->assertFailedLoginRequest($client, $email);
        $this->assertEquals(AuthenticationFailureReason::BLOCKED, $this->getLatestAuditEntry()->getIntParam());
    }

    public function testSuccessfulLoginsOfOtherDoesNotInfluenceBlocks() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $email = 'other@supla.org';
        $this->assertFailedLoginRequest($client, $email);
        $this->assertFailedLoginRequest($client, $email);
        $this->assertFailedLoginRequest($client, $email);
        $this->assertFailedLoginRequest($client, $email);
        $this->assertSuccessfulLoginRequest($client);
        $this->assertFailedLoginRequest($client, $email);
        $this->assertEquals(AuthenticationFailureReason::BLOCKED, $this->getLatestAuditEntry()->getIntParam());
    }

    public function testResettingPasswordUnlocksAccount() {
        $this->testGeneratesForgottenPasswordTokenForValidUser();
        $client = $this->createHttpsClient();
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->assertFailedLoginRequest($client);
        }
        $client->apiRequest('PUT', '/web-api/forgotten-password/' . $this->createdUser->getToken(), ['password' => 'alamapsa']);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSuccessfulLoginRequest($client, self::EMAIL, 'alamapsa');
    }

    public function testAccountIsUnlockedAfterTimePasses() {
        TestTimeProvider::setTime('-25 minutes');
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        TestTimeProvider::reset();
        $this->assertSuccessfulLoginRequest($client);
    }

    public function testAccountIsLockedOnlyOnSuspiciousIpAddress() {
        $this->testConfirmingWithGoodToken();
        $client = $this->createHttpsClient();
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $this->assertFailedLoginRequest($client);
        $client = $this->createHttpsClient('10.0.0.1');
        $this->assertSuccessfulLoginRequest($client);
    }

    private function assertSuccessfulLoginRequest(TestClient $client, $username = null, $password = null) {
        $this->loginRequest($client, $username, $password);
        $this->assertCount(0, $client->getCrawler()->filter('#login-error'));
    }

    private function assertFailedLoginRequest(TestClient $client, $username = null, $password = null) {
        $password = $password ?: 'invalidpassword';
        $this->loginRequest($client, $username, $password);
        $this->assertCount(1, $client->getCrawler()->filter('#login-error'));
    }

    private function loginRequest(TestClient $client, $username = null, $password = null) {
        $username = $username ?: self::EMAIL;
        $password = $password ?: self::PASSWORD;
        $client->request('POST', '/auth/login', [
            '_username' => $username,
            '_password' => $password,
        ]);
    }

    private function createHttpsClient($ipAddress = '1.2.3.4'): TestClient {
        $client = self::createClient([], [
            'HTTPS' => true,
            'HTTP_Accept' => 'application/json',
            'REMOTE_ADDR' => $ipAddress,
        ]);
        $client->followRedirects();
        return $client;
    }

    private function getLatestAuditEntry(): AuditEntry {
        $entries = $this->audit->getRepository()->findAll();
        $this->assertGreaterThanOrEqual(1, count($entries));
        /** @var AuditEntry $entry */
        $entry = end($entries);
        return $entry;
    }
}
