<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 */

namespace App\Tests\Integration\MeasurementLogs;

use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Entity\MeasurementLogs\ElectricityMeterDeltaLogItem;
use App\Entity\MeasurementLogs\EnergyPriceLogItem;
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
use App\Enums\EnergyTariffType;
use App\Model\MeasurementLogs\EnergyTariffDynamicPriceMaterializer;
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
    private ?IODeviceChannel $dynamicProfileChannel = null;
    private ?IODeviceChannel $plainChannel = null;
    private ?IODeviceChannel $priceSwitchProfileChannel = null;
    private ?IODeviceChannel $openEndedProfileChannel = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
        ]);
        $this->switchingProfileChannel = $device->getChannels()[0];
        $this->quarterlyProfileChannel = $device->getChannels()[1];
        $this->dynamicProfileChannel = $device->getChannels()[2];
        $this->plainChannel = $device->getChannels()[3];
        $this->priceSwitchProfileChannel = $device->getChannels()[4];
        $this->openEndedProfileChannel = $device->getChannels()[5];

        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();

        $g12Tariff = $this->createTariff(
            $logsEm,
            'PL_G12_TEST',
            'G12 test',
            'UTC',
            [['code' => 'DAY'], ['code' => 'NIGHT']],
            [
                ['zone' => 'DAY', 'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 'time_ranges' => [['from' => '00:00', 'to' => '00:15']]],
                ['zone' => 'NIGHT', 'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 'time_ranges' => [['from' => '00:15', 'to' => '24:00']]],
            ]
        );
        $allDayTariff = $this->createTariff(
            $logsEm,
            'PL_G11_TEST',
            'G11 test',
            'UTC',
            [['code' => 'ALL_DAY']],
            [
                ['zone' => 'ALL_DAY', 'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 'time_ranges' => [['from' => '00:00', 'to' => '24:00']]],
            ]
        );
        $allDayWideTariff = $this->createTariff(
            $logsEm,
            'PL_G11_WIDE_TEST',
            'G11 wide test',
            'UTC',
            [['code' => 'ALL_DAY']],
            [
                ['zone' => 'ALL_DAY', 'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 'time_ranges' => [['from' => '00:00', 'to' => '24:00']]],
            ]
        );
        $dynamicTariff = $this->createDynamicTariff($logsEm, 'PL_DYNAMIC_TEST', 'Dynamic test', 'UTC', 'fixing1', 'PLN', 0.001);
        $logsEm->flush();

        $this->createDeltaLog($logsEm, $this->switchingProfileChannel->getId(), '2026-01-10 00:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->switchingProfileChannel->getId(), '2026-01-10 00:30:00', 0, 200, 0);
        $this->createDeltaLog($logsEm, $this->switchingProfileChannel->getId(), '2026-02-05 12:15:00', 0, 0, 300);

        $this->createDeltaLog($logsEm, $this->quarterlyProfileChannel->getId(), '2026-01-31 23:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->quarterlyProfileChannel->getId(), '2026-02-01 00:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->quarterlyProfileChannel->getId(), '2026-03-31 23:15:00', 100, 0, 0);

        $this->createDeltaLog($logsEm, $this->dynamicProfileChannel->getId(), '2026-01-10 00:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->dynamicProfileChannel->getId(), '2026-01-10 00:30:00', 0, 200, 0);

        $this->createDeltaLog($logsEm, $this->plainChannel->getId(), '2026-01-10 00:15:00', 150, 0, 0);
        $this->createDeltaLog($logsEm, $this->priceSwitchProfileChannel->getId(), '2026-01-15 12:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->priceSwitchProfileChannel->getId(), '2026-01-16 12:15:00', 100, 0, 0);
        $this->createDeltaLog($logsEm, $this->openEndedProfileChannel->getId(), '2026-02-10 12:15:00', 100, 0, 0);

        $dynamicPriceLogA = new EnergyPriceLogItem(
            new \DateTime('2026-01-10 00:00:00', new \DateTimeZone('UTC')),
            new \DateTime('2026-01-10 00:15:00', new \DateTimeZone('UTC'))
        );
        $dynamicPriceLogA->setFixing1(100.0);
        $logsEm->persist($dynamicPriceLogA);

        $dynamicPriceLogB = new EnergyPriceLogItem(
            new \DateTime('2026-01-10 00:15:00', new \DateTimeZone('UTC')),
            new \DateTime('2026-01-10 00:30:00', new \DateTimeZone('UTC'))
        );
        $dynamicPriceLogB->setFixing1(200.0);
        $logsEm->persist($dynamicPriceLogB);

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
                    $this->createPriceItem(EnergyPriceComponent::FEE_VARIABLE, null, 1.0, EnergyPriceUnit::KWH),
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

        $dynamicProfile = new EnergyTariffProfile();
        $dynamicProfile->setUserId($this->user->getId());
        $dynamicProfile->setName('Dynamic profile');
        $dynamicProfile->addTariffPeriod($this->createTariffPeriod(
            $dynamicTariff,
            null,
            null,
            [
                $this->createPricePeriod('PLN', 1, BillingPeriodUnit::MONTH, null, null, [
                    $this->createPriceItem(EnergyPriceComponent::DISTRIBUTION_FIXED, null, 5.0, EnergyPriceUnit::PERIOD),
                ]),
            ]
        ));
        $logsEm->persist($dynamicProfile);

        $priceSwitchProfile = new EnergyTariffProfile();
        $priceSwitchProfile->setUserId($this->user->getId());
        $priceSwitchProfile->setName('Price switch profile');
        $priceSwitchProfile->addTariffPeriod($this->createTariffPeriod(
            $allDayWideTariff,
            '2026-01-01 00:00:00',
            '2026-02-01 00:00:00',
            [
                $this->createPricePeriod('PLN', 1, BillingPeriodUnit::MONTH, '2026-01-01 00:00:00', '2026-01-16 00:00:00', [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 1.0, EnergyPriceUnit::KWH),
                ]),
                $this->createPricePeriod('PLN', 1, BillingPeriodUnit::MONTH, '2026-01-16 00:00:00', '2026-02-01 00:00:00', [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 2.0, EnergyPriceUnit::KWH),
                ]),
            ]
        ));
        $logsEm->persist($priceSwitchProfile);

        $openEndedProfile = new EnergyTariffProfile();
        $openEndedProfile->setUserId($this->user->getId());
        $openEndedProfile->setName('Open ended profile');
        $openEndedProfile->addTariffPeriod($this->createTariffPeriod(
            $allDayWideTariff,
            '2026-02-01 00:00:00',
            null,
            [
                $this->createPricePeriod('PLN', 1, BillingPeriodUnit::MONTH, '2026-02-01 00:00:00', null, [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 0.75, EnergyPriceUnit::KWH),
                ]),
            ]
        ));
        $logsEm->persist($openEndedProfile);

        $assignmentA = new EnergyTariffProfileAssignment($this->switchingProfileChannel->getId());
        $assignmentA->setProfile($switchingProfile);
        $logsEm->persist($assignmentA);

        $assignmentB = new EnergyTariffProfileAssignment($this->quarterlyProfileChannel->getId());
        $assignmentB->setProfile($quarterlyProfile);
        $logsEm->persist($assignmentB);

        $assignmentC = new EnergyTariffProfileAssignment($this->dynamicProfileChannel->getId());
        $assignmentC->setProfile($dynamicProfile);
        $logsEm->persist($assignmentC);

        $assignmentD = new EnergyTariffProfileAssignment($this->priceSwitchProfileChannel->getId());
        $assignmentD->setProfile($priceSwitchProfile);
        $logsEm->persist($assignmentD);

        $assignmentE = new EnergyTariffProfileAssignment($this->openEndedProfileChannel->getId());
        $assignmentE->setProfile($openEndedProfile);
        $logsEm->persist($assignmentE);

        $logsEm->flush();
        self::getContainer()->get(EnergyTariffDynamicPriceMaterializer::class)->materializeAll();
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
        $this->assertEquals(0.21, $content[0]['costs']['total']);
        $this->assertEquals('PLN', $content[0]['costs']['currency']);
        $this->assertEquals(0.1, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.01, $content[0]['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(0.1, $content[0]['costs']['byComponent']['FEE_VARIABLE']);
        $this->assertEquals(0.21, $content[0]['costs']['byZone']['DAY']);
        $this->assertEquals(0.21, $content[0]['costs']['byPhase']['phase1']);

        $this->assertEquals('NIGHT', $content[1]['zoneCode']);
        $this->assertEquals(200, $content[1]['usage']['totalFae']);
        $this->assertEquals(0.64, $content[1]['costs']['total']);
        $this->assertEquals(0.4, $content[1]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.04, $content[1]['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(0.2, $content[1]['costs']['byComponent']['FEE_VARIABLE']);
        $this->assertEquals(0.64, $content[1]['costs']['byZone']['NIGHT']);
        $this->assertEquals(0.64, $content[1]['costs']['byPhase']['phase2']);

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
        $this->assertEquals(10.85, $januarySummary['costs']['total']);
        $this->assertEquals('PLN', $januarySummary['costs']['currency']);
        $this->assertEquals(0.5, $januarySummary['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.05, $januarySummary['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(10.0, $januarySummary['costs']['byComponent']['DISTRIBUTION_FIXED']);
        $this->assertEquals(0.3, $januarySummary['costs']['byComponent']['FEE_VARIABLE']);
        $this->assertEquals(0.21, $januarySummary['costs']['byZone']['DAY']);
        $this->assertEquals(0.64, $januarySummary['costs']['byZone']['NIGHT']);

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
        $this->assertEquals(18.135, $quarterSummary['costs']['total']);
        $this->assertEquals(0.12, $quarterSummary['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.015, $quarterSummary['costs']['byComponent']['DISTRIBUTION_VARIABLE']);
        $this->assertEquals(18.0, $quarterSummary['costs']['byComponent']['DISTRIBUTION_FIXED']);
    }

    public function testFetchingEnergyCostSummariesForOpenStartProfileWithoutAfterTimestamp(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $beforeTimestamp = strtotime('2026-04-01 00:00:00 UTC');
        $client->apiRequestV24(
            'GET',
            sprintf(
                "/api/2.4.0/channels/%s/energy-cost-summaries?beforeTimestamp=%s",
                $this->quarterlyProfileChannel->getId(),
                $beforeTimestamp
            )
        );
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);
        $this->assertEquals('2026-01-01T00:00:00+00:00', $content[0]['periodStart']);
        $this->assertEquals('2026-04-01T00:00:00+00:00', $content[0]['periodEnd']);
        $this->assertEquals(0.3, $content[0]['usage']['totalKwh']);
        $this->assertEquals(18.135, $content[0]['costs']['total']);
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

    public function testFetchingEnergyCostLogsForDynamicProfile(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->dynamicProfileChannel->getId() . '/energy-cost-logs?order=ASC');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);
        $this->assertNull($content[0]['zoneCode']);
        $this->assertEquals('PLN', $content[0]['costs']['currency']);
        $this->assertEquals(0.01, $content[0]['costs']['total']);
        $this->assertEquals(0.01, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals([], $content[0]['costs']['byZone']);

        $this->assertEquals(0.04, $content[1]['costs']['total']);
        $this->assertEquals(0.04, $content[1]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.04, $content[1]['costs']['byPhase']['phase2']);
    }

    public function testFetchingEnergyCostSummariesForDynamicProfile(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('2026-01-01 00:00:00 UTC');
        $beforeTimestamp = strtotime('2026-02-01 00:00:00 UTC');
        $client->apiRequestV24(
            'GET',
            sprintf(
                '/api/2.4.0/channels/%s/energy-cost-summaries?afterTimestamp=%s&beforeTimestamp=%s',
                $this->dynamicProfileChannel->getId(),
                $afterTimestamp,
                $beforeTimestamp
            )
        );
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);
        $this->assertEquals(0.3, $content[0]['usage']['totalKwh']);
        $this->assertEquals(5.05, $content[0]['costs']['total']);
        $this->assertEquals(0.05, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(5.0, $content[0]['costs']['byComponent']['DISTRIBUTION_FIXED']);
        $this->assertEquals([], $content[0]['costs']['byZone']);
    }

    public function testFetchingEnergyCostLogsSwitchesPricePeriodsWithinSingleTariffPeriod(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->priceSwitchProfileChannel->getId() . '/energy-cost-logs?order=ASC');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);
        $this->assertSame(0.1, $content[0]['costs']['total']);
        $this->assertSame(0.1, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertSame(0.2, $content[1]['costs']['total']);
        $this->assertSame(0.2, $content[1]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
    }

    public function testFetchingEnergyCostSummariesSwitchesPricePeriodsWithinSingleTariffPeriod(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('2026-01-01 00:00:00 UTC');
        $beforeTimestamp = strtotime('2026-02-01 00:00:00 UTC');
        $client->apiRequestV24(
            'GET',
            sprintf(
                '/api/2.4.0/channels/%s/energy-cost-summaries?afterTimestamp=%s&beforeTimestamp=%s',
                $this->priceSwitchProfileChannel->getId(),
                $afterTimestamp,
                $beforeTimestamp
            )
        );
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);
        $this->assertEquals(0.1, $content[0]['costs']['total']);
        $this->assertEquals(0.1, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
        $this->assertEquals(0.2, $content[1]['costs']['total']);
        $this->assertEquals(0.2, $content[1]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
    }

    public function testFetchingEnergyCostLogsForOpenEndedTariffAndPricePeriods(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/2.4.0/channels/' . $this->openEndedProfileChannel->getId() . '/energy-cost-logs?order=ASC');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);
        $this->assertEquals('ALL_DAY', $content[0]['zoneCode']);
        $this->assertEquals(0.075, $content[0]['costs']['total']);
        $this->assertEquals(0.075, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
    }

    public function testFetchingEnergyCostLogsSupportsDescendingOrderLimitAndOffset(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24(
            'GET',
            '/api/2.4.0/channels/' . $this->switchingProfileChannel->getId() . '/energy-cost-logs?order=DESC&limit=2&offset=1'
        );
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);
        $this->assertSame(strtotime('2026-01-10 00:30:00 UTC'), $content[0]['dateTimestamp']);
        $this->assertSame(strtotime('2026-01-10 00:15:00 UTC'), $content[1]['dateTimestamp']);
        $this->assertSame('NIGHT', $content[0]['zoneCode']);
        $this->assertSame('DAY', $content[1]['zoneCode']);
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

    public function testFetchingEnergyCostSummariesAcrossMultipleInternalBatches(): void {
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        $channel = $device->getChannels()[0];

        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $tariff = $this->createTariff(
            $logsEm,
            'PL_BATCH_TEST',
            'Batch test',
            'UTC',
            [['code' => 'ALL_DAY']],
            [
                ['zone' => 'ALL_DAY', 'days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], 'time_ranges' => [['from' => '00:00', 'to' => '24:00']]],
            ]
        );
        $logsEm->flush();

        $profile = new EnergyTariffProfile();
        $profile->setUserId($this->user->getId());
        $profile->setName('Batch profile');
        $profile->addTariffPeriod($this->createTariffPeriod(
            $tariff,
            null,
            null,
            [
                $this->createPricePeriod('PLN', 1, BillingPeriodUnit::YEAR, null, null, [
                    $this->createPriceItem(EnergyPriceComponent::FORWARD_ACTIVE_ENERGY, 'ALL_DAY', 1.0, EnergyPriceUnit::KWH),
                ]),
            ]
        ));
        $logsEm->persist($profile);

        $assignment = new EnergyTariffProfileAssignment($channel->getId());
        $assignment->setProfile($profile);
        $logsEm->persist($assignment);

        $start = new \DateTime('2026-01-01 00:15:00', new \DateTimeZone('UTC'));
        for ($i = 0; $i <= 10000; $i++) {
            $date = clone $start;
            if ($i > 0) {
                $date->modify(sprintf('+%d minutes', $i * 15));
            }
            $this->createDeltaLog($logsEm, $channel->getId(), $date->format('Y-m-d H:i:s'), 100, 0, 0);
        }
        $logsEm->flush();

        $client = $this->createAuthenticatedClient($this->user);
        $beforeTimestamp = strtotime('2027-01-01 00:00:00 UTC');
        $client->apiRequestV24(
            'GET',
            sprintf('/api/2.4.0/channels/%s/energy-cost-summaries?beforeTimestamp=%s', $channel->getId(), $beforeTimestamp)
        );
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertCount(1, $content);
        $this->assertEquals('2026-01-01T00:00:00+00:00', $content[0]['periodStart']);
        $this->assertEquals('2027-01-01T00:00:00+00:00', $content[0]['periodEnd']);
        $this->assertEquals(1000.1, $content[0]['usage']['totalKwh']);
        $this->assertEquals(1000.1, $content[0]['costs']['total']);
        $this->assertEquals(1000.1, $content[0]['costs']['byComponent']['FORWARD_ACTIVE_ENERGY']);
    }

    private function createTariff($logsEm, string $code, string $name, string $timezone, array $zones, array $rules): EnergyTariff {
        $tariff = new EnergyTariff();
        $tariff->setCode($code);
        $tariff->setName($name);
        $tariff->setConfig([
            'type' => EnergyTariffType::ZONED_STATIC->value,
            'timezone' => $timezone,
            'zones' => $zones,
            'rules' => $rules,
        ]);
        $logsEm->persist($tariff);
        return $tariff;
    }

    private function createDynamicTariff(
        $logsEm,
        string $code,
        string $name,
        string $timezone,
        string $source,
        string $currency,
        float $multiplier
    ): EnergyTariff {
        $tariff = new EnergyTariff();
        $tariff->setCode($code);
        $tariff->setName($name);
        $tariff->setConfig([
            'type' => EnergyTariffType::DYNAMIC_15M->value,
            'timezone' => $timezone,
            'dynamicPriceSource' => [
                'source' => $source,
                'currency' => $currency,
                'multiplier' => $multiplier,
            ],
        ]);
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
