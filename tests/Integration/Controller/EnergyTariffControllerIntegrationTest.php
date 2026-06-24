<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Enums\EnergyPriceComponent;
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
        $device = $this->createDevice($location, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        $this->channel = $device->getChannels()[0];

        $this->anotherUser = $this->createConfirmedUser('other@supla.org');
        $anotherLocation = $this->createLocation($this->anotherUser);
        $anotherDevice = $this->createDevice($anotherLocation, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        $this->anotherChannel = $anotherDevice->getChannels()[0];

        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $tariff = new EnergyTariff();
        $tariff->setCode('PL_G12');
        $tariff->setName('Polish G12');
        $tariff->setConfig([
            'timezone' => 'Europe/Warsaw',
            'zones' => [
                ['code' => 'DAY'],
                ['code' => 'NIGHT'],
            ],
        ]);
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

    public function testManagingUserScopedProfiles() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/energy-tariff-profiles', $this->createProfilePayload('Winter profile'));
        $this->assertStatusCode(201, $client->getResponse());
        $created = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($this->user->getId(), $created['userId']);
        $this->assertEquals('Winter profile', $created['name']);
        $this->assertCount(1, $created['tariffPeriods']);
        $this->assertCount(2, $created['tariffPeriods'][0]['pricePeriods']);

        $client->apiRequestV24('GET', '/api/energy-tariff-profiles');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertCount(1, json_decode($client->getResponse()->getContent(), true));

        $anotherClient = $this->createAuthenticatedClient($this->anotherUser);
        $anotherClient->apiRequestV24('GET', '/api/energy-tariff-profiles');
        $this->assertStatusCode(200, $anotherClient->getResponse());
        $this->assertCount(0, json_decode($anotherClient->getResponse()->getContent(), true));

        $anotherClient->apiRequestV24('GET', '/api/energy-tariff-profiles/' . $created['id']);
        $this->assertStatusCode(404, $anotherClient->getResponse());

        $payload = $this->createProfilePayload('Spring profile');
        $payload['tariffPeriods'][0]['pricePeriods'][1]['items'] = [
            ['componentCode' => EnergyPriceComponent::FORWARD_ACTIVE_ENERGY->name, 'zoneCode' => 'NIGHT', 'amount' => 0.55, 'unit' => 'kWh', 'currency' => 'PLN'],
        ];
        $client->apiRequestV24('PUT', '/api/energy-tariff-profiles/' . $created['id'], $payload);
        $this->assertStatusCode(200, $client->getResponse());
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Spring profile', $updated['name']);
        $this->assertCount(1, $updated['tariffPeriods']);

        $client->apiRequestV24('DELETE', '/api/energy-tariff-profiles/' . $created['id']);
        $this->assertStatusCode(204, $client->getResponse());
    }

    public function testRejectingInvalidProfileCoverage() {
        $client = $this->createAuthenticatedClient($this->user);
        $payload = $this->createProfilePayload('Invalid profile');
        $payload['tariffPeriods'][0]['pricePeriods'][0]['validTo'] = '2026-01-15 00:00:00';
        $payload['tariffPeriods'][0]['pricePeriods'][1]['validFrom'] = '2026-01-20 00:00:00';
        $client->apiRequestV24('POST', '/api/energy-tariff-profiles', $payload);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testManagingChannelProfileAssignment() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/energy-tariff-profiles', $this->createProfilePayload('Assigned profile'));
        $this->assertStatusCode(201, $client->getResponse());
        $profile = json_decode($client->getResponse()->getContent(), true);

        $client->apiRequestV24('PUT', '/api/channels/' . $this->channel->getId() . '/energy-tariff-profile-assignment', [
            'profileId' => $profile['id'],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $assignment = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($this->channel->getId(), $assignment['channelId']);
        $this->assertEquals($profile['id'], $assignment['profileId']);

        $client->apiRequestV24('GET', '/api/channels/' . $this->channel->getId() . '/energy-tariff-profile-assignment');
        $this->assertStatusCode(200, $client->getResponse());
        $storedAssignment = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($profile['id'], $storedAssignment['profileId']);

        $anotherClient = $this->createAuthenticatedClient($this->anotherUser);
        $anotherClient->apiRequestV24('PUT', '/api/channels/' . $this->anotherChannel->getId() . '/energy-tariff-profile-assignment', [
            'profileId' => $profile['id'],
        ]);
        $this->assertStatusCode(404, $anotherClient->getResponse());

        $client->apiRequestV24('DELETE', '/api/channels/' . $this->channel->getId() . '/energy-tariff-profile-assignment');
        $this->assertStatusCode(204, $client->getResponse());

        $client->apiRequestV24('GET', '/api/channels/' . $this->channel->getId() . '/energy-tariff-profile-assignment');
        $this->assertStatusCode(204, $client->getResponse());
    }

    private function createProfilePayload(string $name): array {
        return [
            'name' => $name,
            'tariffPeriods' => [[
                'tariffId' => $this->tariffId,
                'validFrom' => '2026-01-01 00:00:00',
                'validTo' => '2026-02-01 00:00:00',
                'pricePeriods' => [
                    [
                        'name' => 'January first half',
                        'billingPeriodStartDay' => 1,
                        'validFrom' => '2026-01-01 00:00:00',
                        'validTo' => '2026-01-16 00:00:00',
                        'items' => [
                            ['componentCode' => EnergyPriceComponent::FORWARD_ACTIVE_ENERGY->name, 'zoneCode' => 'DAY', 'amount' => 0.95, 'unit' => 'kWh', 'currency' => 'PLN'],
                            ['componentCode' => EnergyPriceComponent::DISTRIBUTION_FIXED->name, 'zoneCode' => null, 'amount' => 12.12, 'unit' => 'month', 'currency' => 'PLN'],
                        ],
                    ],
                    [
                        'name' => 'January second half',
                        'billingPeriodStartDay' => 1,
                        'validFrom' => '2026-01-16 00:00:00',
                        'validTo' => '2026-02-01 00:00:00',
                        'items' => [
                            ['componentCode' => EnergyPriceComponent::FORWARD_ACTIVE_ENERGY->name, 'zoneCode' => 'NIGHT', 'amount' => 0.65, 'unit' => 'kWh', 'currency' => 'PLN'],
                        ],
                    ],
                ],
            ]],
        ];
    }
}
