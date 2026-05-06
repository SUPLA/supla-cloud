<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add legacy_password column in order to migrate users to BCrypt.
 */
class Version20170612204116 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD legacy_password VARCHAR(64) DEFAULT NULL, CHANGE password password VARCHAR(64) DEFAULT NULL');
        $this->addSql('UPDATE supla_user SET legacy_password=password');
        $this->addSql('UPDATE supla_user SET password=NULL');
    }
}
