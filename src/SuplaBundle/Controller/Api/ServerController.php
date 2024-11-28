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

use DateTime;
use FOS\RestBundle\Controller\Annotations\Get;
use OpenApi\Annotations as OA;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\RealClientIpResolver;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Twig\FrontendConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends RestController {
    use SuplaServerAware;

    /** @var string */
    private $suplaVersion;
    /** @var string */
    private $suplaVersionFull;
    /** @var bool */
    private $actAsBrokerCloud;
    /** @var string */
    private $suplaServerHost;
    /** @var FrontendConfig */
    private $frontendConfig;

    public function __construct(
        FrontendConfig $frontendConfig,
        string $suplaServerHost,
        string $suplaVersion,
        string $suplaVersionFull,
        bool $actAsBrokerCloud
    ) {
        $this->frontendConfig = $frontendConfig;
        $this->suplaServerHost = $suplaServerHost;
        $this->suplaVersion = $suplaVersion;
        $this->actAsBrokerCloud = $actAsBrokerCloud;
        $this->suplaVersionFull = $suplaVersionFull;
    }

    /**
     * @OA\Get(
     *     path="/server-info",
     *     operationId="getServerInfo",
     *     summary="Get the server info",
     *     security={},
     *     tags={"Server"},
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(
     *       @OA\Property(property="address", type="string", description="SUPLA Server address (to be used in smartphones)"),
     *       @OA\Property(property="time", type="string", format="date-time", description="Current server time"),
     *       @OA\Property(property="timezone", @OA\Property(property="name", type="string", example="UTC"), @OA\Property(property="offset", type="integer")),
     *       @OA\Property(property="authenticated", type="boolean"),
     *       @OA\Property(property="cloudVersion", type="string"),
     *       @OA\Property(property="cloudVersionFull", type="string"),
     *       @OA\Property(property="apiVersion", type="string"),
     *       @OA\Property(property="serverStatus", type="string", description="SUPLA Server status"),
     *       @OA\Property(property="supportedApiVersions", type="array", @OA\Items(type="string"), example={"2.1.0", "2.2.0", "2.3.0"}),
     *       @OA\Property(property="config", type="object", description="Configuration options for frontend webapp"),
     *     ))
     * )
     * @Get("/server-info")
     */
    public function getServerInfoAction(Request $request, RealClientIpResolver $clientIpResolver) {
        $dt = new DateTime();
        $result = [
            'address' => $this->suplaServerHost,
            'time' => $dt,
            'timezone' => [
                'name' => $dt->getTimezone()->getName(),
                'offset' => $dt->getOffset(),
            ],
        ];
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $user = $this->getUser();
            $result['authenticated'] = !!$user;
            if ($user) { // TODO if has read user access
                $result['username'] = $user->getUsername();
            }
            $result['cloudVersion'] = $this->suplaVersion;
            $result['cloudVersionFull'] = $this->suplaVersionFull;
            $result['apiVersion'] = ApiVersions::fromRequest($request)->getValue();
            $result['supportedApiVersions'] = array_values(array_unique(ApiVersions::toArray()));
            if (defined('APPLICATION_ENV') && APPLICATION_ENV !== 'prod') {
                $result['env'] = APPLICATION_ENV;
            }
            if ($this->actAsBrokerCloud) {
                $result['broker'] = true;
            }
            if ($request->headers->has('X-Cloud-Version')) {
                $result ['debug'] = [
                    'requestIp' => $request->getClientIp(),
                    'realIp' => $clientIpResolver->getRealIp(),
                    'headers' => iterator_to_array($request->headers->getIterator()),
                ];
            }
            $result['config'] = $this->frontendConfig->getConfig();
        } else {
            $result = ['data' => $result];
        }
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            $result['serverStatus'] = $this->suplaServer->getServerStatus();
        }
        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/server-status",
     *     operationId="getSuplaServerStatus",
     *     summary="Get the SUPLA Server status",
     *     security={},
     *     tags={"Server"},
     *     @OA\Response(response="200", description="Supla Server is alive", @OA\JsonContent(@OA\Property(property="status", type="string", example="OK"))),
     *     @OA\Response(response="503", description="Supla Server is down", @OA\JsonContent(@OA\Property(property="status", type="string"))),
     * )
     * @Get("/server-status")
     */
    public function getServerStatusAction() {
        $serverStatus = $this->suplaServer->getServerStatus();
        $responseStatus = $serverStatus === 'OK' ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;
        return $this->view(['status' => $serverStatus], $responseStatus);
    }
}
