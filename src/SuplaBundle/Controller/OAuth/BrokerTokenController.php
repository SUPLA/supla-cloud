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
     * @Route("/oauth/v2/token", name="fos_oauth_server_token", methods={"GET", "POST"})
     */
    public function tokenAction(Request $request) {
        try {
            return parent::tokenAction($request);
        } catch (ForwardRequestToTargetCloudException $e) {
            $targetCloud = $e->getTargetCloud();
            list($resp, $status) = $this->suplaCloudRequestForwarder->issueOAuthToken($targetCloud, $request, $e->getMappedClientData());
            return new Response(is_array($resp) ? json_encode($resp) : $resp, $status);
        }
    }
}
