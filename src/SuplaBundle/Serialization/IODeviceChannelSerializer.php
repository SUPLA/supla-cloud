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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\JsonArrayObject;

class IODeviceChannelSerializer extends AbstractSerializer {
    use CurrentUserAware;
    use SuplaServerAware;

    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var SubjectConfigTranslator */
    private $configTranslator;

    public function __construct(
        ChannelStateGetter $channelStateGetter,
        IODeviceChannelRepository $channelRepository,
        SubjectConfigTranslator $configTranslator
    ) {
        parent::__construct();
        $this->channelStateGetter = $channelStateGetter;
        $this->channelRepository = $channelRepository;
        $this->configTranslator = $configTranslator;
    }

    /**
     * @param \SuplaBundle\Entity\Main\IODeviceChannel $channel
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $channel, array $context) {
        $normalized['iodeviceId'] = $channel->getIoDevice()->getId();
        $normalized['locationId'] = $channel->getLocation()->getId();
        $normalized['functionId'] = $channel->getFunction()->getId();
        $normalized['userIconId'] = $channel->getUserIcon() ? $channel->getUserIcon()->getId() : null;
        $normalized['typeId'] = $channel->getType()->getId();
        if ($this->isSerializationGroupRequested('connected', $context) || $this->isSerializationGroupRequested('state', $context)) {
            $state = $this->channelStateGetter->getState($channel);
            if ($this->isSerializationGroupRequested('connected', $context)) {
                $normalized['connected'] = $state['connected'];
            }
            if ($this->isSerializationGroupRequested('state', $context)) {
                $normalized['state'] = new JsonArrayObject($state);
            }
        }
        if (!isset($normalized['relationsCount']) && (
                $this->isSerializationGroupRequested('channel.relationsCount', $context)
                || $this->isSerializationGroupRequested('subject.relationsCount', $context)
            )) {
            $normalized['relationsCount'] = $this->channelRepository->find($channel->getId())->getRelationsCount();
        }
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
            $normalized['config'] = $this->configTranslator->getPublicConfig($channel);
            if ($this->isSerializationGroupRequested('channel.actionTriggers', $context)) {
                $actionTriggers = $this->channelRepository->findActionTriggers($channel);
                $normalized['actionTriggersIds'] = EntityUtils::mapToIds($actionTriggers);
            }
        } else {
            $normalized['param1'] = $channel->getParam1();
            $normalized['param2'] = $channel->getParam2();
            $normalized['param3'] = $channel->getParam3();
            $normalized['param4'] = $channel->getParam4();
            $normalized['textParam1'] = $channel->getTextParam1();
            $normalized['textParam2'] = $channel->getTextParam2();
            $normalized['textParam3'] = $channel->getTextParam3();
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODeviceChannel;
    }
}
