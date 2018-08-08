<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\HasFunction;

abstract class SetCharValueActionExecutor extends SingleChannelActionExecutor {
    protected function getCharValue(HasFunction $subject, array $actionParams = []): int {
        return 1;
    }

    public function execute(HasFunction $subject, array $actionParams = []) {
        $command = $subject->buildServerSetCommand('CHAR', [$this->getCharValue($subject, $actionParams)]);
        $this->suplaServer->executeSetCommand($command);
    }
}
