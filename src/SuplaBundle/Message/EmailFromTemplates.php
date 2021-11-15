<?php

namespace SuplaBundle\Message;

use MyCLabs\Enum\Enum;

/**
 * @method static EmailFromTemplates FAILED_AUTH_ATTEMPT()
 */
class EmailFromTemplates extends Enum {
    const FAILED_AUTH_ATTEMPT = 'failed_auth_attempt';

    public function create($userOrId, array $data = []): EmailFromTemplate {
        return new EmailFromTemplate($this->value, $userOrId, $data);
    }
}
