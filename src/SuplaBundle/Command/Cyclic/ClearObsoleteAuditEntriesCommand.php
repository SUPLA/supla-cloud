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

use DateInterval;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\AuditEntryRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearObsoleteAuditEntriesCommand extends AbstractCyclicCommand {
    /** @var AuditEntryRepository */
    private $auditEntryRepository;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var int */
    private $deleteOlderThanDays;
    /** @var array */
    private $deleteOlderThanDaysCustom;

    public function __construct(
        AuditEntryRepository $auditEntryRepository,
        TimeProvider $timeProvider,
        int $deleteOlderThanDays,
        array $deleteOlderThanDaysCustom
    ) {
        parent::__construct();
        $this->auditEntryRepository = $auditEntryRepository;
        $this->timeProvider = $timeProvider;
        $this->deleteOlderThanDays = $deleteOlderThanDays;
        $this->deleteOlderThanDaysCustom = $deleteOlderThanDaysCustom;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:audit-entries')
            ->setDescription('Clear audit entries older than 60 days.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $now = $this->timeProvider->getDateTime();
        $now->sub(new DateInterval("P{$this->deleteOlderThanDays}D"));

        $customRules = [];
        foreach ($this->deleteOlderThanDaysCustom as $customEventName => $customDays) {
            if (AuditedEvent::isValidKey($customEventName)) {
                $customRules[AuditedEvent::$customEventName()->getValue()] = $customDays;
            } else {
                $output->writeln("<error>Configured event name with custom clear rule does not exist: $customEventName</error>");
            }
        }

        $deletedRows = $this->auditEntryRepository
            ->createQueryBuilder('ae')
            ->delete()
            ->where('ae.createdAt < :date')
            ->andWhere('ae.event NOT IN(:customEvents)')
            ->setParameters(['date' => $now->format('Y-m-d'), 'customEvents' => $customRules ? array_keys($customRules) : [0]])
            ->getQuery()
            ->execute();

        if ($output->isVerbose()) {
            $output->writeln('Deleted obsolete audit entires: ' . $deletedRows);
        }

        foreach ($customRules as $eventId => $days) {
            $now = $this->timeProvider->getDateTime();
            $now->sub(new DateInterval("P{$days}D"));
            $deletedRows = $this->auditEntryRepository
                ->createQueryBuilder('ae')
                ->delete()
                ->where('ae.createdAt < :date')
                ->andWhere('ae.event = :eventId')
                ->setParameters(['date' => $now->format('Y-m-d'), 'eventId' => $eventId])
                ->getQuery()
                ->execute();
            if ($output->isVerbose()) {
                $type = (new AuditedEvent($eventId))->getKey();
                $output->writeln("Deleted obsolete audit entries of type $type: " . $deletedRows);
            }
        }
        return 0;
    }

    public function getIntervalInMinutes(): int {
        return 720; // twice a day
    }
}
