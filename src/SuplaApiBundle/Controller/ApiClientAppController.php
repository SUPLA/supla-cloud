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

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiClientAppController extends RestController {
    use Transactional;
    use SuplaServerAware;

    /**
     * @Rest\Get("/client-apps")
     * @Security("has_role('ROLE_CLIENTAPPS_R')")
     */
    public function getClientAppsAction(Request $request) {
        $clientApps = $this->getUser()->getClientApps();
        $view = $this->view($clientApps, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['accessId', 'connected']);
        return $view;
    }

    /**
     * @Rest\Put("/client-apps/{clientApp}")
     * @Security("clientApp.belongsToUser(user) and has_role('ROLE_CLIENTAPPS_RW')")
     */
    public function putClientAppAction(Request $request, ClientApp $clientApp) {
        return $this->transactional(function (EntityManagerInterface $entityManager) use ($clientApp, $request) {
            $data = $request->request->all();
            $clientApp->setCaption($data['caption'] ?? '');
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
            $view = $this->view($clientApp, Response::HTTP_OK);
            $this->setSerializationGroups($view, $request, ['accessId', 'connected'], ['accessId']);
            return $view;
        });
    }

    /**
     * @Rest\Delete("/client-apps/{clientApp}")
     * @Security("clientApp.belongsToUser(user) and has_role('ROLE_CLIENTAPPS_RW')")
     */
    public function deleteClientAppAction(ClientApp $clientApp): Response {
        return $this->transactional(function (EntityManagerInterface $entityManager) use ($clientApp) {
            $entityManager->remove($clientApp);
            $this->suplaServer->clientReconnect($clientApp);
            return new Response('', Response::HTTP_NO_CONTENT);
        });
    }
}
