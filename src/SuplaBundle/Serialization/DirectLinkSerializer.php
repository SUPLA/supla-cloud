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

use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Exception\InactiveDirectLinkException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class DirectLinkSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /** @var string */
    private $suplaUrl;

    public function __construct(string $suplaUrl) {
        $this->suplaUrl = $suplaUrl;
    }

    /**
     * @param DirectLink $directLink
     * @inheritdoc
     */
    public function normalize($directLink, $format = null, array $context = []) {
        $normalized = parent::normalize($directLink, $format, $context);
        $normalized['userId'] = $directLink->getUser()->getId();
        $subjectType = $directLink->getSubjectType()->getValue();
        $normalized['subjectType'] = $subjectType;
        $normalized['allowedActions'] = array_map(function (ChannelFunctionAction $action) {
            return $action->getName();
        }, $directLink->getAllowedActions());
        $normalized[$subjectType . 'Id'] = $directLink->getSubject()->getId();
        if (isset($context['slug'])) {
            $normalized['slug'] = $context['slug'];
            $normalized['url'] = $directLink->buildUrl($this->suplaUrl, $context['slug']);
        }
        $normalized['activeDateRange'] = [
            'dateStart' => $directLink->getActiveFrom() ? $directLink->getActiveFrom()->format(\DateTime::ATOM) : null,
            'dateEnd' => $directLink->getActiveTo() ? $directLink->getActiveTo()->format(\DateTime::ATOM) : null,
        ];
        if (!$normalized['active']) {
            try {
                $directLink->ensureIsActive();
            } catch (InactiveDirectLinkException $e) {
                $normalized['inactiveReason'] = $e->getMessage();
            }
        }
        return $normalized;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof DirectLink;
    }
}
