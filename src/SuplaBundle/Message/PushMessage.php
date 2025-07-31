<?php

namespace SuplaBundle\Message;

use Assert\Assertion;

readonly class PushMessage {
    public function __construct(private array $accessIdIds, private string $title, private string $body) {
        Assertion::notEmpty($this->accessIdIds, 'AccessIdIds must not be empty.');
        Assertion::allInteger($this->accessIdIds, 'All accessIdIds must be integers.');
    }

    /** @return int[] */
    public function getAccessIdIds(): array {
        return $this->accessIdIds;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getBody(): string {
        return $this->body;
    }
}
