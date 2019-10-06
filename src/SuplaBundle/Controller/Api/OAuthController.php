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
use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use OAuth2\OAuth2;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Auth\SuplaOAuth2;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\OAuth\ApiClientAuthorization;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AccessTokenRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OAuthController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var AccessTokenRepository */
    private $accessTokenRepository;
    /** @var SuplaOAuth2 */
    private $oauthServer;
    /** @var ClientManagerInterface */
    private $clientManager;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        SuplaOAuth2 $oauthServer,
        ClientManagerInterface $clientManager,
        SuplaAutodiscover $autodiscover
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->oauthServer = $oauthServer;
        $this->clientManager = $clientManager;
        $this->autodiscover = $autodiscover;
    }

    /**
     * @Security("has_role('ROLE_WEBAPP')")
     * @Rest\Get("/oauth-clients")
     */
    public function getOAuthClientsAction(Request $request) {
        $applications = $this->getUser()->getApiClients();
        return $this->serializedView($applications, $request);
    }

    /**
     * @Security("client.belongsToUser(user) and has_role('ROLE_WEBAPP')")
     * @Rest\Get("/oauth-clients/{client}")
     */
    public function getOAuthClientAction(ApiClient $client, Request $request) {
        $view = $this->view($client, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['secret']);
        return $view;
    }

    /**
     * @Security("has_role('ROLE_WEBAPP')")
     * @Rest\Post("/oauth-clients")
     */
    public function postOAuthClientsAction(ApiClient $newClient, Request $request) {
        $user = $this->getUser();
        Assertion::false($user->isLimitOAuthClientExceeded(), 'OAuth clients limit has been exceeded');
        $newClient->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $newClient->setUser($user);
        $this->clientManager->updateClient($newClient);
        return $this->getOAuthClientAction($newClient, $request);
    }

    /**
     * @Security("client.belongsToUser(user) && has_role('ROLE_WEBAPP')")
     * @Rest\Put("/oauth-clients/{client}")
     */
    public function putOAuthClientAction(ApiClient $client, ApiClient $updatedClient, Request $request) {
        $client->setName($updatedClient->getName());
        $client->setDescription($updatedClient->getDescription());
        $client->setRedirectUris($updatedClient->getRedirectUris());
        $this->clientManager->updateClient($client);
        return $this->getOAuthClientAction($client, $request);
    }

    /**
     * @Security("client.belongsToUser(user) and has_role('ROLE_WEBAPP')")
     * @Rest\Delete("/oauth-clients/{client}")
     */
    public function deleteOAuthClientAction(ApiClient $client) {
        return $this->transactional(function (EntityManagerInterface $em) use ($client) {
            $em->remove($client);
            $this->suplaServer->onOAuthClientRemoved();
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Get("/oauth-authorized-clients")
     * @Security("has_role('ROLE_WEBAPP')")
     */
    public function getAuthorizedClientsAction(Request $request) {
        $apps = $this->getUser()->getApiClientAuthorizations();
        $view = $this->view($apps, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['client']);
        return $view;
    }

    /**
     * @Rest\Delete("/oauth-authorized-clients/{authorizedApp}")
     * @Security("authorizedApp.belongsToUser(user) and has_role('ROLE_WEBAPP')")
     */
    public function deleteAuthorizedClientsAction(ApiClientAuthorization $authorizedApp, Request $request) {
        return $this->transactional(function (EntityManagerInterface $em) use ($authorizedApp) {
            $em->remove($authorizedApp);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Get("/oauth-personal-tokens")
     * @Security("has_role('ROLE_WEBAPP')")
     */
    public function getPersonalTokensAction(Request $request) {
        $accessTokens = $this->accessTokenRepository->findPersonalTokens($this->getUser());
        return $this->serializedView($accessTokens, $request);
    }

    /**
     * @Rest\Post("/oauth-personal-tokens")
     * @Security("has_role('ROLE_WEBAPP')")
     */
    public function postPersonalTokensAction(Request $request) {
        $data = $request->request->all();
        Assertion::keyExists($data, 'name');
        Assertion::keyExists($data, 'scope');
        Assertion::notBlank(
            $data['name'],
            'Personal token name is required.' // i18n
        );
        $scope = new OAuthScope($data['scope']);
        $token = $this->transactional(function (EntityManagerInterface $entityManager) use ($data, $scope) {
            $token = $this->oauthServer->createPersonalAccessToken($this->getUser(), $data['name'], $scope);
            $entityManager->persist($token);
            return $token;
        });
        return $this->serializedView($token, $request, ['token'], Response::HTTP_CREATED);
    }

    /**
     * @Rest\Delete("/oauth-personal-tokens/{accessToken}")
     * @Security("accessToken.belongsToUser(user) and accessToken.isPersonal() and has_role('ROLE_WEBAPP')")
     */
    public function deletePersonalTokenAction(AccessToken $accessToken, Request $request) {
        return $this->transactional(function (EntityManagerInterface $em) use ($accessToken) {
            $em->remove($accessToken);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Rest\Get("/public-oauth-apps")
     */
    public function getPublicOAuthAppsAction() {
        if ($this->autodiscover->isTarget()) {
            $clients = $this->autodiscover->getPublicClients();
            return $this->view($clients, Response::HTTP_OK);
        } else {
            return new Response('', Response::HTTP_NOT_ACCEPTABLE);
        }
    }
}
