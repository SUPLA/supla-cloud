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

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Supla\SuplaServerAware;

class IODeviceChannelSerializer extends AbstractSerializer {
    use CurrentUserAware;
    use SuplaServerAware;

    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var IODeviceChannelRepository */
    private $channelRepository;

    public function __construct(ChannelStateGetter $channelStateGetter, IODeviceChannelRepository $channelRepository) {
        parent::__construct();
        $this->channelStateGetter = $channelStateGetter;
        $this->channelRepository = $channelRepository;
    }

    /**
     * @param IODeviceChannel $channel
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $channel, array $context) {
        $normalized['iodeviceId'] = $channel->getIoDevice()->getId();
        $normalized['locationId'] = $channel->getLocation()->getId();
        $normalized['functionId'] = $channel->getFunction()->getId();
        $normalized['userIconId'] = $channel->getUserIcon() ? $channel->getUserIcon()->getId() : null;
        $normalized['typeId'] = $channel->getType()->getId();
        if (in_array('connected', $context[self::GROUPS])) {
            $normalized['connected'] = $this->suplaServer->isDeviceConnected($channel->getIoDevice());
        }
        if (in_array('state', $context[self::GROUPS])) {
            $normalized['state'] = $this->emptyArrayAsObject($this->channelStateGetter->getState($channel));
        }
        if (!isset($normalized['relationsCount']) && (
                $this->isSerializationGroupRequested('channel.relationsCount', $context)
                || $this->isSerializationGroupRequested('subject.relationsCount', $context)
            )) {
            $normalized['relationsCount'] = $this->channelRepository->find($channel->getId())->getRelationsCount();
        }
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
            $normalized['subjectType'] = ActionableSubjectType::CHANNEL;
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODeviceChannel;
    }
}
