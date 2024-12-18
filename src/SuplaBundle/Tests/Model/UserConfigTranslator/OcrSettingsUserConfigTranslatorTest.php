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

namespace SuplaBundle\Tests\Model\UserConfigTranslator;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\UserConfigTranslator\OcrSettingsUserConfigTranslator;
use SuplaBundle\Supla\SuplaOcrClient;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class OcrSettingsUserConfigTranslatorTest extends TestCase {
    use UnitTestHelper;

    /** @var OcrSettingsUserConfigTranslator */
    private $configTranslator;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $channel;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new OcrSettingsUserConfigTranslator($this->createMock(SuplaOcrClient::class));
        $this->channel = new IODeviceChannel();
        $this->channel->setFunction(ChannelFunction::IC_GASMETER());
        EntityUtils::setField($this->channel, 'properties', json_encode([
            'ocr' => ['availableLightingModes' => ['OFF', 'AUTO', 'ALWAYS_ON']],
        ]));
    }

    public function testDisablingOcr() {
        $this->configTranslator->setConfig($this->channel, ['ocr' => ['enabled' => false]]);
        $config = $this->configTranslator->getConfig($this->channel);
        $this->assertFalse($config['ocr']['enabled']);
        $this->assertEquals(60, $config['ocr']['photoIntervalSec']);
        $this->assertArrayNotHasKey('enabled', $this->channel->getUserConfigValue('ocr'));
        $this->assertEquals(0, $this->channel->getUserConfigValue('ocr')['photoIntervalSec']);
    }

    public function testTryingToSetInvalidKey() {
        $this->expectExceptionMessage('is not an element of the valid values');
        $this->configTranslator->setConfig($this->channel, ['ocr' => ['unicorn' => 4]]);
    }
}
