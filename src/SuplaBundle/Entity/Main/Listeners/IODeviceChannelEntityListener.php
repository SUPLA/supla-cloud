<?php

namespace SuplaBundle\Entity\Main\Listeners;

use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelConfigChangeScope;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\PrzemekBitsBuilder;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\ArrayUtils;

class IODeviceChannelEntityListener {
    use SuplaServerAware;

    /** @ORM\PostUpdate */
    public function postUpdate(IODeviceChannel $channel, PostUpdateEventArgs $args) {
        $changeArray = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($args->getObject());
        $changes = new PrzemekBitsBuilder();
        if (isset($changeArray['caption'])) {
            $changes->add(ChannelConfigChangeScope::CAPTION);
        }
        if (isset($changeArray['hidden'])) {
            $changes->add(ChannelConfigChangeScope::VISIBILITY);
        }
        if (isset($changeArray['location'])) {
            $changes->add(ChannelConfigChangeScope::LOCATION);
        }
        if (isset($changeArray['function'])) {
            $changes->add(ChannelConfigChangeScope::CHANNEL_FUNCTION);
        }
        if (isset($changeArray['userConfig'])) {
            $before = json_decode($changeArray['userConfig'][0] ?: '[]', true);
            $after = json_decode($changeArray['userConfig'][1] ?: '[]', true);
            $changedConfigKeys = array_keys(ArrayUtils::mergeConfigs($before, $after, $before));
            $relationsChanges = array_filter($changedConfigKeys, function ($key) {
                return strpos($key, 'ChannelId') > 0 || strpos($key, 'ChannelNo') > 0;
            });
            if ($relationsChanges) {
                $changes->add(ChannelConfigChangeScope::RELATIONS);
                $changedConfigKeys = array_diff($changedConfigKeys, $relationsChanges);
            }
            if (in_array('weeklySchedule', $changedConfigKeys)) {
                $changes->add(ChannelConfigChangeScope::JSON_WEEKLY_SCHEDULE);
                unset($changedConfigKeys[array_search('weeklySchedule', $changedConfigKeys)]);
            }
            if (in_array('altWeeklySchedule', $changedConfigKeys)) {
                $changes->add(ChannelConfigChangeScope::JSON_ALT_WEEKLY_SCHEDULE);
                unset($changedConfigKeys[array_search('altWeeklySchedule', $changedConfigKeys)]);
            }
            if (in_array('alexa', $changedConfigKeys)) {
                $previousDisabled = ($before['alexa'] ?? [])['alexaDisabled'] ?? false;
                $currentDisabled = $after['alexa']['alexaDisabled'] ?? false;
                if ($previousDisabled !== $currentDisabled) {
                    $changes->add(ChannelConfigChangeScope::ALEXA_INTEGRATION_ENABLED);
                }
            }
            if (in_array('ocr', $changedConfigKeys)) {
                $changes->add(ChannelConfigChangeScope::OCR);
                unset($changedConfigKeys[array_search('ocr', $changedConfigKeys)]);
            }
            if ($changedConfigKeys || $relationsChanges) {
                $changes->add(ChannelConfigChangeScope::JSON_BASIC);
            }
        }
        if (isset($changeArray['userIcon']) || isset($changeArray['altIcon'])) {
            $changes->add(ChannelConfigChangeScope::ICON);
        }
        $this->suplaServer->channelConfigChanged($channel, $changes->getValue());
        $this->reconnectUserIfNeeded($channel, $changes);
    }

    private function reconnectUserIfNeeded(IODeviceChannel $channel, PrzemekBitsBuilder $changes): void {
        $typesThatDoesNotTriggerReconnect = [
            ChannelType::HVAC,
            ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            ChannelType::GENERAL_PURPOSE_METER,
        ];
        $onlyOcrChanged = $changes->getValue() === ChannelConfigChangeScope::OCR;
        if (!in_array($channel->getType()->getId(), $typesThatDoesNotTriggerReconnect) && !$onlyOcrChanged) {
            $this->suplaServer->reconnect($channel->getUser());
        }
    }
}
