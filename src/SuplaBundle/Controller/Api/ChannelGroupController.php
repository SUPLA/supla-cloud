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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\Voter\AccessIdSecurityVoter;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Dependencies\ChannelGroupDependencies;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Serialization\RequestFiller\ChannelGroupRequestFiller;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="ChannelGroup", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="caption", type="string", description="Caption"),
 *   @OA\Property(property="altIcon", type="integer", description="Chosen alternative icon idenifier. Should not be greater than the `function.maxAlternativeIconIndex`."),
 *   @OA\Property(property="hidden", type="boolean", description="Whether this channel group is shown on client apps or not"),
 *   @OA\Property(property="ownSubjectType", type="string", enum={"channelGroup"}),
 *   @OA\Property(property="functionId", type="integer", example=60),
 *   @OA\Property(property="function", ref="#/components/schemas/ChannelFunction"),
 *   @OA\Property(property="locationId", type="integer"),
 *   @OA\Property(property="location", description="Channel group location, if requested by the `include` param", ref="#/components/schemas/Location"),
 *   @OA\Property(property="channels", type="array", description="Channel group channels, if requested by the `include` param", @OA\Items(ref="#/components/schemas/Channel")),
 *   @OA\Property(property="userIconId", type="integer"),
 *   @OA\Property(property="possibleActions", type="array", description="What action can you execute on this subject?", @OA\Items(ref="#/components/schemas/ChannelFunctionAction")),
 *   @OA\Property(property="state", type="object", @OA\AdditionalProperties(ref="#/components/schemas/ChannelState"), example="{2: {connected: true}, 45: {connected: true, on: false}}"),
 *   @OA\Property(property="relationsCount", description="Counts of related entities.",
 *     @OA\Property(property="channels", type="integer"),
 *     @OA\Property(property="directLinks", type="integer"),
 *     @OA\Property(property="schedules", type="integer"),
 *     @OA\Property(property="scenes", type="integer"),
 *     @OA\Property(property="sceneOperations", type="integer"),
 *   ),
 * )
 */
