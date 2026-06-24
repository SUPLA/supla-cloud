<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 */

namespace App\Tests\Integration\MeasurementLogs;

use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Entity\MeasurementLogs\ElectricityMeterDeltaLogItem;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffAssignment;
use App\Entity\MeasurementLogs\EnergyTariffPriceList;
use App\Entity\MeasurementLogs\EnergyTariffPriceListAssignment;
use App\Entity\MeasurementLogs\EnergyTariffPriceListItem;
use App\Entity\MeasurementLogs\EnergyTariffResolvedZone;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class EnergyCostLogsIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    private ?User $user = null;
    private ?IODeviceChannel $costedChannel = null;
    private ?IODeviceChannel $plainChannel = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
        ]);
        $this->costedChannel = $device->getChannels()[0];
        $this->plainChannel = $device->getChannels()[1];

        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $this->createDeltaLog($logsEm, $this->costedChannel->getId(), '2026-01-10 00:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->costedChannel->getId(), '2026-01-10 00:30:00', 0, 200, 0);
        $this->createDeltaLog($logsEm, $this->plainChannel->getId(), '2026-01-10 00:15:00', 150, 0, 0);

        $tariff = new EnergyTariff();
        $tariff->setCode('PL_TEST');
        $tariff->setName('Test Tariff');
        $tariff->setConfig(['timezone' => 'UTC']);
        $logsEm->persist($tariff);
        $logsEm->flush();

        $dayZone = new EnergyTariffResolvedZone($tariff->getId(), 'DAY', new \DateTime('2026-01-10 00:00:00', new \DateTimeZone('UTC')), new \DateTime('2026-01-10 00:15:00', new \DateTimeZone('UTC')));
        $nightZone = new EnergyTariffResolvedZone($tariff->getId(), 'NIGHT', new \DateTime('2026-01-10 00:15:00', new \DateTimeZone('UTC')), new \DateTime('2026-01-10 00:30:00', new \DateTimeZone('UTC')));
        $logsEm->persist($dayZone);
        $logsEm->persist($nightZone);

        $tariffAssignment = new EnergyTariffAssignment();
        $tariffAssignment->setChannelId($this->costedChannel->getId());
        $tariffAssignment->setTariff($tariff);
        $tariffAssignment->setValidFrom(new \DateTime('2026-01-01 00:00:00', new \DateTimeZone('UTC')));
        $logsEm->persist($tariffAssignment);

        $priceList = new EnergyTariffPriceList();
        $priceList->setTariff($tariff);
        $priceList->setUserId($this->user->getId());
        $priceList->setName('Test price list');
        $priceList->setBillingPeriodStartDay(10);
        $priceList->addItem($this->createPriceItem('ENERGY_ACTIVE_IMPORT', 'DAY', 1.0, 'kWh'));
        $priceList->addItem($this->createPriceItem('ENERGY_ACTIVE_IMPORT', 'NIGHT', 2.0, 'kWh'));
        $priceList->addItem($this->createPriceItem('DISTRIBUTION_VARIABLE', 'DAY', 0.1, 'kWh'));
        $priceList->addItem($this->createPriceItem('DISTRIBUTION_VARIABLE', 'NIGHT', 0.2, 'kWh'));
        $priceList->addItem($this->createPriceItem('DISTRIBUTION_FIXED', null, 10.0, 'month'));
        $priceList->addItem($this->createPriceItem('FEE', null, 1.0, 'day'));
        $logsEm->persist($priceList);

        $priceAssignment = new EnergyTariffPriceListAssignment();
        $priceAssignment->setChannelId($this->costedChannel->getId());
        $priceAssignment->setPriceList($priceList);
        $priceAssignment->setValidFrom(new \DateTime('2026-01-01 00:00:00', new \DateTimeZone('UTC')));
        $logsEm->persist($priceAssignment);
        $logsEm->flush();
    }

    public function testFetchingEnergyCostLogs() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->costedChannel->getId() . '/energy-cost-logs?order=ASC');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);
        $this->assertEquals('DAY', $content[0]['zoneCode']);
        $this->assertEquals(100, $content[0]['usage']['totalFae']);
        $this->assertEquals(0.11, $content[0]['costs']['total']);
        $this->assertEquals(0.1, $content[0]['costs']['byComponent']['ENERGY_ACTIVE_IMPORT']);
        $this->assertEquals(0.01, $content[0]['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(0.11, $content[0]['costs']['byZone']['DAY']);
        $this->assertEquals(0.11, $content[0]['costs']['byPhase']['phase1']);

        $this->assertEquals('NIGHT', $content[1]['zoneCode']);
        $this->assertEquals(200, $content[1]['usage']['totalFae']);
        $this->assertEquals(0.44, $content[1]['costs']['total']);
        $this->assertEquals(0.4, $content[1]['costs']['byComponent']['ENERGY_ACTIVE_IMPORT']);
        $this->assertEquals(0.04, $content[1]['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(0.44, $content[1]['costs']['byZone']['NIGHT']);
        $this->assertEquals(0.44, $content[1]['costs']['byPhase']['phase2']);
    }

    public function testFetchingEnergyCostSummaries() {
        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('2026-01-10 00:00:00 UTC');
        $beforeTimestamp = strtotime('2026-01-12 00:00:00 UTC');
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->costedChannel->getId() . '/energy-cost-summaries?afterTimestamp=' . $afterTimestamp . '&beforeTimestamp=' . $beforeTimestamp);
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);
        $summary = $content[0];
        $this->assertEquals('2026-01-10T00:00:00+00:00', $summary['periodStart']);
        $this->assertEquals('2026-02-10T00:00:00+00:00', $summary['periodEnd']);
        $this->assertEquals(0.3, $summary['usage']['totalKwh']);
        $this->assertEquals(12.55, $summary['costs']['total']);
        $this->assertEquals(0.5, $summary['costs']['byComponent']['ENERGY_ACTIVE_IMPORT']);
        $this->assertEquals(0.05, $summary['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(10.0, $summary['costs']['byComponent']['DISTRIBUTION_FIXED']);
        $this->assertEquals(2.0, $summary['costs']['byComponent']['FEE']);
        $this->assertEquals(0.11, $summary['costs']['byZone']['DAY']);
        $this->assertEquals(0.44, $summary['costs']['byZone']['NIGHT']);
        $this->assertEquals(0.11, $summary['costs']['byPhase']['phase1']);
        $this->assertEquals(0.44, $summary['costs']['byPhase']['phase2']);
    }

    public function testFetchingLogsWithoutTariffCosts() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->plainChannel->getId() . '/energy-cost-logs');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);
        $this->assertNull($content[0]['tariffId']);
        $this->assertNull($content[0]['zoneCode']);
        $this->assertNull($content[0]['priceListId']);
        $this->assertNull($content[0]['costs']);
        $this->assertEquals(150, $content[0]['usage']['totalFae']);
    }

    public function testFetchingEnergyCostSummariesForG12DayNightPrices() {
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        $channel = $device->getChannels()[0];

        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $this->createDeltaLog($logsEm, $channel->getId(), '2026-01-10 05:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $channel->getId(), '2026-01-10 06:15:00', 100, 0, 0);

        $tariff = new EnergyTariff();
        $tariff->setCode('PL_G12_TEST');
        $tariff->setName('G12 test');
        $tariff->setConfig(['timezone' => 'UTC']);
        $logsEm->persist($tariff);
        $logsEm->flush();

        $logsEm->persist(new EnergyTariffResolvedZone(
            $tariff->getId(),
            'NIGHT',
            new \DateTime('2026-01-10 00:00:00', new \DateTimeZone('UTC')),
            new \DateTime('2026-01-10 06:00:00', new \DateTimeZone('UTC'))
        ));
        $logsEm->persist(new EnergyTariffResolvedZone(
            $tariff->getId(),
            'DAY',
            new \DateTime('2026-01-10 06:00:00', new \DateTimeZone('UTC')),
            new \DateTime('2026-01-11 00:00:00', new \DateTimeZone('UTC'))
        ));

        $tariffAssignment = new EnergyTariffAssignment();
        $tariffAssignment->setChannelId($channel->getId());
        $tariffAssignment->setTariff($tariff);
        $tariffAssignment->setValidFrom(new \DateTime('2026-01-01 00:00:00', new \DateTimeZone('UTC')));
        $logsEm->persist($tariffAssignment);

        $priceList = new EnergyTariffPriceList();
        $priceList->setTariff($tariff);
        $priceList->setUserId($this->user->getId());
        $priceList->setName('G12 prices');
        $priceList->setBillingPeriodStartDay(1);
        $priceList->addItem($this->createPriceItem('ENERGY_ACTIVE_IMPORT', 'DAY', 1.30, 'kWh'));
        $priceList->addItem($this->createPriceItem('ENERGY_ACTIVE_IMPORT', 'NIGHT', 0.50, 'kWh'));
        $logsEm->persist($priceList);

        $priceAssignment = new EnergyTariffPriceListAssignment();
        $priceAssignment->setChannelId($channel->getId());
        $priceAssignment->setPriceList($priceList);
        $priceAssignment->setValidFrom(new \DateTime('2026-01-01 00:00:00', new \DateTimeZone('UTC')));
        $logsEm->persist($priceAssignment);
        $logsEm->flush();

        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('2026-01-10 00:00:00 UTC');
        $beforeTimestamp = strtotime('2026-01-11 00:00:00 UTC');
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $channel->getId() . '/energy-cost-summaries?afterTimestamp=' . $afterTimestamp . '&beforeTimestamp=' . $beforeTimestamp);
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);
        $summary = $content[0];
        $this->assertEquals(0.2, $summary['usage']['totalKwh']);
        $this->assertEquals(0.18, $summary['costs']['total']);
        $this->assertEquals(0.18, $summary['costs']['byComponent']['ENERGY_ACTIVE_IMPORT']);
        $this->assertEquals(0.05, $summary['costs']['byZone']['NIGHT']);
        $this->assertEquals(0.13, $summary['costs']['byZone']['DAY']);
    }

    private function createDeltaLog($logsEm, int $channelId, string $date, int $phase1, int $phase2, int $phase3): void {
        $log = new ElectricityMeterDeltaLogItem($channelId, $date);
        EntityUtils::setField($log, 'phase1_fae', $phase1);
        EntityUtils::setField($log, 'phase2_fae', $phase2);
        EntityUtils::setField($log, 'phase3_fae', $phase3);
        EntityUtils::setField($log, 'phase1_rae', 0);
        EntityUtils::setField($log, 'phase2_rae', 0);
        EntityUtils::setField($log, 'phase3_rae', 0);
        $logsEm->persist($log);
    }

    private function createPriceItem(string $componentCode, ?string $zoneCode, float $amount, string $unit): EnergyTariffPriceListItem {
        $item = new EnergyTariffPriceListItem();
        $item->setComponentCode($componentCode);
        $item->setZoneCode($zoneCode);
        $item->setAmount($amount);
        $item->setUnit($unit);
        $item->setCurrency('PLN');
        return $item;
    }
}
