<?php
namespace SuplaBundle\Entity;

use SuplaBundle\Enums\ActionableSubjectType;

/**
 * @property Scene|null $scene
 * @property IODeviceChannel|null $channel
 * @property IODeviceChannelGroup|null $channelGroup
 */
trait HasSubjectTrait {
    protected function initializeSubject(HasFunction $subject) {
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
            throw new \InvalidArgumentException('Invalid link subject given: ' . get_class($subject));
        }
    }

    protected function getTheSubject(): ?HasFunction {
        return $this->channel ?: $this->channelGroup ?: $this->scene;
    }

    public function getSubjectType(): ActionableSubjectType {
        return ActionableSubjectType::forEntity($this->getTheSubject());
    }
}
