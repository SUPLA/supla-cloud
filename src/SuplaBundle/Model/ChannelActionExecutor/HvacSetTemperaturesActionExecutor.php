<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\HvacIpcActionMode;

class HvacSetTemperaturesActionExecutor extends HvacSetWeeklyScheduleActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SET_TEMPERATURES();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        Assertion::count($actionParams, 1, 'Parameter setpoints is required.');
        Assertion::keyIsset($actionParams, 'setpoints');
        $setpoints = $actionParams['setpoints'];
        Assertion::isArray($setpoints);
        Assertion::between(count($setpoints), 1, 2, null, 'param.setpoints');
        $availableTemperatures = array_filter([
            $this->heatAvailable($subject) ? 'heat' : null,
            $this->coolAvailable($subject) ? 'cool' : null,
        ]);
        Assertion::allInArray(array_keys($setpoints), $availableTemperatures, 'Only heat and/or cool setpoints are available.');
        if (isset($setpoints['heat'])) {
            Assertion::numeric($setpoints['heat']);
        }
        if (isset($setpoints['cool'])) {
            Assertion::numeric($setpoints['cool']);
        }
        return $actionParams;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $setpoints = $actionParams['setpoints'];
        $setpointFlag = 0;
        $heat = 0;
        $cool = 0;
        if (isset($setpoints['heat'])) {
            Assertion::numeric($setpoints['heat']);
            $heat = round(floatval($setpoints['heat']) * 100);
            $setpointFlag |= 1;
        }
        if (isset($setpoints['cool'])) {
            Assertion::numeric($setpoints['cool']);
            $cool = round(floatval($setpoints['cool']) * 100);
            $setpointFlag |= 2;
        }
        $command = $subject->buildServerActionCommand('ACTION-SET-HVAC-PARAMETERS', [
            0,
            HvacIpcActionMode::NOT_SET,
            $heat,
            $cool,
            $setpointFlag,
        ]);
        $this->suplaServer->executeCommand($command);
    }

    protected function heatAvailable(ActionableSubject $subject): bool {
        return in_array($subject->getFunction()->getId(), [
                ChannelFunction::HVAC_THERMOSTAT_AUTO,
                ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
                ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ]) || $subject->getUserConfigValue('subfunction') === 'HEAT';
    }

    protected function coolAvailable(ActionableSubject $subject): bool {
        return in_array($subject->getFunction()->getId(), [
                ChannelFunction::HVAC_THERMOSTAT_AUTO,
            ]) || $subject->getUserConfigValue('subfunction') === 'COOL';
    }
}
