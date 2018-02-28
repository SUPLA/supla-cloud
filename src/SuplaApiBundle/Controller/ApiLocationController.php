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
use SuplaApiBundle\Model\ApiVersions;
use SuplaBundle\Model\LocationManager;
use SuplaBundle\Model\Transactional;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiLocationController extends RestController {
    use Transactional;

    /** @var LocationManager */
    private $locationManager;

    public function __construct(LocationManager $locationManager) {
        $this->locationManager = $locationManager;
    }

    protected function getLocations() {

        $result = [];
        $parent = $this->getParentUser();

        if ($parent !== null) {
            foreach ($parent->getLocations() as $location) {
                $iodev = [];
                $accessids = [];

                foreach ($location->getIoDevices() as $iodevice) {
                    $iodev[] = $iodevice->getId();
                }

                foreach ($location->getAccessIds() as $accessid) {
                    $accessids[] = $accessid->getId();
                }

                $result[] = [
                    'id' => $location->getId(),
                    'password' => $location->getPassword(),
                    'caption' => $location->getCaption(),
                    'iodevices' => $iodev,
                    'accessids' => $accessids,
                ];
            }
        }

        return ['locations' => $result];
    }

    public function getLocationsAction(Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $locations = $this->getUser()->getLocations();
            $view = $this->view($locations, Response::HTTP_OK);
//            $this->setSerializationGroups($view, $request, ['accessId', 'connected']);
            return $view;
        } else {
            return $this->handleView($this->view($this->getLocations(), Response::HTTP_OK));
        }
    }

    public function postLocationAction() {
        $user = $this->getUser();
        $locationsCount = $user->getLocations()->count();
        Assertion::lessThan($locationsCount, $user->getLimitLoc(), 'You have reached the maximum limit of locations.');
        return $this->transactional(function (EntityManagerInterface $em) use ($user) {
            $location = $this->locationManager->createLocation($user);
            $em->persist($location);
            $view = $this->view($location, Response::HTTP_CREATED);
//            $this->setSerializationGroups($view, $request, ['accessId', 'connected']);
            return $view;
        });
    }
}
