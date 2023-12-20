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

namespace SuplaBundle\Tests\Integration\Auth;

use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;

class SecurityIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    protected function initializeDatabaseForTests() {
        foreach (['user1@supla.org', 'user2@supla.org'] as $username) {
            $user = $this->createConfirmedUser($username);
            $location = $user->getLocations()[0];
            $device = $this->createDevice($location, [
                [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
                [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
                [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            ]);
            $schedule = new Schedule($user, [
                'timeExpression' => '*/5 * * * *',
                'mode' => ScheduleMode::MINUTELY,
                'subject' => $device->getChannels()[0],
            ]);
            $this->getEntityManager()->persist($schedule);
            $channelGroup = new IODeviceChannelGroup($user, $location, [$device->getChannels()[0], $device->getChannels()[1]]);
            $this->getEntityManager()->persist($channelGroup);
            $directLink = new DirectLink($device->getChannels()[0]);
            $directLink->generateSlug(new NativePasswordEncoder(4));
            $this->getEntityManager()->persist($directLink);
            $scene = new Scene($location);
            $scene->setOpeartions([new SceneOperation($device->getChannels()[0], ChannelFunctionAction::TURN_ON())]);
            $this->getEntityManager()->persist($scene);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @small
     * @dataProvider urlUserAssignments
     */
    public function testMatrixPermissions(string $method, string $url, string $username, bool $shouldBeSuccessful) {
        $username .= '@supla.org';
        $client = $this->createAuthenticatedClient($username);
        $client->apiRequestV24($method, $url);
        $expectedStatusCode = $shouldBeSuccessful ? '2XX' : '4XX';
        $message = $username . ' should ' . (!$shouldBeSuccessful ? 'NOT ' : '') . 'be allowed to access ' . $method . ' ' . $url;
        $this->assertStatusCode($expectedStatusCode, $client->getResponse(), $message);
    }

    public function urlUserAssignments() {
        $specs = [
            'locations' => [
                'url' => '/locations/%d',
                'user1' => [1],
                'user2' => [2],
            ],
            'devices' => [
                'url' => '/iodevices/%d',
                'user1' => [1],
                'user2' => [2],
            ],
            'channels' => [
                'url' => '/channels/%d',
                'user1' => [1, 2, 3],
                'user2' => [4, 5, 6],
            ],
            'schedules' => [
                'url' => '/schedules/%d',
                'user1' => [1],
                'user2' => [2],
            ],
            'channel-groups' => [
                'url' => '/channel-groups/%d',
                'user1' => [1],
                'user2' => [2],
            ],
            'direct-links' => [
                'url' => '/direct-links/%d',
                'user1' => [1],
                'user2' => [2],
            ],
            'scenes' => [
                'url' => '/scenes/%d',
                'user1' => [1],
                'user2' => [2],
            ],
        ];
        $tests = [];
        foreach ($specs as $name => $spec) {
            foreach ($spec['user1'] as $allowedByUser1) {
                $url = '/api' . sprintf($spec['url'], $allowedByUser1);
                $tests[$name . ' - user1 - ' . $allowedByUser1] = [$spec['method'] ?? 'GET', $url, 'user1', true];
                $tests[$name . ' - user2 - ' . $allowedByUser1] = [$spec['method'] ?? 'GET', $url, 'user2', false];
            }
            foreach ($spec['user2'] as $allowedByUser2) {
                $url = '/api' . sprintf($spec['url'], $allowedByUser2);
                $tests[$name . ' - user2 - ' . $allowedByUser2] = [$spec['method'] ?? 'GET', $url, 'user2', true];
                $tests[$name . ' - user1 - ' . $allowedByUser2] = [$spec['method'] ?? 'GET', $url, 'user1', false];
            }
        }
        return $tests;
    }
}
