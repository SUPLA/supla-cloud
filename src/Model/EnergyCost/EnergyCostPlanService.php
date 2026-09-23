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
use App\Entity\Main\User;
use App\Model\TimeProvider;
use App\Repository\EnergyCostPlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyCostPlanService {
    public function __construct(
        private readonly EnergyCostPlanRepository $repository,
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

    /** @param array<string, mixed> $configuration */
    private function validateConfiguration(array $configuration): void {
        $this->compiler->compile($this->parser->parse($configuration));
    }
}
