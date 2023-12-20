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
use DateTime;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteNotConfirmedUsersCommand extends AbstractCyclicCommand {
    /** @var UserRepository */
    private $userRepository;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var int */
    private $deleteOlderThanHours;

    public function __construct(UserRepository $userRepository, TimeProvider $timeProvider, int $deleteOlderThanHours) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->timeProvider = $timeProvider;
        $this->deleteOlderThanHours = $deleteOlderThanHours;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:not-confirmed-users')
            ->setDescription('Delete users that did not confirmed their accounts within the last 24 hours.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $now = $this->timeProvider->getDateTime();
        $now->sub(new DateInterval("PT{$this->deleteOlderThanHours}H"));

        $qb = $this->userRepository
            ->createQueryBuilder('u')
            ->select()
            ->where('u.enabled = 0 AND u.token IS NOT NULL AND u.regDate < :regDate')
            ->setParameters(['regDate' => $now->format(DateTime::ATOM)]);

        /** @var \SuplaBundle\Entity\Main\User[] $usersToDelete */
        $usersToDelete = $qb->getQuery()->execute();
        $output->writeln(sprintf('Users to remove: <info>%d</info>.', count($usersToDelete)));

        foreach ($usersToDelete as $userToDelete) {
            $deleteInput = new ArrayInput([
                'command' => 'supla:user:delete',
                'username' => $userToDelete->getUsername(),
                '--no-interaction' => true,
            ]);
            $code = $this->getApplication()->run($deleteInput, $output);
            if ($code !== 0) {
                $output->writeln('Could not delete user ' . $userToDelete->getUsername());
            }
        }
        return 0;
    }

    public function getIntervalInMinutes(): int {
        return 720; // every twelve hours
    }
}
