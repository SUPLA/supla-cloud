<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\HvacIpcActionMode;
use SuplaBundle\Utils\NumberUtils;

class HvacSetParametersActionExecutor extends HvacSetTemperaturesActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SET_PARAMETERS();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::allInArray(array_keys($actionParams), ['temperatureHeat', 'temperatureCool', 'durationMs', 'mode']);
        $this->validateTemperatures($subject, $actionParams);
        if (isset($actionParams['durationMs'])) {
            Assert::that($actionParams['durationMs'])
                ->integer()
                ->greaterOrEqualThan(0)
                ->lessOrEqualThan(31536000000, 'Maximum duration is one year.'); // i18n
        }
        if (isset($actionParams['mode'])) {
            if ($actionParams['mode']) {
                Assertion::eq(
                    ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
                    $subject->getFunction()->getId(),
                    'Mode can be set only for HVAC_THERMOSTAT_HEAT_COOL.'
                );
                Assert::that($actionParams['mode'])->string()->inArray(['HEAT', 'COOL', 'HEAT_COOL']);
            } else {
                unset($actionParams['mode']);
            }
        }
        if (isset($actionParams['temperatureHeat'])) {
            $actionParams['temperatureHeat'] = round($actionParams['temperatureHeat'] * 100);
        }
        if (isset($actionParams['temperatureCool'])) {
            $actionParams['temperatureCool'] = round($actionParams['temperatureCool'] * 100);
        }
        return $actionParams;
    }

    public function transformActionParamsForApi(ActionableSubject $subject, array $actionParams): array {
        if (isset($actionParams['temperatureHeat'])) {
            $actionParams['temperatureHeat'] = NumberUtils::maximumDecimalPrecision($actionParams['temperatureHeat'] / 100);
        }
        if (isset($actionParams['temperatureCool'])) {
            $actionParams['temperatureCool'] = NumberUtils::maximumDecimalPrecision($actionParams['temperatureCool'] / 100);
        }
        return $actionParams;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        [$heat, $cool, $flag] = $this->getHeatCoolFlag($actionParams);
        $duration = $actionParams['durationMs'] ?? 0;
        $mode = HvacIpcActionMode::toArray()[$actionParams['mode'] ?? ''] ?? HvacIpcActionMode::CMD_SWITCH_TO_MANUAL;
        $command = $subject->buildServerActionCommand('ACTION-SET-HVAC-PARAMETERS', [$duration, $mode, $heat, $cool, $flag]);
        $this->suplaServer->executeCommand($command);
    }
}
