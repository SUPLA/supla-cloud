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
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\AccessIdManager;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AccessIdRepository;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\PasswordStrengthValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="AccessIdentifier", type="object", description="Access Identifier object (AID).",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="caption", type="string", description="Caption"),
 *   @OA\Property(property="enabled", type="boolean", description="`true` if enabled"),
 *   @OA\Property(property="relationsCount", description="Counts of related entities.", @OA\Property(property="locations", type="integer"), @OA\Property(property="clientApps", type="integer")),
 *   @OA\Property(property="password", type="string", description="Location password (plain text). Returned only if requested by the `include` parameter."),
 *   @OA\Property(property="locations", type="array", description="Array of locations, if requested by the `include` param", @OA\Items(ref="#/components/schemas/Location")),
 * )
 */
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
            'locations', 'clientApps', 'password', 'activeNow',
            'locations' => 'accessId.locations',
            'clientApps' => 'accessId.clientApps',
            'activeNow' => 'accessId.activeNow',
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

    /**
     * @OA\Get(
     *     path="/accessids", operationId="getAccessIdentifiers", summary="Get Access Identifiers", tags={"Access Identifiers"},
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"locations", "clientApps", "password"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/AccessIdentifier"))),
     * )
     * @Security("has_role('ROLE_ACCESSIDS_R')")
     */
    public function getAccessidsAction(Request $request) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $accessIds = $this->accessIdRepository->findAllForUser($this->getUser());
            return $this->serializedView($accessIds, $request);
        } else {
            return $this->view($this->getAccessIDS(), Response::HTTP_OK);
        }
    }

    /**
     * @OA\Get(
     *     path="/accessids/{id}", operationId="getAccessIdentifier", summary="Get AID by ID", tags={"Access Identifiers"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"locations", "clientApps", "password"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/AccessIdentifier")),
     * )
     * @Security("accessId.belongsToUser(user) and has_role('ROLE_ACCESSIDS_R')")
     */
    public function getAccessidAction(Request $request, AccessID $accessId) {
        return $this->serializedView($accessId, $request, ['accessId.relationsCount']);
    }

    /**
     * @OA\Post(
     *     path="/accessids", operationId="createAccessIdentifier", summary="Create a new Access Identifier", tags={"Access Identifiers"},
     *     @OA\Response(response="201", description="Success", @OA\JsonContent(ref="#/components/schemas/AccessIdentifier")),
     * )
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
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            return $this->serializedView($aid, $request, ['location.relationsCount'], Response::HTTP_CREATED);
        } else {
            return $this->getAccessidAction($request, $aid);
        }
    }

    /**
     * @OA\Delete(
     *     path="/accessids/{id}", operationId="deleteAccessIdentifier", summary="Delete the access identifier", tags={"Access Identifiers"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="204", description="Success"),
     * )
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
     * @OA\Put(
     *     path="/accessids/{id}", operationId="updateAccessIdentifier", summary="Update the access identifier", tags={"Access Identifiers"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="enabled", type="boolean"),
     *          @OA\Property(property="caption", type="string"),
     *          @OA\Property(property="password", type="string", description="Provide new password if you want to change it."),
     *          @OA\Property(property="locationsIds", type="array", description="Location identifiers to assign to this AID.", @OA\Items(type="integer")),
     *          @OA\Property(property="clientAppsIds", type="array", description="Client Apps identifiers to assign to this Access Identifier. If client app is connected to any other AID, it will be disconnected from the old one before assigning.", @OA\Items(type="integer")),
     *       )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/AccessIdentifier")),
     * )
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
