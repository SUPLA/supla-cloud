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

namespace SuplaApiBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;

class ApiUserControllerIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;

    const EMAIL = 'cooltester@supla.org';
    const PASSWORD = 'alamakota';

    /** @var User */
    private $createdUser;

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
        $client->request('POST', '/auth/login', [
            '_username' => $this->createdUser->getEmail(),
            '_password' => self::PASSWORD,
        ]);
        $this->assertCount(1, $client->getCrawler()->filter('#login-error'));
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
        $client->request('POST', '/auth/login', [
            '_username' => $this->createdUser->getEmail(),
            '_password' => self::PASSWORD,
        ]);
        $this->assertCount(0, $client->getCrawler()->filter('#login-error'));
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

    public function testCanLoginAfterPasswordResetAsked() {
        $this->testGeneratesForgottenPasswordTokenForValidUser();
        $client = $this->createHttpsClient();
        $client->request('POST', '/auth/login', [
            '_username' => $this->createdUser->getEmail(),
            '_password' => self::PASSWORD,
        ]);
        $this->assertCount(0, $client->getCrawler()->filter('#login-error'));
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
        $client->request('POST', '/auth/login', [
            '_username' => $this->createdUser->getEmail(),
            '_password' => 'alamapsa',
        ]);
        $this->assertCount(0, $client->getCrawler()->filter('#login-error'));
    }

    public function testCannotLoginWithOldPasswordAfterReset() {
        $this->testCanResetPasswordWithValidToken();
        $client = $this->createHttpsClient();
        $client->request('POST', '/auth/login', [
            '_username' => $this->createdUser->getEmail(),
            '_password' => self::PASSWORD,
        ]);
        $this->assertCount(1, $client->getCrawler()->filter('#login-error'));
    }

    private function createHttpsClient(): TestClient {
        $client = self::createClient([], [
            'HTTPS' => true,
            'HTTP_Accept' => 'application/json',
        ]);
        $client->followRedirects();
        return $client;
    }
}
