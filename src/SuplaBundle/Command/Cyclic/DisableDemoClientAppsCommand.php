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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Repository\ClientAppRepository;
use SuplaBundle\Supla\SuplaServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableDemoClientAppsCommand extends AbstractCyclicCommand {
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var ClientAppRepository */
    private $clientAppRepository;
    /** @var SuplaServer */
    private $suplaServer;

    public function __construct(EntityManagerInterface $entityManager, ClientAppRepository $clientAppRepository, SuplaServer $suplaServer) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->clientAppRepository = $clientAppRepository;
        $this->suplaServer = $suplaServer;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:disable-demo-client-apps')
            ->setDescription('Disable demonstration client apps.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $criteria = new Criteria();
        $criteria
            ->where($criteria->expr()->eq('enabled', true))
            ->andWhere($criteria->expr()->lte('disableAfterDate', $now));
        $clients = $this->clientAppRepository->matching($criteria);
        $output->writeln('Clients to disable: ' . count($clients));
        foreach ($clients as $clientApp) {
            $output->writeln($clientApp->getId());
            $this->suplaServer->clientReconnect($clientApp);
            $clientApp->setEnabled(false);
            $this->entityManager->persist($clientApp);
            $this->entityManager->flush();
        }
        return 0;
    }

    protected function getIntervalInMinutes(): int {
        return 1; // every minute
    }
}
