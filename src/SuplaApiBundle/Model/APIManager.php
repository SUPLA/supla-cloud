<?php
/*
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

namespace SuplaApiBundle\Model;

use SuplaApiBundle\Entity\ApiUser as APIUser;
use SuplaBundle\Entity\User as ParentUser;

class APIManager {

    protected $doctrine;
    protected $encoder_factory;
    protected $oauth_user_rep;
    protected $oauth_client_rep;
    protected $oauth_token_rep;
    protected $oauth_rtoken_rep;
    protected $oauth_code_rep;
    protected $container;

    public function __construct($doctrine, $encoder_factory, $container) {
        $this->doctrine = $doctrine;
        $this->encoder_factory = $encoder_factory;
        $this->oauth_user_rep = $doctrine->getRepository('SuplaApiBundle:ApiUser');
        $this->oauth_client_rep = $doctrine->getRepository('SuplaApiBundle:Client');
        $this->oauth_token_rep = $doctrine->getRepository('SuplaApiBundle:AccessToken');
        $this->oauth_rtoken_rep = $doctrine->getRepository('SuplaApiBundle:RefreshToken');
        $this->oauth_code_rep = $doctrine->getRepository('SuplaApiBundle:AuthCode');
        $this->container = $container;
    }

    public function getAPIUser(ParentUser $parent) {

        $api_user = $this->oauth_user_rep->findOneBy(['parent' => $parent, 'accessId' => null]);

        if ($api_user === null) {
            $api_user = new APIUser($parent);

            if ($api_user !== null) {
                $m = $this->doctrine->getManager();
                $m->persist($api_user);
                $m->flush();
            }
        }

        return $api_user;
    }

    public function getAPIUserByName($username) {

        $username = preg_replace('/^api_/', '', $username);
        return $this->oauth_user_rep->findOneBy(['id' => intval($username)]);
    }

    public function getClient(ParentUser $parent) {

        $client = $this->oauth_client_rep->findOneBy(['type' => 0, 'parent' => $parent]);

        if ($client === null) {
            $redirectUri = '/';

            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $client = $clientManager->createClient();
            $client->setParent($parent);
            $client->setRedirectUris([$redirectUri]);
            $client->setAllowedGrantTypes(['password']);
            $clientManager->updateClient($client);
        }

        return $client;
    }

    public function setPassword($password, APIUser $user, $flush = false) {
        $user->setPlainPassword($password);
        $encoder = $this->encoder_factory->getEncoder($user);
        $password = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($password);

        if ($flush === true) {
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
        }
    }

    public function deleteTokens(APIUser $user) {

        $qb = $this->oauth_token_rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.user = ?1')
            ->setParameters([1 => $user->getId()]);

        return $qb->getQuery()->execute();
    }

    public function deleteRefreshTokens(APIUser $user) {

        $qb = $this->oauth_rtoken_rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.user = ?1')
            ->setParameters([1 => $user->getId()]);

        return $qb->getQuery()->execute();
    }

    public function deleteAuthCodes(APIUser $user) {

        $qb = $this->oauth_code_rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.user = ?1')
            ->setParameters([1 => $user->getId()]);

        return $qb->getQuery()->execute();
    }

    public function setEnabled($enabled, APIUser $user, $flush = false) {
        $user->setEnabled($enabled);

        if ($flush === true) {
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
        }

        if ($enabled === false) {
            $this->deleteTokens($user);
            $this->deleteRefreshTokens($user);
            $this->deleteRefreshTokens($user);
            $this->deleteAuthCodes($user);
        }
    }

    public function userLogout(APIUser $user, $accessToken, $refreshToken) {

        $qb = $this->oauth_token_rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.user = ?1 AND t.token = ?2')
            ->setParameters([1 => $user->getId(), 2 => $accessToken]);

        $qb->getQuery()->execute();

        $qb = $this->oauth_rtoken_rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.user = ?1 AND t.token = ?2')
            ->setParameters([1 => $user->getId(), 2 => $refreshToken]);

        $qb->getQuery()->execute();
    }
}
