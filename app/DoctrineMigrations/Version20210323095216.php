<?php

namespace Supla\Migrations;

/**
 * Channel's param4.
 */
class Version20210323095216 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel ADD param4 INT DEFAULT 0 NOT NULL');
    }
}
