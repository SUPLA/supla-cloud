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

namespace SuplaBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Entity\Main\UserIcon;
use SuplaBundle\Enums\ChannelFunction;

class UserIconTest extends TestCase {

    /** @var \SuplaBundle\Entity\Main\UserIcon */
    private $userIcon;

    /** @before */
    public function init() {
        $this->userIcon = new \SuplaBundle\Entity\Main\UserIcon($this->createMock(User::class), ChannelFunction::POWERSWITCH());
    }

    public function testReturningIconsInDesiredOrder() {
        EntityUtils::setField($this->userIcon, 'image1', 'A');
        EntityUtils::setField($this->userIcon, 'image2', 'B');
        EntityUtils::setField($this->userIcon, 'image3', 'C');
        EntityUtils::setField($this->userIcon, 'image4', 'D');
        $icons = $this->userIcon->getImages();
        $this->assertEquals(['A', 'B', 'C', 'D'], $icons);
    }

    public function testReturningOnlyIconsThatAreSet() {
        EntityUtils::setField($this->userIcon, 'image1', 'A');
        EntityUtils::setField($this->userIcon, 'image2', 'B');
        $icons = $this->userIcon->getImages();
        $this->assertEquals(['A', 'B'], $icons);
    }
}
