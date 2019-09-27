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
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

abstract class AbstractSerializer extends ObjectNormalizer {
    /** @required */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor) {
        $this->propertyAccessor = $propertyAccessor;
    }

    /** @required */
    public function setNameConverter(NoopNameConverter $nameConverter) {
        $this->nameConverter = $nameConverter;
    }

    /** @required */
    public function setClassMetadataFactory(ClassMetadataFactoryInterface $classMetadataFactory) {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /** @required */
    public function setCircularReferenceHandlerDependency(ObjectIdCircularReferenceHandler $handler) {
        return $this->setCircularReferenceHandler($handler);
    }

    protected function toIds($collection): array {
        return EntityUtils::mapToIds($collection);
    }

    protected function isSerializationGroupRequested(string $groupName, array &$context): bool {
        if (isset($context[self::GROUPS]) && is_array($context[self::GROUPS])) {
            $requested = in_array($groupName, $context[self::GROUPS]);
            if ($requested) {
                $index = array_search($groupName, $context[self::GROUPS]);
                unset($context[self::GROUPS][$index]);
                $context[self::GROUPS] = array_values($context[self::GROUPS]);
            }
            return $requested;
        }
        return false;
    }

    /**
     * Forces to serialize empty array as json object (i.e. {} instead of []).
     */
    protected function emptyArrayAsObject(array $array) {
        if (count($array) == 0) {
            return new \stdClass();
        }
        return $array;
    }

    /** @inheritDoc */
    final public function normalize($object, $format = null, array $context = []) {
        $normalized = parent::normalize($object, $format, $context);
        if (is_array($normalized)) {
            $this->addExtraFields($normalized, $object, $context);
        }
        return $normalized;
    }

    abstract protected function addExtraFields(array &$normalized, $object, array $context);
}
