<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Performance improvement for measurement log tables.
 */
class Version20200430113342 extends NoWayBackMigration {
    public function migrate() {
        $logTables = [
            'supla_temperature_log',
            'supla_temphumidity_log',
            'supla_thermostat_log',
            'supla_ic_log',
            'supla_em_log',
        ];
        foreach ($logTables as $logTable) {
            // drop useless indexes
            $this->addSql("DROP INDEX channel_id_idx ON $logTable");
            $this->addSql("DROP INDEX date_idx ON $logTable");
            // create temporary PK(channel_id, date, id) for faster duplicates removal
            $this->addSql("ALTER TABLE $logTable DROP PRIMARY KEY, ADD KEY(id), ADD PRIMARY KEY(channel_id, date, id)");
            // duplicates removal
            $this->addSql("DELETE t1 FROM $logTable t1 INNER JOIN $logTable t2 WHERE t1.id < t2.id AND t1.channel_id = t2.channel_id AND t1.date = t2.date");
            // create final PK(channel_id, date), drop id
            $this->addSql("ALTER TABLE $logTable DROP PRIMARY KEY, DROP id, ADD PRIMARY KEY(channel_id, date)");
            // change the datetime doctrine comment not to convert it to \DateTime in PHP
            $this->addSql("ALTER TABLE $logTable CHANGE date date DATETIME NOT NULL COMMENT '(DC2Type:stringdatetime)'");
        }
    }
}
