<?php

namespace AppBundle\Command;

use AppBundle\Model\ScheduleManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSchedulesExecutionsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('supla:generate-schedules-executions')
            ->setDescription('Generates executions for schedules that needs regeneration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->generateFutureExecutions($output);
        $this->clearOldExecutions($output);
    }

    private function generateFutureExecutions(OutputInterface $output)
    {
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var ScheduleManager $scheduleManager */
        $scheduleRepo = $doctrine->getRepository('AppBundle:Schedule');
        $scheduleManager = $this->getContainer()->get('schedule_manager');
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
            ->where($criteria->expr()->eq('enabled', true))
            ->where($criteria->expr()->lte('nextCalculationDate', $now));
        $schedules = $scheduleRepo->matching($criteria);
        $output->writeln('Schedules to regenerate: ' . count($schedules));
        foreach ($schedules as $schedule) {
            $scheduleManager->generateScheduledExecutions($schedule);
        }
    }

    private function clearOldExecutions(OutputInterface $output)
    {
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $affectedRows = $doctrine->getManager()->createQueryBuilder()
            ->delete('AppBundle:ScheduledExecution', 's')
            ->where('s.timestamp < :expirationDate')
            ->setParameter('expirationDate', (new \DateTime())->sub(new \DateInterval('P10D')))// 10 days
            ->getQuery()
            ->execute();
        $output->writeln('Deleted expired scheduled executions: ' . $affectedRows);
    }
}
