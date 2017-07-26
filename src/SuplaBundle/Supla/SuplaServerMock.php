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

    protected function command($cmd) {
        $this->commandsCollector->addCommand($cmd);
        return $this->tryToHandleCommand($cmd);
    }

    private function tryToHandleCommand($cmd) {
        if (preg_match('#^IS-IODEV-CONNECTED:(\d+),(\d+)$#', $cmd, $match)) {
            if (rand(0, 5) != 3) { // simulate device connected in 80% cases
                return "CONNECTED:$match[2]\n";
            }
        }
        return false;
    }
}
