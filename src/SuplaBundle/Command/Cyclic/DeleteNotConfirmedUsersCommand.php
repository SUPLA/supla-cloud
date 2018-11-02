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

use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteNotConfirmedUsersCommand extends AbstractCyclicCommand {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:not-confirmed-users')
            ->setDescription('Delete users that did not confirmed their accounts within the last 24 hours.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $now = new \DateTime(null, new \DateTimeZone("UTC"));
        $now->sub(new \DateInterval('PT24H'));

        $qb = $this->userRepository
            ->createQueryBuilder('t')
            ->delete()
            ->where('t.enabled = ?1 AND t.token != ?2 AND t.regDate < ?3')
            ->setParameters([1 => 0, 2 => '', 3 => $now->format('Y-m-d')]);

        $result = $qb->getQuery()->execute();
        $output->writeln(sprintf('Removed <info>%d</info> items from <comment>Users</comment> storage.', $result));
    }

    public function getIntervalInMinutes(): int {
        return 720; // every twelve hours
    }
}
