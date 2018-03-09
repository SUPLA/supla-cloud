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

namespace SuplaApiBundle\Serialization;

use SuplaBundle\Entity\Location;

class LocationSerializer extends AbstractSerializer {
    /**
     * @param Location $location
     * @inheritdoc
     */
    public function normalize($location, $format = null, array $context = []) {
        $normalized = parent::normalize($location, $format, $context);
        $normalized['iodeviceIds'] = $this->toIds($location->getIoDevices());
        $normalized['channelGroupsIds'] = $this->toIds($location->getChannelGroups());
        $normalized['channelsIds'] = $this->toIds($location->getChannels());
        $normalized['accessIdsIds'] = $this->toIds($location->getAccessIds());
        return $normalized;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof Location;
    }
}
