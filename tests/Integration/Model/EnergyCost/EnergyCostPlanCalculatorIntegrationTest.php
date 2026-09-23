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
use App\Exception\ApiException;
use App\Model\EnergyCost\EnergyCostPlanCalculator;
use App\Repository\EnergyCostPlanAssignmentRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\UserFixtures;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Supla\EnergyCostCalculator\Engine\CalculationResult;
use Supla\EnergyCostCalculator\Engine\CostCalculator;
use Supla\EnergyCostCalculator\Exception\TariffPresetNotFoundException;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyCostPlanCalculatorIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    public function testCalculatesUsingRuntimeCompiledPlanAndUtcRange(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createElectricityMeterChannel($user);
        $plan = $this->persistPlan($user, $this->configuration());
        $this->getEntityManager()->persist(new EnergyCostPlanAssignment($channel, $plan));
        $this->getEntityManager()->flush();
        $source = new RecordingEnergyDeltaSource();

        $result = $this->service($source)->calculate(
            $user,
            $channel,
            new DateTimeImmutable('2026-01-02T13:00:00+01:00'),
            new DateTimeImmutable('2026-01-02T13:15:00+01:00'),
        );

        $this->assertInstanceOf(CalculationResult::class, $result);
        $this->assertSame((string)$channel->getId(), $source->meterId);
        $this->assertSame('UTC', $source->range->from->getTimezone()->getName());
        $this->assertSame('2026-01-02T12:00:00+00:00', $source->range->from->format(DATE_ATOM));
        $this->assertSame('2026-01-02T12:15:00+00:00', $source->range->to->format(DATE_ATOM));
        $this->assertCount(1, $result->intervals);
        $this->assertNotEmpty($result->charges);
    }

    public function testRecompilesPersistedConfigurationForEveryCalculation(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createElectricityMeterChannel($user);
        $plan = $this->persistPlan($user, $this->configuration());
        $this->getEntityManager()->persist(new EnergyCostPlanAssignment($channel, $plan));
        $this->getEntityManager()->flush();
        $calculator = $this->service(new RecordingEnergyDeltaSource());
        $from = new DateTimeImmutable('2026-01-02T12:00:00+00:00');
        $to = new DateTimeImmutable('2026-01-02T12:15:00+00:00');

        $calculator->calculate($user, $channel, $from, $to);

        $configuration = $plan->getConfiguration();
        $configuration['entries'][0]['presetId'] = 'PL.UNKNOWN.G11.2026';
        $plan->setConfiguration($configuration, new DateTime('2026-01-03T12:00:00+00:00'));
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $this->expectException(TariffPresetNotFoundException::class);
        $calculator->calculate(
            $this->getEntityManager()->find(User::class, $user->getId()),
            $this->getEntityManager()->find(IODeviceChannel::class, $channel->getId()),
            $from,
            $to,
        );
    }

    public function testRejectsChannelWithoutAssignedPlan(): void {
        $user = $this->createConfirmedUser();

        $this->expectException(NotFoundHttpException::class);
        $this->service(new RecordingEnergyDeltaSource())->calculate(
            $user,
            $this->createElectricityMeterChannel($user),
            new DateTimeImmutable('2026-01-02T12:00:00+00:00'),
            new DateTimeImmutable('2026-01-02T12:15:00+00:00'),
        );
    }

    public function testRejectsAnotherUsersChannelBeforeLoadingAssignment(): void {
        $owner = $this->createConfirmedUser('owner@supla.org');

        $this->expectException(AccessDeniedHttpException::class);
        $this->service(new RecordingEnergyDeltaSource())->calculate(
            $this->createConfirmedUser('other@supla.org'),
            $this->createElectricityMeterChannel($owner),
            new DateTimeImmutable('2026-01-02T12:00:00+00:00'),
            new DateTimeImmutable('2026-01-02T12:15:00+00:00'),
        );
    }

    public function testRejectsUnsupportedChannelFunction(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]],
        )->getChannels()->first();

        $this->expectException(ApiException::class);
        $this->service(new RecordingEnergyDeltaSource())->calculate(
            $user,
            $channel,
            new DateTimeImmutable('2026-01-02T12:00:00+00:00'),
            new DateTimeImmutable('2026-01-02T12:15:00+00:00'),
        );
    }

    private function service(RecordingEnergyDeltaSource $source): EnergyCostPlanCalculator {
        /** @var EnergyCostPlanAssignmentRepository $repository */
        $repository = $this->getEntityManager()->getRepository(EnergyCostPlanAssignment::class);
        return new EnergyCostPlanCalculator(
            $repository,
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            new CostCalculator($source, new EmptyReferenceDataSource()),
        );
    }

    private function createElectricityMeterChannel(User $user): IODeviceChannel {
        return $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]],
        )->getChannels()->first();
    }

    /** @param array<string, mixed> $configuration */
    private function persistPlan(User $user, array $configuration): EnergyCostPlan {
        $plan = new EnergyCostPlan(
            $user,
            'Home',
            $configuration,
            new DateTime('2026-01-01T00:00:00', new DateTimeZone('UTC')),
        );
        $this->getEntityManager()->persist($plan);
        $this->getEntityManager()->flush();
        return $plan;
    }

    /** @return array<string, mixed> */
    private function configuration(): array {
        return [
            'version' => 1,
            'entries' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'presetId' => 'PL.TAURON_DYSTRYBUCJA.G11.2026',
                'values' => [
                    'billingCycle.anchor' => '2026-01-01T00:00:00+01:00',
                    'energy.rate' => '0.71',
                ],
            ]],
        ];
    }
}
