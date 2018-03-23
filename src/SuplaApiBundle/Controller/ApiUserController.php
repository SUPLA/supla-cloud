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
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
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

class ApiUserController extends FOSRestController {
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
                $newPassword = $data['newPassword'] ?? '';
                $oldPassword = $data['oldPassword'] ?? '';
                Assertion::true($this->userManager->isPasswordValid($user, $oldPassword), 'Current password is incorrect');
                Assertion::minLength($newPassword, 8, 'The password should be 8 or more characters.');
                $this->userManager->setPassword($newPassword, $user);
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
        Assertion::isInstanceOf($user, User::class, 'You cannot fetch API settings via API.');
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
        Assertion::isInstanceOf($user, User::class, 'You cannot fetch API settings via API.');
        $data = $request->request->all();
        $apiManager = $this->get('api_manager');
        $apiUser = $apiManager->getAPIUser($user);
        $action = $data['action'] ?? '';
        if ($action == 'generatePassword') {
            $password = $apiUser->generateNewPassword();
            $apiManager->setPassword($password, $apiUser, true);
            return $this->view(['password' => $password], Response::HTTP_OK);
        } else if ($action == 'toggleEnabled') {
            $enabled = $apiManager->setEnabled(!$apiUser->isEnabled(), $apiUser, true);
            return ['enabled' => $enabled];
        }
        Assertion::true(false);
    }
}
