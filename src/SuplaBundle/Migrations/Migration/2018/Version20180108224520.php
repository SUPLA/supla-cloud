<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add supla_dev_channel_group#hidden.
 */
class Version20180108224520 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD hidden TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
