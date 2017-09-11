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

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SuplaMailer {
    protected $router;
    protected $templating;
    protected $mailer_from;
    protected $mailer;
    protected $email_admin;
    protected $supla_server;

    public function __construct(Router $router, TwigEngine $templating, \Swift_Mailer $mailer, $mailer_from, $email_admin, $supla_server) {
        $this->router = $router;
        $this->templating = $templating;
        $this->mailer_from = $mailer_from;
        $this->mailer = $mailer;
        $this->email_admin = $email_admin;
        $this->supla_server = $supla_server;
    }

    private function extractSubjectAndBody($template, $params, &$subject) {
        if ($template == '') {
            return '';
        }

        $template = 'SuplaBundle:Email:' . $template;

        $body = $this->templating->render($template, $params);

        if ($subject !== null) {
            $lines = explode("\n", trim($body));
            $subject = $lines[0];
            $body = implode("\n", array_slice($lines, 1));
        }

        return $body;
    }

    private function sendEmailMessage($txtTmpl, $htmlTmpl, $fromEmail, $toEmail, $params) {
        $subject = null;
        $bodyHtml = $this->extractSubjectAndBody($htmlTmpl, $params, $subject);

        $subject = '';
        $bodyTxt = $this->extractSubjectAndBody($txtTmpl, $params, $subject);

        $message = (new \Swift_Message($subject))
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if ($bodyHtml == '') {
            $message->setBody($bodyTxt, 'text/plain');
        } else {
            $message->setBody($bodyHtml, 'text/html');
            $message->addPart($bodyTxt, 'text/plain');
        }

        $this->mailer->send($message);
    }

    public function sendConfirmationEmailMessage(UserInterface $user) {
        $url = $this->router->generate('_account_confirmemail', ['token' => $user->getToken(), true], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->sendEmailMessage(
            'confirm.txt.twig',
            '', // 'confirm.html.twig'
            $this->mailer_from,
            $user->getEmail(),
            [
                'user' => $user,
                'confirmationUrl' => $url,
            ]
        );
    }

    public function sendResetPasswordEmailMessage(UserInterface $user) {
        $url = $this->router->generate('_account_reset_passwd', ['token' => $user->getToken(), true], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->sendEmailMessage(
            'resetpwd.txt.twig',
            '', // resetpwd.html.twig
            $this->mailer_from,
            $user->getEmail(),
            [
                'user' => $user,
                'confirmationUrl' => $url,
            ]
        );
    }

    public function sendActivationEmailMessage(UserInterface $user) {

        $this->sendEmailMessage(
            'activation.txt.twig',
            '',
            $this->mailer_from,
            $this->email_admin,
            [
                'user' => $user,
                'supla_server' => $this->supla_server,
            ]
        );
    }

    public function sendServiceUnavailableMessage($detail) {
        
        $this->sendEmailMessage(
            'service_unavailable.txt.twig',
            '',
            $this->mailer_from,
            $this->email_admin,
            [
                        'detail' => $detail,
                        'supla_server' => $this->supla_server,
                ]
        );
    }
    
    public function test() {
    }
}
