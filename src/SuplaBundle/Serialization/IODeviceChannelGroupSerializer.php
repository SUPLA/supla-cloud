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

use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class IODeviceChannelGroupSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function __construct(ChannelStateGetter $channelStateGetter) {
        $this->channelStateGetter = $channelStateGetter;
    }

    /**
     * @param IODeviceChannelGroup $group
     * @inheritdoc
     */
    public function normalize($group, $format = null, array $context = []) {
        $fetchChannels = in_array('channels', $context[self::GROUPS]);
        $fetchState = in_array('state', $context[self::GROUPS]);
        // this prevents from fetching IODevice's channels or their recursively
        $context[self::GROUPS] = array_diff($context[self::GROUPS], ['state', 'channels']);
        $normalized = parent::normalize($group, $format, $context);
        $normalized['locationId'] = $group->getLocation()->getId();
        $normalized['channelsIds'] = $this->toIds($group->getChannels());
        $normalized['functionId'] = $group->getFunction()->getId();
        $normalized['userIconId'] = $group->getUserIcon() ? $group->getUserIcon()->getId() : null;
        if ($fetchChannels) {
            $normalized['channels'] = $this->normalizer->normalize($group->getChannels(), $format, $context);
        }
        if ($fetchState) {
            $normalized['state'] = $this->emptyArrayAsObject($this->channelStateGetter->getStateForChannelGroup($group));
        }
        if (in_array('relationsCount', $context[self::GROUPS])) {
            $normalized['relationsCount'] = [
                'directLinks' => $group->getDirectLinks()->count(),
                'schedules' => $group->getSchedules()->count(),
            ];
        }
        return $normalized;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODeviceChannelGroup;
    }
}
