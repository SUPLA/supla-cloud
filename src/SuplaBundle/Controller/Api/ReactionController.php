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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\ValueBasedTrigger;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Serialization\RequestFiller\ValueBasedTriggerRequestFiller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Schema(
 *   schema="Reaction", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="actionId", ref="#/components/schemas/ChannelFunctionActionIds"),
 *   @OA\Property(property="actionParam", nullable=true, ref="#/components/schemas/ChannelActionParams"),
 *   @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
 *   @OA\Property(property="subjectId", type="integer"),
 *   @OA\Property(property="owningChannelId", description="ID of the channel that this reaction belongs to.", type="integer"),
 *   @OA\Property(property="trigger", ref="#/components/schemas/ReactionTrigger"),
 *   @OA\Property(property="subject", description="Only if requested by the `include` param.", ref="#/components/schemas/ActionableSubject"),
 * )
 */
class ReactionController extends RestController {
    use Transactional;

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [
            'subject', 'owningChannel',
            'subject' => 'reaction.subject',
            'owningChannel' => 'reaction.owningChannel',
        ];
        return $groups;
    }

    /**
     * @OA\Post(
     *     path="/channels/{channel}/reactions", operationId="createChannelReaction", summary="Create channel reaction", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject", "owningChannel"})),
     *     ),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *         @OA\Property(property="subjectId", type="integer"),
     *         @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
     *         @OA\Property(property="actionId", ref="#/components/schemas/ChannelFunctionActionIds"),
     *         @OA\Property(property="actionParam", nullable=true, ref="#/components/schemas/ChannelActionParams"),
     *         @OA\Property(property="trigger", ref="#/components/schemas/ReactionTrigger"),
     *         @OA\Property(property="enabled", type="boolean"),
     *       )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Reaction")),
     * )
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Post("/channels/{channel}/reactions")
     */
    public function postChannelReactionsAction(IODeviceChannel $channel, Request $request, ValueBasedTriggerRequestFiller $requestFiller) {
        $this->ensureApiVersion24($request);
        $vbt = $this->transactional(function (EntityManagerInterface $em) use ($request, $requestFiller, $channel) {
            $user = $this->getUser();
            Assertion::false($user->isLimitReactionsExceeded(), 'Reactions limit has been exceeded'); // i18n
            $vbt = new ValueBasedTrigger($channel);
            $requestFiller->fillFromRequest($request, $vbt);
            $em->persist($vbt);
            return $vbt;
        });
        return $this->serializedView($vbt, $request, [], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/channels/{channel}/reactions/{reaction}", operationId="updateChannelReaction", summary="Update channel reaction", tags={"Channels"},
     *     @OA\Parameter(description="Channel ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(description="Reaction ID", in="path", name="reaction", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject", "owningChannel"})),
     *     ),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(
     *         @OA\Property(property="subjectId", type="integer"),
     *         @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
     *         @OA\Property(property="actionId", ref="#/components/schemas/ChannelFunctionActionIds"),
     *         @OA\Property(property="actionParam", nullable=true, ref="#/components/schemas/ChannelActionParams"),
     *         @OA\Property(property="trigger", ref="#/components/schemas/ReactionTrigger"),
     *         @OA\Property(property="enabled", type="boolean"),
     *       )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Reaction")),
     * )
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Put("/channels/{channel}/reactions/{vbt}")
     */
    public function putChannelReactionAction(
        IODeviceChannel $channel,
        ValueBasedTrigger $vbt,
        Request $request,
        ValueBasedTriggerRequestFiller $requestFiller
    ) {
        $this->ensureApiVersion24($request);
        $this->ensureVbtBelongsToChannel($vbt, $channel);
        $vbt = $this->transactional(function (EntityManagerInterface $em) use ($vbt, $request, $requestFiller, $channel) {
            $requestFiller->fillFromRequest($request, $vbt);
            $em->persist($vbt);
            return $vbt;
        });
        return $this->serializedView($vbt, $request);
    }

    /**
     * @OA\Get(
     *     path="/channels/{channel}/reactions", operationId="getChannelReactions", summary="Get channel reactions", tags={"Channels"},
     *     @OA\Parameter(description="Channel ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject", "owningChannel"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reaction"))),
     * )
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/channels/{channel}/reactions")
     */
    public function getChannelReactionsAction(IODeviceChannel $channel, Request $request) {
        $this->ensureApiVersion24($request);
        return $this->serializedView($channel->getOwnReactions(), $request);
    }

    /**
     * @OA\Get(
     *     path="/reactions", operationId="getReactions", summary="Get reactions", tags={"Channels"},
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject", "owningChannel"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reaction"))),
     * )
     * @Security("is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/reactions")
     */
    public function getReactionsAction(Request $request) {
        $this->ensureApiVersion24($request);
        $reactions = $this->getCurrentUser()->getValueBasedTriggers();
        return $this->serializedView($reactions, $request);
    }

    /**
     * @OA\Get(
     *     path="/channels/{channel}/reactions/{reaction}", operationId="getChannelReaction", summary="Get channel reaction", tags={"Channels"},
     *     @OA\Parameter(description="Channel ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(description="Reaction ID", in="path", name="reaction", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject", "owningChannel"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/Reaction")),
     * )
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/channels/{channel}/reactions/{vbt}")
     */
    public function getChannelReactionAction(IODeviceChannel $channel, ValueBasedTrigger $vbt, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureVbtBelongsToChannel($vbt, $channel);
        return $this->serializedView($vbt, $request);
    }

    /**
     * @OA\Delete(
     *     path="/channels/{channel}/reactions/{reaction}", operationId="deleteChannelReaction", summary="Delete channel reaction", tags={"Channels"},
     *     @OA\Parameter(description="Channel ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(description="Reaction ID", in="path", name="reaction", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="204", description="Success"),
     * )
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_RW')")
     * @Rest\Delete("/channels/{channel}/reactions/{vbt}")
     */
    public function deleteChannelReactionAction(IODeviceChannel $channel, ValueBasedTrigger $vbt, Request $request) {
        $this->ensureApiVersion24($request);
        $this->ensureVbtBelongsToChannel($vbt, $channel);
        $this->transactional(function (EntityManagerInterface $em) use ($vbt) {
            $em->remove($vbt);
        });
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function ensureApiVersion24(Request $request) {
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }
    }

    private function ensureVbtBelongsToChannel(ValueBasedTrigger $vbt, IODeviceChannel $channel): void {
        if ($channel->getId() !== $vbt->getOwningChannel()->getId()) {
            throw new NotFoundHttpException();
        }
    }
}
