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

namespace SuplaBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/client-apps")
 */
class ClientAppController extends AbstractController {
    use Transactional;
    use SuplaServerAware;

    /**
     * @Route("", methods={"GET"}, name="_client_apps_list")
     * @Template
     */
    public function clientAppsListAction(Request $request) {
        if ($this->expectsJsonResponse()) {
            $clientApps = $this->getUser()->getClientApps()->toArray();
            if ($request->get('onlyConnected', false)) {
                $clientApps = $this->suplaServer->getOnlyConnectedClientApps($clientApps);
            }
            return $this->jsonResponse($clientApps);
        } else {
            return [];
        }
    }

    /**
     * @Route("/{clientApp}")
     * @Method("PUT")
     * @Security("user == clientApp.getUser()")
     */
    public function editAction(ClientApp $clientApp, Request $request) {
        return $this->transactional(function (EntityManagerInterface $entityManager) use ($clientApp, $request) {
            $data = $request->request->all();
            $clientApp->setName($data['name'] ?? '');
            $reloadClient = false;
            $desiredEnabled = $data['enabled'] ?? false;
            if ($desiredEnabled != $clientApp->getEnabled()) {
                $reloadClient = true;
                $clientApp->setEnabled($desiredEnabled);
            }
            $desiredAccessId = $data['accessIdId'] ?? 0;
            if ($desiredAccessId && (!$clientApp->getAccessId() || $clientApp->getAccessId()->getId() != $desiredAccessId)) {
                $reloadClient = true;
                foreach ($this->getUser()->getAccessIDS() as $accessID) {
                    if ($accessID->getId() == $desiredAccessId) {
                        $clientApp->setAccessId($accessID);
                        break;
                    }
                }
            }
            $entityManager->persist($clientApp);
            if ($reloadClient) {
                $this->suplaServer->clientReconnect($clientApp);
            }
            return $this->jsonResponse($clientApp);
        });
    }

    /**
     * @Route("/{clientApp}")
     * @Method("DELETE")
     * @Security("user == clientApp.getUser()")
     */
    public function deleteAction(ClientApp $clientApp): Response {
        return $this->transactional(function (EntityManagerInterface $entityManager) use ($clientApp) {
        	$this->suplaServer->clientReconnect($clientApp);
            $entityManager->remove($clientApp);
            return new Response('', 204);
        });
    }
}
