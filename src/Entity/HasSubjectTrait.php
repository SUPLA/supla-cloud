<?php
namespace App\Entity;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\IODeviceChannelGroup;
use App\Entity\Main\PushNotification;
use App\Entity\Main\Scene;
use App\Entity\Main\Schedule;
use App\Enums\ActionableSubjectType;
use InvalidArgumentException;

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
        if (property_exists($this, 'schedule')) {
            $this->schedule = null;
        }
        if (property_exists($this, 'pushNotification')) {
            $this->pushNotification = null;
        }
        if ($subject instanceof IODeviceChannel) {
            $this->channel = $subject;
        } elseif ($subject instanceof IODeviceChannelGroup) {
            $this->channelGroup = $subject;
        } elseif ($subject instanceof Scene) {
            $this->scene = $subject;
        } elseif ($subject instanceof Schedule) {
            $this->schedule = $subject;
        } elseif ($subject instanceof PushNotification) {
            $this->pushNotification = $subject;
        } else {
            throw new InvalidArgumentException('Invalid subject given: ' . get_class($subject));
        }
    }

    protected function getTheSubject(): ?ActionableSubject {
        return $this->channel ?: $this->channelGroup ?: $this->scene ?: $this->schedule ?: $this->pushNotification;
    }

    public function getSubjectType(): ?ActionableSubjectType {
        return $this->hasSubject() ? ActionableSubjectType::forEntity($this->getTheSubject()) : null;
    }

    public function hasSubject(): bool {
        return !!$this->getTheSubject();
    }
}
