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

use DateTime;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\ScheduledExecution;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class ScheduleControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEFACADEBLIND],
        ]);
    }

    public function testCreatingNewSchedule() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequest(Request::METHOD_POST, '/api/schedules', [
            'channelId' => $this->device->getChannels()[0]->getId(),
            'actionId' => ChannelFunctionAction::TURN_ON,
            'mode' => ScheduleMode::ONCE,
            'timeExpression' => '2 2 * * *',
        ]);
        $this->assertStatusCode(Response::HTTP_CREATED, $client->getResponse());
        $scheduleFromResponse = json_decode($client->getResponse()->getContent());
        $this->assertGreaterThan(0, $scheduleFromResponse->id);
        $schedule = self::$container->get('doctrine')->getRepository(Schedule::class)->find($scheduleFromResponse->id);
        $this->assertEquals($scheduleFromResponse->config[0]->crontab, '2 2 * * *');
        return $schedule->getId();
    }

    /** @depends testCreatingNewSchedule */
    public function testGetScheduleDetails(int $scheduleId) {
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV23(Request::METHOD_GET, '/api/schedules/' . $scheduleId . '?include=subject,closestExecutions');
        $this->assertStatusCode(Response::HTTP_OK, $client->getResponse());
        $scheduleFromResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('subject', $scheduleFromResponse);
        $this->assertArrayHasKey('subjectType', $scheduleFromResponse);
    }

    public function testGeneratingNextScheduleExecutionsForOnceSchedule() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequest(Request::METHOD_POST, '/api/schedules/next-schedule-executions', [
            'channelId' => $this->device->getChannels()[0]->getId(),
            'actionId' => ChannelFunctionAction::TURN_OFF,
            'mode' => ScheduleMode::ONCE,
            'timeExpression' => '2 2 * * *',
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $client->getResponse());
        $nextExecutions = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThan(1, count($nextExecutions));
        $firstExecution = $nextExecutions[0];
        $this->assertEquals(ChannelFunctionAction::TURN_OFF, $firstExecution['actionId']);
    }

    public function testCreatingMinutelySchedule() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequest(Request::METHOD_POST, '/api/schedules', [
            'channelId' => $this->device->getChannels()[0]->getId(),
            'actionId' => ChannelFunctionAction::TURN_ON,
            'mode' => ScheduleMode::MINUTELY,
            'timeExpression' => '10 2 * * *',
        ]);
        $this->assertStatusCode(Response::HTTP_CREATED, $client->getResponse());
        $scheduleFromResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThan(0, $scheduleFromResponse['id']);
        $schedule = self::$container->get('doctrine')->getRepository(Schedule::class)->find($scheduleFromResponse['id']);
        $this->assertEquals($scheduleFromResponse['config'], $schedule->getConfig());
        return $schedule;
    }

    /** @depends testCreatingMinutelySchedule */
    public function testEditingStartDateOfScheduleThatHasExecutedExecutions(Schedule $schedule) {
        /** @var \SuplaBundle\Entity\Main\ScheduledExecution[] $executions */
        $scheduledExecutionsRepository = $this->getDoctrine()->getRepository(ScheduledExecution::class);
        $executions = $scheduledExecutionsRepository->findBy(['schedule' => $schedule]);
        $execution = $executions[0];
        EntityUtils::setField($execution, 'consumed', true);
        $this->getEntityManager()->persist($execution);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequest(Request::METHOD_PUT, '/api/schedules/' . $schedule->getId(), [
            'channelId' => $this->device->getChannels()[0]->getId(),
            'actionId' => ChannelFunctionAction::TURN_ON,
            'mode' => ScheduleMode::MINUTELY,
            'timeExpression' => '10 2 * * *',
            'dateStart' => date(DateTime::ATOM, strtotime('2030-01-01')),
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $client->getResponse());
        $executions = $scheduledExecutionsRepository->findBy(['schedule' => $schedule, 'consumed' => false]);
        $this->assertCount(1, $executions);
        $this->assertEquals(2030, $executions[0]->getPlannedTimestamp()->format('Y'));
        return $schedule;
    }

    public function testGeneratingNextScheduleExecutionsForDailySchedule() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequest(Request::METHOD_POST, '/api/schedules/next-schedule-executions', [
            'channelId' => $this->device->getChannels()[0]->getId(),
            'mode' => ScheduleMode::DAILY,
            'config' => [
                ['crontab' => "00 20 * * 1", 'action' => ['id' => 60, 'param' => []]],
            ],
        ]);
        $this->assertStatusCode(Response::HTTP_OK, $client->getResponse());
        $nextExecutions = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThan(1, count($nextExecutions));
        $firstExecution = $nextExecutions[0];
        $this->assertEquals(60, $firstExecution['actionId']);
    }

    public function testCreatingNewDailySchedule() {
        $client = $this->createAuthenticatedClient();
        $config = [['crontab' => '10 10 * * 1', 'action' => ['id' => ChannelFunctionAction::TURN_ON, 'param' => []]]];
        $client->apiRequest(Request::METHOD_POST, '/api/schedules', [
            'channelId' => $this->device->getChannels()[0]->getId(),
            'config' => $config,
            'mode' => ScheduleMode::DAILY,
        ]);
        $this->assertStatusCode(Response::HTTP_CREATED, $client->getResponse());
        $scheduleFromResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThan(0, $scheduleFromResponse['id']);
        $schedule = self::$container->get('doctrine')->getRepository(Schedule::class)->find($scheduleFromResponse['id']);
        $this->assertEquals($scheduleFromResponse['config'], $config);
        $this->assertEquals($schedule->getConfig(), $config);
    }

    public function testCreatingScheduleForOpeningGate() {
        $client = $this->createAuthenticatedClient();
        $config = [['crontab' => '10 10 * * 1', 'action' => ['id' => ChannelFunctionAction::OPEN, 'param' => []]]];
        $client->apiRequest(Request::METHOD_POST, '/api/schedules', [
            'channelId' => $this->device->getChannels()[1]->getId(),
            'config' => $config,
            'mode' => ScheduleMode::DAILY,
        ]);
        $this->assertStatusCode(Response::HTTP_CREATED, $client->getResponse());
    }

    public function testCreatingScheduleForFacadeBlind() {
        $client = $this->createAuthenticatedClient();
        $config = [['crontab' => '10 10 * * 1', 'action' => [
            'id' => ChannelFunctionAction::REVEAL_PARTIALLY,
            'param' => ['percentage' => '+20', 'tilt' => '10']]],
        ];
        $client->apiRequest(Request::METHOD_POST, '/api/schedules', [
            'channelId' => $this->device->getChannels()[2]->getId(),
            'config' => $config,
            'mode' => ScheduleMode::DAILY,
        ]);
        $this->assertStatusCode(Response::HTTP_CREATED, $client->getResponse());
        $scheduleFromResponse = json_decode($client->getResponse()->getContent(), true);
        $schedule = $this->freshEntityById(Schedule::class, $scheduleFromResponse['id']);
        $this->assertEquals($scheduleFromResponse['config'], $config);
        $this->assertNotEquals($config, $schedule->getConfig());
        $this->assertEquals([
            'percentageDelta' => 20,
            'tilt' => 10,
        ], $schedule->getConfig()[0]['action']['param']);
    }
}
