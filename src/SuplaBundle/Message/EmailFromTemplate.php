<?php

namespace SuplaBundle\Message;

use SuplaBundle\Entity\User;

class EmailFromTemplate implements AsyncMessage {
    private $template;
    private $userId;
    private $data;

    public function __construct(string $template, $userId, ?array $data = []) {
        $this->template = $template;
        $this->userId = $userId instanceof User ? $userId->getId() : $userId;
        $this->data = $data;
    }

    public function getTemplate(): string {
        return $this->template;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getData(): ?array {
        return $this->data;
    }
}
