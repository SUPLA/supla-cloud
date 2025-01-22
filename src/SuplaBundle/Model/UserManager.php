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

namespace SuplaBundle\Model;

use Assert\Assertion;
use DateInterval;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Mailer\SuplaMailer;
use SuplaBundle\Message\Emails\ConfirmUserEmailNotification;
use SuplaBundle\Message\Emails\DeleteUserConfirmationEmailNotification;
use SuplaBundle\Message\Emails\ResetPasswordEmailNotification;
use SuplaBundle\Model\Audit\Audit;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Repository\UserRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager {
    use Transactional;
    use SuplaServerAware;

    protected $encoder_factory;
    /** @var UserRepository */
    protected $rep;
    protected $loc_man;
    protected $aid_man;
    /** @var ScheduleManager */
    private $scheduleManager;

    private $defaultClientsRegistrationTime;
    private $defaultIoDevicesRegistrationTime;
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** @var Audit */
    private $audit;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var LoggerInterface */
    private $logger;
    /**
     * @var SuplaMailer
     */
    private $mailer;
    /**
     * @var LocalSuplaCloud
     */
    private $localSuplaCloud;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        UserRepository $userRepository,
        EncoderFactoryInterface $encoder_factory,
        AccessIdManager $accessid_manager,
        LocationManager $location_manager,
        ScheduleManager $scheduleManager,
        TimeProvider $timeProvider,
        LoggerInterface $logger,
        SuplaMailer $mailer,
        LocalSuplaCloud $localSuplaCloud,
        MessageBusInterface $messageBus,
        int $defaultClientsRegistrationTime,
        int $defaultIoDevicesRegistrationTime
    ) {
        $this->encoder_factory = $encoder_factory;
        $this->rep = $userRepository;
        $this->loc_man = $location_manager;
        $this->aid_man = $accessid_manager;
        $this->scheduleManager = $scheduleManager;
        $this->defaultClientsRegistrationTime = $defaultClientsRegistrationTime;
        $this->defaultIoDevicesRegistrationTime = $defaultIoDevicesRegistrationTime;
        $this->timeProvider = $timeProvider;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->messageBus = $messageBus;
    }

    /** @required */
    public function setAutodiscover(SuplaAutodiscover $autodiscover) {
        $this->autodiscover = $autodiscover;
    }

    /** @required */
    public function setAudit(Audit $audit) {
        $this->audit = $audit;
    }

    public function create(User $user) {
        Assertion::notEmpty($user->getPlainPassword(), 'User does not have a password.');
        $this->setPassword($user->getPlainPassword(), $user);
        $this->genToken($user);
        $this->transactional(function (EntityManagerInterface $em) use ($user) {
            $em->persist($user);
        });
    }

    public function sendConfirmationEmailMessage(User $user): void {
        $recentEmail = $this->audit->recentEntry(AuditedEvent::USER_ACTIVATION_EMAIL_SENT(), 'PT5M', $user);
        Assertion::null($recentEmail, 'We have just sent you an activation link. You should be receiving it shortly.'); // i18n
        $this->audit->newEntry(AuditedEvent::USER_ACTIVATION_EMAIL_SENT())
            ->setUser($user)
            ->setTextParam($user->getUsername())
            ->buildAndFlush();
        $this->messageBus->dispatch(new ConfirmUserEmailNotification($user));
    }

    public function setPassword($password, User $user, $flush = false) {
        $user->setPlainPassword($password);
        $encoder = $this->encoder_factory->getEncoder($user);
        $password = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($password);

        if ($flush === true) {
            $this->transactional(function (EntityManagerInterface $em) use ($user) {
                $em->persist($user);
            });
        }
    }

    public function clearSessions(User $user): void {
        $this->entityManager->getRepository(AccessToken::class)->createQueryBuilder('at')
            ->delete()
            ->where('at.user = :user')
            ->andWhere('at.expiresAt IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function isPasswordValid(User $user, string $password): bool {
        $encoder = $this->encoder_factory->getEncoder($user);
        return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
    }

    public function passwordResetRequest(User $user): void {
        if ($user->isEnabled() === true) {
            if ($user->getPasswordRequestedAt()) {
                $diff = abs($this->timeProvider->getDateTime()->getTimestamp() - $user->getPasswordRequestedAt()->getTimestamp());
                if ($diff < 300) {
                    return;
                }
            }
            $this->genToken($user);
            $user->setPasswordRequestedAt($this->timeProvider->getDateTime());

            $this->transactional(function (EntityManagerInterface $em) use ($user) {
                $em->persist($user);
            });

            $this->messageBus->dispatch(new ResetPasswordEmailNotification($user));
        }
    }

    public function accountDeleteRequest(User $user, string $password): void {
        Assertion::true($this->isPasswordValid($user, $password), 'Incorrect password'); // i18n
        if ($user->isEnabled()) {
            if ($user->getAccountRemovalRequestedAt()) {
                $diff = abs($this->timeProvider->getDateTime()->getTimestamp() - $user->getAccountRemovalRequestedAt()->getTimestamp());
                if ($diff < 300) {
                    return;
                }
            }
            $user->setTokenForAccountRemoval($this->genToken($user));
            $this->transactional(function (EntityManagerInterface $em) use ($user) {
                $em->persist($user);
            });
            $this->messageBus->dispatch(new DeleteUserConfirmationEmailNotification($user));
        }
    }

    private function genToken(User $user) {
        $bytes = false;
        if (function_exists('openssl_random_pseudo_bytes')) {
            $crypto_strong = true;
            $bytes = openssl_random_pseudo_bytes(32, $crypto_strong);
        }
        if ($bytes === false) {
            $this->logger->info('OpenSSL did not produce a secure random number.');
            $bytes = hash('sha256', uniqid(mt_rand(), true), true);
        }
        $token = rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
        $user->setToken($token);
        return $token;
    }

    public function confirm($token) {
        $user = $this->userByConfirmationToken($token);

        if ($user !== null) {
            if ($user->getAccessIDS()->isEmpty()) {
                $this->aid_man->createID($user);
            }
            if ($user->getLocations()->isEmpty()) {
                $this->loc_man->createLocation($user);
            }

            $user->setToken(null);
            $user->setEnabled(true);
            $user->enableClientsRegistration($this->defaultClientsRegistrationTime);
            $user->enableIoDevicesRegistration($this->defaultClientsRegistrationTime);

            $this->transactional(function (EntityManagerInterface $em) use ($user) {
                $em->persist($user);
            });
            return $user;
        }

        return null;
    }

    public function userByEmail($email) {
        return $this->rep->findOneByEmail($email);
    }

    public function userByConfirmationToken($token) {
        if ($token === null || strlen($token) < 40) {
            return null;
        }
        return $this->rep->findOneBy([
            'token' => $token,
            'enabled' => 0,
            'passwordRequestedAt' => null,
        ]);
    }

    public function userByPasswordToken($token) {
        if ($token === null || strlen($token) < 40) {
            return null;
        }

        $date = $this->timeProvider->getDateTime();
        $date->setTimeZone(new DateTimeZone('UTC'));
        $date->sub(new DateInterval('PT1H'));

        $qb = $this->rep->createQueryBuilder('u');

        try {
            return $qb->where($qb->expr()->eq('u.token', ':token'))
                ->andWhere("u.token != ''")
                ->andWhere("u.token IS NOT NULL")
                ->andWhere("u.enabled = 1")
                ->andWhere($qb->expr()->gte('u.passwordRequestedAt', ':date'))
                ->setParameter('token', $token)
                ->setParameter('date', $date)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function updateTimeZone(User $user, DateTimeZone $timezone) {
        $currentTimezone = new DateTimeZone($user->getTimezone());
        $user->setTimezone($timezone->getName());
        $this->transactional(function (EntityManagerInterface $em) use ($timezone, $currentTimezone, $user) {
            $em->persist($user);
            $now = $this->timeProvider->getDateTime();
            if ($currentTimezone->getOffset($now) != $timezone->getOffset($now)) {
                foreach ($user->getSchedules() as $schedule) {
                    /** @var Schedule $schedule */
                    if ($schedule->getEnabled()) {
                        $this->scheduleManager->recalculateScheduledExecutions($schedule);
                    }
                }
            }
        });
    }

    public function deleteAccount(User $user) {
        $this->transactional(function (EntityManagerInterface $em) use ($user) {
            $userServerFromAd = $this->autodiscover->getUserServerFromAd($user->getEmail());
            if ($userServerFromAd === $this->localSuplaCloud->getAddress()) {
                $deletedFromAd = $this->autodiscover->deleteUser($user);
                Assertion::true($deletedFromAd, "Could not delete user {$user->getUsername()} in Autodiscover.");
            }
            $remove = function ($key, $entity) use ($em) {
                $em->remove($entity);
                return true;
            };
            $user->getAccessIDS()->forAll($remove);
            $user->getClientApps()->forAll($remove);
            $user->getChannelGroups()->forAll($remove);
            $user->getChannels()->forAll($remove);
            $user->getDirectLinks()->forAll($remove);
            $user->getIODevices()->forAll($remove);
            $user->getLocations()->forAll($remove);
            $user->getSchedules()->forAll($remove);
            $user->getUserIcons()->forAll($remove);
            $user->getScenes()->forAll($remove);
            $em->remove($user);
            $this->audit->newEntry(AuditedEvent::USER_ACCOUNT_DELETED())
                ->setIntParam($user->getId())
                ->setTextParam($user->getUsername())
                ->buildAndSave();
            $this->suplaServer->reconnect($user);
        });
    }

    public function findByDeleteToken(string $token): User {
        $date = $this->timeProvider->getDateTime();
        $date->setTimeZone(new DateTimeZone('UTC'));
        $date->sub(new DateInterval('PT1H'));
        $qb = $this->rep->createQueryBuilder('u');
        try {
            return $qb->where($qb->expr()->eq('u.token', ':token'))
                ->andWhere("u.token != ''")
                ->andWhere("u.token IS NOT NULL")
                ->andWhere("u.enabled = 1")
                ->andWhere($qb->expr()->gte('u.accountRemovalRequestedAt', ':date'))
                ->setParameter('token', $token)
                ->setParameter('date', $date)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('Token does not exist', $e);
        }
    }
}
