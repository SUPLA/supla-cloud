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

use Assert\Assertion;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use InvalidArgumentException;
use OAuth2\OAuth2;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Enums\ApiClientType;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Audit\FailedAuthAttemptsUserBlocker;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Model\TargetSuplaCloudRequestForwarder;
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
    /** @var LocalSuplaCloud */
    private $localSuplaCloud;
    /** @var TargetSuplaCloudRequestForwarder */
    private $suplaCloudRequestForwarder;
    /** @var string */
    private $recaptchaSecret;

    public function __construct(
        FailedAuthAttemptsUserBlocker $failedAuthAttemptsUserBlocker,
        SuplaAutodiscover $autodiscover,
        ClientManagerInterface $clientManager,
        LocalSuplaCloud $localSuplaCloud,
        TargetSuplaCloudRequestForwarder $suplaCloudRequestForwarder,
        ?string $recaptchaSecret
    ) {
        $this->failedAuthAttemptsUserBlocker = $failedAuthAttemptsUserBlocker;
        $this->autodiscover = $autodiscover;
        $this->clientManager = $clientManager;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->suplaCloudRequestForwarder = $suplaCloudRequestForwarder;
        $this->recaptchaSecret = $recaptchaSecret;
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
        $client = null;
        if ($this->autodiscover->enabled()) {
            $targetPath = $session->get('_security.oauth_authorize.target_path');
            if (preg_match('#/oauth/v2/auth/?\?(.+)#', $targetPath, $match)) {
                parse_str($match[1], $oauthParams);
            }
            if (isset($oauthParams['client_id'])) {
                if ($this->autodiscover->isBroker() && $request->isMethod(Request::METHOD_POST)) {
                    return $this->handleBrokerAuth($request, $oauthParams);
                } elseif (!($client = $this->clientManager->findClientByPublicId($oauthParams['client_id']))) {
                    // this client does not exist. maybe it has just been created in AD?
                    if ($redirectionToNewClient = $this->fetchClientFromAutodiscover($oauthParams)) {
                        return $redirectionToNewClient;
                    } elseif ($this->autodiscover->isBroker()) {
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
        if ($error && !in_array($error, ['autodiscover_fail', 'private_cloud_fail'])) {
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
            'client' => $client,
        ];
    }

    private function handleBrokerAuth(Request $request, array $oauthParams): Response {
        $session = $request->getSession();
        $username = $request->get('_username');
        $targetCloud = null;
        $targetCloudUrl = $request->get('targetCloud', null);
        $privateCloud = false;
        $error = null;
        if ($targetCloudUrl) {
            $session->set(self::LAST_TARGET_CLOUD_ADDRESS_KEY, $targetCloudUrl);
            try {
                $targetCloud = TargetSuplaCloud::forHost($this->localSuplaCloud->getProtocol(), $targetCloudUrl);
            } catch (InvalidArgumentException $e) {
                $error = 'autodiscover_fail';
            }
            $privateCloud = true;
        } else {
            $session->remove(self::LAST_TARGET_CLOUD_ADDRESS_KEY);
        }
        if (!$targetCloud && !$error) {
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
                if ($privateCloud && !$this->getTargetCloudInfo($targetCloud)) {
                    $error = 'private_cloud_fail';
                } else {
                    return $this->redirect($redirectUrl);
                }
            }
        }
        $session->set(Security::LAST_USERNAME, $username);
        $session->set(Security::AUTHENTICATION_ERROR, $error ?: 'autodiscover_fail');
        return $this->redirectToRoute('_oauth_login');
    }

    private function fetchClientFromAutodiscover(array $oauthParams) {
        $publicId = $this->autodiscover->getPublicIdBasedOnMappedId($oauthParams['client_id']);
        if ($publicId) {
            $clientData = $this->autodiscover->getPublicClient($publicId);
            /** @var ApiClient $client */
            $client = $this->clientManager->createClient();
            $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
            $client->setType(ApiClientType::BROKER());
            $client->setPublicClientId($publicId);
            $client->updateDataFromAutodiscover($clientData);
            $this->clientManager->updateClient($client);
            $this->autodiscover->updateTargetCloudCredentials($oauthParams['client_id'], $client);
            $oauthParams['client_id'] = $client->getPublicId();
            $redirectUrl = $this->localSuplaCloud->getOauthAuthUrl($oauthParams);
            return $this->redirect($redirectUrl);
        }
    }

    /**
     * @Route("/api/register-target-cloud", methods={"POST"})
     * @UnavailableInMaintenance
     */
    public function registerTargetCloudAction(Request $request) {
        $gRecaptchaResponse = $request->get('captcha');
        $recaptcha = new ReCaptcha($this->recaptchaSecret);
        $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);
        Assertion::true($resp->isSuccess(), 'Captcha token is not valid.'); // i18n

        $email = $request->get('email');
        Assertion::email($email, 'Please fill a valid email address'); // i18n

        $targetCloud = TargetSuplaCloud::forHost($this->localSuplaCloud->getProtocol(), $request->get('targetCloud'));
        $info = $this->getTargetCloudInfo($targetCloud);
        Assertion::isArray(
            $info,
            'Your private SUPLA Cloud instance is not available. Make sure your server is online and your https connection works properly.' // i18n
        );
        Assertion::version(
            ApiVersions::V2_3,
            '<=',
            $info['cloudVersion'] ?? '0.0.0',
            'You need to update your SUPLA Cloud to version 2.3.0. or newer.' // i18n
        );
        $token = $this->autodiscover->issueRegistrationTokenForTargetCloud($targetCloud, $email);
        return $this->json(['token' => $token]);
    }

    private function getTargetCloudInfo(TargetSuplaCloud $targetCloud) {
        try {
            return $this->suplaCloudRequestForwarder->getInfo($targetCloud);
        } catch (ApiException $e) {
            return null;
        }
    }
}
