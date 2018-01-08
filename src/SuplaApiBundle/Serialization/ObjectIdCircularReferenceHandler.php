<?php
namespace SuplaApiBundle\Serialization;

class ObjectIdCircularReferenceHandler {
    public function __invoke($object) {
        return $object->getId();
    }
}
