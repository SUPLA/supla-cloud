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
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\ElectricityMeterParamsTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\GeneralPurposeMeasurementParamsTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\HumidityAdjustmentParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\ImpulseCounterParamsTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\InvertedLogicParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\OpeningClosingTimeChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\OpeningSensorParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\OpeningSensorSecondaryParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\RelayTimeMsChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\RelayTimeSChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\TemperatureAdjustmentParamTranslator;

class ChannelParamConfigTranslatorTest extends TestCase {
    /** @var ChannelParamConfigTranslator */
    private $configTranslator;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new ChannelParamConfigTranslator([
            new RelayTimeMsChannelParamTranslator(),
            new RelayTimeSChannelParamTranslator(),
            new OpeningClosingTimeChannelParamTranslator(),
            new OpeningSensorParamTranslator(),
            new OpeningSensorSecondaryParamTranslator(),
            new ElectricityMeterParamsTranslator(),
            new ImpulseCounterParamsTranslator(),
            new HumidityAdjustmentParamTranslator(),
            new TemperatureAdjustmentParamTranslator(),
            new InvertedLogicParamTranslator(),
            new ControllingChannelParamTranslator(),
            new ControllingSecondaryParamTranslator(),
            new GeneralPurposeMeasurementParamsTranslator(),
        ]);
    }

    /** @dataProvider paramsConfigsExamples */
    public function testGettingConfigFromParams(
        ChannelFunction $channelFunction,
        array $params,
        array $expectedConfig,
        ?ChannelType $type = null
    ) {
        $channel = new IODeviceChannel();
        if ($type) {
            EntityUtils::setField($channel, 'type', $type->getId());
        }
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
    public function testSettingParamsFromConfig(
        ChannelFunction $channelFunction,
        array $expectedParams,
        array $config,
        ?ChannelType $type = null
    ) {
        $channel = new IODeviceChannel();
        if ($type) {
            EntityUtils::setField($channel, 'type', $type->getId());
        }
        $channel->setFunction($channelFunction);
        $channel->setParam1(111);
        $channel->setParam2(222);
        $channel->setParam3(333);
        $channel->setTextParam1('aaa');
        $channel->setTextParam2('bbb');
        $channel->setTextParam3('ccc');
        $this->configTranslator->setParamsFromConfig($channel, $config);
        $this->assertEquals($expectedParams[0] ?? 111, $channel->getParam1());
        $this->assertEquals($expectedParams[1] ?? 222, $channel->getParam2());
        $this->assertEquals($expectedParams[2] ?? 333, $channel->getParam3());
        $this->assertEquals($expectedParams[3] ?? 'aaa', $channel->getTextParam1());
        $this->assertEquals($expectedParams[4] ?? 'bbb', $channel->getTextParam2());
        $this->assertEquals($expectedParams[5] ?? 'ccc', $channel->getTextParam3());
    }

    public function paramsConfigsExamples() {
        // @codingStandardsIgnoreStart
        return [
            [ChannelFunction::NONE(), [], []],
            [ChannelFunction::CONTROLLINGTHEDOORLOCK(), [700, 123], ['relayTimeMs' => 700, 'openingSensorChannelId' => 123]],
            [ChannelFunction::CONTROLLINGTHEGARAGEDOOR(), [700, 123], ['relayTimeMs' => 700, 'openingSensorChannelId' => 123]],
            [ChannelFunction::CONTROLLINGTHEGATE(), [700, 123, 1234], ['relayTimeMs' => 700, 'openingSensorChannelId' => 123, 'openingSensorSecondaryChannelId' => 1234]],
            [ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), [700, 123], ['relayTimeMs' => 700, 'openingSensorChannelId' => 123]],
            [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), [700, 123, 800], ['openingTimeS' => 70, 'openingSensorChannelId' => 123, 'closingTimeS' => 80]],
            [ChannelFunction::ELECTRICITYMETER(), [100, 123, 124, 'PLN', 'm3'], ['pricePerUnit' => 0.0123, 'impulsesPerUnit' => 124, 'currency' => 'PLN', 'initialValue' => 100, 'customUnit' => 'm3'], ChannelType::IMPULSECOUNTER()],
            [ChannelFunction::ELECTRICITYMETER(), [null, 123, null, 'PLN'], ['pricePerUnit' => 0.0123, 'currency' => 'PLN'], ChannelType::ELECTRICITYMETER()],
            [ChannelFunction::GASMETER(), [111, 123, 124, 'PLN', 'm3'], ['pricePerUnit' => 0.0123, 'impulsesPerUnit' => 124, 'currency' => 'PLN', 'initialValue' => 111, 'customUnit' => 'm3'], ChannelType::IMPULSECOUNTER()],
            [ChannelFunction::HUMIDITY(), [null, null, 124], ['humidityAdjustment' => 1.24]],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), [null, 123, 124], ['temperatureAdjustment' => 1.23, 'humidityAdjustment' => 1.24]],
            [ChannelFunction::LIGHTSWITCH(), [], []],
            [ChannelFunction::MAILSENSOR(), [null, null, 1], ['invertedLogic' => true]],
            [ChannelFunction::NOLIQUIDSENSOR(), [null, null, 0], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_DOOR(), [123, null, 0], ['invertedLogic' => false, 'controllingChannelId' => 123]],
            [ChannelFunction::OPENINGSENSOR_GARAGEDOOR(), [123, null, 1], ['invertedLogic' => true, 'controllingChannelId' => 123]],
            [ChannelFunction::OPENINGSENSOR_GATE(), [123, 124, 0], ['invertedLogic' => false, 'controllingChannelId' => 123, 'controllingSecondaryChannelId' => 124]],
            [ChannelFunction::OPENINGSENSOR_GATEWAY(), [123, null, 1], ['invertedLogic' => true, 'controllingChannelId' => 123]],
            [ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER(), [123, null, 1], ['invertedLogic' => true, 'controllingChannelId' => 123]],
            [ChannelFunction::OPENINGSENSOR_WINDOW(), [null, null, 1], ['invertedLogic' => true]],
            [ChannelFunction::STAIRCASETIMER(), [1011], ['relayTimeS' => 101.1]],
            [ChannelFunction::THERMOMETER(), [null, 123], ['temperatureAdjustment' => 1.23]],
            [ChannelFunction::WATERMETER(), [111, 123, 124, 'PLN', 'm3'], ['pricePerUnit' => 0.0123, 'impulsesPerUnit' => 124, 'currency' => 'PLN', 'initialValue' => 111, 'customUnit' => 'm3'], ChannelType::IMPULSECOUNTER()],
            [
                ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(),
                [121230, 131300, 0b000011011, '$', 'USD'],
                [
                    'measurementMultiplier' => 12.123,
                    'measurementAdjustment' => 13.13,
                    'precision' => 3,
                    'storeMeasurementHistory' => true,
                    'chartPresentation' => 1,
                    'chartType' => 0,
                    'interpolateMeasurements' => false,
                    'unitPrefix' => '$',
                    'unitSuffix' => 'USD',
                ],
            ],
            [
                ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(),
                [121230, 131300, 0b101011101, '$', 'USD'],
                [
                    'measurementMultiplier' => 12.123,
                    'measurementAdjustment' => 13.13,
                    'precision' => 5,
                    'storeMeasurementHistory' => true,
                    'chartPresentation' => 1,
                    'chartType' => 1,
                    'interpolateMeasurements' => true,
                    'unitPrefix' => '$',
                    'unitSuffix' => 'USD',
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function testNotOverwritingExistingParamsFromConfigIfNotGiven() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::CONTROLLINGTHEGATE());
        $this->configTranslator->setParamsFromConfig($channel, [
            'relayTimeMs' => 700,
            'openingSensorChannelId' => 123,
            'openingSensorSecondaryChannelId' => 1234,
        ]);
        $this->configTranslator->setParamsFromConfig($channel, ['relayTimeMs' => 800]);
        $this->assertEquals(800, $channel->getParam1());
        $this->assertEquals(123, $channel->getParam2());
        $this->assertEquals(1234, $channel->getParam3());
    }
}
