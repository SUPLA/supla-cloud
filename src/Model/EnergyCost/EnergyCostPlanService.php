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
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EnergyCostPlanService {
    public function __construct(
        private readonly EnergyCostPlanRepository $repository,
        private readonly EnergyCostPlanAssignmentRepository $assignmentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TimeProvider $timeProvider,
        private readonly CostPlanDefinitionParser $parser,
        private readonly CostPlanCompiler $compiler,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
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

        $plan = new EnergyCostPlan($user, $this->uniqueName($user, $name), $configuration, $this->timeProvider->getDateTime());
        $this->entityManager->persist($plan);
        $this->entityManager->flush();
        return $plan;
    }

    public function rename(User $user, int|string $id, string $name): EnergyCostPlan {
        $plan = $this->get($user, $id);
        $plan->setName($this->uniqueName($user, $name, $plan), $this->timeProvider->getDateTime());
        $this->entityManager->flush();
        return $plan;
    }

    /** @param array<string, mixed> $configuration */
    public function update(User $user, int|string $id, string $name, array $configuration): EnergyCostPlan {
        $plan = $this->get($user, $id);
        $this->validateConfiguration($configuration);
        return $this->entityManager->wrapInTransaction(function () use ($user, $plan, $name, $configuration): EnergyCostPlan {
            $this->entityManager->refresh($plan, LockMode::PESSIMISTIC_WRITE);
            $this->assertAssignmentsAccessible($this->assignmentsForPlan($user, $plan, true));
            $updatedAt = $this->timeProvider->getDateTime();
            $plan->setName($this->uniqueName($user, $name, $plan), $updatedAt);
            $plan->setConfiguration($configuration, $updatedAt);
            return $plan;
        });
    }

    /** @param array<string, mixed> $configuration */
    public function replaceConfiguration(User $user, int|string $id, array $configuration): EnergyCostPlan {
        $plan = $this->get($user, $id);
        $this->validateConfiguration($configuration);
        return $this->entityManager->wrapInTransaction(function () use ($user, $plan, $configuration): EnergyCostPlan {
            $this->entityManager->refresh($plan, LockMode::PESSIMISTIC_WRITE);
            $this->assertAssignmentsAccessible($this->assignmentsForPlan($user, $plan, true));
            $plan->setConfiguration($configuration, $this->timeProvider->getDateTime());
            return $plan;
        });
    }

    public function delete(User $user, int|string $id): void {
        $plan = $this->get($user, $id);
        $this->entityManager->wrapInTransaction(function () use ($user, $plan): void {
            $this->entityManager->refresh($plan, LockMode::PESSIMISTIC_WRITE);
            $this->assertAssignmentsAccessible($this->assignmentsForPlan($user, $plan, true));
            $this->entityManager->remove($plan);
        });
    }

    public function getAssignment(User $user, IODeviceChannel $channel): EnergyCostPlanAssignment {
        $assignment = $this->findAssignment($user, $channel);
        if ($assignment === null) {
            throw new NotFoundHttpException('Energy cost plan assignment does not exist.');
        }
        return $assignment;
    }

    public function findAssignment(User $user, IODeviceChannel $channel): ?EnergyCostPlanAssignment {
        $this->assertChannelSupported($user, $channel);
        $assignment = $this->assignmentRepository->findForChannel($channel);
        return $assignment !== null && $assignment->getEnergyCostPlan()->belongsToUser($user) ? $assignment : null;
    }

    public function assignToChannel(User $user, IODeviceChannel $channel, int|string $planId): EnergyCostPlanAssignment {
        $this->assertChannelSupported($user, $channel);
        $plan = $this->get($user, $planId);
        return $this->entityManager->wrapInTransaction(fn(): EnergyCostPlanAssignment => $this->assign($channel, $plan));
    }

    /**
     * @param array<string, mixed> $configuration
     * @return array{plan: EnergyCostPlan, assignment: EnergyCostPlanAssignment}
     */
    public function assignStarterToChannel(
        User $user,
        IODeviceChannel $channel,
        string $name,
        array $configuration,
    ): array {
        $this->assertChannelSupported($user, $channel);
        $this->validateConfiguration($configuration);
        return $this->entityManager->wrapInTransaction(function () use ($user, $channel, $name, $configuration): array {
            $plan = null;
            foreach ($this->repository->findByUser($user) as $candidate) {
                if ($this->matchesConfiguration($candidate, $configuration)) {
                    $plan = $candidate;
                    break;
                }
            }
            if ($plan === null) {
                $plan = new EnergyCostPlan($user, $this->uniqueName($user, $name), $configuration, $this->timeProvider->getDateTime());
                $this->entityManager->persist($plan);
            }
            return ['plan' => $plan, 'assignment' => $this->assign($channel, $plan)];
        });
    }

    /**
     * Updates a plan in place or creates a private copy and assigns it to one channel.
     *
     * @param array<string, mixed> $configuration
     * @param int[]|null $expectedChannelIds
     * @return array{plan: EnergyCostPlan, assignment: EnergyCostPlanAssignment, affectedAssignments: EnergyCostPlanAssignment[]}
     */
    public function saveForChannel(
        User $user,
        IODeviceChannel $channel,
        array $configuration,
        bool $allMeters,
        ?int $expectedPlanId = null,
        ?array $expectedChannelIds = null,
    ): array {
        $this->validateConfiguration($configuration);
        return $this->entityManager->wrapInTransaction(function () use (
            $user,
            $channel,
            $configuration,
            $allMeters,
            $expectedPlanId,
            $expectedChannelIds
        ): array {
            $assignment = $this->getAssignment($user, $channel);
            $this->entityManager->refresh($assignment, LockMode::PESSIMISTIC_WRITE);
            $plan = $assignment->getEnergyCostPlan();
            $this->entityManager->refresh($plan, LockMode::PESSIMISTIC_WRITE);
            if ($expectedPlanId !== null && (int)$plan->getId() !== $expectedPlanId) {
                throw new ApiException('The tariff assignment changed. Reopen tariff settings.', 409);
            }
            $affectedAssignments = $this->assignmentsForPlan($user, $plan, true);
            if ($allMeters) {
                $this->assertAssignmentsAccessible($affectedAssignments);
                if ($expectedChannelIds !== null) {
                    $actualIds = array_map(fn(EnergyCostPlanAssignment $item): int => $item->getChannelId(), $affectedAssignments);
                    sort($actualIds);
                    sort($expectedChannelIds);
                    if ($actualIds !== $expectedChannelIds) {
                        throw new ApiException('The meters using this plan changed. Review the save scope again.', 409);
                    }
                }
            }
            if (!$allMeters && count($affectedAssignments) > 1) {
                $plan = new EnergyCostPlan(
                    $user,
                    $this->uniqueName($user, $plan->getName()),
                    $configuration,
                    $this->timeProvider->getDateTime(),
                );
                $this->entityManager->persist($plan);
                $assignment->setEnergyCostPlan($plan);
                $affectedAssignments = [$assignment];
            } else {
                $plan->setConfiguration($configuration, $this->timeProvider->getDateTime());
            }
            return ['plan' => $plan, 'assignment' => $assignment, 'affectedAssignments' => $affectedAssignments];
        });
    }

    /** @return EnergyCostPlanAssignment[] */
    public function assignmentsForPlan(User $user, EnergyCostPlan $plan, bool $lock = false): array {
        $this->get($user, $plan->getId());
        return array_values(array_filter(
            $this->assignmentRepository->findForPlan($plan, $lock),
            fn(EnergyCostPlanAssignment $assignment): bool => $assignment->getChannel()->belongsToUser($user)
        ));
    }

    /** @param EnergyCostPlanAssignment[] $assignments */
    public function assertAssignmentsAccessible(array $assignments, bool $write = true): void {
        foreach ($assignments as $assignment) {
            if (
                !$this->authorizationChecker->isGranted($write ? 'ROLE_CHANNELS_RW' : 'ROLE_CHANNELS_R')
                || !$this->authorizationChecker->isGranted('accessIdContains', $assignment->getChannel())
            ) {
                throw new AccessDeniedHttpException('Access to an affected electricity meter is denied.');
            }
        }
    }

    public function deleteAssignment(User $user, IODeviceChannel $channel): void {
        $assignment = $this->getAssignment($user, $channel);
        $this->entityManager->wrapInTransaction(function () use ($assignment): void {
            $this->entityManager->lock($assignment->getEnergyCostPlan(), LockMode::PESSIMISTIC_WRITE);
            $this->entityManager->remove($assignment);
        });
    }

    /** @param array<string, mixed> $configuration */
    private function validateConfiguration(array $configuration): void {
        $this->compiler->compile($this->parser->parse($configuration));
    }

    private function assign(IODeviceChannel $channel, EnergyCostPlan $plan): EnergyCostPlanAssignment {
        if ($plan->getId() !== null) {
            $this->entityManager->lock($plan, LockMode::PESSIMISTIC_WRITE);
        }
        $assignment = $this->assignmentRepository->findForChannel($channel);
        if ($assignment === null) {
            $assignment = new EnergyCostPlanAssignment($channel, $plan);
            $this->entityManager->persist($assignment);
        } else {
            $this->entityManager->lock($assignment->getEnergyCostPlan(), LockMode::PESSIMISTIC_WRITE);
            $assignment->setEnergyCostPlan($plan);
        }
        return $assignment;
    }

    /** @param array<string, mixed> $configuration */
    private function matchesConfiguration(EnergyCostPlan $plan, array $configuration): bool {
        return $this->canonicalize($plan->getConfiguration()) === $this->canonicalize($configuration);
    }

    /**
     * @param array<string|int, mixed> $value
     * @return array<string|int, mixed>
     */
    private function canonicalize(array $value): array {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->canonicalize($item);
            }
        }
        if (!array_is_list($value)) {
            ksort($value);
        }
        return $value;
    }

    private function uniqueName(User $user, string $name, ?EnergyCostPlan $ignoredPlan = null): string {
        $names = array_map(
            fn(EnergyCostPlan $plan): string => $plan->getName(),
            array_filter($this->repository->findByUser($user), fn(EnergyCostPlan $plan): bool => $plan !== $ignoredPlan),
        );
        if (!in_array($name, $names, true)) {
            return $name;
        }
        for ($suffix = 2;; ++$suffix) {
            $candidate = "$name ($suffix)";
            if (!in_array($candidate, $names, true)) {
                return $candidate;
            }
        }
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
