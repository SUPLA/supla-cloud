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

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ControllingTheGateSecondaryRelatedSensorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var SubjectConfigTranslator */
    private $paramsTranslator;
    /** @var \SuplaBundle\Entity\Main\User */
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
    public function init() {
        $this->paramsTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->simulateAuthentication($this->user);
    }

    public function testSettingSecondarySensorForChannel() {
        $channel = $this->device->getChannels()[0];
        $this->paramsTranslator->setConfig(
            $channel,
            ['openingSensorSecondaryChannelId' => $this->device->getChannels()[1]->getId()]
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[1]->getParam2());
        $this->assertEquals($this->device->getChannels()[1]->getId(), $this->device->getChannels()[0]->getParam3());
    }

    public function testSettingChannelForSecondarySensor() {
        $channel = $this->device->getChannels()[1];
        $this->paramsTranslator->setConfig(
            $channel,
            ['openingSensorSecondaryChannelId' => $this->device->getChannels()[0]->getId()]
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[0]->getParam3());
        $this->assertEquals($this->device->getChannels()[0]->getId(), $this->device->getChannels()[1]->getParam2());
    }

    public function testSettingPrimaryAndSecondarySensorForChannel() {
        $channel = $this->device->getChannels()[0];
        $this->paramsTranslator->setConfig(
            $channel,
            [
                'openingSensorChannelId' => $this->device->getChannels()[1]->getId(),
                'openingSensorSecondaryChannelId' => $this->device->getChannels()[2]->getId(),
            ]
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[1]->getParam1());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[2]->getParam2());
        $this->assertEquals($this->device->getChannels()[1]->getId(), $this->device->getChannels()[0]->getParam2());
        $this->assertEquals($this->device->getChannels()[2]->getId(), $this->device->getChannels()[0]->getParam3());
    }

    public function testSettingTheSamePrimaryAndSecondarySensorForChannelDoesNotSetSecondary() {
        $channel = $this->device->getChannels()[0];
        $this->paramsTranslator->setConfig(
            $channel,
            [
                'openingSensorChannelId' => $this->device->getChannels()[1]->getId(),
                'openingSensorSecondaryChannelId' => $this->device->getChannels()[1]->getId(),
            ]
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $channelConfig = $this->paramsTranslator->getConfig($this->device->getChannels()[0]);
        $sensorConfig = $this->paramsTranslator->getConfig($this->device->getChannels()[1]);
        $this->assertNotEquals($channelConfig['openingSensorChannelId'], $channelConfig['openingSensorSecondaryChannelId']);
        $this->assertNotEquals($sensorConfig['openingSensorChannelId'], $sensorConfig['openingSensorSecondaryChannelId']);
    }

    public function testSettingPrimaryAndSecondaryChannelForSensor() {
        $channel = $this->device->getChannels()[1];
        $this->paramsTranslator->setConfig(
            $channel,
            [
                'openingSensorChannelId' => $this->device->getChannels()[0]->getId(),
                'openingSensorSecondaryChannelId' => $this->device->getChannels()[3]->getId(),
            ]
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[0]->getParam2());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[3]->getParam3());
        $this->assertEquals($this->device->getChannels()[0]->getId(), $this->device->getChannels()[1]->getParam1());
        $this->assertEquals($this->device->getChannels()[3]->getId(), $this->device->getChannels()[1]->getParam2());
    }

    public function testSettingTheSamePrimaryAndSecondaryChannelForSensorDoesNotSetSecondary() {
        $channel = $this->device->getChannels()[1];
        $this->paramsTranslator->setConfig(
            $channel,
            [
                'openingSensorChannelId' => $this->device->getChannels()[0]->getId(),
                'openingSensorSecondaryChannelId' => $this->device->getChannels()[0]->getId(),
            ]
        );
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $this->assertEquals($channel->getId(), $this->device->getChannels()[0]->getParam2());
        $this->assertEquals(0, $this->device->getChannels()[0]->getParam3());
        $this->assertEquals($this->device->getChannels()[0]->getId(), $this->device->getChannels()[1]->getParam1());
        $this->assertEquals(0, $this->device->getChannels()[1]->getParam2());
    }

    public function testSettingUsedPrimarySensorForChannel() {
        $channel = $this->device->getChannels()[0];
        $this->paramsTranslator->setConfig($channel, ['openingSensorChannelId' => $this->device->getChannels()[1]->getId()]);
        $channel = $this->device->getChannels()[3];
        $this->paramsTranslator->setConfig($channel, ['openingSensorChannelId' => $this->device->getChannels()[1]->getId()]);
        $this->device = $this->getEntityManager()->find(IODevice::class, $this->device->getId());
        $configFirst = $this->paramsTranslator->getConfig($this->device->getChannels()[0]);
        $configSecond = $this->paramsTranslator->getConfig($this->device->getChannels()[3]);
        $this->assertEquals($this->device->getChannels()[1]->getId(), $configSecond['openingSensorChannelId']);
        $this->assertNull($configFirst['openingSensorChannelId']);
    }
}
