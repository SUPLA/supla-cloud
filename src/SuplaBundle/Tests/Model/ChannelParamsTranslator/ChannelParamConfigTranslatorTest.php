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

namespace SuplaBundle\Tests\Model\ChannelParamsTranslator;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\ControllingAnyLockRelatedSensorUpdater;
use SuplaBundle\Model\ChannelParamsTranslator\ControllingChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\ControllingSecondaryParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\ElectricityMeterParamsTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\GeneralPurposeMeasurementParamsTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\HumidityAdjustmentParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\ImpulseCounterParamsTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\InvertedLogicParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\OpeningClosingTimeChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\OpeningSensorParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\OpeningSensorSecondaryParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\RelayTimeMsChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\RelayTimeSChannelParamTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\TemperatureAdjustmentParamTranslator;

class ChannelParamConfigTranslatorTest extends TestCase {
    /** @var ChannelParamConfigTranslator */
    private $configTranslator;

    /** @before */
    public function createTranslator() {
        $updaterMock = $this->createMock(ControllingAnyLockRelatedSensorUpdater::class);
        $this->configTranslator = new ChannelParamConfigTranslator([
            new RelayTimeMsChannelParamTranslator(),
            new RelayTimeSChannelParamTranslator(),
            new OpeningClosingTimeChannelParamTranslator(),
            new OpeningSensorParamTranslator($updaterMock),
            new OpeningSensorSecondaryParamTranslator($updaterMock),
            new ElectricityMeterParamsTranslator(),
            new ImpulseCounterParamsTranslator(),
            new HumidityAdjustmentParamTranslator(),
            new TemperatureAdjustmentParamTranslator(),
            new InvertedLogicParamTranslator(),
            new ControllingChannelParamTranslator($updaterMock),
            new ControllingSecondaryParamTranslator($updaterMock),
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
        $this->assertEquals($expectedConfig, array_intersect_key($config, $expectedConfig));
    }

    /** @dataProvider paramsConfigsExamples */
    public function testSettingParamsFromConfig(
        ChannelFunction $channelFunction,
        array $expectedParams,
        array $config,
        ?ChannelType $type = null
    ) {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'id', 1);
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

    /** @dataProvider paramsConfigsExamples */
    public function testClearingParamsConfig(
        ChannelFunction $channelFunction,
        array $expectedParams,
        array $config,
        ?ChannelType $type = null
    ) {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'id', 1);
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
        $this->configTranslator->clearConfig($channel);
        $this->assertEquals(($expectedParams[0] ?? null) !== null ? 0 : 111, $channel->getParam1());
        $this->assertEquals(($expectedParams[1] ?? null) !== null ? 0 : 222, $channel->getParam2());
        $this->assertEquals(($expectedParams[2] ?? null) !== null ? 0 : 333, $channel->getParam3());
        $this->assertEquals(($expectedParams[3] ?? null) !== null ? 0 : 'aaa', $channel->getTextParam1());
        $this->assertEquals(($expectedParams[4] ?? null) !== null ? 0 : 'bbb', $channel->getTextParam2());
        $this->assertEquals(($expectedParams[5] ?? null) !== null ? 0 : 'ccc', $channel->getTextParam3());
    }

    public function paramsConfigsExamples() {
        // the examples below omits testing of controllingChannels and sensorChannels on purpose;
        // they are tested in the ControllingAnyLockRelatedSensorIntegrationTest
        // @codingStandardsIgnoreStart
        return [
            [ChannelFunction::NONE(), [], []],
            [ChannelFunction::CONTROLLINGTHEDOORLOCK(), [700], ['relayTimeMs' => 700]],
            [ChannelFunction::CONTROLLINGTHEGARAGEDOOR(), [700], ['relayTimeMs' => 700]],
            [ChannelFunction::CONTROLLINGTHEGATE(), [700], ['relayTimeMs' => 700]],
            [ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), [700], ['relayTimeMs' => 700]],
            [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), [700, null, 800], ['openingTimeS' => 70, 'closingTimeS' => 80]],
            [ChannelFunction::IC_ELECTRICITYMETER(), [103, 123, 124, 'PLN', 'm3'], ['pricePerUnit' => 0.0123, 'impulsesPerUnit' => 124, 'currency' => 'PLN', 'initialValue' => 1.03, 'unit' => 'm3']],
            [ChannelFunction::ELECTRICITYMETER(), [null, 123, null, 'PLN'], ['pricePerUnit' => 0.0123, 'currency' => 'PLN']],
            [ChannelFunction::IC_GASMETER(), [111, 123, 124, 'PLN', 'm3'], ['pricePerUnit' => 0.0123, 'impulsesPerUnit' => 124, 'currency' => 'PLN', 'initialValue' => 1.11, 'unit' => 'm3']],
            [ChannelFunction::HUMIDITY(), [null, null, 124], ['humidityAdjustment' => 1.24]],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), [null, 123, 124], ['temperatureAdjustment' => 1.23, 'humidityAdjustment' => 1.24]],
            [ChannelFunction::LIGHTSWITCH(), [], []],
            [ChannelFunction::MAILSENSOR(), [null, null, 1], ['invertedLogic' => true]],
            [ChannelFunction::NOLIQUIDSENSOR(), [null, null, 0], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_DOOR(), [null, null, 0], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_GARAGEDOOR(), [null, null, 1], ['invertedLogic' => true]],
            [ChannelFunction::OPENINGSENSOR_GATE(), [null, null, 0], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_GATEWAY(), [null, null, 1], ['invertedLogic' => true]],
            [ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER(), [null, null, 0], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_WINDOW(), [null, null, 1], ['invertedLogic' => true]],
            [ChannelFunction::STAIRCASETIMER(), [1011], ['relayTimeS' => 101.1]],
            [ChannelFunction::THERMOMETER(), [null, 123], ['temperatureAdjustment' => 1.23]],
            [ChannelFunction::IC_WATERMETER(), [111, 123, 124, 'PLN', 'm3'], ['pricePerUnit' => 0.0123, 'impulsesPerUnit' => 124, 'currency' => 'PLN', 'initialValue' => 1.11, 'unit' => 'm3']],
            [
                ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(),
                [121230, 0b000011011, 131300, '$', 'USD'],
                [
                    'initialValue' => 12.123,
                    'impulsesPerUnit' => 13.13,
                    'precision' => 3,
                    'storeMeasurementHistory' => true,
                    'chartType' => 1,
                    'chartDataSourceType' => 0,
                    'interpolateMeasurements' => false,
                    'unitPrefix' => '$',
                    'unitSuffix' => 'USD',
                ],
            ],
            [
                ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(),
                [121230, 0b101011101, 131300, '$', 'USD'],
                [
                    'initialValue' => 12.123,
                    'impulsesPerUnit' => 13.13,
                    'precision' => 5,
                    'storeMeasurementHistory' => true,
                    'chartType' => 1,
                    'chartDataSourceType' => 1,
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
        $channel->setFunction(ChannelFunction::HUMIDITYANDTEMPERATURE());
        $this->configTranslator->setParamsFromConfig($channel, ['temperatureAdjustment' => 1.23, 'humidityAdjustment' => 1.24]);
        $this->configTranslator->setParamsFromConfig($channel, ['temperatureAdjustment' => 1]);
        $this->assertEquals(100, $channel->getParam2());
        $this->assertEquals(124, $channel->getParam3());
    }

    public function testReturningDefaultsIfNoneSet() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::CONTROLLINGTHEGATE());
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertEquals(['openingSensorChannelId' => null, 'openingSensorSecondaryChannelId' => null, 'relayTimeMs' => 500], $config);
    }
}
