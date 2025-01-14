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

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageAberrationLogItem;
use SuplaBundle\Entity\MeasurementLogs\GeneralPurposeMeasurementLogItem;
use SuplaBundle\Entity\MeasurementLogs\GeneralPurposeMeterLogItem;
use SuplaBundle\Entity\MeasurementLogs\ImpulseCounterLogItem;
use SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem;
use SuplaBundle\Entity\MeasurementLogs\TempHumidityLogItem;
use SuplaBundle\Entity\MeasurementLogs\ThermostatLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\MysqlUtcDate;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaDeveloperBundle\DataFixtures\ORM\LogItemsFixture;

/** @small */
class ChannelMeasurementLogsControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device1;
    /** @var IODevice */
    private $device2;
    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $deviceWithManyLogs;

    protected function addLogItems($offset = 0) {
        $datestr = '2018-09-15';
        $date = new DateTime($datestr);
        $oneday = new DateInterval('P1D');

        foreach ([21.3, 22.23, 23] as $temperature) {
            $logItem = new TemperatureLogItem();
            EntityUtils::setField($logItem, 'channel_id', 2 + $offset);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString($date));
            EntityUtils::setField($logItem, 'temperature', $temperature);
            $this->getMeasurementLogsEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        $date = new DateTime($datestr);
        foreach ([[21.5, 30.3], [22.005, 40.04], [23, 50.005]] as $th) {
            $logItem = new TempHumidityLogItem();
            EntityUtils::setField($logItem, 'channel_id', 3 + $offset);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString($date));
            EntityUtils::setField($logItem, 'temperature', $th[0]);
            EntityUtils::setField($logItem, 'humidity', $th[1]);
            $this->getMeasurementLogsEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        $date = new DateTime($datestr);
        foreach ([854800, 854900, 855000] as $energy) {
            $logItem = new ElectricityMeterLogItem();
            EntityUtils::setField($logItem, 'channel_id', 4 + $offset);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString($date));

            foreach ([1, 2, 3] as $phase) {
                EntityUtils::setField($logItem, 'phase' . $phase . '_fae', $energy + $phase);
                EntityUtils::setField($logItem, 'phase' . $phase . '_rae', $energy + $phase + 10);
                EntityUtils::setField($logItem, 'phase' . $phase . '_fre', $energy + $phase + 20);
                EntityUtils::setField($logItem, 'phase' . $phase . '_rre', $energy + $phase + 30);
            }

            $this->getMeasurementLogsEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        $date = new DateTime($datestr);
        foreach ([100, 200, 300] as $impulses) {
            foreach ([1, 2, 3, 4] as $num) {
                $logItem = new ImpulseCounterLogItem();
                EntityUtils::setField($logItem, 'channel_id', 4 + $num + $offset);
                EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString($date));
                EntityUtils::setField($logItem, 'counter', $impulses);
                EntityUtils::setField($logItem, 'calculated_value', $impulses);
                $this->getMeasurementLogsEntityManager()->persist($logItem);
            }
            $date->add($oneday);
        }

        $date = new DateTime($datestr);
        foreach ([-10, 0, 30] as $temperature) {
            foreach ([1, 2] as $num) {
                $logItem = new ThermostatLogItem();
                EntityUtils::setField($logItem, 'channel_id', 8 + $num + $offset);
                EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString($date));
                EntityUtils::setField($logItem, "on", $temperature > -10);
                EntityUtils::setField($logItem, 'measuredTemperature', $temperature);
                EntityUtils::setField($logItem, 'presetTemperature', $temperature + 5);
                $this->getMeasurementLogsEntityManager()->persist($logItem);
            }
            $date->add($oneday);
        }

        foreach ([21, 22, 23] as $humidity) {
            $logItem = new TempHumidityLogItem();
            EntityUtils::setField($logItem, 'channel_id', 12 + $offset);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString($date));
            EntityUtils::setField($logItem, 'temperature', 0);
            EntityUtils::setField($logItem, 'humidity', $humidity);
            $this->getMeasurementLogsEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        foreach ([21, 22, 23] as $measurement) {
            $logItem = new GeneralPurposeMeasurementLogItem();
            EntityUtils::setField($logItem, 'channel_id', 13 + $offset);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString($date));
            EntityUtils::setField($logItem, 'open_value', $measurement * .9);
            EntityUtils::setField($logItem, 'close_value', $measurement * 1.1);
            EntityUtils::setField($logItem, 'avg_value', $measurement);
            EntityUtils::setField($logItem, 'max_value', $measurement * 1.11);
            EntityUtils::setField($logItem, 'min_value', $measurement * .89);
            $this->getMeasurementLogsEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        foreach ([100, 200, 300] as $impulses) {
            $logItem = new GeneralPurposeMeterLogItem();
            EntityUtils::setField($logItem, 'channel_id', 14 + $offset);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString($date));
            EntityUtils::setField($logItem, 'value', $impulses * 0.56 / 0.98 + 3.14);
            $this->getMeasurementLogsEntityManager()->persist($logItem);
            $date->add($oneday);
        }

        $fixture = new LogItemsFixture($this->getDoctrine());
        $fixture->createElectricityMeterVoltageAberrationLogItems($offset + 4, '-1 days', 1);
        $fixture->createElectricityMeterVoltageAberrationLogItems($offset + 4, '-1 days', 2);
        $fixture->createElectricityMeterVoltageLogItems($offset + 4, '-3 hours');
        $fixture->createElectricityMeterCurrentLogItems($offset + 4, '-3 hours');
        $fixture->createElectricityMeterPowerActiveLogItems($offset + 4, '-3 hours');
        $this->getMeasurementLogsEntityManager()->flush();
    }

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);

        $channels = [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
            [ChannelType::HUMIDITYANDTEMPSENSOR, ChannelFunction::HUMIDITYANDTEMPERATURE],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_GASMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_WATERMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_HEATMETER],
            [ChannelType::THERMOSTAT, ChannelFunction::THERMOSTAT],
            [ChannelType::THERMOSTATHEATPOLHOMEPLUS, ChannelFunction::THERMOSTATHEATPOLHOMEPLUS],
            [ChannelType::RELAY, ChannelFunction::STAIRCASETIMER],
            [ChannelType::HUMIDITYSENSOR, ChannelFunction::HUMIDITY],
            [ChannelType::GENERAL_PURPOSE_MEASUREMENT, ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
            [ChannelType::GENERAL_PURPOSE_METER, ChannelFunction::GENERAL_PURPOSE_METER],
        ];

        $this->device1 = $this->createDevice($location, $channels);
        $this->device2 = $this->createDevice($location, $channels);
        $this->deviceWithManyLogs = $this->createDevice($location, $channels);

        $this->addLogItems();
        $this->addLogItems(count($channels));

        for ($timestamp = strtotime('-60 days'); $timestamp < time(); $timestamp += 600) {
            $logItem = new TemperatureLogItem();
            EntityUtils::setField($logItem, 'channel_id', $this->deviceWithManyLogs->getChannels()[1]->getId());
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
            EntityUtils::setField($logItem, 'temperature', rand(0, 200) / 10);
            if (rand(0, 100) < 99) {
                $this->getMeasurementLogsEntityManager()->persist($logItem);
            }
            if (rand() % 2 == 0) {
                $logItem = new TemperatureLogItem();
                EntityUtils::setField($logItem, 'channel_id', 5000);
                EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
                EntityUtils::setField($logItem, 'temperature', rand(0, 200) / 10);
                $this->getMeasurementLogsEntityManager()->persist($logItem);
            }
        }

        $this->getEntityManager()->flush();
        $this->getMeasurementLogsEntityManager()->flush();
    }

    /** @dataProvider measurementLogsCounts */
    public function testMeasurementLogsCount(int $channelId, int $expectedLogCount = 3) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/' . $channelId . '/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertTrue($response->headers->has('X-Total-Count'));
        $this->assertEquals($expectedLogCount, $response->headers->get('X-Total-Count'));
    }

    public static function measurementLogsCounts() {
        return [[2], [3], [4], [5], [6], [7], [8], [9], [10]];
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

    private function getMeasurementLogsV22(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/' . $channelId . '/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(3, $content);
        return $content;
    }

    private function getMeasurementLogsV24(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $channelId . '/measurement-logs');
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
            $this->assertEquals($eInOrder, array_column($content, 'phase' . $phase . '_rae'));
            $this->emIncrement($eInOrder, 10);
            $this->assertEquals($eInOrder, array_column($content, 'phase' . $phase . '_fre'));
            $this->emIncrement($eInOrder, 10);
            $this->assertEquals($eInOrder, array_column($content, 'phase' . $phase . '_rre'));
        }
    }

    public function testGettingTemperatureLogsV22() {
        $content = $this->getMeasurementLogsV22(2);
        $temperaturesInOrder = ['23.0000', '22.2300', '21.3000'];
        $this->assertEquals($temperaturesInOrder, array_column($content, 'temperature'));
    }

    public function testGettingTemperatureLogsInFloatsV24() {
        $content = $this->getMeasurementLogsV24(2);
        $temperaturesInOrder = [23, 22.23, 21.3];
        $this->assertSame($temperaturesInOrder, array_column($content, 'temperature'));
    }

    public function testGettingTemperatureAndHumidityLogsV22() {
        $content = $this->getMeasurementLogsV22(3);
        $temperaturesInOrder = ['23.0000', '22.0050', '21.5000'];
        $this->assertEquals($temperaturesInOrder, array_column($content, 'temperature'));
        $humiditiesInOrder = ['50.0050', '40.0400', '30.3000'];
        $this->assertEquals($humiditiesInOrder, array_column($content, 'humidity'));
    }

    public function testGettingTemperatureAndHumidityLogsV24() {
        $content = $this->getMeasurementLogsV24(3);
        $temperaturesInOrder = [23, 22.005, 21.5];
        $this->assertEquals($temperaturesInOrder, array_column($content, 'temperature'));
        $humiditiesInOrder = [50.005, 40.04, 30.3];
        $this->assertEquals($humiditiesInOrder, array_column($content, 'humidity'));
    }

    public function testGettingElectricityMeasurementLogs() {
        $content = $this->getMeasurementLogsV22(4);
        $this->ensureElectricityMeasurementLogsOrder($content, [855000, 854900, 854800]);
    }

    public function testGettingElectricityMeasurementLogsInStringsV22() {
        $firstLog = $this->getMeasurementLogsV22(4)[0];
        $this->assertIsString($firstLog['date_timestamp']);
        $this->assertSame('855002', $firstLog['phase2_fae']);
        $this->assertNull($firstLog['fae_balanced']);
    }

    public function testGettingElectricityMeasurementLogsInIntsV24() {
        $firstLog = $this->getMeasurementLogsV24(4)[0];
        $this->assertIsInt($firstLog['date_timestamp']);
        $this->assertSame(855002, $firstLog['phase2_fae']);
        $this->assertNull($firstLog['fae_balanced']);
    }

    private function ensureImpulseCounterLogsV22($channelId) {
        $content = $this->getMeasurementLogsV22($channelId);
        $impulsesInOrder = ['300', '200', '100'];
        $calculatedValuesInOrder = ['0.3000', '0.2000', '0.1000'];
        $this->assertSame($impulsesInOrder, array_column($content, 'counter'));
        $this->assertSame($calculatedValuesInOrder, array_column($content, 'calculated_value'));
    }

    public function testGettingGeneralPurposeMeasurementLogs() {
        $firstLog = $this->getMeasurementLogsV24(13)[0];
        $this->assertIsInt($firstLog['date_timestamp']);
        $this->assertSame(23, $firstLog['avg_value']);
        $this->assertEqualsWithDelta(20.7, $firstLog['open_value'], 0.001);
        $this->assertEqualsWithDelta(25.3, $firstLog['close_value'], 0.001);
        $this->assertEqualsWithDelta(23, $firstLog['avg_value'], 0.001);
        $this->assertEqualsWithDelta(20.47, $firstLog['min_value'], 0.001);
        $this->assertEqualsWithDelta(25.53, $firstLog['max_value'], 0.001);
    }

    public function testGettingGeneralPurposeMeterLogs() {
        $firstLog = $this->getMeasurementLogsV24(14)[0];
        $this->assertIsInt($firstLog['date_timestamp']);
        $this->assertIsFloat($firstLog['value']);
        $this->assertEqualsWithDelta(174.568, $firstLog['value'], 0.001);
    }

    public function testGettingElectricityCounterLogsV22() {
        $this->ensureImpulseCounterLogsV22(5);
    }

    public function testGettingGasCounterLogsV22() {
        $this->ensureImpulseCounterLogsV22(6);
    }

    public function testGettingWaterCounterLogsV22() {
        $this->ensureImpulseCounterLogsV22(7);
    }

    public function testGettingHeatCounterLogsV22() {
        $this->ensureImpulseCounterLogsV22(8);
    }

    private function ensureImpulseCounterLogsV24($channelId) {
        $content = $this->getMeasurementLogsV24($channelId);
        $impulsesInOrder = [300, 200, 100];
        $calculatedValuesInOrder = [0.3, 0.2, 0.1];

        $this->assertSame($impulsesInOrder, array_column($content, 'counter'));
        $this->assertSame($calculatedValuesInOrder, array_column($content, 'calculated_value'));
    }

    public function testGettingElectricityCounterLogsV24() {
        $this->ensureImpulseCounterLogsV24(5);
    }

    public function testGettingGasCounterLogsV24() {
        $this->ensureImpulseCounterLogsV24(6);
    }

    public function testGettingWaterCounterLogsV24() {
        $this->ensureImpulseCounterLogsV24(7);
    }

    public function testGettingHeatCounterLogsV24() {
        $this->ensureImpulseCounterLogsV24(8);
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

    private function ensureImpulseCounterLogsAscending($channelId) {
        $content = $this->getMeasurementLogsAscending($channelId);
        $impulsesInOrder = [100, 200, 300];
        $calculatedValuesInOrder = [0.1, 0.2, 0.3];

        $this->assertEquals($impulsesInOrder, array_map('intval', array_column($content, 'counter')));
        $this->assertEquals($calculatedValuesInOrder, array_map('floatval', array_column($content, 'calculated_value')));
    }

    public function testGettingElectricityCounterLogsAscending() {
        $this->ensureImpulseCounterLogsAscending(5);
    }

    public function testGettingGasCounterLogsAscending() {
        $this->ensureImpulseCounterLogsAscending(6);
    }

    public function testGettingWaterCounterLogsAscending() {
        $this->ensureImpulseCounterLogsAscending(7);
    }

    public function testGettingHeatCounterLogsAscending() {
        $this->ensureImpulseCounterLogsAscending(8);
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

    private function ensureGettingImpulseCounterLogsWithOffset($channelId) {
        $content = $this->gettingMeasurementLogsWithOffset($channelId);
        $this->assertEquals(200, intval($content[0]['counter']));
        $this->assertEquals(0.2, floatval($content[0]['calculated_value']));
    }

    private function ensureGettingThermostatLogsWithOffset($channelId) {
        $content = $this->gettingMeasurementLogsWithOffset($channelId);

        $this->assertSame('1', $content[0]['on']);
        $this->assertSame('0.00', $content[0]['measured_temperature']);
        $this->assertSame('5.00', $content[0]['preset_temperature']);
    }

    public function testGettingElectricityCounterLogsWithOffset() {
        $this->ensureGettingImpulseCounterLogsWithOffset(5);
    }

    public function testGettingGasCounterLogsWithOffset() {
        $this->ensureGettingImpulseCounterLogsWithOffset(6);
    }

    public function testGettingWaterCounterLogsWithOffset() {
        $this->ensureGettingImpulseCounterLogsWithOffset(7);
    }

    public function testGettingHeatCounterLogsWithOffset() {
        $this->ensureGettingImpulseCounterLogsWithOffset(8);
    }

    public function testGettingThermostatLogsWithOffset() {
        $this->ensureGettingThermostatLogsWithOffset(9);
        $this->ensureGettingThermostatLogsWithOffset(10);
    }

    public function testGettingThermostatLogsV24() {
        $firstLog = $this->getMeasurementLogsV24(9)[0];
        $this->assertTrue($firstLog['on']);
        $this->assertSame(30, $firstLog['measured_temperature']);
        $this->assertSame(35, $firstLog['preset_temperature']);
    }

    private function getMeasurementLogsWithTimestampRange(int $channelId) {
        $afterDate = new DateTime('2018-09-15');
        $beforeDate = new DateTime('2018-09-17');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/'
            . $channelId . '/measurement-logs?afterTimestamp=' . $afterDate->getTimestamp()
            . '&beforeTimestamp=' . $beforeDate->getTimestamp());
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertTrue($response->headers->has('X-Total-Count'));
        $this->assertTrue($response->headers->has('X-Count'));
        $this->assertEquals(1, $response->headers->get('X-Count'));
        $this->assertEquals(3, $response->headers->get('X-Total-Count'));
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

    public function testGettingHeatCounterLogsWithTimestampRange() {
        $content = $this->getMeasurementLogsWithTimestampRange(8);
        $this->assertEquals(200, intval($content[0]['counter']));
        $this->assertEquals(0.2, floatval($content[0]['calculated_value']));
    }

    private function ensureGettingThermostatLogsWithTimestampRange(int $channelId) {
        $content = $this->getMeasurementLogsWithTimestampRange($channelId);
        $this->assertEquals(1, intval($content[0]['on']));
        $this->assertEquals(0, floatval($content[0]['measured_temperature']));
        $this->assertEquals(5, floatval($content[0]['preset_temperature']));
    }

    public function testGettingThermostatLogsWithTimestampRange() {
        $this->ensureGettingThermostatLogsWithTimestampRange(9);
        $this->ensureGettingThermostatLogsWithTimestampRange(10);
    }

    public function testGettingMeasurementLogsOfUnsupportedChannel() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/1/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('400', $response);
    }

    public function testGettingElectricityMeasurementsLogsCountFromRelatedRelay() {
        $this->device1 = $this->getEntityManager()->find(IODevice::class, $this->device1->getId());
        /** @var SubjectConfigTranslator $paramsTranslator */
        $paramsTranslator = self::$container->get(SubjectConfigTranslator::class);
        $relayChannel = $this->device1->getChannels()[0];
        $paramsTranslator->setConfig($relayChannel, ['relatedMeterChannelId' => 4]);
        $this->persist($relayChannel);
        $content = $this->getMeasurementLogsAscending($relayChannel->getId());
        $this->ensureElectricityMeasurementLogsOrder($content, [854800, 854900, 855000]);
    }

    public function testGettingElectricityMeasurementsLogsCountFromRelatedStaircaseTimer() {
        $this->device1 = $this->getEntityManager()->find(IODevice::class, $this->device1->getId());
        /** @var SubjectConfigTranslator $paramsTranslator */
        $paramsTranslator = self::$container->get(SubjectConfigTranslator::class);
        $staircaseTimerChannel = $this->device1->getChannels()[10];
        $paramsTranslator->setConfig($staircaseTimerChannel, ['relatedMeterChannelId' => 4]);
        $this->persist($staircaseTimerChannel);
        $content = $this->getMeasurementLogsAscending($staircaseTimerChannel->getId());
        $this->ensureElectricityMeasurementLogsOrder($content, [854800, 854900, 855000]);
    }

    public function testGettingLogsFromChannelWithManyLogs() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $logItems = json_decode($response->getContent(), true);
        $this->assertCount(5000, $logItems);
        $this->assertEquals($response->headers->get('X-Count'), $response->headers->get('X-Total-Count'));
        $minTimestamp = $response->headers->get('X-Min-Timestamp');
        $maxTimestamp = $response->headers->get('X-Max-Timestamp');
        $firstTimestamp = current($logItems)['date_timestamp'];
        $lastTimestamp = end($logItems)['date_timestamp'];
        $this->assertEquals($maxTimestamp, $firstTimestamp);
        $this->assertTimestampsGrow(
            strtotime('-70 days'),
            $minTimestamp,
            strtotime('-40 days'),
            $lastTimestamp,
            strtotime('-30 days'),
            $firstTimestamp,
            time()
        );
        $this->assertEqualsWithDelta(35, floor(($firstTimestamp - $lastTimestamp) / 86400), 1); // 5000 logs per 10 minutes each ~= 35 days
    }

    public function testGettingLogsFromChannelWithNoLogs() {
        $channelId = $this->deviceWithManyLogs->getChannels()[2]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $logItems = json_decode($response->getContent(), true);
        $this->assertCount(0, $logItems);
        $minTimestamp = $response->headers->get('X-Min-Timestamp');
        $maxTimestamp = $response->headers->get('X-Max-Timestamp');
        $this->assertNull($minTimestamp);
        $this->assertNull($maxTimestamp);
    }

    public function testGettingSparseLogsFromChannelWithManyLogs() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?sparse=500");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $logItems = json_decode($response->getContent(), true);
        $this->assertEqualsWithDelta(500, count($logItems), 50);
        $minTimestamp = $response->headers->get('X-Min-Timestamp');
        $maxTimestamp = $response->headers->get('X-Max-Timestamp');
        $firstTimestamp = current($logItems)['date_timestamp'];
        $lastTimestamp = end($logItems)['date_timestamp'];
        $this->assertTimestampsGrow(
            strtotime('-70 days'),
            $minTimestamp,
            $lastTimestamp,
            strtotime('-50 days'),
            strtotime('-1 days'),
            $firstTimestamp,
            $maxTimestamp,
            time()
        );
        $this->assertEqualsWithDelta(60, floor(($firstTimestamp - $lastTimestamp) / 86400), 1);
    }

    public function testGettingSparseLogsFromChannelWithManyLogsOrderedAsc() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?sparse=500&order=ASC");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $logItems = json_decode($response->getContent(), true);
        $this->assertEqualsWithDelta(500, count($logItems), 50);
        $minTimestamp = $response->headers->get('X-Min-Timestamp');
        $maxTimestamp = $response->headers->get('X-Max-Timestamp');
        $firstTimestamp = current($logItems)['date_timestamp'];
        $lastTimestamp = end($logItems)['date_timestamp'];
        $this->assertTimestampsGrow(
            strtotime('-70 days'),
            $minTimestamp,
            $firstTimestamp,
            strtotime('-50 days'),
            strtotime('-1 days'),
            $lastTimestamp,
            $maxTimestamp,
            time()
        );
        $this->assertEqualsWithDelta(60, floor(($lastTimestamp - $firstTimestamp) / 86400), 1);
    }

    public function testGettingSparseLogsBetweenTimestamps() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('-10 days');
        $beforeTimestamp = strtotime('-3 days');
        $params = http_build_query(['sparse' => 100, 'afterTimestamp' => $afterTimestamp, 'beforeTimestamp' => $beforeTimestamp]);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?$params");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $logItems = json_decode($response->getContent(), true);
        $this->assertEqualsWithDelta(100, count($logItems), 15);
        $minTimestamp = $response->headers->get('X-Min-Timestamp');
        $maxTimestamp = $response->headers->get('X-Max-Timestamp');
        $firstTimestamp = current($logItems)['date_timestamp'];
        $lastTimestamp = end($logItems)['date_timestamp'];
        $this->assertTimestampsGrow(
            $afterTimestamp,
            $minTimestamp,
            $lastTimestamp,
            $firstTimestamp,
            $maxTimestamp,
            $beforeTimestamp,
            time()
        );
        $this->assertEqualsWithDelta(7, floor(($firstTimestamp - $lastTimestamp) / 86400), 1);
    }

    private function assertTimestampsGrow(...$timestamps) {
        $formatTimestamp = function ($timestamp) {
            return date('Y-m-d H:i:s', $timestamp);
        };
        for ($i = 0; $i < count($timestamps) - 1; $i++) {
            $expected = $timestamps[$i];
            $actual = $timestamps[$i + 1];
            $this->assertGreaterThanOrEqual($expected, $actual, "{$formatTimestamp($expected)} < {$formatTimestamp($actual)}");
        }
    }

    public function testGettingSparseLogsBetweenTimestampsOrderedAsc() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $afterTimestamp = strtotime('-10 days');
        $beforeTimestamp = strtotime('-3 days');
        $params = http_build_query(
            ['sparse' => 100, 'afterTimestamp' => $afterTimestamp, 'beforeTimestamp' => $beforeTimestamp, 'order' => 'ASC']
        );
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?$params");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $logItems = json_decode($response->getContent(), true);
        $this->assertEqualsWithDelta(100, count($logItems), 15);
        $minTimestamp = $response->headers->get('X-Min-Timestamp');
        $maxTimestamp = $response->headers->get('X-Max-Timestamp');
        $firstTimestamp = current($logItems)['date_timestamp'];
        $lastTimestamp = end($logItems)['date_timestamp'];
        $this->assertTimestampsGrow(
            $afterTimestamp,
            $minTimestamp,
            $firstTimestamp,
            $lastTimestamp,
            $maxTimestamp,
            $beforeTimestamp,
            time()
        );
        $this->assertEqualsWithDelta(7, floor(($lastTimestamp - $firstTimestamp) / 86400), 1);
    }

    /** @dataProvider invalidLimits */
    public function testInvalidLimits(int $limit) {
        $channelId = $this->deviceWithManyLogs->getChannels()[0]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/' . $channelId . '/measurement-logs?limit=' . $limit);
        $response = $client->getResponse();
        $this->assertStatusCode('4xx', $response);
    }

    public static function invalidLimits() {
        return [[0], [-1], [5001]];
    }

    public function testGetMeasurementLogsWithTimestampRangeAndOffset() {
        $beforeDate = new DateTime('2018-09-17');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/2/measurement-logs?beforeTimestamp={$beforeDate->getTimestamp()}");
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content);
        $client->apiRequestV24('GET', "/api/channels/2/measurement-logs?beforeTimestamp={$beforeDate->getTimestamp()}&offset=1");
        $content2 = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $content2);
        $this->assertEquals($content[1], $content2[0]);
    }

    public function testGeneratingCsvFromChannelWithManyLogs() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs");
        $memBefore = memory_get_usage();
        ob_start();
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs-csv");
        $data = ob_get_contents();
        ob_end_clean();
        $memAfter = memory_get_usage();
        $memDiff = ($memAfter - $memBefore) / 1024;
        $this->assertLessThan(5000, $memDiff); // less than ~5MB memory consumption
        // https://stackoverflow.com/a/23113182/878514
        $head = unpack("Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen", substr($data, 0, 30));
        $filename = substr($data, 30, $head['namelen']);
        $this->assertStringContainsString('measurement', $filename);
        $csv = gzinflate(substr($data, 30 + $head['namelen'] + $head['exlen'], $head['csize']));
        $this->assertStringContainsString("Temperature", $csv);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?offset=50&limit=10");
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $testItem = $content[3];
        $dateTime = new \DateTime('@' . $testItem['date_timestamp'], new \DateTimeZone($this->user->getTimezone()));
        $expectedRow = "$testItem[date_timestamp],\"" . $dateTime->format('Y-m-d H:i:s') . "\",$testItem[temperature]";
        $this->assertStringContainsString($expectedRow, $csv);
    }

    public function testGeneratingCsvFromChannelWithVoltageAberrationLogs() {
        $channelId = $this->device1->getChannels()[3]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs");
        $memBefore = memory_get_usage();
        ob_start();
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs-csv?logsType=voltage");
        $data = ob_get_contents();
        ob_end_clean();
        $memAfter = memory_get_usage();
        $memDiff = ($memAfter - $memBefore) / 1024;
        $this->assertLessThan(5000, $memDiff); // less than ~5MB memory consumption
        // https://stackoverflow.com/a/23113182/878514
        $head = unpack("Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen", substr($data, 0, 30));
        $filename = substr($data, 30, $head['namelen']);
        $this->assertStringContainsString('voltage_aberrations', $filename);
        $csv = gzinflate(substr($data, 30 + $head['namelen'] + $head['exlen'], $head['csize']));
        $this->assertStringContainsString("Count above", $csv);
        $this->assertStringContainsString("Seconds below", $csv);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?limit=10&logsType=voltage");
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $testItem = $content[3];
        $dateTime = new \DateTime('@' . $testItem['date_timestamp'], new \DateTimeZone($this->user->getTimezone()));
        $expectedRow = "$testItem[date_timestamp],\"" . $dateTime->format('Y-m-d H:i:s') . "\",$testItem[measurementTimeSec]";
        $this->assertStringContainsString($expectedRow, $csv);
    }

    public function testGeneratingCsvFromChannelWithVoltageHistoryLogs() {
        $channelId = $this->device1->getChannels()[3]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs");
        $memBefore = memory_get_usage();
        ob_start();
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs-csv?logsType=voltageHistory");
        $data = ob_get_contents();
        ob_end_clean();
        $memAfter = memory_get_usage();
        $memDiff = ($memAfter - $memBefore) / 1024;
        $this->assertLessThan(5000, $memDiff); // less than ~5MB memory consumption
        // https://stackoverflow.com/a/23113182/878514
        $head = unpack("Vsig/vver/vflag/vmeth/vmodt/vmodd/Vcrc/Vcsize/Vsize/vnamelen/vexlen", substr($data, 0, 30));
        $filename = substr($data, 30, $head['namelen']);
        $this->assertStringContainsString('voltage_history', $filename);
        $csv = gzinflate(substr($data, 30 + $head['namelen'] + $head['exlen'], $head['csize']));
        $this->assertStringContainsString("Minimum voltage", $csv);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?limit=10&logsType=voltageHistory");
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $testItem = $content[3];
        $dateTime = new \DateTime('@' . $testItem['date_timestamp'], new \DateTimeZone($this->user->getTimezone()));
        $expectedRow = "$testItem[date_timestamp],\"" . $dateTime->format('Y-m-d H:i:s') . "\",$testItem[phaseNo],$testItem[min]";
        $this->assertStringContainsString($expectedRow, $csv);
    }

    public function testGettingVoltageLogsFromNotSupportedDevice() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?logsType=voltage");
        $response = $client->getResponse();
        $this->assertStatusCode('400', $response);
    }

    public function testGettingUnsupportedLogsType() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?logsType=unicorn");
        $response = $client->getResponse();
        $this->assertStatusCode('400', $response);
    }

    public function testSettingLogsTypeToDefault() {
        $channelId = $this->deviceWithManyLogs->getChannels()[1]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?logsType=default");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
    }

    public function testGettingVoltageAberrationsLogs() {
        $channelId = $this->device1->getChannels()[3]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?logsType=voltageAberrations");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertGreaterThan(0, count($content));
        $log = $content[0];
        $this->assertArrayHasKey('phaseNo', $log);
        $this->assertArrayHasKey('countTotal', $log);
        $this->assertSame($log['countTotal'], intval($log['countTotal']));
        $this->assertArrayHasKey('countAbove', $log);
        $this->assertArrayHasKey('secBelow', $log);
        $this->assertArrayHasKey('maxSecAbove', $log);
        $this->assertArrayHasKey('minVoltage', $log);
        $this->assertArrayHasKey('avgVoltage', $log);
        $this->assertArrayHasKey('measurementTimeSec', $log);
        $this->assertSame($log['avgVoltage'], floatval($log['avgVoltage']));
        $this->assertNotSame($log['avgVoltage'], intval($log['avgVoltage']));
    }

    public function testGettingVoltageHistoryLogs() {
        $channelId = $this->device1->getChannels()[3]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?logsType=voltageHistory");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertGreaterThan(0, count($content));
        $log = $content[0];
        $this->assertArrayHasKey('phaseNo', $log);
        $this->assertArrayHasKey('min', $log);
        $this->assertArrayHasKey('max', $log);
        $this->assertArrayHasKey('avg', $log);
        $this->assertSame($log['min'], floatval($log['min']));
    }

    public function testGettingCurrentHistoryLogs() {
        $channelId = $this->device1->getChannels()[3]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?logsType=currentHistory");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertGreaterThan(0, count($content));
        $log = $content[0];
        $this->assertArrayHasKey('phaseNo', $log);
        $this->assertArrayHasKey('min', $log);
        $this->assertArrayHasKey('max', $log);
        $this->assertArrayHasKey('avg', $log);
        $this->assertIsNotString($log['min']);
        $this->assertIsNumeric($log['min']);
    }

    public function testGettingPowerHistoryLogs() {
        $channelId = $this->device1->getChannels()[3]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$channelId}/measurement-logs?logsType=powerActiveHistory");
        $response = $client->getResponse();
        $this->assertStatusCode('200', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertGreaterThan(0, count($content));
        $log = $content[0];
        $this->assertArrayHasKey('phaseNo', $log);
        $this->assertArrayHasKey('min', $log);
        $this->assertArrayHasKey('max', $log);
        $this->assertArrayHasKey('avg', $log);
        $this->assertIsNotString($log['min']);
        $this->assertIsNumeric($log['min']);
    }

    public function testDeletingVoltageLogsForSelectedPhase() {
        $channelId = $this->device1->getChannels()[3]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $voltageRepository = $this->getDoctrine()->getRepository(ElectricityMeterVoltageAberrationLogItem::class);
        $logsBefore = $voltageRepository->findBy(['channel_id' => $channelId]);
        $client->apiRequestV24('DELETE', "/api/channels/{$channelId}/measurement-logs?logsType=voltage&phase=1");
        $response = $client->getResponse();
        $this->assertStatusCode('204', $response);
        $emLogRepository = $this->getDoctrine()->getRepository(ElectricityMeterLogItem::class);
        $logsAfter = $voltageRepository->findBy(['channel_id' => $channelId]);
        $this->assertNotEmpty($logsAfter);
        $phases = array_unique(array_map(function (ElectricityMeterVoltageAberrationLogItem $log) {
            return EntityUtils::getField($log, 'phaseNo');
        }, $logsAfter));
        $this->assertEquals([2], $phases);
        $this->assertLessThan(count($logsBefore), count($logsAfter));
        $this->assertNotEmpty($emLogRepository->findBy(['channel_id' => $channelId]));
    }

    public function testDeletingVoltageLogs() {
        $channelId = $this->device1->getChannels()[3]->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('DELETE', "/api/channels/{$channelId}/measurement-logs?logsType=voltage");
        $response = $client->getResponse();
        $this->assertStatusCode('204', $response);
        $voltageRepository = $this->getDoctrine()->getRepository(ElectricityMeterVoltageAberrationLogItem::class);
        $emLogRepository = $this->getDoctrine()->getRepository(ElectricityMeterLogItem::class);
        $this->assertEmpty($voltageRepository->findBy(['channel_id' => $channelId]));
        $this->assertNotEmpty($emLogRepository->findBy(['channel_id' => $channelId]));
    }

    private function deleteMeasurementLogs(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV23('DELETE', '/api/channels/' . $channelId . '/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
    }

    public function testDeletingMeasurementLogs() {
        foreach ($this->device1->getChannels() as $channel) {
            if ($channel->getType()->getId() != ChannelType::RELAY) {
                $this->testMeasurementLogsCount($channel->getId());
                $this->deleteMeasurementLogs($channel->getId());
                $this->testMeasurementLogsCount($channel->getId(), 0);
            }
        }

        foreach ($this->device2->getChannels() as $channel) {
            if ($channel->getType()->getId() != ChannelType::RELAY) {
                $this->testMeasurementLogsCount($channel->getId());
            }
        }
    }

    protected function getMeasurementLogsEntityManager(): EntityManagerInterface {
        return $this->getEntityManager('measurement_logs');
    }
}
