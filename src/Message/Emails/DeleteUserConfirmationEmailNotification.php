<?php

namespace App\Message\Emails;

use App\Entity\Main\User;
use App\Message\AsyncMessage;
use App\Message\EmailFromTemplate;

class DeleteUserConfirmationEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(User $user) {
        parent::__construct('confirm_deletion', $user, ['confirmationUrl' => '/account-deletion/' . $user->getToken()]);
    }
}
