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

namespace SuplaBundle\Tests\Integration\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class AnyMeterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelParamConfigTranslator */
    private $paramsTranslator;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_GASMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_WATERMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_HEATMETER],
        ]);
        $this->paramsTranslator = self::$container->get(ChannelParamConfigTranslator::class);
    }

    public function meterChannelsProvider() {
        return [[0], [1], [2], [3], [4]];
    }

    public function impulseCountersProvider() {
        return [[1], [2], [3], [4]];
    }

    /** @dataProvider meterChannelsProvider */
    public function testUpdatingPricePerUnit(int $channelIndex) {
        $channel = $this->device->getChannels()[$channelIndex];
        $this->assertEquals(0, $channel->getParam2());
        $this->paramsTranslator->setParamsFromConfig($channel, ['pricePerUnit' => 1000]);
        $this->assertEquals(1000 * 10000, $channel->getParam2());
        $this->paramsTranslator->setParamsFromConfig($channel, ['pricePerUnit' => 0.0001]);
        $this->assertEquals(1, $channel->getParam2());
        $this->paramsTranslator->setParamsFromConfig($channel, ['pricePerUnit' => 1001]);
        $this->assertEquals(1000 * 10000, $channel->getParam2());
        $this->paramsTranslator->setParamsFromConfig($channel, ['pricePerUnit' => 0]);
        $this->assertEquals(0, $channel->getParam2());
        $this->paramsTranslator->setParamsFromConfig($channel, ['pricePerUnit' => -1]);
        $this->assertEquals(0, $channel->getParam2());
    }

    /** @dataProvider impulseCountersProvider */
    public function testUpdatingImpulsesPerUnit(int $channelIndex) {
        $channel = $this->device->getChannels()[$channelIndex];
        $this->assertEquals(0, $channel->getParam3());
        $this->paramsTranslator->setParamsFromConfig($channel, ['impulsesPerUnit' => 1000000]);
        $this->assertEquals(1000000, $channel->getParam3());
        $this->paramsTranslator->setParamsFromConfig($channel, ['impulsesPerUnit' => 1]);
        $this->assertEquals(1, $channel->getParam3());
        $this->paramsTranslator->setParamsFromConfig($channel, ['impulsesPerUnit' => 1000001]);
        $this->assertEquals(1000000, $channel->getParam3());
        $this->paramsTranslator->setParamsFromConfig($channel, ['impulsesPerUnit' => 0]);
        $this->assertEquals(0, $channel->getParam3());
        $this->paramsTranslator->setParamsFromConfig($channel, ['impulsesPerUnit' => -1]);
        $this->assertEquals(0, $channel->getParam3());
    }

    /** @dataProvider impulseCountersProvider */
    public function testUpdatingInitialValue(int $channelIndex) {
        $channel = $this->device->getChannels()[$channelIndex];
        $this->assertEquals(0, $channel->getParam1());
        $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 1000000]);
        $this->assertEquals(1000000, $channel->getUserConfigValue('initialValue'));
        $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 100.50]);
        $this->assertEquals(100.50, $channel->getUserConfigValue('initialValue'));
        $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 1]);
        $this->assertEquals(1, $channel->getUserConfigValue('initialValue'));
        $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 0.01]);
        $this->assertEquals(0.01, $channel->getUserConfigValue('initialValue'));
        $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 100000001]);
        $this->assertEquals(100000000, $channel->getUserConfigValue('initialValue'));
        $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 0]);
        $this->assertEquals(0, $channel->getUserConfigValue('initialValue'));
        $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => -1]);
        $this->assertEquals(-1, $channel->getUserConfigValue('initialValue'));
        $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => -100000001]);
        $this->assertEquals(-100000000, $channel->getUserConfigValue('initialValue'));
    }

    /** @dataProvider meterChannelsProvider */
    public function testUpdatingCurrency(int $channelIndex) {
        $channel = $this->device->getChannels()[$channelIndex];
        $this->assertEquals(null, $channel->getTextParam1());
        $this->paramsTranslator->setParamsFromConfig($channel, ['currency' => 'PLN']);
        $this->assertEquals("PLN", $channel->getTextParam1());
        $this->paramsTranslator->setParamsFromConfig($channel, ['currency' => 'ABCD']);
        $this->assertEquals("PLN", $channel->getTextParam1());
        $this->paramsTranslator->setParamsFromConfig($channel, ['currency' => 'P']);
        $this->assertEquals("PLN", $channel->getTextParam1());
        $this->paramsTranslator->setParamsFromConfig($channel, ['currency' => '']);
        $this->assertEquals("", $channel->getTextParam1());
        $this->paramsTranslator->setParamsFromConfig($channel, ['currency' => null]);
        $this->assertEquals(null, $channel->getTextParam1());
    }

    /** @dataProvider meterChannelsProvider */
    public function testUpdatingUnit(int $channelIndex) {
        $channel = $this->device->getChannels()[$channelIndex];
        if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
            $this->assertEquals(null, $channel->getTextParam2());
            $this->paramsTranslator->setParamsFromConfig($channel, ['unit' => 'kWh']);
            $this->assertEquals("kWh", $channel->getTextParam2());
            $this->paramsTranslator->setParamsFromConfig($channel, ['unit' => 'kWh²']);
            $this->assertEquals("kWh²", $channel->getTextParam2());
            $this->paramsTranslator->setParamsFromConfig($channel, ['unit' => 'kWh²³']);
            $this->assertEquals('kWh²', $channel->getTextParam2());
            $this->paramsTranslator->setParamsFromConfig($channel, ['unit' => '']);
            $this->assertEquals("", $channel->getTextParam2());
            $this->paramsTranslator->setParamsFromConfig($channel, ['unit' => null]);
            $this->assertEquals(null, $channel->getTextParam2());
        } elseif ($channel->getType()->getId() == ChannelType::ELECTRICITYMETER) {
            $this->paramsTranslator->setParamsFromConfig($channel, ['unit' => 'kWh']);
            $this->assertEquals(null, $channel->getTextParam2());
        }
    }
}
