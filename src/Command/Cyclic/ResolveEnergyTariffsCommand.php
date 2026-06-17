<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace App\Command\Cyclic;

use App\Command\Initialization\InitializationCommand;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffHoliday;
use App\Entity\MeasurementLogs\EnergyTariffResolvedZone;
use App\Model\TimeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class ResolveEnergyTariffsCommand extends AbstractCyclicCommand implements InitializationCommand {
    private const DEFAULT_MONTHS_AHEAD = 3;
    private const DEFAULT_MONTHS_BACK = 1;
    private const DAY_NAMES = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    private const DEFAULT_RULE_PRIORITY = 500;

    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly LockFactory $lockFactory,
        private readonly TimeProvider $timeProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setHidden(true)
            ->setName('supla:cyclic:resolve-energy-tariffs')
            ->setDescription('Materializes future tariff zones for fixed energy tariffs.')
            ->addOption('months-ahead', null, InputOption::VALUE_REQUIRED, 'How many months ahead should be materialized.', self::DEFAULT_MONTHS_AHEAD)
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Override materialization start date/time in UTC (Y-m-d H:i:s).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $lock = $this->lockFactory->createLock('supla-resolve-energy-tariffs');
        if (!$lock->acquire()) {
            $output->writeln('The command is already running.');
            return self::SUCCESS;
        }

        try {
            $monthsAhead = max(1, (int)$input->getOption('months-ahead'));
            /** @var EnergyTariff[] $tariffs */
            $tariffs = $this->measurementLogsEntityManager->getRepository(EnergyTariff::class)->findAll();
            foreach ($tariffs as $tariff) {
                $this->resolveTariff($tariff, $monthsAhead, $input->getOption('from'), $output);
                $this->measurementLogsEntityManager->flush();
                $this->measurementLogsEntityManager->clear();
            }
        } finally {
            $lock->release();
        }

        return self::SUCCESS;
    }

    private function resolveTariff(EnergyTariff $tariff, int $monthsAhead, ?string $fromOption, OutputInterface $output): void {
        $config = $tariff->getConfig();

        $timezone = new \DateTimeZone($config['timezone'] ?? 'UTC');
        $periodStart = $this->resolvePeriodStart($fromOption, $timezone);
        if (!$fromOption && ($assignmentStart = $this->findEarliestTariffAssignmentStart($tariff))) {
            $periodStart = $assignmentStart < $periodStart ? $assignmentStart : $periodStart;
        }
        $periodEnd = (clone $periodStart)->add(new \DateInterval('P' . $monthsAhead . 'M'));

        if ($output->isVerbose()) {
            $output->writeln(sprintf('Resolving tariff %s from %s to %s', $tariff->getCode(), $periodStart->format('Y-m-d H:i:s'), $periodEnd->format('Y-m-d H:i:s')));
        }

        $this->measurementLogsEntityManager->createQueryBuilder()
            ->delete(EnergyTariffResolvedZone::class, 'z')
            ->where('z.tariffId = :tariffId')
            ->andWhere('z.periodStart >= :periodStart')
            ->setParameter('tariffId', $tariff->getId())
            ->setParameter('periodStart', $periodStart)
            ->getQuery()
            ->execute();

        foreach ($this->buildResolvedZones($tariff, $periodStart, $periodEnd, $timezone) as $zone) {
            $this->measurementLogsEntityManager->persist($zone);
        }
    }

    private function resolvePeriodStart(?string $fromOption, \DateTimeZone $timezone): \DateTime {
        if ($fromOption) {
            return new \DateTime($fromOption, new \DateTimeZone('UTC'));
        }

        $nowUtc = new \DateTime('@' . $this->timeProvider->getTimestamp());
        $nowUtc->setTimezone(new \DateTimeZone('UTC'));
        $localStart = clone $nowUtc;
        $localStart->setTimezone($timezone);
        $localStart->setTime(0, 0, 0);
        $localStart->sub(new \DateInterval('P' . self::DEFAULT_MONTHS_BACK . 'M'));
        $localStart->setTimezone(new \DateTimeZone('UTC'));
        return $localStart;
    }

    private function findEarliestTariffAssignmentStart(EnergyTariff $tariff): ?\DateTime {
        $assignment = $this->measurementLogsEntityManager->getRepository(EnergyTariffAssignment::class)->findOneBy(
            ['tariff' => $tariff],
            ['validFrom' => 'ASC']
        );

        return $assignment?->getValidFrom();
    }

    /**
     * @return EnergyTariffResolvedZone[]
     */
    private function buildResolvedZones(
        EnergyTariff $tariff,
        \DateTime $periodStart,
        \DateTime $periodEnd,
        \DateTimeZone $timezone
    ): array {
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
            foreach ($this->buildIntervalsForDay($tariff->getConfig(), $localCursor, $periodStart, $periodEnd, $timezone, $holidayDates) as $interval) {
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
                'zone' => $winner['zone'],
                'start' => new \DateTime('@' . $segmentStartTs),
                'end' => new \DateTime('@' . $segmentEndTs),
            ];
            $intervals[array_key_last($intervals)]['start']->setTimezone(new \DateTimeZone('UTC'));
            $intervals[array_key_last($intervals)]['end']->setTimezone(new \DateTimeZone('UTC'));
        }

        $merged = [];
        foreach ($intervals as $interval) {
            if (!$merged) {
                $merged[] = $interval;
                continue;
            }

            $lastIndex = count($merged) - 1;
            if ($merged[$lastIndex]['zone'] === $interval['zone'] && $merged[$lastIndex]['end'] == $interval['start']) {
                $merged[$lastIndex]['end'] = $interval['end'];
            } else {
                $merged[] = $interval;
            }
        }

        return array_map(
            fn(array $interval) => new EnergyTariffResolvedZone($tariff->getId(), $interval['zone'], $interval['start'], $interval['end']),
            $merged
        );
    }

    /**
     * @return array<int, array{zone: string, start: \DateTime, end: \DateTime}>
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
            if (!$winner
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

    protected function getIntervalInMinutes(): int {
        return 24 * 60;
    }
}
