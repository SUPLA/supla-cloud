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

namespace SuplaBundle\Tests\Integration\Command;

use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class DeleteUserCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    protected function setUp(): void {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->createDevice($location, [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]]);
        $this->getEntityManager()->refresh($this->user);
    }

    public function testDeletingUserNoAd() {
        SuplaAutodiscoverMock::clear(false);
        $output = $this->executeCommand('supla:delete-user ' . $this->user->getUsername());
        $this->assertStringContainsString('has been deleted', $output);
        $this->assertNull($this->getEntityManager()->find(Location::class, 2));
        $this->assertCount(0, SuplaAutodiscoverMock::$requests);
    }

    public function testDeletingFromAd() {
        SuplaAutodiscoverMock::clear(true);
        SuplaAutodiscoverMock::$userMapping[$this->user->getUsername()] = 'supla.local';
        SuplaAutodiscoverMock::mockResponse('users/supler%40supla.org', [], 204, 'DELETE');
        $output = $this->executeCommand('supla:delete-user ' . $this->user->getUsername());
        $this->assertStringContainsString('has been deleted', $output);
        $this->assertNull($this->getEntityManager()->find(Location::class, 2));
        $this->assertCount(2, SuplaAutodiscoverMock::$requests);
    }

    public function testNotDeletingFromAdIfDifferentTarget() {
        SuplaAutodiscoverMock::clear(true);
        SuplaAutodiscoverMock::$userMapping[$this->user->getUsername()] = 'supla2.local';
        $output = $this->executeCommand('supla:delete-user ' . $this->user->getUsername());
        $this->assertStringContainsString('has been deleted', $output);
        $this->assertNull($this->getEntityManager()->find(Location::class, 2));
        $this->assertCount(1, SuplaAutodiscoverMock::$requests);
    }

    public function testNotDeletingFromAdIfNoTarget() {
        SuplaAutodiscoverMock::clear(true);
        $output = $this->executeCommand('supla:delete-user ' . $this->user->getUsername());
        $this->assertStringContainsString('has been deleted', $output);
        $this->assertNull($this->getEntityManager()->find(Location::class, 2));
        $this->assertCount(1, SuplaAutodiscoverMock::$requests);
    }

    public function testDeletingUserWithHyphenInUsername() {
        SuplaAutodiscoverMock::clear(false);
        $this->user = $this->createConfirmedUser('-zupler@supla.org');
        $location = $this->createLocation($this->user);
        $this->createDeviceFull($location);
        $this->getEntityManager()->refresh($this->user);
        $output = $this->executeCommand(['command' => 'supla:delete-user', 'username' => $this->user->getUsername()]);
        $this->assertStringContainsString('has been deleted', $output);
    }
}
