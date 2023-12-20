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

namespace SuplaBundle\Command\Cyclic;

use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\ScheduledExecution;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Repository\ScheduleRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSchedulesExecutionsCommand extends AbstractCyclicCommand {
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var ScheduleRepository */
    private $scheduleRepository;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        ScheduleManager $scheduleManager,
        ScheduleRepository $scheduleRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->scheduleManager = $scheduleManager;
        $this->scheduleRepository = $scheduleRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:generate-schedules-executions')
            ->setDescription('Generates executions for schedules that needs regeneration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->generateFutureExecutions($output);
        return 0;
    }

    private function generateFutureExecutions(OutputInterface $output) {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $criteria = new Criteria();
        $criteria
            ->where($criteria->expr()->eq('enabled', true))
            ->andWhere($criteria->expr()->lte('nextCalculationDate', $now))
            ->andWhere($criteria->expr()->neq('mode', ScheduleMode::ONCE));
        $schedules = $this->scheduleRepository->matching($criteria);
        $output->writeln('Schedules to regenerate: ' . count($schedules));
        $expired = 0;
        foreach ($schedules as $schedule) {
            $output->writeln($schedule->getId());
            $this->scheduleManager->generateScheduledExecutions($schedule);
            $expired += $this->clearOldExecutions($schedule);
        }
        $output->writeln('Deleted expired scheduled executions: ' . $expired);
    }

    private function clearOldExecutions(Schedule $schedule): int {
        /** @var ScheduledExecution $oldestExecutionToLeave */
        $oldestExecutionToLeave = current($this->scheduleManager->findClosestExecutions($schedule)['past']);
        if ($oldestExecutionToLeave) {
            return $this->entityManager->createQueryBuilder()
                ->delete(ScheduledExecution::class, 's')
                ->where('s.schedule = :schedule')
                ->andWhere('s.resultTimestamp < :expirationDate')
                ->setParameter('schedule', $schedule)
                ->setParameter('expirationDate', $oldestExecutionToLeave->getPlannedTimestamp())
                ->getQuery()
                ->execute();
        }
        return 0;
    }

    public function getIntervalInMinutes(): int {
        return 1; // every one minute
    }
}
