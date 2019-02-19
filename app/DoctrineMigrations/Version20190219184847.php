<?php

namespace Supla\Migrations;

/**
 * Create supla_thermostat_log table
 */
class Version20190219184847 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_thermostat_log (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', `on` TINYINT(1) NOT NULL, temperature1 NUMERIC(8, 4) DEFAULT NULL, temperature2 NUMERIC(8, 4) DEFAULT NULL, temperature3 NUMERIC(8, 4) DEFAULT NULL, temperature4 NUMERIC(8, 4) DEFAULT NULL, temperature5 NUMERIC(8, 4) DEFAULT NULL, temperature6 NUMERIC(8, 4) DEFAULT NULL, temperature7 NUMERIC(8, 4) DEFAULT NULL, temperature8 NUMERIC(8, 4) DEFAULT NULL, temperature9 NUMERIC(8, 4) DEFAULT NULL, temperature10 NUMERIC(8, 4) DEFAULT NULL, INDEX channel_id_idx (channel_id), INDEX date_idx (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}

