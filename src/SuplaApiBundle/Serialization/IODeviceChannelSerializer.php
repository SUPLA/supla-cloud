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

use SuplaBundle\Entity\IODeviceChannel;

class IODeviceChannelSerializer extends AbstractSerializer {
    /**
     * @param IODeviceChannel $channel
     * @inheritdoc
     */
    public function normalize($channel, $format = null, array $context = []) {
        $normalized = parent::normalize($channel, $format, $context);
        if (is_array($normalized)) {
            $normalized['iodeviceId'] = $channel->getIoDevice()->getId();
            $normalized['locationId'] = $channel->getLocation()->getId();
            $normalized['functionId'] = $channel->getFunction()->getId();
            $normalized['typeId'] = $channel->getType()->getId();
        }
        return $normalized;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODeviceChannel;
    }
}
