<?php

namespace App\Tests\Model\UserConfigTranslator;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFlags;
use App\Model\UserConfigTranslator\BatteryUserConfigTranslator;
use PHPUnit\Framework\TestCase;

class BatteryUserConfigTranslatorTest extends TestCase {
    /** @dataProvider batteryStates */
    public function testBatteryAvailability(array $state, bool $available) {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getFlags')->willReturn(ChannelFlags::HAS_EXTENDED_CHANNEL_STATE);
        $channel->method('getLastKnownChannelState')->willReturn($state);

        $this->assertSame(['isBatteryAvailable' => $available], (new BatteryUserConfigTranslator())->getConfig($channel));
    }

    public static function batteryStates(): array {
        return [
            'numeric level' => [['batteryLevel' => 72], true],
            'null level retains legacy behavior' => [['batteryLevel' => null], true],
            'OK' => [['batteryState' => 'OK'], true],
            'LOW' => [['batteryState' => 'LOW'], true],
            'empty' => [[], false],
            'device state' => [['batteryState' => 'LOW', 'deviceBatteryState' => true], true],
            'device marker alone' => [['deviceBatteryState' => true], false],
            'unexpected string' => [['batteryState' => 'UNKNOWN'], false],
            'lowercase string' => [['batteryState' => 'low'], false],
            'boolean' => [['batteryState' => true], false],
            'array' => [['batteryState' => ['LOW']], false],
            'level and state' => [['batteryLevel' => 72, 'batteryState' => 'LOW'], true],
            'level and invalid state' => [['batteryLevel' => 72, 'batteryState' => 'UNKNOWN'], true],
            'device level' => [['batteryLevel' => 72, 'deviceBatteryLevel' => true], true],
        ];
    }

    public function testUnsupportedExtendedStateDoesNotExposeAvailability() {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getFlags')->willReturn(0);
        $channel->expects($this->never())->method('getLastKnownChannelState');

        $this->assertSame([], (new BatteryUserConfigTranslator())->getConfig($channel));
    }

    public function testBatteryCoverAvailabilityIsUnchanged() {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getFlags')->willReturn(ChannelFlags::BATTERY_COVER_AVAILABLE);

        $this->assertSame(['isBatteryCoverAvailable' => true], (new BatteryUserConfigTranslator())->getConfig($channel));
    }
}
