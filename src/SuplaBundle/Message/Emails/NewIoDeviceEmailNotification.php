<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\BurningMessage;
use SuplaBundle\Message\EmailFromTemplate;
use SuplaBundle\Message\UserOptOutNotifications;

class NewIoDeviceEmailNotification extends EmailFromTemplate implements AsyncMessage, BurningMessage {
    public function __construct(IODevice $ioDevice) {
        parent::__construct(
            UserOptOutNotifications::NEW_IO_DEVICE,
            $ioDevice->getUser(),
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

    public function burnAfterSeconds(): int {
        return 1800;
    }
}
