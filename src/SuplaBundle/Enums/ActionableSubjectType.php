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
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Exception\ApiException;

/**
 * @OA\Schema(schema="ActionableSubjectTypeNames", type="string", enum={"channel","channelGroup","scene","schedule","notification","other"})
 * @OA\Schema(schema="ActionableSubject", oneOf={
 *    @OA\Schema(ref="#/components/schemas/Channel"),
 *    @OA\Schema(ref="#/components/schemas/ChannelGroup"),
 *    @OA\Schema(ref="#/components/schemas/Scene"),
 *    @OA\Schema(ref="#/components/schemas/Schedule"),
 * })
 *
 * @method static ActionableSubjectType CHANNEL()
 * @method static ActionableSubjectType CHANNEL_GROUP()
 * @method static ActionableSubjectType SCENE()
 * @method static ActionableSubjectType SCHEDULE()
 * @method static ActionableSubjectType NOTIFICATION()
 * @method static ActionableSubjectType OTHER()
 */
final class ActionableSubjectType extends Enum {
    const CHANNEL = 'channel'; // i18n:['actionableSubjectType_channel']
    const CHANNEL_GROUP = 'channelGroup'; // i18n:['actionableSubjectType_channelGroup']
    const SCENE = 'scene'; // i18n:['actionableSubjectType_scene']
    const SCHEDULE = 'schedule'; // i18n:['actionableSubjectType_schedule']
    const NOTIFICATION = 'notification'; // i18n:['actionableSubjectType_notification']
    const OTHER = 'other';

    public static function forEntity(ActionableSubject $subject): self {
        if (self::isValid($subject->getOwnSubjectType())) {
            return new self($subject->getOwnSubjectType());
        } else {
            throw new \InvalidArgumentException('Invalid entity given: ' . get_class($subject));
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
