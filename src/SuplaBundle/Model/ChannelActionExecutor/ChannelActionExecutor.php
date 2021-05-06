<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\Main\EntityUtils;
use SuplaBundle\Entity\Main\HasFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class ChannelActionExecutor {
    /** @var SingleChannelActionExecutor[][] */
    private $actionExecutors = [];

    /** @param SingleChannelActionExecutor[] $actionExecutors */
    public function __construct($actionExecutors) {
        foreach ($actionExecutors as $actionExecutor) {
            $this->actionExecutors[$actionExecutor->getSupportedAction()->getName()][] = $actionExecutor;
        }
    }

    public function executeAction(HasFunction $subject, ChannelFunctionAction $action, array $actionParams = []) {
        $executor = $this->getExecutor($subject, $action);
        $actionParams = $executor->validateActionParams($subject, $actionParams);
        $executor->execute($subject, $actionParams);
    }

    public function validateActionParams(HasFunction $subject, ChannelFunctionAction $action, array $actionParams): array {
        $executor = $this->getExecutor($subject, $action);
        return $executor->validateActionParams($subject, $actionParams);
    }

    private function getExecutor(HasFunction $subject, ChannelFunctionAction $action): SingleChannelActionExecutor {
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
