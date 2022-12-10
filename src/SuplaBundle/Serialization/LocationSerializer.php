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

use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Repository\LocationRepository;

class LocationSerializer extends AbstractSerializer {
    /** @var LocationRepository */
    private $locationRepository;

    public function __construct(LocationRepository $locationRepository) {
        parent::__construct();
        $this->locationRepository = $locationRepository;
    }

    /**
     * @param Location $location
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $location, array $context) {
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
            if (!isset($normalized['relationsCount']) && $this->isSerializationGroupRequested('location.relationsCount', $context)) {
                $normalized['relationsCount'] = $this->locationRepository->find($location->getId())->getRelationsCount();
            }
        } else {
            $normalized['iodevicesIds'] = $this->toIds($location->getIoDevices());
            $normalized['channelGroupsIds'] = $this->toIds($location->getChannelGroups());
            $normalized['channelsIds'] = $this->toIds($location->getChannels());
            $normalized['accessIdsIds'] = $this->toIds($location->getAccessIds());
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof Location;
    }
}
