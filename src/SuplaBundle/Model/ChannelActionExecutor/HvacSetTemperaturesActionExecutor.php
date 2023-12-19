<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\HvacIpcValueFlags;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use function SuplaBundle\Utils\NumberUtils;

class HvacSetTemperaturesActionExecutor extends SingleChannelActionExecutor {
    /** @var SubjectConfigTranslator */
    private $configTranslator;

    public function __construct(SubjectConfigTranslator $configTranslator) {
        $this->configTranslator = $configTranslator;
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SET_TEMPERATURES();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::allInArray(array_keys($actionParams), ['temperatureHeat', 'temperatureCool']);
        Assertion::greaterOrEqualThan(count($actionParams), 1, 'At least one of temperatureHeat, temperatureCool is required.');
        $this->validateTemperatures($subject, $actionParams);
        return array_map(function ($temp) {
            return round($temp * 100);
        }, $actionParams);
    }

    public function transformActionParamsForApi(ActionableSubject $subject, array $actionParams): array {
        return array_map(function ($temp) {
            return NumberUtils::maximumDecimalPrecision($temp / 100);
        }, $actionParams);
    }

    protected function validateTemperatures(ActionableSubject $subject, array $actionParams) {
        $setpoints = array_intersect_key($actionParams, ['temperatureHeat' => '', 'temperatureCool' => '']);
        $min = -1000;
        $max = 1000;
        $offsetMin = 0;
        if ($subject instanceof HasUserConfig) {
            $config = $this->configTranslator->getConfig($subject);
            $constraints = $config['temperatureConstraints'] ?? [];
            $min = $constraints['roomMin'] ?? -1000;
            $max = $constraints['roomMax'] ?? 1000;
            $offsetMin = $constraints['autoOffsetMin'] ?? 0;
        }
        if (isset($setpoints['temperatureHeat'])) {
            Assert::that($setpoints['temperatureHeat'])->numeric()->between($min, $max);
        }
        if (isset($setpoints['temperatureCool'])) {
            Assert::that($setpoints['temperatureCool'])->numeric()->between($min, $max);
        }
        if (isset($setpoints['temperatureHeat']) && isset($setpoints['temperatureCool'])) {
            Assertion::lessOrEqualThan(
                $setpoints['temperatureHeat'],
                $setpoints['temperatureCool'] - $offsetMin,
                'Too small difference between heat and cool temperatures.'
            );
        }
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        [$heat, $cool, $flag] = $this->getHeatCoolFlag($actionParams);
        $command = $subject->buildServerActionCommand('ACTION-HVAC-SET-TEMPERATURES', [$heat, $cool, $flag]);
        $this->suplaServer->executeCommand($command);
    }

    protected function getHeatCoolFlag(array $setpoints): array {
        $setpointFlag = 0;
        $heat = 0;
        $cool = 0;
        if (isset($setpoints['temperatureHeat'])) {
            $heat = $setpoints['temperatureHeat'];
            $setpointFlag |= HvacIpcValueFlags::TEMPERATURE_HEAT_SET;
        }
        if (isset($setpoints['temperatureCool'])) {
            $cool = $setpoints['temperatureCool'];
            $setpointFlag |= HvacIpcValueFlags::TEMPERATURE_COOL_SET;
        }
        return [$heat, $cool, $setpointFlag];
    }
}
