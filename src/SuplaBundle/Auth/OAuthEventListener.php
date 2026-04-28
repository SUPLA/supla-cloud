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

namespace SuplaBundle\Auth;

use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\ApiClientAuthorizationRepository;
use SuplaBundle\Repository\UserRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @see https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/blob/master/Resources/doc/the_oauth_event_class.md
 */
class OAuthEventListener implements EventSubscriberInterface {
    use Transactional;
    use SuplaServerAware;

    public function __construct(
        private readonly ApiClientAuthorizationRepository $authorizationRepository,
        private readonly UserRepository $userRepository,
        private readonly RequestStack $requestStack,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getSubscribedEvents(): array {
        return [
            OAuthEvent::PRE_AUTHORIZATION_PROCESS => 'onPreAuthorization',
            OAuthEvent::POST_AUTHORIZATION_PROCESS => 'onPostAuthorization',
        ];
    }

    public function onPreAuthorization(OAuthEvent $event): void {
        if ($user = $this->getUser($event)) {
            $scope = $this->requestStack->getCurrentRequest()->get('scope', null);
            $authorization = $this->authorizationRepository->findOneByUserAndApiClient($user, $event->getClient());
            if ($scope && $authorization && $authorization->isAuthorized($scope)) {
                $event->setAuthorizedClient(true);
                $this->invalidateSession();
            }
        }
    }

    public function onPostAuthorization(OAuthEvent $event): void {
        if ($event->isAuthorizedClient()) {
            if (null !== $client = $event->getClient()) {
                $user = $this->getUser($event);
                $request = $this->requestStack->getCurrentRequest();
                $formData = $request->get('fos_oauth_server_authorize_form', []);
                $scope = $formData['scope'] ?? null;
                if ($scope) {
                    $authorization = $this->transactional(function (EntityManagerInterface $entityManager) use ($client, $scope, $user) {
                        $authorization = $this->authorizationRepository->findOneByUserAndApiClient($user, $client);
                        if ($authorization) {
                            $authorization->authorizeNewScope($scope);
                            $entityManager->persist($authorization);
                        } else {
                            $authorization = $user->addApiClientAuthorization($client, $scope);
                            $entityManager->persist($user);
                        }
                        return $authorization;
                    });
                    $this->onAuthorizationCreated($authorization);
                }
            }
        }
        $this->invalidateSession();
    }

    private function onAuthorizationCreated(ApiClientAuthorization $authorization): void {
        $scope = new OAuthScope($authorization->getScope());
        $user = $authorization->getUser();
        if ($scope->hasScope('mqtt_broker') && !$user->isMqttBrokerEnabled()) {
            $this->transactional(function (EntityManagerInterface $entityManager) use ($user) {
                $user->setMqttBrokerEnabled(true);
                $entityManager->persist($user);
            });
            $this->suplaServer->mqttSettingsChanged();
        }
    }

    private function invalidateSession(): void {
        $this->tokenStorage->setToken(null);
        $request = $this->requestStack->getCurrentRequest();
        if ($request && ($session = $request->getSession())) {
            @$session->invalidate();
        }
    }

    private function getUser(OAuthEvent $event): ?User {
        return $this->userRepository->findOneByEmail($event->getUser()->getUsername());
    }
}
