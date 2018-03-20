<?php
namespace SuplaApiBundle\Entity;

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

    public static function mapToFields($entities, string $field): array {
        if (!is_array($entities)) {
            $entities = iterator_to_array($entities);
        }
        return array_values(array_map(function ($entity) use ($field) {
            return self::getField($entity, $field);
        }, $entities));
    }

    public static function mapToIds($entities): array {
        return self::mapToFields($entities, 'id');
    }
}
