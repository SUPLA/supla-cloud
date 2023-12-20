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
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\ScheduledExecution;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\ScheduleActionExecutionResult;
use SuplaBundle\Model\Audit\Audit;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\ScheduleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableBrokenSchedulesCommand extends Command implements CyclicCommand {
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var ScheduleRepository */
    private $scheduleRepository;
    /** @var Audit */
    private $audit;

    public function __construct(
        EntityManagerInterface $entityManager,
        ScheduleManager $scheduleManager,
        ScheduleRepository $scheduleRepository,
        Audit $audit
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->scheduleManager = $scheduleManager;
        $this->scheduleRepository = $scheduleRepository;
        $this->audit = $audit;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:disable-broken-schedules')
            ->setDescription('Disable schedules that did not run successfully over the last month.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $schedulesTableName = $this->entityManager->getClassMetadata(Schedule::class)->getTableName();
        $scheduleExecutionsTableName = $this->entityManager->getClassMetadata(ScheduledExecution::class)->getTableName();
        $successfulResultsIds = implode(',', EntityUtils::mapToIds(
            array_filter(
                ScheduleActionExecutionResult::values(),
                function (ScheduleActionExecutionResult $result) {
                    return $result->isSuccessful();
                }
            )
        ));
        $query = <<<QUERY
    SELECT id, 
    (SELECT COUNT(*) FROM `$scheduleExecutionsTableName` 
        WHERE schedule_id = s.id AND result IS NOT NULL AND result IN($successfulResultsIds) 
              AND planned_timestamp > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) successful,
    (SELECT COUNT(*) FROM `$scheduleExecutionsTableName` 
        WHERE schedule_id = s.id AND result IS NOT NULL AND result NOT IN($successfulResultsIds) 
              AND planned_timestamp > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) failed
	FROM `$schedulesTableName` s
	WHERE enabled = 1 
	HAVING successful = 0 AND failed > 10
QUERY;
        $stmt = $this->entityManager->getConnection()->prepare($query);
        $result = $stmt->executeQuery();
        $disabledCount = 0;
        while ($scheduleToDisable = $result->fetchAssociative()) {
            /** @var Schedule $schedule */
            $schedule = $this->scheduleRepository->find($scheduleToDisable['id']);
            $this->scheduleManager->disable($schedule);
            $this->audit->newEntry(AuditedEvent::SCHEDULE_BROKEN_DISABLED())
                ->setIntParam($schedule->getId())
                ->setUser($schedule->getUser())
                ->buildAndFlush();
            ++$disabledCount;
        }
        $output->writeln(sprintf('Disabled <info>%d</info> schedules due to failed executions.', $disabledCount));
        return 0;
    }

    public function shouldRunNow(TimeProvider $timeProvider): bool {
        return date('H:i', $timeProvider->getTimestamp()) === '03:20';
    }
}
