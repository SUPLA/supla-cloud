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

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="ClientApp", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="name", type="string", description="Name from the device"),
 *   @OA\Property(property="caption", type="string", description="Caption set by the user"),
 *   @OA\Property(property="enabled", type="boolean"),
 *   @OA\Property(property="connected", type="boolean", description="Whether the app is connected now or not, sent only if requested by the `include` param"),
 *   @OA\Property(property="lastAccessDate", type="string", format="date-time"),
 *   @OA\Property(property="lastAccessIpv4", type="string", format="ipv4"),
 *   @OA\Property(property="regDate", type="string", format="date-time"),
 *   @OA\Property(property="regIpv4", type="string", format="ipv4"),
 *   @OA\Property(property="softwareVersion", type="string"),
 *   @OA\Property(property="protocolVersion", type="integer"),
 *   @OA\Property(property="accessIdId", type="integer"),
 *   @OA\Property(property="accessId", description="Access identifier, if requested by the `include` param", ref="#/components/schemas/AccessIdentifier"),
 * )
 */
class ClientAppController extends RestController {
    use Transactional;
    use SuplaServerAware;

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        return [
            'accessId', 'connected',
            'accessId' => 'clientApp.accessId',
        ];
    }

    /**
     * @OA\Get(
     *     path="/client-apps", operationId="getClientApps", summary="Get Client Apps", tags={"Client Apps"},
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"accessId", "connected"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ClientApp"))),
     * )
     * @Rest\Get("/client-apps")
     * @Security("is_granted('ROLE_CLIENTAPPS_R')")
     */
    public function getClientAppsAction(Request $request) {
        $clientApps = $this->getUser()->getClientApps();
        $view = $this->serializedView($clientApps, $request);
        if (ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            $view->setHeader('X-Total-Count', count($clientApps));
        }
        return $view;
    }

    /**
     * @OA\Put(
     *     path="/client-apps/{id}", operationId="updateClientApp", summary="Update the client app", tags={"Client Apps"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="accessIdId", type="integer", nullable=true),
     *          @OA\Property(property="caption", type="string", nullable=true),
     *          @OA\Property(property="enabled", type="boolean", nullable=true),
     *       ),
     *     ),
     *     @OA\Response(response="201", description="Success", @OA\JsonContent(ref="#/components/schemas/ClientApp")),
     * )
     * @Rest\Put("/client-apps/{clientApp}")
     * @Security("clientApp.belongsToUser(user) and is_granted('ROLE_CLIENTAPPS_RW')")
     * @UnavailableInMaintenance
     */
    public function putClientAppAction(Request $request, ClientApp $clientApp) {
        return $this->transactional(function (EntityManagerInterface $entityManager) use ($clientApp, $request) {
            $data = $request->request->all();
            $clientApp->setCaption($data['caption'] ?? '');
            $reloadClient = false;
            $desiredEnabled = $data['enabled'] ?? false;
            if ($desiredEnabled != $clientApp->getEnabled()) {
                $reloadClient = true;
                $clientApp->setEnabled($desiredEnabled);
            }
            $desiredAccessId = $data['accessIdId'] ?? 0;
            if ($desiredAccessId && (!$clientApp->getAccessId() || $clientApp->getAccessId()->getId() != $desiredAccessId)) {
                $reloadClient = true;
                foreach ($this->getUser()->getAccessIDS() as $accessID) {
                    if ($accessID->getId() == $desiredAccessId) {
                        $clientApp->setAccessId($accessID);
                        break;
                    }
                }
            }
            $entityManager->persist($clientApp);
            if ($reloadClient) {
                $this->suplaServer->clientReconnect($clientApp);
            }
            return $this->serializedView($clientApp, $request, ['accessId']);
        });
    }

    /**
     * @OA\Delete(
     *     path="/client-apps/{id}", operationId="deleteClientApp", summary="Delete the client app", tags={"Client Apps"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="204", description="Success"),
     * )
     * @Rest\Delete("/client-apps/{clientApp}")
     * @Security("clientApp.belongsToUser(user) and is_granted('ROLE_CLIENTAPPS_RW')")
     * @UnavailableInMaintenance
     */
    public function deleteClientAppAction(ClientApp $clientApp): Response {
        return $this->transactional(function (EntityManagerInterface $entityManager) use ($clientApp) {
            $entityManager->remove($clientApp);
            $this->suplaServer->clientReconnect($clientApp);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }
}
