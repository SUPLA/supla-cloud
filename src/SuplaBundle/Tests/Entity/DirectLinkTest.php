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
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

class DirectLinkTest extends TestCase {
    public function testGeneratingSlug() {
        $directLink = new DirectLink($this->createMock(IODeviceChannel::class));
        $slug = $directLink->generateSlug(new PlaintextPasswordEncoder());
        $this->assertGreaterThanOrEqual(DirectLink::SLUG_LENGTH_MIN, strlen($slug));
        $this->assertLessThanOrEqual(DirectLink::SLUG_LENGTH_MAX, strlen($slug));
        $this->assertNotContains('0', $slug);
    }

    public function testCreatingForChannelGroup() {
        $directLink = new DirectLink($this->createMock(IODeviceChannelGroup::class));
        $slug = $directLink->generateSlug(new PlaintextPasswordEncoder());
        $this->assertGreaterThanOrEqual(DirectLink::SLUG_LENGTH_MIN, strlen($slug));
        $this->assertLessThanOrEqual(DirectLink::SLUG_LENGTH_MAX, strlen($slug));
        $this->assertNotContains('0', $slug);
    }

    public function testCannotGenerateSlugTwice() {
        $this->expectException(\InvalidArgumentException::class);
        $directLink = new DirectLink($this->createMock(IODeviceChannel::class));
        $directLink->generateSlug(new PlaintextPasswordEncoder());
        $directLink->generateSlug(new PlaintextPasswordEncoder());
    }

    public function testCheckingSlug() {
        $directLink = new DirectLink($this->createMock(IODeviceChannel::class));
        $slug = $directLink->generateSlug(new PlaintextPasswordEncoder());
        $this->assertTrue($directLink->isValidSlug($slug, new PlaintextPasswordEncoder()));
        $this->assertFalse($directLink->isValidSlug($slug . 'X', new PlaintextPasswordEncoder()));
    }
}
