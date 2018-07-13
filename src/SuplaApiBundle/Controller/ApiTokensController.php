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

namespace SuplaApiBundle\Controller;

use Assert\Assertion;
use FOS\RestBundle\Controller\Annotations as Rest;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use SuplaApiBundle\Auth\SuplaOAuth2;
use SuplaBundle\Repository\ApiClientRepository;
use SuplaBundle\Supla\ServerList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Idea of issuing tokens without client & secret taken from the gist: https://gist.github.com/johnpancoast/359bad0255cb50ccd6ab13e4ac18e4e8
 */
class ApiTokensController extends RestController {
    /** @var SuplaOAuth2 */
    private $server;
    /** @var RouterInterface */
    private $router;
    /** @var ApiClientRepository */
    private $apiClientRepository;
    /** @var ServerList */
    private $serverList;

    public function __construct(
        SuplaOAuth2 $server,
        RouterInterface $router,
        ApiClientRepository $apiClientRepository,
        ServerList $serverList
    ) {
        $this->server = $server;
        $this->router = $router;
        $this->apiClientRepository = $apiClientRepository;
        $this->serverList = $serverList;
    }

    /** @Rest\Post("/webapp-auth") */
    public function webappAuthAction(Request $request) {
        $username = $request->get('username');
        $password = $request->get('password');
        Assertion::notBlank($username, 'Username is required.');
        Assertion::notEmpty($password, 'Password is required.');
        $server = $this->serverList->getAuthServerForUser($username);
        if ($server->isLocal()) {
            return $this->issueTokenForWebappAction($request);
        } else {
            list($response, $status) = $server->issueWebappToken($username, $password);
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
            'scope' => 'restapi',
        ];
        if ($grantType == OAuth2::GRANT_TYPE_REFRESH_TOKEN) {
            $requestData['refresh_token'] = $request->get('refresh_token');
        } else {
            $requestData = array_merge($requestData, [
                'username' => $request->get('username'),
                'password' => $request->get('password'),
            ]);
        }
        $tokenRequest = Request::create($this->router->generate('fos_oauth_server_token'), 'POST', $requestData);
        try {
            return $this->server->grantAccessToken($tokenRequest);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse()->setStatusCode(401);
        }
    }
}
