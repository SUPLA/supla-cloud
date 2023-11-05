<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add default value to supla_dev_channel#hidden.
 */
class Version20171210105120 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE `supla_dev_channel` CHANGE COLUMN `hidden` `hidden` TINYINT(1) NOT NULL DEFAULT \'0\'');
    }
}
