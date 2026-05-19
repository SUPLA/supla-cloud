<?php

namespace App\Message\Emails;

use App\Message\AsyncMessage;
use App\Message\EmailFromTemplate;

class DeleteTargetCloudConfirmationEmailNotification extends EmailFromTemplate implements AsyncMessage {
    public function __construct(string $emailAddress, string $targetCloudUrl, int $targetCloudId, string $token) {
        parent::__construct(
            'confirm_target_cloud_deletion',
            $emailAddress,
            [
                'targetCloudUrl' => $targetCloudUrl,
                'confirmationUrl' => '/confirm-target-cloud-deletion/' . $targetCloudId . '/' . $token,
            ]
        );
    }
}
