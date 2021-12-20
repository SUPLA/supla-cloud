<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Enums\ChannelFunctionAction;

/**
 * @OA\Schema(schema="ChannelActionParams", description="Parameters required to execute an action.",
 *   oneOf={
 *     @OA\Schema(type="object"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsPercentage"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsDimmer"),
 *     @OA\Schema(ref="#/components/schemas/ChannelActionParamsRgbw"),
 *   }
 * )
 */
class ChannelActionExecutor {
    /** @var SingleChannelActionExecutor[][] */
    private $actionExecutors = [];

    /** @param SingleChannelActionExecutor[] $actionExecutors */
    public function __construct($actionExecutors) {
        foreach ($actionExecutors as $actionExecutor) {
            $this->actionExecutors[$actionExecutor->getSupportedAction()->getName()][] = $actionExecutor;
        }
    }

    public function executeAction(ActionableSubject $subject, ChannelFunctionAction $action, array $actionParams = []) {
        $executor = $this->getExecutor($subject, $action);
        $actionParams = $executor->validateActionParams($subject, $actionParams);
        $executor->execute($subject, $actionParams);
    }

    public function validateActionParams(ActionableSubject $subject, ChannelFunctionAction $action, array $actionParams): array {
        try {
            $executor = $this->getExecutor($subject, $action);
        } catch (\InvalidArgumentException $e) {
        }
        return isset($executor) ? $executor->validateActionParams($subject, $actionParams) : [];
    }

    private function getExecutor(ActionableSubject $subject, ChannelFunctionAction $action): SingleChannelActionExecutor {
        Assertion::keyIsset($this->actionExecutors, $action->getName(), 'Cannot execute requested action through API.');
        $executors = $this->actionExecutors[$action->getName()];
        foreach ($executors as $executor) {
            if (in_array($subject->getFunction()->getId(), EntityUtils::mapToIds($executor->getSupportedFunctions()))) {
                return $executor;
            }
        }
        Assertion::true(
            false,
            "Cannot execute the requested action {$action->getName()} on function {$subject->getFunction()->getName()}."
        );
    }
}
