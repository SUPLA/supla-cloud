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

namespace SuplaBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Model\Schedule\ScheduleManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSchedulesExecutionsCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('supla:generate-schedules-executions')
            ->setDescription('Generates executions for schedules that needs regeneration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->generateFutureExecutions($output);
    }

    private function generateFutureExecutions(OutputInterface $output) {
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var ScheduleManager $scheduleManager */
        $scheduleRepo = $doctrine->getRepository('SuplaBundle:Schedule');
        $scheduleManager = $this->getContainer()->get('schedule_manager');
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
            ->where($criteria->expr()->eq('enabled', true))
            ->andWhere($criteria->expr()->lte('nextCalculationDate', $now));
        $schedules = $scheduleRepo->matching($criteria);
        $output->writeln('Schedules to regenerate: ' . count($schedules));
        $expired = 0;
        foreach ($schedules as $schedule) {
            $output->writeln($schedule->getId());
            $scheduleManager->generateScheduledExecutions($schedule);
            $expired += $this->clearOldExecutions($schedule);
        }
        $output->writeln('Deleted expired scheduled executions: ' . $expired);
    }

    private function clearOldExecutions(Schedule $schedule): int {
        /** @var ScheduledExecution $oldestExecutionToLeave */
        $oldestExecutionToLeave = current($this->getContainer()->get('schedule_manager')->findClosestExecutions($schedule)['past']);
        if ($oldestExecutionToLeave) {
            $doctrine = $this->getContainer()->get('doctrine');
            return $doctrine->getManager()->createQueryBuilder()
                ->delete('SuplaBundle:ScheduledExecution', 's')
                ->where('s.schedule = :schedule')
                ->andWhere('s.resultTimestamp < :expirationDate')
                ->setParameter('schedule', $schedule)
                ->setParameter('expirationDate', $oldestExecutionToLeave->getPlannedTimestamp())
                ->getQuery()
                ->execute();
        }
        return 0;
    }
}
