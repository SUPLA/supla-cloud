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
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\Main\AmazonAlexa;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Repository\AmazonAlexaRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AmazonAlexaController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var AmazonAlexaRepository */
    private $amazonAlexaRepository;

    public function __construct(AmazonAlexaRepository $amazonAlexaRepository) {
        $this->amazonAlexaRepository = $amazonAlexaRepository;
    }

    /**
     * @Security("has_role('ROLE_CHANNELS_EA')")
     * @Rest\Put("/integrations/amazon-alexa")
     */
    public function putAmazonAlexaAction(Request $request, AmazonAlexa $source) {
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        }

        try {
            $aegc = $this->amazonAlexaRepository->findForUser($this->getUser());
            $aegc->setAccessToken($source->getAccessToken());
            $aegc->setExpiresAt($source->getExpiresAt());
            $aegc->setRefreshToken($source->getRefreshToken());
            $aegc->setRegion($source->getRegion());
        } catch (NotFoundHttpException $e) {
            $aegc = $source;
            $aegc->setRegDate(new DateTime);
        }

        $this->transactional(function (EntityManagerInterface $em) use ($aegc) {
            $em->persist($aegc);
        });

        $this->suplaServer->amazonAlexaCredentialsChanged();

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }
}
