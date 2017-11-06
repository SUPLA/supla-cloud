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

use Assert\Assertion;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class IODeviceSerializer extends ObjectNormalizer {
    use SuplaServerAware;
    use CurrentUserAware;

    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);
    }

    /**
     * @param IODevice $ioDevice
     * @inheritdoc
     */
    public function normalize($ioDevice, $format = null, array $context = []) {
        $normalized = parent::normalize($ioDevice, $format, $context);
        if (isset($context[self::GROUPS]) && is_array($context[self::GROUPS])) {
            if (in_array('connected', $context[self::GROUPS])) {
                $normalized['connected'] = $this->isDeviceConnected($ioDevice);
            }
        }
        return $normalized;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODevice;
    }

    private function isDeviceConnected(IODevice $ioDevice): bool {
        if (!$ioDevice->getEnabled()) {
            return false;
        }
        $user = $this->getCurrentUser();
        Assertion::notNull($user, 'User not authenticated');
        $connectedIds = $this->suplaServer->checkDevicesConnection($user->getId(), [$ioDevice->getId()]);
        return in_array($ioDevice->getId(), $connectedIds);
    }
}
