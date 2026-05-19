<?php

namespace App\Message\Emails;

use App\Message\EmailFromTemplate;

class SampleEmailNotification extends EmailFromTemplate {
    public function __construct($userIdOrRecipient) {
        parent::__construct('sample_email', $userIdOrRecipient);
    }
}
