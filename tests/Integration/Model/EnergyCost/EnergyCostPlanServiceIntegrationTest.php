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
use App\Model\EnergyCost\EnergyCostPlanService;
use App\Repository\EnergyCostPlanAssignmentRepository;
use App\Repository\EnergyCostPlanRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\TestTimeProvider;
use App\Tests\Integration\Traits\UserFixtures;
use Supla\EnergyCostCalculator\Exception\CostPlanDefinitionException;
use Supla\EnergyCostCalculator\Exception\TariffPresetCompilationException;
use Supla\EnergyCostCalculator\Exception\TariffPresetNotFoundException;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyCostPlanServiceIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    public function testCreatesAndReloadsValidG11ConfigurationUnchanged(): void {
        TestTimeProvider::setTime('2026-01-02T12:34:56+00:00');
        $user = $this->createConfirmedUser();
        $configuration = $this->g11Configuration();

        $plan = $this->service()->create($user, 'Home', $configuration);
        $planId = $plan->getId();
        $this->getEntityManager()->clear();

        $plan = $this->service()->get($user, $planId);

        $this->assertSame('Home', $plan->getName());
        $this->assertSame($configuration, $plan->getConfiguration());
        $this->assertSame('2026-01-02T12:34:56+00:00', $plan->getCreatedAt()->format(DATE_ATOM));
        $this->assertSame('2026-01-02T12:34:56+00:00', $plan->getUpdatedAt()->format(DATE_ATOM));
    }

    public function testCreatesValidG12Configuration(): void {
        $plan = $this->service()->create($this->createConfirmedUser(), 'Home', $this->g12Configuration());

        $this->assertSame('PL.TAURON_DYSTRYBUCJA.G12.2026', $plan->getConfiguration()['entries'][0]['presetId']);
    }

    public function testRejectsMalformedConfiguration(): void {
        $this->expectException(CostPlanDefinitionException::class);

        $this->service()->create($this->createConfirmedUser(), 'Home', ['version' => 1, 'entries' => []]);
    }

    public function testRejectsUnknownPreset(): void {
        $configuration = $this->g11Configuration();
        $configuration['entries'][0]['presetId'] = 'PL.UNKNOWN.G11.2026';

        $this->expectException(TariffPresetNotFoundException::class);

        $this->service()->create($this->createConfirmedUser(), 'Home', $configuration);
    }

    public function testRejectsConfigurationThatCannotCompile(): void {
        $configuration = $this->g11Configuration();
        unset($configuration['entries'][0]['values']['energy.rate']);

        $this->expectException(TariffPresetCompilationException::class);

        $this->service()->create($this->createConfirmedUser(), 'Home', $configuration);
    }

    public function testReplacesWholeConfigurationAndKeepsOriginalDocument(): void {
        $user = $this->createConfirmedUser();
        $plan = $this->service()->create($user, 'Home', $this->g11Configuration());
        $configuration = $this->g12Configuration();
        TestTimeProvider::setTime('2026-02-03T12:34:56+00:00');

        $plan = $this->service()->replaceConfiguration($user, $plan->getId(), $configuration);
        $planId = $plan->getId();
        $this->getEntityManager()->clear();

        $plan = $this->service()->get($user, $planId);

        $this->assertSame($configuration, $plan->getConfiguration());
        $this->assertSame('2026-02-03T12:34:56+00:00', $plan->getUpdatedAt()->format(DATE_ATOM));
    }

    public function testRejectedReplacementLeavesPlanUnchanged(): void {
        $user = $this->createConfirmedUser();
        $plan = $this->service()->create($user, 'Home', $this->g11Configuration());
        $configuration = $plan->getConfiguration();

        try {
            $this->service()->replaceConfiguration($user, $plan->getId(), ['version' => 1, 'entries' => []]);
            $this->fail('Expected invalid configuration to be rejected.');
        } catch (CostPlanDefinitionException) {
        }

        $this->assertSame($configuration, $plan->getConfiguration());
    }

    public function testRenamesPlanWithoutChangingConfiguration(): void {
        $user = $this->createConfirmedUser();
        $plan = $this->service()->create($user, 'Home', $this->g11Configuration());
        $configuration = $plan->getConfiguration();
        TestTimeProvider::setTime('2026-02-03T12:34:56+00:00');

        $plan = $this->service()->rename($user, $plan->getId(), 'Summer home');

        $this->assertSame('Summer home', $plan->getName());
        $this->assertSame($configuration, $plan->getConfiguration());
        $this->assertSame('2026-02-03T12:34:56+00:00', $plan->getUpdatedAt()->format(DATE_ATOM));
    }

    public function testListsOnlyOwnedPlansAndTreatsForeignPlanAsNotFound(): void {
        $owner = $this->createConfirmedUser('owner@supla.org');
        $otherUser = $this->createConfirmedUser('other@supla.org');
        $plan = $this->service()->create($owner, 'Home', $this->g11Configuration());

        $this->assertSame([$plan], $this->service()->getAll($owner));
        $this->assertSame([], $this->service()->getAll($otherUser));

        $this->expectException(NotFoundHttpException::class);
        $this->service()->get($otherUser, $plan->getId());
    }

    public function testDeletesOwnedPlan(): void {
        $user = $this->createConfirmedUser();
        $plan = $this->service()->create($user, 'Home', $this->g11Configuration());
        $planId = $plan->getId();

        $this->service()->delete($user, $planId);

        $this->expectException(NotFoundHttpException::class);
        $this->service()->get($user, $planId);
    }

    public function testForeignPlanCannotBeRenamed(): void {
        $owner = $this->createConfirmedUser('owner@supla.org');
        $plan = $this->service()->create($owner, 'Home', $this->g11Configuration());

        $this->expectException(NotFoundHttpException::class);
        $this->service()->rename($this->createConfirmedUser('other@supla.org'), $plan->getId(), 'Other');
    }

    public function testForeignPlanCannotHaveConfigurationReplaced(): void {
        $owner = $this->createConfirmedUser('owner@supla.org');
        $plan = $this->service()->create($owner, 'Home', $this->g11Configuration());

        $this->expectException(NotFoundHttpException::class);
        $this->service()->replaceConfiguration(
            $this->createConfirmedUser('other@supla.org'),
            $plan->getId(),
            ['version' => 1, 'entries' => []],
        );
    }

    public function testForeignPlanCannotBeDeleted(): void {
        $owner = $this->createConfirmedUser('owner@supla.org');
        $plan = $this->service()->create($owner, 'Home', $this->g11Configuration());

        $this->expectException(NotFoundHttpException::class);
        $this->service()->delete($this->createConfirmedUser('other@supla.org'), $plan->getId());
    }

    private function service(): EnergyCostPlanService {
        return new EnergyCostPlanService(
            $this->planRepository(),
            $this->assignmentRepository(),
            $this->getEntityManager(),
            new TestTimeProvider(),
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
        );
    }

    private function planRepository(): EnergyCostPlanRepository {
        /** @var EnergyCostPlanRepository $repository */
        $repository = $this->getEntityManager()->getRepository(EnergyCostPlan::class);
        return $repository;
    }

    private function assignmentRepository(): EnergyCostPlanAssignmentRepository {
        /** @var EnergyCostPlanAssignmentRepository $repository */
        $repository = $this->getEntityManager()->getRepository(EnergyCostPlanAssignment::class);
        return $repository;
    }

    /** @return array<string, mixed> */
    private function g11Configuration(): array {
        return [
            'version' => 1,
            'entries' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'presetId' => 'PL.TAURON_DYSTRYBUCJA.G11.2026',
                'values' => [
                    'billingCycle.anchor' => '2026-01-15T00:00:00+01:00',
                    'energy.rate' => '0.71',
                ],
            ]],
        ];
    }

    /** @return array<string, mixed> */
    private function g12Configuration(): array {
        return [
            'version' => 1,
            'entries' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'presetId' => 'PL.TAURON_DYSTRYBUCJA.G12.2026',
                'values' => [
                    'billingCycle.anchor' => '2026-01-15T00:00:00+01:00',
                    'energy.DAY' => '0.98',
                    'energy.NIGHT' => '0.62',
                    'distribution.DAY' => '0.2841',
                    'distribution.NIGHT' => '0.0558',
                ],
            ]],
        ];
    }
}
