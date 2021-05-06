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
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\AccessIdManager;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AccessIdRepository;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\PasswordStrengthValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessIDController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var AccessIdManager */
    private $accessIdManager;
    /** @var AccessIdRepository */
    private $accessIdRepository;

    public function __construct(AccessIdManager $accessIdManager, AccessIdRepository $accessIdRepository) {
        $this->accessIdManager = $accessIdManager;
        $this->accessIdRepository = $accessIdRepository;
    }

    /** @inheritDoc */
    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        return [
            'locations', 'clientApps', 'password',
            'locations' => 'accessId.locations',
            'clientApps' => 'accessId.clientApps',
        ];
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
            return $this->serializedView($this->accessIdRepository->findAllForUser($this->getUser()), $request);
        } else {
            return $this->view($this->getAccessIDS(), Response::HTTP_OK);
        }
    }

    /**
     * @Security("accessId.belongsToUser(user) and has_role('ROLE_ACCESSIDS_R')")
     */
    public function getAccessidAction(Request $request, AccessID $accessId) {
        return $this->serializedView($accessId, $request, ['accessId.relationsCount']);
    }

    /**
     * @Security("has_role('ROLE_ACCESSIDS_RW')")
     * @UnavailableInMaintenance
     */
    public function postAccessidAction(Request $request) {
        $user = $this->getUser();
        $accessIdCount = $user->getAccessIDS()->count();
        Assertion::lessThan($accessIdCount, $user->getLimitAid(), 'Access identifier limit has been exceeded'); // i18n
        $aid = $this->transactional(function (EntityManagerInterface $em) use ($request, $user) {
            $aid = $this->accessIdManager->createID($user);
            $em->persist($aid);
            return $aid;
        });
        return $this->getAccessidAction($request, $aid);
    }

    /**
     * @Security("accessId.belongsToUser(user) and has_role('ROLE_ACCESSIDS_RW')")
     * @UnavailableInMaintenance
     */
    public function deleteAccessidAction(AccessID $accessId) {
        Assertion::greaterThan($this->getUser()->getAccessIDS()->count(), 1, 'You cannot delete your last access identifier.'); //i18n
        $result = $this->transactional(function (EntityManagerInterface $em) use ($accessId) {
            $em->remove($accessId);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
        $this->suplaServer->reconnect();
        return $result;
    }

    /**
     * @Security("accessId.belongsToUser(user)")
     * @UnavailableInMaintenance
     */
    public function putAccessidAction(Request $request, AccessID $accessId, AccessID $updatedAccessId) {
        $accessId->setCaption($updatedAccessId->getCaption());
        $accessId->setEnabled($updatedAccessId->getEnabled());
        if ($updatedAccessId->getPassword()) {
            $newPassword = $updatedAccessId->getPassword();
            PasswordStrengthValidator::create()
                ->minLength(8)
                ->maxLength(32)
                ->validate($newPassword);
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
        $this->suplaServer->reconnect();
        return $this->getAccessidAction($request, $accessId->clearRelationsCount());
    }
}
