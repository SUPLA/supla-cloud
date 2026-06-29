<?php

namespace App\Model\MeasurementLogs;

use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Enums\BillingPeriodUnit;
use App\Enums\EnergyPriceComponent;
use App\Enums\EnergyPriceUnit;
use Doctrine\ORM\EntityManagerInterface;

class EnergyFixedCostCalculator {
    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly EnergyBillingPeriodResolver $billingPeriodResolver,
    ) {
    }

    public function applyFixedCostsToSummaries(array &$summaries, int $channelId, int $afterTimestamp, int $beforeTimestamp): void {
        $assignment = $this->findProfileAssignmentForChannel($channelId);
        if (!$assignment || !$assignment->getProfile()) {
            return;
        }

        $rangeStart = new \DateTime('@' . max($afterTimestamp, 0));
        $rangeStart->setTimezone(new \DateTimeZone('UTC'));
        $rangeEnd = $beforeTimestamp > 0 ? new \DateTime('@' . $beforeTimestamp) : new \DateTime('now', new \DateTimeZone('UTC'));
        $rangeEnd->setTimezone(new \DateTimeZone('UTC'));

        foreach ($assignment->getProfile()->getTariffPeriods() as $tariffPeriod) {
            $tariff = $tariffPeriod->getTariff();
            if (!$tariff) {
                continue;
            }
            $timezone = new \DateTimeZone($tariff->getConfig()['timezone'] ?? 'UTC');
            foreach ($tariffPeriod->getPricePeriods() as $pricePeriod) {
                $pricePeriodStart = $this->maxDateTime(
                    clone $rangeStart,
                    $tariffPeriod->getValidFrom(),
                    $pricePeriod->getValidFrom()
                );
                $pricePeriodEnd = $this->minDateTime(
                    clone $rangeEnd,
                    $tariffPeriod->getValidTo(),
                    $pricePeriod->getValidTo()
                );
                $start = $pricePeriodStart > $rangeStart ? $pricePeriodStart : clone $rangeStart;
                $end = $pricePeriodEnd < $rangeEnd ? $pricePeriodEnd : clone $rangeEnd;
                if ($start >= $end) {
                    continue;
                }

                $billingContext = $this->billingPeriodResolver->resolveBillingPeriodForTimestamp(
                    $start->getTimestamp(),
                    $timezone,
                    $pricePeriod->getValidFrom(),
                    $pricePeriod->getBillingPeriodLength(),
                    $pricePeriod->getBillingPeriodUnit()
                );
                while (strtotime($billingContext['periodStart']) < $end->getTimestamp()) {
                    $summaryKey = $billingContext['periodStart'] . '|' . $billingContext['timezone'];
                    if (!isset($summaries[$summaryKey])) {
                        $summaries[$summaryKey] = [
                            'periodStart' => $billingContext['periodStart'],
                            'periodEnd' => $billingContext['periodEnd'],
                            'timezone' => $billingContext['timezone'],
                            'usage' => ['totalKwh' => 0.0, 'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0]],
                            'costs' => [
                                'currency' => $pricePeriod->getCurrency(),
                                'total' => 0.0,
                                'byComponent' => [],
                                'byZone' => [],
                                'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0],
                            ],
                        ];
                    }

                    foreach ($pricePeriod->getItems() as $item) {
                        if ($item->getUnit() === EnergyPriceUnit::KWH) {
                            continue;
                        }
                        if ($item->getUnit() === EnergyPriceUnit::PERIOD) {
                            $this->addSummaryCost($summaries[$summaryKey], $item->getComponentCode(), (float)$item->getAmount());
                        } else {
                            $unitCount = $this->countOverlappingFixedUnits(
                                $billingContext['periodStart'],
                                $billingContext['periodEnd'],
                                $start,
                                $end,
                                $timezone,
                                $item->getUnit()
                            );
                            $this->addSummaryCost($summaries[$summaryKey], $item->getComponentCode(), (float)$item->getAmount() * $unitCount);
                        }
                    }

                    $next = new \DateTime($billingContext['periodEnd'], new \DateTimeZone('UTC'));
                    $billingContext = $this->billingPeriodResolver->resolveBillingPeriodForTimestamp(
                        $next->getTimestamp(),
                        $timezone,
                        $pricePeriod->getValidFrom(),
                        $pricePeriod->getBillingPeriodLength(),
                        $pricePeriod->getBillingPeriodUnit()
                    );
                }
            }
        }
    }

    private function findProfileAssignmentForChannel(int $channelId): ?EnergyTariffProfileAssignment {
        return $this->measurementLogsEntityManager->createQueryBuilder()
            ->select('assignment', 'profile', 'tariffPeriod', 'tariff', 'pricePeriod', 'item')
            ->from(EnergyTariffProfileAssignment::class, 'assignment')
            ->leftJoin('assignment.profile', 'profile')
            ->leftJoin('profile.tariffPeriods', 'tariffPeriod')
            ->leftJoin('tariffPeriod.tariff', 'tariff')
            ->leftJoin('tariffPeriod.pricePeriods', 'pricePeriod')
            ->leftJoin('pricePeriod.items', 'item')
            ->where('assignment.channelId = :channelId')
            ->setParameter('channelId', $channelId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function addSummaryCost(array &$summary, EnergyPriceComponent $componentCode, float $amount): void {
        $summary['costs']['total'] += $amount;
        $summary['costs']['byComponent'][$componentCode->name] = ($summary['costs']['byComponent'][$componentCode->name] ?? 0) + $amount;
    }

    private function countOverlappingFixedUnits(
        string $periodStart,
        string $periodEnd,
        \DateTime $rangeStart,
        \DateTime $rangeEnd,
        \DateTimeZone $timezone,
        EnergyPriceUnit $unit
    ): int {
        $periodStartLocal = new \DateTime($periodStart, new \DateTimeZone('UTC'));
        $periodEndLocal = new \DateTime($periodEnd, new \DateTimeZone('UTC'));
        $periodStartLocal->setTimezone($timezone);
        $periodEndLocal->setTimezone($timezone);
        $overlapStart = clone $periodStartLocal;
        $overlapEnd = clone $periodEndLocal;
        $rangeStartLocal = clone $rangeStart;
        $rangeStartLocal->setTimezone($timezone);
        $rangeEndLocal = clone $rangeEnd;
        $rangeEndLocal->setTimezone($timezone);
        if ($rangeStartLocal > $overlapStart) {
            $overlapStart = clone $rangeStartLocal;
        }
        if ($rangeEndLocal < $overlapEnd) {
            $overlapEnd = clone $rangeEndLocal;
        }
        if ($overlapStart >= $overlapEnd) {
            return 0;
        }
        $cursor = clone $periodStartLocal;
        $count = 0;
        while ($cursor < $periodEndLocal) {
            $next = $this->billingPeriodResolver->advanceDateTime(clone $cursor, 1, $this->mapPriceUnitToBillingPeriodUnit($unit));
            if ($next > $periodEndLocal) {
                $next = clone $periodEndLocal;
            }
            if ($next > $overlapStart && $cursor < $overlapEnd) {
                $count++;
            }
            $cursor = $next;
        }
        return $count;
    }

    private function mapPriceUnitToBillingPeriodUnit(EnergyPriceUnit $unit): BillingPeriodUnit {
        return match ($unit) {
            EnergyPriceUnit::DAY => BillingPeriodUnit::DAY,
            EnergyPriceUnit::WEEK => BillingPeriodUnit::WEEK,
            EnergyPriceUnit::MONTH => BillingPeriodUnit::MONTH,
            default => throw new \InvalidArgumentException('Unsupported fixed price unit.'),
        };
    }

    private function maxDateTime(\DateTime $base, ?\DateTime ...$candidates): \DateTime {
        $max = clone $base;
        foreach ($candidates as $candidate) {
            if ($candidate && $candidate > $max) {
                $max = clone $candidate;
            }
        }
        return $max;
    }

    private function minDateTime(\DateTime $base, ?\DateTime ...$candidates): \DateTime {
        $min = clone $base;
        foreach ($candidates as $candidate) {
            if ($candidate && $candidate < $min) {
                $min = clone $candidate;
            }
        }
        return $min;
    }
}
