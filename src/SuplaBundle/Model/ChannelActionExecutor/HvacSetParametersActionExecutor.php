<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\HvacIpcActionMode;
use SuplaBundle\Model\UserConfigTranslator\HvacThermostatConfigTranslator;
use SuplaBundle\Utils\NumberUtils;

class HvacSetParametersActionExecutor extends HvacSetTemperaturesActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
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
        $availableModes = $this->getAvailableModes($subject);
        if (isset($actionParams['mode'])) {
            if ($actionParams['mode']) {
                Assert::that($actionParams['mode'])
                    ->string()
                    ->inArray($availableModes, 'Mode %s cannot be used with this channel. Supported: %s.');
            } else {
                unset($actionParams['mode']);
            }
        }
        if (isset($actionParams['temperatureHeat'])) {
            Assertion::inArray(HvacThermostatConfigTranslator::PROGRAM_MODE_HEAT, $availableModes, 'Cannot set temperatureHeat.');
            $actionParams['temperatureHeat'] = round($actionParams['temperatureHeat'] * 100);
        }
        if (isset($actionParams['temperatureCool'])) {
            Assertion::inArray(HvacThermostatConfigTranslator::PROGRAM_MODE_COOL, $availableModes, 'Cannot set temperatureCool.');
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
        $command = $subject->buildServerActionCommand('ACTION-HVAC-SET-PARAMETERS', [$duration, $mode, $heat, $cool, $flag]);
        $this->suplaServer->executeCommand($command);
    }

    private function getAvailableModes(ActionableSubject $subject): array {
        switch ($subject->getFunction()->getId()) {
            case ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL:
                return [
                    HvacThermostatConfigTranslator::PROGRAM_MODE_HEAT,
                    HvacThermostatConfigTranslator::PROGRAM_MODE_COOL,
                    HvacThermostatConfigTranslator::PROGRAM_MODE_HEAT_COOL,
                ];
            case ChannelFunction::HVAC_THERMOSTAT:
                return [
                    HvacThermostatConfigTranslator::PROGRAM_MODE_HEAT,
                    HvacThermostatConfigTranslator::PROGRAM_MODE_COOL,
                ];
            default:
                return [HvacThermostatConfigTranslator::PROGRAM_MODE_HEAT];
        }
    }
}
