<?php

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