class ChannelGroupController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;

    public function __construct(ChannelActionExecutor $channelActionExecutor, ChannelGroupRepository $channelGroupRepository) {
        $this->channelActionExecutor = $channelActionExecutor;
        $this->channelGroupRepository = $channelGroupRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [
            'iodevice', 'location', 'state', 'relationsCount', 'userIcon',
            'location' => 'channelGroup.location',
            'iodevice' => 'channel.iodevice',
            'relationsCount' => 'channelGroup.relationsCount',
            'userIcon' => 'channelGroup.userIcon',
        ];
        if (!strpos($request->get('_route'), 'channelGroups_list')) {
            $groups[] = 'channels';
            $groups['channels'] = 'channelGroup.channels';
        }
        return $groups;
    }

    /** @return Collection|\SuplaBundle\Entity\Main\IODeviceChannelGroup[] */
    private function returnChannelGroups(callable $filters = null): Collection {
        return $this->channelGroupRepository->findAllForUser($this->getUser(), $filters)
            ->filter(function (IODeviceChannelGroup $channelGroup) {
                return $this->isGranted(AccessIdSecurityVoter::PERMISSION_NAME, $channelGroup);
            });
    }

    /**
     * @OA\Get(
     *     path="/channel-groups", operationId="getChannelGroups", summary="Get Channel Groups", tags={"Channel Groups"},
     *     @OA\Parameter(name="function", in="query", explode=false, required=false, @OA\Schema(type="array", @OA\Items(ref="#/components/schemas/ChannelFunctionEnumNames"))),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"iodevice", "location", "state", "relationsCount", "userIcon"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ChannelGroup"))),
     * )
     * @Rest\Get(path="/channel-groups", name="channelGroups_list")
     * @Security("is_granted('ROLE_CHANNELGROUPS_R')")
     */
    public function getChannelGroupsAction(Request $request) {
        $filters = function (QueryBuilder $builder, string $alias) use ($request) {
            if (($function = $request->get('function')) !== null) {
                $functionIds = EntityUtils::mapToIds(ChannelFunction::fromStrings(explode(',', $function)));
                $builder->andWhere("$alias.function IN(:functionIds)")->setParameter('functionIds', $functionIds);
            }
        };
        return $this->serializedView($this->returnChannelGroups($filters)->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/channels/{id}/channel-groups", operationId="getChannelChannelGroups", summary="Get Channel Groups that the given channel belongs to", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"iodevice", "location", "state", "relationsCount", "userIcon"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ChannelGroup"))),
     * )
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/channels/{channel}/channel-groups", name="channels_channelGroups_list")
     */
    public function getChannelChannelGroupsAction(IODeviceChannel $channel, Request $request) {
        $channelGroups = $this->returnChannelGroups()
            ->filter(function (IODeviceChannelGroup $channelGroup) use ($channel) {
                return in_array($channel->getId(), EntityUtils::mapToIds($channelGroup->getChannels()));
            });
        return $this->serializedView($channelGroups->getValues(), $request);
    }

    /**
     * @OA\Get(
     *     path="/channel-groups/{id}", operationId="getChannelGroup", summary="Get Channel Group", tags={"Channel Groups"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"iodevice", "location", "state", "relationsCount", "channels", "userIcon"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/ChannelGroup")),
     * )
     * @Rest\Get("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and is_granted('ROLE_CHANNELGROUPS_R') and is_granted('accessIdContains', channelGroup)")
     */
    public function getChannelGroupAction(Request $request, IODeviceChannelGroup $channelGroup) {
        return $this->serializedView($channelGroup, $request, ['location.relationsCount', 'channelGroup.relationsCount']);
    }

    /**
     * @OA\Post(
     *     path="/channel-groups", operationId="createChannelGroup", summary="Create a new channel group", tags={"Channel Groups"},
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="channelsIds", type="array", @OA\Items(type="integer"), description="Channels identifiers that share the same function."),
     *          @OA\Property(property="locationId", type="integer", nullable=true),
     *          @OA\Property(property="userIconId", type="integer", nullable=true),
     *          @OA\Property(property="caption", type="string", nullable=true),
     *          @OA\Property(property="altIcon", type="integer", nullable=true),
     *          @OA\Property(property="hidden", type="boolean", nullable=true),
     *       ),
     *     ),
     *     @OA\Response(response="201", description="Success", @OA\JsonContent(ref="#/components/schemas/ChannelGroup")),
     * )
     * @Rest\Post("/channel-groups")
     * @Security("is_granted('ROLE_CHANNELGROUPS_RW')")
     * @UnavailableInMaintenance
     */
    public function postChannelGroupAction(Request $request, ChannelGroupRequestFiller $channelGroupFiller) {
        $user = $this->getUser();
        Assertion::lessThan(
            $user->getChannelGroups()->count(),
            $user->getLimitChannelGroup(),
            'Channel group limit has been exceeded' // i18n
        );
        $channelGroup = $channelGroupFiller->fillFromRequest($request);
        Assertion::notInArray(
            $channelGroup->getFunction()->getId(),
            [
                ChannelFunction::DIGIGLASS_VERTICAL,
                ChannelFunction::DIGIGLASS_HORIZONTAL,
                ChannelFunction::HVAC_THERMOSTAT,
                ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
                ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
                ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
                ChannelFunction::THERMOSTAT,
                ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
            ],
            'Channels groups are not supported for this function.' // i18n
        );
        $channelGroup = $this->transactional(function (EntityManagerInterface $em) use ($channelGroup) {
            $em->persist($channelGroup);
            return $channelGroup;
        });
        $this->suplaServer->reconnect();
        return $this->serializedView($channelGroup, $request, ['channelGroup.relationsCount'], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/channel-groups/{id}", operationId="updateChannelGroup", summary="Update the channel group", tags={"Channel Groups"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          @OA\Property(property="channelsIds", type="array", @OA\Items(type="integer"), description="Channels identifiers that share the same function."),
     *          @OA\Property(property="locationId", type="integer", nullable=true),
     *          @OA\Property(property="userIconId", type="integer", nullable=true),
     *          @OA\Property(property="caption", type="string", nullable=true),
     *          @OA\Property(property="altIcon", type="integer", nullable=true),
     *          @OA\Property(property="hidden", type="boolean", nullable=true),
     *       ),
     *     ),
     *     @OA\Response(response="201", description="Success", @OA\JsonContent(ref="#/components/schemas/ChannelGroup")),
     * )
     * @Rest\Put("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and is_granted('ROLE_CHANNELGROUPS_RW') and is_granted('accessIdContains', channelGroup)")
     * @UnavailableInMaintenance
     */
    public function putChannelGroupAction(
        IODeviceChannelGroup $channelGroup,
        ChannelGroupRequestFiller $channelGroupFiller,
        Request $request
    ) {
        $channelGroup = $channelGroupFiller->fillFromRequest($request, $channelGroup);
        $channelGroup = $this->transactional(function (EntityManagerInterface $em) use ($channelGroup) {
            $em->persist($channelGroup);
            return $channelGroup;
        });
        $this->suplaServer->reconnect();
        return $this->getChannelGroupAction($request, $channelGroup->clearRelationsCount());
    }

    /**
     * @OA\Delete(
     *     path="/channel-groups/{id}", operationId="deleteChannelGroup", summary="Delete the channel group", tags={"Channel Groups"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="204", description="Success"),
     * )
     * @Rest\Delete("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and is_granted('ROLE_CHANNELGROUPS_RW') and is_granted('accessIdContains', channelGroup)")
     * @UnavailableInMaintenance
     */
    public function deleteChannelGroupAction(
        Request $request,
        IODeviceChannelGroup $channelGroup,
        ChannelGroupDependencies $channelGroupDependencies
    ) {
        $result = $this->transactional(function (EntityManagerInterface $em) use ($channelGroupDependencies, $request, $channelGroup) {
            if ($request->get('safe', false)) {
                $dependencies = $channelGroupDependencies->getDependencies($channelGroup);
                if (array_filter($dependencies)) {
                    $view = $this->view(['conflictOn' => 'deletion', 'dependencies' => $dependencies], Response::HTTP_CONFLICT);
                    $this->setSerializationGroups($view, $request, ['scene'], ['scene']);
                    return $view;
                }
            } else {
                $channelGroupDependencies->clearDependencies($channelGroup);
            }
            $em->remove($channelGroup);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
        if ($result->getStatusCode() === Response::HTTP_NO_CONTENT) {
            $this->suplaServer->reconnect();
        }
        return $result;
    }

    /**
     * @OA\Patch(
     *     path="/channel-groups/{id}", operationId="executeActionOnChannelGroup", tags={"Channel Groups"},
     *     @OA\Parameter(description="ID", in="path", name="id", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *          description="Defines an action to execute on channel group. The `action` key is always required. The rest of the keys are params depending on the chosen action.",
     *          externalDocs={"description": "Github Wiki", "url":"https://github.com/SUPLA/supla-cloud/wiki/Channel-Actions"},
     *          allOf={
     *            @OA\Schema(@OA\Property(property="action", ref="#/components/schemas/ChannelFunctionActionEnumNames")),
     *            @OA\Schema(ref="#/components/schemas/ChannelActionParams"),
     *         }
     *       ),
     *     ),
     *     @OA\Response(response="202", description="Action has been committed."),
     *     @OA\Response(response="400", description="Invalid request", @OA\JsonContent(
     *          @OA\Property(property="status", type="integer", example="400"),
     *          @OA\Property(property="message", type="string", example="Cannot execute requested action on this channel group."),
     *     )),
     * )
     * @Rest\Patch("/channel-groups/{channelGroup}")
     * @Security("channelGroup.belongsToUser(user) and is_granted('ROLE_CHANNELGROUPS_EA') and is_granted('accessIdContains', channelGroup)")
     */
    public function patchChannelGroupAction(Request $request, IODeviceChannelGroup $channelGroup) {
        $params = json_decode($request->getContent(), true);
        Assertion::keyExists($params, 'action', 'Missing action.');
        $action = ChannelFunctionAction::fromString($params['action']);
        unset($params['action']);
        $this->channelActionExecutor->executeAction($channelGroup, $action, $params);
        $status = ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request) ? Response::HTTP_ACCEPTED : Response::HTTP_NO_CONTENT;
        return $this->handleView($this->view(null, $status));
    }
}
