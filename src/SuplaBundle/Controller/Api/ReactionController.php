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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Model\Transactional;
use Symfony\Component\HttpFoundation\Request;

/**
 * @OA\Schema(
 *   schema="Reaction", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 * )
 */
class ReactionController extends RestController {
    use Transactional;

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [];
        return $groups;
    }

    /**
     * @OA\Put(
     *     path="/channels/{channel}/reactions", operationId="updateChannelReactions", summary="Update channel scenes", tags={"Channels"},
     *     @OA\Parameter(description="ID", in="path", name="channel", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reaction"))),
     * )
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_RW')")
     * @Rest\Put("/channels/{channel}/reactions")
     */
    public function putChannelReactionsAction(IODeviceChannel $channel, Request $request) {
//        $this->ensureApiVersion24($request);
//        $scenes = $this->returnScenesFilteredBySubject($channel);
//        return $this->serializedView($scenes->getValues(), $request);
    }
}
