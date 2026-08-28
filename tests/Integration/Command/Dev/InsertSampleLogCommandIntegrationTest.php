<?php

namespace App\Tests\Integration\Command\Dev;

use App\Entity\Main\IODeviceChannel;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\UserFixtures;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class InsertSampleLogCommandIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    private const ELECTRICITY_METER_FIELDS = [
        'phase1_fae',
        'phase1_rae',
        'phase1_fre',
        'phase1_rre',
        'phase2_fae',
        'phase2_rae',
        'phase2_fre',
        'phase2_rre',
        'phase3_fae',
        'phase3_rae',
        'phase3_fre',
        'phase3_rre',
        'fae_balanced',
        'rae_balanced',
    ];

    private $logsEntityManager;

    /** @before */
    public function initializeLogsEntityManager() {
        $this->logsEntityManager = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
    }

    public function testInsertingSampleElectricityMeterLogWithExplicitTime() {
        $channel = $this->createElectricityMeterChannel();
        $command = $this->application->find('supla:dev:insertSampleLog');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'channelId' => $channel->getId(),
            'time' => ['2026-08-26', '10:00:00'],
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $logs = $this->logsEntityManager->getRepository(ElectricityMeterLogItem::class)->findBy(['channel_id' => $channel->getId()], ['date' => 'ASC']);
        $this->assertCount(1, $logs);
        $this->assertSame('2026-08-26 10:00:00', $logs[0]->getDate());
        foreach (self::ELECTRICITY_METER_FIELDS as $field) {
            $this->assertGreaterThan(0, $this->getPrivateField($logs[0], $field));
        }
    }

    public function testInsertingSampleElectricityMeterLogWithNegativeRelativeTime() {
        $channel = $this->createElectricityMeterChannel();
        $command = $this->application->find('supla:dev:insertSampleLog');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'channelId' => $channel->getId(),
            'time' => ['-15', 'minutes'],
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $logs = $this->logsEntityManager->getRepository(ElectricityMeterLogItem::class)->findBy(['channel_id' => $channel->getId()], ['date' => 'ASC']);
        $this->assertCount(1, $logs);
    }

    public function testInsertingSampleElectricityMeterLogUsesIncreasingCumulativeValues() {
        $channel = $this->createElectricityMeterChannel();
        $command = $this->application->find('supla:dev:insertSampleLog');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'channelId' => $channel->getId(),
            'time' => ['2026-08-26', '10:00:00'],
        ]);
        $exitCode = $commandTester->execute([
            'channelId' => $channel->getId(),
            'time' => ['2026-08-26', '10:05:00'],
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $logs = $this->logsEntityManager->getRepository(ElectricityMeterLogItem::class)->findBy(['channel_id' => $channel->getId()], ['date' => 'ASC']);
        $this->assertCount(2, $logs);
        foreach (self::ELECTRICITY_METER_FIELDS as $field) {
            $this->assertGreaterThan($this->getPrivateField($logs[0], $field), $this->getPrivateField($logs[1], $field));
        }
    }

    public function testInsertingSampleElectricityMeterLogDefaultsToCurrentTime() {
        $channel = $this->createElectricityMeterChannel();
        $command = $this->application->find('supla:dev:insertSampleLog');
        $commandTester = new CommandTester($command);
        $before = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $exitCode = $commandTester->execute(['channelId' => $channel->getId()]);

        $after = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->assertSame(Command::SUCCESS, $exitCode);
        $logs = $this->logsEntityManager->getRepository(ElectricityMeterLogItem::class)->findBy(['channel_id' => $channel->getId()], ['date' => 'ASC']);
        $this->assertCount(1, $logs);
        $logDate = new \DateTimeImmutable($logs[0]->getDate(), new \DateTimeZone('UTC'));
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $logDate->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $logDate->getTimestamp());
    }

    public function testRejectingUnsupportedChannelType() {
        $channel = $this->createOtherChannel();
        $command = $this->application->find('supla:dev:insertSampleLog');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(['channelId' => $channel->getId()]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Only ELECTRICITYMETER is supported', $commandTester->getDisplay());
    }

    public function testRejectingInvalidTime() {
        $channel = $this->createElectricityMeterChannel();
        $command = $this->application->find('supla:dev:insertSampleLog');
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'channelId' => $channel->getId(),
            'time' => ['definitely-not-a-date'],
        ]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Cannot parse log time', $commandTester->getDisplay());
    }

    private function createElectricityMeterChannel(): IODeviceChannel {
        $user = $this->createConfirmedUser('em-' . bin2hex(random_bytes(4)) . '@supla.org');
        $location = $this->createLocation($user);
        $device = $this->createDevice($location, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        return $device->getChannels()->first();
    }

    private function createOtherChannel(): IODeviceChannel {
        $user = $this->createConfirmedUser('other-' . bin2hex(random_bytes(4)) . '@supla.org');
        $location = $this->createLocation($user);
        $device = $this->createDevice($location, [[ChannelType::THERMOMETER, ChannelFunction::THERMOMETER]]);
        return $device->getChannels()->first();
    }

    private function getPrivateField(object $entity, string $field) {
        $getter = function (string $field) {
            return $this->{$field};
        };

        return $getter->call($entity, $field);
    }
}
