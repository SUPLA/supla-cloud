<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\User;
use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\EmailFromTemplate;

class UserActivatedAdminEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(User $user) {
        parent::__construct('admin_user_activated', null, ['email' => $user->getEmail()]);
    }
}
