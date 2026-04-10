<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * direct_link#ipv4 unsigned
 */
class Version20181222001450 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_direct_link CHANGE last_ipv4 last_ipv4 INT UNSIGNED DEFAULT NULL');
    }
}
