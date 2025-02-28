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
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Model\UserConfigTranslator\DigiglassParamTranslator;
use SuplaBundle\Model\UserConfigTranslator\ElectricityMeterUserConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\GeneralPurposeMeasurementConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\HumidityAdjustmentConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\ImpulseCounterUserConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\InvertedLogicParamTranslator;
use SuplaBundle\Model\UserConfigTranslator\NumberOfAttemptsToOpenOrCloseParamTranslator;
use SuplaBundle\Model\UserConfigTranslator\OpeningClosingTimeUserConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\RelayTimeMsUserConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\RelayTimeSUserConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\RollerShutterUserConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\TemperatureAdjustmentConfigTranslator;

class ChannelParamConfigTranslatorTest extends TestCase {
    /** @var SubjectConfigTranslator */
    private $configTranslator;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new SubjectConfigTranslator([
            new RelayTimeMsUserConfigTranslator(),
            new RelayTimeSUserConfigTranslator(),
            new OpeningClosingTimeUserConfigTranslator(),
            new ElectricityMeterUserConfigTranslator(),
            new ImpulseCounterUserConfigTranslator(),
            new HumidityAdjustmentConfigTranslator(),
            new TemperatureAdjustmentConfigTranslator(),
            new InvertedLogicParamTranslator(),
            new GeneralPurposeMeasurementConfigTranslator(),
            new DigiglassParamTranslator(),
            new NumberOfAttemptsToOpenOrCloseParamTranslator(),
            new RollerShutterUserConfigTranslator(),
        ]);
    }

    /** @dataProvider paramsConfigsExamples */
    public function testGettingConfigFromParams(
        ChannelFunction $channelFunction,
        array $params,
        array $expectedConfig
    ) {
        $channel = new IODeviceChannel();
        $channel->setFunction($channelFunction);
        $this->configTranslator->setConfig($channel, $expectedConfig);
        $channel->setFunction($channelFunction);
        $channel->setParam1($params[0] ?? 0);
        $channel->setParam2($params[1] ?? 0);
        $channel->setParam3($params[2] ?? 0);
        $channel->setParam4($params[3] ?? 0);
        $channel->setTextParam1($params[4] ?? null);
        $channel->setTextParam2($params[5] ?? null);
        $channel->setTextParam3($params[6] ?? null);
        $config = $this->configTranslator->getConfig($channel);
        $this->assertEquals($expectedConfig, array_intersect_key($config, $expectedConfig));
    }

    /** @dataProvider paramsConfigsExamples */
    public function testSettingParamsFromConfig(
        ChannelFunction $channelFunction,
        array $expectedParams,
        array $config
    ) {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'id', 1);
        $channel->setFunction($channelFunction);
        $channel->setParam1(111);
        $channel->setParam2(222);
        $channel->setParam3(333);
        $channel->setParam4(444);
        $channel->setTextParam1('aaa');
        $channel->setTextParam2('bbb');
        $channel->setTextParam3('ccc');
        $this->configTranslator->setConfig($channel, $config);
        $this->assertEquals($expectedParams[0] ?? 111, $channel->getParam1());
        $this->assertEquals($expectedParams[1] ?? 222, $channel->getParam2());
        $this->assertEquals($expectedParams[2] ?? 333, $channel->getParam3());
        $this->assertEquals($expectedParams[3] ?? 444, $channel->getParam4());
        $this->assertEquals($expectedParams[4] ?? 'aaa', $channel->getTextParam1());
        $this->assertEquals($expectedParams[5] ?? 'bbb', $channel->getTextParam2());
        $this->assertEquals($expectedParams[6] ?? 'ccc', $channel->getTextParam3());
        $actualConfig = $this->configTranslator->getConfig($channel);
        $this->assertEquals(array_intersect_key($actualConfig, $config), $config);
    }

    /** @dataProvider paramsConfigsExamples */
    public function testClearingParamsConfig(
        ChannelFunction $channelFunction,
        array $expectedParams,
        array $config,
        ?array $expectedDefaults = []
    ) {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'id', 1);
        $channel->setFunction($channelFunction);
        $channel->setParam1(111);
        $channel->setParam2(222);
        $channel->setParam3(333);
        $channel->setParam4(444);
        $channel->setTextParam1('aaa');
        $channel->setTextParam2('bbb');
        $channel->setTextParam3('ccc');
        $this->configTranslator->setConfig($channel, $config);
        $this->configTranslator->clearConfig($channel);
        $this->assertEquals(($expectedParams[0] ?? null) !== null ? $expectedDefaults[0] ?? 0 : 111, $channel->getParam1());
        $this->assertEquals(($expectedParams[1] ?? null) !== null ? 0 : 222, $channel->getParam2());
        $this->assertEquals(($expectedParams[2] ?? null) !== null ? 0 : 333, $channel->getParam3());
        $this->assertEquals(($expectedParams[3] ?? null) !== null ? 0 : 444, $channel->getParam4());
        $this->assertEquals(($expectedParams[4] ?? null) !== null ? '' : 'aaa', $channel->getTextParam1());
        $this->assertEquals(($expectedParams[5] ?? null) !== null ? '' : 'bbb', $channel->getTextParam2());
        $this->assertEquals(($expectedParams[6] ?? null) !== null ? '' : 'ccc', $channel->getTextParam3());
    }

    public static function paramsConfigsExamples() {
        // the examples below omits testing of controllingChannels and sensorChannels on purpose;
        // they are tested in the ControllingAnyLockRelatedSensorIntegrationTest
        // @codingStandardsIgnoreStart
        return [
            [ChannelFunction::NONE(), [], []],
            [ChannelFunction::CONTROLLINGTHEDOORLOCK(), [700], ['relayTimeMs' => 700], [500]],
            [ChannelFunction::CONTROLLINGTHEGARAGEDOOR(), [700], ['relayTimeMs' => 700], [500]],
            [ChannelFunction::CONTROLLINGTHEGATE(), [700], ['relayTimeMs' => 700], [500]],
            [ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), [700], ['relayTimeMs' => 700], [500]],
            [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), [null, null, null, 1], ['bottomPosition' => 1]],
            [ChannelFunction::ELECTRICITYMETER(), [null, 123, null, null, 'PLN'], ['pricePerUnit' => 0.0123, 'currency' => 'PLN']],
            [ChannelFunction::ELECTRICITYMETER(), [null, 123, null, null, 'PLN'], ['pricePerUnit' => 0.0123, 'currency' => 'PLN']],
            [ChannelFunction::HUMIDITY(), [null, null, null], ['humidityAdjustment' => 1.24]],
            [ChannelFunction::HUMIDITYANDTEMPERATURE(), [null, null, null], ['temperatureAdjustment' => 1.23, 'humidityAdjustment' => 1.24]],
            [ChannelFunction::LIGHTSWITCH(), [], []],
            [ChannelFunction::MAILSENSOR(), [null, null, null], ['invertedLogic' => true]],
            [ChannelFunction::NOLIQUIDSENSOR(), [null, null, null], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_DOOR(), [null, null, null], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_GARAGEDOOR(), [null, null, null], ['invertedLogic' => true]],
            [ChannelFunction::OPENINGSENSOR_GATE(), [null, null, null], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_GATEWAY(), [null, null, null], ['invertedLogic' => true]],
            [ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER(), [null, null, null], ['invertedLogic' => false]],
            [ChannelFunction::OPENINGSENSOR_WINDOW(), [null, null, null], ['invertedLogic' => true]],
            [ChannelFunction::STAIRCASETIMER(), [1011], ['relayTimeS' => 101.1]],
            [ChannelFunction::THERMOMETER(), [null, null], ['temperatureAdjustment' => 1.23]],
            [ChannelFunction::DIGIGLASS_VERTICAL(), [2, 1000], ['sectionsCount' => 2, 'regenerationTimeStart' => 1000]],
            [ChannelFunction::DIGIGLASS_HORIZONTAL(), [2, 1000], ['sectionsCount' => 2, 'regenerationTimeStart' => 1000]],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function testNotOverwritingExistingParamsFromConfigIfNotGiven() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::HUMIDITYANDTEMPERATURE());
        $this->configTranslator->setConfig($channel, ['temperatureAdjustment' => 1.23, 'humidityAdjustment' => 1.24]);
        $this->configTranslator->setConfig($channel, ['temperatureAdjustment' => 1]);
        $this->assertEquals(100, $channel->getUserConfigValue('temperatureAdjustment'));
        $this->assertEquals(124, $channel->getUserConfigValue('humidityAdjustment'));
    }

    public function testReturningDefaultsIfNoneSet() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::CONTROLLINGTHEGATE());
        $config = $this->configTranslator->getConfig($channel);
        $this->assertEquals([
            'relayTimeMs' => 500,
            'numberOfAttemptsToOpen' => 5,
            'numberOfAttemptsToClose' => 5,
            'timeSettingAvailable' => true,
            'stateVerificationMethodActive' => false,
        ], $config);
    }

    public function testReturningInfoFromFlagsNoneSet() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::CONTROLLINGTHEGATE());
        $config = $this->configTranslator->getConfig($channel);
        $this->assertTrue($config['timeSettingAvailable']);
        EntityUtils::setField($channel, 'flags', ChannelFunctionBitsFlags::getAllFeaturesFlag());
        $config = $this->configTranslator->getConfig($channel);
        $this->assertFalse($config['timeSettingAvailable']);
    }

    public function testDefault0IfConfigEmptyString() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::IC_GASMETER());
        $this->configTranslator->setConfig($channel, ['initialValue' => '']);
        $config = $this->configTranslator->getConfig($channel);
        $this->assertEquals(0, $config['initialValue']);
    }
}
