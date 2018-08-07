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

namespace SuplaApiBundle\Auth;

use Doctrine\ORM\EntityManagerInterface;
use FOS\OAuthServerBundle\Event\OAuthEvent;
use SuplaBundle\Entity\User;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\ApiClientAuthorizationRepository;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;

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
    /** @var SuplaOAuth2 */
    private $oAuth2;

    public function __construct(
        ApiClientAuthorizationRepository $authorizationRepository,
        UserRepository $userRepository,
        RequestStack $requestStack,
        SuplaOAuth2 $oAuth2
    ) {
        $this->authorizationRepository = $authorizationRepository;
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->oAuth2 = $oAuth2;
    }

    public function onPreAuthorizationProcess(OAuthEvent $event) {
        if ($user = $this->getUser($event)) {
            $scope = $this->requestStack->getCurrentRequest()->get('scope', null);
            $authorization = $this->authorizationRepository->findOneByUserAndApiClient($user, $event->getClient());
            if ($scope && $authorization) {
                $event->setAuthorizedClient($this->oAuth2->isAuthorized($authorization, $scope));
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
                    $user->addApiClientAuthorization($client, $scope);
                    $this->transactional(function (EntityManagerInterface $entityManager) use ($user) {
                        $entityManager->persist($user);
                    });
                }
            }
        }
    }

    /** @return User|null */
    private function getUser(OAuthEvent $event) {
        return $this->userRepository->findOneByEmail($event->getUser()->getUsername());
    }
}
