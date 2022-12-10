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

use SuplaBundle\Entity\Main\Scene;

/**
 * @see Version20220929090847
 */
class Version20220929090847MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV2207();
        $this->initialize();
    }

    public function testCalculatedSceneEstimatedExecutionTime() {
        $this->assertEquals(2000, $this->getEntityManager()->find(Scene::class, 1)->getEstimatedExecutionTime());
        $this->assertEquals(62000, $this->getEntityManager()->find(Scene::class, 2)->getEstimatedExecutionTime());
        $this->assertEquals(61000, $this->getEntityManager()->find(Scene::class, 3)->getEstimatedExecutionTime());
        $this->assertEquals(3000, $this->getEntityManager()->find(Scene::class, 4)->getEstimatedExecutionTime());
        $this->assertEquals(32000, $this->getEntityManager()->find(Scene::class, 5)->getEstimatedExecutionTime());
    }
}
