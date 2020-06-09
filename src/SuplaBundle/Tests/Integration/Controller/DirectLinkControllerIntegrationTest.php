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
use SuplaBundle\Enums\DirectLinkExecutionFailureReason;
use SuplaBundle\Supla\SuplaServerMock;
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

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::DIMMERANDRGBLIGHTING],
            [ChannelType::VALVEOPENCLOSE, ChannelFunction::VALVEOPENCLOSE],
        ]);
        $this->channelGroup = new IODeviceChannelGroup($this->user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[1],
        ]);
        $this->getEntityManager()->persist($this->channelGroup);
        $this->getEntityManager()->flush();
    }

    private function createDirectLink(array $data = []): Response {
        $data = array_merge([
            'caption' => 'My link',
            'subjectType' => 'channel',
            'subjectId' => $this->device->getChannels()[0]->getId(),
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
        $this->assertEquals($this->device->getChannels()[0]->getId(), $content['subjectId']);
        $this->assertArrayHasKey('slug', $content);
        $this->assertLessThanOrEqual(DirectLink::SLUG_LENGTH_MAX, strlen($content['slug']));
        return $content;
    }

    /** @depends testCreatingDirectLink */
    public function testGettingDirectLinkDetails(array $directLinkData) {
        $id = $directLinkData['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/direct-links/' . $id . '?include=subject');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals(ActionableSubjectType::CHANNEL, $content['subjectType']);
        $this->assertArrayHasKey('subjectId', $content);
        $this->assertEquals(1, $content['subjectId']);
        $this->assertArrayNotHasKey('slug', $content);
        $this->assertArrayHasKey('subject', $content);
        $this->assertArrayHasKey('relationsCount', $content['subject']);
    }

    /** @depends testCreatingDirectLink */
    public function testGettingDirectLinkDetailsV24(array $directLinkData) {
        $id = $directLinkData['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/direct-links/' . $id . '?include=subject');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals(ActionableSubjectType::CHANNEL, $content['subjectType']);
        $this->assertArrayHasKey('subjectId', $content);
        $this->assertEquals(1, $content['subjectId']);
        $this->assertArrayNotHasKey('slug', $content);
        $this->assertArrayHasKey('subject', $content);
        $this->assertArrayHasKey('relationsCount', $content['subject']);
    }

    /** @small */
    public function testCannotCreateDirectLinkWithActionNotSupportedInChannel() {
        $response = $this->createDirectLink(['allowedActions' => ['open']]);
        $this->assertStatusCode(400, $response);
    }

    /** @depends testCreatingDirectLink */
    public function testExecutingDirectLink(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/turn-on");
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testExecutingDirectLinkWithPatch(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('PATCH', "/direct/$directLink[id]/$directLink[slug]/turn-on");
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testExecutingDirectLinkWithPut(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('PUT', "/direct/$directLink[id]/$directLink[slug]/turn-on");
        $response = $client->getResponse();
        $this->assertStatusCode(405, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertEmpty($commands);
    }

    /** @depends testCreatingDirectLink */
    public function testExecutingDirectLinkWithPatchAndCredentialsInRequestBody(array $directLinkDetails) {
        $client = $this->createClient();
        $client->enableProfiler();
        $requestData = ['code' => $directLinkDetails['slug'], 'action' => 'turn-on'];
        $client->request('PATCH', "/direct/$directLinkDetails[id]", $requestData, [], ['CONTENT_TYPE' => 'application/json']);
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testCannotExecuteForbiddenAction(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/turn-off");
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertNotContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testCannotExecuteActionWithInvalidSlug(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]X/turn-on", [], [], ['HTTP_ACCEPT' => '*/*']);
        $response = $client->getResponse();
        $this->assertStatusCode(403, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals(DirectLinkExecutionFailureReason::INVALID_SLUG, $content['message']);
        $this->assertEquals('Given verification code is not valid.', $content['messageText']);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertNotContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testCannotExecuteNotExistingAction(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/unicornify");
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertNotContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testReadingDirectLinkAsJson(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/read", [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('on', $content);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testReadingDirectLinkAsJsonThroughGetParam(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/read?format=json");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('on', $content);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testPreferJson(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $acceptHeader = ['HTTP_ACCEPT' => 'text/html, text/plain, application/json'];
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/read", [], [], $acceptHeader);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('on', $content);
    }

    /** @depends testCreatingDirectLink */
    public function testReadingDirectLinkAsText(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/read", [], [], ['HTTP_ACCEPT' => 'text/plain']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertContains('ON: ', $response->getContent());
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testReadingDirectLinkAsHtml(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/read", [], [], ['HTTP_ACCEPT' => 'text/html']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertContains('directLink = {"id":' . $directLink['id'], $response->getContent());
        $this->assertContains('"on":', $response->getContent());
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
    }

    /** @depends testCreatingDirectLink */
    public function testReadingDoNotExposeLinkDataIfInvalidSlug(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]X/read", [], [], ['HTTP_ACCEPT' => 'text/html']);
        $response = $client->getResponse();
        $this->assertStatusCode(403, $response);
        $this->assertNotContains('directLink = {"id":' . $directLink['id'], $response->getContent());
        $this->assertNotContains('"on":', $response->getContent());
        $this->assertContains('directLink = [];', $response->getContent());
        $this->assertEmpty($this->getSuplaServerCommands($client));
    }

    /** @depends testCreatingDirectLink */
    public function testDisplayingDirectLinkOptionsPage(array $directLink) {
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertContains('directLink = {"id":' . $directLink['id'], $response->getContent());
        $this->assertContains('"on":', $response->getContent());
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
    }

    public function testExecutingDirectLinkWithGetWhenGetDisabled() {
        $directLinkDetails = $this->testCreatingDirectLink();
        $directLink = $this->getEntityManager()->find(DirectLink::class, $directLinkDetails['id']);
        $directLink->setDisableHttpGet(true);
        $this->getEntityManager()->persist($directLink);
        $this->getEntityManager()->flush();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLinkDetails[id]/$directLinkDetails[slug]/turn-on");
        $response = $client->getResponse();
        $this->assertStatusCode(405, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertEmpty($commands);
    }

    public function testExecutingDirectLinkWithPatchWhenGetDisabled() {
        $directLinkDetails = $this->testCreatingDirectLink();
        $directLink = $this->getEntityManager()->find(DirectLink::class, $directLinkDetails['id']);
        $directLink->setDisableHttpGet(true);
        $this->getEntityManager()->persist($directLink);
        $this->getEntityManager()->flush();
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('PATCH', "/direct/$directLinkDetails[id]/$directLinkDetails[slug]/turn-on");
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-CHAR-VALUE:1,1,1,1', $commands);
    }

    public function testCreatingDirectLinkForChannelGroup() {
        $response = $this->createDirectLink([
            'caption' => 'My link',
            'subjectType' => 'channelGroup',
            'subjectId' => $this->channelGroup->getId(),
            'allowedActions' => ['turn-on', 'read'],
        ]);
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My link', $content['caption']);
        $this->assertEquals(ActionableSubjectType::CHANNEL_GROUP, $content['subjectType']);
        $this->assertEquals($this->channelGroup->getId(), $content['subjectId']);
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
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/read", [], [], ['HTTP_ACCEPT' => '*/*']);
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

    public function testExecutingDirectLinkWithParameters() {
        SuplaServerMock::mockResponse('GET-RGBW', "VALUE:1,0,100\n");
        $response = $this->createDirectLink([
            'subjectId' => $this->device->getChannels()[3]->getId(),
            'allowedActions' => ['set-rgbw-parameters'],
        ]);
        $directLink = json_decode($response->getContent(), true);
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/set-rgbw-parameters?brightness=66");
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-RGBW-VALUE:1,1,4,1,0,66', $commands);
    }

    public function testExecutingDirectLinkWithComplexParameters() {
        SuplaServerMock::mockResponse('GET-RGBW', "VALUE:1,0,100\n");
        $response = $this->createDirectLink([
            'subjectId' => $this->device->getChannels()[3]->getId(),
            'allowedActions' => ['set-rgbw-parameters'],
        ]);
        $directLink = json_decode($response->getContent(), true);
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]"
            . '/set-rgbw-parameters?hsv[saturation]=66&hsv[value]=67&hsv[hue]=100');
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-RGBW-VALUE:1,1,4,9437015,67,100', $commands);
    }

    public function testExecutingDirectLinkToOpenValve() {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', ["VALUE:0,0\n", "VALUE:0,0\n"]);
        $response = $this->createDirectLink([
            'subjectId' => $this->device->getChannels()[4]->getId(),
            'allowedActions' => ['open', 'close'],
        ]);
        $directLink = json_decode($response->getContent(), true);
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/open");
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-CHAR-VALUE:1,1,5,1', $commands);
        return $directLink;
    }

    /** @depends testExecutingDirectLinkToOpenValve */
    public function testCantExecuteDirectLinkToOpenValveIfManuallyShut(array $directLink) {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:1,2\n");
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/open");
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
    }

    /** @depends testExecutingDirectLinkToOpenValve */
    public function testCantExecuteDirectLinkToOpenValveIfFlooding(array $directLink) {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:1,1\n");
        $client = $this->createClient();
        $client->enableProfiler();
        $client->request('GET', "/direct/$directLink[id]/$directLink[slug]/open");
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
    }
}
