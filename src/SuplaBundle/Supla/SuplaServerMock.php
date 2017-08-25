<?php

namespace SuplaBundle\Supla;

/**
 * SuplaServer implementation to be used during development.
 */
class SuplaServerMock extends SuplaServer {
    /** @var SuplaServerMockCommandsCollector */
    private $commandsCollector;

    public function __construct(SuplaServerMockCommandsCollector $commandsCollector) {
        $this->commandsCollector = $commandsCollector;
    }

    protected function connect() {
        return true;
    }

    protected function disconnect() {
        return true;
    }

    public function oauthAuthorize($userId, $accessToken) {
        return true;
    }

    protected function command($command) {
        $this->commandsCollector->addCommand($command);
        return $this->tryToHandleCommand($command);
    }

    private function tryToHandleCommand($cmd) {
        if (preg_match('#^IS-IODEV-CONNECTED:(\d+),(\d+)$#', $cmd, $match)) {
            return (rand() % 2) ? "CONNECTED:$match[2]\n" : 'NO';
        } elseif (preg_match('#^SET-CHAR-VALUE:.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        }
        return false;
    }
}
