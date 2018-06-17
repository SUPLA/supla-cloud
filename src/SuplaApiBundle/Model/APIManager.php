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

namespace SuplaApiBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use SuplaApiBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\User as ParentUser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class APIManager {

    protected $doctrine;
    protected $encoder_factory;
    protected $user_rep;
    protected $oauth_client_rep;
    protected $oauth_token_rep;
    protected $oauth_rtoken_rep;
    protected $oauth_code_rep;
    protected $container;

    public function __construct(ManagerRegistry $doctrine, EncoderFactory $encoder_factory, ContainerInterface $container) {
        $this->doctrine = $doctrine;
        $this->encoder_factory = $encoder_factory;
        $this->user_rep = $doctrine->getRepository('SuplaBundle:User');
        $this->oauth_client_rep = $doctrine->getRepository('SuplaApiBundle:OAuth\ApiClient');
        $this->oauth_token_rep = $doctrine->getRepository('SuplaApiBundle:OAuth\AccessToken');
        $this->oauth_rtoken_rep = $doctrine->getRepository('SuplaApiBundle:OAuth\RefreshToken');
        $this->oauth_code_rep = $doctrine->getRepository('SuplaApiBundle:OAuth\AuthCode');
        $this->container = $container;
    }

    public function getAPIUserByName($username) {
        
        $user = null;
        
        if (!filter_var($username, FILTER_VALIDATE_EMAIL)
                && preg_match('/^api_[0-9]+$/', $username) ) {
             $user = $this->user_rep->findOneBy(['oauthCompatUserName' => $username]);
             
            if ($user) {
                $user->setOAuthOldApiCompatEnabled();
            }
        } else {
             $user = $this->user_rep->findOneByEmail($username);
        }

        return $user;
    }

    public function getClient(ParentUser $parent): ApiClient {

        $client = $this->oauth_client_rep->findOneBy(['type' => 0, 'parent' => $parent]);

        if ($client === null) {
            $redirectUri = '/';

            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $client = $clientManager->createClient();
            $client->setParent($parent);
            $client->setRedirectUris([$redirectUri]);
            $client->setAllowedGrantTypes(['authorization_code']);
            $clientManager->updateClient($client);
        }

        return $client;
    }

    public function deleteTokens(User $user) {

        $qb = $this->oauth_token_rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.user = ?1')
            ->setParameters([1 => $user->getId()]);

        return $qb->getQuery()->execute();
    }

    public function deleteRefreshTokens(User $user) {

        $qb = $this->oauth_rtoken_rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.user = ?1')
            ->setParameters([1 => $user->getId()]);

        return $qb->getQuery()->execute();
    }

    public function deleteAuthCodes(User $user) {

        $qb = $this->oauth_code_rep->createQueryBuilder('t');
        $qb
            ->delete()
            ->where('t.user = ?1')
            ->setParameters([1 => $user->getId()]);

        return $qb->getQuery()->execute();
    }

    public function userLogout(User $user, $accessToken, $refreshToken) {

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
