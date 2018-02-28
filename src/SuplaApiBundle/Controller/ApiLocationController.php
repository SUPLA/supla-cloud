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
use SuplaBundle\Entity\Location;
use SuplaBundle\Model\LocationManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiLocationController extends RestController {
    use Transactional;
    use SuplaServerAware;

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
            $this->setSerializationGroups($view, $request, ['channels', 'iodevices', 'accessids', 'channelGroups']);
            return $view;
        } else {
            return $this->handleView($this->view($this->getLocations(), Response::HTTP_OK));
        }
    }

    public function postLocationAction(Request $request) {
        $user = $this->getUser();
        $locationsCount = $user->getLocations()->count();
        Assertion::lessThan($locationsCount, $user->getLimitLoc(), 'Location limit has been exceeded');
        return $this->transactional(function (EntityManagerInterface $em) use ($request, $user) {
            $location = $this->locationManager->createLocation($user);
            $em->persist($location);
            return $this->getLocationAction($request, $location);
        });
    }

    /**
     * @Security("location.belongsToUser(user)")
     */
    public function getLocationAction(Request $request, Location $location) {
        $view = $this->view($location, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['channels', 'iodevices', 'accessids', 'channelGroups', 'password']);
        return $view;
    }

    /**
     * @Security("location.belongsToUser(user)")
     */
    public function deleteLocationAction(Location $location) {
        Assertion::count($location->getIoDevices(), 0, 'Remove all the associated devices before you delete this location');
        Assertion::count($location->getChannels(), 0, 'Remove all the associated channels before you delete this location');
        Assertion::count($location->getChannelGroups(), 0, 'Remove all the associated channel groups before you delete this location');
        return $this->transactional(function (EntityManagerInterface $em) use ($location) {
            $em->remove($location);
            $this->suplaServer->reconnect($this->getUser()->getId());
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }
}
