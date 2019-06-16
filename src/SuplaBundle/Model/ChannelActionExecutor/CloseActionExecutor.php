<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;

class CloseActionExecutor extends OpenCloseActionExecutor {
    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function __construct(ChannelStateGetter $channelStateGetter) {
        $this->channelStateGetter = $channelStateGetter;
    }

    public function execute(HasFunction $subject, array $actionParams = []) {
        if ($subject instanceof IODeviceChannelGroup) {
            foreach ($subject->getChannels() as $channel) {
                $this->execute($channel);
            }
        } else {
            $state = $this->channelStateGetter->getState($subject);
            if (isset($state['hi']) && !$state['hi']) {
                parent::execute($subject, $actionParams);
            }
        }
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::CLOSE();
    }
}
