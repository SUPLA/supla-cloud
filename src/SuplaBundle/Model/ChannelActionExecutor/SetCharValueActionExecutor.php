<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\Main\HasFunction;

abstract class SetCharValueActionExecutor extends SingleChannelActionExecutor {
    protected function getCharValue(HasFunction $subject, array $actionParams = []): int {
        return 1;
    }

    public function execute(HasFunction $subject, array $actionParams = []) {
        $params = [$this->getCharValue($subject, $actionParams)];
        $command = $subject->buildServerSetCommand('CHAR', $this->assignCommonParams($params, $actionParams));
        $this->suplaServer->executeSetCommand($command);
    }
}
