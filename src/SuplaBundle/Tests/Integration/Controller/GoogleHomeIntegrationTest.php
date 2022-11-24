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
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class GoogleHomeIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;
    /** @var IODeviceChannelGroup */
    private $channelGroup;
    /** @var Scene */
    private $scene;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::DIMMERANDRGBLIGHTING],
            [ChannelType::VALVEOPENCLOSE, ChannelFunction::VALVEOPENCLOSE],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::RELAY, ChannelFunction::NONE],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
        ]);
        $this->channelGroup = new IODeviceChannelGroup($this->user, $this->location, [$this->device->getChannels()[0]]);
        $this->persist($this->channelGroup);
        $this->scene = new Scene($this->device->getLocation());
        $this->scene->setOpeartions([new SceneOperation($this->channelGroup, ChannelFunctionAction::TURN_ON())]);
        $this->persist($this->scene);
    }

    public function testChangingChannelStateWithGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'turn-on',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'SET-CHAR-VALUE:1,1,1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testChangingChannelGroupStateWithGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/channel-groups/1', json_encode([
            'action' => 'turn-on',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'SET-CG-CHAR-VALUE:1,1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testExecutingSceneWithGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'EXECUTE-SCENE:1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }
}
