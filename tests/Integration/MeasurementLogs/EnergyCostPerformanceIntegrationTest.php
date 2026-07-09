<?php

namespace App\Tests\Integration\MeasurementLogs;

use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Entity\MeasurementLogs\ElectricityMeterDeltaLogItem;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffProfile;
use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfilePriceItem;
use App\Entity\MeasurementLogs\EnergyTariffProfilePricePeriod;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Entity\MeasurementLogs\EnergyTariffResolvedZone;
use App\Enums\BillingPeriodUnit;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Enums\EnergyPriceComponent;
use App\Enums\EnergyPriceUnit;
use App\Model\MeasurementLogs\EnergyCostLogHydrator;
use App\Model\MeasurementLogs\EnergyCostRowFetcher;
use App\Model\MeasurementLogs\EnergyCostSummaryBuilder;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\TestTimeProvider;
use App\Tests\Integration\Traits\UserFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Large;

#[Large]
class EnergyCostPerformanceIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    private const DEFINITIONS_FILE = __DIR__ . '/../../../src/DataFixtures/tariff-definitions.json';
    private const RAW_LOG_START = '2026-01-01 00:00:00';
    private const RAW_LOG_END = '2027-01-01 00:00:00';
    private const HOURLY_INCREMENT = 400;

    private ?User $user = null;
    private ?IODeviceChannel $channel = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        $this->channel = $device->getChannels()[0];
    }

    public function testProfilingOneYearHistoryWithG13Tariff(): void {
        ini_set('memory_limit', '768M');

        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $channelId = $this->channel->getId();

        $rawInsertMs = $this->measureMilliseconds(fn() => $this->insertHourlyLogs($logsEm, $channelId));
        $rawLogCount = (int)$logsEm->getRepository(ElectricityMeterLogItem::class)->count(['channel_id' => $channelId]);
        $this->assertSame(8761, $rawLogCount);

        $deltaCalculationMs = $this->measureMilliseconds(fn() => $this->executeCommand('supla:cyclic:electricity-meter-logs-calculate-deltas'));
        $deltaLogCount = (int)$logsEm->getRepository(ElectricityMeterDeltaLogItem::class)->count(['channel_id' => $channelId]);
        $this->assertSame(35040, $deltaLogCount);

        $tariff = $this->createTariffFromFixture($logsEm, 'PL_G13_TAURON', 'Polish G13 Tauron');
        $profile = $this->createG13Profile($logsEm, $tariff);
        $assignment = new EnergyTariffProfileAssignment($channelId);
        $assignment->setProfile($profile);
        $logsEm->persist($assignment);
        $logsEm->flush();

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $holidayGenerationMs = $this->measureMilliseconds(fn() => $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1'));
        $tariffResolutionMs = $this->measureMilliseconds(fn() => $this->executeCommand(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=12 --tariff-code=PL_G13_TAURON --start-date="2026-01-01 00:00:00"'
        ));
        $resolvedZoneCount = (int)$logsEm->getRepository(EnergyTariffResolvedZone::class)->count(['tariffId' => $tariff->getId()]);
        $this->assertGreaterThan(1000, $resolvedZoneCount);

        $afterTimestamp = strtotime(self::RAW_LOG_START . ' UTC');
        $summaryBeforeTimestamp = strtotime(self::RAW_LOG_END . ' UTC');
        $rowFetchBeforeTimestamp = $summaryBeforeTimestamp + 1;

        /** @var EnergyCostRowFetcher $rowFetcher */
        $rowFetcher = self::getContainer()->get(EnergyCostRowFetcher::class);
        /** @var EnergyCostLogHydrator $hydrator */
        $hydrator = self::getContainer()->get(EnergyCostLogHydrator::class);
        /** @var EnergyCostSummaryBuilder $summaryBuilder */
        $summaryBuilder = self::getContainer()->get(EnergyCostSummaryBuilder::class);

        $allRows = [];
        $rowFetchMs = $this->measureMilliseconds(function () use ($rowFetcher, $channelId, $afterTimestamp, $rowFetchBeforeTimestamp, &$allRows): void {
            $rowFetcher->forEachCostRowBatch($channelId, $afterTimestamp, $rowFetchBeforeTimestamp, function (array $rows) use (&$allRows): void {
                array_push($allRows, ...$rows);
            });
        });
        $joinedRowCount = count($allRows);
        $this->assertGreaterThan($deltaLogCount, $joinedRowCount);

        $hydratedLogs = [];
        $hydrationMs = $this->measureMilliseconds(function () use ($hydrator, $allRows, &$hydratedLogs): void {
            $hydratedLogs = $hydrator->hydrateLogs($allRows);
        });
        $hydratedLogCount = count($hydratedLogs);
        $this->assertSame($deltaLogCount, $hydratedLogCount);

        $summaries = [];
        $summaryBuildMs = $this->measureMilliseconds(function () use (
            $summaryBuilder,
            $channelId,
            $afterTimestamp,
            $summaryBeforeTimestamp,
            $rowFetcher,
            &
            $summaries
        ): void {
            $summaries = $summaryBuilder->buildSummaries($channelId, $afterTimestamp, $summaryBeforeTimestamp, $rowFetcher);
        });
        $this->assertGreaterThanOrEqual(12, count($summaries));

        fwrite(STDERR, sprintf(
            "\nEnergy cost performance profile\n%s\n",
            json_encode([
                'rawLogCount' => $rawLogCount,
                'deltaLogCount' => $deltaLogCount,
                'resolvedZoneCount' => $resolvedZoneCount,
                'joinedRowCount' => $joinedRowCount,
                'hydratedLogCount' => $hydratedLogCount,
                'rowFanoutRatio' => round($joinedRowCount / max($hydratedLogCount, 1), 2),
                'timingsMs' => [
                    'insertRawLogs' => $rawInsertMs,
                    'calculateDeltas' => $deltaCalculationMs,
                    'generateHolidays' => $holidayGenerationMs,
                    'resolveTariffZones' => $tariffResolutionMs,
                    'fetchJoinedCostRows' => $rowFetchMs,
                    'hydrateCostLogs' => $hydrationMs,
                    'buildSummaries' => $summaryBuildMs,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        ));
    }

    private function insertHourlyLogs(EntityManagerInterface $logsEm, int $channelId): void {
        $current = new \DateTime(self::RAW_LOG_START, new \DateTimeZone('UTC'));
        $limit = new \DateTime(self::RAW_LOG_END, new \DateTimeZone('UTC'));
        $counter = 0;
        $batchSize = 500;

        while ($current <= $limit) {
            $log = new ElectricityMeterLogItem();
            EntityUtils::setField($log, 'channel_id', $channelId);
            EntityUtils::setField($log, 'date', $current->format('Y-m-d H:i:s'));
            EntityUtils::setField($log, 'phase1_fae', $counter * self::HOURLY_INCREMENT);
            EntityUtils::setField($log, 'phase1_rae', 0);
            EntityUtils::setField($log, 'phase2_fae', 0);
            EntityUtils::setField($log, 'phase2_rae', 0);
            EntityUtils::setField($log, 'phase3_fae', 0);
            EntityUtils::setField($log, 'phase3_rae', 0);
            EntityUtils::setField($log, 'phase1_fre', 0);
            EntityUtils::setField($log, 'phase1_rre', 0);
            EntityUtils::setField($log, 'phase2_fre', 0);
            EntityUtils::setField($log, 'phase2_rre', 0);
            EntityUtils::setField($log, 'phase3_fre', 0);
            EntityUtils::setField($log, 'phase3_rre', 0);
            EntityUtils::setField($log, 'fae_balanced', 0);
            EntityUtils::setField($log, 'rae_balanced', 0);
            $logsEm->persist($log);

            $counter++;
            if ($counter % $batchSize === 0) {
                $logsEm->flush();
                $logsEm->clear();
            }

            $current->modify('+1 hour');
        }

        $logsEm->flush();
        $logsEm->clear();
    }

    private function createTariffFromFixture(EntityManagerInterface $logsEm, string $code, string $name): EnergyTariff {
        $definitions = json_decode(file_get_contents(self::DEFINITIONS_FILE), true) ?: [];
        $definition = current(array_values(array_filter($definitions, fn(array $definition) => $definition['code'] === $code)));

        $tariff = new EnergyTariff();
        $tariff->setCode($code);
        $tariff->setName($name);
        $tariff->setConfig($definition['config'] ?? []);
        $logsEm->persist($tariff);
        $logsEm->flush();

        return $tariff;
    }

    private function createG13Profile(EntityManagerInterface $logsEm, EnergyTariff $tariff): EnergyTariffProfile {
        $profile = new EnergyTariffProfile();
        $profile->setUserId($this->user->getId());
        $profile->setName('G13 benchmark profile');

        $tariffPeriod = new EnergyTariffProfileTariffPeriod();
        $tariffPeriod->setTariff($tariff);

        $pricePeriod = new EnergyTariffProfilePricePeriod();
        $pricePeriod->setCurrency('PLN');
        $pricePeriod->setBillingPeriodLength(1);
        $pricePeriod->setBillingPeriodUnit(BillingPeriodUnit::MONTH);

        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'OFF_PEAK', 0.60, EnergyPriceUnit::KWH));
        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'MORNING_PEAK', 0.90, EnergyPriceUnit::KWH));
        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'AFTERNOON_PEAK', 1.10, EnergyPriceUnit::KWH));
        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'OFF_PEAK', 0.18, EnergyPriceUnit::KWH));
        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'MORNING_PEAK', 0.23, EnergyPriceUnit::KWH));
        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'AFTERNOON_PEAK', 0.27, EnergyPriceUnit::KWH));
        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::FEE_VARIABLE, null, 0.12, EnergyPriceUnit::KWH));
        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_FIXED, null, 14.0, EnergyPriceUnit::MONTH));
        $pricePeriod->addItem($this->createPriceItem(EnergyPriceComponent::FEE_FIXED, null, 3.0, EnergyPriceUnit::PERIOD));

        $tariffPeriod->addPricePeriod($pricePeriod);
        $profile->addTariffPeriod($tariffPeriod);
        $logsEm->persist($profile);
        $logsEm->flush();

        return $profile;
    }

    private function createPriceItem(
        EnergyPriceComponent $componentCode,
        ?string $zoneCode,
        float $amount,
        EnergyPriceUnit $unit
    ): EnergyTariffProfilePriceItem {
        $item = new EnergyTariffProfilePriceItem();
        $item->setComponentCode($componentCode);
        $item->setZoneCode($zoneCode);
        $item->setAmount($amount);
        $item->setUnit($unit);

        return $item;
    }

    private function measureMilliseconds(callable $callback): float {
        $start = hrtime(true);
        $callback();
        return round((hrtime(true) - $start) / 1000000, 2);
    }
}
