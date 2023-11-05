<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add `retry` column to `supla_schedule` table
 */
class Version20180116184415 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_schedule ADD COLUMN retry TINYINT(1) NOT NULL DEFAULT 1');
    }
}
