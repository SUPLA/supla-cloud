<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;

class UpOrStopActionExecutor extends SingleChannelActionExecutor {
    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-UP-OR-STOP', $this->assignCommonParams([], $actionParams));
        $this->suplaServer->executeCommand($command);
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::UP_OR_STOP();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        Assertion::true(
            $subject instanceof IODeviceChannel,
            "Cannot execute the requested action on channel group."
        );
        /** @var IODeviceChannel $subject */
        Assertion::true(
            ChannelFunctionBitsFlags::ROLLER_SHUTTER_STEP_BY_STEP_ACTIONS()->isSupported(($subject->getFlags())),
            'This action is not supported by the hardware.'
        );
        return parent::validateActionParams($subject, $actionParams);
    }
}
