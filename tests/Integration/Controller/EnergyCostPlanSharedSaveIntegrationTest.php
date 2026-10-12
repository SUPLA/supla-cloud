<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\EnergyCost\EnergyCostPlanService;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use Supla\EnergyCostCalculator\Plan\CostPlanStarterCatalog;

class EnergyCostPlanSharedSaveIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    public function testListsAffectedMetersAndCopyOnWriteLeavesOtherAssignmentsUnchanged(): void {
        [$user, $first, $second, $planId] = $this->sharedPlan();
        $client = $this->createAuthenticatedClient($user);
        $client->apiRequestV24('GET', "/api/energy-cost-plans/$planId/channels");
        $this->assertStatusCode(200, $client->getResponse());
        $channels = json_decode($client->getResponse()->getContent(), true);
        $this->assertEqualsCanonicalizing([
            ['id' => $first->getId(), 'caption' => 'Kitchen'],
            ['id' => $second->getId(), 'caption' => 'Garage'],
        ], $channels);

        $configuration = $this->history();
        $client->apiRequestV24('PUT', '/api/channels/' . $first->getId() . '/energy-cost-plan-assignment/configuration', [
            'configuration' => $configuration, 'scope' => 'thisMeter', 'expectedPlanId' => $planId,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotSame($planId, $result['plan']['id']);
        $this->assertSame($configuration, $result['plan']['configuration']);
        $this->assertCount(1, $result['affectedAssignments']);
        $client->apiRequestV24('GET', '/api/channels/' . $second->getId() . '/energy-cost-plan-assignment');
        $this->assertSame($planId, json_decode($client->getResponse()->getContent(), true)['planId']);
        $client->apiRequestV24('GET', "/api/energy-cost-plans/$planId");
        $this->assertCount(1, json_decode($client->getResponse()->getContent(), true)['configuration']['periods']);
    }

    public function testAllMetersPreservesPlanIdentityAndEveryAssignment(): void {
        [$user, $first, $second, $planId] = $this->sharedPlan();
        $client = $this->createAuthenticatedClient($user);
        $client->apiRequestV24('PUT', '/api/channels/' . $first->getId() . '/energy-cost-plan-assignment/configuration', [
            'configuration' => $this->history(), 'scope' => 'allMeters', 'expectedPlanId' => $planId,
            'expectedChannelIds' => [$first->getId(), $second->getId()],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($planId, $result['plan']['id']);
        $this->assertSame('Shared', $result['plan']['name']);
        $this->assertCount(2, $result['affectedAssignments']);
        $this->assertSame([$planId, $planId], array_column($result['affectedAssignments'], 'planId'));
    }

    public function testRejectsStaleSharedScope(): void {
        [$user, $first, , $planId] = $this->sharedPlan();
        $client = $this->createAuthenticatedClient($user);
        $client->apiRequestV24('PUT', '/api/channels/' . $first->getId() . '/energy-cost-plan-assignment/configuration', [
            'configuration' => $this->history(), 'scope' => 'allMeters', 'expectedChannelIds' => [$first->getId()],
        ]);
        $this->assertStatusCode(409, $client->getResponse());
        $client->apiRequestV24('GET', "/api/energy-cost-plans/$planId");
        $this->assertCount(1, json_decode($client->getResponse()->getContent(), true)['configuration']['periods']);
    }

    public function testRemovingOneAssignmentDoesNotDeleteSharedPlan(): void {
        [$user, $first, $second, $planId] = $this->sharedPlan();
        $client = $this->createAuthenticatedClient($user);
        $client->apiRequestV24('DELETE', '/api/channels/' . $first->getId() . '/energy-cost-plan-assignment');
        $this->assertStatusCode(204, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $second->getId() . '/energy-cost-plan-assignment');
        $this->assertSame($planId, json_decode($client->getResponse()->getContent(), true)['planId']);
        $client->apiRequestV24('GET', "/api/energy-cost-plans/$planId");
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testAffectedMeterListIsOwnerScoped(): void {
        [, , , $planId] = $this->sharedPlan();
        $client = $this->createAuthenticatedClient($this->createConfirmedUser('other@supla.org'));
        $client->apiRequestV24('GET', "/api/energy-cost-plans/$planId/channels");
        $this->assertStatusCode(404, $client->getResponse());
    }

    public function testRejectsEmptyCustomDraftAndAcceptsMixedProvidersWithFixedCharges(): void {
        $user = $this->createConfirmedUser();
        $client = $this->createAuthenticatedClient($user);
        $configuration = $this->configuration();
        $configuration['periods'][0]['components'] = [];
        $client->apiRequestV24('POST', '/api/energy-cost-plans', ['name' => 'Custom', 'configuration' => $configuration]);
        $this->assertStatusCode(400, $client->getResponse());
        $catalog = new CostPlanStarterCatalog();
        $configuration['periods'][0]['components'] = array_merge(
            array_values(array_filter(
                $catalog->get('PL.STARTER.ENERGA_OPERATOR.G12')->components,
                fn(array $item): bool => $item['kind'] === 'ENERGY_PURCHASE',
            )),
            array_values(array_filter(
                $catalog->get('PL.STARTER.TAURON_DYSTRYBUCJA.G12')->components,
                fn(array $item): bool => str_starts_with($item['kind'], 'DISTRIBUTION_'),
            )),
        );
        $client->apiRequestV24('POST', '/api/energy-cost-plans', ['name' => 'Custom', 'configuration' => $configuration]);
        $this->assertStatusCode(201, $client->getResponse());
        $this->assertCount(5, json_decode($client->getResponse()->getContent(), true)['configuration']['periods'][0]['components']);
    }

    private function sharedPlan(): array {
        $user = $this->createConfirmedUser();
        $first = $this->meter($user, 'Kitchen');
        $second = $this->meter($user, 'Garage');
        $service = self::getContainer()->get(EnergyCostPlanService::class);
        $plan = $service->create($user, 'Shared', $this->configuration());
        $service->assignToChannel($user, $first, $plan->getId());
        $service->assignToChannel($user, $second, $plan->getId());
        return [$user, $first, $second, (int)$plan->getId()];
    }

    private function meter(User $user, string $caption): IODeviceChannel {
        $channel = $this->createDevice(
            $this->createLocation($user),
            [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]],
        )->getChannels()->first();
        $channel->setCaption($caption);
        $this->getEntityManager()->flush();
        return $channel;
    }

    private function configuration(): array {
        return ['version' => 2, 'currency' => 'PLN', 'timezone' => 'Europe/Warsaw',
            'billingCycles' => [['anchor' => '2026-01-01', 'length' => 1, 'unit' => 'MONTH']],
            'periods' => [[
                'validFrom' => null, 'validTo' => null,
                'components' => (new CostPlanStarterCatalog())->get('PL.STARTER.TAURON_DYSTRYBUCJA.G11')->components,
            ]],
        ];
    }

    private function history(): array {
        $configuration = $this->configuration();
        $boundary = '2026-05-01T00:00:00+02:00';
        $configuration['periods'][0]['validTo'] = $boundary;
        $configuration['periods'][] = [
            'validFrom' => $boundary, 'validTo' => null,
            'components' => (new CostPlanStarterCatalog())->get('PL.STARTER.TAURON_DYSTRYBUCJA.G12')->components,
        ];
        return $configuration;
    }
}
