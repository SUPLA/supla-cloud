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

use SuplaBundle\Entity\Main\ValueBasedTrigger;

class ValueBasedTriggerSerializer extends AbstractSerializer {
    /**
     * @param ValueBasedTrigger $vbt
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $vbt, array $context) {
        if ($vbt->hasSubject()) {
            $normalized['subjectType'] = $vbt->getSubjectType()->getValue();
            $normalized['subjectId'] = $vbt->getSubject()->getId();
            $normalized['functionId'] = $vbt->getSubject()->getFunction()->getId();
            $normalized['actionId'] = $vbt->getAction()->getId();
        }
        $normalized['trigger'] = $vbt->getTrigger();
        $normalized['owningChannelId'] = $vbt->getOwningChannel()->getId();
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof ValueBasedTrigger;
    }
}
