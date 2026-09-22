<?php

namespace App\Model\MeasurementLogs;

use App\Enums\BillingPeriodUnit;

class EnergyBillingPeriodResolver {
    public function resolveBillingPeriodForTimestamp(
        int $timestamp,
        \DateTimeZone $timezone,
        ?\DateTime $billingAnchorUtc,
        int $billingPeriodLength,
        BillingPeriodUnit $billingPeriodUnit
    ): array {
        if ($billingAnchorUtc === null) {
            return $this->resolveNaturalBillingPeriodForTimestamp($timestamp, $timezone, $billingPeriodLength, $billingPeriodUnit);
        }

        $local = new \DateTime('@' . $timestamp);
        $local->setTimezone($timezone);
        $periodStartLocal = clone $billingAnchorUtc;
        $periodStartLocal->setTimezone($timezone);
        $periodEndLocal = $this->advanceDateTime(clone $periodStartLocal, $billingPeriodLength, $billingPeriodUnit);

        while ($local < $periodStartLocal) {
            $periodEndLocal = clone $periodStartLocal;
            $periodStartLocal = $this->advanceDateTime(clone $periodStartLocal, -$billingPeriodLength, $billingPeriodUnit);
        }

        while ($local >= $periodEndLocal) {
            $periodStartLocal = clone $periodEndLocal;
            $periodEndLocal = $this->advanceDateTime(clone $periodEndLocal, $billingPeriodLength, $billingPeriodUnit);
        }

        return $this->createBillingContext($periodStartLocal, $periodEndLocal, $timezone);
    }

    public function resolveNaturalBillingPeriodForTimestamp(
        int $timestamp,
        \DateTimeZone $timezone,
        int $billingPeriodLength,
        BillingPeriodUnit $billingPeriodUnit
    ): array {
        $local = new \DateTime('@' . $timestamp);
        $local->setTimezone($timezone);
        $periodStartLocal = $this->alignNaturalBillingPeriodStart(clone $local, $billingPeriodLength, $billingPeriodUnit);
        $periodEndLocal = $this->advanceDateTime(clone $periodStartLocal, $billingPeriodLength, $billingPeriodUnit);

        return $this->createBillingContext($periodStartLocal, $periodEndLocal, $timezone);
    }

    public function resolveDefaultBillingPeriodForTimestamp(int $timestamp, \DateTimeZone $timezone): array {
        return $this->resolveNaturalBillingPeriodForTimestamp($timestamp, $timezone, 1, BillingPeriodUnit::MONTH);
    }

    public function advanceDateTime(\DateTime $dateTime, int $length, BillingPeriodUnit $unit): \DateTime {
        $sign = $length >= 0 ? '+' : '-';
        $absoluteLength = abs($length);
        return match ($unit) {
            BillingPeriodUnit::DAY => $dateTime->modify(sprintf('%s%d day', $sign, $absoluteLength)),
            BillingPeriodUnit::WEEK => $dateTime->modify(sprintf('%s%d week', $sign, $absoluteLength)),
            BillingPeriodUnit::MONTH => $dateTime->modify(sprintf('%s%d month', $sign, $absoluteLength)),
            BillingPeriodUnit::YEAR => $dateTime->modify(sprintf('%s%d year', $sign, $absoluteLength)),
        };
    }

    private function createBillingContext(\DateTime $periodStartLocal, \DateTime $periodEndLocal, \DateTimeZone $timezone): array {
        $periodStartUtc = clone $periodStartLocal;
        $periodStartUtc->setTimezone(new \DateTimeZone('UTC'));
        $periodEndUtc = clone $periodEndLocal;
        $periodEndUtc->setTimezone(new \DateTimeZone('UTC'));

        return [
            'key' => $periodStartUtc->format(\DateTime::ATOM) . '|' . $timezone->getName(),
            'periodStart' => $periodStartUtc->format(\DateTime::ATOM),
            'periodEnd' => $periodEndUtc->format(\DateTime::ATOM),
            'timezone' => $timezone->getName(),
        ];
    }

    private function alignNaturalBillingPeriodStart(\DateTime $dateTime, int $length, BillingPeriodUnit $unit): \DateTime {
        $length = max($length, 1);

        return match ($unit) {
            BillingPeriodUnit::DAY => $this->alignDayBillingPeriodStart($dateTime, $length),
            BillingPeriodUnit::WEEK => $this->alignWeekBillingPeriodStart($dateTime, $length),
            BillingPeriodUnit::MONTH => $this->alignMonthBillingPeriodStart($dateTime, $length),
            BillingPeriodUnit::YEAR => $this->alignYearBillingPeriodStart($dateTime, $length),
        };
    }

    private function alignDayBillingPeriodStart(\DateTime $dateTime, int $length): \DateTime {
        $dateTime->setTime(0, 0, 0);
        if ($length === 1) {
            return $dateTime;
        }

        $epoch = new \DateTime('1970-01-01 00:00:00', $dateTime->getTimezone());
        $dayOffset = (int)$epoch->diff($dateTime)->format('%r%a');
        $remainder = (($dayOffset % $length) + $length) % $length;
        return $remainder ? $dateTime->modify(sprintf('-%d day', $remainder)) : $dateTime;
    }

    private function alignWeekBillingPeriodStart(\DateTime $dateTime, int $length): \DateTime {
        $dateTime->setTime(0, 0, 0);
        $dateTime->modify('monday this week');
        if ($length === 1) {
            return $dateTime;
        }

        $epoch = new \DateTime('1970-01-05 00:00:00', $dateTime->getTimezone());
        $dayOffset = (int)$epoch->diff($dateTime)->format('%r%a');
        $weekOffset = intdiv($dayOffset, 7);
        $remainder = (($weekOffset % $length) + $length) % $length;
        return $remainder ? $dateTime->modify(sprintf('-%d week', $remainder)) : $dateTime;
    }

    private function alignMonthBillingPeriodStart(\DateTime $dateTime, int $length): \DateTime {
        $dateTime->setDate((int)$dateTime->format('Y'), (int)$dateTime->format('m'), 1);
        $dateTime->setTime(0, 0, 0);
        if ($length === 1) {
            return $dateTime;
        }

        $monthOffset = ((int)$dateTime->format('Y') * 12) + ((int)$dateTime->format('n') - 1);
        $remainder = (($monthOffset % $length) + $length) % $length;
        return $remainder ? $dateTime->modify(sprintf('-%d month', $remainder)) : $dateTime;
    }

    private function alignYearBillingPeriodStart(\DateTime $dateTime, int $length): \DateTime {
        $dateTime->setDate((int)$dateTime->format('Y'), 1, 1);
        $dateTime->setTime(0, 0, 0);
        if ($length === 1) {
            return $dateTime;
        }

        $year = (int)$dateTime->format('Y');
        $remainder = (($year % $length) + $length) % $length;
        return $remainder ? $dateTime->modify(sprintf('-%d year', $remainder)) : $dateTime;
    }
}
