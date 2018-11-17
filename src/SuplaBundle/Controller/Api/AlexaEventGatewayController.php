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

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AlexaEventGatewayCredentialsRepository;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Entity\AlexaEventGatewayCredentials;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlexaEventGatewayController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var AlexaEventGatewayCredentialsRepository */
    private $alexaEgcRepository;

    public function __construct(AlexaEventGatewayCredentialsRepository $alexaEgcRepository) {
        $this->alexaEgcRepository = $alexaEgcRepository;
    }

    /**
     * @Security("has_role('ROLE_CHANNELS_EA')")
     * @Rest\Put("/alexa-event-gateway-credentials")
     */
    public function putCredentialsAction(Request $request, AlexaEventGatewayCredentials $source) {
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        };

        try {
            $aegc = $this->alexaEgcRepository->findForUser($this->getUser());
            $aegc->setAccessToken($source->getAccessToken());
            $aegc->setExpiresAt($source->getExpiresAt());
            $aegc->setRefreshToken($source->getRefreshToken());
            $aegc->setRegion($source->getRegion());
            $aegc->setEndpointScope($source->getEndpointScope());
        } catch (NotFoundHttpException $e) {
            $aegc = $source;
            $aegc->setRegDate(new \DateTime);
        };

        $this->transactional(function (EntityManagerInterface $em) use ($aegc) {
            $em->persist($aegc);
        });

        $this->suplaServer->alexaEventGatewayCredentialsChanged();

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @Security("has_role('ROLE_CHANNELS_EA')")
     * @Rest\Delete("/alexa-event-gateway-credentials")
     */
    public function deleteCredentialsAction(Request $request) {
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }

        $this->transactional(function (EntityManagerInterface $em) {
            $aegc = $this->alexaEgcRepository->findForUser($this->getUser());
            $em->remove($aegc);
        });

        $this->suplaServer->alexaEventGatewayCredentialsChanged();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
