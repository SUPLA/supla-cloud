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

use Assert\Assertion;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteBrokerCloudsCacheCommand extends AbstractCyclicCommand {
    protected function configure() {
        $this
            ->setName('supla:clean:broker-clouds-cache')
            ->setDescription('Delete broker clouds list cache file.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (file_exists(SuplaAutodiscover::BROKER_CLOUDS_SAVE_PATH)) {
            $success = unlink(SuplaAutodiscover::BROKER_CLOUDS_SAVE_PATH);
            Assertion::true($success, 'Could not delete cache file that contains broker clouds list.');
        }
        return 0;
    }

    protected function getIntervalInMinutes(): int {
        return 360; // every 6 hours
    }
}
