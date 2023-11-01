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
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use Symfony\Component\HttpFoundation\Request;

class EnumController extends RestController {
    /**
     * @OA\Get(
     *     path="/enum/functions", operationId="getFunctionsEnum", tags={"Enums"},
     *     @OA\Response(response="200", description="Success",  @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ChannelFunction"))),
     * )
     * @Rest\Get("/enum/functions")
     */
    public function getFunctionsAction(Request $request) {
        return $this->serializedView(array_values(ChannelFunction::values()), $request);
    }

    /**
     * @OA\Get(
     *     path="/enum/actions", operationId="getActionsEnum", tags={"Enums"},
     *     @OA\Response(response="200", description="Success",  @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ChannelFunctionAction"))),
     * )
     * @Rest\Get("/enum/actions")
     */
    public function getActionsAction(Request $request) {
        return $this->serializedView(array_values(ChannelFunctionAction::values()), $request);
    }

    /**
     * @OA\Get(
     *     path="/enum/channel-types", operationId="getChannelTypesEnum", tags={"Enums"},
     *     @OA\Response(response="200", description="Success",  @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ChannelType"))),
     * )
     * @Rest\Get("/enum/channel-types")
     */
    public function getChannelTypesAction(Request $request) {
        return $this->serializedView(array_values(ChannelType::values()), $request);
    }
}
