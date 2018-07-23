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

namespace SuplaBundle\Command;

use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\TokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SuplaBundle\Supla\SuplaServerAware;

class MaintenanceCommand extends ContainerAwareCommand {
    use SuplaServerAware;

    protected function configure() {
        $this
            ->setName('supla:maintenance')
            ->setDescription('Maintenance')
            ->addArgument(
                'period',
                InputArgument::REQUIRED,
                'Period?'
            );
    }

    protected function oauthClean($output) {

        $services = [
            'fos_oauth_server.access_token_manager' => 'Access token',
            'fos_oauth_server.refresh_token_manager' => 'Refresh token',
            'fos_oauth_server.auth_code_manager' => 'Auth code',
        ];

        foreach ($services as $service => $name) {
            /** @var $instance TokenManagerInterface */
            $instance = $this->getContainer()->get($service);
            if ($instance instanceof TokenManagerInterface || $instance instanceof AuthCodeManagerInterface) {
                $result = $instance->deleteExpired();
                $output->writeln(sprintf('Removed <info>%d</info> items from <comment>%s</comment> storage.', $result, $name));
            }
        }
    }

    protected function usersClean($em, $output) {

        $rep = $em->getRepository('SuplaBundle:User');
        
        $now = new \DateTime(null, new \DateTimeZone("UTC"));
        $now->sub(new \DateInterval('PT24H'));

        $qb = $rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.enabled = ?1 AND t.token != ?2 AND t.regDate < ?3')
            ->setParameters([1 => 0, 2 => '', 3 => $now->format('Y-m-d')]);

        $result = $qb->getQuery()->execute();
        $output->writeln(sprintf('Removed <info>%d</info> items from <comment>Users</comment> storage.', $result));
    }
    
    protected function regDatesClean($em, $scope, $output) {
        
        $field = $scope == 'client' ? 'clientsRegistrationEnabled' : 'ioDevicesRegistrationEnabled';
        $now = new \DateTime(null, new \DateTimeZone("UTC"));
        
        $rep = $em->getRepository('SuplaBundle:User');
        
        $qb = $rep->createQueryBuilder('t');
        $qb
        ->update('SuplaBundle:User', 'u')
        ->set('u.'.$field, '?1')
        ->where('u.'.$field.' IS NOT NULL AND u.'.$field.' < ?2')
        ->setParameters([1 => null, 2 => $now]);

        $result = $qb->getQuery()->execute();
        $output->writeln(sprintf(
            ($scope == 'client' ? 'Client' : 'I/O Device').' registration expiration date - cleared: <info>%d</info>',
            $result
        ));
    }

    protected function logClean($em, $output, $entity, $name) {

        $sql = "DELETE t FROM `" . $em->getClassMetadata($entity)->getTableName()
            . "` AS t LEFT JOIN supla_dev_channel AS c ON c.id = t.channel_id WHERE c.id IS NULL";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $output->writeln(sprintf('Removed <info>%d</info> items from <comment>%s</comment> storage.', $stmt->rowCount(), $name));
    }

    protected function disableClients($em, OutputInterface $output) {
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var SuplaServer $suplaServer */
        $clientRepo = $doctrine->getRepository('SuplaBundle:ClientApp');
        
        if (!$this->suplaServer) {
            $this->suplaServer = $this->getContainer()->get('user_manager');
        }
    
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
        ->where($criteria->expr()->eq('enabled', true))
        ->andWhere($criteria->expr()->lte('disableAfterDate', $now));
        $clients = $clientRepo->matching($criteria);

        $output->writeln('Clients to disable: ' . count($clients));
        
        if ($this->suplaServer == null) {
            $this->suplaServer= $this->getContainer()->get('SuplaServer');
        }
        
        foreach ($clients as $clientApp) {
            $output->writeln($clientApp->getId());
            $this->suplaServer->clientReconnect($clientApp);
            $clientApp->setEnabled(false);
            $em->persist($clientApp);
            $em->flush();
        }
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getManager();

        switch ($input->getArgument('period')) {
            case 'min':
                $this->oauthClean($output);
                $this->regDatesClean($em, 'client', $output);
                $this->regDatesClean($em, 'iodevice', $output);
                $this->disableClients($em, $output);
                break;
            case 'hour':
                break;
            case 'day':
                $this->usersClean($em, $output);
                $this->logClean($em, $output, 'SuplaBundle:TemperatureLogItem', 'TemperatureLog');
                $this->logClean($em, $output, 'SuplaBundle:TempHumidityLogItem', 'TempHumidityLog');
                $this->logClean($em, $output, 'SuplaBundle:ElectricityMeterLogItem', 'ElectricityMeterLogItem');
                break;
        }
    }
}
