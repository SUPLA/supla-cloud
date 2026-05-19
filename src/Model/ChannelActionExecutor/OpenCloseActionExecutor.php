<?php
namespace App\Model\ChannelActionExecutor;

use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;

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
