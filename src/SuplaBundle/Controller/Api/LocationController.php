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
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\LocationManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\LocationRepository;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\PasswordStrengthValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="Location", type="object", description="Location object.",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="caption", type="string", description="Caption"),
 *   @OA\Property(property="enabled", type="boolean", description="`true` if enabled"),
 *   @OA\Property(property="relationsCount", description="Counts of related entities.", @OA\Property(property="channels", type="integer"), @OA\Property(property="accessIds", type="integer"), @OA\Property(property="channelGroups", type="integer"), @OA\Property(property="ioDevices", type="integer")),
 *   @OA\Property(property="password", type="string", nullable=true, description="Location password (plain text). Returned only if requested by the `include` param"),
 *   @OA\Property(property="accessIds", type="array", nullable=true, description="Array of AIDs, if requested by the `include` param", @OA\Items(ref="#/components/schemas/AccessIdentifier")),
 * )
 */
class LocationController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var LocationManager */
    private $locationManager;
    /** @var LocationRepository */
    private $locationRepository;

    public function __construct(LocationManager $locationManager, LocationRepository $locationRepository) {
        $this->locationManager = $locationManager;
        $this->locationRepository = $locationRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        return [
            'channels', 'iodevices', 'accessids', 'channelGroups', 'password',
            'channels' => 'location.channels',
            'accessids' => 'location.accessids',
            'iodevices' => 'location.iodevices',
            'channelGroups' => 'location.channelGroups',
        ];
    }

    private function getLocations() {
        $result = [];
        $parent = $this->getUser();
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

    /**
     * @OA\Get(
     *     path="/locations", operationId="getLocations", summary="Get locations", tags={"Locations"},
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"channels", "iodevices", "accessids", "channelGroups", "password"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Location"))),
     * )
     * @Rest\Get("/locations")
     * @Security("is_granted('ROLE_LOCATIONS_R')")
     */
    public function getLocationsAction(Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $locations = $this->locationRepository->findAllForUser($this->getUser());
            return $this->serializedView($locations, $request);
        } else {
            return $this->handleView($this->view($this->getLocations(), Response::HTTP_OK));
        }
    }

    /**
     * @OA\Post(
     *     path="/locations", operationId="createLocation", summary="Create a new location", tags={"Locations"},
     *     @OA\Response(response="201", description="Success", @OA\JsonContent(ref="#/components/schemas/Location")),
     * )
     * @Rest\Post("/locations")
     * @Security("is_granted('ROLE_LOCATIONS_RW')")
     * @UnavailableInMaintenance
     */
    public function postLocationAction(Request $request) {
        $user = $this->getUser();
        $locationsCount = $user->getLocations()->count();
        Assertion::lessThan($locationsCount, $user->getLimitLoc(), 'Location limit has been exceeded'); // i18n
        $location = $this->transactional(function (EntityManagerInterface $em) use ($request, $user) {
            $location = $this->locationManager->createLocation($user);
            $em->persist($location);
            return $location;
        });
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            return $this->serializedView($location, $request, ['location.relationsCount'], Response::HTTP_CREATED);
        } else {
            return $this->getLocationAction($request, $location);
        }
    }

    /**
     * @OA\Get(
     *     path="/locations/{id}", operationId="getLocation", summary="Get location by ID", tags={"Locations"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"channels", "iodevices", "accessids", "channelGroups", "password"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Location")),
     * )
     * @Rest\Get("/locations/{location}")
     * @Security("location.belongsToUser(user) and is_granted('ROLE_LOCATIONS_R') and is_granted('accessIdContains', location)")
     */
    public function getLocationAction(Request $request, Location $location) {
        return $this->serializedView($location, $request, ['location.relationsCount']);
    }

    /**
     * @OA\Delete(
     *     path="/locations/{id}", operationId="deleteLocation", summary="Delete the location", tags={"Locations"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="204", description="Success"),
     * )
     * @Rest\Delete("/locations/{location}")
     * @Security("location.belongsToUser(user) and is_granted('ROLE_LOCATIONS_RW') and is_granted('accessIdContains', location)")
     * @UnavailableInMaintenance
     */
    public function deleteLocationAction(Location $location) {
        $this->ensureNoRelatedEntities(
            $location->getIoDevices(),
            'Remove all the associated devices before you delete this location. Ids: {relatedIds}.' // i18n
        );
        $this->ensureNoRelatedEntities(
            $location->getIoDevicesByOriginalLocation(),
            'Remove all the devices using this location as an official before you delete it. Ids: {relatedIds}.' // i18n
        );
        $this->ensureNoRelatedEntities(
            $location->getChannels(),
            'Relocate all the associated channels before you delete this location. Ids: {relatedIds}.' // i18n
        );
        $this->ensureNoRelatedEntities(
            $location->getChannelGroups(),
            'Relocate all the associated channel groups before you delete this location. Ids: {relatedIds}.' // i18n
        );
        $this->ensureNoRelatedEntities(
            $location->getScenes(),
            'Remove all the associated scenes before you delete this location. Ids: {relatedIds}.' // i18n
        );
        Assertion::greaterThan($this->getUser()->getLocations()->count(), 1, 'You cannot delete your last location.'); // i18n
        $result = $this->transactional(function (EntityManagerInterface $em) use ($location) {
            $em->remove($location);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
        $this->suplaServer->reconnect();
        return $result;
    }

    private function ensureNoRelatedEntities($entities, string $message) {
        ApiException::throwIf(count($entities), $message, ['relatedIds' => implode(', ', EntityUtils::mapToIds($entities))]);
    }

    /**
     * @OA\Put(
     *     path="/locations/{id}", operationId="updateLocation", summary="Update the location", tags={"Locations"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="enabled", type="boolean"),
     *          @OA\Property(property="caption", type="string"),
     *          @OA\Property(property="password", type="string", description="Provide new password if you want to change it."),
     *          @OA\Property(property="accessIdsIds", type="array", description="Access Identifiers identifiers to assign to this location.", @OA\Items(type="integer")),
     *       )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Location")),
     * )
     * @Rest\Put("/locations/{location}")
     * @Security("location.belongsToUser(user) and is_granted('ROLE_LOCATIONS_RW') and is_granted('accessIdContains', location)")
     * @UnavailableInMaintenance
     */
    public function putLocationAction(Request $request, Location $location, Location $updatedLocation) {
        $location->setCaption($updatedLocation->getCaption());
        $location->setEnabled($updatedLocation->getEnabled());
        if ($updatedLocation->getPassword()) {
            $newPassword = $updatedLocation->getPassword();
            PasswordStrengthValidator::create()
                ->minLength(4)
                ->maxLength(32)
                ->validate($newPassword);
            $location->setPassword($newPassword);
        }
        $location->getAccessIds()->clear();
        foreach ($updatedLocation->getAccessIds() as $accessId) {
            $location->getAccessIds()->add($accessId);
        }
        $result = $this->transactional(function (EntityManagerInterface $em) use ($request, $location) {
            $em->persist($location);
            return $this->getLocationAction($request, $location->clearRelationsCount());
        });
        $this->suplaServer->reconnect();
        return $result;
    }
}
