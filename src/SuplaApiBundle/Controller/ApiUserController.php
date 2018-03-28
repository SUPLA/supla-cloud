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
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use ReCaptcha\ReCaptcha;
use SuplaApiBundle\Exception\ApiException;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Supla\ServerList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiUserController extends RestController {
    use Transactional;

    private $serverList;
    /** @var UserManager */
    private $userManager;

    public function __construct(ServerList $serverList, UserManager $userManager) {
        $this->serverList = $serverList;
        $this->userManager = $userManager;
    }

    /**
     * @api {get} /users/current Current user
     * @apiDescription Get the currently authenticated user
     * @apiGroup Users
     * @apiVersion 2.2.0
     * @apiSuccess {Number} id User ID
     * @apiSuccess {String} email User email address
     * @apiSuccess {Boolean} ioDevicesRegistrationEnabled Whether the registration of new IO Devices is enabled or not.
     * @apiSuccess {Boolean} clientsRegistrationEnabled Whether the registration of new clients is enabled or not.
     */
    public function currentUserAction() {
        return $this->view($this->getUser(), Response::HTTP_OK);
    }

    /**
     * @Rest\Get("users/current/schedulable-channels")
     */
    public function getUserSchedulableChannelsAction() {
        /** @var IODeviceManager $ioDeviceManager */
        $ioDeviceManager = $this->get('iodevice_manager');
        $schedulableChannels = $this->get('schedule_manager')->getSchedulableChannels($this->getUser());
        $channelToFunctionsMap = [];
        foreach ($schedulableChannels as $channel) {
            $channelToFunctionsMap[$channel->getId()] = (new ChannelFunction($channel->getFunction()->getId()))->getPossibleActions();
        }
        return $this->view([
            'userChannels' => array_map(function (IODeviceChannel $channel) use ($ioDeviceManager) {
                return [
                    'id' => $channel->getId(),
                    'function' => $channel->getFunction()->getId(),
                    'functionName' => $ioDeviceManager->channelFunctionToString($channel->getFunction()),
                    'type' => $channel->getType(),
                    'caption' => $channel->getCaption(),
                    'device' => [
                        'id' => $channel->getIoDevice()->getId(),
                        'name' => $channel->getIoDevice()->getName(),
                        'location' => [
                            'id' => $channel->getIoDevice()->getLocation()->getId(),
                            'caption' => $channel->getIoDevice()->getLocation()->getCaption(),
                        ],
                    ],
                ];
            }, $schedulableChannels),
            'actionCaptions' => ChannelFunctionAction::captions(),
            'channelFunctionMap' => $channelToFunctionsMap,
        ]);
    }

    public function patchUsersCurrentAction(Request $request) {
        $data = $request->request->all();
        $user = $this->transactional(function (EntityManagerInterface $em) use ($data) {
            $user = $this->getUser();
            if ($data['action'] == 'change:clientsRegistrationEnabled') {
                $enable = $data['enable'] ?? false;
                if ($enable) {
                    $enableForTime = $this->container->getParameter('supla.clients_registration.registration_active_time.manual');
                    $user->enableClientsRegistration($enableForTime);
                } else {
                    $user->disableClientsRegistration();
                }
            } elseif ($data['action'] == 'change:ioDevicesRegistrationEnabled') {
                $enable = $data['enable'] ?? false;
                if ($enable) {
                    $enableForTime = $this->container->getParameter('supla.io_devices_registration.registration_active_time.manual');
                    $user->enableIoDevicesRegistration($enableForTime);
                } else {
                    $user->disableIoDevicesRegistration();
                }
            } elseif ($data['action'] == 'change:userTimezone') {
                try {
                    $timezone = new \DateTimeZone($data['timezone']);
                    $this->userManager->updateTimeZone($this->getUser(), $timezone);
                } catch (\Exception $e) {
                    throw new ApiException('Bad timezone: ' . $data['timezone'], 400, $e);
                }
            } elseif ($data['action'] == 'change:password') {
                $this->assertNotApliUser();
                $newPassword = $data['newPassword'] ?? '';
                $oldPassword = $data['oldPassword'] ?? '';
                Assertion::true($this->userManager->isPasswordValid($user, $oldPassword), 'Current password is incorrect');
                Assertion::minLength($newPassword, 8, 'The password should be 8 or more characters.');
                $this->userManager->setPassword($newPassword, $user);
            } elseif ($data['action'] == 'agree:rules') {
                $this->assertNotApliUser();
                $user->agreeOnRules();
            } elseif ($data['action'] == 'agree:cookies') {
                $this->assertNotApliUser();
                $user->agreeOnCookies();
            }
            $em->persist($user);
            return $user;
        });
        return $this->view($user, Response::HTTP_OK);
    }

    /**
     * @Rest\Get("users/current/api-settings")
     */
    public function getUserApiSettingsAction() {
        $user = $this->getUser();
        $this->assertNotApliUser();
        $apiManager = $this->get('api_manager');
        $client = $apiManager->getClient($user);
        $apiUser = $apiManager->getAPIUser($user);
        $url = $this->generateUrl('fos_oauth_server_token', [], UrlGeneratorInterface::ABSOLUTE_URL);
        return $this->view([
            'user' => $apiUser,
            'client' => $client,
            'tokenUrl' => $url,
            'server' => $this->container->getParameter('supla_protocol') . '://' . $this->container->getParameter('supla_server'),
        ], Response::HTTP_OK);
    }

    /**
     * @Rest\Patch("users/current/api-settings")
     */
    public function patchUserApiSettingsAction(Request $request) {
        $user = $this->getUser();
        $this->assertNotApliUser();
        $data = $request->request->all();
        $apiManager = $this->get('api_manager');
        $apiUser = $apiManager->getAPIUser($user);
        $action = $data['action'] ?? '';
        if ($action == 'generatePassword') {
            $password = $apiUser->generateNewPassword();
            $apiManager->setPassword($password, $apiUser, true);
            return $this->view(['password' => $password], Response::HTTP_OK);
        } elseif ($action == 'toggleEnabled') {
            $enabled = $apiManager->setEnabled(!$apiUser->isEnabled(), $apiUser, true);
            return ['enabled' => $enabled];
        }
        Assertion::true(false);
    }

    private function assertNotApliUser() {
        $user = $this->getUser();
        Assertion::isInstanceOf($user, User::class, 'You cannot perform this action as an API user.');
    }

    /**
     * @Rest\Post("/register")
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
            ->notEmptyKey('regulationsAgreed')
            ->notEmptyKey('email')
            ->notEmptyKey('username')
            ->notEmptyKey('plainPassword')
            ->notEmptyKey('timezone');

        Assertion::true($data['regulationsAgreed'], 'You need to agree on regulations.');

        $newPassword = $data['plainPassword'];
        Assertion::minLength($newPassword, 8, 'The password should be 8 or more characters.');

        $user = new User();
        $user->agreeOnRules();
        $user->fill($data);

        $this->userManager->create($user);

        // send email
        $mailer = $this->get('supla_mailer');
        $mailer->sendConfirmationEmailMessage($user);

        return $this->view($user, Response::HTTP_CREATED);
    }
}
