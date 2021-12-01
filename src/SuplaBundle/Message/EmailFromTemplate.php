<?php

namespace SuplaBundle\Message;

use SuplaBundle\Entity\User;

class EmailFromTemplate {
    private $templateName;
    private $userId;
    private $data;
    private $recipient;

    public function __construct(string $templateName, $userIdOrRecipient, ?array $data = []) {
        $this->templateName = $templateName;
        if (is_string($userIdOrRecipient)) {
            $this->recipient = $userIdOrRecipient;
        } else {
            $this->userId = $userIdOrRecipient instanceof User ? $userIdOrRecipient->getId() : $userIdOrRecipient;
        }
        $this->data = $data;
    }

    public function getTemplateName(): string {
        return $this->templateName;
    }

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function getData(): ?array {
        return $this->data;
    }

    public function getRecipient(): ?string {
        return $this->recipient;
    }
}
