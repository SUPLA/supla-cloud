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

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Tests\AnyFieldSetter;

class ClientAppsFixture extends SuplaFixture {
    const ORDER = AccessIdsFixture::ORDER + 1;

    public function load(ObjectManager $manager) {
        $user = $this->getReference(UsersFixture::USER);
        $accessIds = [null, $this->getReference(AccessIdsFixture::ACCESS_ID_CHILDREN), $this->getReference(AccessIdsFixture::ACCESS_ID_COMMON)];
        foreach (['HTC One M8', 'iPhone 6s', 'Nokia 3310', 'Samsung Galaxy Tab S2', 'Apple iPad'] as $name) {
            $clientApp = new ClientApp();
            AnyFieldSetter::set($clientApp, [
                'guid' => rand(0, 9999999),
                'regDate' => new \DateTime('-' . rand(86400 * 7, 86400 * 60) . 'seconds'),
                'regIpv4' => rand(1, 2147483647),
                'lastAccessDate' => new \DateTime('-' . rand(86400, 86400 * 7) . 'seconds'),
                'lastAccessIpv4' => rand(1, 2147483647),
                'softwareVersion' => '1.' . rand(1, 100),
                'protocolVersion' => rand(1, 100),
                'user' => $user,
                'name' => $name,
                'enabled' => rand() % 2,
                'accessId' => $accessIds[rand(0, count($accessIds) - 1)],
            ]);
            $manager->persist($clientApp);
        }
        $manager->flush();
    }
}
