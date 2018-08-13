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
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpFoundation\Response;

class DirectLinkControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var IODeviceChannelGroup */
    private $channelGroup;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
        ]);
        $this->channelGroup = new IODeviceChannelGroup($this->user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[1],
        ]);
        $this->getEntityManager()->persist($this->channelGroup);
        $this->getEntityManager()->flush();
    }

    /**
     * @return mixed
     */
    private function createDirectLink(array $data = []): Response {
        $data = array_merge([
            'caption' => 'My link',
            'channelId' => $this->device->getChannels()[0]->getId(),
            'allowedActions' => ['turn-on', 'read'],
        ], $data);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('POST', '/api/direct-links', $data);
        return $client->getResponse();
    }

    public function testCreatingDirectLink() {
        $response = $this->createDirectLink();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My link', $content['caption']);
        $this->assertEquals($this->device->getChannels()[0]->getId(), $content['channelId']);
        $this->assertArrayHasKey('slug', $content);
        $this->assertLessThanOrEqual(DirectLink::SLUG_LENGTH_MAX, strlen($content['slug']));
        return $content;
    }

    public function testGettingDirectLinkDetails() {
        $id = $this->testCreatingDirectLink()['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/direct-links/' . $id . '?include=subject');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals(ActionableSubjectType::CHANNEL, $content['subjectType']);
        $this->assertArrayHasKey('channelId', $content);
        $this->assertArrayNotHasKey('channelGroupId', $content);
        $this->assertEquals(1, $content['channelId']);
        $this->assertArrayNotHasKey('slug', $content);
    }

    public function testCannotCreateDirectLinkWithActionNotSupportedInChannel() {
        $response = $this->createDirectLink(['allowedActions' => ['open']]);
        $this->assertStatusCode(400, $response);
    }

    public function testExecutingDirectLink() {
        $directLink = $this->testCreatingDirectLink();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/turn-on");
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    public function testCannotExecuteForbiddenAction() {
        $directLink = $this->testCreatingDirectLink();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/turn-off");
        $response = $client->getResponse();
        $this->assertStatusCode(403, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertNotContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    public function testCannotExecuteActionWithInvalidSlug() {
        $directLink = $this->testCreatingDirectLink();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]X/turn-on");
        $response = $client->getResponse();
        $this->assertStatusCode(403, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertNotContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    public function testCannotExecuteNotExistingAction() {
        $directLink = $this->testCreatingDirectLink();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/unicornify");
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertNotContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    public function testReadingDirectLink() {
        $directLink = $this->testCreatingDirectLink();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/read");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('on', $content);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
    }

    public function testCreatingDirectLinkForChannelGroup() {
        $data = [
            'caption' => 'My link',
            'channelGroupId' => $this->channelGroup->getId(),
            'allowedActions' => ['turn-on', 'read'],
        ];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('POST', '/api/direct-links', $data);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My link', $content['caption']);
        $this->assertArrayNotHasKey('channelId', $content);
        $this->assertEquals($this->channelGroup->getId(), $content['channelGroupId']);
        $this->assertArrayHasKey('slug', $content);
        return $content;
    }

    public function testExecutingDirectLinkForChannelGroup() {
        $directLink = $this->testCreatingDirectLinkForChannelGroup();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/turn-on");
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-CG-CHAR-VALUE:1,1,1', $commands);
    }

    public function testReadingDirectLinkForChannelGroup() {
        $directLink = $this->testCreatingDirectLinkForChannelGroup();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/read");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content);
        $this->assertArrayHasKey(1, $content);
        $this->assertArrayHasKey('on', $content[1]);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
        $this->assertContains('GET-CHAR-VALUE:1,1,2', $commands);
    }
}
