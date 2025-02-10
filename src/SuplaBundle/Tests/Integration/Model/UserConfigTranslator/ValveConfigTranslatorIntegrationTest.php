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

use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaDeveloperBundle\DataFixtures\ORM\DevicesFixture;

/** @small */
class ValveConfigTranslatorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    private ?SubjectConfigTranslator $translator = null;
    private ?User $user;
    private ?Location $location;

    public function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
    }

    /** @before */
    public function init() {
        $this->translator = self::$container->get(SubjectConfigTranslator::class);
    }

    public function testTranslatingValveSensors() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $valve = $device->getChannels()[11];
        $valve->setUserConfigValue('sensorChannelNumbers', [12, 13, 15]);
        $config = $this->translator->getConfig($valve);
        $this->assertArrayHasKey('floodSensorChannelIds', $config);
        $this->assertCount(3, $config['floodSensorChannelIds']);
        $this->assertEquals($device->getChannels()[12]->getId(), $config['floodSensorChannelIds'][0]);
        $this->assertEquals($device->getChannels()[13]->getId(), $config['floodSensorChannelIds'][1]);
        $this->assertEquals($device->getChannels()[15]->getId(), $config['floodSensorChannelIds'][2]);
    }

    public function testSettingValveSensors() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $valve = $device->getChannels()[11];
        $this->translator->setConfig($valve, ['floodSensorChannelIds' => [
            $device->getChannels()[12]->getId(),
            $device->getChannels()[17]->getId(),
        ]]);
        $channelNos = $valve->getUserConfigValue('sensorChannelNumbers');
        $this->assertCount(2, $channelNos);
        $this->assertEquals([12, 17], $channelNos);
    }

    public function testSettingValveSensorsEmpty() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $valve = $device->getChannels()[11];
        $this->translator->setConfig($valve, ['floodSensorChannelIds' => []]);
        $channelNos = $valve->getUserConfigValue('sensorChannelNumbers');
        $this->assertEmpty($channelNos);
    }

    public function testCanChooseAnyBinarySensorForValveSensor() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $valve = $device->getChannels()[11];
        $this->translator->setConfig($valve, ['floodSensorChannelIds' => [
            $device->getChannels()[4]->getId(),
            $device->getChannels()[17]->getId(),
        ]]);
        $channelNos = $valve->getUserConfigValue('sensorChannelNumbers');
        $this->assertCount(2, $channelNos);
        $this->assertEquals([4, 17], $channelNos);
    }

    public function testCantChooseNotBinarySensorForValveSensor() {
        $this->expectExceptionMessage('Only binary sensors can be chosen for valve sensors');
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $valve = $device->getChannels()[11];
        $this->translator->setConfig($valve, ['floodSensorChannelIds' => [
            $device->getChannels()[0]->getId(),
            $device->getChannels()[17]->getId(),
        ]]);
    }

    public function testCantChooseInvalidChannelIdForValveSensor() {
        $this->expectExceptionMessage('Invalid channel ID given');
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $valve = $device->getChannels()[11];
        $this->translator->setConfig($valve, ['floodSensorChannelIds' => [666]]);
    }
}
