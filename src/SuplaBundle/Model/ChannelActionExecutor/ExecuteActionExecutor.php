<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class ExecuteActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [ChannelFunction::SCENE()];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::EXECUTE();
    }

    public function execute(HasFunction $scene, array $actionParams = []) {
        $command = $scene->buildServerSetCommand('', $this->assignCommonParams([], $actionParams));
        $this->suplaServer->executeSetCommand($command);
    }
}
