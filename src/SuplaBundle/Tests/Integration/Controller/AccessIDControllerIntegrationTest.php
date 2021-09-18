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

use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class AccessIDControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    public function testCreatingAid() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/accessids');
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Access Identifier #', $content['caption']);
    }

    public function testCreatingLocationWithPlLocale() {
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->user->setLocale('pl_PL');
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/accessids');
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Identyfikator Dostępu #', $content['caption']);
    }
}
