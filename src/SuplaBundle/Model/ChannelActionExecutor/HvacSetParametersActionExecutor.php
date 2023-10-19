<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\HvacIpcActionMode;

class HvacSetParametersActionExecutor extends HvacSetTemperaturesActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SET_PARAMETERS();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        Assertion::allInArray(array_keys($actionParams), ['setpoints', 'durationMs', 'mode']);
        if (isset($actionParams['setpoints'])) {
            $this->validateSetpoints($subject, $actionParams['setpoints']);
        }
        if (isset($actionParams['durationMs'])) {
            Assert::that($actionParams['durationMs'])
                ->integer()
                ->greaterOrEqualThan(0)
                ->lessOrEqualThan(31536000000, 'Maximum duration is one year.'); // i18n
        }
        if (isset($actionParams['mode'])) {
            if ($actionParams['mode']) {
                Assertion::eq(
                    ChannelFunction::HVAC_THERMOSTAT_AUTO,
                    $subject->getFunction()->getId(),
                    'Mode can be set only for HVAC_THERMOSTAT_AUTO.'
                );
                Assert::that($actionParams['mode'])->string()->inArray(['HEAT', 'COOL', 'AUTO']);
            } else {
                unset($actionParams['mode']);
            }
        }
        return $actionParams;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        [$heat, $cool, $flag] = $this->getHeatCoolFlag($actionParams['setpoints'] ?? []);
        $duration = $actionParams['durationMs'] ?? 0;
        $mode = HvacIpcActionMode::toArray()[$actionParams['mode'] ?? ''] ?? HvacIpcActionMode::CMD_SWITCH_TO_MANUAL;
        $command = $subject->buildServerActionCommand('ACTION-SET-HVAC-PARAMETERS', [$duration, $mode, $heat, $cool, $flag]);
        $this->suplaServer->executeCommand($command);
    }
}
