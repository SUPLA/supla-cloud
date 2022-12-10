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

use DateTime;
use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Exception\InactiveDirectLinkException;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\LocalSuplaCloud;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class DirectLinkSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /** @var LocalSuplaCloud */
    private $localSuplaCloud;

    public function __construct(LocalSuplaCloud $localSuplaCloud) {
        parent::__construct();
        $this->localSuplaCloud = $localSuplaCloud;
    }

    /**
     * @param \SuplaBundle\Entity\Main\DirectLink $directLink
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $directLink, array $context) {
        $normalized['userId'] = $directLink->getUser()->getId();
        $normalized['subjectType'] = $directLink->getSubjectType()->getValue();
        $normalized['allowedActions'] = array_map(function (ChannelFunctionAction $action) {
            return $action->getName();
        }, $directLink->getAllowedActions());
        $normalized['subjectId'] = $directLink->getSubject()->getId();
        if (isset($context['slug'])) {
            $normalized['slug'] = $context['slug'];
            $normalized['url'] = $directLink->buildUrl($this->localSuplaCloud->getAddress(), $context['slug']);
        }
        $normalized['activeDateRange'] = [
            'dateStart' => $directLink->getActiveFrom() ? $directLink->getActiveFrom()->format(DateTime::ATOM) : null,
            'dateEnd' => $directLink->getActiveTo() ? $directLink->getActiveTo()->format(DateTime::ATOM) : null,
        ];
        if (!$normalized['active']) {
            try {
                $directLink->ensureIsActive();
            } catch (InactiveDirectLinkException $e) {
                $normalized['inactiveReason'] = $e->getReason()->getValue();
            }
        }
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
            $normalized['lastIpv4'] = ($normalized['lastIpv4'] ?? null) ? ip2long($normalized['lastIpv4']) : null;
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof DirectLink;
    }
}
