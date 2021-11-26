<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\User;
use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\EmailFromTemplate;

class FailedAuthAttemptEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(User $user, string $ip) {
        parent::__construct('failed_auth_attempt', $user, ['ip' => $ip]);
    }
}
