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
use SuplaApiBundle\Auth\OAuthScope;
use SuplaApiBundle\Entity\EntityUtils;
use SuplaApiBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\User;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\ApiClientRepository;

class UsersFixture extends SuplaFixture {
    const ORDER = 0;

    const USER = 'user';

    public function load(ObjectManager $manager) {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('user_manager');
        $user = new User();
        $user->setEmail('user@supla.org');
        $user->agreeOnRules();
        $user->agreeOnCookies();
        $userManager->create($user);
        $userManager->setPassword('pass', $user, true);
        $userManager->confirm($user->getToken());
        $this->addReference(self::USER, $user);

        // create an always valid simple access token issued for webapp
        $webappClient = $this->container->get(ApiClientRepository::class)->getWebappClient();
        $token = new AccessToken();
        EntityUtils::setField($token, 'client', $webappClient);
        EntityUtils::setField($token, 'user', $user);
        EntityUtils::setField($token, 'expiresAt', (new \DateTime('2035-01-01T00:00:00'))->getTimestamp());
        EntityUtils::setField($token, 'token', '0123456789012345678901234567890123456789');
        EntityUtils::setField($token, 'scope', (string)(new OAuthScope(OAuthScope::getSupportedScopes())));
        $em = $this->container->get('doctrine')->getManager();
        $em->persist($token);
        $em->flush();
    }
}
