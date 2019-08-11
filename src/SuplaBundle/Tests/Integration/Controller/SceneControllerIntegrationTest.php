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
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpFoundation\Response;

class SceneControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var IODeviceChannelGroup */
    private $channelGroup;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::DIMMERANDRGBLIGHTING],
        ]);
        $this->channelGroup = new IODeviceChannelGroup($this->user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[1],
        ]);
        $this->getEntityManager()->persist($this->channelGroup);
        $this->getEntityManager()->flush();
    }

    private function createScene(array $data = []): Response {
        $data = array_merge([
            'caption' => 'My scene',
            'enabled' => true,
//            'subjectType' => 'channel',
//            'subjectId' => $this->device->getChannels()[0]->getId(),
        ], $data);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV23('POST', '/api/scenes', $data);
        return $client->getResponse();
    }

    public function testCreatingScene() {
        $response = $this->createScene();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My scene', $content['caption']);
        $this->assertEmpty($content['operationsIds']);
        return $content;
    }

    /** @depends testCreatingScene */
    public function testGettingSceneDetails($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV23('GET', '/api/scenes/' . $id . '?include=subject,operations');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals('My scene', $content['caption']);
        $this->assertEmpty($content['operationsIds']);
        $this->assertEmpty($content['operations']);
    }
}
