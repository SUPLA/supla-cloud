<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

/**
 * @OA\Schema(schema="ChannelActionParamsDuration",
 *   description="Action params for actions with duration.",
 *   @OA\Property(property="durationMs", type="integer", minimum=0, maximum=31536000000),
 * )
 */
class TurnOffTimerActionExecutor extends TurnOffActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TURN_OFF_WITH_DURATION();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        if ($actionParams) {
            Assertion::count($actionParams, 1, 'Only durationMs parameter is allowed.');
            Assertion::keyIsset($actionParams, 'durationMs');
            Assert::that($actionParams['durationMs'])
                ->integer()
                ->greaterOrEqualThan(0)
                ->lessOrEqualThan(31536000000, 'Maximum duration is one year.'); // i18n
        }
        return $actionParams;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        if ($duration = ($actionParams['durationMs'] ?? 0)) {
            $command = $subject->buildServerActionCommand('ACTION-TURN-OFF-WITH-DURATION', [$duration]);
            $this->suplaServer->executeCommand($command);
        } else {
            parent::execute($subject, $actionParams);
        }
    }
}
