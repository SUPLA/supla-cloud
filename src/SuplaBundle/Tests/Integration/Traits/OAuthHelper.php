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

namespace SuplaBundle\Tests\Integration\Traits;

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use Psr\Container\ContainerInterface;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Repository\ApiClientAuthorizationRepository;

/**
 * @property ContainerInterface $container
 */
trait OAuthHelper {
    protected function createApiClient(string $publicId = null): ApiClient {
        $clientManager = self::$container->get(ClientManagerInterface::class);
        /** @var ApiClient $client */
        $client = $clientManager->createClient();
        if ($publicId) {
            $client->setPublicClientId($publicId);
        }
        $client->setRedirectUris(['https://unicorns.pl']);
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $clientManager->updateClient($client);
        return $client;
    }

    protected function createApiClientAuthorization(ApiClient $client, User $user, $scope = null): ApiClientAuthorization {
        /** @var ApiClientAuthorizationRepository $authorizationRepository */
        $authorizationRepository = $this->getDoctrine()->getRepository(ApiClientAuthorization::class);
        $authorization = $authorizationRepository->findOneByUserAndApiClient($user, $client);
        if ($authorization) {
            $authorization->authorizeNewScope($scope);
            $this->getEntityManager()->persist($authorization);
        } else {
            $authorization = $user->addApiClientAuthorization($client, $scope);
            $this->getEntityManager()->persist($user);
        }
        $this->getEntityManager()->flush();
        return $authorization;
    }

    protected function createAccessToken(ApiClient $client, User $user, $scope = null, $tokenCode = 'ABC'): AccessToken {
        $authorization = $this->createApiClientAuthorization($client, $user, $scope);
        $token = new AccessToken();
        $token->setClient($client);
        $token->setUser($user);
        $token->setToken($tokenCode);
        $scope = $scope ? new OAuthScope($scope) : new OAuthScope(OAuthScope::getAllKnownScopes());
        $token->setScope($scope);
        $token->setApiClientAuthorization($authorization);
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
        return $token;
    }
}
