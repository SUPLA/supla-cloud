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

namespace SuplaBundle\Tests\Integration\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Enums\ActionableSubjectType;

trait UnitTestHelper {
    public function createEntityMock(string $entityClass, int $id = 1): MockObject {
        $mock = $this->createMock($entityClass);
        $mock->method('getId')->willReturn($id);
        return $mock;
    }

    public function createSubjectMock(string $entityClass, int $id = 1): MockObject {
        $mock = $this->createEntityMock($entityClass, $id);
        if ($entityClass === IODeviceChannel::class) {
            $mock->method('getOwnSubjectType')->willReturn(ActionableSubjectType::CHANNEL);
        } elseif ($entityClass === Scene::class) {
            $mock->method('getOwnSubjectType')->willReturn(ActionableSubjectType::SCENE);
        }
        return $mock;
    }
}
