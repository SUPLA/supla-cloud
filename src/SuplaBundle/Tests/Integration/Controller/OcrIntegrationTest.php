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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestSuplaHttpClient;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class OcrIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    private ?User $user;
    private ?IODevice $device;
    private ?IODeviceChannel $counter;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->device = $this->createDevice($this->createLocation($this->user), [
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_WATERMETER],
        ]);
        $this->counter = $this->device->getChannels()[0];
        EntityUtils::setField($this->counter, 'properties', json_encode(['ocr' => ['authKey' => '123']]));
        $this->persist($this->counter);
    }

    public function testSyncingOcrSettings() {
        TestSuplaHttpClient::mockHttpRequest('/devices', function (array $request) {
            $this->assertEquals('POST', $request['method']);
            $this->assertEquals($this->counter->getIoDevice()->getGUIDString(), $request['payload']['guid']);
            $this->assertEquals($this->counter->getChannelNumber(), $request['payload']['channelNo']);
            $this->assertEquals('123', $request['payload']['authKey']);
            return [true, '', 201];
        });
        $command = $this->application->find('supla:cyclic:synchronize-ocr-authkeys');
        $commandTester = new CommandTester($command);
        $result = $commandTester->execute([]);
        $this->assertEquals(0, $result);
        $counter = $this->freshEntity($this->counter);
        $this->assertTrue($counter->getProperty('ocr')['ocrSynced']);
    }

    public function testGettingLatestPhoto() {
        $client = $this->createAuthenticatedClient($this->user);
        TestSuplaHttpClient::mockHttpRequest('/latest', ['image' => 'abcjpeg']);
        $client->apiRequestV3('GET', "/api/integrations/ocr/{$this->counter->getId()}/latest");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('abcjpeg', $content['image']);
    }

    public function testUpdatingOcrSettings() {
        $client = $this->createAuthenticatedClient($this->user);
        $photoSettings = ['unicorn' => 'crop it!'];
        TestSuplaHttpClient::mockHttpRequest($this->device->getGUIDString(), function (array $request) use ($photoSettings) {
            $this->assertEquals('PUT', $request['method']);
            $this->assertEquals(['config' => $photoSettings], $request['payload']);
            return [true, '', 200];
        });
        $client->apiRequestV3('PUT', "/api/channels/{$this->counter->getId()}", [
            'config' => ['ocr' => ['photoSettings' => $photoSettings, 'photoIntervalSec' => 66]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $counter = $this->freshEntity($this->counter);
        $this->assertEquals(['photoIntervalSec' => 66, 'photoSettings' => $photoSettings], $counter->getUserConfigValue('ocr'));
    }
}
