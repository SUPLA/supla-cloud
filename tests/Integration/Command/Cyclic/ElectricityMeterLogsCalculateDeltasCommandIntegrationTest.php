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
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

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

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
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
        $this->assertEquals(1333, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals(1500, $deltas[1]->getTotalForwardActiveEnergy());
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

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
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

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
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

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
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

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
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
        // Log 3: 12:30 - 100 (Reset! Should be treated as baseline)
        // Log 4: 12:45 - 250 (Delta 150)
        $this->createEmLog(7, '2026-06-11 12:00:00', 1000);
        $this->createEmLog(7, '2026-06-11 12:15:00', 1200);
        $this->createEmLog(7, '2026-06-11 12:30:00', 100);
        $this->createEmLog(7, '2026-06-11 12:45:00', 250);

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 7], ['date' => 'ASC']);

        // Slots:
        // 12:15: [12:00, 12:15] -> 1200 - 1000 = 200.
        // 12:30: [12:15, 12:30] -> Reset occurred at 12:30 = 0
        // 12:45: [12:30, 12:45] -> 250 - 100 = 150.

        $this->assertCount(3, $deltas);
        $this->assertEquals(200, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals(0, $deltas[1]->getTotalForwardActiveEnergy());
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

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 8], ['date' => 'ASC']);

        // Slot 12:15: [12:00, 12:15]
        // [12:00-12:10]: 1100 - 1000 = 100.
        // [12:10-12:15]: Part of [12:10, 12:20] where reset happened. Should be 0.
        // Total 12:15 = 100.

        // Slot 12:30: [12:15, 12:30]
        // [12:15-12:20]: Part of [12:10, 12:20] where reset happened. Should be 0.
        // [12:20-12:30]: 150 - 50 = 100.
        // Total 12:30 = 100.

        $this->assertCount(2, $deltas);
        $this->assertEquals(100, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals(100, $deltas[1]->getTotalForwardActiveEnergy());
    }

    public function testSparseLogsOnceADay() {
        // Log 1: Day 1 12:00:00 - 1000
        // Log 2: Day 2 12:00:00 - 2000 (1000 units consumed in 24 hours)
        // 24 hours = 24 * 4 = 96 slots.
        // 1000 / 96 = 10.4166...
        // Some slots will be 10, some will be 11.
        $this->createEmLog(9, '2026-06-11 12:00:00', 1000);
        $this->createEmLog(9, '2026-06-12 12:00:00', 2000);

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
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

    public function testConsequentSameLogs() {
        // Log 1: 12:00 - 1000 (Baseline)
        // Log 2: 12:15 - 1000 (Delta 0)
        // Log 3: 12:30 - 1000 (Delta 0)
        // Log 4: 12:45 - 1100 (Delta 100)
        $this->createEmLog(10, '2026-06-11 12:00:00', 1000);
        $this->createEmLog(10, '2026-06-11 12:15:00', 1000);
        $this->createEmLog(10, '2026-06-11 12:30:00', 1000);
        $this->createEmLog(10, '2026-06-11 12:45:00', 1100);

        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => 10], ['date' => 'ASC']);

        $this->assertCount(3, $deltas);
        $this->assertEquals('2026-06-11 12:15:00', $deltas[0]->getDate());
        $this->assertEquals(0, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals('2026-06-11 12:30:00', $deltas[1]->getDate());
        $this->assertEquals(0, $deltas[1]->getTotalForwardActiveEnergy());
        $this->assertEquals('2026-06-11 12:45:00', $deltas[2]->getDate());
        $this->assertEquals(100, $deltas[2]->getTotalForwardActiveEnergy());
    }

    public function testDecreaseProducesZeroDelta() {
        $channelId = 12;
        $this->createEmLog($channelId, '2026-06-11 12:00:00', 1000);
        $this->createEmLog($channelId, '2026-06-11 12:15:00', 1200);
        $this->createEmLog($channelId, '2026-06-11 12:30:00', 1100);
        $this->createEmLog($channelId, '2026-06-11 12:45:00', 1300);

        $this->runDeltaCalculation();

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => $channelId], ['date' => 'ASC']);

        $this->assertCount(3, $deltas);
        $this->assertEquals('2026-06-11 12:15:00', $deltas[0]->getDate());
        $this->assertEquals(200, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals('2026-06-11 12:30:00', $deltas[1]->getDate());
        $this->assertEquals(0, $deltas[1]->getTotalForwardActiveEnergy());
        $this->assertEquals('2026-06-11 12:45:00', $deltas[2]->getDate());
        $this->assertEquals(200, $deltas[2]->getTotalForwardActiveEnergy());
    }

    public function testZeroSourceReadingIsIgnoredBeforeLaterDecreaseAndIncrease() {
        $channelId = 13;
        $this->createEmLog($channelId, '2026-06-11 12:00:00', 1000);
        $this->createEmLog($channelId, '2026-06-11 12:15:00', 1200);
        $this->createEmLog($channelId, '2026-06-11 12:16:00', 0);
        $this->createEmLog($channelId, '2026-06-11 12:30:00', 1000);
        $this->createEmLog($channelId, '2026-06-11 12:45:00', 1200);

        $this->runDeltaCalculation();

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => $channelId], ['date' => 'ASC']);

        $this->assertCount(3, $deltas);
        $this->assertEquals(200, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals(0, $deltas[1]->getTotalForwardActiveEnergy());
        $this->assertEquals(200, $deltas[2]->getTotalForwardActiveEnergy());
    }

    public function testZeroSourceReadingIsIgnoredBeforeLaterIncreases() {
        $channelId = 14;
        $this->createEmLog($channelId, '2026-06-11 12:00:00', 1000);
        $this->createEmLog($channelId, '2026-06-11 12:15:00', 1200);
        $this->createEmLog($channelId, '2026-06-11 12:16:00', 0);
        $this->createEmLog($channelId, '2026-06-11 12:30:00', 1300);
        $this->createEmLog($channelId, '2026-06-11 12:45:00', 1400);

        $this->runDeltaCalculation();

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => $channelId], ['date' => 'ASC']);

        $this->assertCount(3, $deltas);
        $this->assertEquals(200, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals(100, $deltas[1]->getTotalForwardActiveEnergy());
        $this->assertEquals(100, $deltas[2]->getTotalForwardActiveEnergy());
    }

    public function testNullSourceReadingIsIgnoredLikeZero() {
        $channelId = 15;
        $this->createEmLog($channelId, '2026-06-11 12:00:00', 1000);
        $this->createEmLog($channelId, '2026-06-11 12:15:00', 1200);
        $this->createEmLog($channelId, '2026-06-11 12:16:00', null);
        $this->createEmLog($channelId, '2026-06-11 12:30:00', 1300);
        $this->createEmLog($channelId, '2026-06-11 12:45:00', 1400);

        $this->runDeltaCalculation();

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => $channelId], ['date' => 'ASC']);

        $this->assertCount(3, $deltas);
        $this->assertEquals(200, $deltas[0]->getTotalForwardActiveEnergy());
        $this->assertEquals(100, $deltas[1]->getTotalForwardActiveEnergy());
        $this->assertEquals(100, $deltas[2]->getTotalForwardActiveEnergy());
    }

    public function testPhaseAndEnergySums() {
        $channelId = 11;
        $this->createCustomEmLog($channelId, '2026-06-11 12:00:00', [
            'phase1_fae' => 100000,
            'phase1_rae' => 50000,
            'phase1_fre' => 25000,
            'phase1_rre' => 15000,
            'phase2_fae' => 200000,
            'phase2_rae' => 100000,
            'phase2_fre' => 50000,
            'phase2_rre' => 30000,
            'phase3_fae' => 300000,
            'phase3_rae' => 150000,
            'phase3_fre' => 75000,
            'phase3_rre' => 45000,
            'fae_balanced' => 600000,
            'rae_balanced' => 300000,
        ]);
        $this->createCustomEmLog($channelId, '2026-06-11 12:15:00', [
            'phase1_fae' => 200000,
            'phase1_rae' => 100000,
            'phase1_fre' => 50000,
            'phase1_rre' => 30000,
            'phase2_fae' => 400000,
            'phase2_rae' => 200000,
            'phase2_fre' => 100000,
            'phase2_rre' => 60000,
            'phase3_fae' => 600000,
            'phase3_rae' => 300000,
            'phase3_fre' => 150000,
            'phase3_rre' => 90000,
            'fae_balanced' => 900000,
            'rae_balanced' => 450000,
        ]);

        $this->runDeltaCalculation();

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => $channelId], ['date' => 'ASC']);

        $this->assertCount(1, $deltas);
        $delta = $deltas[0];

        $this->assertEquals(100000, EntityUtils::getField($delta, 'phase1_fae'));
        $this->assertEquals(50000, EntityUtils::getField($delta, 'phase1_rae'));
        $this->assertEquals(200000, EntityUtils::getField($delta, 'phase2_fae'));
        $this->assertEquals(100000, EntityUtils::getField($delta, 'phase2_rae'));
        $this->assertEquals(25000, EntityUtils::getField($delta, 'phase1_fre'));
        $this->assertEquals(15000, EntityUtils::getField($delta, 'phase1_rre'));
        $this->assertEquals(50000, EntityUtils::getField($delta, 'phase2_fre'));
        $this->assertEquals(30000, EntityUtils::getField($delta, 'phase2_rre'));
        $this->assertEquals(300000, EntityUtils::getField($delta, 'phase3_fae'));
        $this->assertEquals(150000, EntityUtils::getField($delta, 'phase3_rae'));
        $this->assertEquals(75000, EntityUtils::getField($delta, 'phase3_fre'));
        $this->assertEquals(45000, EntityUtils::getField($delta, 'phase3_rre'));
        $this->assertEquals(300000, $delta->getTotalForwardActiveEnergyBalanced());
        $this->assertEquals(150000, $delta->getTotalReverseActiveEnergyBalanced());

        // Sums
        // FAE: 100000 + 200000 + 300000 = 600000
        // RAE: 50000 + 100000 + 150000 = 300000
        $this->assertEquals(600000, $delta->getTotalForwardActiveEnergy());
        $this->assertEquals(300000, $delta->getTotalReverseActiveEnergy());
        $this->assertEquals(150000, $delta->getTotalForwardReactiveEnergy());
        $this->assertEquals(90000, $delta->getTotalReverseReactiveEnergy());

        // Individual phase sums via getter
        $this->assertEquals(100000, $delta->getTotalForwardActiveEnergy(1));
        $this->assertEquals(200000, $delta->getTotalForwardActiveEnergy(2));
        $this->assertEquals(300000, $delta->getTotalForwardActiveEnergy(3));

        $this->assertEquals(50000, $delta->getTotalReverseActiveEnergy(1));
        $this->assertEquals(100000, $delta->getTotalReverseActiveEnergy(2));
        $this->assertEquals(150000, $delta->getTotalReverseActiveEnergy(3));
        $this->assertEquals(25000, $delta->getTotalForwardReactiveEnergy(1));
        $this->assertEquals(50000, $delta->getTotalForwardReactiveEnergy(2));
        $this->assertEquals(75000, $delta->getTotalForwardReactiveEnergy(3));
        $this->assertEquals(15000, $delta->getTotalReverseReactiveEnergy(1));
        $this->assertEquals(30000, $delta->getTotalReverseReactiveEnergy(2));
        $this->assertEquals(45000, $delta->getTotalReverseReactiveEnergy(3));
    }

    public function testReactiveAndBalancedValuesFollowSameDeltaRules() {
        $channelId = 16;
        $this->createCustomEmLog($channelId, '2026-06-11 12:00:00', [
            'phase1_fre' => 1000,
            'phase1_rre' => 2000,
            'fae_balanced' => 3000,
            'rae_balanced' => 4000,
        ]);
        $this->createCustomEmLog($channelId, '2026-06-11 12:15:00', [
            'phase1_fre' => 1200,
            'phase1_rre' => 2300,
            'fae_balanced' => 3200,
            'rae_balanced' => 4500,
        ]);
        $this->createCustomEmLog($channelId, '2026-06-11 12:30:00', [
            'phase1_fre' => 0,
            'phase1_rre' => null,
            'fae_balanced' => 0,
            'rae_balanced' => null,
        ]);
        $this->createCustomEmLog($channelId, '2026-06-11 12:45:00', [
            'phase1_fre' => 1300,
            'phase1_rre' => 2200,
            'fae_balanced' => 3100,
            'rae_balanced' => 4700,
        ]);

        $this->runDeltaCalculation();

        $deltas = $this->entityManager->getRepository(ElectricityMeterDeltaLogItem::class)->findBy(['channel_id' => $channelId], ['date' => 'ASC']);

        $this->assertCount(3, $deltas);
        $this->assertEquals(200, $deltas[0]->getTotalForwardReactiveEnergy(1));
        $this->assertEquals(300, $deltas[0]->getTotalReverseReactiveEnergy(1));
        $this->assertEquals(200, $deltas[0]->getTotalForwardActiveEnergyBalanced());
        $this->assertEquals(500, $deltas[0]->getTotalReverseActiveEnergyBalanced());

        $this->assertEquals(0, $deltas[1]->getTotalForwardReactiveEnergy(1));
        $this->assertEquals(0, $deltas[1]->getTotalReverseReactiveEnergy(1));
        $this->assertEquals(0, $deltas[1]->getTotalForwardActiveEnergyBalanced());
        $this->assertEquals(0, $deltas[1]->getTotalReverseActiveEnergyBalanced());

        $this->assertEquals(100, $deltas[2]->getTotalForwardReactiveEnergy(1));
        $this->assertEquals(0, $deltas[2]->getTotalReverseReactiveEnergy(1));
        $this->assertEquals(0, $deltas[2]->getTotalForwardActiveEnergyBalanced());
        $this->assertEquals(200, $deltas[2]->getTotalReverseActiveEnergyBalanced());
    }

    public function testSingleInstanceOnly() {
        $lockFactory = new LockFactory(new FlockStore());
        $lock = $lockFactory->createLock('supla-electricity-meter-logs-calculate-deltas');
        $lock->acquire();

        try {
            $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
            $tester = new CommandTester($command);
            $tester->execute([]);

            $this->assertStringContainsString('The command is already running.', $tester->getDisplay());
        } finally {
            $lock->release();
        }
    }

    private function createEmLog(int $channelId, string $date, ?int $fae) {
        $this->createCustomEmLog($channelId, $date, ['phase1_fae' => $fae]);
    }

    private function createCustomEmLog(int $channelId, string $date, array $fields): void {
        $logItem = new ElectricityMeterLogItem();
        EntityUtils::setField($logItem, 'channel_id', $channelId);
        EntityUtils::setField($logItem, 'date', $date);
        foreach (
            [
                'phase1_fae', 'phase1_rae', 'phase1_fre', 'phase1_rre',
                'phase2_fae', 'phase2_rae', 'phase2_fre', 'phase2_rre',
                'phase3_fae', 'phase3_rae', 'phase3_fre', 'phase3_rre',
                'fae_balanced', 'rae_balanced',
            ] as $field
        ) {
            EntityUtils::setField($logItem, $field, array_key_exists($field, $fields) ? $fields[$field] : 0);
        }
        $this->entityManager->persist($logItem);
        $this->entityManager->flush();
    }

    private function runDeltaCalculation(): void {
        $lockFactory = new LockFactory(new FlockStore());
        $command = new ElectricityMeterLogsCalculateDeltasCommand($this->entityManager, $lockFactory);
        EntityUtils::setField($command, 'name', 'supla:cyclic:electricity-meter-logs-calculate-deltas');
        $this->application->add($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }
}
