<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Model\EnergyCost;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\EnergyCost\EnergyCostPlanCalculator;
use App\Model\EnergyCost\EnergyCostPlanService;
use App\Model\EnergyCost\SuplaEnergyDeltaSource;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\UserFixtures;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;

class EnergyCostPlanCalculationWiringIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    /** @var Connection */
    private $measurementLogsConnection;

    protected function initializeDatabaseForTests(): void {
        $this->measurementLogsConnection = self::getContainer()
            ->get(MeasurementLogsEntityManagerProvider::class)
            ->get()
            ->getConnection();
    }

    /** @param array<string, mixed> $configuration */
    #[DataProvider('planConfigurations')]
    public function testCalculatesPersistedPlansUsingSuppliedMeterAndReferenceFacts(
        array $configuration,
        bool $hasZones,
        bool $requiresReference,
    ): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createElectricityMeterChannel($user);
        $plan = $this->planService()->create($user, 'Home', $configuration);
        $this->assertSame($configuration, $plan->getConfiguration());
        $this->planService()->assignToChannel($user, $channel, $plan->getId());
        foreach (['12:15:00', '12:30:00', '12:45:00', '13:00:00'] as $time) {
            $this->insertDelta($channel->getId(), '2026-01-02 ' . $time);
        }
        if ($requiresReference) {
            foreach (['12:00:00', '12:15:00', '12:30:00', '12:45:00'] as $time) {
                $this->insertReference('2026-01-02 ' . $time);
            }
        }

        $result = $this->calculator()->calculate(
            $user,
            $channel,
            new DateTimeImmutable('2026-01-02T12:00:00+00:00'),
            new DateTimeImmutable('2026-01-02T13:00:00+00:00'),
        );

        $this->assertSame(4, $result->processedDeltaCount);
        $this->assertCount(4, $result->intervals);
        $this->assertNotEmpty($result->charges);
        $this->assertArrayHasKey('energy-purchase', $result->costs['gross']['usageBased']['byComponent']);
        $this->assertArrayHasKey('distribution-variable', $result->costs['gross']['usageBased']['byComponent']);
        $this->assertNotEmpty($result->costs['net']);
        $this->assertNotEmpty($result->costs['taxes']);
        if ($hasZones) {
            $this->assertNotEmpty($result->costs['gross']['usageBased']['byZone']);
        }
    }

    public function testFindsLongestContinuousLoggedRange(): void {
        $channel = $this->createElectricityMeterChannel($this->createConfirmedUser());
        foreach (['12:15:00', '12:30:00', '12:45:00', '13:00:00', '14:15:00', '14:30:00'] as $time) {
            $this->insertDelta($channel->getId(), '2026-01-02 ' . $time);
        }

        $range = self::getContainer()->get(SuplaEnergyDeltaSource::class)->longestContinuousRange(
            (string)$channel->getId(),
            new \Supla\EnergyCostCalculator\Model\TimeRange(
                new DateTimeImmutable('2026-01-02T12:00:00+00:00'),
                new DateTimeImmutable('2026-01-02T15:00:00+00:00'),
            ),
        );

        $this->assertNotNull($range);
        $this->assertSame('2026-01-02T12:00:00+00:00', $range->from->format(DATE_ATOM));
        $this->assertSame('2026-01-02T13:00:00+00:00', $range->to->format(DATE_ATOM));
    }

    public function testCalculatesLongestContinuousLoggedRangeWhenRequestedRangeContainsAGap(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createElectricityMeterChannel($user);
        $plan = $this->planService()->create($user, 'Home', self::planConfigurations()[2][0]);
        $this->planService()->assignToChannel($user, $channel, $plan->getId());
        foreach (['12:15:00', '12:30:00', '12:45:00', '13:00:00'] as $time) {
            $this->insertDelta($channel->getId(), '2026-01-02 ' . $time);
        }

        $result = $this->calculator()->calculate(
            $user,
            $channel,
            new DateTimeImmutable('2026-01-02T11:00:00+00:00'),
            new DateTimeImmutable('2026-01-02T13:00:00+00:00'),
        );

        $this->assertSame('2026-01-02T12:00:00+00:00', $result->range->from->format(DATE_ATOM));
        $this->assertSame('2026-01-02T13:00:00+00:00', $result->range->to->format(DATE_ATOM));
        $this->assertSame(4, $result->processedDeltaCount);
    }

    public function testReturnsAnIncompleteResultWhenNoMeterDeltasAreAvailable(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createElectricityMeterChannel($user);
        $plan = $this->planService()->create($user, 'Home', self::planConfigurations()[2][0]);
        $this->planService()->assignToChannel($user, $channel, $plan->getId());

        $result = $this->calculator()->calculate(
            $user,
            $channel,
            new DateTimeImmutable('2026-01-02T12:00:00+00:00'),
            new DateTimeImmutable('2026-01-02T13:00:00+00:00'),
        );

        $this->assertSame(0, $result->processedDeltaCount);
        $this->assertNull($result->costs['gross']['total']);
        $this->assertSame([], $result->charges);
    }

    public function testReturnsPartialResultWhenDynamicPriceDataIsMissing(): void {
        $user = $this->createConfirmedUser();
        $channel = $this->createElectricityMeterChannel($user);
        $plan = $this->planService()->create($user, 'Home', self::planConfigurations()[3][0]);
        $this->planService()->assignToChannel($user, $channel, $plan->getId());
        foreach (['12:15:00', '12:30:00', '12:45:00', '13:00:00', '13:15:00', '13:30:00', '13:45:00', '14:00:00'] as $time) {
            $this->insertDelta($channel->getId(), '2026-01-02 ' . $time);
        }
        foreach (['12:00:00', '12:15:00', '12:30:00', '12:45:00'] as $time) {
            $this->insertReference('2026-01-02 ' . $time);
        }

        $result = $this->calculator()->calculate(
            $user,
            $channel,
            new DateTimeImmutable('2026-01-02T12:00:00+00:00'),
            new DateTimeImmutable('2026-01-02T14:00:00+00:00'),
        );

        $this->assertSame(4, $result->processedDeltaCount);
        $this->assertTrue($result->jsonSerialize()['incomplete']);
        $this->assertSame('MISSING_REFERENCE_DATA', $result->warnings[0]['code']);
        $this->assertSame('TEMPORAL_NETTING_WINDOW', $result->warnings[0]['scope']);
    }

    /** @return list<array{array<string, mixed>, bool, bool}> */
    public static function planConfigurations(): array {
        return [
            [[
                'version' => 2,
                'currency' => 'PLN',
                'timezone' => 'Europe/Warsaw',
                'billingCycles' => [[
                    'validFrom' => '2026-01-01T00:00:00+01:00',
                    'validTo' => '2027-01-01T00:00:00+01:00',
                    'anchor' => '2026-01-01',
                    'length' => 1,
                    'unit' => 'MONTH',
                ]],
                'periods' => [[
                    'validFrom' => '2026-01-01T00:00:00+01:00',
                    'validTo' => '2027-01-01T00:00:00+01:00',
                    'components' => [
                        ['kind' => 'ENERGY_PURCHASE', 'presetId' => 'PL.TAURON_SPRZEDAZ.G11.2026', 'componentId' => 'energy-purchase',
                            'values' => []],
                        ['kind' => 'DISTRIBUTION_VARIABLE', 'presetId' => 'PL.TAURON_DYSTRYBUCJA.G11.2026',
                            'componentId' => 'distribution-variable', 'values' => []],
                    ],
                ]],
            ], false, false],
            [[
                'version' => 2,
                'currency' => 'PLN',
                'timezone' => 'Europe/Warsaw',
                'billingCycles' => [[
                    'validFrom' => '2026-01-01T00:00:00+01:00',
                    'validTo' => '2027-01-01T00:00:00+01:00',
                    'anchor' => '2026-01-01',
                    'length' => 1,
                    'unit' => 'MONTH',
                ]],
                'periods' => [[
                    'validFrom' => '2026-01-01T00:00:00+01:00',
                    'validTo' => '2027-01-01T00:00:00+01:00',
                    'components' => [
                        [
                            'kind' => 'ENERGY_PURCHASE',
                            'presetId' => 'PL.TAURON_SPRZEDAZ.G12.2026',
                            'componentId' => 'energy-purchase',
                            'values' => ['energy.DAY' => '0.98', 'energy.NIGHT' => '0.62'],
                        ],
                        ['kind' => 'DISTRIBUTION_VARIABLE', 'presetId' => 'PL.TAURON_DYSTRYBUCJA.G11.2026',
                            'componentId' => 'distribution-variable', 'values' => []],
                        ['kind' => 'SUPPLIER_FIXED', 'rate' => '12.00', 'per' => 'BILLING_PERIOD', 'prorate' => false,
                            'taxTreatment' => ['included' => []]],
                    ],
                ]],
            ], true, false],
            [[
                'version' => 2,
                'currency' => 'PLN',
                'timezone' => 'Europe/Warsaw',
                'billingCycles' => [[
                    'validFrom' => '2026-01-01T00:00:00+01:00',
                    'validTo' => '2027-01-01T00:00:00+01:00',
                    'anchor' => '2026-01-01',
                    'length' => 1,
                    'unit' => 'MONTH',
                ]],
                'periods' => [[
                    'validFrom' => '2026-01-01T00:00:00+01:00',
                    'validTo' => '2027-01-01T00:00:00+01:00',
                    'components' => [
                        [
                            'kind' => 'ENERGY_PURCHASE',
                            'presetId' => 'PL.TAURON_SPRZEDAZ.G13.2026',
                            'componentId' => 'energy-purchase',
                            'values' => [
                                'energy.MORNING_PEAK' => '0.98',
                                'energy.AFTERNOON_PEAK' => '0.88',
                                'energy.OFF_PEAK' => '0.62',
                            ],
                        ],
                        ['kind' => 'DISTRIBUTION_VARIABLE', 'presetId' => 'PL.TAURON_DYSTRYBUCJA.G13.2026',
                            'componentId' => 'distribution-variable', 'values' => []],
                    ],
                ]],
            ], true, false],
            [[
                'version' => 2,
                'currency' => 'PLN',
                'timezone' => 'Europe/Warsaw',
                'billingCycles' => [[
                    'validFrom' => '2026-01-01T00:00:00+01:00',
                    'validTo' => '2027-01-01T00:00:00+01:00',
                    'anchor' => '2026-01-01',
                    'length' => 1,
                    'unit' => 'MONTH',
                ]],
                'periods' => [[
                    'validFrom' => '2026-01-01T00:00:00+01:00',
                    'validTo' => '2027-01-01T00:00:00+01:00',
                    'components' => [
                        ['kind' => 'ENERGY_PURCHASE', 'presetId' => 'PL.GENERIC.ENERGY_PURCHASE.CONSTANT.V1',
                            'componentId' => 'energy-purchase', 'values' => ['energy.rate' => '0.71']],
                        [
                            'kind' => 'DISTRIBUTION_VARIABLE',
                            'presetId' => 'PL.TAURON_DYSTRYBUCJA.G14dynamic.2026',
                            'componentId' => 'distribution-variable',
                            'values' => [
                                'distribution.S1' => '0.0224',
                                'distribution.S2' => '0.0893',
                                'distribution.S3' => '0.3881',
                                'distribution.S4' => '2.3756',
                            ],
                        ],
                    ],
                ]],
            ], true, true],
        ];
    }

    private function planService(): EnergyCostPlanService {
        return self::getContainer()->get(EnergyCostPlanService::class);
    }

    private function calculator(): EnergyCostPlanCalculator {
        return self::getContainer()->get(EnergyCostPlanCalculator::class);
    }

    private function createElectricityMeterChannel(User $user): IODeviceChannel {
        return $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]],
        )->getChannels()->first();
    }

    private function insertDelta(int $channelId, string $date = '2026-01-02 12:15:00'): void {
        $this->measurementLogsConnection->insert('supla_em_delta_log', [
            'channel_id' => $channelId,
            'date' => $date,
            'phase1_fae' => 100000,
            'phase1_rae' => 0,
            'phase2_fae' => 0,
            'phase2_rae' => 0,
            'phase3_fae' => 0,
            'phase3_rae' => 0,
            'phase1_fre' => 0,
            'phase1_rre' => 0,
            'phase2_fre' => 0,
            'phase2_rre' => 0,
            'phase3_fre' => 0,
            'phase3_rre' => 0,
            'fae_balanced' => null,
            'rae_balanced' => null,
        ]);
    }

    private function insertReference(string $from): void {
        $from = new DateTimeImmutable($from);
        $this->measurementLogsConnection->insert('supla_energy_price_log', [
            'date_from' => $from->format('Y-m-d H:i:s'),
            'date_to' => $from->modify('+15 minutes -1 second')->format('Y-m-d H:i:s'),
            'rce' => null,
            'pdgsz' => 1,
            'fixing1' => null,
            'fixing2' => null,
            'fixing1_hourly' => null,
            'fixing2_hourly' => null,
        ]);
    }
}
