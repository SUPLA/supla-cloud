<?php

namespace App\Message\Emails;

use App\Entity\Main\User;
use App\Message\EmailFromTemplate;

class ConfirmUserEmailNotification extends EmailFromTemplate {
    public function __construct(User $user) {
        parent::__construct('confirm_user', $user, ['confirmationUrl' => 'confirm/' . $user->getToken()]);
    }
}
