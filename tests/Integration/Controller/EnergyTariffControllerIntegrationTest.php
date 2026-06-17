<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class EnergyTariffControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    private ?User $user;
    private ?User $anotherUser;
    private ?IODeviceChannel $channel;
    private ?IODeviceChannel $anotherChannel;
    private ?int $tariffId;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]]);
        $this->channel = $device->getChannels()[0];

        $this->anotherUser = $this->createConfirmedUser('other@supla.org');
        $anotherLocation = $this->createLocation($this->anotherUser);
        $anotherDevice = $this->createDevice($anotherLocation, [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]]);
        $this->anotherChannel = $anotherDevice->getChannels()[0];

        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $tariff = new EnergyTariff();
        $tariff->setCode('PL_G12');
        $tariff->setName('Polish G12');
        $tariff->setConfig(['timezone' => 'Europe/Warsaw']);
        $logsEm->persist($tariff);
        $logsEm->flush();
        $this->tariffId = $tariff->getId();
    }

    public function testListingTariffs() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/energy-tariffs');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals('PL_G12', $content[0]['code']);
    }

    public function testManagingUserScopedPriceLists() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/energy-tariffs/' . $this->tariffId . '/price-lists', [
            'name' => 'Winter 2026',
            'items' => [
                ['componentCode' => 'ENERGY_ACTIVE_IMPORT', 'zoneCode' => 'DAY', 'amount' => 0.95, 'unit' => 'kWh', 'currency' => 'PLN'],
                ['componentCode' => 'DISTRIBUTION_FIXED', 'zoneCode' => null, 'amount' => 12.12, 'unit' => 'month', 'currency' => 'PLN'],
            ],
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $created = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($this->user->getId(), $created['userId']);
        $this->assertCount(2, $created['items']);

        $client->apiRequestV24('GET', '/api/energy-tariffs/' . $this->tariffId . '/price-lists');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertCount(1, json_decode($client->getResponse()->getContent(), true));

        $anotherClient = $this->createAuthenticatedClient($this->anotherUser);
        $anotherClient->apiRequestV24('GET', '/api/energy-tariffs/' . $this->tariffId . '/price-lists');
        $this->assertStatusCode(200, $anotherClient->getResponse());
        $this->assertCount(0, json_decode($anotherClient->getResponse()->getContent(), true));

        $anotherClient->apiRequestV24('GET', '/api/energy-tariffs/' . $this->tariffId . '/price-lists/' . $created['id']);
        $this->assertStatusCode(404, $anotherClient->getResponse());

        $client->apiRequestV24('PUT', '/api/energy-tariffs/' . $this->tariffId . '/price-lists/' . $created['id'], [
            'name' => 'Spring 2026',
            'items' => [
                ['componentCode' => 'ENERGY_ACTIVE_IMPORT', 'zoneCode' => 'NIGHT', 'amount' => 0.55, 'unit' => 'kWh', 'currency' => 'PLN'],
            ],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Spring 2026', $updated['name']);
        $this->assertCount(1, $updated['items']);

        $client->apiRequestV24('DELETE', '/api/energy-tariffs/' . $this->tariffId . '/price-lists/' . $created['id']);
        $this->assertStatusCode(204, $client->getResponse());
    }

    public function testManagingTariffAssignments() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/channels/' . $this->channel->getId() . '/energy-tariff-assignments', [
            'tariffId' => $this->tariffId,
            'validFrom' => '2026-01-01 00:00:00',
            'validTo' => '2026-03-31 23:59:59',
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $assignment = json_decode($client->getResponse()->getContent(), true);

        $client->apiRequestV24('GET', '/api/channels/' . $this->channel->getId() . '/energy-tariff-assignments');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertCount(1, json_decode($client->getResponse()->getContent(), true));

        $client->apiRequestV24('PUT', '/api/channels/' . $this->channel->getId() . '/energy-tariff-assignments/' . $assignment['id'], [
            'validTo' => null,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertNull($updated['validTo']);

        $client->apiRequestV24('DELETE', '/api/channels/' . $this->channel->getId() . '/energy-tariff-assignments/' . $assignment['id']);
        $this->assertStatusCode(204, $client->getResponse());
    }

    public function testManagingPriceListAssignments() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/energy-tariffs/' . $this->tariffId . '/price-lists', [
            'name' => 'Default list',
            'items' => [
                ['componentCode' => 'ENERGY_ACTIVE_IMPORT', 'zoneCode' => 'DAY', 'amount' => 0.95, 'unit' => 'kWh', 'currency' => 'PLN'],
            ],
        ]);
        $priceList = json_decode($client->getResponse()->getContent(), true);

        $client->apiRequestV24('POST', '/api/channels/' . $this->channel->getId() . '/energy-price-list-assignments', [
            'priceListId' => $priceList['id'],
            'validFrom' => '2026-01-01 00:00:00',
            'validTo' => null,
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $assignment = json_decode($client->getResponse()->getContent(), true);

        $anotherClient = $this->createAuthenticatedClient($this->anotherUser);
        $anotherClient->apiRequestV24('POST', '/api/channels/' . $this->anotherChannel->getId() . '/energy-price-list-assignments', [
            'priceListId' => $priceList['id'],
            'validFrom' => '2026-01-01 00:00:00',
            'validTo' => null,
        ]);
        $this->assertStatusCode(404, $anotherClient->getResponse());

        $client->apiRequestV24('GET', '/api/channels/' . $this->channel->getId() . '/energy-price-list-assignments');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertCount(1, json_decode($client->getResponse()->getContent(), true));

        $client->apiRequestV24('DELETE', '/api/channels/' . $this->channel->getId() . '/energy-price-list-assignments/' . $assignment['id']);
        $this->assertStatusCode(204, $client->getResponse());
    }
}
