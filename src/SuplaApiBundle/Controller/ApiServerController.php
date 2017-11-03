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

namespace SuplaApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use SuplaApiBundle\Model\ApiVersions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @apiDefine Error401
 * @apiError 401 Unauthorized. Please authenticate in API with OAuth.
 */
class ApiServerController extends RestController {
    /**
     * @api {get} /server-info Server Info
     * @apiDescription Get server info.
     * @apiGroup Server
     * @apiVersion 2.2.0
     * @apiUse Error401
     * @apiSuccess {string} address URL address of the server
     * @apiSuccess {time} time Current server time
     * @apiSuccess {object} timezone Current server timezone
     * @apiSuccess {string} timezone.name Current server timezone name
     * @apiSuccess {int} timezone.offset Current server timezone offset in minutes
     * @apiSuccess {string} username Current user's username
     * @apiSuccess {string} cloudVersion The version of the SUPLA Cloud
     * @apiSuccess {string} apiVersion The requsted version of SUPLA Cloud API
     * @apiSuccess {string[]} supportedApiVersions List of SUPLA Cloud API versions that is supported by the server and can be chosen with
     * `X-Accept-Version` header
     * @apiSuccessExample Success
     * {"address":"supla.org","time":"2017-11-03T10:47:29+01:00","timezone":{"name":"Europe/Berlin","offset":3600},
     * "username":"supler@supla.org","cloudVersion":"2.2.2","apiVersion":"2.2.0","supportedApiVersions":["2.0.0","2.1.0","2.2.0"]}
     */
    /**
     * @Get("/server-info") */
    public function getServerInfoAction(Request $request) {
        $dt = new \DateTime();
        $result = [
            'address' => $this->container->getParameter('supla_server'),
            'time' => $dt,
            'timezone' => [
                'name' => $dt->getTimezone()->getName(),
                'offset' => $dt->getOffset(),
            ],
        ];
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $user = $this->getUser();
            $result['username'] = $user->getUsername();
            $result['cloudVersion'] = $this->container->getParameter('supla.version');
            $result['apiVersion'] = ApiVersions::fromRequest($request)->getValue();
            $result['supportedApiVersions'] = array_values(array_unique(ApiVersions::toArray()));
        } else {
            $result = ['data' => $result];
        }
        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Get("/logout/{refreshToken}", name="api_logout")
     */
    public function logoutAction(Request $request, $refreshToken) {

        $api_man = $this->container->get('api_manager');

        $ts = $this->container->get('security.token_storage')->getToken();
        $api_man->userLogout($ts->getUser(), $ts->getToken(), $refreshToken);

        return $this->view(null, Response::HTTP_OK);
    }
}
