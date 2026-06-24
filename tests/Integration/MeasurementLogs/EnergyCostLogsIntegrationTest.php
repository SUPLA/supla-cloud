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
use App\Entity\MeasurementLogs\EnergyTariffProfile;
use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfilePriceItem;
use App\Entity\MeasurementLogs\EnergyTariffProfilePricePeriod;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Entity\MeasurementLogs\EnergyTariffResolvedZone;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Enums\EnergyPriceComponent;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class EnergyCostLogsIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    private ?User $user = null;
    private ?IODeviceChannel $switchingProfileChannel = null;
    private ?IODeviceChannel $monthlyProfileChannel = null;
    private ?IODeviceChannel $plainChannel = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
        ]);
        $this->switchingProfileChannel = $device->getChannels()[0];
        $this->monthlyProfileChannel = $device->getChannels()[1];
        $this->plainChannel = $device->getChannels()[2];

        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();

        $g12Tariff = $this->createTariff($logsEm, 'PL_G12_TEST', 'G12 test', 'UTC', [['code' => 'DAY'], ['code' => 'NIGHT']]);
        $allDayTariff = $this->createTariff($logsEm, 'PL_G11_TEST', 'G11 test', 'UTC', [['code' => 'ALL_DAY']]);
        $logsEm->flush();

        $logsEm->persist(new EnergyTariffResolvedZone(
            $g12Tariff->getId(),
            'DAY',
            new \DateTime('2026-01-10 00:00:00', new \DateTimeZone('UTC')),
            new \DateTime('2026-01-10 00:15:00', new \DateTimeZone('UTC'))
        ));
        $logsEm->persist(new EnergyTariffResolvedZone(
            $g12Tariff->getId(),
            'NIGHT',
            new \DateTime('2026-01-10 00:15:00', new \DateTimeZone('UTC')),
            new \DateTime('2026-01-10 00:30:00', new \DateTimeZone('UTC'))
        ));
        $logsEm->persist(new EnergyTariffResolvedZone(
            $allDayTariff->getId(),
            'ALL_DAY',
            new \DateTime('2026-01-31 00:00:00', new \DateTimeZone('UTC')),
            new \DateTime('2026-02-06 00:00:00', new \DateTimeZone('UTC'))
        ));

        $this->createDeltaLog($logsEm, $this->switchingProfileChannel->getId(), '2026-01-10 00:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->switchingProfileChannel->getId(), '2026-01-10 00:30:00', 0, 200, 0);
        $this->createDeltaLog($logsEm, $this->switchingProfileChannel->getId(), '2026-02-05 12:15:00', 0, 0, 300);

        $this->createDeltaLog($logsEm, $this->monthlyProfileChannel->getId(), '2026-01-31 23:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->monthlyProfileChannel->getId(), '2026-02-01 00:15:00', 100, 0, 0);

        $this->createDeltaLog($logsEm, $this->plainChannel->getId(), '2026-01-10 00:15:00', 150, 0, 0);

        $switchingProfile = new EnergyTariffProfile();
        $switchingProfile->setUserId($this->user->getId());
        $switchingProfile->setName('Switching profile');
        $switchingProfile->addTariffPeriod($this->createTariffPeriod(
            $g12Tariff,
            '2026-01-01 00:00:00',
            '2026-02-01 00:00:00',
            [
                $this->createPricePeriod('January prices', 'PLN', 10, '2026-01-01 00:00:00', '2026-02-01 00:00:00', [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'DAY', 1.0, 'kWh'),
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'NIGHT', 2.0, 'kWh'),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'DAY', 0.1, 'kWh'),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'NIGHT', 0.2, 'kWh'),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_FIXED, null, 10.0, 'month'),
                    $this->createPriceItem(EnergyPriceComponent::FEE_VARIABLE, null, 1.0, 'day'),
                ]),
            ]
        ));
        $switchingProfile->addTariffPeriod($this->createTariffPeriod(
            $allDayTariff,
            '2026-02-01 00:00:00',
            '2026-03-01 00:00:00',
            [
                $this->createPricePeriod('February prices', 'PLN', 1, '2026-02-01 00:00:00', '2026-03-01 00:00:00', [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 0.5, 'kWh'),
                    $this->createPriceItem(EnergyPriceComponent::FEE_FIXED, null, 3.0, 'month'),
                ]),
            ]
        ));
        $logsEm->persist($switchingProfile);

        $monthlyProfile = new EnergyTariffProfile();
        $monthlyProfile->setUserId($this->user->getId());
        $monthlyProfile->setName('Monthly EUR profile');
        $monthlyProfile->addTariffPeriod($this->createTariffPeriod(
            $allDayTariff,
            '2026-01-01 00:00:00',
            null,
            [
                $this->createPricePeriod('All months', 'EUR', 1, '2026-01-01 00:00:00', null, [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 0.4, 'kWh'),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'ALL_DAY', 0.05, 'kWh'),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_FIXED, null, 6.0, 'month'),
                ]),
            ]
        ));
        $logsEm->persist($monthlyProfile);

        $assignmentA = new EnergyTariffProfileAssignment();
        $assignmentA->setChannelId($this->switchingProfileChannel->getId());
        $assignmentA->setProfile($switchingProfile);
        $logsEm->persist($assignmentA);

        $assignmentB = new EnergyTariffProfileAssignment();
        $assignmentB->setChannelId($this->monthlyProfileChannel->getId());
        $assignmentB->setProfile($monthlyProfile);
        $logsEm->persist($assignmentB);

        $logsEm->flush();
    }

    public function testFetchingEnergyCostLogsAcrossTariffPeriods(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->switchingProfileChannel->getId() . '/energy-cost-logs?order=ASC');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(3, $content);

        $this->assertEquals('DAY', $content[0]['zoneCode']);
        $this->assertNotNull($content[0]['profileId']);
        $this->assertNotNull($content[0]['pricePeriodId']);
        $this->assertEquals(100, $content[0]['usage']['totalFae']);
        $this->assertEquals(0.11, $content[0]['costs']['total']);
        $this->assertEquals('PLN', $content[0]['costs']['currency']);
        $this->assertEquals(0.1, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.01, $content[0]['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(0.11, $content[0]['costs']['byZone']['DAY']);
        $this->assertEquals(0.11, $content[0]['costs']['byPhase']['phase1']);

        $this->assertEquals('NIGHT', $content[1]['zoneCode']);
        $this->assertEquals(200, $content[1]['usage']['totalFae']);
        $this->assertEquals(0.44, $content[1]['costs']['total']);
        $this->assertEquals(0.4, $content[1]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.04, $content[1]['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(0.44, $content[1]['costs']['byZone']['NIGHT']);
        $this->assertEquals(0.44, $content[1]['costs']['byPhase']['phase2']);

        $this->assertEquals('ALL_DAY', $content[2]['zoneCode']);
        $this->assertEquals(300, $content[2]['usage']['totalFae']);
        $this->assertEquals(0.15, $content[2]['costs']['total']);
        $this->assertEquals(0.15, $content[2]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.15, $content[2]['costs']['byZone']['ALL_DAY']);
        $this->assertEquals(0.15, $content[2]['costs']['byPhase']['phase3']);
    }

    public function testFetchingEnergyCostSummariesAcrossTwoBillingPeriods(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('2026-01-10 00:00:00 UTC');
        $beforeTimestamp = strtotime('2026-02-10 00:00:00 UTC');
        $client->apiRequestV24(
            'GET',
            '/api/2.4.0/channels/' . $this->switchingProfileChannel->getId() . '/energy-cost-summaries?afterTimestamp=' . $afterTimestamp . '&beforeTimestamp=' . $beforeTimestamp
        );
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);

        $januarySummary = $content[0];
        $this->assertEquals('2026-01-10T00:00:00+00:00', $januarySummary['periodStart']);
        $this->assertEquals('2026-02-10T00:00:00+00:00', $januarySummary['periodEnd']);
        $this->assertEquals('UTC', $januarySummary['timezone']);
        $this->assertEquals(0.3, $januarySummary['usage']['totalKwh']);
        $this->assertEquals(32.55, $januarySummary['costs']['total']);
        $this->assertEquals('PLN', $januarySummary['costs']['currency']);
        $this->assertEquals(0.5, $januarySummary['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.05, $januarySummary['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(10.0, $januarySummary['costs']['byComponent']['DISTRIBUTION_FIXED']);
        $this->assertEquals(22.0, $januarySummary['costs']['byComponent']['FEE_VARIABLE']);
        $this->assertEquals(0.11, $januarySummary['costs']['byZone']['DAY']);
        $this->assertEquals(0.44, $januarySummary['costs']['byZone']['NIGHT']);

        $februarySummary = $content[1];
        $this->assertEquals('2026-02-01T00:00:00+00:00', $februarySummary['periodStart']);
        $this->assertEquals('2026-03-01T00:00:00+00:00', $februarySummary['periodEnd']);
        $this->assertEquals(0.3, $februarySummary['usage']['totalKwh']);
        $this->assertEquals(3.15, $februarySummary['costs']['total']);
        $this->assertEquals(0.15, $februarySummary['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(3.0, $februarySummary['costs']['byComponent']['FEE_FIXED']);
        $this->assertEquals(0.15, $februarySummary['costs']['byZone']['ALL_DAY']);
        $this->assertEquals(0.15, $februarySummary['costs']['byPhase']['phase3']);
    }

    public function testFetchingEnergyCostSummariesForMonthlyProfileCreatesSeparateMonthlySummaries(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('2026-01-31 00:00:00 UTC');
        $beforeTimestamp = strtotime('2026-02-02 00:00:00 UTC');
        $client->apiRequestV24(
            'GET',
            '/api/2.4.0/channels/' . $this->monthlyProfileChannel->getId() . '/energy-cost-summaries?afterTimestamp=' . $afterTimestamp . '&beforeTimestamp=' . $beforeTimestamp
        );
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);

        $janSummary = $content[0];
        $this->assertEquals('2026-01-01T00:00:00+00:00', $janSummary['periodStart']);
        $this->assertEquals('2026-02-01T00:00:00+00:00', $janSummary['periodEnd']);
        $this->assertEquals(0.1, $janSummary['usage']['totalKwh']);
        $this->assertEquals('EUR', $janSummary['costs']['currency']);
        $this->assertEquals(6.045, $janSummary['costs']['total']);
        $this->assertEquals(0.04, $janSummary['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.005, $janSummary['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(6.0, $janSummary['costs']['byComponent']['DISTRIBUTION_FIXED']);

        $febSummary = $content[1];
        $this->assertEquals('2026-02-01T00:00:00+00:00', $febSummary['periodStart']);
        $this->assertEquals('2026-03-01T00:00:00+00:00', $febSummary['periodEnd']);
        $this->assertEquals(0.1, $febSummary['usage']['totalKwh']);
        $this->assertEquals('EUR', $febSummary['costs']['currency']);
        $this->assertEquals(6.045, $febSummary['costs']['total']);
    }

    public function testFetchingLogsWithoutTariffProfileCosts(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->plainChannel->getId() . '/energy-cost-logs?order=ASC');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);
        $this->assertNull($content[0]['profileId']);
        $this->assertNull($content[0]['tariffId']);
        $this->assertNull($content[0]['zoneCode']);
        $this->assertNull($content[0]['pricePeriodId']);
        $this->assertNull($content[0]['costs']);
        $this->assertEquals(150, $content[0]['usage']['totalFae']);
    }

    private function createTariff($logsEm, string $code, string $name, string $timezone, array $zones): EnergyTariff {
        $tariff = new EnergyTariff();
        $tariff->setCode($code);
        $tariff->setName($name);
        $tariff->setConfig(['timezone' => $timezone, 'zones' => $zones]);
        $logsEm->persist($tariff);
        return $tariff;
    }

    private function createTariffPeriod(
        EnergyTariff $tariff,
        string $validFrom,
        ?string $validTo,
        array $pricePeriods
    ): EnergyTariffProfileTariffPeriod {
        $tariffPeriod = new EnergyTariffProfileTariffPeriod();
        $tariffPeriod->setTariff($tariff);
        $tariffPeriod->setValidFrom(new \DateTime($validFrom, new \DateTimeZone('UTC')));
        $tariffPeriod->setValidTo($validTo ? new \DateTime($validTo, new \DateTimeZone('UTC')) : null);
        foreach ($pricePeriods as $pricePeriod) {
            $tariffPeriod->addPricePeriod($pricePeriod);
        }
        return $tariffPeriod;
    }

    private function createPricePeriod(
        string $name,
        string $currency,
        int $billingPeriodStartDay,
        string $validFrom,
        ?string $validTo,
        array $items
    ): EnergyTariffProfilePricePeriod {
        $pricePeriod = new EnergyTariffProfilePricePeriod();
        $pricePeriod->setName($name);
        $pricePeriod->setCurrency($currency);
        $pricePeriod->setBillingPeriodStartDay($billingPeriodStartDay);
        $pricePeriod->setValidFrom(new \DateTime($validFrom, new \DateTimeZone('UTC')));
        $pricePeriod->setValidTo($validTo ? new \DateTime($validTo, new \DateTimeZone('UTC')) : null);
        foreach ($items as $item) {
            $pricePeriod->addItem($item);
        }
        return $pricePeriod;
    }

    private function createPriceItem(
        EnergyPriceComponent $component,
        ?string $zoneCode,
        float $amount,
        string $unit
    ): EnergyTariffProfilePriceItem {
        $item = new EnergyTariffProfilePriceItem();
        $item->setComponentCode($component);
        $item->setZoneCode($zoneCode);
        $item->setAmount($amount);
        $item->setUnit($unit);
        return $item;
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
}
