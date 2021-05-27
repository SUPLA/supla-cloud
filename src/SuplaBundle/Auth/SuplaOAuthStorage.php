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

namespace SuplaBundle\Auth;

use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2AuthenticateException;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\OAuth\RefreshToken;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ApiClientType;
use SuplaBundle\Enums\AuthenticationFailureReason;
use SuplaBundle\Repository\ApiClientAuthorizationRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SuplaOAuthStorage extends OAuthStorage {
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var UserLoginAttemptListener */
    private $userLoginAttemptListener;
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** @var ApiClientAuthorizationRepository */
    private $apiClientAuthorizationRepository;

    public function __construct(
        ClientManagerInterface $clientManager,
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        AuthCodeManagerInterface $authCodeManager,
        UserProviderInterface $userProvider,
        EncoderFactoryInterface $encoderFactory,
        EntityManagerInterface $entityManager,
        UserLoginAttemptListener $userLoginAttemptListener,
        SuplaAutodiscover $autodiscover,
        ApiClientAuthorizationRepository $apiClientAuthorizationRepository
    ) {
        parent::__construct($clientManager, $accessTokenManager, $refreshTokenManager, $authCodeManager, $userProvider, $encoderFactory);
        $this->entityManager = $entityManager;
        $this->userLoginAttemptListener = $userLoginAttemptListener;
        $this->autodiscover = $autodiscover;
        $this->apiClientAuthorizationRepository = $apiClientAuthorizationRepository;
    }

    /**
     * @param ApiClient $client
     * @param $username
     * @param $plainPassword
     * @return array|bool
     */
    public function checkUserCredentials(IOAuth2Client $client, $username, $plainPassword) {
        $credentialsValid = parent::checkUserCredentials($client, $username, $plainPassword);
        if ($credentialsValid) {
            $this->migrateUserPasswordIfAuthenticatedWithLegacy($username, $plainPassword);
        }
        if ($client->getType() == ApiClientType::WEBAPP()) {
            if ($credentialsValid) {
                $this->userLoginAttemptListener->onAuthenticationSuccess($username);
            } else {
                $this->onAuthenticationFailure($username);
            }
        }
        return $credentialsValid;
    }

    protected function migrateUserPasswordIfAuthenticatedWithLegacy($username, $plainPassword) {
        /** @var User $user */
        $user = $this->userProvider->loadUserByUsername($username);
        if ($user->hasLegacyPassword()) {
            $user->clearLegacyPassword();
            $encoder = $this->encoderFactory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($plainPassword, $user->getSalt()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    private function onAuthenticationFailure($username) {
        $reason = AuthenticationFailureReason::UNKNOWN();
        try {
            $this->userProvider->loadUserByUsername($username);
            $reason = AuthenticationFailureReason::BAD_CREDENTIALS();
        } catch (UsernameNotFoundException $e) {
            $reason = AuthenticationFailureReason::NOT_EXISTS();
        } catch (LockedException $e) {
            $reason = AuthenticationFailureReason::BLOCKED();
        } catch (DisabledException $e) {
            $reason = AuthenticationFailureReason::DISABLED();
        } catch (AuthenticationException $e) {
        }
        $this->userLoginAttemptListener->onAuthenticationFailure($username, $reason);
    }

    public function getClient($clientId) {
        $client = parent::getClient($clientId);
        if (!$client && $this->autodiscover->isBroker()) { // maybe it exists in AD?
            $client = new AutodiscoverPublicClientStub($clientId);
        }
        return $client;
    }

    public function checkClientCredentials(IOAuth2Client $client, $clientSecret = null) {
        if ($client instanceof AutodiscoverPublicClientStub) {
            $client->setSecret($clientSecret);
            return true;
        } else {
            return parent::checkClientCredentials($client, $clientSecret);
        }
    }

    public function createAccessToken($tokenString, IOAuth2Client $client, $data, $expires, $scope = null) {
        $token = parent::createAccessToken($tokenString, $client, $data, $expires, $scope);
        $this->setTokenApiClientAuthorization($token, $client, $data);
        $this->accessTokenManager->updateToken($token);
        return $token;
    }

    public function createRefreshToken($tokenString, IOAuth2Client $client, $data, $expires, $scope = null) {
        $token = parent::createRefreshToken($tokenString, $client, $data, $expires, $scope);
        $this->setTokenApiClientAuthorization($token, $client, $data);
        $this->refreshTokenManager->updateToken($token);
        return $token;
    }

    /**
     * @param AccessToken|RefreshToken $token
     * @param ApiClient $client
     * @param User $user
     * @throws OAuth2AuthenticateException
     */
    private function setTokenApiClientAuthorization($token, $client, $user) {
        $clientType = $client->getType()->getValue();
        if (!in_array($clientType, [ApiClientType::WEBAPP, ApiClientType::CLIENT_APP])) {
            $authorization = $this->apiClientAuthorizationRepository->findOneByUserAndApiClient($user, $client);
            if (!$authorization) {
                throw new OAuth2AuthenticateException(
                    Response::HTTP_UNAUTHORIZED,
                    '',
                    '',
                    SuplaOAuth2::ERROR_INVALID_GRANT,
                    'User has revoked this application.'
                );
            }
            $token->setApiClientAuthorization($authorization);
        }
    }
}
