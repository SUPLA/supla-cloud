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

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\Main\AmazonAlexa;
use SuplaBundle\Entity\Main\GoogleHome;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\AmazonAlexaRepository;
use SuplaBundle\Repository\GoogleHomeRepository;
use SuplaBundle\Supla\SuplaOcrClient;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IntegrationsController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /**
     * @Security("is_granted('ROLE_CHANNELS_EA')")
     * @Rest\Put("/integrations/amazon-alexa")
     */
    public function putAmazonAlexaAction(Request $request, AmazonAlexa $source, AmazonAlexaRepository $amazonAlexaRepository) {
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        };

        try {
            $aegc = $amazonAlexaRepository->findForUser($this->getUser());
            $aegc->setAccessToken($source->getAccessToken());
            $aegc->setExpiresAt($source->getExpiresAt());
            $aegc->setRefreshToken($source->getRefreshToken());
            $aegc->setRegion($source->getRegion());
        } catch (NotFoundHttpException $e) {
            $aegc = $source;
            $aegc->setRegDate(new \DateTime);
        };

        $this->transactional(function (EntityManagerInterface $em) use ($aegc) {
            $em->persist($aegc);
        });

        $this->suplaServer->amazonAlexaCredentialsChanged();

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @Security("is_granted('ROLE_CHANNELS_EA')")
     * @Rest\Put("/integrations/google-home")
     */
    public function putGoogleHomeAction(Request $request, GoogleHome $source, GoogleHomeRepository $googleHomeRepository) {
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        };

        try {
            $gh = $googleHomeRepository->findForUser($this->getUser());
            $gh->setAccessToken($source->getAccessToken());
        } catch (NotFoundHttpException $e) {
            $gh = $source;
            $gh->setRegDate(new \DateTime);
        };

        $this->transactional(function (EntityManagerInterface $em) use ($gh) {
            $em->persist($gh);
        });

        $this->suplaServer->googleHomeCredentialsChanged();

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/integrations/ocr/{channel}/images/latest")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getLatestOcrImageAction(
        Request $request,
        IODeviceChannel $channel,
        SuplaOcrClient $ocr,
        SubjectConfigTranslator $configTranslator
    ) {
        if (!ApiVersions::V3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        };
        $config = $configTranslator->getConfig($channel);
        if (!isset($config['ocr'])) {
            throw new NotFoundHttpException();
        }
        $synced = $channel->getProperty('ocr', [])['ocrSynced'] ?? false;
        if (!$synced) {
            $ocr->registerDevice($channel);
        }
        $image = $ocr->getLatestImage($channel);
        return $this->view($image);
    }

    /**
     * @Rest\Get("/integrations/ocr/{channel}/images")
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getLatestOcrImagesAction(
        Request $request,
        IODeviceChannel $channel,
        SuplaOcrClient $ocr,
        SubjectConfigTranslator $configTranslator
    ) {
        if (!ApiVersions::V3()->isRequestedEqualOrGreaterThan($request)) {
            throw new NotFoundHttpException();
        };
        $config = $configTranslator->getConfig($channel);
        if (!isset($config['ocr'])) {
            throw new NotFoundHttpException();
        }
        $synced = $channel->getProperty('ocr', [])['ocrSynced'] ?? false;
        if (!$synced) {
            $ocr->registerDevice($channel);
        }
        $images = $ocr->getLatestImages($channel);
        return $this->view($images);
    }
}
