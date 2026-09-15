<?php

namespace App\Tests\Integration\MeasurementLogs;

use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffProfile;
use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfilePriceItem;
use App\Entity\MeasurementLogs\EnergyTariffProfilePricePeriod;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Enums\BillingPeriodUnit;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Enums\EnergyPriceComponent;
use App\Enums\EnergyPriceUnit;
use App\Model\MeasurementLogs\EnergyCostLogHydrator;
use App\Model\MeasurementLogs\EnergyCostRowFetcher;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\SuplaApiHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

/** @small */
class TariffCostScenariosIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    private const DEFINITIONS_FILE = __DIR__ . '/../../../src/DataFixtures/tariff-definitions.json';
    private const SCENARIOS_DIRECTORY = __DIR__ . '/fixtures/predefined-tariff-cost-scenarios';

    private ?User $user = null;
    private ?IODeviceChannel $channel = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        $this->channel = $device->getChannels()[0];
    }

    public static function predefinedTariffCostScenarios(): iterable {
        foreach (glob(self::SCENARIOS_DIRECTORY . '/*.yaml') as $file) {
            yield pathinfo($file, PATHINFO_FILENAME) => [Yaml::parseFile($file)];
        }
    }

    /**
     * @dataProvider predefinedTariffCostScenarios
     */
    public function testPredefinedTariffCostScenario(array $scenario): void {
        $this->createScenarioChannel();
        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $tariff = $this->createTariffFromFixture($logsEm, $scenario['tariff']);
        $this->createProfileAssignment($logsEm, $tariff, $scenario['prices']);
        foreach ($scenario['logs'] as $log) {
            $this->insertRawLog($logsEm, $log);
        }
        $logsEm->flush();

        $this->executeCommand('supla:cyclic:electricity-meter-logs-calculate-deltas');

        $costs = self::getContainer()->get(EnergyCostLogHydrator::class)->hydrateLogs(
            self::getContainer()->get(EnergyCostRowFetcher::class)->fetchCostRows(
                $this->channel->getId(),
                strtotime($scenario['logs'][0]['date'] . ' UTC') - 1,
                strtotime(end($scenario['logs'])['date'] . ' UTC') + 1,
                false,
                100,
                0
            )
        );
        $this->assertCount(count($scenario['expectedCostLogs']), $costs);
        foreach ($scenario['expectedCostLogs'] as $index => $expectedCost) {
            foreach ($expectedCost as $field => $value) {
                if ($field === 'zoneCode') {
                    $this->assertSame($value, $costs[$index]['zoneCode']);
                } elseif ($field === 'dateTimestamp') {
                    $this->assertSame(strtotime($value . ' UTC'), $costs[$index]['dateTimestamp']);
                } elseif ($field === 'cost') {
                    $this->assertEquals($value, $costs[$index]['costs']['total']);
                } else {
                    $this->assertEquals($value, $costs[$index]['usage'][$field]);
                }
            }
        }
    }

    private function createScenarioChannel(): void {
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        $this->channel = $device->getChannels()[0];
    }

    private function createTariffFromFixture(EntityManagerInterface $logsEm, string $code): EnergyTariff {
        $definitions = json_decode(file_get_contents(self::DEFINITIONS_FILE), true);
        $definition = current(array_filter($definitions, fn(array $definition) => $definition['code'] === $code));
        $tariff = new EnergyTariff();
        $tariff->setCode($code);
        $tariff->setName($definition['name']);
        $tariff->setConfig($definition['config']);
        $logsEm->persist($tariff);
        $logsEm->flush();

        return $tariff;
    }

    private function createProfileAssignment(EntityManagerInterface $logsEm, EnergyTariff $tariff, array $prices): void {
        $profile = new EnergyTariffProfile();
        $profile->setUserId($this->user->getId());
        $profile->setName('Tariff scenario');
        $tariffPeriod = new EnergyTariffProfileTariffPeriod();
        $tariffPeriod->setTariff($tariff);
        $tariffPeriod->setValidFrom(new \DateTime('2026-01-01 00:00:00', new \DateTimeZone('UTC')));
        $pricePeriod = new EnergyTariffProfilePricePeriod();
        $pricePeriod->setCurrency('PLN');
        $pricePeriod->setBillingPeriodLength(1);
        $pricePeriod->setBillingPeriodUnit(BillingPeriodUnit::MONTH);
        $pricePeriod->setValidFrom(new \DateTime('2026-01-01 00:00:00', new \DateTimeZone('UTC')));
        foreach ($prices as $zoneCode => $components) {
            foreach ($components as $componentCode => $amount) {
                $pricePeriod->addItem($this->priceItem(EnergyPriceComponent::{$componentCode}, $zoneCode, $amount));
            }
        }
        $tariffPeriod->addPricePeriod($pricePeriod);
        $profile->addTariffPeriod($tariffPeriod);
        $logsEm->persist($profile);
        $assignment = new EnergyTariffProfileAssignment($this->channel->getId());
        $assignment->setProfile($profile);
        $logsEm->persist($assignment);
        $logsEm->flush();
    }

    private function priceItem(EnergyPriceComponent $component, string $zoneCode, float $amount): EnergyTariffProfilePriceItem {
        $item = new EnergyTariffProfilePriceItem();
        $item->setComponentCode($component);
        $item->setZoneCode($zoneCode);
        $item->setAmount($amount);
        $item->setUnit(EnergyPriceUnit::KWH);

        return $item;
    }

    private function insertRawLog(EntityManagerInterface $logsEm, array $values): void {
        $log = new ElectricityMeterLogItem();
        EntityUtils::setField($log, 'channel_id', $this->channel->getId());
        EntityUtils::setField($log, 'date', $values['date']);
        foreach (
            [
                'phase1_fae', 'phase2_fae', 'phase3_fae', 'phase1_rae', 'phase2_rae', 'phase3_rae',
                'phase1_fre', 'phase2_fre', 'phase3_fre', 'phase1_rre', 'phase2_rre', 'phase3_rre',
                'fae_balanced', 'rae_balanced',
            ] as $field
        ) {
            EntityUtils::setField($log, $field, $field === 'phase1_fae' ? $values['forward'] : ($field === 'phase1_rae' ? $values['reverse'] : 0));
        }
        $logsEm->persist($log);
    }
}
