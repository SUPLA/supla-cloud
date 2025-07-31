<?php

namespace SuplaBundle\Message;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\TimeProvider;

class EmailFromTemplate {
    private $templateName;
    private $userId;
    private $data;
    private $recipient;
    private $eventTimestamp;

    public function __construct(string $templateName, $userIdOrRecipient, ?array $data = [], private $burnAfterSeconds = 0) {
        $this->eventTimestamp = time();
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

    public function getEventTimestamp(): int {
        return $this->eventTimestamp;
    }

    public function isBurnt(TimeProvider $timeProvider): bool {
        return $this->burnAfterSeconds > 0 && ($timeProvider->getTimestamp() - $this->eventTimestamp) > $this->burnAfterSeconds;
    }
}
