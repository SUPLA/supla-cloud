<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add User#accountRemovalRequestedAt
 */
class Version20190720215803 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD account_removal_requested_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\'');
    }
}
