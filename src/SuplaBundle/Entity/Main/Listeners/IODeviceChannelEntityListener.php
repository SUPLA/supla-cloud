<?php

namespace SuplaBundle\Entity\Main\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelConfigChangeScope;
use SuplaBundle\Enums\PrzemekBitsBuilder;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\ArrayUtils;

class IODeviceChannelEntityListener {
    use SuplaServerAware;

    /** @ORM\PostUpdate */
    public function postUpdate(IODeviceChannel $channel, LifecycleEventArgs $args) {
        $changeArray = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($args->getObject());
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
            $changedKeys = array_keys(ArrayUtils::mergeConfigs($before, $after, $before));
            $relationsChanges = array_filter($changedKeys, function ($key) {
                return strpos($key, 'ChannelId') > 0 || strpos($key, 'ChannelNo') > 0;
            });
            if ($relationsChanges) {
                $changes->add(ChannelConfigChangeScope::RELATIONS);
                $changedKeys = array_diff($changedKeys, $relationsChanges);
            }
            if (in_array('weeklySchedule', $changedKeys)) {
                $changes->add(ChannelConfigChangeScope::JSON_WEEKLY_SCHEDULE);
                unset($changedKeys[array_search('weeklySchedule', $changedKeys)]);
            }
            if (in_array('altWeeklySchedule', $changedKeys)) {
                $changes->add(ChannelConfigChangeScope::JSON_ALT_WEEKLY_SCHEDULE);
                unset($changedKeys[array_search('altWeeklySchedule', $changedKeys)]);
            }
            if (in_array('alexa', $changedKeys)) {
                $previousDisabled = ($before['alexa'] ?? [])['alexaDisabled'] ?? false;
                $currentDisabled = $after['alexa']['alexaDisabled'] ?? false;
                if ($previousDisabled !== $currentDisabled) {
                    $changes->add(ChannelConfigChangeScope::ALEXA_INTEGRATION_ENABLED);
                }
            }
            if ($changedKeys || $relationsChanges) {
                $changes->add(ChannelConfigChangeScope::JSON_BASIC);
            }
        }
        if (isset($changeArray['userIcon']) || isset($changeArray['altIcon'])) {
            $changes->add(ChannelConfigChangeScope::ICON);
        }
        $this->suplaServer->channelConfigChanged($channel, $changes->getValue());
    }
}
