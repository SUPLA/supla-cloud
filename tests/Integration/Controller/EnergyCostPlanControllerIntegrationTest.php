<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Controller;

use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use Supla\EnergyCostCalculator\Preset\TariffPresetCatalog;

/** @small */
class EnergyCostPlanControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    public function testExposesPresetCatalogueThroughPackageApi(): void {
        $user = $this->createConfirmedUser('presets@supla.org');
        $client = $this->createAuthenticatedClient($user);

        $client->apiRequestV24('GET', '/api/energy-tariff-presets');

        $this->assertStatusCode(200, $client->getResponse());
        /** @var TariffPresetCatalog $catalog */
        $catalog = self::$container->get(TariffPresetCatalog::class);
        $presets = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($catalog->presets(), $presets);

        $presetId = $presets[0]['id'];
        $client->apiRequestV24('GET', '/api/energy-tariff-presets/' . $presetId);
        $this->assertStatusCode(200, $client->getResponse());
        $preset = $catalog->get($presetId);
        $this->assertSame([
            'id' => $preset->id,
            'revision' => $preset->revision,
            'metadata' => $preset->metadata,
            'document' => $preset->document,
        ], json_decode($client->getResponse()->getContent(), true));
    }

    public function testReturnsSafeNotFoundForUnknownPreset(): void {
        $client = $this->createAuthenticatedClient($this->createConfirmedUser('missing-preset@supla.org'));

        $client->apiRequestV24('GET', '/api/energy-tariff-presets/PL.UNKNOWN.G11.2026');

        $this->assertStatusCode(404, $client->getResponse());
        $this->assertSame(
            'Energy tariff preset does not exist.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );
    }

    public function testCreatesUpdatesAndHidesForeignPlans(): void {
        $user = $this->createConfirmedUser('plans@supla.org');
        $client = $this->createAuthenticatedClient($user);
        $configuration = $this->configuration('PL.TAURON_DYSTRYBUCJA.G11.2026');

        $client->apiRequestV24('POST', '/api/energy-cost-plans', [
            'name' => 'Home',
            'configuration' => $configuration,
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $plan = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Home', $plan['name']);
        $this->assertSame($configuration, $plan['configuration']);

        $newConfiguration = $this->configuration('PL.TAURON_DYSTRYBUCJA.G12.2026');
        $client->apiRequestV24('PUT', '/api/energy-cost-plans/' . $plan['id'], [
            'name' => 'Summer home',
            'configuration' => $newConfiguration,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Summer home', $updated['name']);
        $this->assertSame($newConfiguration, $updated['configuration']);

        $other = $this->createConfirmedUser('other@supla.org');
        $otherClient = $this->createAuthenticatedClient($other);
        $otherClient->apiRequestV24('GET', '/api/energy-cost-plans/' . $plan['id']);
        $this->assertStatusCode(404, $otherClient->getResponse());
    }

    public function testMapsInvalidCostPlanFailuresToSafeResponses(): void {
        $client = $this->createAuthenticatedClient($this->createConfirmedUser('errors@supla.org'));

        $client->apiRequestV24('POST', '/api/energy-cost-plans', [
            'name' => 'Home',
            'configuration' => ['version' => 2],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(
            'Invalid energy cost plan configuration.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );

        $client->apiRequestV24('POST', '/api/energy-cost-plans', [
            'name' => 'Home',
            'configuration' => $this->configuration('PL.UNKNOWN.G11.2026'),
        ]);
        $this->assertStatusCode(404, $client->getResponse());
        $this->assertSame(
            'Energy tariff preset does not exist.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );
    }

    /** @return array<string, mixed> */
    private function configuration(string $presetId): array {
        $components = $presetId === 'PL.TAURON_DYSTRYBUCJA.G12.2026'
            ? [
                ['kind' => 'ENERGY_PURCHASE', 'presetId' => $presetId, 'componentId' => 'energy-purchase',
                    'values' => ['energy.DAY' => '0.98', 'energy.NIGHT' => '0.62']],
                ['kind' => 'DISTRIBUTION_VARIABLE', 'presetId' => $presetId,
                    'componentId' => 'distribution-variable', 'values' => []],
            ]
            : [
                ['kind' => 'ENERGY_PURCHASE', 'presetId' => $presetId, 'componentId' => 'energy-purchase', 'values' => ['energy.rate' => '0.71']],
                ['kind' => 'DISTRIBUTION_VARIABLE', 'presetId' => $presetId, 'componentId' => 'distribution-variable', 'values' => []],
            ];
        return [
            'version' => 2,
            'currency' => 'PLN',
            'timezone' => 'Europe/Warsaw',
            'priceBasis' => 'NET',
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
                'components' => $components,
            ]],
        ];
    }
}
