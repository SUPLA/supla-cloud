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

namespace App\Tests\Entity;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\Location;
use App\Entity\Main\Scene;
use App\Entity\Main\SceneOperation;
use App\Enums\ChannelFunctionAction;
use App\Tests\Integration\Traits\UnitTestHelper;
use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SceneTest extends TestCase {
    use UnitTestHelper;

    public function testSettingOwningScene() {
        $scene = new Scene($this->createMock(Location::class));
        $operation = new SceneOperation($this->createMock(IODeviceChannel::class), ChannelFunctionAction::OPEN());
        $operation2 = new SceneOperation($this->createMock(IODeviceChannel::class), ChannelFunctionAction::OPEN());
        $scene->setOpeartions([$operation, $operation2]);
        $this->assertSame($scene, $operation->getOwningScene());
        $this->assertSame($scene, $operation2->getOwningScene());
        $this->assertContains($operation, $scene->getOperations());
        $this->assertContains($operation2, $scene->getOperations());
    }

    public function testAddingOperationsManyTimes() {
        $scene = new Scene($this->createMock(Location::class));
        $operation = new SceneOperation($this->createMock(IODeviceChannel::class), ChannelFunctionAction::OPEN());
        $operation2 = new SceneOperation($this->createMock(IODeviceChannel::class), ChannelFunctionAction::OPEN());
        $scene->setOpeartions([$operation, $operation2]);
        $scene->setOpeartions([$operation2, $operation]);
        $this->assertCount(2, $scene->getOperations());
    }

    public function testAcceptsMaximumDelay() {
        $operation = new SceneOperation($this->createMock(IODeviceChannel::class), ChannelFunctionAction::OPEN(), [], 172800000);
        $this->assertEquals(172800000, $operation->getUserDelayMs());
    }

    public function testRejectsDelayAboveMaximum() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Maximum delay is 48 hours.');
        new SceneOperation($this->createMock(IODeviceChannel::class), ChannelFunctionAction::OPEN(), [], 172800001);
    }

    public function testDelayOnlyAcceptsMaximumDelay() {
        $operation = SceneOperation::delayOnly(172800000);
        $this->assertEquals(172800000, $operation->getUserDelayMs());
    }
}
