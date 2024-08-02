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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\UserConfigTranslator\ImpulseCounterUserConfigTranslator;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class ImpulseCounterUserConfigTranslatorTest extends TestCase {
    use UnitTestHelper;

    /** @var ImpulseCounterUserConfigTranslator */
    private $configTranslator;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $channel;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new ImpulseCounterUserConfigTranslator();
        $this->channel = new IODeviceChannel();
        $this->channel->setFunction(ChannelFunction::IC_GASMETER());
    }

    public function testStoringInitialValueMultiplied() {
        $this->configTranslator->setConfig($this->channel, ['initialValue' => 100.343]);
        $config = $this->configTranslator->getConfig($this->channel);
        $this->assertEquals(100.343, $config['initialValue']);
        $this->assertEquals(100343, $this->channel->getUserConfigValue('initialValue'));
    }
}
