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
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class DirectLinkSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param DirectLink $directLink
     * @inheritdoc
     */
    public function normalize($directLink, $format = null, array $context = []) {
        $normalized = parent::normalize($directLink, $format, $context);
        $normalized['userId'] = $directLink->getUser()->getId();
        $normalized['channelId'] = $directLink->getChannel()->getId();
        if ($this->shouldInclude('channel', $context)) {
            $normalized['channel'] = $this->normalizer->normalize($directLink->getChannel(), $format, $context);
        }
        if (isset($context['slug'])) {
            $normalized['slug'] = $context['slug'];
        }
        return $normalized;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof DirectLink;
    }
}
