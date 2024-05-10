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

namespace SuplaBundle\Utils;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;

final class SceneUtils {
    private const MAX_OPERATIONS_EXECUTIONS_PER_SCENE = 640;

    private function __construct() {
    }

    /** @see https://gist.github.com/jmullan/450465/77bdaa2a57e73ecee8ca6d9449aad3f74b379ca5 */
    public static function ensureOperationsAreNotCyclic(Scene $scene, array $usedScenesIds = [], int &$operationsCounter = 0) {
        $sceneId = $scene->isNew() ? 0 : $scene->getId();
        Assertion::notInArray($sceneId, $usedScenesIds, 'It is forbidden to have recursive execution of scenes.');
        $usedScenesIds[] = $sceneId;
        foreach ($scene->getOperations() as $operation) {
            Assertion::lessThan(
                $operationsCounter++,
                self::MAX_OPERATIONS_EXECUTIONS_PER_SCENE,
                'The scene would execute too many operations.' // i18n
            );
            $isSceneSubject = $operation->getSubjectType() == ActionableSubjectType::SCENE();
            if ($isSceneSubject && $operation->getAction() != ChannelFunctionAction::INTERRUPT()) {
                self::ensureOperationsAreNotCyclic($operation->getSubject(), $usedScenesIds, $operationsCounter);
            }
        }
    }

    public static function updateDelaysAndEstimatedExecutionTimes(Scene $scene, EntityManagerInterface $entityManager) {
        $scenesToCalculate = [$scene];
        $shield = 150;
        while ($currentScene = array_shift($scenesToCalculate)) {
            Assertion::greaterThan(--$shield, 0, 'Could not update scene estimated execution time.');
            $totalExecutionTime = 0;
            $delayFromWaiting = 0;
            $commandExecutionsCount = 0;
            foreach ($currentScene->getOperations() as $operation) {
                /** @var SceneOperation $operation */
                $delayFromUser = $operation->getUserDelayMs();
                $totalDelay = $delayFromUser + $delayFromWaiting;
                $delayFromWaiting = 0;
                $operation->setDelayMs($totalDelay);
                $totalExecutionTime += $totalDelay;
                $isSceneSubject = $operation->getSubjectType() == ActionableSubjectType::SCENE();
                if ($isSceneSubject && $operation->getAction() != ChannelFunctionAction::INTERRUPT()) {
                    /** @var Scene $executedScene */
                    $executedScene = $operation->getSubject();
                    $commandExecutionsCount += $executedScene->getCommandExecutionsCount();
                    if ($operation->isWaitForCompletion()) {
                        $delayFromWaiting = $executedScene->getEstimatedExecutionTime();
                    }
                } else {
                    ++$commandExecutionsCount;
                }
                $entityManager->persist($operation);
            }
            $totalExecutionTime += $delayFromWaiting;
            $executionTimeChanged = $currentScene->getEstimatedExecutionTime() !== $totalExecutionTime;
            if ($executionTimeChanged || $currentScene->getCommandExecutionsCount() !== $commandExecutionsCount) {
                Assertion::lessThan(
                    $commandExecutionsCount,
                    self::MAX_OPERATIONS_EXECUTIONS_PER_SCENE,
                    'The scene would execute too many operations.' // i18n
                );
                $scenesToCalculate = array_merge($scenesToCalculate, self::getScenesThatUsesScene($currentScene));
                $currentScene->setCommandExecutionsCount($commandExecutionsCount);
                if ($executionTimeChanged) {
                    $currentScene->setEstimatedExecutionTime($totalExecutionTime);
                    $entityManager->persist($currentScene);
                }
            }
        }
    }

    private static function getScenesThatUsesScene(Scene $currentScene): array {
        $dependentScenes = [];
        $currentScene->getOperationsThatReferToThisScene()
            ->filter(fn(SceneOperation $so) => $so->getAction() != ChannelFunctionAction::INTERRUPT())
            ->map(fn(SceneOperation $so) => $so->getOwningScene())
            ->forAll(function (int $index, Scene $dependentScene) use (&$dependentScenes) {
                $dependentScenes[$dependentScene->getId()] = $dependentScene;
                return true;
            });
        return array_values($dependentScenes);
    }
}
