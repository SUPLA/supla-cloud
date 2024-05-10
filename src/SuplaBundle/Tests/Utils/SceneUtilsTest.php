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
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
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
        $operation1->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
        $operation2->method('getAction')->willReturn(ChannelFunctionAction::INTERRUPT_AND_EXECUTE());
        $operation3->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
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
        $operation1->method('getOwningScene')->willReturn($scene1);
        $operation2->method('getOwningScene')->willReturn($scene1);
        $operation1->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
        $operation2->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
        $scene1->method('getOperations')->willReturn([$operation1, $operation2]);
        $scene2->method('getOperations')->willReturn([]);
        $scene1->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([]));
        $scene2->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([$operation1, $operation2]));
        SceneUtils::ensureOperationsAreNotCyclic($scene1);
        SceneUtils::ensureOperationsAreNotCyclic($scene2);
        $this->assertTrue(true);
    }

    public function testCycleIfReferencesItself() {
        $this->expectExceptionMessage('forbidden to have recursive');
        $scene1 = $this->createEntityMock(Scene::class, 1);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation1->method('getSubject')->willReturn($scene1);
        $operation1->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
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
                $operation->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
                $operations[] = $operation;
            }
            $scene->method('getOperations')->willReturn($operations);
            $scene = $sceneNext;
        }
        $scene->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([]));
        $scene->method('getOperations')->willReturn([]);
        SceneUtils::ensureOperationsAreNotCyclic($firstScene);
    }

    public function testCalculatingSceneEstimatedExecutionTime() {
        $scene = new Scene($this->createEntityMock(Location::class));
        $operation1 = new SceneOperation($this->createSubjectMock(IODeviceChannel::class), ChannelFunctionAction::TURN_ON(), [], 10);
        $operation2 = new SceneOperation($this->createSubjectMock(IODeviceChannel::class), ChannelFunctionAction::TURN_ON(), [], 20);
        $scene->setOpeartions([$operation1, $operation2]);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene, $this->createMock(EntityManagerInterface::class));
        $this->assertEquals(10, $operation1->getDelayMs());
        $this->assertEquals(20, $operation2->getDelayMs());
        $this->assertEquals(30, $scene->getEstimatedExecutionTime());
    }

    public function testTakingOtherSceneTimeIntoConsideration() {
        $scene1 = $this->createSubjectMock(Scene::class);
        $scene2 = $this->createSubjectMock(Scene::class);
        $scene2->method('getEstimatedExecutionTime')->willReturn(100);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getUserDelayMs')->willReturn(10);
        $operation1->expects($this->once())->method('setDelayMs')->with(10);
        $operation1->method('isWaitForCompletion')->willReturn(true);
        $operation1->method('getSubject')->willReturn($scene2);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation1->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation2->method('getUserDelayMs')->willReturn(20);
        $operation2->method('getSubjectType')->willReturn(ActionableSubjectType::CHANNEL());
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
        $operation1->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation2->method('getUserDelayMs')->willReturn(20);
        $operation2->method('getSubjectType')->willReturn(ActionableSubjectType::CHANNEL());
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
        $operation1->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
        $operation1->method('getOwningScene')->willReturn($scene1);
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation2->method('getUserDelayMs')->willReturn(20);
        $operation2->method('getSubjectType')->willReturn(ActionableSubjectType::CHANNEL());
        $operation2->expects($this->once())->method('setDelayMs')->with(20);
        $scene1->method('getOperations')->willReturn([$operation1]);
        $scene2->method('getOperations')->willReturn([$operation2]);
        $scene1->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([]));
        $scene2->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([$operation1, $operation1]));
        $scene1->expects($this->once())->method('setEstimatedExecutionTime')->with(10 + 100);
        $scene2->expects($this->once())->method('setEstimatedExecutionTime')->with(20);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene2, $this->createMock(EntityManagerInterface::class));
    }

    /**
     * @see https://github.com/SUPLA/supla-cloud/issues/641
     */
    public function testDetectingTooManyExecutionsInBothDirections() {
        $sceneChild = new Scene($this->createEntityMock(Location::class));
        EntityUtils::setField($sceneChild, 'id', 2);
        $operations = [];
        for ($operationCounter = 0; $operationCounter < 100; $operationCounter++) {
            $operation = $this->createEntityMock(SceneOperation::class);
            $operation->method('getSubjectType')->willReturn(ActionableSubjectType::CHANNEL());
            $operation->method('getOwningScene')->willReturn($sceneChild);
            $operations[] = $operation;
        }
        $sceneChild->setOpeartions($operations);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($sceneChild, $this->createMock(EntityManagerInterface::class));
        $this->assertEquals(100, $sceneChild->getCommandExecutionsCount());
        $sceneParent = new Scene($this->createEntityMock(Location::class));
        EntityUtils::setField($sceneParent, 'id', 3);
        $operations = [];
        for ($operationCounter = 0; $operationCounter < 10; $operationCounter++) {
            $operation = $this->createEntityMock(SceneOperation::class);
            $operation->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
            $operation->method('getSubject')->willReturn($sceneChild);
            $operation->method('getOwningScene')->willReturn($sceneParent);
            $operation->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
            $operations[] = $operation;
        }
        $sceneParent->setOpeartions($operations);
        EntityUtils::setField($sceneChild, 'sceneOperations', new ArrayCollection($operations));
        SceneUtils::ensureOperationsAreNotCyclic($sceneChild);
        try {
            SceneUtils::ensureOperationsAreNotCyclic($sceneParent);
            $this->fail('The above should fail and it worked even before the fix.');
        } catch (\InvalidArgumentException $e) {
        }
        try {
            SceneUtils::updateDelaysAndEstimatedExecutionTimes($sceneParent, $this->createMock(EntityManagerInterface::class));
            $this->fail('The above should fail and it worked even before the fix.');
        } catch (\InvalidArgumentException $e) {
        }
        $this->expectExceptionMessage('too many operations');
        $sceneChild->setCommandExecutionsCount(0);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($sceneChild, $this->createMock(EntityManagerInterface::class));
    }

    public function testCreatingSceneThatExecutesSceneThatExecutesSceneYouKnowWhatIMean() {
        $scene = $this->createEntityMock(Scene::class);
        $scene->method('getOperations')->willReturn([]);
        $startTime = microtime(true);
        for ($i = 0; $i < 50; $i++) {
            $newScene = $this->createEntityMock(Scene::class, $i + 2);
            $operation = $this->createEntityMock(SceneOperation::class);
            $operation->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
            $operation->method('getSubject')->willReturn($scene);
            $operation->method('getOwningScene')->willReturn($newScene);
            $operation->method('isWaitForCompletion')->willReturn(true);
            $operation->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
            $scene->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([$operation]));
            $newScene->method('getOperations')->willReturn([$operation]);
            SceneUtils::ensureOperationsAreNotCyclic($scene);
            SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene, $this->createMock(EntityManagerInterface::class));
            $scene = $newScene;
        }
        $this->assertLessThan(1, microtime(true) - $startTime);
    }

    public function testNoCycleIfSceneActionIsStop() {
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
        $operation1->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
        $operation2->method('getAction')->willReturn(ChannelFunctionAction::EXECUTE());
        $operation3->method('getAction')->willReturn(ChannelFunctionAction::INTERRUPT());
        $scene1->method('getOperations')->willReturn([$operation1]);
        $scene2->method('getOperations')->willReturn([$operation2]);
        $scene3->method('getOperations')->willReturn([$operation3]);
        SceneUtils::ensureOperationsAreNotCyclic($scene1);
        $this->assertTrue(true);
    }

    public function testCanEstimateTimeIfScenesAreCyclicThroughInterruptAction() {
        $scene1 = $this->createEntityMock(Scene::class, 1);
        $scene2 = $this->createEntityMock(Scene::class, 2);
        $operation1 = $this->createEntityMock(SceneOperation::class);
        $operation2 = $this->createEntityMock(SceneOperation::class);
        $operation1->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation2->method('getSubjectType')->willReturn(ActionableSubjectType::SCENE());
        $operation1->method('getOwningScene')->willReturn($scene1);
        $operation2->method('getOwningScene')->willReturn($scene2);
        $operation1->method('getSubject')->willReturn($scene2);
        $operation2->method('getSubject')->willReturn($scene1);
        $operation1->method('getAction')->willReturn(ChannelFunctionAction::INTERRUPT());
        $operation2->method('getAction')->willReturn(ChannelFunctionAction::INTERRUPT());
        $operation1->method('getUserDelayMs')->willReturn(100);
        $operation2->method('getUserDelayMs')->willReturn(100);
        $scene1->method('getOperations')->willReturn([$operation1]);
        $scene2->method('getOperations')->willReturn([$operation2]);
        $scene1->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([$operation2]));
        $scene2->method('getOperationsThatReferToThisScene')->willReturn(new ArrayCollection([$operation1]));
        $scene1->expects($this->once())->method('setEstimatedExecutionTime')->with(100);
        SceneUtils::updateDelaysAndEstimatedExecutionTimes($scene1, $this->createMock(EntityManagerInterface::class));
    }
}
