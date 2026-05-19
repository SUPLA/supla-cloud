<?php

namespace App\Message\Common;

use App\Entity\Main\IODevice;
use App\Message\CommonMessage;
use App\Message\UserOptOutNotifications;

readonly class NewIoDeviceNotification extends CommonMessage {
    public function __construct(IODevice $ioDevice) {
        parent::__construct(
            $ioDevice->getUser(),
            UserOptOutNotifications::NEW_IO_DEVICE,
            [
                'device' => [
                    'id' => $ioDevice->getId(),
                    'name' => $ioDevice->getName(),
                    'softwareVersion' => $ioDevice->getSoftwareVersion(),
                    'regIp' => $ioDevice->getRegIpv4(),
                ],
            ]
        );
    }
}
