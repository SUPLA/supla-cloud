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

use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

class IODevicesControllerIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use UserFixtures;

    /** @var User */
    private $user;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->createDeviceSonoff($location);
        $this->getEntityManager()->refresh($this->user);
    }

    public function testGetDeviceDetails() {
        $client = self::createClient([], [
            'PHP_AUTH_USER' => $this->user->getEmail(),
            'PHP_AUTH_PW' => 'supla123',
        ]);
        $client->followRedirects(true);
        $deviceId = $this->user->getIODevices()->first()->getId();
        $crawler = $client->request('GET', "/iodev/$deviceId/view");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertCount(2, $crawler->filter('.channels .element-item'));
    }
}
