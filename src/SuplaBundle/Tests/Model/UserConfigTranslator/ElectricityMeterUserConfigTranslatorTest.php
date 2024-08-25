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

namespace SuplaBundle\Tests\Model\UserConfigTranslator;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Model\UserConfigTranslator\ElectricityMeterUserConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\OpeningClosingTimeUserConfigTranslator;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class ElectricityMeterUserConfigTranslatorTest extends TestCase {
    use UnitTestHelper;

    /** @var OpeningClosingTimeUserConfigTranslator */
    private $configTranslator;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $channel;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new ElectricityMeterUserConfigTranslator();
        $this->channel = new IODeviceChannel();
        EntityUtils::setField($this->channel, 'properties', json_encode([
            'countersAvailable' => ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy',
                'reverseActiveEnergyBalanced'],
            'availableCTTypes' => ["100A_33mA", "200A_66mA", "400A_133mA"],
            'availablePhaseLedTypes' => [
                "OFF", "VOLTAGE_PRESENCE", "VOLTAGE_PRESENCE_INVERTED", "VOLTAGE_LEVEL", "POWER_ACTIVE_DIRECTION",
            ],
        ]));
    }

    public function testDisablingPhases() {
        $this->configTranslator->setConfig($this->channel, ['disabledPhases' => [2]]);
        $config = $this->configTranslator->getConfig($this->channel);
        $this->assertArrayHasKey('enabledPhases', $config);
        $this->assertArrayHasKey('disabledPhases', $config);
        $this->assertEquals([1, 3], $config['enabledPhases']);
        $this->assertEquals([2], $config['disabledPhases']);
    }

    public function testSettingInitialValues() {
        $this->configTranslator->setConfig($this->channel, ['electricityMeterInitialValues' => [
            'forwardActiveEnergy' => 123.45,
        ]]);
        $config = $this->channel->getUserConfig();
        $this->assertArrayHasKey('electricityMeterInitialValues', $config);
        $this->assertEquals(123.45, $config['electricityMeterInitialValues']['forwardActiveEnergy']);
    }

    public function testLeavingPreviousInitialValues() {
        $this->configTranslator->setConfig($this->channel, ['electricityMeterInitialValues' => [
            'forwardActiveEnergy' => 123.45,
        ]]);
        $this->configTranslator->setConfig($this->channel, ['electricityMeterInitialValues' => [
            'reverseActiveEnergyBalanced' => 234.56,
        ]]);
        $config = $this->channel->getUserConfig();
        $this->assertArrayHasKey('electricityMeterInitialValues', $config);
        $this->assertEquals(123.45, $config['electricityMeterInitialValues']['forwardActiveEnergy']);
        $this->assertEquals(234.56, $config['electricityMeterInitialValues']['reverseActiveEnergyBalanced']);
    }

    public function testChecksSupportedCounters() {
        $this->expectExceptionMessage('is not an element of the valid values');
        $this->configTranslator->setConfig($this->channel, ['electricityMeterInitialValues' => [
            'forwardActiveEnergyBalanced' => 123.45,
        ]]);
    }

    public function testSettingInitialValuesForEveryPhase() {
        $this->configTranslator->setConfig($this->channel, ['electricityMeterInitialValues' => [
            'forwardActiveEnergy' => [1 => 100, 2 => 200, 3 => 300],
        ]]);
        $config = $this->channel->getUserConfig();
        $this->assertArrayHasKey('electricityMeterInitialValues', $config);
        $this->assertEquals([1 => 100, 2 => 200, 3 => 300], $config['electricityMeterInitialValues']['forwardActiveEnergy']);
    }

    public function testSettingInitialValuesForOnePhaseSetsOthersToDefaults() {
        $this->configTranslator->setConfig($this->channel, ['electricityMeterInitialValues' => [
            'forwardActiveEnergy' => [2 => 200],
        ]]);
        $config = $this->channel->getUserConfig();
        $this->assertArrayHasKey('electricityMeterInitialValues', $config);
        $this->assertEquals([1 => 0, 2 => 200, 3 => 0], $config['electricityMeterInitialValues']['forwardActiveEnergy']);
    }

    public function testCannotSetValuesForEachPhaseForReverseActiveEnergyBalanced() {
        $this->expectExceptionMessage('Advanced mode is unsupported for this counter');
        $this->configTranslator->setConfig($this->channel, ['electricityMeterInitialValues' => [
            'reverseActiveEnergyBalanced' => [2 => 200],
        ]]);
    }

    public function testSettingInitialValuesMixedAdvancedAndSimpleMode() {
        $this->configTranslator->setConfig($this->channel, ['electricityMeterInitialValues' => [
            'forwardActiveEnergy' => [1 => 300.1, 2 => 200],
            'reverseActiveEnergyBalanced' => 200,
        ]]);
        $config = $this->channel->getUserConfig();
        $this->assertArrayHasKey('electricityMeterInitialValues', $config);
        $this->assertEquals([1 => 300.1, 2 => 200, 3 => 0], $config['electricityMeterInitialValues']['forwardActiveEnergy']);
        $this->assertEquals(200, $config['electricityMeterInitialValues']['reverseActiveEnergyBalanced']);
    }

    public function testReadingResetCountersAvailable() {
        EntityUtils::setField($this->channel, 'flags', 0x2000);
        $config = $this->configTranslator->getConfig($this->channel);
        $this->assertTrue($config['resetCountersAvailable']);
        EntityUtils::setField($this->channel, 'flags', 0);
        $config = $this->configTranslator->getConfig($this->channel);
        $this->assertFalse($config['resetCountersAvailable']);
    }

    public function testReadingAvailableCTTypes() {
        $config = $this->configTranslator->getConfig($this->channel);
        $this->assertCount(3, $config['availableCTTypes']);
    }

    public function testReadingAvailablePhaseLedTypes() {
        $config = $this->configTranslator->getConfig($this->channel);
        $this->assertCount(5, $config['availablePhaseLedTypes']);
    }

    public function testUpdatingUsedCTType() {
        $this->assertNull($this->configTranslator->getConfig($this->channel)['usedCTType']);
        $this->configTranslator->setConfig($this->channel, ['usedCTType' => '100A_33mA']);
        $this->assertEquals('100A_33mA', $this->configTranslator->getConfig($this->channel)['usedCTType']);
        $this->configTranslator->setConfig($this->channel, ['usedCTType' => '200A_66mA']);
        $this->assertEquals('200A_66mA', $this->configTranslator->getConfig($this->channel)['usedCTType']);
        $this->expectExceptionMessage('100A_33mA, 200A_66mA, 400A_133mA');
        $this->configTranslator->setConfig($this->channel, ['usedCTType' => 'unicorn']);
    }

    public function testUpdatingUsedPhaseLedType() {
        $this->assertEquals('OFF', $this->configTranslator->getConfig($this->channel)['usedPhaseLedType']);
        $this->configTranslator->setConfig($this->channel, ['usedPhaseLedType' => 'VOLTAGE_PRESENCE']);
        $this->assertEquals('VOLTAGE_PRESENCE', $this->configTranslator->getConfig($this->channel)['usedPhaseLedType']);
        $this->configTranslator->setConfig($this->channel, ['usedPhaseLedType' => 'VOLTAGE_PRESENCE_INVERTED']);
        $this->assertEquals('VOLTAGE_PRESENCE_INVERTED', $this->configTranslator->getConfig($this->channel)['usedPhaseLedType']);
        $this->expectExceptionMessage('OFF, VOLTAGE_PRESENCE, VOLTAGE_PRESENCE_INVERTED');
        $this->configTranslator->setConfig($this->channel, ['usedPhaseLedType' => 'unicorn']);
    }

    public function testUpdatingUsedPhaseLedTypeToVoltageLevel() {
        $this->assertEquals('OFF', $this->configTranslator->getConfig($this->channel)['usedPhaseLedType']);
        $this->configTranslator->setConfig(
            $this->channel,
            ['usedPhaseLedType' => 'VOLTAGE_LEVEL', 'phaseLedParam1' => 220, 'phaseLedParam2' => 240]
        );
        $this->assertEquals('VOLTAGE_LEVEL', $this->configTranslator->getConfig($this->channel)['usedPhaseLedType']);
        $this->assertEquals(220, $this->configTranslator->getConfig($this->channel)['phaseLedParam1']);
        $this->assertEquals(240, $this->configTranslator->getConfig($this->channel)['phaseLedParam2']);
        $this->assertEquals(22000, $this->channel->getUserConfigValue('phaseLedParam1'));
        $this->configTranslator->setConfig($this->channel, ['phaseLedParam1' => 220.2]);
        $this->assertEquals(220.2, $this->configTranslator->getConfig($this->channel)['phaseLedParam1']);
        $this->assertEquals(240, $this->configTranslator->getConfig($this->channel)['phaseLedParam2']);
        $this->expectExceptionMessage('Low threshold must be less than high threshold');
        $this->configTranslator->setConfig($this->channel, ['phaseLedParam2' => 210]);
    }

    public function testClearingUsedPhaseLedTypeWhenNotAvailable() {
        $channel = new IODeviceChannel();
        $this->assertEquals('OFF', $this->configTranslator->getConfig($channel)['usedPhaseLedType']);
        $channel->setUserConfigValue('usedPhaseLedType', 'OFF');
        $this->configTranslator->setConfig($channel, ['usedPhaseLedType' => null]);
    }
}
