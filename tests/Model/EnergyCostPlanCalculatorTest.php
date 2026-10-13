<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Model;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Model\EnergyCost\EnergyCostPlanCalculator;
use App\Repository\EnergyCostPlanAssignmentRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Supla\EnergyCostCalculator\Contract\EnergyDeltaSource;
use Supla\EnergyCostCalculator\Contract\ReferenceDataSource;
use Supla\EnergyCostCalculator\Engine\CostCalculator;
use Supla\EnergyCostCalculator\Math\NativeDecimalMath;
use Supla\EnergyCostCalculator\Model\EnergyDelta;
use Supla\EnergyCostCalculator\Model\TimeRange;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Symfony\Component\HttpFoundation\JsonResponse;

class EnergyCostPlanCalculatorTest extends TestCase {
    /** @dataProvider selectedRanges */
    public function testReportsOriginalSelectionWithOnlyAvailableMeasurements(string $from, string $to, string $fixed, bool $complete): void {
        $user = $this->createMock(User::class);
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('belongsToUser')->with($user)->willReturn(true);
        $channel->method('getFunction')->willReturn(ChannelFunction::ELECTRICITYMETER());
        $channel->method('getId')->willReturn(123);
        $source = new class implements EnergyDeltaSource {
            public ?TimeRange $requestedRange = null;

            public function getDeltas(string $meterId, TimeRange $range): iterable {
                $this->requestedRange = $range;
                foreach (['2026-08-10', '2026-09-10', '2026-10-10'] as $date) {
                    $start = new DateTimeImmutable($date . 'T12:00:00+02:00');
                    for ($slot = 0; $slot < 4; $slot++) {
                        $deltaFrom = $start->modify('+' . ($slot * 15) . ' minutes');
                        $deltaTo = $deltaFrom->modify('+15 minutes');
                        if ($deltaFrom >= $range->from && $deltaTo <= $range->to) {
                            yield new EnergyDelta($deltaFrom, $deltaTo, ['ACTIVE_ENERGY_IMPORT' => '0.5', 'ACTIVE_ENERGY_EXPORT' => '0']);
                        }
                    }
                }
            }
        };
        $references = $this->createMock(ReferenceDataSource::class);
        $service = new EnergyCostPlanCalculator(
            $this->createMock(EnergyCostPlanAssignmentRepository::class),
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            new CostCalculator($source, $references),
        );
        $configuration = [
            'version' => 2, 'currency' => 'PLN', 'timezone' => 'Europe/Warsaw',
            'billingCycles' => [['validFrom' => null, 'validTo' => null, 'anchor' => '2026-01-01', 'length' => 1, 'unit' => 'MONTH']],
            'periods' => [['validFrom' => null, 'validTo' => null, 'components' => [
                ['kind' => 'ENERGY_PURCHASE', 'componentId' => 'energy-purchase', 'presetId' => 'PL.TAURON_SPRZEDAZ.G11',
                    'values' => ['energy.rate' => '0.71']],
                ['kind' => 'SUPPLIER_FIXED', 'componentId' => 'fixed', 'rate' => '10', 'per' => 'MONTH', 'taxTreatment' => ['included' => []]],
            ]]],
        ];
        $result = $service->calculateConfiguration($user, $channel, $configuration, new DateTimeImmutable($from), new DateTimeImmutable($to));
        self::assertEquals(new DateTimeImmutable($from), $source->requestedRange->from);
        self::assertEquals(new DateTimeImmutable($to), $source->requestedRange->to);
        self::assertSame('UTC', $source->requestedRange->from->getTimezone()->getName());
        // Exercise exactly the JsonResponse serialization used by both endpoints.
        $json = json_decode((new JsonResponse($result))->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $report = $json['reportSummary'];
        self::assertSame($complete, $report['fixedChargesComplete']);
        self::assertSame($fixed, $report['costs']['gross']['periodic']['total']);
        self::assertNotNull($report['costs']['taxes']['total']);
        self::assertGreaterThan(0, (float)$report['costs']['gross']['usageBased']['total']);
        $math = new NativeDecimalMath();
        self::assertSame($math->add($report['costs']['net']['total'], $report['costs']['taxes']['total']), $report['costs']['gross']['total']);
        foreach ($json['charges'] as $charge) {
            self::assertSame('energy-purchase', $charge['componentId']);
        }
        if (!$complete) {
            self::assertNull($json['costs']['gross']['total']);
        }
    }

    public static function selectedRanges(): iterable {
        yield 'historical partial month' => ['2026-08-10T00:00:00+02:00', '2026-08-21T00:00:00+02:00', '0', false];
        yield 'ongoing partial month' => ['2026-10-01T00:00:00+02:00', '2026-10-14T00:00:00+02:00', '0', false];
        yield 'complete month beyond readings and DST' => ['2026-10-01T00:00:00+02:00', '2026-11-01T00:00:00+01:00', '12.3', true];
        yield 'fully covered middle month' => ['2026-08-10T00:00:00+02:00', '2026-10-21T00:00:00+02:00', '12.3', false];
        yield 'no full periods' => ['2026-08-10T00:00:00+02:00', '2026-09-21T00:00:00+02:00', '0', false];
    }
}
