<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\BurningMessage;
use SuplaBundle\Message\EmailFromTemplate;

class UserActivatedAdminEmailNotification extends EmailFromTemplate implements AsyncMessage, BurningMessage {
    public function __construct(User $user) {
        parent::__construct('admin_user_activated', null, ['email' => $user->getEmail()]);
    }

    public function burnAfterSeconds(): int {
        return 3600;
    }
}
