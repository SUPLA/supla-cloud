<?php

namespace App\Message\Common;

use App\Entity\Main\ClientApp;
use App\Message\CommonMessage;
use App\Message\UserOptOutNotifications;

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
