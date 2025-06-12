<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Tests\Integration\MeasurementLogs;

use PHPUnit\Framework\Attributes\Depends;
use SuplaBundle\Entity\Main\ChannelValue;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Entity\MeasurementLogs\EnergyPriceLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

/** @small */
class EnergyPriceForecastIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use SuplaAssertions;

    private ?User $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->createLocation($this->user);
    }

    public function testFetchingData() {
        // @codingStandardsIgnoreStart
        SuplaAutodiscoverMock::mockResponse('energy-price-forecast', [
            ['dateFrom' => '2025-06-12T00:00:00+02:00', 'dateTo' => '2025-06-12T00:14:59+02:00', 'rce' => 440.8, 'fixing1' => 436.2],
            ['dateFrom' => '2025-06-12T00:15:00+02:00', 'dateTo' => '2025-06-12T00:29:59+02:00', 'rce' => 440.8],
            ['dateFrom' => '2025-06-12T00:30:00+02:00', 'dateTo' => '2025-06-12T00:44:59+02:00', 'rce' => 440.8],
        ]);
        // @codingStandardsIgnoreEnd
        $output = $this->executeCommand('supla:cyclic:energy-price-forecast-fetch');
        $em = $this->getEntityManager('measurement_logs');
        $logsRepo = $em->getRepository(EnergyPriceLogItem::class);
        $this->assertEquals(3, $logsRepo->count([]));
        /** @var EnergyPriceLogItem $firstLog */
        $firstLog = $logsRepo->find('2025-06-11 22:00:00');
        $this->assertNotNull($firstLog);
        $this->assertEquals(440.8, $firstLog->getRce());
        $this->assertEquals(436.2, $firstLog->getFixing1());
        $this->assertNull($firstLog->getFixing2());
    }

    /** @depends testFetchingData */
    public function testFetchingDataSecondTimeCorrects() {
        // @codingStandardsIgnoreStart
        SuplaAutodiscoverMock::mockResponse('energy-price-forecast', [
            ['dateFrom' => '2025-06-12T00:00:00+02:00', 'dateTo' => '2025-06-12T00:14:59+02:00', 'rce' => 441.8, 'fixing1' => 436.2, 'fixing2' => 448.91],
            ['dateFrom' => '2025-06-12T00:15:00+02:00', 'dateTo' => '2025-06-12T00:29:59+02:00', 'rce' => 440.8, 'fixing1' => 436.2, 'fixing2' => 448.91],
            ['dateFrom' => '2025-06-12T00:30:00+02:00', 'dateTo' => '2025-06-12T00:44:59+02:00', 'rce' => 440.8],
        ]);
        // @codingStandardsIgnoreEnd
        $this->executeCommand('supla:cyclic:energy-price-forecast-fetch');
        $em = $this->getEntityManager('measurement_logs');
        $logsRepo = $em->getRepository(EnergyPriceLogItem::class);
        $this->assertEquals(3, $logsRepo->count([]));
        /** @var EnergyPriceLogItem $firstLog */
        $firstLog = $logsRepo->find('2025-06-11 22:00:00');
        $this->assertNotNull($firstLog);
        $this->assertEquals(441.8, $firstLog->getRce());
        $this->assertEquals(436.2, $firstLog->getFixing1());
        $this->assertEquals(448.91, $firstLog->getFixing2());
    }

    public function testCreatingVirtualChannelEnergyForecast() {
        // @codingStandardsIgnoreStart
        SuplaAutodiscoverMock::mockResponse('energy-price-forecast', [
            ['dateFrom' => '2025-06-12T00:00:00+02:00', 'dateTo' => '2025-06-12T00:14:59+02:00', 'rce' => 441.8, 'fixing1' => 436.2, 'fixing2' => 448.91],
            ['dateFrom' => '2025-06-12T00:15:00+02:00', 'dateTo' => '2025-06-12T00:29:59+02:00', 'rce' => 442.8, 'fixing1' => 436.2, 'fixing2' => 448.91],
            ['dateFrom' => '2025-06-12T00:30:00+02:00', 'dateTo' => '2025-06-12T00:44:59+02:00', 'rce' => 443.8],
        ]);
        // @codingStandardsIgnoreEnd
        TestTimeProvider::setTime('2025-06-12T00:11:00+02:00');
        $this->executeCommand('supla:cyclic:energy-price-forecast-fetch');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::ENERGY_PRICE_FORECAST,
            'virtualChannelConfig' => [
                'energyField' => 'rce',
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ChannelFunction::GENERAL_PURPOSE_MEASUREMENT, $content['functionId']);
        $this->assertArrayHasKey('virtualChannelConfig', $content['config']);
        $this->assertEquals(VirtualChannelType::ENERGY_PRICE_FORECAST, $content['config']['virtualChannelConfig']['type']);
        $this->assertEquals('rce', $content['config']['virtualChannelConfig']['energyField']);
        $this->assertEquals(1, $this->getEntityManager()->getRepository(IODevice::class)->count([]));
        $chValue = $this->getEntityManager()->getRepository(ChannelValue::class)->findOneBy(['channel' => $content['id']]);
        $this->assertNotNull($chValue);
        $this->assertEquals(441.8, current(unpack('d', $chValue->getValue())));
        return $content['id'];
    }

    #[Depends('testCreatingVirtualChannelEnergyForecast')]
    public function testUpdatingEnergyForecast(int $id) {
        TestTimeProvider::setTime('2025-06-12T00:11:30+02:00');
        $this->executeCommand('supla:cyclic:update-virtual-channels-state');
        $chValue = $this->getEntityManager()->getRepository(ChannelValue::class)->findOneBy(['channel' => $id]);
        $this->assertNotNull($chValue);
        $this->assertEquals(441.8, current(unpack('d', $chValue->getValue())));
        TestTimeProvider::setTime('2025-06-12T00:17:30+02:00');
        $this->executeCommand('supla:cyclic:update-virtual-channels-state');
        $this->getEntityManager()->refresh($chValue);
        $this->assertEquals(442.8, current(unpack('d', $chValue->getValue())));
        TestTimeProvider::setTime('2025-06-12T00:29:55+02:00');
        $this->executeCommand('supla:cyclic:update-virtual-channels-state');
        $this->getEntityManager()->refresh($chValue);
        $this->assertEquals(442.8, current(unpack('d', $chValue->getValue())));
        TestTimeProvider::setTime('2025-06-12T00:30:00+02:00');
        $this->executeCommand('supla:cyclic:update-virtual-channels-state');
        $this->getEntityManager()->refresh($chValue);
        $this->assertEquals(443.8, current(unpack('d', $chValue->getValue())));
    }

    public function testCreatingVirtualChannelEnergyForecastFixingMissing() {
        // @codingStandardsIgnoreStart
        SuplaAutodiscoverMock::mockResponse('energy-price-forecast', [
            ['dateFrom' => '2025-06-12T00:00:00+02:00', 'dateTo' => '2025-06-12T00:14:59+02:00', 'rce' => 441.8],
            ['dateFrom' => '2025-06-12T00:15:00+02:00', 'dateTo' => '2025-06-12T00:29:59+02:00', 'rce' => 442.8],
            ['dateFrom' => '2025-06-12T00:30:00+02:00', 'dateTo' => '2025-06-12T00:44:59+02:00', 'rce' => 443.8],
        ]);
        // @codingStandardsIgnoreEnd
        TestTimeProvider::setTime('2025-06-12T00:11:00+02:00');
        $this->executeCommand('supla:cyclic:energy-price-forecast-fetch');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::ENERGY_PRICE_FORECAST,
            'virtualChannelConfig' => [
                'energyField' => 'fixing1',
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ChannelFunction::GENERAL_PURPOSE_MEASUREMENT, $content['functionId']);
        $this->assertArrayHasKey('virtualChannelConfig', $content['config']);
        $this->assertEquals(VirtualChannelType::ENERGY_PRICE_FORECAST, $content['config']['virtualChannelConfig']['type']);
        $this->assertEquals('fixing1', $content['config']['virtualChannelConfig']['energyField']);
        $this->assertEquals(1, $this->getEntityManager()->getRepository(IODevice::class)->count([]));
        $chValue = $this->getEntityManager()->getRepository(ChannelValue::class)->findOneBy(['channel' => $content['id']]);
        $this->assertNotNull($chValue);
        $this->assertEquals(0, current(unpack('d', $chValue->getValue())));
        $this->assertLessThanOrEqual($chValue->getValidTo()->getTimestamp(), strtotime('2025-06-12T00:11:00+02:00'));
    }
}
