<?php

namespace App\Message\Emails;

use App\Entity\Main\IODevice;
use App\Message\AsyncMessage;
use App\Message\EmailFromTemplate;

class ConfirmationAfterDeviceUnlockEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(IODevice $device) {
        parent::__construct(
            'confirmation_after_device_unlock',
            $device->getUser(),
            [
                'deviceId' => $device->getId(),
                'deviceLabel' => $this->getDeviceLabel($device),
            ],
            3600
        );
    }

    private function getDeviceLabel(IODevice $device): string {
        if ($device->getComment()) {
            return sprintf("%s (%s)", $device->getComment(), $device->getName());
        } else {
            return $device->getName();
        }
    }
}
