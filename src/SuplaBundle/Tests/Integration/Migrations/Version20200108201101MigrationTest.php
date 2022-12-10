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

namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Entity\Main\IODeviceChannel;

/**
 * @see Version20200108201101
 */
class Version20200108201101MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV23();
        $this->initialize();
    }

    public function testImpulseCounterInitialValueIsMigrated() {
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 67);
        $this->assertNotEmpty($channel->getUserConfig());
        $this->assertEquals(103, $channel->getUserConfig()['initialValue']);
    }
}
