<?php

namespace SuplaBundle\Message;

use SuplaBundle\Entity\User;

class EmailFromTemplate {
    private $templateName;
    private $userId;
    private $data;

    public function __construct(string $templateName, $userId, ?array $data = []) {
        $this->templateName = $templateName;
        $this->userId = $userId instanceof User ? $userId->getId() : $userId;
        $this->data = $data;
    }

    public function getTemplateName(): string {
        return $this->templateName;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getData(): ?array {
        return $this->data;
    }
}
