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
class RelatedMeterUserConfigTranslatorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var SubjectConfigTranslator */
    private $configTranslator;
    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    public function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_ELECTRICITYMETER],
            [ChannelType::RELAY, ChannelFunction::HEATORCOLDSOURCESWITCH],
        ]);
    }

    /** @before */
    public function init() {
        $this->configTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->simulateAuthentication($this->user);
    }

    public function testSettingRelatedChannelForPowerswitch() {
        $powerswitch = $this->device->getChannels()[0];
        $em = $this->device->getChannels()[2];
        $this->configTranslator->setConfig($powerswitch, ['relatedMeterChannelId' => $em->getId()]);
        $this->assertEquals($em->getId(), $this->configTranslator->getConfig($this->freshEntity($powerswitch))['relatedMeterChannelId']);
        $this->assertEquals($powerswitch->getId(), $this->configTranslator->getConfig($this->freshEntity($em))['relatedRelayChannelId']);
    }

    public function testSettingRelatedChannelForElectricityMeter() {
        $powerswitch = $this->device->getChannels()[0];
        $em = $this->device->getChannels()[2];
        $this->configTranslator->setConfig($em, ['relatedRelayChannelId' => $powerswitch->getId()]);
        $this->assertEquals($em->getId(), $this->configTranslator->getConfig($this->freshEntity($powerswitch))['relatedMeterChannelId']);
        $this->assertEquals($powerswitch->getId(), $this->configTranslator->getConfig($this->freshEntity($em))['relatedRelayChannelId']);
    }

    public function testClearingRelatedChannelForElectricityMeter() {
        $powerswitch = $this->device->getChannels()[0];
        $em = $this->device->getChannels()[2];
        $this->configTranslator->setConfig($em, ['relatedRelayChannelId' => $powerswitch->getId()]);
        $this->configTranslator->setConfig($em, ['relatedRelayChannelId' => null]);
        $this->assertNull($this->configTranslator->getConfig($this->freshEntity($powerswitch))['relatedMeterChannelId']);
        $this->assertNull($this->configTranslator->getConfig($this->freshEntity($em))['relatedRelayChannelId']);
    }

    public function testClearingRelatedChannelForSwitch() {
        $powerswitch = $this->device->getChannels()[0];
        $em = $this->device->getChannels()[2];
        $this->configTranslator->setConfig($powerswitch, ['relatedMeterChannelId' => $em->getId()]);
        $this->configTranslator->setConfig($powerswitch, ['relatedMeterChannelId' => null]);
        $this->assertNull($this->configTranslator->getConfig($this->freshEntity($powerswitch))['relatedMeterChannelId']);
        $this->assertNull($this->configTranslator->getConfig($this->freshEntity($em))['relatedRelayChannelId']);
    }

    public function testChangingRelatedChannelForPowerswitch() {
        $powerswitch = $this->device->getChannels()[0];
        $em = $this->device->getChannels()[2];
        $em2 = $this->device->getChannels()[3];
        $this->configTranslator->setConfig($powerswitch, ['relatedMeterChannelId' => $em->getId()]);
        $this->configTranslator->setConfig($powerswitch, ['relatedMeterChannelId' => $em2->getId()]);
        $this->assertNull($this->configTranslator->getConfig($this->freshEntity($em))['relatedRelayChannelId']);
        $this->assertEquals($em2->getId(), $this->configTranslator->getConfig($this->freshEntity($powerswitch))['relatedMeterChannelId']);
        $this->assertEquals($powerswitch->getId(), $this->configTranslator->getConfig($this->freshEntity($em2))['relatedRelayChannelId']);
    }

    public function testChangingRelatedChannelForEm() {
        $powerswitch = $this->device->getChannels()[0];
        $lightswitch = $this->device->getChannels()[1];
        $em = $this->device->getChannels()[2];
        $this->configTranslator->setConfig($em, ['relatedRelayChannelId' => $powerswitch->getId()]);
        $this->configTranslator->setConfig($em, ['relatedRelayChannelId' => $lightswitch->getId()]);
        $this->assertNull($this->configTranslator->getConfig($this->freshEntity($powerswitch))['relatedMeterChannelId']);
        $this->assertEquals($em->getId(), $this->configTranslator->getConfig($this->freshEntity($lightswitch))['relatedMeterChannelId']);
        $this->assertEquals($lightswitch->getId(), $this->configTranslator->getConfig($this->freshEntity($em))['relatedRelayChannelId']);
    }

    public function testSettingMeterToAnotherSwitch() {
        $powerswitch = $this->device->getChannels()[0];
        $lightswitch = $this->device->getChannels()[1];
        $em = $this->device->getChannels()[2];
        $this->configTranslator->setConfig($powerswitch, ['relatedMeterChannelId' => $em->getId()]);
        $this->configTranslator->setConfig($lightswitch, ['relatedMeterChannelId' => $em->getId()]);
        $this->assertNull($this->configTranslator->getConfig($this->freshEntity($powerswitch))['relatedMeterChannelId']);
        $this->assertEquals($em->getId(), $this->configTranslator->getConfig($this->freshEntity($lightswitch))['relatedMeterChannelId']);
        $this->assertEquals($lightswitch->getId(), $this->configTranslator->getConfig($this->freshEntity($em))['relatedRelayChannelId']);
    }

    public function testSettingSwitchToToAnotherMeter() {
        $powerswitch = $this->device->getChannels()[0];
        $em = $this->device->getChannels()[2];
        $em2 = $this->device->getChannels()[3];
        $this->configTranslator->setConfig($em, ['relatedRelayChannelId' => $powerswitch->getId()]);
        $this->configTranslator->setConfig($em2, ['relatedRelayChannelId' => $powerswitch->getId()]);
        $this->assertNull($this->configTranslator->getConfig($this->freshEntity($em))['relatedRelayChannelId']);
        $this->assertEquals($em2->getId(), $this->configTranslator->getConfig($this->freshEntity($powerswitch))['relatedMeterChannelId']);
        $this->assertEquals($powerswitch->getId(), $this->configTranslator->getConfig($this->freshEntity($em2))['relatedRelayChannelId']);
    }

    public function testSettingInvalidRelatedChannelForElectricityMeter() {
        $this->expectExceptionMessage('Invalid relay function');
        $invalidRelay = $this->device->getChannels()[4];
        $em = $this->device->getChannels()[2];
        $this->configTranslator->setConfig($em, ['relatedRelayChannelId' => $invalidRelay->getId()]);
    }

    public function testSettingRelatedChannelForElectricityMeterAsString() {
        $this->expectExceptionMessage('is not an integer');
        $powerswitch = $this->device->getChannels()[0];
        $em = $this->device->getChannels()[2];
        $this->configTranslator->setConfig($em, ['relatedRelayChannelId' => '' . $powerswitch->getId()]);
    }
}
