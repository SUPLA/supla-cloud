<?php
/*
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

use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends RestController {

    /**
     * @Rest\Get("/server-info")
     */
    public function getServerParamsAction(Request $request) {
        $dt = new \DateTime();

        $result = ['address' => $this->container->getParameter('supla_server'),
            'time' => $dt,
            'timezone' => ['name' => $dt->getTimezone()->getName(),
                'offset' => $dt->getOffset()],
        ];

        return $this->handleView($this->view(['data' => $result], Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/logout/{refreshToken}")
     */
    public function logoutAction(Request $request, $refreshToken) {

        $api_man = $this->container->get('api_manager');

        $ts = $this->container->get('security.token_storage')->getToken();
        $api_man->userLogout($ts->getUser(), $ts->getToken(), $refreshToken);

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }
}
