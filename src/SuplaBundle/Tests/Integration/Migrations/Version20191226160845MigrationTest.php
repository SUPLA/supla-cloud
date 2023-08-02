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

namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\UserIcon;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/**
 * @see Version20191226160845
 * @see UpdateChannelConfigsInitializationCommand
 */
class Version20191226160845MigrationTest extends DatabaseMigrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    /** @before */
    public function prepare() {
        $this->loadDumpV23();
        $this->initialize();
    }

    public function testChannelParamsConfigIsSet() {
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 150);
        $paramsTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->assertNotEmpty($channel->getUserConfig());
        $this->assertEquals($paramsTranslator->getConfig($channel), $channel->getUserConfig());
    }

    public function testChannelParamsConfigDefault() {
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 151);
        $this->assertEquals(
            [
                'openingSensorChannelId' => null,
                'openingSensorSecondaryChannelId' => null,
                'relayTimeMs' => 500,
                'timeSettingAvailable' => true,
                'numberOfAttemptsToOpen' => 5,
                'numberOfAttemptsToClose' => 5,
                'stateVerificationMethodActive' => false,
                'closingRule' => [],
            ],
            array_diff_key($channel->getUserConfig(), ['googleHome' => '', 'alexa' => '']),
        );
    }

    public function testElectricityMeterFunctionIsMigrated() {
        // 66 -> ELECTRICITY_METER / ELECTRICITY_METER
        // 67 -> IMPULSECOUNTER / ELECTRICITY_METER
        $electricityMeter = $this->getEntityManager()->find(IODeviceChannel::class, 66);
        $electricityMeterImpulseCounter = $this->getEntityManager()->find(IODeviceChannel::class, 67);
        $this->assertEquals($electricityMeter->getType()->getId(), ChannelType::ELECTRICITYMETER);
        $this->assertEquals($electricityMeterImpulseCounter->getType()->getId(), ChannelType::IMPULSECOUNTER);
        $this->assertEquals($electricityMeter->getFunction()->getId(), ChannelFunction::ELECTRICITYMETER);
        $this->assertEquals($electricityMeterImpulseCounter->getFunction()->getId(), ChannelFunction::IC_ELECTRICITYMETER);
    }

    public function testMigratedChannelIconsForElectricityMeter() {
        $iconForEm = $this->getEntityManager()->find(UserIcon::class, 1);
        $this->assertEquals(ChannelFunction::ELECTRICITYMETER, $iconForEm->getFunction()->getId());
        $iconForEmImpulseCounter = $this->getEntityManager()->find(UserIcon::class, 2);
        $this->assertNotNull($iconForEmImpulseCounter);
        $this->assertEquals(ChannelFunction::IC_ELECTRICITYMETER, $iconForEmImpulseCounter->getFunction()->getId());
        $this->assertEquals($iconForEm->getUser()->getId(), $iconForEmImpulseCounter->getUser()->getId());
        $electricityMeter = $this->getEntityManager()->find(IODeviceChannel::class, 66);
        $electricityMeterImpulseCounter = $this->getEntityManager()->find(IODeviceChannel::class, 67);
        $this->assertEquals($iconForEm->getId(), $electricityMeter->getUserIcon()->getId());
        $this->assertEquals($iconForEmImpulseCounter->getId(), $electricityMeterImpulseCounter->getUserIcon()->getId());
    }
}
