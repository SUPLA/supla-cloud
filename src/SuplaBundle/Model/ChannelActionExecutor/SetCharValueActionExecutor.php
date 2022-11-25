<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;

abstract class SetCharValueActionExecutor extends SingleChannelActionExecutor {
    protected function getCharValue(ActionableSubject $subject, array $actionParams = []): int {
        return 1;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $params = [$this->getCharValue($subject, $actionParams)];
        $command = $subject->buildServerActionCommand('SET-CHAR-VALUE', $params);
        $this->suplaServer->executeCommand($command);
    }
}
