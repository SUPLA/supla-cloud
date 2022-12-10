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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\HasRelationsCount;
use SuplaBundle\Model\ApiVersions;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

abstract class AbstractSerializer extends ObjectNormalizer {
    private $defaultCircularReferenceHandler;

    public function __construct() {
        parent::__construct();
        $this->defaultCircularReferenceHandler = new ObjectIdCircularReferenceHandler();
    }

    /** @required */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor) {
        $this->propertyAccessor = $propertyAccessor;
    }

    /** @required */
    public function setNameConverter(NameConverterInterface $nameConverter) {
        $this->nameConverter = $nameConverter;
    }

    /** @required */
    public function setClassMetadataFactory(ClassMetadataFactoryInterface $classMetadataFactory) {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    protected function toIds($collection): array {
        return EntityUtils::mapToIds($collection);
    }

    protected function isSerializationGroupRequested(string $groupName, array &$context): bool {
        if (isset($context[self::GROUPS]) && is_array($context[self::GROUPS])) {
            return in_array($groupName, $context[self::GROUPS]);
        }
        return false;
    }

    /** @inheritDoc */
    final public function normalize($object, $format = null, array $context = []) {
        $context[self::ENABLE_MAX_DEPTH] = true;
        $context[self::CIRCULAR_REFERENCE_HANDLER] = $this->defaultCircularReferenceHandler;
        $normalized = parent::normalize($object, $format, $context);
        if (is_array($normalized)) {
            if ($object instanceof HasRelationsCount && ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
                if ($relationsCount = $object->getRelationsCount()) {
                    $normalized['relationsCount'] = $relationsCount;
                }
            }
            $this->addExtraFields($normalized, $object, $context);
        }
        return $normalized;
    }

    abstract protected function addExtraFields(array &$normalized, $object, array $context);
}
