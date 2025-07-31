<?php

namespace SuplaBundle\Message\Common;

use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Message\CommonMessage;
use SuplaBundle\Message\UserOptOutNotifications;

readonly class NewClientAppNotification extends CommonMessage {
    public function __construct(ClientApp $clientApp) {
        parent::__construct(
            $clientApp->getUser(),
            UserOptOutNotifications::NEW_CLIENT_APP,
            [
                'clientApp' => [
                    'id' => $clientApp->getId(),
                    'name' => $clientApp->getName(),
                    'softwareVersion' => $clientApp->getSoftwareVersion(),
                    'regIp' => $clientApp->getRegIpv4(),
                ],
            ],
        );
    }
}
