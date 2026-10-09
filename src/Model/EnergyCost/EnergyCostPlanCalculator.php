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
use App\Repository\EnergyCostPlanAssignmentRepository;
use DateTimeImmutable;
use DateTimeZone;
use Supla\EnergyCostCalculator\Engine\CalculationOptions;
use Supla\EnergyCostCalculator\Engine\CalculationProblemPolicy;
use Supla\EnergyCostCalculator\Engine\CalculationResult;
use Supla\EnergyCostCalculator\Engine\CostCalculator;
use Supla\EnergyCostCalculator\Exception\CalculationException;
use Supla\EnergyCostCalculator\Model\TimeRange;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnergyCostPlanCalculator {
    public function __construct(
        private readonly EnergyCostPlanAssignmentRepository $assignmentRepository,
        private readonly CostPlanDefinitionParser $parser,
        private readonly CostPlanCompiler $compiler,
        private readonly CostCalculator $calculator,
        private readonly ?SuplaEnergyDeltaSource $energyDeltaSource = null,
    ) {
    }

    public function calculate(
        User $user,
        IODeviceChannel $channel,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
    ): CalculationResult {
        if (!$channel->belongsToUser($user)) {
            throw new AccessDeniedHttpException('Access to this channel is denied.');
        }
        if ($channel->getFunction()->getId() !== ChannelFunction::ELECTRICITYMETER) {
            throw new ApiException('Energy cost calculation is supported only for electricity meter channels.');
        }
        $plan = $this->assignmentRepository->findPlanForChannel($channel);
        if ($plan === null || !$plan->belongsToUser($user)) {
            throw new NotFoundHttpException('Energy cost plan assignment does not exist.');
        }

        // Compile on every request so live corrections to referenced presets take effect.
        return $this->calculateConfiguration($user, $channel, $plan->getConfiguration(), $from, $to);
    }

    /** @param array<string, mixed> $configuration */
    public function calculateConfiguration(
        User $user,
        IODeviceChannel $channel,
        array $configuration,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
    ): CalculationResult {
        if (!$channel->belongsToUser($user)) {
            throw new AccessDeniedHttpException('Access to this channel is denied.');
        }
        if ($channel->getFunction()->getId() !== ChannelFunction::ELECTRICITYMETER) {
            throw new ApiException('Energy cost calculation is supported only for electricity meter channels.');
        }

        $definition = $this->compiler->compile($this->parser->parse($configuration));
        $utc = new DateTimeZone('UTC');
        $range = new TimeRange($from->setTimezone($utc), $to->setTimezone($utc));

        try {
            return $this->calculator->calculate(
                (string)$channel->getId(),
                $range,
                $definition,
                new CalculationOptions(
                    includeIntervals: true,
                    includeCharges: true,
                    calculationProblemPolicy: CalculationProblemPolicy::SKIP_AFFECTED,
                ),
            );
        } catch (CalculationException $exception) {
            $availableRange = $this->energyDeltaSource?->longestContinuousRange((string)$channel->getId(), $range);
            if ($availableRange === null && $this->energyDeltaSource !== null) {
                return new CalculationResult(
                    $definition->currency,
                    $range,
                    null,
                    $definition->billingCycles,
                    [],
                    [
                        'net' => ['total' => null],
                        'taxes' => ['total' => null, 'byTax' => []],
                        'gross' => [
                            'total' => null,
                            'usageBased' => ['total' => '0', 'byComponent' => [], 'byZone' => []],
                            'periodic' => ['total' => null, 'byComponent' => []],
                        ],
                    ],
                    [],
                    ['requestedRangeCoversWholePeriods' => false, 'periods' => []],
                    [],
                    0,
                );
            }
            if ($availableRange === null || $availableRange == $range) {
                throw $exception;
            }

            return $this->calculator->calculate(
                (string)$channel->getId(),
                $availableRange,
                $definition,
                new CalculationOptions(
                    includeIntervals: true,
                    includeCharges: true,
                    calculationProblemPolicy: CalculationProblemPolicy::SKIP_AFFECTED,
                ),
            );
        }
    }
}
