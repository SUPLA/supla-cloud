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

namespace SuplaBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;

class EntityUtilsTest extends TestCase {
    public function testSetField() {
        $entity = new IODeviceChannel();
        EntityUtils::setField($entity, 'id', 123);
        $this->assertEquals(123, $entity->getId());
    }

    public function testGetField() {
        $entity = new IODeviceChannel();
        EntityUtils::setField($entity, 'id', 12);
        $this->assertEquals(12, EntityUtils::getField($entity, 'id'));
    }

    public function testMapToIds() {
        $entity1 = new IODeviceChannel();
        $entity2 = new IODeviceChannel();
        EntityUtils::setField($entity1, 'id', 1);
        EntityUtils::setField($entity2, 'id', 2);
        $this->assertEquals([1, 2], EntityUtils::mapToIds([$entity1, $entity2]));
    }

    public function testUniqueById() {
        $entity1 = new IODeviceChannel();
        $entity2 = new IODeviceChannel();
        $entity3 = new IODeviceChannel();
        EntityUtils::setField($entity1, 'id', 1);
        EntityUtils::setField($entity2, 'id', 2);
        EntityUtils::setField($entity3, 'id', 2);
        $uniqueEntities = EntityUtils::uniqueByIds([$entity1, $entity2, $entity3]);
        $this->assertEquals([$entity1, $entity2], $uniqueEntities);
    }
}
