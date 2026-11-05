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

namespace App\Tests\Integration\Serialization;

use App\Entity\EntityUtils;
use App\Entity\Main\IODevice;
use App\Enums\IoDeviceFlags;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\SuplaApiHelper;
use App\Tests\Integration\Traits\TestTimeProvider;

/** @small */
class IODeviceSerializerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \App\Entity\Main\User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDeviceFull($location);
    }

    public function testSerializingIODevice() {
        $serializedDevice = self::$container->get('serializer')->serialize($this->device, 'json', ['groups' => ['basic']]);
        $deviceJson = json_decode($serializedDevice, true);
        $this->assertEquals($this->device->getId(), $deviceJson['id']);
        $this->assertFalse(isset($deviceJson['location']));
    }

    public function testSerializesBackendDerivedOfflineActionEligibility() {
        $actionSupport = IoDeviceFlags::ENTER_CONFIGURATION_MODE_AVAILABLE
            | IoDeviceFlags::REMOTE_RESTART_AVAILABLE
            | IoDeviceFlags::IDENTIFY_DEVICE_AVAILABLE
            | IoDeviceFlags::FACTORY_RESET_SUPPORTED
            | IoDeviceFlags::AUTOMATIC_FIRMWARE_UPDATE_SUPPORTED
            | IoDeviceFlags::SET_CFG_MODE_PASSWORD_SUPPORTED;
        $cases = [
            'awake devices do not advertise offline actions' => [0, false, false],
            'legacy sleeping devices advertise supported device actions only' => [IoDeviceFlags::SLEEP_MODE_ENABLED, true, false],
            'sync-capable sleeping devices include other supported queue commands' => [
                IoDeviceFlags::SLEEP_MODE_ENABLED | IoDeviceFlags::SYNC_DONE_SUPPORTED,
                true,
                true,
            ],
        ];
        foreach ($cases as $name => [$sleepFlags, $legacyActionsExpected, $syncActionsExpected]) {
            EntityUtils::setField($this->device, 'flags', $actionSupport | $sleepFlags);
            $deviceJson = json_decode(self::$container->get('serializer')->serialize($this->device, 'json', ['groups' => ['basic']]), true);
            $this->assertSame($syncActionsExpected, $deviceJson['flags']['syncDoneSupported'], $name);
            $this->assertSame($legacyActionsExpected, $deviceJson['calcfgQueueableActions']['enterConfigurationMode'], $name);
            $this->assertSame($legacyActionsExpected, $deviceJson['calcfgQueueableActions']['restartDevice'], $name);
            $this->assertSame($legacyActionsExpected, $deviceJson['calcfgQueueableActions']['identifyDevice'], $name);
            $this->assertSame($syncActionsExpected, $deviceJson['calcfgQueueableActions']['factoryReset'], $name);
            $this->assertSame($syncActionsExpected, $deviceJson['calcfgQueueableActions']['setCfgModePassword'], $name);
            $this->assertSame($syncActionsExpected, $deviceJson['calcfgQueueableActions']['otaCheckUpdates'], $name);
            $this->assertSame($syncActionsExpected, $deviceJson['calcfgQueueableActions']['otaPerformUpdate'], $name);
        }
    }

    public function testDoesNotAdvertiseUnsupportedDeviceActions() {
        EntityUtils::setField($this->device, 'flags', IoDeviceFlags::SLEEP_MODE_ENABLED | IoDeviceFlags::SYNC_DONE_SUPPORTED);
        $deviceJson = json_decode(self::$container->get('serializer')->serialize($this->device, 'json', ['groups' => ['basic']]), true);

        foreach ($deviceJson['calcfgQueueableActions'] as $action => $queueable) {
            $this->assertFalse($queueable, "$action must not be advertised without firmware support.");
        }
    }

    public function testSerializesChannelEligibilityWithEmptyPublicConfig() {
        $channel = $this->device->getChannels()[0];
        EntityUtils::setField($this->device, 'flags', IoDeviceFlags::SLEEP_MODE_ENABLED | IoDeviceFlags::SYNC_DONE_SUPPORTED);
        EntityUtils::setField($channel, 'flags', 0);
        $channel->setUserConfig([]);

        $channelJson = json_decode(self::$container->get('serializer')->serialize($channel, 'json', ['groups' => ['basic']]), true);

        $this->assertSame([
            'muteAlarm' => false,
            'ocr:takePhoto' => false,
            'identify' => false,
            'restart' => false,
        ], $channelJson['calcfgQueueableActions']);
        $this->assertSame([], (array)($channelJson['config'] ?? []));
    }

    public function testSerializingIODeviceWithLocation() {
        $serializedDevice = self::$container->get('serializer')
            ->serialize($this->device, 'json', ['groups' => ['basic', 'iodevice.location']]);
        $deviceJson = json_decode($serializedDevice, true);
        $this->assertEquals($this->device->getId(), $deviceJson['id']);
        $this->assertTrue(isset($deviceJson['location']));
        $this->assertFalse(isset($deviceJson['connected']));
    }

    public function testSerializingPairingStatus() {
        EntityUtils::setField($this->device, 'pairingResult', json_encode([
            'time' => (new \DateTime())->format(\DateTime::ATOM),
            'result' => 'ONGOING',
        ]));
        $serializedDevice = self::$container->get('serializer')
            ->serialize($this->device, 'json', ['groups' => ['basic', 'pairingResult']]);
        $deviceJson = json_decode($serializedDevice, true);
        $this->assertEquals($this->device->getId(), $deviceJson['id']);
        $this->assertArrayHasKey('pairingResult', $deviceJson);
        $this->assertFalse($deviceJson['pairingResult']['timedOut']);
    }

    public function testSerializingPairingStatusTimedOut() {
        EntityUtils::setField($this->device, 'pairingResult', json_encode([
            'time' => (new \DateTime())->format(\DateTime::ATOM),
            'result' => 'ONGOING',
        ]));
        TestTimeProvider::setTime('+10 minutes');
        $serializedDevice = self::$container->get('serializer')
            ->serialize($this->device, 'json', ['groups' => ['basic', 'pairingResult']]);
        $deviceJson = json_decode($serializedDevice, true);
        $this->assertEquals($this->device->getId(), $deviceJson['id']);
        $this->assertArrayHasKey('pairingResult', $deviceJson);
        $this->assertTrue($deviceJson['pairingResult']['timedOut']);
    }
}
