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

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Model\TimeProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableBrokenSchedulesCommand extends Command implements CyclicCommand {
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:disable-broken-schedules')
            ->setDescription('Disable schedules that did not run successfully over the last month.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $schedulesTableName = $this->entityManager->getClassMetadata(Schedule::class)->getTableName();
        $scheduleExecutionsTableName = $this->entityManager->getClassMetadata(ScheduledExecution::class)->getTableName();
        $query = <<<QUERY
UPDATE `$schedulesTableName` SET enabled = 0 WHERE id IN(
  SELECT id FROM (
    SELECT id, 
    (SELECT COUNT(*) FROM `$scheduleExecutionsTableName` 
        WHERE schedule_id = s.id AND result=0 AND planned_timestamp > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) successful,
    (SELECT COUNT(*) FROM `$scheduleExecutionsTableName` 
        WHERE schedule_id = s.id AND result!=0 AND planned_timestamp > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) failed
	FROM `$schedulesTableName` s
	WHERE enabled = 1
  ) AS schedules_to_disable
)
QUERY;
        $stmt = $this->entityManager->getConnection()->prepare($query);
        $stmt->execute();
        $output->writeln(sprintf('Disabled <info>%d</info> schedules due to failed executions.', $stmt->rowCount()));
    }

    public function shouldRunNow(TimeProvider $timeProvider): bool {
        return date('H:i', $timeProvider->getTimestamp()) === '03:20';
    }
}
