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

namespace SuplaBundle\Controller\OAuth;

use FOS\OAuthServerBundle\Controller\TokenController;
use OAuth2\OAuth2;
use OpenApi\Annotations as OA;
use SuplaBundle\Auth\ForwardRequestToTargetCloudException;
use SuplaBundle\Model\TargetSuplaCloudRequestForwarder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BrokerTokenController extends TokenController {
    /** @var TargetSuplaCloudRequestForwarder */
    private $suplaCloudRequestForwarder;

    public function __construct(OAuth2 $server, TargetSuplaCloudRequestForwarder $suplaCloudRequestForwarder) {
        parent::__construct($server);
        $this->suplaCloudRequestForwarder = $suplaCloudRequestForwarder;
    }

    /**
     * @OA\Schema(schema="AccessTokenRequestBody", required={"grant_type", "client_id", "client_secret"},
     *     @OA\Property(property="grant_type", type="string", enum={"authorization_code", "refresh_token"}),
     *     @OA\Property(property="client_id", type="string"),
     *     @OA\Property(property="client_secret", type="string"),
     *     @OA\Property(property="redirect_uri", type="string"),
     *     @OA\Property(property="code", type="string"),
     *     @OA\Property(property="refresh_token", type="string"),
     * )
     * @OA\Post(
     *     path="/oauth/v2/token",
     *     operationId="issueAccessToken",
     *     summary="Issues an access token based on authorization_code or refresh_token.",
     *     security={},
     *     tags={"OAuth"},
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\JsonContent(ref="#/components/schemas/AccessTokenRequestBody"),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string"),
     *       @OA\Property(property="expires_in", type="integer"),
     *       @OA\Property(property="token_type", type="string", enum={"bearer"}),
     *       @OA\Property(property="scope", type="string"),
     *       @OA\Property(property="refresh_token", type="string"),
     *       @OA\Property(property="target_url", type="string"),
     *     ))
     * )
     * @Route("/oauth/v2/token", name="fos_oauth_server_token", methods={"GET", "POST"})
     * @Route("/api/{version}/oauth/v2/token", methods={"GET", "POST"})
     * @Route("/api/oauth/v2/token", methods={"GET", "POST"})
     */
    public function tokenAction(Request $request) {
        try {
            return parent::tokenAction($request);
        } catch (ForwardRequestToTargetCloudException $e) {
            $targetCloud = $e->getTargetCloud();
            [$resp, $status] = $this->suplaCloudRequestForwarder->issueOAuthToken($targetCloud, $request, $e->getMappedClientData());
            return new Response(is_array($resp) ? json_encode($resp) : $resp, $status);
        }
    }
}
