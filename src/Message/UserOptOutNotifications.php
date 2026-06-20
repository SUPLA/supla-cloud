<?php

namespace App\Message;

use MyCLabs\Enum\Enum;

class UserOptOutNotifications extends Enum {
    const FAILED_AUTH_ATTEMPT = 'failed_auth_attempt';
    const NEW_IO_DEVICE = 'new_io_device';
    const NEW_CLIENT_APP = 'new_client_app';
    const SCHEDULE_DISABLED_DUE_TO_FAILED_EXECUTIONS = 'schedule_disabled_due_to_failed_executions';
}
