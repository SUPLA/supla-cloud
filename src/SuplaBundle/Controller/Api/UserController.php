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

namespace SuplaBundle\Controller\Api;

use Assert\Assert;
use Assert\Assertion;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\Audit\AuditAware;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\AuditEntryRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends RestController {
    use Transactional;
    use AuditAware;

    /** @var UserManager */
    private $userManager;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(UserManager $userManager, AuditEntryRepository $auditEntryRepository, SuplaAutodiscover $autodiscover) {
        $this->userManager = $userManager;
        $this->auditEntryRepository = $auditEntryRepository;
        $this->autodiscover = $autodiscover;
    }

    /** @Security("has_role('ROLE_ACCOUNT_R')") */
    public function currentUserAction() {
        return $this->view($this->getUser(), Response::HTTP_OK);
    }

    /** @Security("has_role('ROLE_ACCOUNT_RW')") */
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
            } elseif ($data['action'] == 'change:userLocale') {
                Assertion::inArray(
                    $data['locale'],
                    ['en', 'pl', 'cs', 'lt', 'de', 'ru', 'it', 'pt', 'es', 'fr'],
                    'Language is not available'
                );
                $user->setLocale($data['locale']);
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

    /** @Security("has_role('ROLE_ACCOUNT_R')") */
    public function getUsersCurrentAuditAction(Request $request) {
        $events = $request->get('events', []);
        Assertion::isArray($events);
        $events = array_map(function ($event) {
            return (AuditedEvent::isValidKey($event) ? AuditedEvent::$event() : new AuditedEvent($event))->getValue();
        }, $events);
        $criteria = Criteria::create()->orderBy(['createdAt' => 'DESC']);
        $criteria->where(Criteria::expr()->eq('user', $this->getUser()));
        if ($events) {
            $criteria->andWhere(Criteria::expr()->in('event', $events));
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
     * @Rest\Post("/register-account")
     */
    public function routeAccountCreateAction(Request $request) {
        $server = $this->autodiscover->getRegisterServerForUser($request);
        if ($server->isLocal()) {
            return $this->accountCreateAction($request);
        } else {
            list($response, $status) = $server->registerUser($request);
            return $this->view($response, $status);
        }
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

        $remoteServer = '';
        $exists = $this->autodiscover->userExists($username, $remoteServer);
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
        if ($this->autodiscover->enabled()) {
            $this->autodiscover->registerUser($user);
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
                $server = $this->autodiscover->getAuthServerForUser($username);
                list(, $status) = $server->resetPasswordToken($username, $request->getLocale());
                Assertion::eq($status, Response::HTTP_OK, 'Could not reset the password.');
            } elseif ($request->getMethod() == Request::METHOD_POST) {
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
