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

namespace SuplaBundle\ParamConverter;

use Assert\Assertion;
use SuplaBundle\Entity\Main\AmazonAlexa;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\AmazonAlexaRepository;

class AmazonAlexaParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    /** @var AmazonAlexaRepository */
    private $amazonAlexaRepository;

    public function getConvertedClass(): string {
        return AmazonAlexa::class;
    }

    public function __construct(AmazonAlexaRepository $amazonAlexaRepository) {
        $this->amazonAlexaRepository = $amazonAlexaRepository;
    }

    public function convert(array $requestData) {
        $aegc = new AmazonAlexa($this->getCurrentUserOrThrow());

        $accessToken = $requestData['aeg_access_token'] ?? '';
        $expiresIn = intval($requestData['aeg_expires_in'] ?? 0);
        $refreshToken = $requestData['aeg_refresh_token'] ?? '';
        $region = $requestData['aeg_region'] ?? '';

        Assertion::betweenLength($accessToken, 1, 1024);
        Assertion::betweenLength($refreshToken, 1, 1024);
        Assertion::betweenLength($region, 0, 5);

        if ($expiresIn > 0) {
            Assertion::max($expiresIn, 1000000000);
            $interval = new \DateInterval('PT' . $expiresIn . 'S');
        } else {
            $interval = new \DateInterval('P20Y');
        }

        $date = new \DateTime;
        $expiresAt = $date->add($interval);

        $aegc->setAccessToken($accessToken);
        $aegc->setExpiresAt($expiresAt);
        $aegc->setRefreshToken($refreshToken);
        $aegc->setRegion($region);

        return $aegc;
    }
}
