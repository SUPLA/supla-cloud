<?php
namespace SuplaBundle\Serialization;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class NoopNameConverter implements NameConverterInterface {
    public function normalize($propertyName) {
        return $propertyName;
    }

    public function denormalize($propertyName) {
        return $propertyName;
    }
}
