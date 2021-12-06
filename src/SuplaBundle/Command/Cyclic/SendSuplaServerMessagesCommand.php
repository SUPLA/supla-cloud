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
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Message\EmailFromTemplateAsync;
use SuplaBundle\Message\UserOptOutNotifications;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendSuplaServerMessagesCommand extends AbstractCyclicCommand {
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus, EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    protected function configure() {
        $this
            ->setHidden(true)
            ->setName('supla:cyclic:send-server-messages')
            ->setDescription('Sends notifications set up by supla-server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $messagesQuery = $this->entityManager->getConnection()
            ->executeQuery('SELECT id, body FROM messenger_messages WHERE queue_name = "supla-server" LIMIT 100');
        while ($suplaServerMessage = $messagesQuery->fetchAssociative()) {
            $decodedBody = json_decode($suplaServerMessage['body'], true);
            $type = $decodedBody['type'] ?? 'email';
            $template = $decodedBody['template'] ?? null;
            $userId = $decodedBody['userId'] ?? null;
            $data = $decodedBody['data'] ?? [];
            if (!$template || !$userId) {
                $output->writeln('<error>No template or user id in the message! Do not sending this supla-server message.</error>');
                $output->writeln($suplaServerMessage['body']);
            } elseif ($type === 'email') {
                if ($template == UserOptOutNotifications::NEW_IO_DEVICE) {
                    $ioDevice = $this->entityManager->find(IODevice::class, $data['ioDeviceId'] ?? 0);
                    if (!$ioDevice || $ioDevice->getUser()->getId() !== $userId) {
                        $userId = null;
                    }
                    $data = [
                        'device' => [
                            'id' => $ioDevice->getId(),
                            'name' => $ioDevice->getName(),
                            'softwareVersion' => $ioDevice->getSoftwareVersion(),
                            'regIp' => $ioDevice->getRegIpv4(),
                        ],
                    ];
                }
                if ($userId) {
                    $this->messageBus->dispatch(new EmailFromTemplateAsync($template, $userId, $data));
                }
            } else {
                $output->writeln('<error>Invalid message type.</error>');
                $output->writeln($suplaServerMessage['body']);
            }
            $this->entityManager->getConnection()
                ->executeQuery('DELETE FROM messenger_messages WHERE id = :id', ['id' => $suplaServerMessage['id']]);
            if ($output->isVerbose() || $output->isVeryVerbose()) {
                $output->writeln("<info>Dispatched supla server message ($template to user ID $userId)</info>");
            }
        }
    }

    public function getIntervalInMinutes(): int {
        return 1; // every minute
    }
}
