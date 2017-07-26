<?php
namespace SuplaBundle\Supla;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class SuplaServerMockCommandsCollector extends DataCollector {
    const NAME = 'supla.supla_server_mock_data_collector';

    public function __construct() {
        $this->data['commands'] = [];
    }

    public function collect(Request $request, Response $response, \Exception $exception = null) {
    }

    public function addCommand(string $command) {
        $this->data['commands'][] = $command;
    }

    public function getCommands(): array {
        return $this->data['commands'];
    }

    public function getName() {
        return self::NAME;
    }
}
