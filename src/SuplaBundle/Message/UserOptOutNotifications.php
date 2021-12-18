<?php

namespace SuplaBundle\Message;

use MyCLabs\Enum\Enum;

class UserOptOutNotifications extends Enum {
    const FAILED_AUTH_ATTEMPT = 'failed_auth_attempt';
    const NEW_IO_DEVICE = 'new_io_device';
    const NEW_CLIENT_APP = 'new_client_app';
}
