<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Model;

use App\DataFixtures\DevicesFixture;
use App\Entity\Main\User;
use App\Supla\SuplaServerMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\SuplaApiHelper;

class CalcfgMockScenarioIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    private ?User $user;
    private ?int $deviceId;
    private ?int $channelId;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createCalcfgQueueDevice($location, true);
        $this->deviceId = $device->getId();
        $this->channelId = $device->getChannels()[0]->getId();
    }

    public function testDeferredDeviceAndChannelActions(): void {
        $client = $this->createAuthenticatedClient($this->user);
        SuplaServerMock::mockResponse('ENTER-CONFIGURATION-MODE', "QUEUED:{$this->deviceId},1793577600\n");
        $client->apiRequestV3('PATCH', "/api/iodevices/{$this->deviceId}", ['action' => 'enterConfigurationMode']);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(['status' => 'QUEUED', 'id' => $this->deviceId, 'queuedAt' => 1793577600], $client->getResponseBody()['actionResult']);

        SuplaServerMock::mockResponse('IS-CHANNEL-CONNECTED', "OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED:{$this->deviceId}\n");
        SuplaServerMock::mockResponse('MUTE-ALARM-SOUND', "QUEUED:{$this->channelId},1793577600\n");
        $client->apiRequestV3('PATCH', "/api/channels/{$this->channelId}/settings", ['action' => 'muteAlarm']);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(['status' => 'QUEUED', 'id' => $this->channelId, 'queuedAt' => 1793577600], $client->getResponseBody()['actionResult']);

        foreach (['first-pass', 'other-pass'] as $password) {
            SuplaServerMock::mockResponse('SET-CFG-MODE-PASSWORD', "WAITING_FOR_RESULT:{$this->deviceId},1793577500\n");
            $client->apiRequestV3('PATCH', "/api/iodevices/{$this->deviceId}", ['action' => 'setCfgModePassword', 'password' => $password]);
            $this->assertSame(200, $client->getResponse()->getStatusCode());
            $this->assertSame(
                ['status' => 'WAITING_FOR_RESULT', 'id' => $this->deviceId, 'queuedAt' => 1793577500],
                $client->getResponseBody()['actionResult']
            );
            $this->assertStringNotContainsString($password, $client->getResponse()->getContent());
        }
    }
}
