<?php

namespace App\Supla;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/** Runs a child process with a mandatory wall-clock deadline. */
class BoundedProcessRunner {
    /**
     * @param string[] $command
     * @param array<string, string>|null $environment
     */
    public function run(array $command, float $timeoutSeconds, ?array $environment = null): string {
        $process = new Process($command, null, $environment);
        $process->setTimeout($timeoutSeconds);
        try {
            $process->run();
        } catch (ProcessTimedOutException $exception) {
            throw new \RuntimeException('Bounded subprocess timed out.', 0, $exception);
        }
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Bounded subprocess failed.');
        }
        return $process->getOutput();
    }
}
