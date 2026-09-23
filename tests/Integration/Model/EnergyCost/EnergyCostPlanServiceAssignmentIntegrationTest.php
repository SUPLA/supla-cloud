<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Model\EnergyCost;

use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\EnergyCostPlanAssignment;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\EnergyCost\EnergyCostPlanService;
use App\Repository\EnergyCostPlanAssignmentRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\UserFixtures;
use DateTime;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyCostPlanServiceAssignmentIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    public function testAssignsReplacesAndDeletesAssignment(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createElectricityMeterChannel($user);
        $firstPlan = $this->persistPlan($user, 'First');
        $secondPlan = $this->persistPlan($user, 'Second');

        $assignment = $this->service()->assignToChannel($user, $channel, $firstPlan->getId());
        $this->assertSame($firstPlan->getId(), $assignment->getEnergyCostPlan()->getId());

        $assignment = $this->service()->assignToChannel($user, $channel, $secondPlan->getId());
        $this->assertSame($secondPlan->getId(), $assignment->getEnergyCostPlan()->getId());
        $this->assertSame($secondPlan->getId(), $this->repository()->findPlanForChannel($channel)->getId());

        $this->service()->deleteAssignment($user, $channel);
        $this->assertNull($this->repository()->findForChannel($channel));
    }

    public function testHidesForeignPlan(): void {
        $user = $this->createConfirmedUser('owner@supla.org');
        $channel = $this->createElectricityMeterChannel($user);
        $foreignPlan = $this->persistPlan($this->createConfirmedUser('other@supla.org'), 'Other');

        $this->expectException(NotFoundHttpException::class);
        $this->service()->assignToChannel($user, $channel, $foreignPlan->getId());
    }

    private function service(): EnergyCostPlanService {
        return self::$container->get(EnergyCostPlanService::class);
    }

    private function repository(): EnergyCostPlanAssignmentRepository {
        /** @var EnergyCostPlanAssignmentRepository $repository */
        $repository = $this->getEntityManager()->getRepository(EnergyCostPlanAssignment::class);
        return $repository;
    }

    private function createElectricityMeterChannel(User $user): IODeviceChannel {
        return $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]],
        )->getChannels()->first();
    }

    private function persistPlan(User $user, string $name): EnergyCostPlan {
        $plan = new EnergyCostPlan($user, $name, $this->configuration(), new DateTime('2026-01-01T00:00:00+00:00'));
        $this->getEntityManager()->persist($plan);
        $this->getEntityManager()->flush();
        return $plan;
    }

    /** @return array<string, mixed> */
    private function configuration(): array {
        return [
            'version' => 1,
            'entries' => [[
                'presetId' => 'PL.TAURON_DYSTRYBUCJA.G11.2026',
                'values' => [
                    'billingCycle.anchor' => '2026-01-15T00:00:00+01:00',
                    'energy.rate' => '0.71',
                ],
            ]],
        ];
    }
}
