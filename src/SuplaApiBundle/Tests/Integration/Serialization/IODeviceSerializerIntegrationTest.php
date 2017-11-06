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

namespace SuplaApiBundle\Tests\Integration;

use JMS\Serializer\Serializer;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;

class IODeviceSerializerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function setUp() {
        $this->user = $this->createConfirmedUserWithApiAccess();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDeviceFull($location);
    }

    public function testSeralizingIODevice() {
        $serializedDevice = $this->container->get('serializer')->serialize($this->device, 'json', ['groups' => ['basic']]);
        $deviceJson = json_decode($serializedDevice, true);
        $this->assertEquals($this->device->getId(), $deviceJson['id']);
        $this->assertFalse(isset($deviceJson['location']));
    }

    public function testSeralizingIODeviceWithLocation() {
        $serializedDevice = $this->container->get('serializer')->serialize($this->device, 'json', ['groups' => ['basic', 'location']]);
        $deviceJson = json_decode($serializedDevice, true);
        $this->assertEquals($this->device->getId(), $deviceJson['id']);
        $this->assertTrue(isset($deviceJson['location']));
    }

}
