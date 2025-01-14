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

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SuplaMailer {
    private const DEFAULT_FROM_NAME = 'SUPLA';

    public function __construct(private MailerInterface $mailer, private ?string $mailerFrom) {
    }

    public function send(Email $message): bool {
        $message->from($this->mailerFrom);
        $this->mailer->send($message);
        return true;
    }

    private function parseFrom(): array {
        if (preg_match('#(.+)<(.+)>#', $this->mailerFrom, $match)) {
            return [trim($match[2]), trim($match[1]) ?: self::DEFAULT_FROM_NAME];
        } else {
            return [$this->mailerFrom, self::DEFAULT_FROM_NAME];
        }
    }
}
