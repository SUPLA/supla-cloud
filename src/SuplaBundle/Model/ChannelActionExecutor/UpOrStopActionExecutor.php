<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;

class UpOrStopActionExecutor extends SingleChannelActionExecutor {
    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-UP-OR-STOP');
        $this->suplaServer->executeCommand($command);
    }

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
        return ChannelFunctionAction::UP_OR_STOP();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::true(
            $subject instanceof IODeviceChannel,
            "Cannot execute the requested action on channel group."
        );
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $subject */
        Assertion::true(
            ChannelFunctionBitsFlags::ROLLER_SHUTTER_STEP_BY_STEP_ACTIONS()->isSupported(($subject->getFlags())),
            'This action is not supported by the hardware.'
        );
        return parent::validateAndTransformActionParamsFromApi($subject, $actionParams);
    }
}
