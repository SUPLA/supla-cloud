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

use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffResolvedZone;
use App\Model\TimeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class ResolveEnergyTariffsCommand extends AbstractCyclicCommand {
    private const DEFAULT_MONTHS_AHEAD = 3;
    private const SCHEMA_ZONE_PROFILE_V1 = 'supla.energy.zone_profile.v1';
    private const TARIFF_TYPE_FIXED = 'fixed';
    private const DAY_NAMES = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

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
        $localStart->setTimezone(new \DateTimeZone('UTC'));
        return $localStart;
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
        $intervals = [];
        $localCursor = clone $periodStart;
        $localCursor->setTimezone($timezone);
        $localCursor->setTime(0, 0, 0);
        $localCursor->modify('-1 day');

        $localLimit = clone $periodEnd;
        $localLimit->setTimezone($timezone);
        $localLimit->setTime(0, 0, 0);
        $localLimit->modify('+1 day');

        while ($localCursor < $localLimit) {
            foreach ($this->buildIntervalsForDay($tariff->getConfig()['rules'] ?? [], $localCursor, $periodStart, $periodEnd, $timezone) as $interval) {
                $intervals[] = $interval;
            }
            $localCursor->modify('+1 day');
        }

        usort($intervals, fn(array $a, array $b) => $a['start'] <=> $b['start']);

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
        array $rules,
        \DateTime $localDay,
        \DateTime $periodStart,
        \DateTime $periodEnd,
        \DateTimeZone $timezone
    ): array {
        $dayName = self::DAY_NAMES[(int)$localDay->format('w')];
        $intervals = [];
        foreach ($rules as $rule) {
            if (!in_array($dayName, $rule['days'] ?? [], true)) {
                continue;
            }

            foreach ($rule['time_ranges'] ?? [] as $timeRange) {
                [$fromHour, $fromMinute] = array_map('intval', explode(':', $timeRange['from']));
                [$toHour, $toMinute] = array_map('intval', explode(':', $timeRange['to']));

                $localStart = new \DateTime($localDay->format('Y-m-d H:i:s'), $timezone);
                $localStart->setTime($fromHour, $fromMinute, 0);

                $localEnd = new \DateTime($localDay->format('Y-m-d H:i:s'), $timezone);
                $localEnd->setTime($toHour, $toMinute, 0);
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
                    'start' => $startUtc,
                    'end' => $endUtc,
                ];
            }
        }

        return $intervals;
    }

    protected function getIntervalInMinutes(): int {
        return 1000;
    }
}
