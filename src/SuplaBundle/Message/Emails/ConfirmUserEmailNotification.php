<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Message\EmailFromTemplate;

class ConfirmUserEmailNotification extends EmailFromTemplate {
    public function __construct(User $user) {
        parent::__construct('confirm_user', $user, ['confirmationUrl' => 'confirm/' . $user->getToken()]);
    }
}
