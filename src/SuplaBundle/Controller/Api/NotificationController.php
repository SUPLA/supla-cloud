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

use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Transactional;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="Notification", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="title", type="string"),
 *   @OA\Property(property="body", type="string"),
 * )
 */
class NotificationController extends RestController {
    use Transactional;

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [];
        return $groups;
    }

    /**
     * @OA\Get(
     *     path="/notifications/{id}", operationId="getNotification", summary="Get Notification", tags={"Notifications"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Notification")),
     * )
     * @Security("notification.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', notification.getChannel())")
     */
    public function getNotificationAction(PushNotification $notification) {
        return $this->handleView($this->view($notification, Response::HTTP_OK));
    }

    /**
     * @OA\Patch(
     *     path="/notifications", operationId="sendNotification", summary="Send a notification.",  tags={"Notifications"},
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(ref="#/components/schemas/ChannelActionParamsSend"),
     *     ),
     *     @OA\Response(response="202", description="Notification has been sent."),
     *     @OA\Response(response="400", description="Invalid request", @OA\JsonContent(
     *          @OA\Property(property="status", type="integer", example="400"),
     *     )),
     * )
     * @Rest\Patch("/notifications")
     * @Security("has_role('ROLE_CHANNELS_EA')")
     */
    public function sendNotificationAction(Request $request, ChannelActionExecutor $channelActionExecutor) {
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            throw $this->createNotFoundException();
        }
        $params = json_decode($request->getContent(), true);
        $channelActionExecutor->executeAction(new PushNotification($this->getUser()), ChannelFunctionAction::SEND(), $params);
        return $this->handleView($this->view(null, Response::HTTP_ACCEPTED));
    }
}
