<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\User;
use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\EmailFromTemplate;
use SuplaBundle\Message\UserOptOutNotifications;

class FailedAuthAttemptEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(User $user, string $ip) {
        parent::__construct(UserOptOutNotifications::FAILED_AUTH_ATTEMPT, $user, ['ip' => $ip]);
    }
}
