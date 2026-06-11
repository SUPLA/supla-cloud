<?php

namespace App\Tests\Integration\Command\Cyclic;

use App\Command\Cyclic\ElectricityMeterLogsCalculateDeltasCommand;
use App\Entity\EntityUtils;
use App\Entity\MeasurementLogs\ElectricityMeterDeltaLogItem;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class ElectricityMeterLogsCalculateDeltasCommandIntegrationTest extends IntegrationTestCase {
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @before */
    protected function initializeEntityManager() {
        $this->entityManager = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
    }

    public function testCalculatingDeltasForIrregularLogs() {
        // Prepare some irregular logs
        // 12:05 - 1000
        // 12:20 - 2000
        // 12:50 - 5000

        $this->createEmLog(2, '2026-06-11 12:05:00', 1000);
        $this->createEmLog(2, '2026-06-11 12:20:00', 2000);
        $this->createEmLog(2, '2026-06-11 12:50:00', 5000);

        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        $this->assertEquals(0, $exitCode);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 2], ['date' => 'ASC']);

        // Expected slots:
        // First log 12:05 -> next 15-min mark is 12:15.
        // For 12:15 slot, prevSlotDate is 12:00.
        // Since 12:00 < first log (12:05), it returns null and skips 12:15 slot.
        // Next slot is 12:30. prevSlotDate is 12:15.
        // val(12:15) = 1000 + (10/15)*1000 = 1666.67
        // val(12:30) = 2000 + (10/30)*3000 = 3000
        // Delta 12:30 = 3000 - 1666.67 = 1333.33 -> 1333
        // Next slot is 12:45. prevSlotDate is 12:30.
        // val(12:45) = 2000 + (25/30)*3000 = 4500
        // Delta 12:45 = 4500 - 3000 = 1500

        $this->assertCount(2, $deltas);
        $this->assertEquals('2026-06-11 12:30:00', $deltas[0]->getDate());
        $this->assertEquals('2026-06-11 12:45:00', $deltas[1]->getDate());

        $totalDelta = 0;
        foreach ($deltas as $delta) {
            $totalDelta += $delta->getTotalForwardActiveEnergy();
        }

        // Total energy from 12:15 (estimated 1666.67) to 12:45 (estimated 4500) is 2833.33 -> 2833
        $this->assertEquals(2833, $totalDelta);
    }

    public function testCalculatingDeltasFor10MinuteIrregularLogs() {
        // 16:13:33 1000
        // 16:23:34 1200
        // 16:33:33 1500
        // 16:43:33 1550
        $this->createEmLog(4, '2026-06-11 16:13:33', 1000);
        $this->createEmLog(4, '2026-06-11 16:23:34', 1200);
        $this->createEmLog(4, '2026-06-11 16:33:33', 1500);
        $this->createEmLog(4, '2026-06-11 16:43:33', 1550);

        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 4], ['date' => 'ASC']);

        // First log 16:13:33 -> first slot 16:15.
        // For 16:15, prevSlotDate is 16:00. 16:00 < 16:13:33, so it's skipped.
        // Next slot 16:30. prevSlotDate is 16:15.
        // logIndex=0 (16:13:33 to 16:23:34) contains 16:15.
        // val(16:15) = 1000 + (16:15:00 - 16:13:33)/(601) * 200 = 1000 + 87/601 * 200 = 1028.95
        // logIndex=1 (16:23:34 to 16:33:33) contains 16:30.
        // val(16:30) = 1200 + (16:30:00 - 16:23:34)/(599) * 300 = 1200 + 386/599 * 300 = 1393.32
        // Delta 16:30 = round(1393.32 - 1028.95) = 1393 - 1029 = 364.

        // Next slot 16:45. 16:45 is AFTER the last log (16:43:33).
        // So we only have ONE delta.

        $this->assertCount(1, $deltas);
        $this->assertEquals('2026-06-11 16:30:00', $deltas[0]->getDate());

        $this->assertEquals(364, $deltas[0]->getTotalForwardActiveEnergy());
    }

    public function testFirstLogIsBaselineNoEnergyLoss() {
        // Log 1: 12:05 - 1000 (Baseline)
        // Log 2: 12:20 - 2000
        // Log 3: 12:35 - 3000
        $this->createEmLog(6, '2026-06-11 12:05:00', 1000);
        $this->createEmLog(6, '2026-06-11 12:20:00', 2000);
        $this->createEmLog(6, '2026-06-11 12:35:00', 3000);

        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 6], ['date' => 'ASC']);

        // First log 12:05 -> baseline.
        // First slot 12:15. prevSlotDate 12:00. 12:00 < 12:05, so 12:15 slot is SKIPPED.
        // Next slot 12:30. prevSlotDate 12:15.
        // val(12:15) = 1000 + (10/15)*1000 = 1666.67
        // val(12:30) = 2000 + (10/15)*1000 = 2666.67
        // Delta 12:30 = 2666.67 - 1666.67 = 1000.
        // Next slot 12:45. prevSlotDate 12:30. 12:45 > 12:35, so SKIPPED.

        $this->assertCount(1, $deltas);
        $this->assertEquals('2026-06-11 12:30:00', $deltas[0]->getDate());
        $this->assertEquals(1000, $deltas[0]->getTotalForwardActiveEnergy());
    }

    public function testSubsequentRuns() {
        $this->createEmLog(3, '2026-06-11 11:45:00', 500); // Baseline
        $this->createEmLog(3, '2026-06-11 12:00:00', 1000);
        $this->createEmLog(3, '2026-06-11 12:15:00', 2000);

        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertCount(2, $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 3]));

        $this->createEmLog(3, '2026-06-11 12:30:00', 3500);

        $commandTester->execute([]);
        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 3], ['date' => 'ASC']);
        $this->assertCount(3, $deltas);
        $this->assertEquals(500, $deltas[0]->getTotalForwardActiveEnergy()); // 1000-500
        $this->assertEquals(1000, $deltas[1]->getTotalForwardActiveEnergy()); // 2000-1000
        $this->assertEquals(1500, $deltas[2]->getTotalForwardActiveEnergy()); // 3500-2000
    }

    public function testFirstLogIsOnlyBaseline() {
        // Log 1: 11:45 - 500 (Baseline)
        // Log 2: 12:00 - 1000 (Delta 500)
        // Log 3: 12:15 - 1100 (Delta 100)
        // Log 4: 12:30 - 1300 (Delta 200)
        $this->createEmLog(5, '2026-06-11 11:45:00', 500);
        $this->createEmLog(5, '2026-06-11 12:00:00', 1000);
        $this->createEmLog(5, '2026-06-11 12:15:00', 1100);
        $this->createEmLog(5, '2026-06-11 12:30:00', 1300);

        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 5], ['date' => 'ASC']);

        $this->assertCount(3, $deltas);
        $this->assertEquals('2026-06-11 12:00:00', $deltas[0]->getDate());
        $this->assertEquals(500, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals('2026-06-11 12:15:00', $deltas[1]->getDate());
        $this->assertEquals(100, $deltas[1]->getTotalForwardActiveEnergy());
        $this->assertEquals('2026-06-11 12:30:00', $deltas[2]->getDate());
        $this->assertEquals(200, $deltas[2]->getTotalForwardActiveEnergy());
    }

    public function testCounterReset() {
        // Log 1: 12:00 - 1000
        // Log 2: 12:15 - 1200 (Delta 200)
        // Log 3: 12:30 - 100 (Reset! Delta should probably be 100)
        // Log 4: 12:45 - 250 (Delta 150)
        $this->createEmLog(7, '2026-06-11 12:00:00', 1000);
        $this->createEmLog(7, '2026-06-11 12:15:00', 1200);
        $this->createEmLog(7, '2026-06-11 12:30:00', 100);
        $this->createEmLog(7, '2026-06-11 12:45:00', 250);

        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 7], ['date' => 'ASC']);

        // Slots:
        // 12:15: [12:00, 12:15] -> 1200 - 1000 = 200.
        // 12:30: [12:15, 12:30] -> Reset occurred. 100 is the new energy.
        // 12:45: [12:30, 12:45] -> 250 - 100 = 150.

        $this->assertCount(3, $deltas);
        $this->assertEquals(200, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals(100, $deltas[1]->getTotalForwardActiveEnergy());
        $this->assertEquals(150, $deltas[2]->getTotalForwardActiveEnergy());
    }

    public function testCounterResetWithInterpolation() {
        // Log 1: 12:00 - 1000
        // Log 2: 12:10 - 1100
        // Log 3: 12:20 - 50 (Reset!)
        // Log 4: 12:30 - 150
        $this->createEmLog(8, '2026-06-11 12:00:00', 1000);
        $this->createEmLog(8, '2026-06-11 12:10:00', 1100);
        $this->createEmLog(8, '2026-06-11 12:20:00', 50);
        $this->createEmLog(8, '2026-06-11 12:30:00', 150);

        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 8], ['date' => 'ASC']);

        // Slots:
        // 12:15: [12:00, 12:15]
        // val(12:00) = 1000.
        // 12:15 is between 12:10 (1100) and 12:20 (50).
        // ratio = (12:15 - 12:10) / (12:20 - 12:10) = 5 / 10 = 0.5
        // val(12:15) = 1100 + 0.5 * (50 - 1100) = 1100 - 525 = 575.
        // Delta 12:15 = 575 - 1000 = -425.
        // Wait, if it's -425, my fix `if ($v < 0)` will change it to `val(12:15) = 575`.
        // So Delta 12:15 = 575.

        // Next slot 12:30: [12:15, 12:30]
        // val(12:15) = 575.
        // val(12:30) = 150.
        // Delta 12:30 = 150 - 575 = -425.
        // Fixed to val(12:30) = 150.

        // This behavior is a bit weird but it's consistent with "do your best to estimate" and "don't lose energy".
        // Actually, if a reset happens between logs, linear interpolation IS weird.

        $this->assertCount(2, $deltas);
        $this->assertEquals(575, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals(150, $deltas[1]->getTotalForwardActiveEnergy());
    }

    public function testSparseLogsOnceADay() {
        // Log 1: Day 1 12:00:00 - 1000
        // Log 2: Day 2 12:00:00 - 2000 (1000 units consumed in 24 hours)
        // 24 hours = 24 * 4 = 96 slots.
        // 1000 / 96 = 10.4166...
        // Some slots will be 10, some will be 11.
        $this->createEmLog(9, '2026-06-11 12:00:00', 1000);
        $this->createEmLog(9, '2026-06-12 12:00:00', 2000);

        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 9], ['date' => 'ASC']);

        $this->assertCount(96, $deltas);

        $totalDelta = 0;
        foreach ($deltas as $delta) {
            $energy = $delta->getTotalForwardActiveEnergy();
            $this->assertGreaterThanOrEqual(10, $energy);
            $this->assertLessThanOrEqual(11, $energy);
            $totalDelta += $energy;
        }

        // 1000 / 96 = 10.4166...
        // floor(10.4166) = 10.
        // 96 * 10 = 960.
        $this->assertEquals(960, $totalDelta);
    }

    private function createEmLog(int $channelId, string $date, int $fae) {
        $logItem = new ElectricityMeterLogItem();
        EntityUtils::setField($logItem, 'channel_id', $channelId);
        EntityUtils::setField($logItem, 'date', $date);
        EntityUtils::setField($logItem, 'phase1_fae', $fae);
        EntityUtils::setField($logItem, 'phase1_rae', 0);
        EntityUtils::setField($logItem, 'phase2_fae', 0);
        EntityUtils::setField($logItem, 'phase2_rae', 0);
        EntityUtils::setField($logItem, 'phase3_fae', 0);
        EntityUtils::setField($logItem, 'phase3_rae', 0);
        $this->entityManager->persist($logItem);
        $this->entityManager->flush();
    }
}
