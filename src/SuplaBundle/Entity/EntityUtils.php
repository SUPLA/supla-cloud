<?php
namespace SuplaBundle\Entity;

final class EntityUtils {
    private function __construct() {
    }

    public static function setField($entity, string $field, $value) {
        (new self())->doSetField($entity, $field, $value);
    }

    private function doSetField($entity, string $field, $value) {
        $prop = $this->getProperty($entity, $field);
        $prop->setAccessible(true);
        $prop->setValue($entity, $value);
    }

    private function getProperty($entity, string $field): \ReflectionProperty {
        $rc = new \ReflectionClass($entity);
        do {
            if ($rc->hasProperty($field)) {
                return $rc->getProperty($field);
            }
        } while ($rc = $rc->getParentClass());
        throw new \InvalidArgumentException("There is no $field field in the " . get_class($entity));
    }

    public static function getField($entity, string $field) {
        return (new self())->doGetField($entity, $field);
    }

    private function doGetField($entity, string $field) {
        $getter = function (string $field) {
            return $this->{$field};
        };
        return $getter->call($entity, $field);
    }

    public static function mapToIds($entities): array {
        if (!is_array($entities)) {
            $entities = iterator_to_array($entities);
        }
        return array_values(array_map(fn($entity) => $entity->getId(), $entities));
    }

    public static function uniqueByIds(array $entities): array {
        return array_values(array_combine(self::mapToIds($entities), $entities));
    }
}
