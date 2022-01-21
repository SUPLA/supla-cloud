<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\BurningMessage;
use SuplaBundle\Message\EmailFromTemplate;

class ServiceUnavailableAdminEmailNotification extends EmailFromTemplate implements AsyncMessage, BurningMessage {
    public function __construct(string $url, string $message) {
        parent::__construct('admin_service_unavailable', null, ['url' => $url, 'message' => $message]);
    }

    public function burnAfterSeconds(): int {
        return 3600;
    }
}
