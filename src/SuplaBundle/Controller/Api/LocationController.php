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
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Location;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\LocationManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LocationController extends RestController {
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

    /** @Security("has_role('ROLE_LOCATIONS_R')") */
    public function getLocationsAction(Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $locations = $this->getUser()->getLocations();
            $view = $this->view($locations, Response::HTTP_OK);
            $this->setSerializationGroups($view, $request, ['channels', 'iodevices', 'accessids', 'channelGroups', 'password']);
            return $view;
        } else {
            return $this->handleView($this->view($this->getLocations(), Response::HTTP_OK));
        }
    }

    /** @Security("has_role('ROLE_LOCATIONS_RW')") */
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
     * @Security("location.belongsToUser(user) and has_role('ROLE_LOCATIONS_R')")
     */
    public function getLocationAction(Request $request, Location $location) {
        $view = $this->view($location, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['channels', 'iodevices', 'accessids', 'channelGroups', 'password']);
        return $view;
    }

    /**
     * @Security("location.belongsToUser(user) and has_role('ROLE_LOCATIONS_RW')")
     */
    public function deleteLocationAction(Location $location) {
        $this->ensureNoRelatedEntities(
            $location->getIoDevices(),
            'Remove all the associated devices before you delete this location. Ids: {relatedIds}.'
        );
        $this->ensureNoRelatedEntities(
            $location->getIoDevicesByOriginalLocation(),
            'Remove all the devices using this location as an official before you delete it. Ids: {relatedIds}.'
        );
        $this->ensureNoRelatedEntities(
            $location->getChannels(),
            'Relocate all the associated channels before you delete this location. Ids: {relatedIds}.'
        );
        $this->ensureNoRelatedEntities(
            $location->getChannelGroups(),
            'Relocate all the associated channel groups before you delete this location. Ids: {relatedIds}.'
        );
        Assertion::greaterThan($this->getUser()->getLocations()->count(), 1, 'You cannot delete your last location.');
        return $this->transactional(function (EntityManagerInterface $em) use ($location) {
            $em->remove($location);
            $this->suplaServer->reconnect($this->getUser()->getId());
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }

    private function ensureNoRelatedEntities($entities, string $message) {
        ApiException::throwIf(count($entities), $message, ['relatedIds' => implode(', ', EntityUtils::mapToIds($entities))]);
    }

    /**
     * @Security("location.belongsToUser(user) and has_role('ROLE_LOCATIONS_RW')")
     */
    public function putLocationAction(Request $request, Location $location, Location $updatedLocation) {
        $location->setCaption($updatedLocation->getCaption());
        $location->setEnabled($updatedLocation->getEnabled());
        if ($updatedLocation->getPassword()) {
            $newPassword = $updatedLocation->getPassword();
            Assertion::minLength($newPassword, 4, 'Location password must be at least 4 characters.');
            Assertion::maxLength($newPassword, 32, 'Location password must be no longer than 32 characters.');
            $location->setPassword($newPassword);
        }
        $location->getAccessIds()->clear();
        foreach ($updatedLocation->getAccessIds() as $accessId) {
            $location->getAccessIds()->add($accessId);
        }
        return $this->transactional(function (EntityManagerInterface $em) use ($request, $location) {
            $em->persist($location);
            $this->suplaServer->reconnect();
            return $this->getLocationAction($request, $location);
        });
    }
}
