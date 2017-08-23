<?php

namespace SuplaBundle\Tests;

class AnyFieldSetter {
    public static function set($object, $property, $value = null) {
        (new self())->setValue($object, $property, $value);
    }

    private function setValue($object, $property, $value) {
        $properties = $property;
        if (!is_array($properties)) {
            $properties = [$property => $value];
        }
        foreach ($properties as $propertyName => $propertyValue) {
            $setter = function () use ($propertyName, $propertyValue) {
                $this->$propertyName = $propertyValue;
            };
            $setter->call($object);
        }
    }
}
