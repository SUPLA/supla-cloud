<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Tests\Entity;

use App\Entity\Main\OcppChargingStation;
use App\Entity\Main\IODevice;
use App\Entity\Main\User;
use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OcppChargingStationTest extends TestCase {
    public function testNormalizesAuthenticationKeyHash(): void {
        $device = $this->createMock(IODevice::class);
        $station = new OcppChargingStation(
            $this->createMock(User::class),
            $device,
            'SP-7K4M-92QD',
            str_repeat('A', 128)
        );

        $this->assertSame(str_repeat('a', 128), $station->getAuthKeyHash());
        $this->assertSame($device, $station->getIODevice());
    }

    public function testRejectsIdentityThatCannotBeUsedAsSingleUrlPathSegment(): void {
        $this->expectException(InvalidArgumentException::class);

        new OcppChargingStation(
            $this->createMock(User::class),
            $this->createMock(IODevice::class),
            'station/1',
            str_repeat('a', 128)
        );
    }

    public function testRejectsInvalidAuthenticationKeyHash(): void {
        $this->expectException(InvalidArgumentException::class);

        new OcppChargingStation(
            $this->createMock(User::class),
            $this->createMock(IODevice::class),
            'SP-7K4M-92QD',
            'not-a-sha512-hash'
        );
    }
}
