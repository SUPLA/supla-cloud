<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\EmailFromTemplate;

class ServiceUnavailableAdminEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(string $details) {
        parent::__construct('admin_service_unavailable', null, ['detail' => $details]);
    }
}
