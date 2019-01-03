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
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SuplaBundle\Auth\ForwardRequestToTargetCloudException;
use SuplaBundle\Model\LocalSuplaCloud;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BrokerTokenController extends TokenController {
    /** @var LoggerInterface */
    private $logger;
    /** @var LocalSuplaCloud */
    private $localSuplaCloud;

    public function __construct(OAuth2 $server, LoggerInterface $logger, LocalSuplaCloud $localSuplaCloud) {
        parent::__construct($server);
        $this->logger = $logger;
        $this->localSuplaCloud = $localSuplaCloud;
    }

    /**
     * @Route("/oauth/v2/token", name="fos_oauth_server_token", methods={"GET", "POST"})
     */
    public function tokenAction(Request $request) {
        try {
            return parent::tokenAction($request);
        } catch (ForwardRequestToTargetCloudException $e) {
            $targetCloud = $e->getTargetCloud();
            list($response, $status) = $targetCloud->issueOAuthToken($request, $e->getMappedClientData());
            if ($status > 310) {
                $this->logger->error('BROKER: forwarded the token issue request, but target refused to issue the token.', [
                    'payload' => $request->request->all(),
                    'query' => $request->query->all(),
                    'instance' => $this->localSuplaCloud->getAddress(),
                    'target' => $targetCloud->getAddress(),
                    'mappedClientData' => $e->getMappedClientData(),
                ]);
            } else {
                $this->logger->debug('BROKER: issued the token.', [
                    'payload' => $request->request->all(),
                    'query' => $request->query->all(),
                    'instance' => $this->localSuplaCloud->getAddress(),
                    'target' => $targetCloud->getAddress(),
                    'mappedClientData' => $e->getMappedClientData(),
                ]);
            }
            return new Response(is_array($response) ? json_encode($response) : $response, $status);
        } catch (\Exception $e) {
            $this->logger->error('TARGET: refused to issue the token.', [
                'payload' => $request->request->all(),
                'query' => $request->query->all(),
                'instance' => $this->localSuplaCloud->getAddress(),
            ]);
            throw $e;
        }
    }
}
