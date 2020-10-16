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

namespace SuplaBundle\Controller\Api;

use Assert\Assertion;
use FOS\RestBundle\Controller\Annotations as Rest;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Auth\SuplaOAuth2;
use SuplaBundle\Model\Audit\FailedAuthAttemptsUserBlocker;
use SuplaBundle\Model\TargetSuplaCloudRequestForwarder;
use SuplaBundle\Repository\ApiClientRepository;
use SuplaBundle\Repository\UserRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Idea of issuing tokens without client & secret taken from the gist: https://gist.github.com/johnpancoast/359bad0255cb50ccd6ab13e4ac18e4e8
 */
class TokenController extends RestController {
    /** @var SuplaOAuth2 */
    private $server;
    /** @var RouterInterface */
    private $router;
    /** @var ApiClientRepository */
    private $apiClientRepository;
    /** @var FailedAuthAttemptsUserBlocker */
    private $failedAuthAttemptsUserBlocker;
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** @var TargetSuplaCloudRequestForwarder */
    private $suplaCloudRequestForwarder;
    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        SuplaOAuth2 $server,
        RouterInterface $router,
        ApiClientRepository $apiClientRepository,
        FailedAuthAttemptsUserBlocker $failedAuthAttemptsUserBlocker,
        SuplaAutodiscover $autodiscover,
        TokenStorageInterface $tokenStorage,
        TargetSuplaCloudRequestForwarder $suplaCloudRequestForwarder,
        UserRepository $userRepository
    ) {
        $this->server = $server;
        $this->router = $router;
        $this->apiClientRepository = $apiClientRepository;
        $this->failedAuthAttemptsUserBlocker = $failedAuthAttemptsUserBlocker;
        $this->autodiscover = $autodiscover;
        $this->tokenStorage = $tokenStorage;
        $this->suplaCloudRequestForwarder = $suplaCloudRequestForwarder;
        $this->userRepository = $userRepository;
    }

    /** @Rest\Post("/webapp-auth") */
    public function webappAuthAction(Request $request) {
        $username = $request->get('username');
        $password = $request->get('password');
        if (!$username || !$password) {
            return $this->view(
                ['error' => OAuth2::ERROR_INVALID_GRANT, 'error_description' => 'Invalid username and password combination'],
                Response::HTTP_UNAUTHORIZED
            );
        }
        $server = $this->autodiscover->getAuthServerForUser($username);
        if ($server->isLocal()) {
            return $this->issueTokenForWebappAction($request);
        } else {
            list($response, $status) = $this->suplaCloudRequestForwarder->issueWebappToken($server, $username, $password);
            return $this->view($response, $status);
        }
    }

    /**
     * @Rest\Post("webapp-tokens")
     */
    public function issueTokenForWebappAction(Request $request) {
        $webappClient = $this->apiClientRepository->getWebappClient();
        $grantType = $request->get('grant_type', 'password');
        $requestData = [
            'client_id' => $webappClient->getPublicId(),
            'client_secret' => $webappClient->getSecret(),
            'grant_type' => $grantType,
            'scope' => (string)OAuthScope::getScopeForWebappToken(),
        ];
        if ($grantType == OAuth2::GRANT_TYPE_REFRESH_TOKEN) {
            $requestData['refresh_token'] = $request->get('refresh_token');
        } else {
            $requestData = array_merge($requestData, [
                'username' => $request->get('username'),
                'password' => $request->get('password'),
            ]);
            Assertion::notBlank($requestData['username'], 'Please enter a valid email address'); // i18n
            Assertion::notEmpty($requestData['password'], 'The password should be 8 or more characters.'); // i18n
        }
        $tokenRequest = Request::create($this->router->generate('fos_oauth_server_token'), 'POST', $requestData);
        try {
            return $this->server->grantAccessToken($tokenRequest);
        } catch (OAuth2ServerException $e) {
            $username = $request->get('username');
            $user = $this->userRepository->findOneByEmail($username);
            if ($username && $this->failedAuthAttemptsUserBlocker->isAuthenticationFailureLimitExceeded($username)) {
                return $this->view(
                    ['error' => 'locked', 'error_description' => 'Your account has been blocked for a while.'],
                    Response::HTTP_TOO_MANY_REQUESTS
                );
            } elseif ($user && !$user->isEnabled()) {
                return $this->view(
                    ['error' => 'disabled', 'error_description' => 'Your account has not been confirmed.'],
                    Response::HTTP_CONFLICT
                );
            } else {
                return $e->getHttpResponse()->setStatusCode(401);
            }
        }
    }

    /** @Rest\Get("/token-info") */
    public function tokenInfoAction() {
        $token = $this->tokenStorage->getToken()->getCredentials();
        $accessToken = $this->server->getStorage()->getAccessToken($token);
        return $this->view([
            'userShortUniqueId' => $this->getUser()->getShortUniqueId(),
            'scope' => $accessToken->getScope(),
            'expiresAt' => $accessToken->getExpiresAt(),
        ]);
    }
}
