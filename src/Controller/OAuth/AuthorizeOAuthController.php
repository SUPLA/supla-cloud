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

namespace App\Controller\OAuth;

use App\EventListener\UnavailableInMaintenance;
use App\Exception\ApiException;
use App\Message\Emails\DeleteTargetCloudConfirmationEmailNotification;
use App\Model\ApiVersions;
use App\Model\LocalSuplaCloud;
use App\Model\TargetSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Supla\SuplaAutodiscover;
use Assert\Assertion;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthorizeOAuthController extends AbstractController {
    /** @var SuplaAutodiscover */
    private $autodiscover;

    /** @var LocalSuplaCloud */
    private $localSuplaCloud;
    /** @var TargetSuplaCloudRequestForwarder */
    private $suplaCloudRequestForwarder;
    /** @var string */
    private $recaptchaSecret;
    /** @var bool */
    private $recaptchaEnabled;

    public function __construct(
        SuplaAutodiscover $autodiscover,
        LocalSuplaCloud $localSuplaCloud,
        TargetSuplaCloudRequestForwarder $suplaCloudRequestForwarder,
        ?string $recaptchaSecret,
        ?string $recaptchaSiteKey,
    ) {
        $this->autodiscover = $autodiscover;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->suplaCloudRequestForwarder = $suplaCloudRequestForwarder;
        $this->recaptchaSecret = $recaptchaSecret;
        $this->recaptchaEnabled = !!$recaptchaSiteKey;
    }

    /**
     * Compatibility endpoints for already-open legacy OAuth pages.
     * @Route("/oauth-authorize", name="_oauth_login", methods={"GET", "POST"})
     * @Route("/oauth/v2/auth_login", name="_oauth_login_check", methods={"GET", "POST"})
     * @Route("/oauth/v2/broker_login", name="_oauth_broker_check", methods={"POST"})
     */
    public function oAuthLoginAction(): Response {
        return new Response('This OAuth login page is no longer available. Restart authorization from the application.', Response::HTTP_GONE, [
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * @Route("/api/register-target-cloud", methods={"POST"})
     * @UnavailableInMaintenance
     */
    public function registerTargetCloudAction(Request $request): Response {
        if ($this->recaptchaEnabled) {
            $gRecaptchaResponse = $request->get('captcha');
            $recaptcha = new ReCaptcha($this->recaptchaSecret);
            $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);
            Assertion::true($resp->isSuccess(), 'Captcha token is not valid.'); // i18n
        }

        $email = $request->get('email');
        Assertion::email($email, 'Please fill a valid email address'); // i18n

        $targetCloud = TargetSuplaCloud::forHost($this->localSuplaCloud->getProtocol(), $request->get('targetCloud'));

        $parts = explode('.', $targetCloud->getHost());
        $parts = array_reverse($parts);
        Assertion::true(
            (!str_contains($parts[0], 'supla') && !str_contains($parts[1], 'supla')) || "$parts[1].$parts[0]" === 'supla.io',
            'You cannot use SUPLA project name in the domain name.' // i18n
        );

        $info = $this->getRegistrationTargetCloudInfo($targetCloud);
        // @codingStandardsIgnoreStart
        Assertion::isArray(
            $info,
            'Your private SUPLA Cloud instance is not available. Make sure your server is online and your https connection works properly.' // i18n
        );
        // @codingStandardsIgnoreEnd
        Assertion::version(
            ApiVersions::V2_3,
            '<=',
            $info['cloudVersion'] ?? '0.0.0',
            'You need to update your SUPLA Cloud to version 2.3.0. or newer.' // i18n
        );
        $token = $this->autodiscover->issueRegistrationTokenForTargetCloud($targetCloud, $email);
        return $this->json(['token' => $token]);
    }

    /**
     * @Route("/api/remove-target-cloud", methods={"POST"})
     * @UnavailableInMaintenance
     */
    public function issueRemovalTokenForTargetCloudAction(Request $request, MessageBusInterface $messageBus): Response {
        if ($this->recaptchaEnabled) {
            $gRecaptchaResponse = $request->get('captcha');
            $recaptcha = new ReCaptcha($this->recaptchaSecret);
            $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);
            Assertion::true($resp->isSuccess(), 'Captcha token is not valid.'); // i18n
        }
        $email = $request->get('email');
        Assertion::email($email, 'Please fill a valid email address'); // i18n
        $targetCloud = TargetSuplaCloud::forHost($this->localSuplaCloud->getProtocol(), $request->get('targetCloud'));
        $tokenData = $this->autodiscover->issueRemovalTokenForTargetCloud($targetCloud, $email);
        $email = new DeleteTargetCloudConfirmationEmailNotification(
            $email,
            $targetCloud->getAddress(),
            $tokenData['targetCloudId'],
            $tokenData['token']
        );
        $messageBus->dispatch($email);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/remove-target-cloud/{targetCloudId}/{token}", methods={"DELETE"})
     * @UnavailableInMaintenance
     */
    public function removeTargetCloudAction(int $targetCloudId, string $token): Response {
        $this->autodiscover->removeTargetCloud($targetCloudId, $token);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /** @return array<string, mixed>|null */
    private function getRegistrationTargetCloudInfo(TargetSuplaCloud $targetCloud): ?array {
        try {
            return $this->suplaCloudRequestForwarder->getRegistrationInfo($targetCloud->getAddress());
        } catch (ApiException | \InvalidArgumentException $e) {
            return null;
        }
    }
}
