<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\EmailFromTemplate;

class DeleteUserConfirmationEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(User $user) {
        parent::__construct('confirm_deletion', $user, ['confirmationUrl' => '/account-deletion/' . $user->getToken()]);
    }
}
