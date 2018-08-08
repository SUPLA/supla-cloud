<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class OpenCloseActionExecutor extends SetCharValueActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGATE(),
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::OPEN_CLOSE();
    }
}
