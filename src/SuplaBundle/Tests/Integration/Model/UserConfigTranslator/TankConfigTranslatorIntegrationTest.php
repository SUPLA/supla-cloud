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
class TankConfigTranslatorIntegrationTest extends IntegrationTestCase {
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

    public function testTranslatingDefaultConfig() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tank = $device->getChannels()[0];
        $config = $this->translator->getConfig($tank);
        $this->assertArrayHasKey('warningAboveLevel', $config);
        $this->assertArrayHasKey('alarmAboveLevel', $config);
        $this->assertArrayHasKey('warningBelowLevel', $config);
        $this->assertArrayHasKey('alarmBelowLevel', $config);
        $this->assertArrayHasKey('muteAlarmSoundWithoutAdditionalAuth', $config);
        $this->assertArrayHasKey('fillLevelReportingInFullRange', $config);
        $this->assertEquals(20, $config['warningAboveLevel']);
        $this->assertEquals(30, $config['alarmAboveLevel']);
        $this->assertEquals(40, $config['warningBelowLevel']);
        $this->assertEquals(50, $config['alarmBelowLevel']);
        $this->assertEquals(false, $config['muteAlarmSoundWithoutAdditionalAuth']);
        $this->assertEquals(false, $config['fillLevelReportingInFullRange']);
    }

    public function testTranslatingLevelSensors() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tank = $device->getChannels()[0];
        $tank->setUserConfigValue('sensors', [['channelNo' => 1, 'fillLevel' => 10], ['channelNo' => 3, 'fillLevel' => 55]]);
        $config = $this->translator->getConfig($tank);
        $this->assertArrayHasKey('levelSensorChannelIds', $config);
        $this->assertArrayHasKey('levelSensors', $config);
        $this->assertCount(2, $config['levelSensorChannelIds']);
        $this->assertEquals($device->getChannels()[1]->getId(), $config['levelSensorChannelIds'][0]);
        $this->assertEquals($device->getChannels()[3]->getId(), $config['levelSensorChannelIds'][1]);
        $this->assertCount(2, $config['levelSensors']);
        $this->assertEquals(['channelId' => $device->getChannels()[1]->getId(), 'fillLevel' => 10], $config['levelSensors'][0]);
    }

    public function testOrderingLevelSensorsByFillLevel() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tank = $device->getChannels()[0];
        $tank->setUserConfigValue('sensors', [['channelNo' => 1, 'fillLevel' => 55], ['channelNo' => 3, 'fillLevel' => 10]]);
        $config = $this->translator->getConfig($tank);
        $this->assertEquals([$device->getChannels()[3]->getId(), $device->getChannels()[1]->getId()], $config['levelSensorChannelIds']);
        $this->assertEquals(
            [$device->getChannels()[3]->getId(), $device->getChannels()[1]->getId()],
            array_column($config['levelSensors'], 'channelId')
        );
    }

    public function testSettingLevelSensors() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tank = $device->getChannels()[0];
        $this->translator->setConfig($tank, ['levelSensors' => [
            ['channelId' => $device->getChannels()[2]->getId(), 'fillLevel' => 15],
            ['channelId' => $device->getChannels()[5]->getId(), 'fillLevel' => 75],
            ['channelId' => $device->getChannels()[6]->getId(), 'fillLevel' => 95],
        ]]);
        $sensorsConfig = $tank->getUserConfigValue('sensors');
        $this->assertEquals([
            ['channelNo' => 2, 'fillLevel' => 15],
            ['channelNo' => 5, 'fillLevel' => 75],
            ['channelNo' => 6, 'fillLevel' => 95],
        ], $sensorsConfig);
    }

    public function testSettingFullConfig() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tank = $device->getChannels()[0];
        $this->translator->setConfig($tank, [
            'levelSensors' => [
                ['channelId' => $device->getChannels()[5]->getId(), 'fillLevel' => 75],
                ['channelId' => $device->getChannels()[6]->getId(), 'fillLevel' => 95],
                ['channelId' => $device->getChannels()[7]->getId(), 'fillLevel' => 99],
            ],
            'warningAboveLevel' => 0,
            'alarmAboveLevel' => 75,
            'warningBelowLevel' => 95,
            'alarmBelowLevel' => 99,
            'muteAlarmSoundWithoutAdditionalAuth' => true,
        ]);
        $this->assertEquals(0, $tank->getUserConfigValue('warningAboveLevel'));
        $this->assertEquals(75, $tank->getUserConfigValue('alarmAboveLevel'));
        $this->assertEquals(95, $tank->getUserConfigValue('warningBelowLevel'));
        $this->assertEquals(99, $tank->getUserConfigValue('alarmBelowLevel'));
        $this->assertTrue($tank->getUserConfigValue('muteAlarmSoundWithoutAdditionalAuth'));
    }

    public function testClearsWarningIfLevelIsRemovedSettingFullConfig() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tank = $device->getChannels()[0];
        $this->translator->setConfig($tank, [
            'levelSensors' => [['channelId' => $device->getChannels()[2]->getId(), 'fillLevel' => 15]],
            'warningAboveLevel' => 15,
        ]);
        $this->translator->setConfig($tank, [
            'levelSensors' => [['channelId' => $device->getChannels()[2]->getId(), 'fillLevel' => 25]],
        ]);
        $this->assertNull($tank->getUserConfigValue('warningAboveLevel'));
    }

    public function testCannotSetWarningToNotExistingLevel() {
        $this->expectExceptionMessage('is not an element');
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tank = $device->getChannels()[0];
        $this->translator->setConfig($tank, [
            'levelSensors' => [['channelId' => $device->getChannels()[2]->getId(), 'fillLevel' => 15]],
            'warningAboveLevel' => 25,
        ]);
    }

    public function testCannotSetTheSameLevelForTwoSensors() {
        $this->expectExceptionMessage('must have different fill');
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tank = $device->getChannels()[0];
        $this->translator->setConfig($tank, [
            'levelSensors' => [
                ['channelId' => $device->getChannels()[2]->getId(), 'fillLevel' => 15],
                ['channelId' => $device->getChannels()[3]->getId(), 'fillLevel' => 15],
            ],
        ]);
    }

    public function testCanSetWarningToNotExistingLevelIfFullLevelReporting() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceSeptic($this->location);
        $tankWithFullLevelReporting = $device->getChannels()[22];
        $this->translator->setConfig($tankWithFullLevelReporting, [
            'levelSensors' => [['channelId' => $device->getChannels()[2]->getId(), 'fillLevel' => 15]],
            'warningAboveLevel' => 25,
        ]);
        $this->assertEquals(25, $tankWithFullLevelReporting->getUserConfigValue('warningAboveLevel'));
    }
}
