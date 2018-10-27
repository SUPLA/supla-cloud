<?php


namespace Supla\Migrations;

/**
 * Add param4 column
 */
class Version20181026171557  extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel ADD param4 VARCHAR(50) DEFAULT NULL AFTER `param3`');
    }
}
