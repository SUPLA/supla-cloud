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

use SuplaBundle\Entity\IODevice;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig\ChannelParamConfigTranslator;
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
            [ChannelType::IMPULSECOUNTER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::GASMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::WATERMETER],
        ]);
        $this->paramsTranslator = self::$container->get(ChannelParamConfigTranslator::class);
    }

    public function testUpdatingPricePerUnit() {
        foreach ($this->device->getChannels() as $channel) {
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
    }

    public function testUpdatingImpulsesPerUnit() {
        foreach ($this->device->getChannels() as $channel) {
            if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
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
        }
    }

    public function testUpdatingInitialValue() {
        foreach ($this->device->getChannels() as $channel) {
            if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
                $this->assertEquals(0, $channel->getParam1());
                $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 1000000]);
                $this->assertEquals(1000000, $channel->getParam1());
                $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 1]);
                $this->assertEquals(1, $channel->getParam1());
                $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 1000001]);
                $this->assertEquals(1000000, $channel->getParam1());
                $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => 0]);
                $this->assertEquals(0, $channel->getParam1());
                $this->paramsTranslator->setParamsFromConfig($channel, ['initialValue' => -1]);
                $this->assertEquals(0, $channel->getParam1());
            }
        }
    }

    public function testUpdatingCurrency() {
        foreach ($this->device->getChannels() as $channel) {
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
    }

    public function testUpdatingUnit() {
        foreach ($this->device->getChannels() as $channel) {
            if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
                $this->assertEquals(null, $channel->getTextParam2());
                $this->paramsTranslator->setParamsFromConfig($channel, ['customUnit' => 'kWh']);
                $this->assertEquals("kWh", $channel->getTextParam2());
                $this->paramsTranslator->setParamsFromConfig($channel, ['customUnit' => '']);
                $this->assertEquals("", $channel->getTextParam2());
                $this->paramsTranslator->setParamsFromConfig($channel, ['customUnit' => null]);
                $this->assertEquals(null, $channel->getTextParam2());
            } elseif ($channel->getType()->getId() == ChannelType::ELECTRICITYMETER) {
                $this->paramsTranslator->setParamsFromConfig($channel, ['customUnit' => 'kWh']);
                $this->assertEquals(null, $channel->getTextParam2());
            }
        }
    }
}
