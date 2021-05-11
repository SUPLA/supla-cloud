<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20210506104813 extends NoWayBackMigration {
    public function up(Schema $schema) {
        $this->migrate();
    }

    public function migrate() {
        $this->addSql('CREATE TABLE supla_temperature_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, temperature NUMERIC(8, 4) NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_temperature_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql("SELECT create_hypertable('supla_temperature_log','date');");
        $this->addSql('CREATE TABLE supla_thermostat_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "on" BOOLEAN NOT NULL, measured_temperature NUMERIC(5, 2) NOT NULL, preset_temperature NUMERIC(5, 2) NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_thermostat_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql("SELECT create_hypertable('supla_thermostat_log','date');");
        $this->addSql('CREATE TABLE supla_ic_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, counter BIGINT NOT NULL, calculated_value BIGINT NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_ic_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql("SELECT create_hypertable('supla_ic_log','date');");
        $this->addSql('CREATE TABLE supla_temphumidity_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, temperature NUMERIC(8, 4) NOT NULL, humidity NUMERIC(8, 4) NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_temphumidity_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql("SELECT create_hypertable('supla_temphumidity_log','date');");
        $this->addSql('CREATE TABLE supla_em_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phase1_fae BIGINT DEFAULT NULL, phase1_rae BIGINT DEFAULT NULL, phase1_fre BIGINT DEFAULT NULL, phase1_rre BIGINT DEFAULT NULL, phase2_fae BIGINT DEFAULT NULL, phase2_rae BIGINT DEFAULT NULL, phase2_fre BIGINT DEFAULT NULL, phase2_rre BIGINT DEFAULT NULL, phase3_fae BIGINT DEFAULT NULL, phase3_rae BIGINT DEFAULT NULL, phase3_fre BIGINT DEFAULT NULL, phase3_rre BIGINT DEFAULT NULL, fae_balanced BIGINT DEFAULT NULL, rae_balanced BIGINT DEFAULT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_em_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql("SELECT create_hypertable('supla_em_log','date');");
    }
}
