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

namespace SuplaBundle\Serialization;

use SuplaBundle\Entity\Main\OAuth\AccessToken;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use UAParser\Parser;

class AccessTokenSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param AccessToken $accessToken
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $accessToken, array $context) {
        if ($this->isSerializationGroupRequested('issuer', $context)) {
            $normalized['issuerSystem'] = null;
            if ($accessToken->getIssuerBrowserString()) {
                $parsed = Parser::create()->parse($accessToken->getIssuerBrowserString());
                $normalized['issuerSystem'] = [
                    'os' => $parsed->os->family,
                    'device' => $parsed->device->family,
                    'browserName' => $parsed->ua->family,
                    'browserVersion' => $parsed->ua->major,
                ];
            }
            $normalized['issuerIp'] = $accessToken->getIssuerIp();
            $normalized['isForWebapp'] = $accessToken->isForWebapp();
            $normalized['apiClient'] = $this->normalizer->normalize($accessToken->getClient(), null, $context);
            $normalized['apiClientAuthorization'] = $this->normalizer->normalize($accessToken->getApiClientAuthorization(), null, $context);
            $normalized['expiresAt'] = (new \DateTime('@' . $accessToken->getExpiresAt()))->format(\DateTime::ATOM);
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof AccessToken;
    }
}
