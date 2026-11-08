<?php

namespace App\Tests\Integration\Command\Autodiscover;

use App\Command\Cyclic\CyclicCommand;
use App\Entity\Main\SettingsString;
use App\Enums\InstanceSettings;
use App\Model\TimeProvider;
use App\Repository\SettingsStringRepository;
use App\Supla\SuplaAutodiscoverMock;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class RotateTargetCloudTokenCommandIntegrationTest extends IntegrationTestCase {
    /** @var SettingsStringRepository */
    private $repository;

    protected function initializeDatabaseForTests() {
        $this->repository = $this->getDoctrine()->getRepository(SettingsString::class);
    }

    public function testRotatesAndVerifiesTargetToken(): void {
        SuplaAutodiscoverMock::$isTarget = true;
        $this->repository->setValue(InstanceSettings::TARGET_TOKEN, '123_old-token');
        SuplaAutodiscoverMock::$requests = [];

        $exitCode = (new CommandTester($this->application->find('supla:ad:rotate-token')))->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringStartsWith('123_', $this->repository->getValue(InstanceSettings::TARGET_TOKEN));
        $this->assertNotSame('123_old-token', $this->repository->getValue(InstanceSettings::TARGET_TOKEN));
        $this->assertFalse($this->repository->hasValue(InstanceSettings::TARGET_TOKEN_ROTATION_PENDING_TOKEN));
        $this->assertFalse($this->repository->hasValue(InstanceSettings::TARGET_TOKEN_ROTATION_PENDING_IDEMPOTENCY_KEY));
        $this->assertTrue($this->repository->hasValue(InstanceSettings::TARGET_TOKEN_ROTATION_NEXT_AT));
        $this->assertSame('/target-cloud-auth-token', SuplaAutodiscoverMock::$requests[0]['endpoint']);
        $this->assertArrayHasKey('Idempotency-Key', SuplaAutodiscoverMock::$requests[0]['headers']);
        $this->assertSame('/about', SuplaAutodiscoverMock::$requests[1]['endpoint']);
    }

    public function testCyclicDispatchInitializesRandomizedScheduleWithoutRotating(): void {
        SuplaAutodiscoverMock::$isTarget = true;
        $this->repository->setValue(InstanceSettings::TARGET_TOKEN, '123_old-token');
        SuplaAutodiscoverMock::$requests = [];
        /** @var CyclicCommand $command */
        $command = $this->application->find('supla:ad:rotate-token');

        $beforeScheduling = time();
        $this->assertFalse($command->shouldRunNow(self::$container->get(TimeProvider::class)));
        $nextRotationAt = (int)$this->repository->getValue(InstanceSettings::TARGET_TOKEN_ROTATION_NEXT_AT);
        $this->assertGreaterThanOrEqual($beforeScheduling + 5 * 86400, $nextRotationAt);
        $this->assertLessThanOrEqual($beforeScheduling + 10 * 86400, $nextRotationAt);
        $this->assertSame([], SuplaAutodiscoverMock::$requests);
    }
}
