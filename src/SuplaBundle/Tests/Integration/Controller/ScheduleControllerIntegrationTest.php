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

use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDeviceFull($location);
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
        $schedule = $this->container->get('doctrine')->getRepository(Schedule::class)->find($scheduleFromResponse->id);
        $this->assertEquals($scheduleFromResponse->timeExpression, $schedule->getTimeExpression());
    }
}
