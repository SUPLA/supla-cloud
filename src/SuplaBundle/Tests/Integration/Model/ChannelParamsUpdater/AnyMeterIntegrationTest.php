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

namespace SuplaBundle\Tests\Integration\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class AnyMeterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelParamsUpdater */
    private $updater;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::GASMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::WATERMETER],
        ]);
        $this->updater = self::$container->get(ChannelParamsUpdater::class);
    }

    public function testUpdatingPricePerUnit() {

        foreach ($this->device->getChannels() as $channel) {
            $this->assertEquals(0, $channel->getParam2());
            $newChannel = new IODeviceChannel();

            $newChannel->setParam2(1000 * 10000);
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals(1000 * 10000, $channel->getParam2());

            $newChannel->setParam2(1);
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals(1, $channel->getParam2());

            $newChannel->setParam2(1000 * 10000 + 1);
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals(1000 * 10000, $channel->getParam2());

            $newChannel->setParam2(0);
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals(0, $channel->getParam2());

            $newChannel->setParam2(-1);
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals(0, $channel->getParam2());
        }
    }

    public function testUpdatingImpulsesPerUnit() {

        foreach ($this->device->getChannels() as $channel) {
            if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
                $this->assertEquals(0, $channel->getParam3());
                $newChannel = new IODeviceChannel();

                $newChannel->setParam3(1000000);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(1000000, $channel->getParam3());

                $newChannel->setParam3(1);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(1, $channel->getParam3());

                $newChannel->setParam3(1000001);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(1000000, $channel->getParam3());

                $newChannel->setParam3(0);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(0, $channel->getParam3());

                $newChannel->setParam3(-1);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(0, $channel->getParam3());
            }
        }
    }

    public function testUpdatingInitialValue() {

        foreach ($this->device->getChannels() as $channel) {
            if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
                $this->assertEquals(0, $channel->getParam1());
                $newChannel = new IODeviceChannel();

                $newChannel->setParam1(1000000);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(1000000, $channel->getParam1());

                $newChannel->setParam1(1);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(1, $channel->getParam1());

                $newChannel->setParam1(1000001);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(1000000, $channel->getParam1());

                $newChannel->setParam1(0);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(0, $channel->getParam1());

                $newChannel->setParam1(0);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(0, $channel->getParam1());
            }
        }
    }

    public function testUpdatingCurrency() {

        foreach ($this->device->getChannels() as $channel) {
            $this->assertEquals(null, $channel->getTextParam1());
            $newChannel = new IODeviceChannel();

            $newChannel->setTextParam1("PLN");
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals("PLN", $channel->getTextParam1());

            $newChannel->setTextParam1("ABCD");
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals("PLN", $channel->getTextParam1());

            $newChannel->setTextParam1("P");
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals("PLN", $channel->getTextParam1());

            $newChannel->setTextParam1("");
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals("", $channel->getTextParam1());

            $newChannel->setTextParam1(null);
            $this->updater->updateChannelParams($channel, $newChannel);
            $this->assertEquals(null, $channel->getTextParam1());
        }
    }

    public function testUpdatingUnit() {

        foreach ($this->device->getChannels() as $channel) {
            if ($channel->getType()->getId() == ChannelType::IMPULSECOUNTER) {
                $this->assertEquals(null, $channel->getTextParam2());
                $newChannel = new IODeviceChannel();

                $newChannel->setTextParam2("kWh");
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals("kWh", $channel->getTextParam2());

                $newChannel->setTextParam2("");
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals("", $channel->getTextParam2());

                $newChannel->setTextParam2(null);
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(null, $channel->getTextParam2());
            } elseif ($channel->getType()->getId() == ChannelType::ELECTRICITYMETER) {
                $newChannel = new IODeviceChannel();
                $newChannel->setTextParam2("kWh");
                $this->updater->updateChannelParams($channel, $newChannel);
                $this->assertEquals(null, $channel->getTextParam2());
            }
        }
    }
}
