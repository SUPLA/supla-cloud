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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use ReCaptcha\ReCaptcha;
use SuplaApiBundle\Exception\ApiException;
use SuplaApiBundle\Model\Audit\AuditAware;
use SuplaBundle\Controller\AjaxController;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\EventListener\LocaleListener;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\AuditEntryRepository;
use SuplaBundle\Supla\ServerList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiUserController extends RestController {
    use Transactional;
    use AuditAware;

    private $serverList;
    /** @var UserManager */
    private $userManager;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;

    public function __construct(ServerList $serverList, UserManager $userManager, AuditEntryRepository $auditEntryRepository) {
        $this->serverList = $serverList;
        $this->userManager = $userManager;
        $this->auditEntryRepository = $auditEntryRepository;
    }

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
                $this->assertNotApiUser();
                $newPassword = $data['newPassword'] ?? '';
                $oldPassword = $data['oldPassword'] ?? '';
                Assertion::true($this->userManager->isPasswordValid($user, $oldPassword), 'Current password is incorrect');
                Assertion::minLength($newPassword, 8, 'The password should be 8 or more characters.');
                $this->userManager->setPassword($newPassword, $user);
            } elseif ($data['action'] == 'agree:rules') {
                $this->assertNotApiUser();
                $user->agreeOnRules();
            } elseif ($data['action'] == 'agree:cookies') {
                $this->assertNotApiUser();
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
        $this->assertNotApiUser();
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
        $this->assertNotApiUser();
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
        Assertion::true(false, "Invalid action given: $action");
    }

    public function getUsersCurrentAuditAction(Request $request) {
        $events = $request->get('events', []);
        Assertion::isArray($events);
        $events = array_map(function ($event) {
            return (AuditedEvent::isValidKey($event) ? AuditedEvent::$event() : new AuditedEvent($event))->getValue();
        }, $events);
        $criteria = Criteria::create()->orderBy(['createdAt' => 'DESC']);
        if ($events) {
            $criteria->where(Criteria::expr()->in('event', $events));
        }
        $criteria->setMaxResults(2); // currently used only for displaying last IPs
        $entries = $this->auditEntryRepository->matching($criteria);
        return $this->view($entries, Response::HTTP_OK);
    }

    private function assertNotApiUser() {
        $user = $this->getUser();
        Assertion::isInstanceOf($user, User::class, 'You cannot perform this action as an API user.');
    }

    /**
     * @Rest\Post("/register")
     */
    public function accountCreateAction(Request $request) {
        $regulationsRequired = $this->container->getParameter('supla_require_regulations_acceptance');
        $recaptchaEnabled = $this->container->getParameter('recaptcha_enabled');
        if ($recaptchaEnabled) {
            $recaptchaSecret = $this->container->getParameter('recaptcha_secret');
            $gRecaptchaResponse = $request->get('captcha');
            $recaptcha = new ReCaptcha($recaptchaSecret);
            $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);
            Assertion::true($resp->isSuccess(), 'Captcha token is not valid.');
        }

        $username = $request->get('email');
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
            ->notEmptyKey('password')
            ->notEmptyKey('timezone');
        
        
        if ($regulationsRequired) {
            Assert::that($data)->notEmptyKey('regulationsAgreed');
            Assertion::true($data['regulationsAgreed'], 'You must agree to the Terms and Conditions.');
        }

        $newPassword = $data['password'];
        Assertion::minLength($newPassword, 8, 'The password should be 8 or more characters.');

        $user = new User();
        if ($regulationsRequired) {
            $user->agreeOnRules();
        }
        $user->fill($data);

        $this->userManager->create($user);
        if ($serverList->getAutodiscover()->enabled()) {
            $serverList->getAutodiscover()->registerUser($user);
        }

        // send email
        $mailer = $this->get('supla_mailer');
        $sent = $mailer->sendConfirmationEmailMessage($user);

        $view = $this->view($user, Response::HTTP_CREATED);
        $view->setHeader('SUPLA-Email-Sent', $sent ? 'true' : 'false');
        return $view;
    }

    /**
     * @Rest\Patch("/confirm/{token}")
     */
    public function confirmEmailAction($token) {
        $user = $this->userManager->confirm($token);
        Assertion::notNull($user, 'Token does not exist');
        $mailer = $this->get('supla_mailer');
        $mailer->sendActivationEmailMessage($user);
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Patch("/forgotten-password", name="forgot_passwd_post")
     * @Rest\Post("/forgotten-password", name="forgot_passwd_patch")
     * @Rest\Head("/forgotten-password/{token}", name="forgot_passwd_head")
     * @Rest\Put("/forgotten-password/{token}", name="forgot_passwd_put")
     */
    public function forgotPasswordAction(Request $request, string $token = null) {
        $data = json_decode($request->getContent(), true);
        $username = $data['email'] ?? '';
        if (preg_match('/@/', $username) || $token) {
            if ($request->getMethod() == Request::METHOD_PATCH) {
                $sl = $this->get('server_list');
                $server = $sl->getAuthServerForUser($request, $username);
                if ($server) {
                    $result = AjaxController::remoteRequest($server . $this->generateUrl('_homepage') . 'web-api/forgotten-password', [
                        'email' => $username,
                        'locale' => $request->getLocale(),
                    ]);
                    Assertion::true($result && $result->success, 'Could not reset the password.');
                }
            } elseif ($request->getMethod() == Request::METHOD_POST) {
                if (LocaleListener::localeAllowed($data['locale'] ?? null)) {
                    $request->getSession()->set('_locale', $data['locale']);
                    $request->setLocale($data['locale']);
                }
                $user = $this->userManager->userByEmail($username);
                if ($user && $this->userManager->paswordRequest($user) === true) {
                    $mailer = $this->get('supla_mailer');
                    $mailer->sendResetPasswordEmailMessage($user);
                }
            } else {
                /** @var User $user */
                $user = $this->userManager->userByPasswordToken($token);
                Assertion::notNull($user, 'Token does not exist');
                $password = $data['password'] ?? '';
                if ($password) {
                    Assertion::minLength($password, 8, 'The password should be 8 or more characters.');
                    $user->setToken(null);
                    $user->setPasswordRequestedAt(null);
                    $this->userManager->setPassword($password, $user, true);
                    $this->auditEntry(AuditedEvent::PASSWORD_RESET())
                        ->setTextParam($user->getUsername())
                        ->setUser($user)
                        ->buildAndFlush();
                }
            }
        }
        return $this->view(['success' => true], Response::HTTP_OK);
    }
}
