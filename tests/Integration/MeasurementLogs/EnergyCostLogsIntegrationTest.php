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
use App\Enums\BillingPeriodUnit;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Enums\EnergyPriceComponent;
use App\Enums\EnergyPriceUnit;
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
    private ?IODeviceChannel $quarterlyProfileChannel = null;
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
        $this->quarterlyProfileChannel = $device->getChannels()[1];
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

        $this->createDeltaLog($logsEm, $this->quarterlyProfileChannel->getId(), '2026-01-31 23:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->quarterlyProfileChannel->getId(), '2026-02-01 00:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->quarterlyProfileChannel->getId(), '2026-03-31 23:15:00', 100, 0, 0);

        $this->createDeltaLog($logsEm, $this->plainChannel->getId(), '2026-01-10 00:15:00', 150, 0, 0);

        $switchingProfile = new EnergyTariffProfile();
        $switchingProfile->setUserId($this->user->getId());
        $switchingProfile->setName('Switching profile');
        $switchingProfile->addTariffPeriod($this->createTariffPeriod(
            $g12Tariff,
            '2026-01-01 00:00:00',
            '2026-02-01 00:00:00',
            [
                $this->createPricePeriod('PLN', 1, BillingPeriodUnit::MONTH, '2026-01-10 00:00:00', '2026-02-01 00:00:00', [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'DAY', 1.0, EnergyPriceUnit::KWH),
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'NIGHT', 2.0, EnergyPriceUnit::KWH),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'DAY', 0.1, EnergyPriceUnit::KWH),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'NIGHT', 0.2, EnergyPriceUnit::KWH),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_FIXED, null, 10.0, EnergyPriceUnit::MONTH),
                    $this->createPriceItem(EnergyPriceComponent::FEE_VARIABLE, null, 1.0, EnergyPriceUnit::DAY),
                ]),
            ]
        ));
        $switchingProfile->addTariffPeriod($this->createTariffPeriod(
            $allDayTariff,
            '2026-02-01 00:00:00',
            '2026-03-01 00:00:00',
            [
                $this->createPricePeriod('PLN', 1, BillingPeriodUnit::MONTH, '2026-02-01 00:00:00', '2026-03-01 00:00:00', [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 0.5, EnergyPriceUnit::KWH),
                    $this->createPriceItem(EnergyPriceComponent::FEE_FIXED, null, 3.0, EnergyPriceUnit::PERIOD),
                ]),
            ]
        ));
        $logsEm->persist($switchingProfile);

        $quarterlyProfile = new EnergyTariffProfile();
        $quarterlyProfile->setUserId($this->user->getId());
        $quarterlyProfile->setName('Quarterly EUR profile');
        $quarterlyProfile->addTariffPeriod($this->createTariffPeriod(
            $allDayTariff,
            null,
            null,
            [
                $this->createPricePeriod('EUR', 3, BillingPeriodUnit::MONTH, null, null, [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 0.4, EnergyPriceUnit::KWH),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_VARIABLE, 'ALL_DAY', 0.05, EnergyPriceUnit::KWH),
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_FIXED, null, 6.0, EnergyPriceUnit::MONTH),
                ]),
            ]
        ));
        $logsEm->persist($quarterlyProfile);

        $assignmentA = new EnergyTariffProfileAssignment($this->switchingProfileChannel->getId());
        $assignmentA->setProfile($switchingProfile);
        $logsEm->persist($assignmentA);

        $assignmentB = new EnergyTariffProfileAssignment($this->quarterlyProfileChannel->getId());
        $assignmentB->setProfile($quarterlyProfile);
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
            sprintf(
                "/api/2.4.0/channels/%s/energy-cost-summaries?afterTimestamp=%s&beforeTimestamp=%s",
                $this->switchingProfileChannel->getId(),
                $afterTimestamp,
                $beforeTimestamp
            )
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

    public function testFetchingEnergyCostSummariesForQuarterlyProfileUsesThreeMonthBillingPeriod(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('2026-01-01 00:00:00 UTC');
        $beforeTimestamp = strtotime('2026-04-01 00:00:00 UTC');
        $client->apiRequestV24(
            'GET',
            sprintf(
                "/api/2.4.0/channels/%s/energy-cost-summaries?afterTimestamp=%s&beforeTimestamp=%s",
                $this->quarterlyProfileChannel->getId(),
                $afterTimestamp,
                $beforeTimestamp
            )
        );
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);

        $quarterSummary = $content[0];
        $this->assertEquals('2026-01-01T00:00:00+00:00', $quarterSummary['periodStart']);
        $this->assertEquals('2026-04-01T00:00:00+00:00', $quarterSummary['periodEnd']);
        $this->assertEquals(0.3, $quarterSummary['usage']['totalKwh']);
        $this->assertEquals('EUR', $quarterSummary['costs']['currency']);
        $this->assertEquals(18.09, $quarterSummary['costs']['total']);
        $this->assertEquals(0.08, $quarterSummary['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.01, $quarterSummary['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(18.0, $quarterSummary['costs']['byComponent']['DISTRIBUTION_FIXED']);
    }

    public function testFetchingEnergyCostLogsForOpenStartProfile(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->quarterlyProfileChannel->getId() . '/energy-cost-logs?order=ASC');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(3, $content);
        $this->assertEquals('ALL_DAY', $content[0]['zoneCode']);
        $this->assertEquals(100, $content[0]['usage']['totalFae']);
        $this->assertEquals('EUR', $content[0]['costs']['currency']);
        $this->assertEquals(0.045, $content[0]['costs']['total']);
        $this->assertEquals(0.04, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.005, $content[0]['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
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
        ?string $validFrom,
        ?string $validTo,
        array $pricePeriods
    ): EnergyTariffProfileTariffPeriod {
        $tariffPeriod = new EnergyTariffProfileTariffPeriod();
        $tariffPeriod->setTariff($tariff);
        $tariffPeriod->setValidFrom($validFrom ? new \DateTime($validFrom, new \DateTimeZone('UTC')) : null);
        $tariffPeriod->setValidTo($validTo ? new \DateTime($validTo, new \DateTimeZone('UTC')) : null);
        foreach ($pricePeriods as $pricePeriod) {
            $tariffPeriod->addPricePeriod($pricePeriod);
        }
        return $tariffPeriod;
    }

    private function createPricePeriod(
        string $currency,
        int $billingPeriodLength,
        BillingPeriodUnit $billingPeriodUnit,
        ?string $validFrom,
        ?string $validTo,
        array $items
    ): EnergyTariffProfilePricePeriod {
        $pricePeriod = new EnergyTariffProfilePricePeriod();
        $pricePeriod->setCurrency($currency);
        $pricePeriod->setBillingPeriodLength($billingPeriodLength);
        $pricePeriod->setBillingPeriodUnit($billingPeriodUnit);
        $pricePeriod->setValidFrom($validFrom ? new \DateTime($validFrom, new \DateTimeZone('UTC')) : null);
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
        EnergyPriceUnit $unit
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
