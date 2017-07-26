<?php

namespace SuplaApiBundle\Tests\Integration;

use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;

class ApiChannelControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function setUp() {
        $this->user = $this->createConfirmedUserWithApiAccess();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDeviceFull($location);
    }

    public function testGettingChannelInfo() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $channel = $this->device->getChannels()[0];
        $client->request('GET', '/api/channels/' . $channel->getId());
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertTrue($content->enabled);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertGreaterThanOrEqual(1, count($commands));
    }

    /**
     * @dataProvider changingChannelStateDataProvider
     */
    public function testChangingChannelState(int $deviceId, string $action, string $expectedCommand, array $additionalRequest = []) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $request = array_merge(['action' => $action], $additionalRequest);
        $client->request('PATCH', '/api/channels/' . $deviceId, [], [], [], json_encode($request));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains($expectedCommand, $commands);
    }

    public function changingChannelStateDataProvider() {
        return [
            [1, 'turn-on', 'SET-CHAR-VALUE:1,1,1,1'],
            [1, 'turn-off', 'SET-CHAR-VALUE:1,1,1,0'],
            [2, 'open', 'SET-CHAR-VALUE:1,1,2,1'],
            [3, 'open-close', 'SET-CHAR-VALUE:1,1,3,1'],
            [4, 'shut', 'SET-CHAR-VALUE:1,1,4,10'],
            [4, 'reveal', 'SET-CHAR-VALUE:1,1,4,110'],
            [4, 'stop', 'SET-CHAR-VALUE:1,1,4,0'],
            [4, 'shut', 'SET-CHAR-VALUE:1,1,4,50', ['percent' => 40]],
            [4, 'reveal', 'SET-CHAR-VALUE:1,1,4,50', ['percent' => 60]],
        ];
    }

    public function testTryingToExecuteInvalidAction() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->request('PATCH', '/api/channels/' . 1, [], [], [], json_encode(array_merge(['action' => 'open'])));
        $response = $client->getResponse();
        $this->assertStatusCode('4xx', $response);
    }
}
