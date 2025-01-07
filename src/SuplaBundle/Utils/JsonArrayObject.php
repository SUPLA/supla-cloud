<?php

namespace SuplaBundle\Utils;

class JsonArrayObject implements \JsonSerializable, \ArrayAccess, \Countable {
    private array $array;

    public function __construct($array) {
        $this->array = is_array($array) ? $array : [];
    }

    public function jsonSerialize(): mixed {
        if (isset($this->array[0])) {
            return (object)$this->array;
        }
        return $this->array ?: new \stdClass();
    }

    public function offsetExists($offset): bool {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset): mixed {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value): void {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset): void {
        unset($this->array[$offset]);
    }

    public function count(): int {
        return count($this->array);
    }

    public function toArray(): array {
        return $this->array;
    }
}
