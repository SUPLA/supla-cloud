<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;

class ToggleActionExecutor extends TurnOnActionExecutor {
    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var TurnOnActionExecutor */
    private $on;
    /** @var TurnOffActionExecutor */
    private $off;

    public function __construct(ChannelStateGetter $channelStateGetter, TurnOnActionExecutor $on, TurnOffActionExecutor $off) {
        $this->channelStateGetter = $channelStateGetter;
        $this->on = $on;
        $this->off = $off;
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TOGGLE();
    }

    public function execute(HasFunction $subject, array $actionParams = []) {
        if ($subject instanceof IODeviceChannelGroup) {
            foreach ($subject->getChannels() as $channel) {
                $this->execute($channel);
            }
        } else {
            $state = $this->channelStateGetter->getState($subject);
            if ($state['on'] ?? false) {
                $this->off->execute($subject);
            } else {
                $this->on->execute($subject);
            }
        }
    }
}
