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

use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Utils\JsonArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class IODeviceChannelGroupSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;

    public function __construct(ChannelStateGetter $channelStateGetter, ChannelGroupRepository $channelGroupRepository) {
        parent::__construct();
        $this->channelStateGetter = $channelStateGetter;
        $this->channelGroupRepository = $channelGroupRepository;
    }

    /**
     * @param \SuplaBundle\Entity\Main\IODeviceChannelGroup $group
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $group, array $context) {
        $normalized['locationId'] = $group->getLocation()->getId();
        $normalized['functionId'] = $group->getFunction()->getId();
        $normalized['userIconId'] = $group->getUserIcon() ? $group->getUserIcon()->getId() : null;
        if ($this->isSerializationGroupRequested('state', $context)) {
            $normalized['state'] = new JsonArrayObject($this->channelStateGetter->getStateForChannelGroup($group));
        }
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
            $normalized['channelsIds'] = $this->toIds($group->getChannels());
        }
        if (!isset($normalized['relationsCount'])) {
            if ($this->isSerializationGroupRequested('channelGroup.relationsCount', $context)
                || $this->isSerializationGroupRequested('subject.relationsCount', $context)) {
                $normalized['relationsCount'] = $this->channelGroupRepository->find($group->getId())->getRelationsCount();
            }
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODeviceChannelGroup;
    }
}
