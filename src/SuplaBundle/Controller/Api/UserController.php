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
use DateTimeZone;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use InvalidArgumentException;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Mailer\SuplaMailer;
use SuplaBundle\Model\Audit\AuditAware;
use SuplaBundle\Model\TargetSuplaCloudRequestForwarder;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\AuditEntryRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\PasswordStrengthValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserController extends RestController {
    use Transactional;
    use AuditAware;
    use SuplaServerAware;

    /** @var UserManager */
    private $userManager;
    /** @var AuditEntryRepository */
    private $auditEntryRepository;
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** @var SuplaMailer */
    private $mailer;
    /** @var TargetSuplaCloudRequestForwarder */
    private $suplaCloudRequestForwarder;
    /** @var int */
    private $clientsRegistrationEnableTime;
    /** @var int */
    private $ioDevicesRegistrationEnableTime;
    /** @var bool */
    private $requireRegulationsAcceptance;
    /** @var bool */
    private $recaptchaEnabled;
    /** @var string */
    private $recaptchaSecret;
    /** @var array */
    private $availableLanguages;
    /** * @var bool */
    private $accountsRegistrationEnabled;
    /** @var bool */
    private $mqttBrokerEnabled;
    /** @var bool */
    private $mqttAuthEnabled;
    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    public function __construct(
        UserManager $userManager,
        AuditEntryRepository $auditEntryRepository,
        SuplaAutodiscover $autodiscover,
        SuplaMailer $mailer,
        TargetSuplaCloudRequestForwarder $suplaCloudRequestForwarder,
        EncoderFactoryInterface $encoderFactory,
        int $clientsRegistrationEnableTime,
        int $ioDevicesRegistrationEnableTime,
        bool $requireRegulationsAcceptance,
        bool $recaptchaEnabled,
        ?string $recaptchaSecret,
        array $availableLanguages,
        bool $accountsRegistrationEnabled,
        bool $mqttBrokerEnabled,
        bool $mqttAuthEnabled
    ) {
        $this->userManager = $userManager;
        $this->auditEntryRepository = $auditEntryRepository;
        $this->autodiscover = $autodiscover;
        $this->mailer = $mailer;
        $this->suplaCloudRequestForwarder = $suplaCloudRequestForwarder;
        $this->clientsRegistrationEnableTime = $clientsRegistrationEnableTime;
        $this->ioDevicesRegistrationEnableTime = $ioDevicesRegistrationEnableTime;
        $this->requireRegulationsAcceptance = $requireRegulationsAcceptance;
        $this->recaptchaEnabled = $recaptchaEnabled;
        $this->recaptchaSecret = $recaptchaSecret;
        $this->availableLanguages = $availableLanguages;
        $this->accountsRegistrationEnabled = $accountsRegistrationEnabled;
        $this->mqttBrokerEnabled = $mqttBrokerEnabled;
        $this->mqttAuthEnabled = $mqttAuthEnabled;
        $this->encoderFactory = $encoderFactory;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        return ['longUniqueId', 'limits', 'relationsCount',
            'relationsCount' => 'user.relationsCount',
        ];
    }

    /**
     * @Rest\Patch("/user-info")
     * @Security("is_granted('isRequestFromBroker', request)")
     */
    public function getUserAction(Request $request) {
        $username = $request->get('username');
        $user = $this->userManager->userByEmail($username);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        return $this->serializedView($user, $request);
    }

    /** @Security("has_role('ROLE_ACCOUNT_R')") */
    public function currentUserAction(Request $request) {
        return $this->serializedView($this->getUser(), $request);
    }

    /**
     * @Security("has_role('ROLE_ACCOUNT_RW')")
     * @UnavailableInMaintenance
     */
    public function patchUsersCurrentAction(Request $request) {
        $data = $request->request->all();
        $user = $this->getUser();
        if ($data['action'] == 'delete') {
            $this->assertNotApiUser();
            $password = $data['password'] ?? '';
            Assertion::true($this->userManager->isPasswordValid($user, $password), 'Incorrect password'); // i18n
            $this->userManager->accountDeleteRequest($user);
            $this->mailer->sendDeleteAccountConfirmationEmailMessage($user);
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }
        $headers = [];
        $user = $this->transactional(function (EntityManagerInterface $em) use ($user, $data, &$headers) {
            if ($data['action'] == 'change:clientsRegistrationEnabled') {
                $enable = $data['enable'] ?? false;
                if ($enable) {
                    $user->enableClientsRegistration($this->clientsRegistrationEnableTime);
                } else {
                    $user->disableClientsRegistration();
                }
            } elseif ($data['action'] == 'change:ioDevicesRegistrationEnabled') {
                $enable = $data['enable'] ?? false;
                if ($enable) {
                    $user->enableIoDevicesRegistration($this->ioDevicesRegistrationEnableTime);
                } else {
                    $user->disableIoDevicesRegistration();
                }
            } elseif ($data['action'] == 'change:userTimezone') {
                try {
                    $timezone = new DateTimeZone($data['timezone']);
                    $this->userManager->updateTimeZone($this->getUser(), $timezone);
                } catch (Exception $e) {
                    throw new ApiException('Bad timezone: ' . $data['timezone'], 400, $e);
                }
            } elseif ($data['action'] == 'change:userLocale') {
                Assertion::inArray($data['locale'], $this->availableLanguages, 'Language is not available');
                $user->setLocale($data['locale']);
            } elseif ($data['action'] == 'change:password') {
                $this->assertNotApiUser();
                $newPassword = $data['newPassword'] ?? '';
                $oldPassword = $data['oldPassword'] ?? '';
                Assertion::true($this->userManager->isPasswordValid($user, $oldPassword), 'Current password is incorrect'); // i18n
                PasswordStrengthValidator::create()
                    ->minLength(8)
                    ->maxLength(32)
                    ->validate($newPassword);
                $this->userManager->setPassword($newPassword, $user);
            } elseif ($data['action'] == 'agree:rules') {
                $this->assertNotApiUser();
                $user->agreeOnRules();
            } elseif ($data['action'] == 'agree:cookies') {
                $this->assertNotApiUser();
                $user->agreeOnCookies();
            } elseif ($data['action'] == 'change:mqttBrokerEnabled') {
                $this->assertNotApiUser();
                Assertion::true($this->mqttBrokerEnabled, 'MQTT Broker is disabled.'); // i18n
                $enabled = boolval($data['enabled'] ?? false);
                $recentChange = $this->audit->recentEntry(AuditedEvent::MQTT_ENABLED_DISABLED());
                $tooQuicklyMsg = 'You are changing the settings too quickly. You have to wait a while before making this change.'; // i18n
                Assertion::null($recentChange, $tooQuicklyMsg);
                $this->audit->newEntry(AuditedEvent::MQTT_ENABLED_DISABLED())
                    ->setUser($user)
                    ->setIntParam($enabled ? 1 : 0)
                    ->buildAndSave();
                $user->setMqttBrokerEnabled($enabled);
                if ($enabled && $this->mqttAuthEnabled && !$user->hasMqttBrokerAuthPassword()) {
                    $data['action'] = 'change:mqttBrokerPassword';
                }
            }
            if ($data['action'] == 'change:mqttBrokerPassword') {
                $this->assertNotApiUser();
                Assertion::true($this->mqttBrokerEnabled, 'MQTT Broker is disabled.'); // i18n
                Assertion::true($user->isMqttBrokerEnabled(), 'You must enable MQTT Broker first.'); // i18n
                list($rawPassword, $encodedPassword) = MqttSettingsController::generateMqttBrokerPassword();
                $user->setMqttBrokerAuthPassword($encodedPassword);
                $headers['SUPLA-MQTT-Password'] = $rawPassword;
            }
            $em->persist($user);
            return $user;
        });
        if (in_array($data['action'], ['change:mqttBrokerEnabled', 'change:mqttBrokerPassword'])) {
            $this->suplaServer->mqttSettingsChanged();
        }
        return $this->view($user, Response::HTTP_OK, $headers);
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
     * @UnavailableInMaintenance
     */
    public function routeAccountCreateAction(Request $request) {
        $server = $this->autodiscover->getRegisterServerForUser($request);
        if ($server->isLocal()) {
            return $this->accountCreateAction($request);
        } else {
            [$response, $status] = $this->suplaCloudRequestForwarder->registerUser($server, $request);
            return $this->view($response, $status);
        }
    }

    /**
     * @Rest\Post("/register")
     * @UnavailableInMaintenance
     */
    public function accountCreateAction(Request $request) {
        if (!$this->accountsRegistrationEnabled) {
            return $this->view(
                ['status' => Response::HTTP_LOCKED, 'message' => 'Account registration is diabled'],
                Response::HTTP_LOCKED
            );
        }

        if ($this->recaptchaEnabled) {
            $gRecaptchaResponse = $request->get('captcha');
            $recaptcha = new ReCaptcha($this->recaptchaSecret);
            $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);
            Assertion::true($resp->isSuccess(), 'Captcha token is not valid.');
        }

        $username = $request->get('email');
        Assertion::email($username, 'Please fill a valid email address'); // i18n

        $remoteServer = '';
        $exists = $this->autodiscover->userExists($username);
        if ($exists) {
            $targetCloud = $this->autodiscover->getAuthServerForUser($username);
            if ($targetCloud->isLocal()) {
                $enabled = $this->userManager->userByEmail($username)->isEnabled();
            } else {
                [$response,] = $this->suplaCloudRequestForwarder->getUserInfo($targetCloud, $username);
                $enabled = $response ? ($response['enabled'] ?? false) : false;
            }
            return $this->view(
                ['status' => Response::HTTP_CONFLICT, 'message' => 'Email already exists', 'accountEnabled' => $enabled],
                Response::HTTP_CONFLICT
            );
        }

        if ($exists === null) {
            $this->mailer->sendServiceUnavailableMessage('createAction - remote server: ' . $remoteServer);
            return $this->view([
                'status' => Response::HTTP_SERVICE_UNAVAILABLE,
                'message' => 'Service temporarily unavailable',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $user = new User();
        $user->setEmail($username);

        $data = $request->request->all();
        Assert::that($data)
            ->notEmptyKey('password')
            ->notEmptyKey('timezone');

        $user->setTimezone($data['timezone']);

        $newPassword = $data['password'];
        PasswordStrengthValidator::create()
            ->minLength(8)
            ->maxLength(32)
            ->validate($newPassword);
        $user->setPlainPassword($newPassword);

        $locale = $data['locale'] ?? 'en';
        Assertion::inArray($locale, $this->availableLanguages, 'Language is not available'); // i18n
        $user->setLocale($locale);

        if ($this->requireRegulationsAcceptance) {
            Assert::that($data)->notEmptyKey('regulationsAgreed');
            Assertion::true(
                $data['regulationsAgreed'],
                'You must agree to the Terms and Conditions.' // i18n
            );
            $user->agreeOnRules();
        }

        if ($this->autodiscover->enabled()) {
            $createdInAd = $this->autodiscover->registerUser($user);
            if (!$createdInAd) {
                $message = "Error when communicating the server. Try again in a while."; // i18n
                throw new ServiceUnavailableHttpException(10, $message);
            }
        }
        $this->userManager->create($user);

        $sent = $this->userManager->sendConfirmationEmailMessage($user);

        $view = $this->view($user, Response::HTTP_CREATED);
        $view->setHeader('SUPLA-Email-Sent', $sent ? 'true' : 'false');
        return $view;
    }

    /**
     * @Rest\Post("/register-resend", name="resend_activation_email_post")
     * @Rest\Patch("/register-resend", name="resend_activation_email_patch")
     * @UnavailableInMaintenance
     */
    public function resendActivationEmailAction(Request $request) {
        $data = $request->request->all();
        $username = $data['email'] ?? '';
        if (preg_match('/@/', $username)) {
            if ($request->getMethod() == Request::METHOD_PATCH) {
                $server = $this->autodiscover->getAuthServerForUser($username);
                if (!$server->isLocal()) {
                    [$content, $status] = $this->suplaCloudRequestForwarder->resendActivationEmail($server, $username);
                    return new JsonResponse($content, $status);
                }
            }
            $user = $this->userManager->userByEmail($username);
            if ($user) {
                Assertion::false($user->isEnabled(), 'User is already confirmed. Try to log in.'); // i18n
                try {
                    $sent = $this->userManager->sendConfirmationEmailMessage($user);
                    if (!$sent) {
                        throw new ServiceUnavailableHttpException(10, 'Cannot send an activation e-mail. Try again later.'); // i18n
                    }
                } catch (InvalidArgumentException $e) {
                    throw new ConflictHttpException($e->getMessage(), $e);
                }
            }
        }
        return new Response(null, Response::HTTP_ACCEPTED);
    }

    /**
     * @Rest\Patch("/confirm/{token}")
     * @UnavailableInMaintenance
     */
    public function confirmEmailAction(string $token) {
        $user = $this->userManager->confirm($token);
        Assertion::notNull($user, 'Token does not exist');
        $this->mailer->sendUserConfirmationSuccessEmailMessage($user);
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Patch("/confirm-deletion/{token}")
     * @UnavailableInMaintenance
     */
    public function confirmDeletingAccountAction(string $token) {
        $this->userManager->deleteAccountByToken($token);
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Patch("/forgotten-password", name="forgot_passwd_post")
     * @Rest\Post("/forgotten-password", name="forgot_passwd_patch")
     * @Rest\Head("/forgotten-password/{token}", name="forgot_passwd_head")
     * @Rest\Put("/forgotten-password/{token}", name="forgot_passwd_put")
     * @UnavailableInMaintenance
     */
    public function forgotPasswordAction(Request $request, string $token = null) {
        $data = json_decode($request->getContent(), true);
        $username = $data['email'] ?? '';
        if (preg_match('/@/', $username) || $token) {
            if ($request->getMethod() == Request::METHOD_PATCH) {
                $server = $this->autodiscover->getAuthServerForUser($username);
                [, $status] = $this->suplaCloudRequestForwarder->resetPasswordToken($server, $username);
                Assertion::eq($status, Response::HTTP_OK, 'Could not reset the password.'); // i18n
            } elseif ($request->getMethod() == Request::METHOD_POST) {
                $user = $this->userManager->userByEmail($username);
                if ($user && $this->userManager->paswordRequest($user) === true) {
                    $this->mailer->sendResetPasswordEmailMessage($user);
                }
            } else {
                /** @var User $user */
                $user = $this->userManager->userByPasswordToken($token);
                Assertion::notNull($user, 'Token does not exist'); // i18n
                $password = $data['password'] ?? '';
                if ($password) {
                    Assertion::minLength($password, 8, 'The password should be 8 or more characters.'); // i18n
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
