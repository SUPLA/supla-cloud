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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Model\ApiVersions;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Model\AccessIdManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAccessIDController extends RestController {
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

    public function getAccessidsAction(Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            return $this->view($this->getUser()->getAccessIDS());
        } else {
            return $this->view($this->getAccessIDS(), Response::HTTP_OK);
        }
    }

    /**
     * @Security("accessId.belongsToUser(user)")
     */
    public function getAccessidAction(Request $request, AccessID $accessId) {
        $view = $this->view($accessId, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['locations', 'clientApps', 'password']);
        return $view;
    }

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
     * @Security("accessId.belongsToUser(user)")
     */
    public function deleteAccessidAction(AccessID $accessId) {
        return $this->transactional(function (EntityManagerInterface $em) use ($accessId) {
            $em->remove($accessId);
            $this->suplaServer->reconnect($this->getUser()->getId());
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }
}
