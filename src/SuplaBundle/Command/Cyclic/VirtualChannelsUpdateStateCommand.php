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

use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Model\VirtualChannel\VirtualChannelStateUpdater;
use SuplaBundle\Repository\IODeviceChannelRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VirtualChannelsUpdateStateCommand extends AbstractCyclicCommand {
    use Transactional;

    public function __construct(private VirtualChannelStateUpdater $updater, private IODeviceChannelRepository $channelRepository) {
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setHidden(true)
            ->setName('supla:cyclic:update-virtual-channels-state')
            ->setDescription('Updates states of virtual channels.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $channels = $this->channelRepository->findBy(['type' => ChannelType::VIRTUAL]);
        $this->updater->updateChannels($channels);
        return 0;
    }

    public function getIntervalInMinutes(): int {
        return 2; // every two minutes
    }
}
