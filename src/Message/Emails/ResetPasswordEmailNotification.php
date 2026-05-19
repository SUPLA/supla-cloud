<?php

namespace App\Message\Emails;

use App\Entity\Main\User;
use App\Message\EmailFromTemplate;

class ResetPasswordEmailNotification extends EmailFromTemplate {
    public function __construct(User $user) {
        parent::__construct('resetpwd', $user, ['confirmationUrl' => '/reset-password/' . $user->getToken()]);
    }
}
