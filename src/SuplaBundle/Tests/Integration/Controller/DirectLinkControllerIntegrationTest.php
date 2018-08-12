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

use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

class DirectLinkControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
        ]);
    }

    public function testCreatingDirectLink() {
        $client = $this->createAuthenticatedClient($this->user);
        $channel = $this->device->getChannels()[0];
        $client->apiRequestV22('POST', '/api/direct-links', [
            'caption' => 'My link',
            'channelId' => $channel->getId(),
            'allowedActions' => ['turn-on'],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My link', $content['caption']);
        $this->assertArrayHasKey('slug', $content);
        $this->assertLessThanOrEqual(DirectLink::SLUG_LENGTH_MAX, strlen($content['slug']));
        return $content;
    }

    public function testExecutingDirectLink() {
        $this->markTestSkipped('How to authenticate in the supla server socket??');
        $directLink = $this->testCreatingDirectLink();
        $client = $this->createClient();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/turn-on");
        $resopnse = $client->getResponse();
        $this->assertStatusCode('2XX', $resopnse);
    }
}
