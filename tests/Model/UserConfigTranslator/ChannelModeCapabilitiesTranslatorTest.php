<?php

namespace App\Tests\Model\UserConfigTranslator;

use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFlags;
use App\Enums\ChannelFunction;
use App\Model\UserConfigTranslator\ChannelModeCapabilitiesTranslator;
use PHPUnit\Framework\TestCase;

class ChannelModeCapabilitiesTranslatorTest extends TestCase {
    public function testButtonCapabilitiesAreReadOnlyAndIndependent(): void {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::ACTION_TRIGGER());
        EntityUtils::setField($channel, 'flags', ChannelFlags::BUTTON_TEMPORARY_UNLOCK_SUPPORTED | ChannelFlags::HAS_EXTENDED_CHANNEL_STATE);
        $translator = new ChannelModeCapabilitiesTranslator();
        $this->assertTrue($translator->supports($channel));
        $this->assertSame(['buttonModeSupported' => false, 'buttonTemporaryUnlockSupported' => true], $translator->getConfig($channel));
        $translator->setConfig($channel, ['buttonModeSupported' => true, 'buttonTemporaryUnlockSupported' => false]);
        $this->assertSame(['buttonModeSupported' => false, 'buttonTemporaryUnlockSupported' => true], $translator->getConfig($channel));
    }

    public function testRelayCapabilitiesAndUnrelatedFunction(): void {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::LIGHTSWITCH());
        EntityUtils::setField($channel, 'flags', ChannelFlags::RELAY_MODE_START_SUPPORTED | ChannelFlags::RELAY_MODE_NOT_SET_SUPPORTED);
        $translator = new ChannelModeCapabilitiesTranslator();
        $this->assertSame([
            'relayModeStartSupported' => true,
            'relayModeForcedSupported' => false,
            'relayModeAutomaticSupported' => false,
            'relayModeNotSetSupported' => true,
        ], $translator->getConfig($channel));
        $channel->setFunction(ChannelFunction::THERMOMETER());
        $this->assertFalse($translator->supports($channel));
        $this->assertSame([], $translator->getConfig($channel));
    }
}
