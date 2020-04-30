<?php

namespace Supla\Migrations;

/**
 * Performance improvement for measurement log tables.
 */
class Version20200430113342 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_temperature_log DROP PRIMARY KEY, ADD KEY(id), ADD PRIMARY KEY(channel_id, date, id)');
    }
}
