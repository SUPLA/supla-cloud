<?php

namespace App\Tests\Integration;

class ConsoleSignalHandlersIntegrationTest extends IntegrationTestCase {
    public function testCommandsDoNotReplaceProcessSignalHandlers(): void {
        if (!function_exists('pcntl_signal_get_handler')) {
            $this->markTestSkipped('The pcntl extension is required to inspect signal handlers.');
        }

        $signals = [SIGINT, SIGTERM, SIGUSR1, SIGUSR2];
        $handlers = array_map('pcntl_signal_get_handler', $signals);

        $this->executeCommand('list', $this->createHttpsClient());

        $this->assertSame($handlers, array_map('pcntl_signal_get_handler', $signals));
    }
}
