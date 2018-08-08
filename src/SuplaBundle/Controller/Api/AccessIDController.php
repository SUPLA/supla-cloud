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

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Model\ApiVersions;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Model\AccessIdManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessIDController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var AccessIdManager */
    private $accessIdManager;

    public function __construct(AccessIdManager $accessIdManager) {
        $this->accessIdManager = $accessIdManager;
    }

    protected function getAccessIDS() {
        $result = [];
        $user = $this->getUser();

        if ($user !== null) {
            foreach ($user->getAccessIDS() as $aid) {
                $locations = [];

                foreach ($aid->getLocations() as $location) {
                    $locations[] = $location->getId();
                }

                $result[] = [
                    'id' => $aid->getId(),
                    'password' => $aid->getPassword(),
                    'caption' => $aid->getCaption(),
                    'enabled' => $aid->getEnabled() === true ? true : false,
                    'locations' => $locations,
                ];
            }
        }

        return ['accessids' => $result];
    }

    /** @Security("has_role('ROLE_ACCESSIDS_R')") */
    public function getAccessidsAction(Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            return $this->view($this->getUser()->getAccessIDS());
        } else {
            return $this->view($this->getAccessIDS(), Response::HTTP_OK);
        }
    }

    /**
     * @Security("accessId.belongsToUser(user) and has_role('ROLE_ACCESSIDS_R')")
     */
    public function getAccessidAction(Request $request, AccessID $accessId) {
        $view = $this->view($accessId, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['locations', 'clientApps', 'password']);
        return $view;
    }

    /** @Security("has_role('ROLE_ACCESSIDS_RW')") */
    public function postAccessidAction(Request $request) {
        $user = $this->getUser();
        $accessIdCount = $user->getAccessIDS()->count();
        Assertion::lessThan($accessIdCount, $user->getLimitAid(), 'Access identifier limit has been exceeded');
        return $this->transactional(function (EntityManagerInterface $em) use ($request, $user) {
            $aid = $this->accessIdManager->createID($user);
            $em->persist($aid);
            return $this->getAccessidAction($request, $aid);
        });
    }

    /**
     * @Security("accessId.belongsToUser(user) and has_role('ROLE_ACCESSIDS_RW')")
     */
    public function deleteAccessidAction(AccessID $accessId) {
        Assertion::greaterThan($this->getUser()->getAccessIDS()->count(), 1, 'You cannot delete your last access identifier.');
        return $this->transactional(function (EntityManagerInterface $em) use ($accessId) {
            $em->remove($accessId);
            $this->suplaServer->reconnect($this->getUser()->getId());
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    /**
     * @Security("accessId.belongsToUser(user)")
     */
    public function putAccessidAction(Request $request, AccessID $accessId, AccessID $updatedAccessId) {
        $accessId->setCaption($updatedAccessId->getCaption());
        $accessId->setEnabled($updatedAccessId->getEnabled());
        if ($updatedAccessId->getPassword()) {
            $newPassword = $updatedAccessId->getPassword();
            Assertion::minLength($newPassword, 8, 'Access identifier password must be at least 8 characters.');
            Assertion::maxLength($newPassword, 32, 'Access identifier password must be no longer than 32 characters.');
            $accessId->setPassword($newPassword);
        }
        $this->transactional(function (EntityManagerInterface $em) use ($updatedAccessId, $request, $accessId) {
            $accessId->updateLocations($updatedAccessId->getLocations());
            $em->persist($accessId);
            foreach ($accessId->getClientApps() as $clientApp) {
                $clientApp->setAccessId(null);
                $em->persist($clientApp);
            }
            foreach ($updatedAccessId->getClientApps() as $clientApp) {
                $clientApp->setAccessId($accessId);
                $em->persist($clientApp);
            }
        });
        $this->suplaServer->reconnect($this->getCurrentUser()->getId());
        $this->getDoctrine()->getManager()->refresh($accessId);
        return $this->getAccessidAction($request, $accessId);
    }
}
