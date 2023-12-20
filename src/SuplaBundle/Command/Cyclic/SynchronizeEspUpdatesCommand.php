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
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SynchronizeEspUpdatesCommand extends AbstractCyclicCommand {
    /** @var SuplaAutodiscover */
    private $suplaAutodiscover;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(SuplaAutodiscover $suplaAutodiscover, EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->suplaAutodiscover = $suplaAutodiscover;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setHidden(true)
            ->setName('supla:esp:synchronize-updates')
            ->setDescription('Fetches updates from AD and synchronizes them in this instance.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $updates = $this->suplaAutodiscover->getEspUpdates();
        if (is_array($updates)) {
            $this->deleteAllSyncedUpdates();
            foreach ($updates as $update) {
                $this->insertUpdate($update);
            }
            if ($input->isInteractive()) {
                $io->writeln('The number of existing synchronized ESP Updates: ' . count($updates));
                if (count($updates) && $io->isVerbose()) {
                    $io->newLine();
                    $io->table(array_keys($updates[0]), $updates);
                }
            }
        } else {
            $io->error('Could not contact AD.');
        }
        return 0;
    }

    private function deleteAllSyncedUpdates() {
        $this->entityManager->getConnection()->executeQuery('DELETE FROM `esp_update` WHERE is_synced = 1');
    }

    private function insertUpdate(array $update) {
        $this->entityManager->getConnection()->executeQuery(
            'INSERT INTO `esp_update` (`device_id`, `device_name`, `platform`, `latest_software_version`, `fparam1`, `fparam2`, ' .
            '`fparam3`, `fparam4`, `protocols`, `host`, `port`, `path`, `is_synced`) ' .
            'VALUES (:device_id, :device_name, :platform, :latest_software_version, :fparam1, :fparam2, :fparam3, :fparam4,' .
            ':protocols, :host, :port, :path, 1);',
            $update
        );
    }

    public function getIntervalInMinutes(): int {
        return 120; // every two hours
    }
}
