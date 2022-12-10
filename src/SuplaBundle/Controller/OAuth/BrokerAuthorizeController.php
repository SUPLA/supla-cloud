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

namespace SuplaBundle\Controller\OAuth;

use FOS\OAuthServerBundle\Controller\AuthorizeController;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2RedirectException;
use OAuth2\OAuth2ServerException;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BrokerAuthorizeController extends AuthorizeController {
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** @var RequestStack */
    private $requestStack;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ClientManagerInterface */
    private $clientManager;
    /** @var OAuth2 */
    private $oauth2Server;

    /**
     * @Route("/oauth/v2/auth", name="fos_oauth_server_authorize", methods={"GET", "POST"})
     * @UnavailableInMaintenance
     */
    public function authorizeAction(Request $request) {
        try {
            try {
                try {
                    return parent::authorizeAction($request);
                } catch (OAuthReauthenticateException $exception) {
                    return new RedirectResponse($request->getUri());
                } catch (OAuth2RedirectException $redirectException) {
                    throw $redirectException;
                } catch (OAuth2ServerException $e) {
                    throw new ApiException($e->getDescription() ?: 'error', Response::HTTP_BAD_REQUEST, $e);
                }
            } catch (ApiException $e) {
                $this->oauth2Server->finishClientAuthorization(false);
            }
        } catch (OAuth2RedirectException $redirectException) {
            return new Response('', Response::HTTP_FOUND, $redirectException->getResponseHeaders());
        }
    }

    /** @required */
    public function setAutodiscover(SuplaAutodiscover $autodiscover) {
        $this->autodiscover = $autodiscover;
    }

    /** @required */
    public function setRequestStack(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    /** @required */
    public function setTokenStorage(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    /** @required */
    public function setClientManager(ClientManagerInterface $clientManager) {
        $this->clientManager = $clientManager;
    }

    /** @required */
    public function setOAuth2Server(OAuth2 $oauth2Server) {
        $this->oauth2Server = $oauth2Server;
    }

    protected function getClient() {
        try {
            /** @var \SuplaBundle\Entity\Main\OAuth\ApiClient $client */
            $client = parent::getClient();
            if ($client->getPublicClientId()) {
                $clientData = $this->autodiscover->getPublicClient($client->getPublicClientId());
                if ($clientData) {
                    $client->updateDataFromAutodiscover($clientData);
                    $this->clientManager->updateClient($client);
                }
            }
            return $client;
        } catch (NotFoundHttpException $e) {
            $request = $this->requestStack->getCurrentRequest();
            if ($request && $clientId = $request->get('client_id')) {
                $shouldAuthenticateAgain = false;
                if ($this->autodiscover->isTarget()) {
                    // maybe we hit mapped id?
                    $shouldAuthenticateAgain = $this->autodiscover->getPublicIdBasedOnMappedId($clientId);
                }
                if (!$shouldAuthenticateAgain && $this->autodiscover->isBroker()) {
                    // maybe we hit public id?
                    $publicClients = $this->autodiscover->getPublicClients();
                    $shouldAuthenticateAgain = count(array_filter($publicClients, function ($client) use ($clientId) {
                        return $client['id'] == $clientId;
                    }));
                }
                if ($shouldAuthenticateAgain) {
                    $this->tokenStorage->setToken(null);
                    $session = $request->getSession();
                    $session->invalidate();
                    throw new OAuthReauthenticateException();
                }
            }
            throw $e;
        }
    }
}
