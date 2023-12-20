<?php

namespace SuplaBundle\Utils;

class JsonArrayObject implements \JsonSerializable, \ArrayAccess, \Countable {
    /** @var array */
    private $array;

    public function __construct($array) {
        $this->array = is_array($array) ? $array : [];
    }

    public function jsonSerialize() {
        if (isset($this->array[0])) {
            return (object)$this->array;
        }
        return $this->array ?: new \stdClass();
    }

    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset) {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }

    public function count() {
        return count($this->array);
    }

    public function toArray(): array {
        return $this->array;
    }
}
