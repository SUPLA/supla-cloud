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
use Supla\EnergyCostCalculator\Engine\CalculationResult;
use Supla\EnergyCostCalculator\Engine\CostCalculator;
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
        $definition = $this->compiler->compile($this->parser->parse($plan->getConfiguration()));
        $utc = new DateTimeZone('UTC');
        $range = new TimeRange($from->setTimezone($utc), $to->setTimezone($utc));

        return $this->calculator->calculate(
            (string)$channel->getId(),
            $range,
            $definition,
            new CalculationOptions(includeIntervals: true),
        );
    }
}
