<?php

namespace Supla\Migrations;

/**
 * Drop supla_dev_channel_group.enabled column.
 */
class Version20180411203913 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP INDEX enabled_idx ON supla_dev_channel_group');
        $this->addSql('ALTER TABLE supla_dev_channel_group DROP enabled');
    }
}
