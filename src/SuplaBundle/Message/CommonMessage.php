<?php

namespace SuplaBundle\Message;

use SuplaBundle\Entity\Main\User;

readonly class CommonMessage {
    public function __construct(
        private User $user,
        private string $template,
        private array $data = [],
        private int $burnAfterSeconds = 1800
    ) {
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getTemplate(): string {
        return $this->template;
    }

    public function getData(): array {
        return $this->data;
    }

    public function getBurnAfterSeconds(): int {
        return $this->burnAfterSeconds;
    }
}
