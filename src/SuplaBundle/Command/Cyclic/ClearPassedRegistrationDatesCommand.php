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

use DateTime;
use DateTimeZone;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearPassedRegistrationDatesCommand extends AbstractCyclicCommand {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:passed-io-client-registration-dates')
            ->setDescription('Clear I/O or Client App registration availability dates that are in the past.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->regDatesClean('client', $output);
        $this->regDatesClean('iodevice', $output);
        return 0;
    }

    protected function regDatesClean($scope, $output) {
        $field = $scope == 'client' ? 'clientsRegistrationEnabled' : 'ioDevicesRegistrationEnabled';
        $now = new DateTime(null, new DateTimeZone("UTC"));
        $qb = $this->userRepository
            ->createQueryBuilder('t')
            ->update(User::class, 'u')
            ->set('u.' . $field, '?1')
            ->where('u.' . $field . ' IS NOT NULL AND u.' . $field . ' < ?2')
            ->setParameters([1 => null, 2 => $now]);
        $result = $qb->getQuery()->execute();
        $output->writeln(sprintf(
            ($scope == 'client' ? 'Client' : 'I/O Device') . ' registration expiration date - cleared: <info>%d</info>',
            $result
        ));
    }

    public function getIntervalInMinutes(): int {
        return 1; // every minute
    }
}
