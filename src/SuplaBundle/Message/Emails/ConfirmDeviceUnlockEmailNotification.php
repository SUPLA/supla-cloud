<?php

namespace SuplaBundle\Message\Emails;

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Message\AsyncMessage;
use SuplaBundle\Message\EmailFromTemplate;

class ConfirmDeviceUnlockEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(string $emailAddress, IODevice $device, string $unlockCode) {
        parent::__construct(
            'confirm_device_unlock',
            $emailAddress,
            [
                'locale' => $device->getUser()->getLocale(),
                'deviceName' => $device->getName(),
                'deviceGuid' => $device->getGUIDString(),
                'deviceUserEmail' => $device->getUser()->getEmail(),
                'confirmationUrl' => '/confirm-device-unlock/' . $device->getId() . '/' . $unlockCode,
            ],
            3600
        );
    }
}
