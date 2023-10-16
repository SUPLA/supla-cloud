<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\HvacIpcActionMode;

class HvacSetWeeklyScheduleActionExecutor extends TurnOffActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_AUTO(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SET_WEEKLY_SCHEDULE();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-SET-HVAC-PARAMETERS', [
            0,
            HvacIpcActionMode::CMD_WEEKLY_SCHEDULE,
            0,
            0,
            0,
        ]);
        $this->suplaServer->executeCommand($command);
    }

}
