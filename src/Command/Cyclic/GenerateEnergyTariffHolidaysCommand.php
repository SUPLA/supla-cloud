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
use App\Model\TimeProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class GenerateEnergyTariffHolidaysCommand extends AbstractCyclicCommand implements InitializationCommand {
    private const DEFAULT_YEARS_AHEAD = 2;
    private const POLISH_FIXED_HOLIDAYS = ['01-01', '01-06', '05-01', '05-03', '08-15', '11-01', '11-11', '12-25', '12-26'];

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
            ->setName('supla:cyclic:generate-energy-tariff-holidays')
            ->setDescription('Generates holidays per timezone for energy tariffs.')
            ->addOption('years-ahead', null, InputOption::VALUE_REQUIRED, 'How many years ahead should be generated.', self::DEFAULT_YEARS_AHEAD)
            ->addOption('timezone', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Generate holidays only for selected timezones.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $lock = $this->lockFactory->createLock('supla-generate-energy-tariff-holidays');
        if (!$lock->acquire()) {
            $output->writeln('The command is already running.');
            return self::SUCCESS;
        }

        try {
            $yearsAhead = max(1, (int)$input->getOption('years-ahead'));
            $timezones = $input->getOption('timezone') ?: $this->extractTariffTimezones();
            $currentYear = (int)(new \DateTime('@' . $this->timeProvider->getTimestamp()))->setTimezone(new \DateTimeZone('UTC'))->format('Y');
            foreach (array_unique(array_filter($timezones)) as $timezone) {
                $this->generateForTimezone((string)$timezone, $currentYear, $yearsAhead, $output);
                $this->measurementLogsEntityManager->flush();
                $this->measurementLogsEntityManager->clear();
            }
        } finally {
            $lock->release();
        }

        return self::SUCCESS;
    }

    private function extractTariffTimezones(): array {
        $timezones = [];
        /** @var EnergyTariff[] $tariffs */
        $tariffs = $this->measurementLogsEntityManager->getRepository(EnergyTariff::class)->findAll();
        foreach ($tariffs as $tariff) {
            $timezones[] = $tariff->getConfig()['timezone'] ?? 'UTC';
        }
        return $timezones;
    }

    private function generateForTimezone(string $timezone, int $currentYear, int $yearsAhead, OutputInterface $output): void {
        $provider = $this->resolveHolidayProvider($timezone);
        if (!$provider) {
            if ($output->isVerbose()) {
                $output->writeln(sprintf('Skipping unsupported timezone %s', $timezone));
            }
            return;
        }

        $startDate = new \DateTimeImmutable(sprintf('%d-01-01', $currentYear));
        $endDate = new \DateTimeImmutable(sprintf('%d-01-01', $currentYear + $yearsAhead + 1));

        $this->measurementLogsEntityManager->createQueryBuilder()
            ->delete(EnergyTariffHoliday::class, 'h')
            ->where('h.timezone = :timezone')
            ->andWhere('h.date >= :startDate')
            ->andWhere('h.date < :endDate')
            ->setParameter('timezone', $timezone)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->execute();

        for ($year = $currentYear; $year <= $currentYear + $yearsAhead; $year++) {
            foreach ($provider($year) as $holidayDate) {
                $this->measurementLogsEntityManager->persist(new EnergyTariffHoliday($timezone, $holidayDate));
            }
        }
    }

    private function resolveHolidayProvider(string $timezone): ?callable {
        return match ($timezone) {
            'Europe/Warsaw' => fn(int $year) => $this->buildPolishHolidaysForYear($year),
            default => null,
        };
    }

    /**
     * @return \DateTimeImmutable[]
     */
    private function buildPolishHolidaysForYear(int $year): array {
        $holidays = array_map(
            fn(string $monthDay) => new \DateTimeImmutable(sprintf('%d-%s', $year, $monthDay)),
            self::POLISH_FIXED_HOLIDAYS
        );

        $easterSunday = (new \DateTimeImmutable(sprintf('%d-03-21', $year)))->modify('+' . easter_days($year) . ' days');

        $holidays[] = $easterSunday;
        $holidays[] = $easterSunday->modify('+1 day');
        $holidays[] = $easterSunday->modify('+60 days');

        usort($holidays, fn(\DateTimeImmutable $a, \DateTimeImmutable $b) => $a <=> $b);
        return $holidays;
    }

    protected function getIntervalInMinutes(): int {
        return 1010;
    }
}
