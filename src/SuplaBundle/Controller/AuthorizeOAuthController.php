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

namespace SuplaBundle\Controller;

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Auth\ForwardRequestToTargetCloudException;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Enums\ApiClientType;
use SuplaBundle\Model\Audit\FailedAuthAttemptsUserBlocker;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class AuthorizeOAuthController extends Controller {
    /** @var FailedAuthAttemptsUserBlocker */
    private $failedAuthAttemptsUserBlocker;
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** @var ClientManagerInterface */
    private $clientManager;

    const LAST_TARGET_CLOUD_ADDRESS_KEY = '_lastTargetCloud';
    /** @var OAuth2 */
    private $oauth2;

    public function __construct(
        FailedAuthAttemptsUserBlocker $failedAuthAttemptsUserBlocker,
        SuplaAutodiscover $autodiscover,
        ClientManagerInterface $clientManager,
        OAuth2 $oauth2
    ) {
        $this->failedAuthAttemptsUserBlocker = $failedAuthAttemptsUserBlocker;
        $this->autodiscover = $autodiscover;
        $this->clientManager = $clientManager;
        $this->oauth2 = $oauth2;
    }

    /**
     * @Route("/oauth-authorize", name="_oauth_login")
     * @Route("/oauth/v2/auth_login", name="_oauth_login_check")
     * @Route("/oauth/v2/broker_login", name="_oauth_broker_check", methods={"POST"})
     * @Template()
     */
    public function oAuthLoginAction(Request $request) {
        $session = $request->getSession();
        $lastUsername = $session->get(Security::LAST_USERNAME);

        $askForTargetCloud = false;
        if ($this->autodiscover->enabled()) {
            $targetPath = $session->get('_security.oauth_authorize.target_path');
            if (preg_match('#/oauth/v2/auth/?\?(.+)#', $targetPath, $match)) {
                parse_str($match[1], $oauthParams);
            }
            if (isset($oauthParams['client_id'])) {
                if ($this->autodiscover->isBroker() && $request->isMethod(Request::METHOD_POST)) {
                    return $this->handleBrokerAuth($request, $oauthParams);
                } elseif (!$this->clientManager->findClientByPublicId($oauthParams['client_id'])) {
                    // this client does not exist. maybe it has just been created in AD?
                    if ($redirectionToNewClient = $this->fetchClientFromAutodiscover($oauthParams)) {
                        return $redirectionToNewClient;
                    } else if ($this->autodiscover->isBroker()) {
                        // this client neither exists nor AD provided it. Maybe it exists somewhere else... lets ask for the Target Cloud!
                        $askForTargetCloud = true;
                    }
                }
            }
            if (!$lastUsername && isset($oauthParams) && isset($oauthParams['ad_username'])) {
                $lastUsername = $oauthParams['ad_username'];
            }
        }

        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }

        $lastTargetCloudAddress = $session->get(self::LAST_TARGET_CLOUD_ADDRESS_KEY);
        if ($error && $error != 'autodiscover_fail') {
            $error = 'error';
            if ($lastUsername && $this->failedAuthAttemptsUserBlocker->isAuthenticationFailureLimitExceeded($lastUsername)) {
                $error = 'locked';
            }
        }

        return [
            'last_username' => $lastUsername,
            'error' => $error,
            'askForTargetCloud' => $askForTargetCloud,
            'lastTargetCloud' => $lastTargetCloudAddress,
        ];
    }

    /**
     * @Route("/oauth/v2/token", name="fos_oauth_server_token", methods={"GET", "POST"})
     */
    public function tokenAction(Request $request) {
        try {
            return $this->oauth2->grantAccessToken($request);
        } catch (ForwardRequestToTargetCloudException $e) {
            $targetCloud = $e->getTargetCloud();
            list($response, $status) = $targetCloud->issueOAuthToken($request, $e->getMappedClientData());
            return new Response($response, $status);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }

    private function handleBrokerAuth(Request $request, array $oauthParams): Response {
        $session = $request->getSession();
        $username = $request->get('_username');
        $targetCloud = $request->get('targetCloud', null);
        $protocol = $this->getParameter('supla_protocol');
        if ($targetCloud) {
            $session->set(self::LAST_TARGET_CLOUD_ADDRESS_KEY, $targetCloud);
            $targetCloud = new TargetSuplaCloud($protocol . '://' . $targetCloud, false);
        } else {
            $session->remove(self::LAST_TARGET_CLOUD_ADDRESS_KEY);
        }
        if (!$targetCloud) {
            if ($this->autodiscover->userExists($username)) {
                $targetCloud = $this->autodiscover->getAuthServerForUser($username);
            }
        }
        if ($targetCloud) {
            $mappedClientId = $this->autodiscover->getTargetCloudClientId($targetCloud, $oauthParams['client_id']);
            if ($mappedClientId) {
                $oauthParams['client_id'] = $mappedClientId;
                $oauthParams['ad_username'] = $username;
                $redirectUrl = $targetCloud->getOauthAuthUrl($oauthParams);
                return $this->redirect($redirectUrl);
            }
        }
        $session->set(Security::LAST_USERNAME, $username);
        $session->set(Security::AUTHENTICATION_ERROR, 'autodiscover_fail');
        return $this->redirectToRoute('_oauth_login');
    }

    private function fetchClientFromAutodiscover(array $oauthParams) {
        // TODO this must be authorized!
        $clientData = $this->autodiscover->fetchTargetCloudClientData($oauthParams['client_id']);
        if ($clientData) {
            /** @var ApiClient $client */
            $client = $this->clientManager->createClient();
            $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
            $client->setType(ApiClientType::BROKER());
            $client->setName($clientData['name']);
            $client->setDescription($clientData['description']);
            $client->setRedirectUris($clientData['redirectUris']);
            $this->clientManager->updateClient($client);
            $this->autodiscover->updateTargetCloudClientData($oauthParams['client_id'], $client);
            $oauthParams['client_id'] = $client->getPublicId();
            $redirectUrl = (new TargetSuplaCloud($this->getParameter('supla_url'), true))->getOauthAuthUrl($oauthParams);
            return $this->redirect($redirectUrl);
        }
    }
}
