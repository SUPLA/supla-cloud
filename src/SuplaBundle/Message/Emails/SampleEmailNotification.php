<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Message\EmailFromTemplate;

class SampleEmailNotification extends EmailFromTemplate {
    public function __construct($userIdOrRecipient) {
        parent::__construct('sample_email', $userIdOrRecipient);
    }
}
