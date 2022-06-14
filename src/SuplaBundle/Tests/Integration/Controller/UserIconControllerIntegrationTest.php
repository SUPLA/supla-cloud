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

use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/** @small */
class UserIconControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;
    /** @var AccessToken */
    private $peronsalToken;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ]);
    }

    public function testCreatingIconForThermometer() {
        $client = $this->createAuthenticatedClient($this->user);
        $image = new UploadedFile(\AppKernel::ROOT_PATH . '/../web/assets/img/devices.png', 'devices.png');
        $client->apiRequestV24('POST', '/api/user-icons', ['function' => ChannelFunction::THERMOMETER], [], ['image1' => $image]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        return $content['id'];
    }

    /** @depends testCreatingIconForThermometer */
    public function testGettingThermometerIcon(int $iconId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/user-icons/' . $iconId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content['images']);
    }

    public function testCreatingIconForLightSwitch() {
        $client = $this->createAuthenticatedClient($this->user);
        $image1 = new UploadedFile(\AppKernel::ROOT_PATH . '/../web/assets/img/devices.png', 'devices.png');
        $image2 = new UploadedFile(\AppKernel::ROOT_PATH . '/../web/assets/img/user.png', 'user.png');
        $client->apiRequestV24(
            'POST',
            '/api/user-icons',
            ['function' => ChannelFunction::LIGHTSWITCH],
            [],
            ['image1' => $image1, 'image2' => $image2]
        );
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        return $content['id'];
    }

    /** @depends testCreatingIconForLightSwitch */
    public function testGettingLightSwitchIcon(int $iconId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/user-icons/' . $iconId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content['images']);
    }

    /** @depends testCreatingIconForThermometer */
    public function testAssigningUserIcon(int $iconId) {
        $channel = $this->device->getChannels()[1];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $channel->getId(), ['userIconId' => $iconId]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $channel = $this->freshEntity($channel);
        $this->assertNotNull($channel->getUserIcon());
        return $iconId;
    }

    /** @depends testAssigningUserIcon */
    public function testDeletingUserIcon(int $iconId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('DELETE', '/api/user-icons/' . $iconId);
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
        $channel = $this->freshEntity($this->device->getChannels()[1]);
        $this->assertNull($channel->getUserIcon());
    }

    public function testCreatingIconWithNotEnoughFiles() {
        $client = $this->createAuthenticatedClient($this->user);
        $image1 = new UploadedFile(\AppKernel::ROOT_PATH . '/../web/assets/img/devices.png', 'devices.png');
        $client->apiRequestV24('POST', '/api/user-icons', ['function' => ChannelFunction::LIGHTSWITCH], [], ['image1' => $image1]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testAssigningUserIconWithWrongFunction() {
        $iconId = $this->testCreatingIconForLightSwitch();
        $channel = $this->device->getChannels()[1];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $channel->getId(), ['userIconId' => $iconId]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }
}
