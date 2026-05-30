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

namespace App\Tests\Model\UserConfigTranslator;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Model\UserConfigTranslator\GeneralPurposeTextConfigTranslator;
use PHPUnit\Framework\TestCase;

class GeneralPurposeTextConfigTranslatorTest extends TestCase {
    private GeneralPurposeTextConfigTranslator $configTranslator;
    private IODeviceChannel $channel;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new GeneralPurposeTextConfigTranslator();
        $this->channel = new IODeviceChannel();
        $this->channel->setFunction(ChannelFunction::GENERAL_PURPOSE_TEXT());
    }

    public function testGettingDefaultConfig() {
        $config = $this->configTranslator->getConfig($this->channel);
        $this->assertTrue($config['keepHistory']);
        $this->assertSame(0, $config['refreshIntervalMs']);
    }

    public function testSettingConfig() {
        $this->configTranslator->setConfig($this->channel, [
            'keepHistory' => false,
            'refreshIntervalMs' => 1234,
        ]);

        $this->assertFalse($this->channel->getUserConfigValue('keepHistory'));
        $this->assertSame(1234, $this->channel->getUserConfigValue('refreshIntervalMs'));
    }

    public function testSupportsGeneralPurposeTextOnly() {
        $this->assertTrue($this->configTranslator->supports($this->channel));

        $otherChannel = new IODeviceChannel();
        $otherChannel->setFunction(ChannelFunction::THERMOMETER());
        $this->assertFalse($this->configTranslator->supports($otherChannel));
    }

    /** @dataProvider invalidConfigs */
    public function testSettingInvalidConfig(array $config) {
        $this->expectException(\InvalidArgumentException::class);
        $this->configTranslator->setConfig($this->channel, $config);
    }

    public static function invalidConfigs(): array {
        return [
            [['refreshIntervalMs' => -1]],
            [['refreshIntervalMs' => 65536]],
            [['refreshIntervalMs' => '1000']],
        ];
    }
}
