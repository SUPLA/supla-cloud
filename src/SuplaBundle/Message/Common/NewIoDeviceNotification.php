<?php

namespace SuplaBundle\Message\Common;

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Message\CommonMessage;
use SuplaBundle\Message\UserOptOutNotifications;

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
