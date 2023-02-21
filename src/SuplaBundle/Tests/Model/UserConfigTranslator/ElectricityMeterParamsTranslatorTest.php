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
use SuplaBundle\Model\UserConfigTranslator\ElectricityMeterParamsTranslator;
use SuplaBundle\Model\UserConfigTranslator\OpeningClosingTimeUserConfigTranslator;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class ElectricityMeterParamsTranslatorTest extends TestCase {
    use UnitTestHelper;

    /** @var OpeningClosingTimeUserConfigTranslator */
    private $configTranslator;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $channel;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new ElectricityMeterParamsTranslator();
        $this->channel = new IODeviceChannel();
        EntityUtils::setField($this->channel, 'properties', json_encode([
            'countersAvailable' => ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy',
                'reverseActiveEnergyBalanced'],
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
}
