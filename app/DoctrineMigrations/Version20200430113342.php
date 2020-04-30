<?php

namespace Supla\Migrations;

/**
 * Performance improvement for measurement log tables.
 */
class Version20200430113342 extends NoWayBackMigration {
    public function migrate() {
        // drop useless indexes
        $this->addSql('DROP INDEX channel_id_idx ON supla_temperature_log');
        $this->addSql('DROP INDEX date_idx ON supla_temperature_log');
        // create temporary PK(channel_id, date, id) for faster duplicates removal
        $this->addSql('ALTER TABLE supla_temperature_log DROP PRIMARY KEY, ADD KEY(id), ADD PRIMARY KEY(channel_id, date, id)');
        // duplicates removal
        $this->addSql('DELETE t1 FROM supla_temperature_log t1 INNER JOIN supla_temperature_log t2 WHERE t1.id < t2.id AND t1.channel_id = t2.channel_id AND t1.date = t2.date');
        // create final PK(channel_id, date), drop id
        $this->addSql('ALTER TABLE supla_temperature_log DROP PRIMARY KEY, DROP id, ADD PRIMARY KEY(channel_id, date)');
    }
}
