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

namespace SuplaBundle\Enums;

use MyCLabs\Enum\Enum;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @method static AuditedEvent AUTHENTICATION_SUCCESS()
 * @method static AuditedEvent AUTHENTICATION_FAILURE()
 * @method static AuditedEvent PASSWORD_RESET()
 * @method static AuditedEvent DIRECT_LINK_EXECUTION()
 */
final class AuditedEvent extends Enum {
    const AUTHENTICATION_SUCCESS = 1;
    const AUTHENTICATION_FAILURE = 2;
    const PASSWORD_RESET = 3;
    const DIRECT_LINK_EXECUTION = 4;
    const DIRECT_LINK_EXECUTION_FAILURE = 5;

    /** @Groups({"basic"}) */
    public function getId(): int {
        return $this->value;
    }

    /** @Groups({"basic"}) */
    public function getName(): string {
        return $this->getKey();
    }
}
