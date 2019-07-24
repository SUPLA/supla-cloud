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

namespace SuplaBundle\Mailer;

use Assert\Assertion;
use SuplaBundle\Entity\User;
use SuplaBundle\Model\LocalSuplaCloud;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SuplaMailer {
    protected $router;
    protected $templating;
    protected $mailerFrom;
    protected $mailer;
    protected $adminEmail;
    protected $defaultLocale;
    /** @var LocalSuplaCloud */
    private $localSuplaCloud;

    public function __construct(
        Router $router,
        TwigEngine $templating,
        \Swift_Mailer $mailer,
        $mailerFrom,
        $adminEmail,
        LocalSuplaCloud $localSuplaCloud,
        $defaultLocale
    ) {
        $this->router = $router;
        $this->templating = $templating;
        $this->mailerFrom = $mailerFrom;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->defaultLocale = $defaultLocale;
    }

    private function extractSubjectAndBody(string $templateName, array $params, string $locale, &$subject = null) {
        $templatePath = "SuplaBundle::Email/$locale/$templateName.twig";
        if (!$this->templating->exists($templatePath)) {
            if ($locale !== 'en') {
                return $this->extractSubjectAndBody($templateName, $params, 'en', $subject);
            } else {
                return '';
            }
        }
        $body = $this->templating->render($templatePath, $params);
        if ($subject !== null) {
            $lines = explode("\n", trim($body));
            $subject = $lines[0];
            $body = implode("\n", array_slice($lines, 1));
        }
        return $body;
    }

    private function sendEmailMessage(string $templateName, $recipient, array $params, string $locale = null): bool {
        if ($recipient instanceof User) {
            $locale = $recipient->getLocale() ?? $this->defaultLocale;
            if (!isset($params['user'])) {
                $params['user'] = $recipient;
            }
            $recipient = $recipient->getEmail();
        }
        if (!$locale) {
            $locale = $this->defaultLocale;
        }
        $bodyHtml = $this->extractSubjectAndBody($templateName . '.html', $params, $locale);
        $subject = '';
        $bodyTxt = $this->extractSubjectAndBody($templateName . '.txt', $params, $locale, $subject);
        Assertion::notBlank($bodyTxt, 'Email "' . $templateName . '" has no TXT template.');
        $message = (new \Swift_Message($subject))
            ->setFrom($this->mailerFrom)
            ->setTo($recipient);
        if ($bodyHtml == '') {
            $message->setBody($bodyTxt, 'text/plain');
        } else {
            $message->setBody($bodyHtml, 'text/html');
            $message->addPart($bodyTxt, 'text/plain');
        }
        $sent = $this->mailer->send($message);
        return $sent > 0;
    }

    public function sendConfirmationEmailMessage(User $user): bool {
        $url = $this->linkWithLang($user, 'confirm/' . $user->getToken());
        return $this->sendEmailMessage('confirm', $user, ['confirmationUrl' => $url]);
    }

    public function sendResetPasswordEmailMessage(User $user): bool {
        $url = $this->linkWithLang($user, 'reset-password/' . $user->getToken());
        return $this->sendEmailMessage('resetpwd', $user, ['confirmationUrl' => $url]);
    }

    public function sendActivationEmailMessage(User $user): bool {
        return $this->sendEmailMessage(
            'activation',
            $this->adminEmail,
            ['user' => $user, 'supla_server' => $this->localSuplaCloud->getHost()],
            'en'
        );
    }

    public function sendDeleteAccountConfirmationEmailMessage(User $user): bool {
        $url = $this->linkWithLang($user, 'confirm-deletion/' . $user->getToken());
        return $this->sendEmailMessage('confirm_deletion', $user, ['confirmationUrl' => $url]);
    }

    public function sendServiceUnavailableMessage($detail): bool {
        return $this->sendEmailMessage(
            'service_unavailable',
            $this->adminEmail,
            [
                'detail' => $detail,
                'supla_server' => $this->localSuplaCloud->getHost(),
            ],
            'en'
        );
    }

    public function sendFailedAuthenticationAttemptWarning(User $user, $ip): bool {
        return $this->sendEmailMessage('failed_auth_attempt', $user, ['ip' => is_numeric($ip) ? long2ip($ip) : '']);
    }

    private function linkWithLang(User $user, string $suffix): string {
        $url = $this->router->generate('_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL) . $suffix;
        $url .= '?lang=' . ($user->getLocale() ?? $this->defaultLocale);
        return $url;
    }
}
