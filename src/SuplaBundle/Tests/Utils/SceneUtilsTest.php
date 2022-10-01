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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
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

    public function test640DeepSceneForbidden() {
        $this->expectExceptionMessage('too many operations');
        $sceneIdCounter = 1;
        $scene = $firstScene = $this->createEntityMock(Scene::class);
        // this generates a scene with 10^6 operations
        for ($depth = 0; $depth < 6; $depth++) {
            $sceneNext = $this->createEntityMock(Scene::class, ++$sceneIdCounter);
            $operations = [];
            for ($sceneCounter = 0; $sceneCounter < 10; $sceneCounter++) {
                $operation = $this->createEntityMock(SceneOperation::class);
                $operation->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
                $operation->method('getSubject')->willReturn($sceneNext);
                $operations[] = $operation;
            }
            $scene->method('getOperations')->willReturn($operations);
            $scene = $sceneNext;
        }
        $scene->method('getOperations')->willReturn([]);
        SceneUtils::ensureOperationsAreNotCyclic($firstScene);
    }

    public function testCalculatingSceneEstimatedExecutionTime() {
        $scene = $this->createEntityMock(Scene::class);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getUserDelayMs')->willReturn(10);
        $operation1->expects($this->once())->method('setDelayMs')->with(10);
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation2->method('getUserDelayMs')->willReturn(20);
        $operation2->expects($this->once())->method('setDelayMs')->with(20);
        $scene->method('getOperations')->willReturn([$operation1, $operation2]);
        $scene->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection());
        $scene->expects($this->once())->method('setEstimatedExecutionTime')->with(10 + 20);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene, $this->createMock(EntityManagerInterface::class));
    }

    public function testTakingOtherSceneTimeIntoConsideration() {
        $scene1 = $this->createEntityMock(Scene::class);
        $scene2 = $this->createEntityMock(Scene::class);
        $scene2->method('getEstimatedExecutionTime')->willReturn(100);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getUserDelayMs')->willReturn(10);
        $operation1->expects($this->once())->method('setDelayMs')->with(10);
        $operation1->method('isWaitForCompletion')->willReturn(true);
        $operation1->method('getSubject')->willReturn($scene2);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation2->method('getUserDelayMs')->willReturn(20);
        $operation2->expects($this->once())->method('setDelayMs')->with(20 + 100);
        $scene1->method('getOperations')->willReturn([$operation1, $operation2]);
        $scene1->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection());
        $scene1->expects($this->once())->method('setEstimatedExecutionTime')->with(10 + 20 + 100);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene1, $this->createMock(EntityManagerInterface::class));
    }

    public function testLastOperationWithWaitInfluencesSceneTime() {
        $scene1 = $this->createEntityMock(Scene::class);
        $scene2 = $this->createEntityMock(Scene::class);
        $scene2->method('getEstimatedExecutionTime')->willReturn(100);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getUserDelayMs')->willReturn(10);
        $operation1->expects($this->once())->method('setDelayMs')->with(10);
        $operation1->method('isWaitForCompletion')->willReturn(true);
        $operation1->method('getSubject')->willReturn($scene2);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation2->method('getUserDelayMs')->willReturn(20);
        $operation2->expects($this->once())->method('setDelayMs')->with(20);
        $scene1->method('getOperations')->willReturn([$operation2, $operation1]);
        $scene1->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection());
        $scene1->expects($this->once())->method('setEstimatedExecutionTime')->with(10 + 20 + 100);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene1, $this->createMock(EntityManagerInterface::class));
    }

    public function testRecalculatingTimeOfDependentScenes() {
        $scene1 = $this->createEntityMock(Scene::class);
        $scene2 = $this->createEntityMock(Scene::class);
        $scene1->method('getEstimatedExecutionTime')->willReturn(10);
        $scene2->method('getEstimatedExecutionTime')->willReturn(100);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getUserDelayMs')->willReturn(10);
        $operation1->expects($this->once())->method('setDelayMs')->with(10);
        $operation1->method('isWaitForCompletion')->willReturn(true);
        $operation1->method('getSubject')->willReturn($scene2);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation1->method('getOwningScene')->willReturn($scene1);
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation2->method('getUserDelayMs')->willReturn(20);
        $operation2->expects($this->once())->method('setDelayMs')->with(20);
        $scene1->method('getOperations')->willReturn([$operation1]);
        $scene2->method('getOperations')->willReturn([$operation2]);
        $scene1->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([]));
        $scene2->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([$operation1, $operation1]));
        $scene1->expects($this->once())->method('setEstimatedExecutionTime')->with(10 + 100);
        $scene2->expects($this->once())->method('setEstimatedExecutionTime')->with(20);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene2, $this->createMock(EntityManagerInterface::class));
    }
}
