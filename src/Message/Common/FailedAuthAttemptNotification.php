<?php

namespace App\Message\Common;

use App\Entity\Main\User;
use App\Message\CommonMessage;
use App\Message\UserOptOutNotifications;

readonly class FailedAuthAttemptNotification extends CommonMessage {
    public function __construct(User $user, string $ip) {
        parent::__construct($user, UserOptOutNotifications::FAILED_AUTH_ATTEMPT, ['ip' => $ip], 3600);
    }
}
