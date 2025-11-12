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

namespace SuplaBundle\Serialization;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Entity\Main\ValueBasedTrigger;

class PushNotificationSerializer extends AbstractSerializer {
    /**
     * @param PushNotification $notification
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $notification, array $context) {
        $subject = $notification->getSubject();
        if ($subject instanceof ValueBasedTrigger) {
            $normalized['subjectId'] = $subject->getId();
            $normalized['subjectType'] = 'reaction';
        } elseif ($subject instanceof ActionableSubject) {
            $normalized['subjectId'] = $subject->getId();
            $normalized['subjectType'] = $subject->getOwnSubjectType();
        } elseif ($subject instanceof IODevice) {
            $normalized['subjectId'] = $subject->getId();
            $normalized['subjectType'] = 'device';
        }
        $normalized['accessIdsIds'] = $notification->getAccessIds()->map(fn(AccessId $accessId) => $accessId->getId())->toArray();
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof PushNotification;
    }
}
