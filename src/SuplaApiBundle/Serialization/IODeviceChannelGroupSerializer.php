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

namespace SuplaApiBundle\Serialization;

use SuplaBundle\Entity\IODeviceChannelGroup;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class IODeviceChannelGroupSerializer extends ObjectNormalizer {

    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);
    }

    /**
     * @param IODeviceChannelGroup $group
     * @inheritdoc
     */
    public function normalize($group, $format = null, array $context = []) {
        $normalized = parent::normalize($group, $format, $context);
        $normalized['locationId'] = $group->getLocation()->getId();
        $normalized['channelIds'] = $this->toIds($group->getChannels());
        $normalized['functionId'] = $group->getFunction()->getId();
        return $normalized;
    }

    private function toIds($collection): array {
        $ids = [];
        foreach ($collection as $item) {
            $ids[] = $item->getId();
        }
        return $ids;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODeviceChannelGroup;
    }
}
