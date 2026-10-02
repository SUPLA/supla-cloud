<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Controller;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\EnergyCost\EnergyCostPlanService;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use Supla\EnergyCostCalculator\Plan\CostPlanStarterCatalog;

/** @small */
class EnergyCostPlanChannelIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \Doctrine\DBAL\Connection */
    private $measurementLogsConnection;

    protected function initializeDatabaseForTests(): void {
        $this->measurementLogsConnection = self::getContainer()
            ->get(MeasurementLogsEntityManagerProvider::class)
            ->get()
            ->getConnection();
    }

    public function testAssignsReadsAndDeletesPlanThroughChannelApi(): void {
        $user = $this->createConfirmedUser('channel-api@supla.org');
        $channel = $this->createElectricityMeterChannel($user);
        $this->insertHourlyDeltas($channel);
        /** @var EnergyCostPlanService $plans */
        $plans = self::getContainer()->get(EnergyCostPlanService::class);
        $plan = $plans->create($user, 'Home', $this->configuration());
        $client = $this->createAuthenticatedClient($user);
        $path = '/api/channels/' . $channel->getId() . '/energy-cost-plan-assignment';

        $client->apiRequestV24('PUT', $path, ['planId' => (int)$plan->getId()]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame([
            'channelId' => $channel->getId(),
            'planId' => (int)$plan->getId(),
        ], json_decode($client->getResponse()->getContent(), true));

        $client->apiRequestV24('GET', $path);
        $this->assertStatusCode(200, $client->getResponse());

        $client->apiRequestV24(
            'GET',
            '/api/channels/' . $channel->getId() . '/energy-cost-calculation?fromTimestamp=1767265200&toTimestamp=1767268800'
        );
        $this->assertStatusCode(200, $client->getResponse());
        $calculation = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('charges', $calculation);
        $this->assertArrayHasKey('intervals', $calculation);
        $this->assertArrayHasKey('costs', $calculation);

        $client->apiRequestV24('DELETE', $path);
        $this->assertStatusCode(204, $client->getResponse());
    }

    public function testRejectsInvalidCalculationRangeBeforeCalculation(): void {
        $user = $this->createConfirmedUser('range-api@supla.org');
        $channel = $this->createElectricityMeterChannel($user);
        $this->insertHourlyDeltas($channel);
        $client = $this->createAuthenticatedClient($user);

        $client->apiRequestV24('GET', '/api/channels/' . $channel->getId() . '/energy-cost-calculation?fromTimestamp=10&toTimestamp=10');

        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(
            'fromTimestamp must be lower than toTimestamp.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );
    }

    public function testSimulatesTemporaryConfigurationWithoutAssignment(): void {
        $user = $this->createConfirmedUser('simulation-api@supla.org');
        $channel = $this->createElectricityMeterChannel($user);
        $this->insertHourlyDeltas($channel);
        $client = $this->createAuthenticatedClient($user);
        $assignmentPath = '/api/channels/' . $channel->getId() . '/energy-cost-plan-assignment';

        $client->apiRequestV24('GET', $assignmentPath);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame('', $client->getResponse()->getContent());

        $client->apiRequestV24('POST', '/api/channels/' . $channel->getId() . '/energy-cost-calculation', [
            'fromTimestamp' => 1767265200,
            'toTimestamp' => 1767268800,
            'configuration' => $this->configuration(),
        ]);

        $this->assertStatusCode(200, $client->getResponse());
        $calculation = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('costs', $calculation);
        $this->assertArrayHasKey('charges', $calculation);
    }

    public function testCreatesAndReusesTariffPlanWhenAssigningStarter(): void {
        $user = $this->createConfirmedUser('starter-assignment-api@supla.org');
        $channel = $this->createElectricityMeterChannel($user);
        $client = $this->createAuthenticatedClient($user);
        $path = '/api/channels/' . $channel->getId() . '/energy-cost-plan-assignment/from-starter';
        $payload = ['starterId' => 'PL.STARTER.TAURON_DYSTRYBUCJA.G11', 'configuration' => $this->configuration()];

        $client->apiRequestV24('POST', $path, $payload);
        $this->assertStatusCode(200, $client->getResponse());
        $first = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($first['plan']['id'], $first['assignment']['planId']);

        $client->apiRequestV24('POST', $path, $payload);
        $this->assertStatusCode(200, $client->getResponse());
        $second = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($first['plan']['id'], $second['plan']['id']);
    }

    private function createElectricityMeterChannel(User $user): IODeviceChannel {
        return $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]],
        )->getChannels()->first();
    }

    private function insertHourlyDeltas(IODeviceChannel $channel): void {
        foreach (['11:15:00', '11:30:00', '11:45:00', '12:00:00'] as $time) {
            $this->measurementLogsConnection->insert('supla_em_delta_log', [
                'channel_id' => $channel->getId(), 'date' => '2026-01-01 ' . $time,
                'phase1_fae' => 100000, 'phase1_rae' => 0, 'phase2_fae' => 0, 'phase2_rae' => 0,
                'phase3_fae' => 0, 'phase3_rae' => 0, 'phase1_fre' => 0, 'phase1_rre' => 0,
                'phase2_fre' => 0, 'phase2_rre' => 0, 'phase3_fre' => 0, 'phase3_rre' => 0,
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function configuration(): array {
        return [
            'version' => 2,
            'currency' => 'PLN',
            'timezone' => 'Europe/Warsaw',
            'billingCycles' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'anchor' => '2026-01-15',
                'length' => 1,
                'unit' => 'MONTH',
            ]],
            'periods' => [[
                'validFrom' => '2026-01-01T00:00:00+01:00',
                'validTo' => '2027-01-01T00:00:00+01:00',
                'components' => (new CostPlanStarterCatalog())->get('PL.STARTER.TAURON_DYSTRYBUCJA.G11')->components,
            ]],
        ];
    }
}
