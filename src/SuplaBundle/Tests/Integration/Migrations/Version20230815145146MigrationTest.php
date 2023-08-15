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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\User;

/**
 * @see Version20230815145146
 */
class Version20230815145146MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV2207();
        $this->initialize();
    }

    public function testUserHomeCoordinatesCalculated() {
        $user = $this->getEntityManager()->find(User::class, 1);
        $this->assertEquals(52.5, EntityUtils::getField($user, 'homeLatitude'));
        $this->assertEquals(13.36666, EntityUtils::getField($user, 'homeLongitude'));
    }
}
