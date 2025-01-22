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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AccessIdRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="Notification", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="title", type="string"),
 *   @OA\Property(property="body", type="string"),
 *   @OA\Property(property="ownSubjectType", type="string", enum={"notification"}),
 *   @OA\Property(property="possibleActions", type="array", description="What action can you execute on this subject?", @OA\Items(ref="#/components/schemas/ChannelFunctionAction")),
 *   @OA\Property(property="accessIds", type="array", description="Assigned access identifiers, only if requested in the `include` param", @OA\Items(ref="#/components/schemas/AccessIdentifier")),
 * )
 */
class NotificationController extends RestController {
    use Transactional;

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        return [
            'accessIds',
            'accessIds' => 'notification.accessIds',
        ];
    }

    /**
     * @OA\Get(
     *     path="/notifications/{id}", operationId="getNotification", summary="Get Notification", tags={"Notifications"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"accessIds"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Notification")),
     * )
     * @Rest\Get("/notifications/{notification}")
     * @Security("notification.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', notification.getChannel())")
     */
    public function getNotificationAction(Request $request, PushNotification $notification) {
        return $this->serializedView($notification, $request);
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
     * @Security("is_granted('ROLE_CHANNELS_EA')")
     */
    public function sendNotificationAction(Request $request, ChannelActionExecutor $channelActionExecutor) {
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            throw $this->createNotFoundException();
        }
        $params = json_decode($request->getContent(), true);
        $channelActionExecutor->executeAction(new PushNotification($this->getUser()), ChannelFunctionAction::SEND(), $params);
        return $this->handleView($this->view(null, Response::HTTP_ACCEPTED));
    }

    /**
     * @OA\Put(
     *     path="/notifications/{notification}", operationId="updateNotification",  tags={"Notifications"},
     *     @OA\Parameter(description="ID", in="path", name="notification", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"accessIds"})),
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ChannelActionParamsSend")),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Notification")),
     * )
     * @Rest\Put("/notifications/{notification}")
     * @Security("notification.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW') and is_granted('accessIdContains', notification.getChannel())")
     */
    public function updateNotificationAction(Request $request, PushNotification $notification, AccessIdRepository $aidRepository) {
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            throw $this->createNotFoundException();
        }
        if (!$notification->isManagedByDevice()) {
            throw $this->createNotFoundException();
        }
        $params = json_decode($request->getContent(), true);
        Assertion::isArray($params);
        if (isset($params['title'])) {
            Assertion::string($params['title']);
            Assertion::notNull($notification->getTitle(), 'The title is set by the device and cannot be overridden.');
            $notification->setTitle($params['title']);
        }
        if (isset($params['body'])) {
            Assertion::string($params['body']);
            Assertion::notBlank($params['body'], 'Notification body must not be blank.');
            Assertion::notNull($notification->getBody(), 'The body is set by the device and cannot be overridden.');
            $notification->setBody($params['body']);
        }
        if (isset($params['accessIds'])) {
            $accessIds = array_map(function (int $aid) use ($aidRepository) {
                return $aidRepository->findForUser($this->getUser(), $aid);
            }, $params['accessIds']);
            $notification->setAccessIds($accessIds);
        } else {
            Assertion::notEmpty($notification->getAccessIds(), 'Notification must have recipients.'); // i18n
        }
        $this->transactional(function (EntityManagerInterface $em) use ($notification) {
            $em->persist($notification);
        });
        return $this->serializedView($notification, $request);
    }

    /**
     * @OA\Get(
     *     path="/channels/{channel}/notifications", operationId="getChannelNotifications", tags={"Channels"},
     *     @OA\Parameter(name="channel", description="ID", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="onlyManaged", in="query", description="Return only notification managed by the device (i.e. originating from the firmware). Can be only set to `true`.", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"accessIds"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Notification"))),
     * )
     * @Rest\Get("/channels/{channel}/notifications")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getChannelNotificationsAction(Request $request, IODeviceChannel $channel) {
        $criteria = Criteria::create();
        if (($onlyManaged = $request->get('onlyManaged')) !== null) {
            $onlyManaged = filter_var($onlyManaged, FILTER_VALIDATE_BOOLEAN);
            Assertion::true($onlyManaged, 'The onlyManaged param must be set to true or skipped.');
            $criteria->where(Criteria::expr()->eq('managedByDevice', true));
        }
        $notifications = $channel->getPushNotifications()->matching($criteria);
        return $this->serializedView($notifications, $request);
    }

    /**
     * @OA\Get(
     *     path="/iodevices/{device}/notifications", operationId="getIoDeviceNotifications", tags={"Devices"},
     *     @OA\Parameter(name="device", description="ID", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"accessIds"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Notification"))),
     * )
     * @Rest\Get("/iodevices/{device}/notifications")
     * @Security("device.belongsToUser(user) and is_granted('ROLE_IODEVICES_R') and is_granted('accessIdContains', device)")
     */
    public function getIoDeviceNotificationsAction(Request $request, IODevice $device) {
        $notifications = $device->getPushNotifications();
        return $this->serializedView($notifications, $request);
    }
}
