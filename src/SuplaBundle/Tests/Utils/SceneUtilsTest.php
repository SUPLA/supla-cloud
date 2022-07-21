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

namespace SuplaBundle\Tests\Utils;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;
use SuplaBundle\Utils\SceneUtils;

class SceneUtilsTest extends TestCase {
    use UnitTestHelper;

    public function testDetectingCycles() {
        $this->expectExceptionMessage('forbidden to have recursive');
        $scene1 = $this->createEntityMock(Scene::class, 1);
        $scene2 = $this->createEntityMock(Scene::class, 2);
        $scene3 = $this->createEntityMock(Scene::class, 3);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation3 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation2->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation3->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation1->method('getSubject')->willReturn($scene2);
        $operation2->method('getSubject')->willReturn($scene3);
        $operation3->method('getSubject')->willReturn($scene1);
        $scene1->method('getOperations')->willReturn([$operation1]);
        $scene2->method('getOperations')->willReturn([$operation2]);
        $scene3->method('getOperations')->willReturn([$operation3]);
        SceneUtils::ensureOperationsAreNotCyclic($scene1);
    }

    public function testNoCycleIfSceneAddedTwice() {
        $scene1 = $this->createEntityMock(Scene::class, 1);
        $scene2 = $this->createEntityMock(Scene::class, 2);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation2->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation1->method('getSubject')->willReturn($scene2);
        $operation2->method('getSubject')->willReturn($scene2);
        $scene1->method('getOperations')->willReturn([$operation1, $operation2]);
        $scene2->method('getOperations')->willReturn([]);
        SceneUtils::ensureOperationsAreNotCyclic($scene1);
        $this->assertTrue(true);
    }

    public function testCycleIfReferencesItself() {
        $this->expectExceptionMessage('forbidden to have recursive');
        $scene1 = $this->createEntityMock(Scene::class, 1);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation1->method('getSubject')->willReturn($scene1);
        $scene1->method('getOperations')->willReturn([$operation1]);
        SceneUtils::ensureOperationsAreNotCyclic($scene1);
    }
}
