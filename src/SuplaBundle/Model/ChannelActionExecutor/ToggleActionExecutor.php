<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Enums\ChannelFunctionAction;

class ToggleActionExecutor extends TurnOnActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TOGGLE();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        if ($subject instanceof IODeviceChannelGroup) {
            foreach ($subject->getChannels() as $channel) {
                $this->execute($channel);
            }
        } else {
            $command = $subject->buildServerSetCommand('ACTION-TOGGLE', $this->assignCommonParams([], $actionParams));
            $command = str_replace('SET-ACTION-TOGGLE-VALUE:', 'ACTION-TOGGLE:', $command);
            $this->suplaServer->executeSetCommand($command);
        }
    }
}
