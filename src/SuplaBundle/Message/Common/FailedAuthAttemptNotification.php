<?php

namespace SuplaBundle\Message\Common;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Message\CommonMessage;
use SuplaBundle\Message\UserOptOutNotifications;

readonly class FailedAuthAttemptNotification extends CommonMessage {
    public function __construct(User $user, string $ip) {
        parent::__construct($user, UserOptOutNotifications::FAILED_AUTH_ATTEMPT, ['ip' => $ip], 3600);
    }
}
