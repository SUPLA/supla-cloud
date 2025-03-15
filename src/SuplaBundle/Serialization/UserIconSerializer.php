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

use SuplaBundle\Entity\Main\UserIcon;
use SuplaBundle\Model\ApiVersions;

class UserIconSerializer extends AbstractSerializer {
    /**
     * @param \SuplaBundle\Entity\Main\UserIcon $icon
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $icon, array $context) {
        $normalized['functionId'] = $icon->getFunction()->getId();
        if ($this->isSerializationGroupRequested('images', $context)) {
            $normalized['images'] = array_map('base64_encode', $icon->getImages());
            $normalized['imagesDark'] = array_map('base64_encode', $icon->getImagesDark());
        }
        if (ApiVersions::V3()->isRequestedEqualOrGreaterThan($context)) {
            if (isset($normalized['function'])) {
                unset($normalized['function']);
            }
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof UserIcon;
    }
}
