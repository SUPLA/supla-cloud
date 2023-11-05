<?php

namespace SuplaBundle\Migrations\Migration;

/**
 * Adds locale to user.
 */
class Version20181007112610 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD locale VARCHAR(5) DEFAULT NULL');
    }
}
