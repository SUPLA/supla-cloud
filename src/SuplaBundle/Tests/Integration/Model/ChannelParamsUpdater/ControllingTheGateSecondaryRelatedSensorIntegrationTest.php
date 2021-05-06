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

namespace SuplaBundle\Tests\Integration\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ControllingTheGateSecondaryRelatedSensorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelParamsUpdater */
    private $updater;
    /** @var User */
    private $user;

    public function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::SENSORNC, ChannelFunction::OPENINGSENSOR_GATE],
            [ChannelType::SENSORNC, ChannelFunction::OPENINGSENSOR_GATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
        ]);
    }

    /** @before */
    public function initialize() {
        $this->updater = $this->container->get(ChannelParamsUpdater::class);
        $this->simulateAuthentication($this->user);
    }

    public function testSettingSecondarySensorForChannel() {
        $channel = $this->device->getChannels()[0];
        $this->updater->updateChannelParams($channel, new IODeviceChannelWithParams(0, 0, $this->device->getChannels()[1]->getId()));
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[1]->getParam2());
        $this->assertEquals($this->device->getChannels()[1]->getId(), $this->device->getChannels()[0]->getParam3());
    }

    public function testSettingChannelForSecondarySensor() {
        $channel = $this->device->getChannels()[1];
        $this->updater->updateChannelParams($channel, new IODeviceChannelWithParams(0, $this->device->getChannels()[0]->getId()));
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[0]->getParam3());
        $this->assertEquals($this->device->getChannels()[0]->getId(), $this->device->getChannels()[1]->getParam2());
    }

    public function testSettingPrimaryAndSecondarySensorForChannel() {
        $channel = $this->device->getChannels()[0];
        $this->updater->updateChannelParams(
            $channel,
            new IODeviceChannelWithParams(0, $this->device->getChannels()[1]->getId(), $this->device->getChannels()[2]->getId())
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[1]->getParam1());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[2]->getParam2());
        $this->assertEquals($this->device->getChannels()[1]->getId(), $this->device->getChannels()[0]->getParam2());
        $this->assertEquals($this->device->getChannels()[2]->getId(), $this->device->getChannels()[0]->getParam3());
    }

    public function testSettingTheSamePrimaryAndSecondarySensorForChannelDoesNotSetSecondary() {
        $channel = $this->device->getChannels()[0];
        $this->updater->updateChannelParams(
            $channel,
            new IODeviceChannelWithParams(0, $this->device->getChannels()[1]->getId(), $this->device->getChannels()[1]->getId())
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[1]->getParam1());
        $this->assertEquals(0, $this->device->getChannels()[1]->getParam2());
        $this->assertEquals($this->device->getChannels()[1]->getId(), $this->device->getChannels()[0]->getParam2());
        $this->assertEquals(0, $this->device->getChannels()[0]->getParam3());
    }

    public function testSettingPrimaryAndSecondaryChannelForSensor() {
        $channel = $this->device->getChannels()[1];
        $this->updater->updateChannelParams(
            $channel,
            new IODeviceChannelWithParams($this->device->getChannels()[0]->getId(), $this->device->getChannels()[3]->getId())
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[0]->getParam2());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[3]->getParam3());
        $this->assertEquals($this->device->getChannels()[0]->getId(), $this->device->getChannels()[1]->getParam1());
        $this->assertEquals($this->device->getChannels()[3]->getId(), $this->device->getChannels()[1]->getParam2());
    }

    public function testSettingTheSamePrimaryAndSecondaryChannelForSensorDoesNotSetSecondary() {
        $channel = $this->device->getChannels()[1];
        $this->updater->updateChannelParams(
            $channel,
            new IODeviceChannelWithParams($this->device->getChannels()[0]->getId(), $this->device->getChannels()[0]->getId())
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[0]->getParam2());
        $this->assertEquals(0, $this->device->getChannels()[0]->getParam3());
        $this->assertEquals($this->device->getChannels()[0]->getId(), $this->device->getChannels()[1]->getParam1());
        $this->assertEquals(0, $this->device->getChannels()[1]->getParam2());
    }
}
