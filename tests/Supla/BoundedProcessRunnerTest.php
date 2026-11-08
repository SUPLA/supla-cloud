<?php

namespace App\Tests\Supla;

use App\Supla\BoundedProcessRunner;
use PHPUnit\Framework\TestCase;

class BoundedProcessRunnerTest extends TestCase {
    public function testStopsAStalledResolverProcessAtItsDeadline(): void {
        $runner = new BoundedProcessRunner();
        $startedAt = microtime(true);

        try {
            $runner->run([PHP_BINARY, '-r', 'usleep(5000000);'], 0.15);
            $this->fail('Expected the resolver process to exceed its deadline.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('timed out', $exception->getMessage());
        }

        $this->assertLessThan(1.0, microtime(true) - $startedAt);
    }
}
