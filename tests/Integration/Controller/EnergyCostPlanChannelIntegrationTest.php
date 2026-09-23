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
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class EnergyCostPlanChannelIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    public function testAssignsReadsAndDeletesPlanThroughChannelApi(): void {
        $user = $this->createConfirmedUser('channel-api@supla.org');
        $channel = $this->createElectricityMeterChannel($user);
        /** @var EnergyCostPlanService $plans */
        $plans = self::$container->get(EnergyCostPlanService::class);
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
            '/api/channels/' . $channel->getId() . '/energy-cost-calculation?fromTimestamp=1767265200&toTimestamp=1767266100'
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
        $client = $this->createAuthenticatedClient($user);

        $client->apiRequestV24('GET', '/api/channels/' . $channel->getId() . '/energy-cost-calculation?fromTimestamp=10&toTimestamp=10');

        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(
            'fromTimestamp must be lower than toTimestamp.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );
    }

    private function createElectricityMeterChannel(User $user): IODeviceChannel {
        return $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]],
        )->getChannels()->first();
    }

    /** @return array<string, mixed> */
    private function configuration(): array {
        return [
            'version' => 1,
            'entries' => [[
                'presetId' => 'PL.TAURON_DYSTRYBUCJA.G11.2026',
                'values' => [
                    'billingCycle.anchor' => '2026-01-15T00:00:00+01:00',
                    'energy.rate' => '0.71',
                ],
            ]],
        ];
    }
}
