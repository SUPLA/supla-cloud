<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;

class StopActionExecutor extends SetCharValueActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
            ChannelFunction::CONTROLLINGTHEROOFWINDOW(),
            ChannelFunction::CONTROLLINGTHEFACADEBLIND(),
            ChannelFunction::TERRACE_AWNING(),
            ChannelFunction::PROJECTOR_SCREEN(),
            ChannelFunction::CURTAIN(),
            ChannelFunction::ROLLER_GARAGE_DOOR(),
            ChannelFunction::VERTICAL_BLIND(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::STOP();
    }

    protected function getCharValue(ActionableSubject $subject, array $actionParams = []): int {
        return 0;
    }
}
