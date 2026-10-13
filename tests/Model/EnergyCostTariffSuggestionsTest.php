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
use App\Exception\ApiException;
use App\Model\EnergyCost\EnergyCostTariffSuggestions;
use App\Model\EnergyCost\EnergyObservationRangeSource;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Supla\EnergyCostCalculator\Contract\EnergyDeltaSource;
use Supla\EnergyCostCalculator\Contract\ReferenceDataSource;
use Supla\EnergyCostCalculator\Model\EnergyDelta;
use Supla\EnergyCostCalculator\Model\ReferenceInterval;
use Supla\EnergyCostCalculator\Model\TimeRange;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Supla\EnergyCostCalculator\Plan\CostPlanStarterCatalog;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnergyCostTariffSuggestionsTest extends TestCase {
    private function configuration(): array {
        return [
            'version' => 2, 'currency' => 'PLN', 'timezone' => 'Europe/Warsaw',
            'billingCycles' => [['anchor' => '2026-01-01', 'length' => 1, 'unit' => 'MONTH']],
            'periods' => [['components' => $this->catalog()->get('normal')->components]],
        ];
    }

    private function catalog(): CostPlanStarterCatalog {
        return new CostPlanStarterCatalog(__DIR__ . '/fixtures/energy-cost-suggestion-starters.json');
    }

    /** @dataProvider ranges */
    public function testRanksDisplayedTotalsAndKeepsUnavailableTariffs(string $from, string $to, string $cheapest, bool $complete): void {
        $user = $this->createMock(User::class);
        $channel = $this->channel($user);
        $source = $this->createMock(EnergyDeltaSource::class);
        $source->expects(self::once())->method('getDeltas')->willReturn($this->deltas());
        $references = $this->createMock(ReferenceDataSource::class);
        // Both dynamic tariffs share one fetch, even when the series is empty.
        $references->expects(self::once())->method('get')->willReturn([]);
        $service = new EnergyCostTariffSuggestions(
            $source,
            $references,
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            $this->catalog(),
            $this->observations(),
        );
        $result = $service->suggest($user, $channel, $this->configuration(), new DateTimeImmutable($from), new DateTimeImmutable($to));
        self::assertSame($cheapest, $result['cheapestId']);
        $tariffs = array_column($result['tariffs'], null, 'id');
        self::assertCount(6, $tariffs);
        self::assertSame($complete, $tariffs['normal']['fixedChargesComplete']);
        self::assertSame($tariffs['cheap-usage']['total'], $tariffs['tie']['total']);
        self::assertFalse($tariffs['missing-preset']['eligible']);
        self::assertNull($tariffs['missing-preset']['total']);
        self::assertFalse($tariffs['dynamic']['eligible']);
        self::assertFalse($tariffs['dynamic-markup']['eligible']);
        self::assertSame($complete ? '14.268' : '1.968', $tariffs['normal']['total']);
        self::assertSame($complete ? '26.322' : '1.722', $tariffs['cheap-usage']['total']);
    }

    public static function ranges(): iterable {
        yield 'partial excludes fixed charges and preserves first tie' => ['2026-09-10T00:00:00+02:00', '2026-09-11T00:00:00+02:00', 'cheap-usage', false];
        yield 'whole month includes fixed charges' => ['2026-09-01T00:00:00+02:00', '2026-10-01T00:00:00+02:00', 'normal', true];
    }

    public function testReusesReferenceDataAndAllowsDynamicTariffsWithCompleteData(): void {
        $user = $this->createMock(User::class);
        $source = $this->createMock(EnergyDeltaSource::class);
        $source->expects(self::once())->method('getDeltas')->willReturn($this->deltas());
        $references = $this->createMock(ReferenceDataSource::class);
        $references->expects(self::once())->method('get')->willReturn([
            new ReferenceInterval(new DateTimeImmutable('2026-09-10T12:00:00+02:00'), new DateTimeImmutable('2026-09-10T13:00:00+02:00'), '500', 'PLN/MWh'),
        ]);
        $service = new EnergyCostTariffSuggestions(
            $source,
            $references,
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            $this->catalog(),
            $this->observations(),
        );
        $result = $service->suggest(
            $user,
            $this->channel($user),
            $this->configuration(),
            new DateTimeImmutable('2026-09-10'),
            new DateTimeImmutable('2026-09-11'),
        );
        self::assertSame('dynamic', $result['cheapestId']);
        $tariffs = array_column($result['tariffs'], null, 'id');
        self::assertTrue($tariffs['dynamic-markup']['eligible']);
        self::assertSame('1.2423', $tariffs['dynamic']['total']);
    }

    public function testNoMeasurementsCannotProduceARecommendation(): void {
        $user = $this->createMock(User::class);
        $source = $this->createMock(EnergyDeltaSource::class);
        $source->method('getDeltas')->willReturn([]);
        $references = $this->createMock(ReferenceDataSource::class);
        $references->method('get')->willReturn([]);
        $service = new EnergyCostTariffSuggestions(
            $source,
            $references,
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            $this->catalog(),
            $this->observations(),
        );
        $result = $service->suggest(
            $user,
            $this->channel($user),
            $this->configuration(),
            new DateTimeImmutable('2026-09-01'),
            new DateTimeImmutable('2026-10-01'),
        );
        self::assertNull($result['cheapestId']);
        self::assertEmpty(array_filter($result['tariffs'], fn(array $tariff): bool => $tariff['eligible']));
    }

    /** @dataProvider rejectedRequests */
    public function testRejectsUnauthorizedOrLongRequestsBeforeFetchingData(bool $owned, int $seconds, string $exception): void {
        $user = $this->createMock(User::class);
        $source = $this->createMock(EnergyDeltaSource::class);
        $source->expects(self::never())->method('getDeltas');
        $service = new EnergyCostTariffSuggestions(
            $source,
            $this->createMock(ReferenceDataSource::class),
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            $this->catalog(),
            $this->createMock(EnergyObservationRangeSource::class),
        );
        $this->expectException($exception);
        $service->suggest($user, $this->channel($user, $owned), $this->configuration(), new DateTimeImmutable('@0'), new DateTimeImmutable('@' . $seconds));
    }

    public static function rejectedRequests(): iterable {
        yield 'ownership' => [false, 86400, AccessDeniedHttpException::class];
        yield 'over 31 days' => [true, EnergyCostTariffSuggestions::MAX_RANGE_DAYS * 86400 + 1, ApiException::class];
        yield 'empty range' => [true, 0, ApiException::class];
    }

    /** @dataProvider calendarRanges */
    public function testExactly31CalendarDaysIsAllowed(string $fromDate, string $toDate): void {
        $user = $this->createMock(User::class);
        $source = $this->createMock(EnergyDeltaSource::class);
        $source->expects(self::once())->method('getDeltas')->willReturn([]);
        $references = $this->createMock(ReferenceDataSource::class);
        $references->method('get')->willReturn([]);
        $service = new EnergyCostTariffSuggestions(
            $source,
            $references,
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            $this->catalog(),
            $this->observations(),
        );
        $from = new DateTimeImmutable($fromDate);
        $to = new DateTimeImmutable($toDate);
        self::assertNull($service->suggest($user, $this->channel($user), $this->configuration(), $from, $to)['cheapestId']);
        $this->expectException(ApiException::class);
        $service->suggest($user, $this->channel($user), $this->configuration(), $from, $to->modify('+1 second'));
    }

    public static function calendarRanges(): iterable {
        yield 'summer' => ['2026-07-01T00:00:00+02:00', '2026-08-01T00:00:00+02:00'];
        yield 'DST end October 5 through November 4 inclusive' => ['2026-10-04T22:00:00Z', '2026-11-04T23:00:00Z'];
        yield 'DST start' => ['2026-03-04T23:00:00Z', '2026-04-04T22:00:00Z'];
    }

    private function channel(User $user, bool $owned = true): IODeviceChannel {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('belongsToUser')->with($user)->willReturn($owned);
        $channel->method('getFunction')->willReturn(ChannelFunction::ELECTRICITYMETER());
        $channel->method('getId')->willReturn(123);
        return $channel;
    }

    private function observations(): EnergyObservationRangeSource {
        $source = $this->createMock(EnergyObservationRangeSource::class);
        $source->expects(self::once())->method('getObservationRange')->with('123')->willReturn(new TimeRange(
            new DateTimeImmutable('2026-09-03T00:00:00+02:00'),
            new DateTimeImmutable('2026-10-09T00:00:00+02:00'),
        ));
        return $source;
    }

    public function testSuggestionTotalsExcludeUnobservedBillingPeriods(): void {
        $user = $this->createMock(User::class);
        $deltas = $this->createMock(EnergyDeltaSource::class);
        $deltas->method('getDeltas')->willReturn([]);
        $service = new EnergyCostTariffSuggestions(
            $deltas,
            $this->createMock(ReferenceDataSource::class),
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            $this->catalog(),
            $this->observations(),
        );
        $result = $service->suggest(
            $user,
            $this->channel($user),
            $this->configuration(),
            new DateTimeImmutable('2026-11-01T00:00:00+01:00'),
            new DateTimeImmutable('2026-12-01T00:00:00+01:00'),
        );
        $tariffs = array_column($result['tariffs'], null, 'id');
        self::assertSame('0', $tariffs['normal']['total']);
        self::assertSame('0', $tariffs['cheap-usage']['total']);
        self::assertTrue($tariffs['normal']['fixedChargesComplete']);
        self::assertNull($result['cheapestId']);
    }

    public function testEmptyHistoryCannotProduceUnrestrictedFixedChargeSuggestions(): void {
        $user = $this->createMock(User::class);
        $deltas = $this->createMock(EnergyDeltaSource::class);
        $deltas->expects(self::never())->method('getDeltas');
        $service = new EnergyCostTariffSuggestions(
            $deltas,
            $this->createMock(ReferenceDataSource::class),
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            $this->catalog(),
            $this->createMock(EnergyObservationRangeSource::class),
        );
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('No logs are available for the selected range.');
        $service->suggest(
            $user,
            $this->channel($user),
            $this->configuration(),
            new DateTimeImmutable('2026-09-01T00:00:00+02:00'),
            new DateTimeImmutable('2026-10-01T00:00:00+02:00'),
        );
    }

    private function deltas(): iterable {
        $start = new DateTimeImmutable('2026-09-10T12:00:00+02:00');
        for ($slot = 0; $slot < 4; $slot++) {
            $from = $start->modify('+' . ($slot * 15) . ' minutes');
            yield new EnergyDelta($from, $from->modify('+15 minutes'), ['ACTIVE_ENERGY_IMPORT' => '0.5', 'ACTIVE_ENERGY_EXPORT' => '0']);
        }
    }
}
