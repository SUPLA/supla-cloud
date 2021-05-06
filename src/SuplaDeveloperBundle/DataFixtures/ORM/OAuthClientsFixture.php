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
use OAuth2\OAuth2;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ApiClientType;

class OAuthClientsFixture extends SuplaFixture {
    const ORDER = UsersFixture::ORDER + 1;

    public function load(ObjectManager $manager) {
        $user = $this->getReference(UsersFixture::USER);
        $this->createLocalSuplaScriptsClient($manager, $user);
        $manager->flush();
    }

    private function createLocalSuplaScriptsClient(ObjectManager $manager, User $user) {
        $newClient = new ApiClient();
        $newClient->setRandomId('2v9sx1qc6wqocows4ocoo80s4gggg4wcgogk40k4ocosk4o0ck');
        $newClient->setSecret('658cikjp0n40wows4c8sgwcwcow0wk44wcsw84ooks44cc8koo');
        $newClient->setName('SUPLA Scripts Tester');
        $newClient->setRedirectUris(['http://suplascripts.local/authorize']);
        $newClient->setType(ApiClientType::USER());
        $newClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $newClient->setUser($user);
        $manager->persist($newClient);
    }
}
