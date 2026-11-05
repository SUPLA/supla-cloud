<?php

namespace App\Tests\Model;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFlags;
use App\Enums\ChannelFunction;
use App\Model\CalcfgQueueEligibility;
use PHPUnit\Framework\TestCase;

class CalcfgQueueEligibilityTest extends TestCase {
    /**
     * @dataProvider channelEligibilityCases
     * @param array<string, bool> $expected
     */
    public function testChannelEligibilityUsesCapabilitiesAndQueueSupport(
        int $subDeviceId,
        int $channelFlags,
        bool $sleepMode,
        bool $syncDone,
        bool $muteSupported,
        bool $ocrSupported,
        array $expected
    ): void {
        $device = $this->createMock(IODevice::class);
        $device->method('isSleepModeEnabled')->willReturn($sleepMode);
        $device->method('isSyncDoneSupported')->willReturn($syncDone);
        $device->method('getFlags')->willReturn([
            'identifyDeviceAvailable' => true,
            'remoteRestartAvailable' => true,
        ]);

        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getIoDevice')->willReturn($device);
        $channel->method('getSubDeviceId')->willReturn($subDeviceId);
        $channel->method('getFlags')->willReturn($channelFlags);

        self::assertSame($expected, CalcfgQueueEligibility::forChannel($channel, $muteSupported, $ocrSupported));
    }

    /** @return array<string, array{int, int, bool, bool, bool, bool, array<string, bool>}> */
    public static function channelEligibilityCases(): array {
        $bothSubdeviceFlags = ChannelFlags::IDENTIFY_SUBDEVICE_AVAILABLE | ChannelFlags::RESTART_SUBDEVICE_AVAILABLE;
        return [
            'sleeping sync-capable channel endpoint flags, legacy channel id' => [0, $bothSubdeviceFlags, true, true, true, true, [
                'muteAlarm' => true, 'ocr:takePhoto' => true, 'identify' => false, 'restart' => false,
            ]],
            'sleeping sync-capable channel endpoint flags, subdevice id' => [7, $bothSubdeviceFlags, true, true, true, true, [
                'muteAlarm' => true, 'ocr:takePhoto' => true, 'identify' => false, 'restart' => false,
            ]],
            'absent channel endpoint flags do not advertise actions' => [7, 0, true, true, false, false, [
                'muteAlarm' => false, 'ocr:takePhoto' => false, 'identify' => false, 'restart' => false,
            ]],
            'device target does not inherit subdevice command queueability' => [0, 0, true, true, true, true, [
                'muteAlarm' => true, 'ocr:takePhoto' => true, 'identify' => false, 'restart' => false,
            ]],
            'legacy sleeping channels do not advertise queue actions' => [7, $bothSubdeviceFlags, true, false, true, true, [
                'muteAlarm' => false, 'ocr:takePhoto' => false, 'identify' => false, 'restart' => false,
            ]],
            'awake channels do not advertise queue actions' => [0, $bothSubdeviceFlags, false, true, true, true, [
                'muteAlarm' => false, 'ocr:takePhoto' => false, 'identify' => false, 'restart' => false,
            ]],
            'unsupported mute and OCR capabilities remain unavailable' => [0, $bothSubdeviceFlags, true, true, false, false, [
                'muteAlarm' => false, 'ocr:takePhoto' => false, 'identify' => false, 'restart' => false,
            ]],
        ];
    }

    /** @dataProvider muteAlarmFunctions */
    public function testMuteAlarmSupportIsBasedOnStableChannelFunction(ChannelFunction $function, bool $supported): void {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getFunction')->willReturn($function);
        self::assertSame($supported, CalcfgQueueEligibility::supportsMuteAlarm($channel));
    }

    /** @return array<string, array{ChannelFunction, bool}> */
    public static function muteAlarmFunctions(): array {
        return [
            'container' => [ChannelFunction::CONTAINER(), true],
            'septic tank' => [ChannelFunction::SEPTIC_TANK(), true],
            'water tank' => [ChannelFunction::WATER_TANK(), true],
            'thermometer' => [ChannelFunction::THERMOMETER(), false],
        ];
    }
}
