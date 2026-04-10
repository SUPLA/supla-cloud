<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Nullable User#token
 */
class Version20190813232026 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user CHANGE token token VARCHAR(255) DEFAULT NULL');
    }
}
