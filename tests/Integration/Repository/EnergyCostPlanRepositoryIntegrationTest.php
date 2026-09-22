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

namespace App\Tests\Integration\Repository;

use App\Entity\Main\EnergyCostPlan;
use App\Entity\Main\EnergyCostPlanAssignment;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Repository\EnergyCostPlanAssignmentRepository;
use App\Repository\EnergyCostPlanRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\UserFixtures;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Supla\EnergyCostCalculator\Definition\BillingDefinition;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;

class EnergyCostPlanRepositoryIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    public function testPersistsAndCompilesConfigurationUnchanged(): void {
        $user = $this->createConfirmedUser();
        $configuration = $this->validConfiguration();
        $createdAt = new DateTime('2026-01-02T12:34:56+00:00');
        $plan = new EnergyCostPlan($user, 'Home', $configuration, $createdAt);

        $this->getEntityManager()->persist($plan);
        $this->getEntityManager()->flush();
        $planId = $plan->getId();
        $this->getEntityManager()->clear();

        $plan = $this->planRepository()->find($planId);

        $this->assertInstanceOf(EnergyCostPlan::class, $plan);
        $this->assertSame('Home', $plan->getName());
        $this->assertSame($configuration, $plan->getConfiguration());
        $this->assertSame('UTC', $plan->getCreatedAt()->getTimezone()->getName());
        $this->assertEquals($createdAt, $plan->getCreatedAt());
        $this->assertEquals($createdAt, $plan->getUpdatedAt());

        $definition = (new CostPlanDefinitionParser())->parse($plan->getConfiguration());
        $this->assertInstanceOf(BillingDefinition::class, (new CostPlanCompiler())->compile($definition));
    }

    public function testUpdatesPlanAndItsTimestamp(): void {
        $user = $this->createConfirmedUser();
        $plan = $this->persistPlan($user, 'Home');
        $configuration = $this->validConfiguration();
        $configuration['entries'][0]['values']['energy.rate'] = '0.75';
        $updatedAt = new DateTime('2026-02-03T12:34:56+00:00');

        $plan->setName('Summer home', $updatedAt);
        $plan->setConfiguration($configuration, $updatedAt);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $plan = $this->planRepository()->find($plan->getId());
        $this->assertSame('Summer home', $plan->getName());
        $this->assertSame($configuration, $plan->getConfiguration());
        $this->assertEquals($updatedAt, $plan->getUpdatedAt());
    }

    public function testFindsPlansOnlyForTheirOwner(): void {
        $owner = $this->createConfirmedUser('owner@supla.org');
        $otherUser = $this->createConfirmedUser('other@supla.org');
        $plan = $this->persistPlan($owner, 'Home');

        $this->assertSame([$plan], $this->planRepository()->findByUser($owner));
        $this->assertSame([], $this->planRepository()->findByUser($otherUser));
        $this->assertSame($plan, $this->planRepository()->findOneByIdAndUser($plan->getId(), $owner));
        $this->assertNull($this->planRepository()->findOneByIdAndUser($plan->getId(), $otherUser));
    }

    public function testAssignsOnePlanToMultipleChannelsAndDeletesAssignmentsWithPlan(): void {
        $user = $this->createConfirmedUser();
        [$firstChannel, $secondChannel] = $this->createElectricityMeterChannels($user, 2);
        $plan = $this->persistPlan($user, 'Home');

        $this->getEntityManager()->persist(new EnergyCostPlanAssignment($firstChannel, $plan));
        $this->getEntityManager()->persist(new EnergyCostPlanAssignment($secondChannel, $plan));
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $this->assertSame(
            $plan->getId(),
            $this->assignmentRepository()->findPlanForChannel($firstChannel)->getId()
        );
        $this->assertSame(
            $plan->getId(),
            $this->assignmentRepository()->findPlanForChannel($secondChannel)->getId()
        );

        $managedPlan = $this->planRepository()->find($plan->getId());
        $this->getEntityManager()->remove($managedPlan);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $this->assertNull($this->assignmentRepository()->findForChannel($firstChannel));
        $this->assertNull($this->assignmentRepository()->findForChannel($secondChannel));
    }

    public function testChannelCanHaveOnlyOneAssignment(): void {
        $user = $this->createConfirmedUser();
        [$channel] = $this->createElectricityMeterChannels($user, 1);
        $firstPlan = $this->persistPlan($user, 'First');
        $secondPlan = $this->persistPlan($user, 'Second');

        $this->getEntityManager()->persist(new EnergyCostPlanAssignment($channel, $firstPlan));
        $this->getEntityManager()->flush();
        $channelId = $channel->getId();
        $secondPlanId = $secondPlan->getId();
        $this->getEntityManager()->clear();

        $channel = $this->getEntityManager()->find(IODeviceChannel::class, $channelId);
        $secondPlan = $this->planRepository()->find($secondPlanId);
        $this->getEntityManager()->persist(new EnergyCostPlanAssignment($channel, $secondPlan));

        $this->expectException(UniqueConstraintViolationException::class);
        $this->getEntityManager()->flush();
    }

    public function testReplacesAssignmentAndDeletesItWithChannel(): void {
        $user = $this->createConfirmedUser();
        [$channel] = $this->createElectricityMeterChannels($user, 1);
        $firstPlan = $this->persistPlan($user, 'First');
        $secondPlan = $this->persistPlan($user, 'Second');
        $assignment = new EnergyCostPlanAssignment($channel, $firstPlan);
        $this->getEntityManager()->persist($assignment);
        $this->getEntityManager()->flush();

        $assignment->setEnergyCostPlan($secondPlan);
        $this->getEntityManager()->flush();
        $channelId = $channel->getId();
        $this->getEntityManager()->clear();

        $channel = $this->getEntityManager()->find(IODeviceChannel::class, $channelId);
        $this->assertSame(
            $secondPlan->getId(),
            $this->assignmentRepository()->findPlanForChannel($channel)->getId()
        );

        $this->getEntityManager()->remove($channel);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $this->assertNull($this->getEntityManager()->find(EnergyCostPlanAssignment::class, $channelId));
    }

    private function persistPlan(User $user, string $name): EnergyCostPlan {
        $plan = new EnergyCostPlan(
            $user,
            $name,
            $this->validConfiguration(),
            new DateTime('2026-01-02T12:34:56', new DateTimeZone('UTC'))
        );
        $this->getEntityManager()->persist($plan);
        $this->getEntityManager()->flush();
        return $plan;
    }

    /** @return IODeviceChannel[] */
    private function createElectricityMeterChannels(User $user, int $count): array {
        $channelTypes = array_fill(0, $count, [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]);
        return $this->createDevice($this->createLocation($user), $channelTypes)->getChannels()->toArray();
    }

    private function planRepository(): EnergyCostPlanRepository {
        /** @var EnergyCostPlanRepository $repository */
        $repository = $this->getDoctrine()->getRepository(EnergyCostPlan::class);
        return $repository;
    }

    private function assignmentRepository(): EnergyCostPlanAssignmentRepository {
        /** @var EnergyCostPlanAssignmentRepository $repository */
        $repository = $this->getDoctrine()->getRepository(EnergyCostPlanAssignment::class);
        return $repository;
    }

    /** @return array<string, mixed> */
    private function validConfiguration(): array {
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
}
