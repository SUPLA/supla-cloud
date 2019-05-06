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
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Exception\ApiException;

/**
 * @method static ActionableSubjectType CHANNEL()
 * @method static ActionableSubjectType CHANNEL_GROUP()
 * @method static ActionableSubjectType SCENE()
 */
final class ActionableSubjectType extends Enum {
    const CHANNEL = 'channel';
    const CHANNEL_GROUP = 'channelGroup';
    const SCENE = 'scene';

    public static function forEntity($entity): self {
        if ($entity instanceof IODeviceChannel) {
            return self::CHANNEL();
        } elseif ($entity instanceof IODeviceChannelGroup) {
            return self::CHANNEL_GROUP();
        } elseif ($entity instanceof Scene) {
            return self::SCENE();
        } else {
            throw new \InvalidArgumentException('Invalid entity given: ' . get_class($entity));
        }
    }

    public static function fromString(string $subjectType): self {
        try {
            return new self($subjectType);
        } catch (\RuntimeException $e) {
            throw new ApiException('Invalid subjectType given: ' . $subjectType, 400, $e);
        }
    }
}
