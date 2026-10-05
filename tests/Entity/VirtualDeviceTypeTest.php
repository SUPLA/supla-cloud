<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Tests\Entity;

use App\Entity\EntityUtils;
use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\OcppChargingStation;
use App\Entity\Main\User;
use App\Enums\VirtualDeviceType;
use PHPUnit\Framework\TestCase;

class VirtualDeviceTypeTest extends TestCase {
    public function testVirtualTypeFollowsStationAssociationWithoutChangingBoolean(): void {
        $device = new IODevice();
        $this->assertNull($device->getVirtualType());
        EntityUtils::setField($device, 'isVirtual', true);
        $this->assertSame(VirtualDeviceType::DATA_SOURCE, $device->getVirtualType());
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'iodevice', $device);
        EntityUtils::setField($channel, 'isVirtual', true);
        $this->assertTrue($channel->isDataSource());
        $this->assertTrue($channel->isDeletable());
        new OcppChargingStation($this->createMock(User::class), $device, 'U1-ABCD', str_repeat('a', 128));
        $this->assertTrue($device->isVirtual());
        $this->assertTrue($channel->isVirtual());
        $this->assertSame(VirtualDeviceType::OCPP, $channel->getVirtualType());
        $this->assertFalse($device->isDataSource());
        $this->assertFalse($channel->isDataSource());
        $this->assertFalse($channel->isDeletable());
    }
}
