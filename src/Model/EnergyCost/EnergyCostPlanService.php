<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Model\EnergyCost;

use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\EnergyCostPlanAssignment;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Exception\ApiException;
use App\Model\TimeProvider;
use App\Repository\EnergyCostPlanAssignmentRepository;
use App\Repository\EnergyCostPlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyCostPlanService {
    public function __construct(
        private readonly EnergyCostPlanRepository $repository,
        private readonly EnergyCostPlanAssignmentRepository $assignmentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TimeProvider $timeProvider,
        private readonly CostPlanDefinitionParser $parser,
        private readonly CostPlanCompiler $compiler,
    ) {
    }

    /** @return EnergyCostPlan[] */
    public function getAll(User $user): array {
        return $this->repository->findByUser($user);
    }

    public function get(User $user, int|string $id): EnergyCostPlan {
        $plan = $this->repository->findOneByIdAndUser($id, $user);
        if ($plan === null) {
            throw new NotFoundHttpException('Energy cost plan does not exist.');
        }
        return $plan;
    }

    /** @param array<string, mixed> $configuration */
    public function create(User $user, string $name, array $configuration): EnergyCostPlan {
        $this->validateConfiguration($configuration);

        $plan = new EnergyCostPlan($user, $name, $configuration, $this->timeProvider->getDateTime());
        $this->entityManager->persist($plan);
        $this->entityManager->flush();
        return $plan;
    }

    public function rename(User $user, int|string $id, string $name): EnergyCostPlan {
        $plan = $this->get($user, $id);
        $plan->setName($name, $this->timeProvider->getDateTime());
        $this->entityManager->flush();
        return $plan;
    }

    /** @param array<string, mixed> $configuration */
    public function update(User $user, int|string $id, string $name, array $configuration): EnergyCostPlan {
        $plan = $this->get($user, $id);
        $this->validateConfiguration($configuration);
        $updatedAt = $this->timeProvider->getDateTime();
        $plan->setName($name, $updatedAt);
        $plan->setConfiguration($configuration, $updatedAt);
        $this->entityManager->flush();
        return $plan;
    }

    /** @param array<string, mixed> $configuration */
    public function replaceConfiguration(User $user, int|string $id, array $configuration): EnergyCostPlan {
        $plan = $this->get($user, $id);
        $this->validateConfiguration($configuration);
        $plan->setConfiguration($configuration, $this->timeProvider->getDateTime());
        $this->entityManager->flush();
        return $plan;
    }

    public function delete(User $user, int|string $id): void {
        $this->entityManager->remove($this->get($user, $id));
        $this->entityManager->flush();
    }

    public function getAssignment(User $user, IODeviceChannel $channel): EnergyCostPlanAssignment {
        $this->assertChannelSupported($user, $channel);
        $assignment = $this->assignmentRepository->findForChannel($channel);
        if ($assignment === null || !$assignment->getEnergyCostPlan()->belongsToUser($user)) {
            throw new NotFoundHttpException('Energy cost plan assignment does not exist.');
        }
        return $assignment;
    }

    public function assignToChannel(User $user, IODeviceChannel $channel, int|string $planId): EnergyCostPlanAssignment {
        $this->assertChannelSupported($user, $channel);
        $plan = $this->get($user, $planId);
        return $this->entityManager->wrapInTransaction(function () use ($channel, $plan): EnergyCostPlanAssignment {
            $assignment = $this->assignmentRepository->findForChannel($channel);
            if ($assignment === null) {
                $assignment = new EnergyCostPlanAssignment($channel, $plan);
                $this->entityManager->persist($assignment);
            } else {
                $assignment->setEnergyCostPlan($plan);
            }
            return $assignment;
        });
    }

    public function deleteAssignment(User $user, IODeviceChannel $channel): void {
        $assignment = $this->getAssignment($user, $channel);
        $this->entityManager->wrapInTransaction(function () use ($assignment): void {
            $this->entityManager->remove($assignment);
        });
    }

    /** @param array<string, mixed> $configuration */
    private function validateConfiguration(array $configuration): void {
        $this->compiler->compile($this->parser->parse($configuration));
    }

    private function assertChannelSupported(User $user, IODeviceChannel $channel): void {
        if (!$channel->belongsToUser($user)) {
            throw new AccessDeniedHttpException('Access to this channel is denied.');
        }
        if ($channel->getFunction()->getId() !== ChannelFunction::ELECTRICITYMETER) {
            throw new ApiException('Energy cost calculation is supported only for electricity meter channels.');
        }
    }
}
