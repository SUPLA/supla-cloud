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

namespace SuplaBundle\Tests\Integration\Model\UserConfigTranslator;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ControllingAnyLockTimeIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var SubjectConfigTranslator */
    private $paramsTranslator;
    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    public function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
        ]);
        $this->paramsTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->simulateAuthentication($this->user);
    }

    public function testUpdatingControllingTheDoorLockTime() {
        $channel = $this->device->getChannels()[0];
        $this->assertEquals(0, $channel->getParam1());
        $this->paramsTranslator->setConfig($channel, ['relayTimeMs' => 0]);
        $this->assertEquals(500, $channel->getParam1());
        $this->paramsTranslator->setConfig($channel, ['relayTimeMs' => 1000]);
        $this->assertEquals(1000, $channel->getParam1());
        $this->paramsTranslator->setConfig($channel, ['relayTimeMs' => -5]);
        $this->assertEquals(500, $channel->getParam1());
        $this->paramsTranslator->setConfig($channel, ['relayTimeMs' => 1000000]);
        $this->assertEquals(120000, $channel->getParam1());
    }

    public function testUpdatingControllingTheGateTime() {
        $channel = $this->device->getChannels()[1];
        $this->assertEquals(0, $channel->getParam1());
        $this->paramsTranslator->setConfig($channel, ['relayTimeMs' => 0]);
        $this->assertEquals(500, $channel->getParam1());
        $this->paramsTranslator->setConfig($channel, ['relayTimeMs' => 1000]);
        $this->assertEquals(1000, $channel->getParam1());
        $this->paramsTranslator->setConfig($channel, ['relayTimeMs' => -5]);
        $this->assertEquals(500, $channel->getParam1());
        $this->paramsTranslator->setConfig($channel, ['relayTimeMs' => 1000000]);
        $this->assertEquals(2000, $channel->getParam1());
    }
}
