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

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ReactionControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;
    /** @var IODeviceChannel */
    private $thermometer;
    /** @var IODeviceChannel */
    private $lightswitch;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::HUMIDITYANDTEMPSENSOR, ChannelFunction::HUMIDITYANDTEMPERATURE],
        ]);
        $this->lightswitch = $this->device->getChannels()[0];
        $this->thermometer = $this->device->getChannels()[1];
    }

    public function testSavingReactions() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', "/api/channels/{$this->thermometer->getId()}/reactions", [
            [
                'subjectId' => $this->lightswitch->getId(), 'subjectType' => $this->lightswitch->getOwnSubjectType(),
                'actionId' => ChannelFunctionAction::TURN_ON,
                'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'temperature', 'resume' => ['ge' => 20]]],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
    }
}
