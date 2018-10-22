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
use SuplaBundle\Entity\User;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\ApiClientAuthorizationRepository;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @see https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/blob/master/Resources/doc/the_oauth_event_class.md
 */
class OAuthEventListener {
    use Transactional;

    /** @var ApiClientAuthorizationRepository */
    private $authorizationRepository;
    /** @var UserRepository */
    private $userRepository;
    /** @var RequestStack */
    private $requestStack;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        ApiClientAuthorizationRepository $authorizationRepository,
        UserRepository $userRepository,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationRepository = $authorizationRepository;
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    public function onPreAuthorizationProcess(OAuthEvent $event) {
        if ($user = $this->getUser($event)) {
            $scope = $this->requestStack->getCurrentRequest()->get('scope', null);
            $authorization = $this->authorizationRepository->findOneByUserAndApiClient($user, $event->getClient());
            if ($scope && $authorization) {
                $event->setAuthorizedClient($authorization->isAuthorized($scope));
                $this->invalidateSession();
            }
        }
    }

    public function onPostAuthorizationProcess(OAuthEvent $event) {
        if ($event->isAuthorizedClient()) {
            if (null !== $client = $event->getClient()) {
                $user = $this->getUser($event);
                $request = $this->requestStack->getCurrentRequest();
                $formData = $request->get('fos_oauth_server_authorize_form', []);
                $scope = $formData['scope'] ?? null;
                if ($scope) {
                    $this->transactional(function (EntityManagerInterface $entityManager) use ($client, $scope, $user) {
                        $authorization = $this->authorizationRepository->findOneByUserAndApiClient($user, $client);
                        if ($authorization) {
                            $authorization->authorizeNewScope($scope);
                            $entityManager->persist($authorization);
                        } else {
                            $user->addApiClientAuthorization($client, $scope);
                            $entityManager->persist($user);
                        }
                    });
                }
            }
        }
        $this->invalidateSession();
    }

    private function invalidateSession() {
        $this->tokenStorage->setToken(null);
        $request = $this->requestStack->getCurrentRequest();
        if ($request && ($session = $request->getSession())) {
            @$session->invalidate();
        }
    }

    /** @return User|null */
    private function getUser(OAuthEvent $event) {
        return $this->userRepository->findOneByEmail($event->getUser()->getUsername());
    }
}
