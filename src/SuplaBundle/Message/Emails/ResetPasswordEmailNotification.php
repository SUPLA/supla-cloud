<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Message\EmailFromTemplate;

class ResetPasswordEmailNotification extends EmailFromTemplate {
    public function __construct(User $user) {
        parent::__construct('resetpwd', $user, ['confirmationUrl' => '/reset-password/' . $user->getToken()]);
    }
}
