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

namespace SuplaBundle\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

class SuplaMailer {
    private const DEFAULT_FROM_NAME = 'SUPLA';

    public function __construct(
        private TransportInterface $transport,
        private ?string $mailerFrom,
        private LoggerInterface $logger,
    ) {
    }

    public function send(Email $message): ?SentMessage {
        $message->from($this->mailerFrom);
        $sentMessage = $this->transport->send($message);
        if ($sentMessage) {
            $this->logger->notice('E-mail sent.', [
                'recipient' => $message->getTo()[0]->getAddress(),
                'subject' => $message->getSubject(),
            ]);
            $this->logger->debug('E-mail sent (debug).', [
                'recipient' => $message->getTo()[0]->getAddress(),
                'subject' => $message->getSubject(),
                'debug' => $sentMessage->getDebug(),
            ]);
        } else {
            $this->logger->error('Unable to send email.', [
                'recipient' => $message->getTo()[0]->getAddress(),
                'subject' => $message->getSubject(),
            ]);
        }
        return $sentMessage;
    }

    private function parseFrom(): array {
        if (preg_match('#(.+)<(.+)>#', $this->mailerFrom, $match)) {
            return [trim($match[2]), trim($match[1]) ?: self::DEFAULT_FROM_NAME];
        } else {
            return [$this->mailerFrom, self::DEFAULT_FROM_NAME];
        }
    }
}
