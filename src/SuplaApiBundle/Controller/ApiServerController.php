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

class ApiServerController extends RestController {
    /**
     * @Get("/server-info")
     */
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
        if (ApiVersions::v2_2()->isRequestedEqualOrGreaterThan($request)) {
            $user = $this->getUser();
            $result['username'] = $user->getUsername();
            $result['cloud_version'] = $this->container->getParameter('supla.version');
            $result['api_version'] = ApiVersions::fromRequest($request)->getValue();
            $result['supported_api_versions'] = array_values(array_unique(ApiVersions::toArray()));
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
