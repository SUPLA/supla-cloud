<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Model\EnergyCost;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Exception\ApiException;
use DateTimeImmutable;
use DateTimeZone;
use Supla\EnergyCostCalculator\Contract\EnergyDeltaSource;
use Supla\EnergyCostCalculator\Contract\ReferenceDataSource;
use Supla\EnergyCostCalculator\Engine\CalculationOptions;
use Supla\EnergyCostCalculator\Engine\CalculationProblemPolicy;
use Supla\EnergyCostCalculator\Engine\CostCalculator;
use Supla\EnergyCostCalculator\Exception\EnergyCostCalculatorException;
use Supla\EnergyCostCalculator\Model\EnergyDelta;
use Supla\EnergyCostCalculator\Model\ReferenceDataId;
use Supla\EnergyCostCalculator\Model\ReferenceInterval;
use Supla\EnergyCostCalculator\Model\TimeRange;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Supla\EnergyCostCalculator\Plan\CostPlanStarterCatalog;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnergyCostTariffSuggestions {
    public const MAX_RANGE_DAYS = 31;

    public function __construct(
        private readonly EnergyDeltaSource $deltaSource,
        private readonly ReferenceDataSource $referenceDataSource,
        private readonly CostPlanDefinitionParser $parser,
        private readonly CostPlanCompiler $compiler,
        private readonly CostPlanStarterCatalog $catalog,
        private readonly EnergyObservationRangeSource $observationSource,
    ) {
    }

    /**
     * @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    public function suggest(User $user, IODeviceChannel $channel, array $configuration, DateTimeImmutable $from, DateTimeImmutable $to): array {
        if (!$channel->belongsToUser($user)) {
            throw new AccessDeniedHttpException('Access to this channel is denied.');
        }
        if ($channel->getFunction()->getId() !== ChannelFunction::ELECTRICITYMETER) {
            throw new ApiException('Energy cost calculation is supported only for electricity meter channels.');
        }
        $definition = $this->compiler->compile($this->parser->parse($configuration));
        $maximumTo = $from->setTimezone(new DateTimeZone($definition->timezone))->modify('+' . self::MAX_RANGE_DAYS . ' days');
        if ($to <= $from || $to > $maximumTo) {
            throw new ApiException('Tariff suggestions require a period of at most 31 days.');
        }
        $range = new TimeRange($from, $to);
        $observationRange = $this->observationSource->getObservationRange((string)$channel->getId());
        if ($observationRange === null) {
            throw new ApiException('No logs are available for the selected range.');
        }
        // The range is bounded, so materialize canonical facts once for all tariffs.
        $deltas = iterator_to_array((function () use ($channel, $range): iterable {
            yield from $this->deltaSource->getDeltas((string)$channel->getId(), $range);
        })(), false);
        $source = new class ($deltas) implements EnergyDeltaSource {
            /** @param list<EnergyDelta> $deltas */
            public function __construct(private readonly array $deltas) {
            }

            public function getDeltas(string $meterId, TimeRange $range): iterable {
                return $this->deltas;
            }
        };
        $references = new class ($this->referenceDataSource) implements ReferenceDataSource {
            /** @var array<string, list<ReferenceInterval>> */
            private array $cache = [];

            public function __construct(private readonly ReferenceDataSource $source) {
            }

            public function get(ReferenceDataId $id, TimeRange $range): iterable {
                // All calculations in this batch use exactly the same range.
                return $this->cache[$id->value] ??= iterator_to_array((function () use ($id, $range): iterable {
                    yield from $this->source->get($id, $range);
                })(), false);
            }
        };
        $calculator = new CostCalculator($source, $references);
        $options = new CalculationOptions(
            includeIntervals: false,
            includeCharges: false,
            calculationProblemPolicy: CalculationProblemPolicy::SKIP_AFFECTED,
            includeReportSummary: true,
            observationRange: $observationRange,
        );
        $calculate = fn(array $config): array => $calculator->calculate(
            (string)$channel->getId(),
            $range,
            $this->compiler->compile($this->parser->parse($config)),
            $options,
        )->jsonSerialize();
        $baseline = $calculator->calculate((string)$channel->getId(), $range, $definition, $options)->jsonSerialize();
        $valid = static fn(array $result): bool => $result['processedDeltaCount'] > 0
            && !array_filter($result['warnings'], fn(array $warning): bool => $warning['code'] !== 'MISSING_METER_DATA');
        $results = [];
        $cheapestId = null;
        $cheapestTotal = null;
        foreach ($this->catalog->starters() as $metadata) {
            $id = $metadata['id'];
            try {
                $starter = $this->catalog->get($id);
                $alternative = $configuration;
                $alternative['periods'] = [['validFrom' => null, 'validTo' => null, 'components' => $starter->components]];
                $result = $calculate($alternative);
                $total = $result['reportSummary']['costs']['gross']['total'];
                $eligible = $valid($baseline) && $valid($result)
                    && $result['currency'] === $baseline['currency']
                    && $result['processedDeltaCount'] === $baseline['processedDeltaCount']
                    && $result['usage'] === $baseline['usage'];
                $results[] = [
                    'id' => $id, 'total' => $total, 'currency' => $result['currency'],
                    'fixedChargesComplete' => $result['reportSummary']['fixedChargesComplete'],
                    'eligible' => $eligible,
                    'error' => $eligible ? null : 'This tariff cannot be reliably compared for the selected period.',
                ];
                if ($eligible && ($cheapestTotal === null || $this->compareAmounts($total, $cheapestTotal) < 0)) {
                    $cheapestId = $id;
                    $cheapestTotal = $total;
                }
            } catch (EnergyCostCalculatorException $exception) {
                $results[] = ['id' => $id, 'total' => null, 'eligible' => false, 'error' => 'This tariff could not be calculated for the selected period.'];
            }
        }
        return ['tariffs' => $results, 'cheapestId' => $cheapestId];
    }

    private function compareAmounts(string $left, string $right): int {
        $negativeLeft = str_starts_with($left, '-');
        $negativeRight = str_starts_with($right, '-');
        if ($negativeLeft !== $negativeRight) {
            return $negativeLeft ? -1 : 1;
        }
        [$leftWhole, $leftFraction] = explode('.', ltrim($left, '+-'), 2) + [1 => ''];
        [$rightWhole, $rightFraction] = explode('.', ltrim($right, '+-'), 2) + [1 => ''];
        $leftWhole = ltrim($leftWhole, '0');
        $rightWhole = ltrim($rightWhole, '0');
        $scale = max(strlen($leftFraction), strlen($rightFraction));
        $comparison = strlen($leftWhole) <=> strlen($rightWhole);
        $comparison = $comparison ?: strcmp($leftWhole, $rightWhole);
        $comparison = $comparison ?: strcmp(str_pad($leftFraction, $scale, '0'), str_pad($rightFraction, $scale, '0'));
        return $negativeLeft ? -$comparison : $comparison;
    }
}
