<?php

namespace App\Message\Emails;

use App\Entity\Main\User;
use App\Message\AsyncMessage;
use App\Message\EmailFromTemplate;

class UserActivatedAdminEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(User $user) {
        parent::__construct('admin_user_activated', null, ['email' => $user->getEmail()], 3600);
    }
}
