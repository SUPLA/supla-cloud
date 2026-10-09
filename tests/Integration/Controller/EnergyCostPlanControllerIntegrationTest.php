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
use Supla\EnergyCostCalculator\Plan\CostPlanStarterCatalog;
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

        $client->apiRequestV24('GET', '/api/energy-tariff-presets/PL.UNKNOWN.G11');

        $this->assertStatusCode(404, $client->getResponse());
        $this->assertSame(
            'Energy tariff preset does not exist.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );
    }

    public function testExposesStarterCatalogueAndSafeNotFound(): void {
        $client = $this->createAuthenticatedClient($this->createConfirmedUser('starters@supla.org'));
        /** @var CostPlanStarterCatalog $catalog */
        $catalog = self::$container->get(CostPlanStarterCatalog::class);

        $client->apiRequestV24('GET', '/api/energy-cost-plan-starters');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame($catalog->starters(), json_decode($client->getResponse()->getContent(), true));

        $starterId = 'PL.STARTER.PGE_DYSTRYBUCJA.G12';
        $client->apiRequestV24('GET', '/api/energy-cost-plan-starters/' . $starterId);
        $this->assertStatusCode(200, $client->getResponse());
        $starter = $catalog->get($starterId);
        $this->assertSame([
            'id' => $starter->id,
            'revision' => $starter->revision,
            'metadata' => $starter->metadata,
            'components' => $starter->components,
        ], json_decode($client->getResponse()->getContent(), true));

        $client->apiRequestV24('GET', '/api/energy-cost-plan-starters/PL.UNKNOWN.G11');
        $this->assertStatusCode(404, $client->getResponse());
        $this->assertSame('Energy cost plan starter does not exist.', json_decode($client->getResponse()->getContent(), true)['message']);
    }

    public function testCreatesUpdatesAndHidesForeignPlans(): void {
        $user = $this->createConfirmedUser('plans@supla.org');
        $client = $this->createAuthenticatedClient($user);
        $configuration = $this->configuration('PL.STARTER.TAURON_DYSTRYBUCJA.G11');

        $client->apiRequestV24('POST', '/api/energy-cost-plans', [
            'name' => 'Home',
            'configuration' => $configuration,
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $plan = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Home', $plan['name']);
        $this->assertSame($configuration, $plan['configuration']);

        $newConfiguration = $this->configuration('PL.STARTER.TAURON_DYSTRYBUCJA.G12');
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
            'Invalid energy cost plan configuration: Version 2 plan requires currency and timezone.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );

        $configuration = $this->configuration('PL.STARTER.TAURON_DYSTRYBUCJA.G11');
        unset($configuration['periods'][0]['validFrom']);
        $client->apiRequestV24('POST', '/api/energy-cost-plans', [
            'name' => 'Home',
            'configuration' => $configuration,
        ]);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame(
            'Invalid energy cost plan configuration: Preset does not cover period 0 component ENERGY_PURCHASE continuously.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );

        $configuration = $this->configuration('PL.STARTER.TAURON_DYSTRYBUCJA.G11');
        $configuration['periods'][0]['components'][0]['presetId'] = 'PL.UNKNOWN.G11';
        $client->apiRequestV24('POST', '/api/energy-cost-plans', [
            'name' => 'Home',
            'configuration' => $configuration,
        ]);
        $this->assertStatusCode(404, $client->getResponse());
        $this->assertSame(
            'Energy tariff preset does not exist.',
            json_decode($client->getResponse()->getContent(), true)['message']
        );
    }

    /** @return array<string, mixed> */
    private function configuration(string $starterId): array {
        $components = (new CostPlanStarterCatalog())->get($starterId)->components;
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
                'components' => $components,
            ]],
        ];
    }
}
