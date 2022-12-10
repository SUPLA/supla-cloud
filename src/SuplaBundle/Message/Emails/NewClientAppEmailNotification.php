<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\BurningMessage;
use SuplaBundle\Message\EmailFromTemplate;
use SuplaBundle\Message\UserOptOutNotifications;

class NewClientAppEmailNotification extends EmailFromTemplate implements AsyncMessage, BurningMessage {
    public function __construct(ClientApp $clientApp) {
        parent::__construct(
            UserOptOutNotifications::NEW_CLIENT_APP,
            $clientApp->getUser(),
            [
                'clientApp' => [
                    'id' => $clientApp->getId(),
                    'name' => $clientApp->getName(),
                    'softwareVersion' => $clientApp->getSoftwareVersion(),
                    'regIp' => $clientApp->getRegIpv4(),
                ],
            ]
        );
    }

    public function burnAfterSeconds(): int {
        return 1800;
    }
}
