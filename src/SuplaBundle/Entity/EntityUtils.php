<?php
namespace SuplaBundle\Entity;

final class EntityUtils {
    private function __construct() {
    }

    public static function setField($entity, string $field, $value) {
        (new self())->doSetField($entity, $field, $value);
    }

    private function doSetField($entity, string $field, $value) {
        $setter = function (string $field, $value) {
            $this->{$field} = $value;
        };
        $setter->call($entity, $field, $value);
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
        return array_values(array_map(function ($entity) {
            return $entity->getId();
        }, $entities));
    }
}
