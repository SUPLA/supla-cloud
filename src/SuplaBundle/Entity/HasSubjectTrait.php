<?php
namespace SuplaBundle\Entity;

use InvalidArgumentException;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Enums\ActionableSubjectType;

/**
 * @property Scene|null $scene
 * @property IODeviceChannel|null $channel
 * @property IODeviceChannelGroup|null $channelGroup
 * @property Schedule|null $schedule
 */
trait HasSubjectTrait {
    protected function initializeSubject(ActionableSubject $subject) {
        $this->channel = null;
        $this->channelGroup = null;
        $this->scene = null;
        $this->schedule = null;
        if ($subject instanceof IODeviceChannel) {
            $this->channel = $subject;
        } elseif ($subject instanceof IODeviceChannelGroup) {
            $this->channelGroup = $subject;
        } elseif ($subject instanceof Scene) {
            $this->scene = $subject;
        } elseif ($subject instanceof Schedule) {
            $this->schedule = $subject;
        } else {
            throw new InvalidArgumentException('Invalid subject given: ' . get_class($subject));
        }
    }

    protected function getTheSubject(): ?ActionableSubject {
        return $this->channel ?: $this->channelGroup ?: $this->scene ?: $this->schedule;
    }

    public function getSubjectType(): ?ActionableSubjectType {
        return $this->hasSubject() ? ActionableSubjectType::forEntity($this->getTheSubject()) : null;
    }

    public function hasSubject(): bool {
        return !!$this->getTheSubject();
    }
}
