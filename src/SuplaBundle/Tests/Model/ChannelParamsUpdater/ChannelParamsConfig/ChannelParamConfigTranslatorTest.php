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

namespace SuplaBundle\Tests\Model\ChannelParamsUpdater\ChannelParamsConfig;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\ChannelParamConfigTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\ControllingChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\ControllingSecondaryParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\CustomUnitParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\HumidityAdjustmentParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\InvertedLogicParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\MeterParamsTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\OpeningSensorParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\OpeningSensorSecondaryParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\RelayTimeChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\TemperatureAdjustmentParamTranslator;

class ChannelParamConfigTranslatorTest extends TestCase {
    /** @var ChannelParamConfigTranslator */
    private $configTranslator;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new ChannelParamConfigTranslator([
            new RelayTimeChannelParamTranslator(),
            new OpeningSensorParamTranslator(),
            new OpeningSensorSecondaryParamTranslator(),
            new MeterParamsTranslator(),
            new CustomUnitParamTranslator(),
            new HumidityAdjustmentParamTranslator(),
            new TemperatureAdjustmentParamTranslator(),
            new InvertedLogicParamTranslator(),
            new ControllingChannelParamTranslator(),
            new ControllingSecondaryParamTranslator(),
        ]);
    }

    /** @dataProvider paramsConfigsExamples */
    public function testGettingConfigFromParams(ChannelFunction $channelFunction, array $params, array $expectedConfig) {
        $channel = new IODeviceChannel();
        $channel->setFunction($channelFunction);
        $channel->setParam1($params[0] ?? 0);
        $channel->setParam2($params[1] ?? 0);
        $channel->setParam3($params[2] ?? 0);
        $channel->setTextParam1($params[3] ?? null);
        $channel->setTextParam2($params[4] ?? null);
        $channel->setTextParam3($params[5] ?? null);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertEquals($expectedConfig, $config);
    }

    /** @dataProvider paramsConfigsExamples */
    public function testSettingParamsFromConfig(ChannelFunction $channelFunction, array $expectedParams, array $config) {
        $channel = new IODeviceChannel();
        $channel->setFunction($channelFunction);
        $channel->setParam1(111);
        $channel->setParam2(222);
        $channel->setParam3(333);
        $channel->setTextParam1('aaa');
        $channel->setTextParam2('bbb');
        $channel->setTextParam3('ccc');
        $this->configTranslator->setParamsFromConfig($config, $channel);
        $this->assertEquals($expectedParams[0] ?? 111, $channel->getParam1());
        $this->assertEquals($expectedParams[1] ?? 222, $channel->getParam2());
        $this->assertEquals($expectedParams[2] ?? 333, $channel->getParam3());
        $this->assertEquals($expectedParams[3] ?? 'aaa', $channel->getTextParam1());
        $this->assertEquals($expectedParams[4] ?? 'bbb', $channel->getTextParam2());
        $this->assertEquals($expectedParams[5] ?? 'ccc', $channel->getTextParam3());
    }

    public function paramsConfigsExamples() {
        return [
            [ChannelFunction::NONE(), [], []],
            [ChannelFunction::CONTROLLINGTHEDOORLOCK(), [700, 123], ['relayTime' => 700, 'openingSensorChannelId' => 123]],
            [ChannelFunction::CONTROLLINGTHEGARAGEDOOR(), [700, 123], ['relayTime' => 700, 'openingSensorChannelId' => 123]],
            [ChannelFunction::CONTROLLINGTHEGATE(), [700, 123, 1234], ['relayTime' => 700, 'openingSensorChannelId' => 123, 'openingSensorSecondaryChannelId' => 1234]],
            [ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), [700, 123], ['relayTime' => 700, 'openingSensorChannelId' => 123]],
            [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), [700, 123], ['relayTime' => 700, 'openingSensorChannelId' => 123]],
            [ChannelFunction::ELECTRICITYMETER(), [null, 123, 124, 'PLN'], ['pricePerUnit' => 123, 'impulsesPerUnit' => 124, 'currency' => 'PLN']],
            [ChannelFunction::GASMETER(), [null, 123, 124, 'PLN', 'm3'], ['pricePerUnit' => 123, 'impulsesPerUnit' => 124, 'currency' => 'PLN', 'customUnit' => 'm3']],
            [ChannelFunction::HUMIDITY(), [null, null, 124], ['humidityAdjustment' => 124]],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), [null, 123, 124], ['temperatureAdjustment' => 123, 'humidityAdjustment' => 124]],
            [ChannelFunction::MAILSENSOR(), [null, null, 1], ['invertedLogic' => true]],
            [ChannelFunction::NOLIQUIDSENSOR(), [null, null, 0], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_DOOR(), [123, null, 0], ['invertedLogic' => false, 'controllingChannelId' => 123]],
            [ChannelFunction::OPENINGSENSOR_GARAGEDOOR(), [123, null, 1], ['invertedLogic' => true, 'controllingChannelId' => 123]],
            [ChannelFunction::OPENINGSENSOR_GATE(), [123, 124, 0], ['invertedLogic' => false, 'controllingChannelId' => 123, 'controllingSecondaryChannelId' => 124]],
            [ChannelFunction::OPENINGSENSOR_GATEWAY(), [123, null, 1], ['invertedLogic' => true, 'controllingChannelId' => 123]],
            [ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER(), [123, null, 1], ['invertedLogic' => true, 'controllingChannelId' => 123]],
            [ChannelFunction::OPENINGSENSOR_WINDOW(), [null, null, 1], ['invertedLogic' => true]],
            [ChannelFunction::STAIRCASETIMER(), [1011], ['relayTime' => 1011]],
            [ChannelFunction::THERMOMETER(), [null, 123], ['temperatureAdjustment' => 123]],
            [ChannelFunction::WATERMETER(), [null, 123, 124, 'PLN', 'm3'], ['pricePerUnit' => 123, 'impulsesPerUnit' => 124, 'currency' => 'PLN', 'customUnit' => 'm3']],
        ];
    }

    public function testNotOverwritingExistingParamsFromConfigIfNotGiven() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::CONTROLLINGTHEGATE());
        $this->configTranslator->setParamsFromConfig(['relayTime' => 700, 'openingSensorChannelId' => 123, 'openingSensorSecondaryChannelId' => 1234], $channel);
        $this->configTranslator->setParamsFromConfig(['relayTime' => 800], $channel);
        $this->assertEquals(800, $channel->getParam1());
        $this->assertEquals(123, $channel->getParam2());
        $this->assertEquals(1234, $channel->getParam3());
    }

    public function testElectricityMeterImpulseCounterHasCustomUnit() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::ELECTRICITYMETER());
        EntityUtils::setField($channel, 'type', ChannelType::IMPULSECOUNTER);
        $this->configTranslator->setParamsFromConfig(['customUnit' => 'm3'], $channel);
        $this->assertEquals('m3', $channel->getTextParam2());
    }
}
