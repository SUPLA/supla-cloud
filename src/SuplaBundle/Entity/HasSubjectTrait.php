<?php
namespace SuplaBundle\Entity;

use InvalidArgumentException;
use SuplaBundle\Enums\ActionableSubjectType;

/**
 * @property Scene|null $scene
 * @property IODeviceChannel|null $channel
 * @property IODeviceChannelGroup|null $channelGroup
 */
trait HasSubjectTrait {
    protected function initializeSubject(ActionableSubject $subject) {
        $this->channel = null;
        $this->channelGroup = null;
        $this->scene = null;
        if ($subject instanceof IODeviceChannel) {
            $this->channel = $subject;
        } elseif ($subject instanceof IODeviceChannelGroup) {
            $this->channelGroup = $subject;
        } elseif ($subject instanceof Scene) {
            $this->scene = $subject;
        } else {
            throw new InvalidArgumentException('Invalid subject given: ' . get_class($subject));
        }
    }

    protected function getTheSubject(): ?ActionableSubject {
        return $this->channel ?: $this->channelGroup ?: $this->scene;
    }

    public function getSubjectType(): ActionableSubjectType {
        return ActionableSubjectType::forEntity($this->getTheSubject());
    }
}
