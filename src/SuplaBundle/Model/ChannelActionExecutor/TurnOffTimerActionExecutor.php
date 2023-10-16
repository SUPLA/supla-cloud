<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\HvacIpcActionMode;

/**
 * @OA\Schema(schema="ChannelActionParamsTurnOffTimer",
 *   description="Action params for `TURN_OFF_TIMER` action.",
 *   @OA\Property(property="duration", type="integer", minimum=0),
 * )
 */
class TurnOffTimerActionExecutor extends TurnOffActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_AUTO(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TURN_OFF_TIMER();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        if ($actionParams) {
            Assertion::count($actionParams, 1, 'Only duration parameter is allowed.');
            Assertion::keyIsset($actionParams, 'duration');
            Assert::that($actionParams['duration'])->integer()->greaterOrEqualThan(0);
        }
        return $actionParams;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        if ($duration = ($actionParams['duration'] ?? 0)) {
            $command = $subject->buildServerActionCommand('ACTION-SET-HVAC-PARAMETERS', [$duration, HvacIpcActionMode::MODE_OFF, 0, 0, 0]);
            $this->suplaServer->executeCommand($command);
        } else {
            parent::execute($subject, $actionParams);
        }
    }

}
