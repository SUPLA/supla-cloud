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

use Assert\Assert;
use Assert\Assertion;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use ReCaptcha\ReCaptcha;
use SuplaBundle\Entity\User;
use SuplaBundle\Model\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAccountController extends FOSRestController {

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @param UserManager $userManager
     */
    public function __construct(
        UserManager $userManager
    ) {
        $this->userManager = $userManager;
    }

    /**
     * @Rest\Post("/account-create")
     */
    public function accountCreateAction(Request $request) {

        $recaptchaEnabled = $this->container->getParameter('recaptcha_enabled');
        if ($recaptchaEnabled) {
            $recaptchaSecret = $this->container->getParameter('recaptcha_secret');
            $gRecaptchaResponse = $request->get('captcha');

            $recaptcha = new ReCaptcha($recaptchaSecret);
            $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);
            if (!$resp->isSuccess()) {
                $errors = $resp->getErrorCodes();

                return $this->view([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Captcha token is not valid.',
                    'errors' => $errors,
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $username = $request->get('username');
        Assertion::email($username, 'Please fill a valid email address');

        $serverList = $this->get('server_list');
        $remoteServer = '';
        $exists = $serverList->userExists($username, $remoteServer);
        Assertion::false($exists, 'Email already exists');

        if ($exists === null) {
            $mailer = $this->get('supla_mailer');
            $mailer->sendServiceUnavailableMessage('createAction - remote server: ' . $remoteServer);

            return $this->view([
                'status' => Response::HTTP_SERVICE_UNAVAILABLE,
                'message' => 'Service temporarily unavailable',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $data = $request->request->all();
        Assert::that($data)
            ->notEmptyKey('email')
            ->notEmptyKey('username')
            ->notEmptyKey('plainPassword')
            ->notEmptyKey('timezone');

        $newPassword = $data['plainPassword']['password'];
        Assertion::minLength($newPassword, 8, 'The password should be 8 or more characters.');

        $confirmPassword = $data['plainPassword']['confirm'];
        Assertion::true($newPassword == $confirmPassword, 'The password and its confirm are not the same.');

        $user = new User;
        $user->fill($data);

        $this->userManager->create($user);

        // send email
        $mailer = $this->get('supla_mailer');
        $mailer->sendConfirmationEmailMessage($user);

        return $this->view([
            'username' => $user->getEmail(),
        ], Response::HTTP_CREATED);
    }
}
