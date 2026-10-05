<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Tests\Model\Ocpp;

use App\Entity\EntityUtils;
use App\Entity\Main\IODevice;
use App\Entity\Main\Location;
use App\Entity\Main\OcppChargingStation;
use App\Entity\Main\User;
use App\Model\Ocpp\OcppConfiguration;
use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OcppConfigurationTest extends TestCase {
    public function testConnectionSettingsDoNotExposeCredentials(): void {
        $device = new IODevice();
        EntityUtils::setField($device, 'location', $this->createMock(Location::class));
        EntityUtils::setField($device, 'name', 'Original charger name');
        $station = new OcppChargingStation($this->createMock(User::class), $device, 'U1-ABCD', str_repeat('a', 128));
        $configuration = new OcppConfiguration('wss://ocpp.example.org:443/');
        $this->assertTrue($configuration->isEnabled());
        $settings = $configuration->describe($station);
        $this->assertSame('wss://ocpp.example.org:443/U1-ABCD', $settings['url']);
        $this->assertSame('wss://ocpp.example.org:443', $settings['baseUrl']);
        $this->assertSame('U1-ABCD', $settings['username']);
        $this->assertSame('Original charger name', $settings['name']);
        $device->setComment('Renamed charger');
        $this->assertSame('Renamed charger', $configuration->describe($station)['name']);
        $this->assertArrayNotHasKey('authorizationKey', $settings);
        $this->assertArrayNotHasKey('authKeyHash', $settings);
        $this->assertFalse((new OcppConfiguration(''))->isEnabled());
    }

    /** @dataProvider invalidUrls */
    public function testRejectsUnsafeOrNonBaseUrls(string $url): void {
        $this->expectException(InvalidArgumentException::class);
        new OcppConfiguration($url);
    }

    public static function invalidUrls(): array {
        return array_map(fn($url) => [$url], [
            'ws://ocpp.example.org', 'https://ocpp.example.org', 'wss://user:pass@ocpp.example.org',
            'wss://ocpp.example.org/station', 'wss://ocpp.example.org?key=secret', 'wss://ocpp.example.org#fragment', 'not-a-url',
        ]);
    }
}
