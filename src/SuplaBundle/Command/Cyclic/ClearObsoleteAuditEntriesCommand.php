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

    public function __construct(AuditEntryRepository $auditEntryRepository, TimeProvider $timeProvider, int $deleteOlderThanDays) {
        parent::__construct();
        $this->auditEntryRepository = $auditEntryRepository;
        $this->timeProvider = $timeProvider;
        $this->deleteOlderThanDays = $deleteOlderThanDays;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:audit-entries')
            ->setDescription('Clear audit entries older than 60 days.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $now = $this->timeProvider->getDateTime();
        $now->sub(new \DateInterval("P{$this->deleteOlderThanDays}D"));

        $deletedRows = $this->auditEntryRepository
            ->createQueryBuilder('ae')
            ->delete()
            ->where('ae.createdAt < :date')
            ->setParameters(['date' => $now->format('Y-m-d')])
            ->getQuery()
            ->execute();

        $output->writeln('Deleted obsolete audit entires: ' . $deletedRows);
    }

    public function getIntervalInMinutes(): int {
        return 720; // twice a day
    }
}
