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

use App\Entity\Main\DirectLink;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\IODeviceChannelGroup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher;

class DirectLinkTest extends TestCase {
    public function testGeneratingSlug() {
        $directLink = new \App\Entity\Main\DirectLink($this->createMock(IODeviceChannel::class));
        $slug = $directLink->generateSlug(new PlaintextPasswordHasher());
        $this->assertGreaterThanOrEqual(\App\Entity\Main\DirectLink::SLUG_LENGTH_MIN, strlen($slug));
        $this->assertLessThanOrEqual(\App\Entity\Main\DirectLink::SLUG_LENGTH_MAX, strlen($slug));
        $this->assertStringNotContainsString('0', $slug);
    }

    public function testCreatingForChannelGroup() {
        $directLink = new DirectLink($this->createMock(IODeviceChannelGroup::class));
        $slug = $directLink->generateSlug(new PlaintextPasswordHasher());
        $this->assertGreaterThanOrEqual(DirectLink::SLUG_LENGTH_MIN, strlen($slug));
        $this->assertLessThanOrEqual(\App\Entity\Main\DirectLink::SLUG_LENGTH_MAX, strlen($slug));
        $this->assertStringNotContainsString('0', $slug);
    }

    public function testCannotGenerateSlugTwice() {
        $this->expectException(\InvalidArgumentException::class);
        $directLink = new \App\Entity\Main\DirectLink($this->createMock(IODeviceChannel::class));
        $directLink->generateSlug(new PlaintextPasswordHasher());
        $directLink->generateSlug(new PlaintextPasswordHasher());
    }

    public function testCheckingSlug() {
        $directLink = new \App\Entity\Main\DirectLink($this->createMock(\App\Entity\Main\IODeviceChannel::class));
        $slug = $directLink->generateSlug(new PlaintextPasswordHasher());
        $this->assertTrue($directLink->isValidSlug($slug, new PlaintextPasswordHasher()));
        $this->assertFalse($directLink->isValidSlug($slug . 'X', new PlaintextPasswordHasher()));
    }
}
