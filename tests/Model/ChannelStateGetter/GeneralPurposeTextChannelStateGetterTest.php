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

namespace App\Tests\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Model\ChannelStateGetter\GeneralPurposeTextChannelStateGetter;
use App\Supla\SuplaServer;
use App\Tests\Integration\Traits\UnitTestHelper;
use PHPUnit\Framework\TestCase;

class GeneralPurposeTextChannelStateGetterTest extends TestCase {
    use UnitTestHelper;

    public function testGettingStateWithPrefixedValue() {
        $channel = $this->createEntityMock(IODeviceChannel::class, 111);
        $suplaServer = $this->createMock(SuplaServer::class);
        $suplaServer
            ->expects($this->once())
            ->method('getRawValue')
            ->with('GPT', $channel)
            ->willReturn("VALUE:Hello GPT!  \n");

        $stateGetter = new GeneralPurposeTextChannelStateGetter();
        $stateGetter->setSuplaServer($suplaServer);

        $state = $stateGetter->getState($channel);
        $this->assertSame(['value' => 'Hello GPT!'], $state);
    }

    public function testGettingStateWithUnexpectedValueFormat() {
        $channel = $this->createEntityMock(IODeviceChannel::class, 111);
        $suplaServer = $this->createMock(SuplaServer::class);
        $suplaServer->method('getRawValue')->willReturn("BROKEN\n");

        $stateGetter = new GeneralPurposeTextChannelStateGetter();
        $stateGetter->setSuplaServer($suplaServer);

        $state = $stateGetter->getState($channel);
        $this->assertSame(['value' => null], $state);
    }

    public function testSupportedFunctions() {
        $stateGetter = new GeneralPurposeTextChannelStateGetter();

        $this->assertEquals([
            ChannelFunction::GENERAL_PURPOSE_TEXT(),
        ], $stateGetter->supportedFunctions());
    }
}
