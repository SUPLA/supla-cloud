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

use FOS\RestBundle\Controller\Annotations\Get;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends RestController {
    use SuplaServerAware;

    /** @Get("/server-info") */
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
            $result['authenticated'] = !!$user;
            if ($user) { // TODO if has read user access
                $result['username'] = $user->getUsername();
            }
            $result['cloudVersion'] = $this->container->getParameter('supla.version');
            $result['apiVersion'] = ApiVersions::fromRequest($request)->getValue();
            $result['supportedApiVersions'] = array_values(array_unique(ApiVersions::toArray()));
            if ($this->getParameter('act_as_broker_cloud')) {
                $result['broker'] = true;
            }
        } else {
            $result = ['data' => $result];
        }
        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Get("/logout/{refreshToken}", name="api_logout")
     */
    public function logoutAction($refreshToken) {
        $api_man = $this->container->get('api_manager');
        $ts = $this->container->get('security.token_storage')->getToken();
        $api_man->userLogout($ts->getUser(), $ts->getToken(), $refreshToken);
        return $this->view(null, Response::HTTP_OK);
    }

    /** @Get("/server-status") */
    public function getServerStatusAction() {
        $alive = $this->suplaServer->isAlive();
        if ($alive) {
            return $this->view(['status' => 'OK'], Response::HTTP_OK);
        } else {
            return $this->view(['status' => 'DOWN'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /** @Get("/swagger.yaml") */
    public function getApiDocsAction() {
        $yaml = file_get_contents(\AppKernel::VAR_PATH . '/../web/api/supla-api-docs.yaml');
        $suplaDomain = $this->container->getParameter('supla_server');
        $yaml = str_replace('cloud.supla.org', $suplaDomain, $yaml);
        return new Response($yaml, Response::HTTP_OK, ['Content-Type' => 'application/yaml']);
    }
}
