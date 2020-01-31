<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class OpenActionExecutor extends SetCharValueActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
            ChannelFunction::CONTROLLINGTHEDOORLOCK(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::OPEN();
    }
}
