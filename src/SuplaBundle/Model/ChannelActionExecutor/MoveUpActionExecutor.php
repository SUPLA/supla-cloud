<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;

class MoveUpActionExecutor extends SingleChannelActionExecutor {
    public function execute(ActionableSubject $subject, array $actionParams = []) {

        $command = $subject->buildServerActionCommand('ACTION-MOVE-UP', $this->assignCommonParams([], $actionParams));
        $this->suplaServer->executeCommand($command);
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::MOVE_UP();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        Assertion::true(
            $subject instanceof IODeviceChannel,
            "Cannot execute the requested action MOVE_UP on channel group."
        );
        /** @var IODeviceChannel $subject */
        Assertion::true(
            ChannelFunctionBitsFlags::ROLLER_SHUTTER_START_STOP_ACTIONS()->isSupported(($subject->getFlags())),
            'This action is not supported by the hardware.'
        );
        return parent::validateActionParams($subject, $actionParams);
    }
}
