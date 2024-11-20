<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Utils\NumberUtils;

class HvacSetTemperatureActionExecutor extends SingleChannelActionExecutor {
    /** @var SubjectConfigTranslator */
    private $configTranslator;

    public function __construct(SubjectConfigTranslator $configTranslator) {
        $this->configTranslator = $configTranslator;
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SET_TEMPERATURE();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::count($actionParams, 1, 'Parameter temperature is required.');
        Assertion::keyIsset($actionParams, 'temperature');
        $min = -1000;
        $max = 1000;
        if ($subject instanceof HasUserConfig) {
            $config = $this->configTranslator->getConfig($subject);
            $constraints = $config['temperatureConstraints'] ?? [];
            $constraintName = ($config['defaultTemperatureConstraintName'] ?? null) ?: 'room';
            $min = $constraints["{$constraintName}Min"] ?? -1000;
            $max = $constraints["{$constraintName}Max"] ?? 1000;
        }
        Assert::that($actionParams['temperature'], null, 'temperature')
            ->numeric()
            ->between($min, $max);
        return ['temperature' => round($actionParams['temperature'] * 100)];
    }

    public function transformActionParamsForApi(ActionableSubject $subject, array $actionParams): array {
        return ['temperature' => NumberUtils::maximumDecimalPrecision($actionParams['temperature'] / 100)];
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-HVAC-SET-TEMPERATURE', [$actionParams['temperature']]);
        $this->suplaServer->executeCommand($command);
    }
}
