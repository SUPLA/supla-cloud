<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Synchronized ESP updates.
 */
class Version20201213133718 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE esp_update MODIFY COLUMN id int(11) auto_increment NOT NULL');
        $this->addSql('ALTER TABLE esp_update ADD is_synced TINYINT DEFAULT 0 NOT NULL');
    }
}
