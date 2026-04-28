<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * API rate limits.
 */
class Version20200322123636 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD api_rate_limit VARCHAR(100) DEFAULT NULL');
    }
}
