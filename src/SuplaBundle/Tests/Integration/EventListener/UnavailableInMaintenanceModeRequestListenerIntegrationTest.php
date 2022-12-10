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

namespace SuplaBundle\Tests\Integration\EventListener;

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\EventListener\UnavailableInMaintenanceRequestListener;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class UnavailableInMaintenanceModeRequestListenerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    public function testCannotCreateLocationInMaintenanceMode() {
        $client = $this->createAuthenticatedClient($this->user);
        $listener = $client->getContainer()->get(UnavailableInMaintenanceRequestListener::class);
        EntityUtils::setField($listener, 'maintenanceMode', true);
        $client->apiRequestV24('POST', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(503, $response);
    }

    public function testCanGetLocationsInMaintenanceMode() {
        $client = $this->createAuthenticatedClient($this->user);
        $listener = $client->getContainer()->get(UnavailableInMaintenanceRequestListener::class);
        EntityUtils::setField($listener, 'maintenanceMode', true);
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
    }

    public function testCanCreateLocationWhenNoMaintenanceMode() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
    }
}
