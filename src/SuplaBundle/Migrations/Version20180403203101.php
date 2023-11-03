<?php

namespace SuplaBundle\Migrations;

/**
 * Add alt_icon to supla_dev_channel_group.
 */
class Version20180403203101 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD alt_icon INT DEFAULT NULL');
    }
}
