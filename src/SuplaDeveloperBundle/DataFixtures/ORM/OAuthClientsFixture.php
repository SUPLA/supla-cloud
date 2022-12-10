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

use Doctrine\Persistence\ObjectManager;
use OAuth2\OAuth2;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ApiClientType;

class OAuthClientsFixture extends SuplaFixture {
    const ORDER = UsersFixture::ORDER + 1;

    public function load(ObjectManager $manager) {
        /** @var User $user */
        $user = $this->getReference(UsersFixture::USER);
        $this->createLocalSuplaScriptsClient($manager, $user);
        $this->createLocalSuplaCallerClient($manager, $user);
        $this->createLocalSuplaIconsClient($manager, $user);
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

    private function createLocalSuplaCallerClient(ObjectManager $manager, User $user) {
        $newClient = new ApiClient();
        $newClient->setRandomId('CALLERzqczpc4wgk0oo4wsoss040k88sks4goc0osow4sk8cgc');
        $newClient->setSecret('CALLERgd2oowo408gws84kwwo88k8ck8kwk4w0kccog444wocc');
        $newClient->setName('SUPLA Caller Tester');
        $newClient->setRedirectUris(['http://localhost:8080/authorize']);
        $newClient->setType(ApiClientType::USER());
        $newClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $newClient->setUser($user);
        $manager->persist($newClient);
    }

    private function createLocalSuplaIconsClient(ObjectManager $manager, User $user) {
        $newClient = new ApiClient();
        $newClient->setRandomId('ICONSpzqczpc4wgk0oo4wsoss040k88sks4goc0osow4sk8cgc');
        $newClient->setSecret('ICONSpgd2oowo408gws84kwwo88k8ck8kwk4w0kccog444wocc');
        $newClient->setName('SUPLA Icons Tester');
        $newClient->setRedirectUris(['http://localhost:8080/authorize']);
        $newClient->setType(ApiClientType::USER());
        $newClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $newClient->setUser($user);
        $manager->persist($newClient);
    }
}
