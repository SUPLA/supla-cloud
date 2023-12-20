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
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\User;

class BelongsToUserTest extends TestCase {
    /** @var User */
    private $user;

    /** @var BelongsToUser */
    private $entity;

    protected function setUp(): void {
        $this->user = $user = $this->createMock(\SuplaBundle\Entity\Main\User::class);
        $this->user->method('getId')->willReturn(1);
        $this->entity = new Schedule($this->user);
    }

    public function testFalseIfNull() {
        $this->assertFalse($this->entity->belongsToUser(null));
    }

    public function testFalseIfNotMatches() {
        $user2 = $this->createMock(\SuplaBundle\Entity\Main\User::class);
        $user2->method('getId')->willReturn(2);
        $this->assertFalse($this->entity->belongsToUser($user2));
    }

    public function testTrueIfMatches() {
        $this->assertTrue($this->entity->belongsToUser($this->user));
    }
}
