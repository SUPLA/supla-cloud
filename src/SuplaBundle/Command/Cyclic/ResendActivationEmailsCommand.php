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
use Exception;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\AuditEntryRepository;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResendActivationEmailsCommand extends AbstractCyclicCommand {
    /** @var UserRepository */
    private $userRepository;
    /** @var TimeProvider */
    private $timeProvider;
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var AuditEntryRepository
     */
    private $auditEntryRepository;

    public function __construct(
        UserRepository $userRepository,
        TimeProvider $timeProvider,
        UserManager $userManager,
        AuditEntryRepository $auditEntryRepository
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->timeProvider = $timeProvider;
        $this->userManager = $userManager;
        $this->auditEntryRepository = $auditEntryRepository;
    }

    protected function configure() {
        $this
            ->setName('supla:user:resend-activation-emails')
            ->setDescription('Resend activation e-mails for users who did not succeeded in account activation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $halfHourAgo = $this->timeProvider->getDateTime();
        $halfHourAgo->sub(new DateInterval('PT30M'));
        $hourAgo = $this->timeProvider->getDateTime();
        $hourAgo->sub(new DateInterval('PT60M'));

        $qb = $this->userRepository
            ->createQueryBuilder('u')
            ->select()
            ->where('u.enabled = 0 AND u.token IS NOT NULL AND u.regDate BETWEEN :dateFrom AND :dateTo')
            ->setParameters(['dateFrom' => $hourAgo->format(DateTime::ATOM), 'dateTo' => $halfHourAgo->format(DateTime::ATOM)]);

        /** @var \SuplaBundle\Entity\Main\User[] $usersToNotify */
        $usersToNotify = $qb->getQuery()->execute();
        $output->writeln(sprintf('Users to resend activation e-mail: <info>%d</info>.', count($usersToNotify)));

        foreach ($usersToNotify as $userToNotify) {
            try {
                $sentCount = $this->auditEntryRepository->createQueryBuilder('ae')
                    ->select('count(ae.id)')
                    ->where('ae.event IN(:events)')
                    ->andWhere('ae.user = :user')
                    ->setParameter('events', [AuditedEvent::USER_ACTIVATION_EMAIL_SENT])
                    ->setParameter('user', $userToNotify)
                    ->getQuery()
                    ->getSingleScalarResult();
                if ($sentCount < 2) {
                    $this->userManager->sendConfirmationEmailMessage($userToNotify);
                }
            } catch (Exception $e) {
            }
        }
        return 0;
    }

    public function getIntervalInMinutes(): int {
        return 30; // every half hour
    }
}
