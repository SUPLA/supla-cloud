<?php

namespace SuplaBundle\Message;

use Assert\Assertion;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\UserPreferences;
use SuplaBundle\Model\TimeProvider;

class PushMessageFromTemplate implements AsyncMessage {
    private $templateName;
    private array $accessIdIds;
    private array $data;
    private $eventTimestamp;

    public function __construct(string $templateName, $userOrAccessIds, ?array $data = [], private $burnAfterSeconds = 0) {
        $this->eventTimestamp = time();
        $this->templateName = $templateName;
        if (is_array($userOrAccessIds)) {
            $this->accessIdIds = $userOrAccessIds;
            Assertion::allInteger($this->accessIdIds, 'All accessIdIds must be integers.');
        } else {
            Assertion::isInstanceOf($userOrAccessIds, User::class, 'User must be instance of User.');
            $this->accessIdIds = $userOrAccessIds->getPreference(UserPreferences::ACCOUNT_PUSH_NOTIFICATIONS_ACCESS_IDS_IDS, []);
        }
        $this->data = $data ?: [];
    }

    public function getTemplateName(): string {
        return $this->templateName;
    }

    public function getAccessIdIds(): array {
        return $this->accessIdIds;
    }

    public function getData(): array {
        return $this->data;
    }

    public function getEventTimestamp(): int {
        return $this->eventTimestamp;
    }

    public function isBurnt(TimeProvider $timeProvider): bool {
        return $this->burnAfterSeconds > 0 && ($timeProvider->getTimestamp() - $this->eventTimestamp) > $this->burnAfterSeconds;
    }
}
