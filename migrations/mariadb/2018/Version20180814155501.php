<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Flags have been added for compatibility with the Supla protocol v10
 */
class Version20180814155501 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel ADD flags INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_iodevice ADD flags INT DEFAULT NULL');
    }
}
