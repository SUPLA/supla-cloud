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

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\ElectricityMeterLogItem;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\ImpulseCounterLogItem;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\TemperatureLogItem;
use SuplaBundle\Entity\TempHumidityLogItem;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

class ChannelMeasurementLogsControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function setUp() {

        $datestr = '2018-09-15';
        $date = new \DateTime($datestr);
        $oneday = new \DateInterval('P1D');

        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
            [ChannelType::HUMIDITYANDTEMPSENSOR, ChannelFunction::HUMIDITYANDTEMPERATURE],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::GASMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::WATERMETER],
        ]);

        $date = new \DateTime($datestr);
        foreach ([21, 22, 23] as $temperature) {
            $logItem = new TemperatureLogItem();
            EntityUtils::setField($logItem, 'channel_id', 2);
            EntityUtils::setField($logItem, 'date', clone $date);
            EntityUtils::setField($logItem, 'temperature', $temperature);
            $this->getEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        $date = new \DateTime($datestr);
        foreach ([[21, 30], [22, 40], [23, 50]] as $th) {
            $logItem = new TempHumidityLogItem();
            EntityUtils::setField($logItem, 'channel_id', 3);
            EntityUtils::setField($logItem, 'date', clone $date);
            EntityUtils::setField($logItem, 'temperature', $th[0]);
            EntityUtils::setField($logItem, 'humidity', $th[1]);
            $this->getEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        $date = new \DateTime($datestr);
        foreach ([854800, 854900, 855000] as $energy) {
            $logItem = new ElectricityMeterLogItem();
            EntityUtils::setField($logItem, 'channel_id', 4);
            EntityUtils::setField($logItem, 'date', clone $date);

            foreach ([1, 2, 3] as $phase) {
                EntityUtils::setField($logItem, 'phase' . $phase . '_fae', $energy + $phase);
                EntityUtils::setField($logItem, 'phase' . $phase . '_rae', $energy + $phase + 10);
                EntityUtils::setField($logItem, 'phase' . $phase . '_fre', $energy + $phase + 20);
                EntityUtils::setField($logItem, 'phase' . $phase . '_rre', $energy + $phase + 30);
            }

            $this->getEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        $date = new \DateTime($datestr);
        foreach ([100, 200, 300] as $impulses) {
            foreach ([1, 2, 3] as $num) {
                $logItem = new ImpulseCounterLogItem();

                EntityUtils::setField($logItem, 'channel_id', 4+$num);
                EntityUtils::setField($logItem, 'date', clone $date);
                EntityUtils::setField($logItem, 'counter', $impulses);
                EntityUtils::setField($logItem, 'calculated_value', $impulses);

                $this->getEntityManager()->persist($logItem);
            }
            $date->add($oneday);
        }

        $this->getEntityManager()->flush();
    }

    private function ensureMeasurementLogsCount(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/' . $channelId . '/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertTrue($response->headers->has('X-Total-Count'));
        $this->assertEquals(3, $response->headers->get('X-Total-Count'));
    }

    public function testGettingTemperatureLogsCount() {
        $this->ensureMeasurementLogsCount(2);
    }

    public function testGettingTemperatureAndHumidityLogsCount() {
        $this->ensureMeasurementLogsCount(3);
    }

    public function testGettingElectricityMeasurementsLogsCount() {
        $this->ensureMeasurementLogsCount(4);
    }

    public function testGettingElectricityImpulsesLogsCount() {
        $this->ensureMeasurementLogsCount(5);
    }

    public function testGettingGasImpulsesLogsCount() {
        $this->ensureMeasurementLogsCount(6);
    }

    public function testGettingWaterImpulsesLogsCount() {
        $this->ensureMeasurementLogsCount(7);
    }

    public function testGettingTemperatureLogsCountObsolete() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequest('GET', '/api/channels/2/temperature-log-count');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(3, $content['count']);
    }

    public function testGettingTemperatureAnHumidityLogsCountObsolete() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequest('GET', '/api/channels/3/temperature-and-humidity-count');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(3, $content['count']);
    }

    private function getMeasurementLogs(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/' . $channelId . '/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(3, $content);
        return $content;
    }

    private function emIncrement(array &$values, int $inc) {
        array_walk($values, function (&$value, $key) use ($inc) {
            $value += $inc;
        });
    }

    private function ensureElectricityMeasurementLogsOrder(array $content, array $energiesInOrder) {
        foreach ([1, 2, 3] as $phase) {
            $eInOrder = $energiesInOrder;
            array_walk($eInOrder, function (&$value, $key) use ($phase) {
                $value += $phase;
            });
            $this->emIncrement($eInOrder, 10);
            $this->assertEquals($eInOrder, array_map('intval', array_column($content, 'phase' . $phase . '_rae')));
            $this->emIncrement($eInOrder, 10);
            $this->assertEquals($eInOrder, array_map('intval', array_column($content, 'phase' . $phase . '_fre')));
            $this->emIncrement($eInOrder, 10);
            $this->assertEquals($eInOrder, array_map('intval', array_column($content, 'phase' . $phase . '_rre')));
        }
    }

    public function testGettingTemperatureLogs() {
        $content = $this->getMeasurementLogs(2);
        $temperaturesInOrder = [23, 22, 21];
        $this->assertEquals($temperaturesInOrder, array_map('intval', array_column($content, 'temperature')));
    }

    public function testGettingTemperatureAndHumidityLogs() {
        $content = $this->getMeasurementLogs(3);
        $temperaturesInOrder = [23, 22, 21];
        $this->assertEquals($temperaturesInOrder, array_map('intval', array_column($content, 'temperature')));
        $humiditiesInOrder = [50, 40, 30];
        $this->assertEquals($humiditiesInOrder, array_map('intval', array_column($content, 'humidity')));
    }

    public function testGettingElectricityMeasurementLogs() {
        $content = $this->getMeasurementLogs(4);
        $this->ensureElectricityMeasurementLogsOrder($content, [855000, 854900, 854800]);
    }

    private function testImpulseCounterLogs($channelId) {
        $content = $this->getMeasurementLogs($channelId);
        $impulsesInOrder = [300, 200,100];
        $calculatedValuesInOrder = [0.3, 0.2, 0.1];

        $this->assertEquals($impulsesInOrder, array_map('intval', array_column($content, 'counter')));
        $this->assertEquals($calculatedValuesInOrder, array_map('floatval', array_column($content, 'calculated_value')));
    }


    public function testGettingElectricityCounterLogs() {
        $this->testImpulseCounterLogs(5);
    }

    public function testGettingGasCounterLogs() {
        $this->testImpulseCounterLogs(6);
    }

    public function testGettingWaterCounterLogs() {
        $this->testImpulseCounterLogs(7);
    }

    private function getMeasurementLogsAscending(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/' . $channelId . '/measurement-logs?order=ASC');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(3, $content);
        return $content;
    }

    public function testGettingTemperatureLogsAscending() {
        $content = $this->getMeasurementLogsAscending(2);
        $temperaturesInOrder = [21, 22, 23];
        $this->assertEquals($temperaturesInOrder, array_map('intval', array_column($content, 'temperature')));
    }

    public function testGettingTemperatureAndHumidityLogsAscending() {
        $content = $this->getMeasurementLogsAscending(3);
        $temperaturesInOrder = [21, 22, 23];
        $this->assertEquals($temperaturesInOrder, array_map('intval', array_column($content, 'temperature')));
        $humiditiesInOrder = [30, 40, 50];
        $this->assertEquals($humiditiesInOrder, array_map('intval', array_column($content, 'humidity')));
    }

    public function testGettingElectricityMeasurementLogsAscending() {
        $content = $this->getMeasurementLogsAscending(4);
        $this->ensureElectricityMeasurementLogsOrder($content, [854800, 854900, 855000]);
    }

    private function testImpulseCounterLogsAscending($channelId) {
        $content = $this->getMeasurementLogsAscending($channelId);
        $impulsesInOrder = [100, 200,300];
        $calculatedValuesInOrder = [0.1, 0.2, 0.3];

        $this->assertEquals($impulsesInOrder, array_map('intval', array_column($content, 'counter')));
        $this->assertEquals($calculatedValuesInOrder, array_map('floatval', array_column($content, 'calculated_value')));
    }

    public function testGettingElectricityCounterLogsAscending() {
        $this->testImpulseCounterLogsAscending(5);
    }

    public function testGettingGasCounterLogsAscending() {
        $this->testImpulseCounterLogsAscending(6);
    }

    public function testGettingWaterCounterLogsAscending() {
        $this->testImpulseCounterLogsAscending(7);
    }

    private function gettingMeasurementLogsWithOffset(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/' . $channelId . '/measurement-logs?offset=1&limit=1');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        return $content;
    }

    public function testGettingTemperatureLogsWithOffset() {
        $content = $this->gettingMeasurementLogsWithOffset(2);
        $this->assertEquals(22, intval($content[0]['temperature']));
    }

    public function testGettingTemperatureAndHumidityLogsWithOffset() {
        $content = $this->gettingMeasurementLogsWithOffset(3);
        $this->assertEquals(22, intval($content[0]['temperature']));
        $this->assertEquals(40, intval($content[0]['humidity']));
    }

    public function testGettingElectricityMeasurementLogsWithOffset() {
        $content = $this->gettingMeasurementLogsWithOffset(4);
        $this->ensureElectricityMeasurementLogsOrder($content, [854900]);
    }

    private function testGettingImpulseCounterLogsWithOffset($channelId) {
        $content = $this->gettingMeasurementLogsWithOffset($channelId);
        $this->assertEquals(200, intval($content[0]['counter']));
        $this->assertEquals(0.2, floatval($content[0]['calculated_value']));
    }

    public function testGettingElectricityCounterLogsWithOffset() {
        $this->testGettingImpulseCounterLogsWithOffset(5);
    }

    public function testGettingGasCounterLogsWithOffset() {
        $this->testGettingImpulseCounterLogsWithOffset(6);
    }

    public function testGettingWaterCounterLogsWithOffset() {
        $this->testGettingImpulseCounterLogsWithOffset(7);
    }

    private function getMeasurementLogsWithTimestampRange(int $channelId) {
        $afterDate = new \DateTime('2018-09-15');
        $beforeDate = new \DateTime('2018-09-17');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/'
            . $channelId . '/measurement-logs?afterTimestamp=' . $afterDate->getTimestamp()
            . '&beforeTimestamp=' . $beforeDate->getTimestamp());
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);

        return $content;
    }

    public function testGettingTemperatureLogsWithTimestampRange() {
        $content = $this->getMeasurementLogsWithTimestampRange(2);
        $this->assertEquals(22, intval($content[0]['temperature']));
    }

    public function testGettingTemperatureAndHumidityLogsWithTimestampRange() {
        $content = $this->getMeasurementLogsWithTimestampRange(3);
        $this->assertEquals(22, intval($content[0]['temperature']));
        $this->assertEquals(40, intval($content[0]['humidity']));
    }

    public function testGettingElectricityMeasurementLogsWithTimestampRange() {
        $content = $this->getMeasurementLogsWithTimestampRange(4);
        $this->ensureElectricityMeasurementLogsOrder($content, [854900]);
    }

    public function testGettingElectricityCounterLogsWithTimestampRange() {
        $content = $this->getMeasurementLogsWithTimestampRange(5);
        $this->assertEquals(200, intval($content[0]['counter']));
        $this->assertEquals(0.2, floatval($content[0]['calculated_value']));
    }

    public function testGettingGasCounterLogsWithTimestampRange() {
        $content = $this->getMeasurementLogsWithTimestampRange(6);
        $this->assertEquals(200, intval($content[0]['counter']));
        $this->assertEquals(0.2, floatval($content[0]['calculated_value']));
    }

    public function testGettingWaterCounterLogsWithTimestampRange() {
        $content = $this->getMeasurementLogsWithTimestampRange(7);
        $this->assertEquals(200, intval($content[0]['counter']));
        $this->assertEquals(0.2, floatval($content[0]['calculated_value']));
    }

    public function testGettingMeasurementLogsOfUnsupportedChannel() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/1/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('400', $response);
    }
}
