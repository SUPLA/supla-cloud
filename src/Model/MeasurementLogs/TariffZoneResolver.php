<?php

namespace App\Model\MeasurementLogs;

use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffHoliday;
use Doctrine\ORM\EntityManagerInterface;

class TariffZoneResolver {
    private const DAY_NAMES = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    private const DEFAULT_RULE_PRIORITY = 500;

    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
    ) {
    }

    /**
     * @return array<int, array{zoneCode: string, startTs: int, endTs: int}>
     */
    public function resolveIntervals(EnergyTariff $tariff, \DateTime $periodStart, \DateTime $periodEnd): array {
        if ($tariff->isDynamic()) {
            return [];
        }

        $config = $tariff->getConfig();
        $timezone = new \DateTimeZone($config['timezone'] ?? 'UTC');
        $ruleIntervals = [];
        $holidayDates = $this->loadHolidayDates($timezone, $periodStart, $periodEnd);
        $localCursor = clone $periodStart;
        $localCursor->setTimezone($timezone);
        $localCursor->setTime(0, 0, 0);
        $localCursor->modify('-1 day');

        $localLimit = clone $periodEnd;
        $localLimit->setTimezone($timezone);
        $localLimit->setTime(0, 0, 0);
        $localLimit->modify('+1 day');

        while ($localCursor < $localLimit) {
            foreach ($this->buildIntervalsForDay($config, $localCursor, $periodStart, $periodEnd, $timezone, $holidayDates) as $interval) {
                $ruleIntervals[] = $interval;
            }
            $localCursor->modify('+1 day');
        }

        if (!$ruleIntervals) {
            return [];
        }

        $boundaries = [$periodStart->getTimestamp(), $periodEnd->getTimestamp()];
        foreach ($ruleIntervals as $interval) {
            $boundaries[] = $interval['start']->getTimestamp();
            $boundaries[] = $interval['end']->getTimestamp();
        }
        sort($boundaries);
        $boundaries = array_values(array_unique($boundaries));

        $intervals = [];
        for ($i = 0; $i < count($boundaries) - 1; $i++) {
            $segmentStartTs = $boundaries[$i];
            $segmentEndTs = $boundaries[$i + 1];
            if ($segmentStartTs >= $segmentEndTs) {
                continue;
            }

            $winner = $this->resolveWinningInterval($ruleIntervals, $segmentStartTs, $segmentEndTs);
            if (!$winner) {
                continue;
            }

            $intervals[] = [
                'zoneCode' => $winner['zone'],
                'startTs' => $segmentStartTs,
                'endTs' => $segmentEndTs,
            ];
        }

        return $this->mergeIntervals($intervals);
    }

    /**
     * @return array<int, array{zone: string, priority: int, ruleOrder: int, start: \DateTime, end: \DateTime}>
     */
    private function buildIntervalsForDay(
        array $config,
        \DateTime $localDay,
        \DateTime $periodStart,
        \DateTime $periodEnd,
        \DateTimeZone $timezone,
        array $holidayDates
    ): array {
        $rules = $config['rules'] ?? [];
        $dayName = self::DAY_NAMES[(int)$localDay->format('w')];
        $seasonId = $this->resolveSeasonId($config['seasons'] ?? [], $localDay);
        $isHoliday = isset($holidayDates[$localDay->format('Y-m-d')]);
        $intervals = [];
        foreach (array_values($rules) as $ruleIndex => $rule) {
            if (!$this->matchesDay($rule['days'] ?? [], $dayName, $isHoliday)) {
                continue;
            }
            if (!$this->matchesSeason($rule['season'] ?? null, $seasonId)) {
                continue;
            }

            foreach ($rule['time_ranges'] ?? [] as $timeRange) {
                [$fromHour, $fromMinute, $fromNextDay] = $this->parseTime($timeRange['from']);
                [$toHour, $toMinute, $toNextDay] = $this->parseTime($timeRange['to']);

                $localStart = new \DateTime($localDay->format('Y-m-d H:i:s'), $timezone);
                $localStart->setTime($fromHour, $fromMinute, 0);
                if ($fromNextDay) {
                    $localStart->modify('+1 day');
                }

                $localEnd = new \DateTime($localDay->format('Y-m-d H:i:s'), $timezone);
                $localEnd->setTime($toHour, $toMinute, 0);
                if ($toNextDay) {
                    $localEnd->modify('+1 day');
                }
                if ($localEnd <= $localStart) {
                    $localEnd->modify('+1 day');
                }

                $startUtc = clone $localStart;
                $startUtc->setTimezone(new \DateTimeZone('UTC'));
                $endUtc = clone $localEnd;
                $endUtc->setTimezone(new \DateTimeZone('UTC'));

                if ($endUtc <= $periodStart || $startUtc >= $periodEnd) {
                    continue;
                }
                if ($startUtc < $periodStart) {
                    $startUtc = clone $periodStart;
                }
                if ($endUtc > $periodEnd) {
                    $endUtc = clone $periodEnd;
                }

                $intervals[] = [
                    'zone' => (string)$rule['zone'],
                    'priority' => (int)($rule['priority'] ?? self::DEFAULT_RULE_PRIORITY),
                    'ruleOrder' => $ruleIndex,
                    'start' => $startUtc,
                    'end' => $endUtc,
                ];
            }
        }

        return $intervals;
    }

    private function resolveWinningInterval(array $ruleIntervals, int $segmentStartTs, int $segmentEndTs): ?array {
        $winner = null;
        foreach ($ruleIntervals as $interval) {
            if ($interval['start']->getTimestamp() > $segmentStartTs || $interval['end']->getTimestamp() < $segmentEndTs) {
                continue;
            }
            if (
                !$winner
                || $interval['priority'] < $winner['priority']
                || ($interval['priority'] === $winner['priority'] && $interval['ruleOrder'] < $winner['ruleOrder'])
            ) {
                $winner = $interval;
            }
        }

        return $winner;
    }

    private function matchesDay(array $ruleDays, string $dayName, bool $isHoliday): bool {
        return in_array($dayName, $ruleDays, true) || ($isHoliday && in_array('holiday', $ruleDays, true));
    }

    private function matchesSeason(?string $seasonRule, ?string $seasonId): bool {
        return $seasonRule === null || $seasonRule === '*' || $seasonRule === $seasonId;
    }

    private function resolveSeasonId(array $seasons, \DateTime $localDay): ?string {
        foreach ($seasons as $season) {
            if ($this->isWithinSeason($localDay, (string)$season['from'], (string)$season['to'])) {
                return $season['id'] ?? null;
            }
        }

        return null;
    }

    /**
     * @return array<string, true>
     */
    private function loadHolidayDates(\DateTimeZone $timezone, \DateTime $periodStart, \DateTime $periodEnd): array {
        $localStart = clone $periodStart;
        $localStart->setTimezone($timezone);
        $localStart = new \DateTimeImmutable($localStart->format('Y-m-d'));

        $localEnd = clone $periodEnd;
        $localEnd->setTimezone($timezone);
        $localEnd = new \DateTimeImmutable($localEnd->format('Y-m-d'));

        $holidays = $this->measurementLogsEntityManager->createQueryBuilder()
            ->select('h')
            ->from(EnergyTariffHoliday::class, 'h')
            ->where('h.timezone = :timezone')
            ->andWhere('h.date >= :startDate')
            ->andWhere('h.date <= :endDate')
            ->setParameter('timezone', $timezone->getName())
            ->setParameter('startDate', $localStart)
            ->setParameter('endDate', $localEnd)
            ->getQuery()
            ->getResult();

        $holidayDates = [];
        foreach ($holidays as $holiday) {
            $holidayDates[$holiday->getDate()->format('Y-m-d')] = true;
        }

        return $holidayDates;
    }

    private function isWithinSeason(\DateTime $localDay, string $from, string $to): bool {
        $dayMd = $localDay->format('m-d');
        $fromMd = substr($from, 2);
        $toMd = substr($to, 2);

        if ($fromMd === $toMd) {
            return true;
        }
        if ($fromMd < $toMd) {
            return $dayMd >= $fromMd && $dayMd < $toMd;
        }

        return $dayMd >= $fromMd || $dayMd < $toMd;
    }

    /**
     * @return array{0: int, 1: int, 2: bool}
     */
    private function parseTime(string $time): array {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        if ($hour === 24 && $minute === 0) {
            return [0, 0, true];
        }

        return [$hour, $minute, false];
    }

    /**
     * @param array<int, array{zoneCode: string, startTs: int, endTs: int}> $intervals
     * @return array<int, array{zoneCode: string, startTs: int, endTs: int}>
     */
    private function mergeIntervals(array $intervals): array {
        $merged = [];
        foreach ($intervals as $interval) {
            if (!$merged) {
                $merged[] = $interval;
                continue;
            }

            $lastIndex = count($merged) - 1;
            if ($merged[$lastIndex]['zoneCode'] === $interval['zoneCode'] && $merged[$lastIndex]['endTs'] === $interval['startTs']) {
                $merged[$lastIndex]['endTs'] = $interval['endTs'];
            } else {
                $merged[] = $interval;
            }
        }

        return $merged;
    }
}
