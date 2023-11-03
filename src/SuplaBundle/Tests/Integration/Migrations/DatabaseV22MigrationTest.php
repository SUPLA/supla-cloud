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

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

class DatabaseV22MigrationTest extends DatabaseMigrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    /** @before */
    public function prepare() {
        $this->loadDumpV22();
        $this->migrate();
    }

    public function testMigratedCorrectly() {
        $this->transitionToOauth2Version20180518131234();
    }

    /**
     * @see Version20180518131234
     */
    private function transitionToOauth2Version20180518131234() {
        $this->assertCompatFieldsAreSet();
    }

    private function assertCompatFieldsAreSet() {
        $user = $this->getEntityManager()->find(User::class, 1);
        $user->setOAuthOldApiCompatEnabled();
        $this->assertEquals('api_1', $user->getUsername());
        $this->assertEquals('$2y$04$0ydWylMOTNDnSA/GNhl.nulSldoCVbKCo4AyT3wrXnZwncnA2iqaa', $user->getPassword());
    }
}
