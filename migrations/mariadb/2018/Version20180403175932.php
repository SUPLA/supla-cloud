<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add DC2Type:utcdatetime comments to required columns.
 */
class Version20180403175932 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_client CHANGE reg_date reg_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE last_access_date last_access_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\'');
        $this->addSql('ALTER TABLE supla_iodevice CHANGE reg_date reg_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE last_connected last_connected DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE software_version software_version VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE supla_schedule CHANGE date_start date_start DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE date_end date_end DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE next_calculation_date next_calculation_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\'');
        $this->addSql('ALTER TABLE supla_scheduled_executions CHANGE planned_timestamp planned_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE fetched_timestamp fetched_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE retry_timestamp retry_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE result_timestamp result_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\'');
        $this->addSql('ALTER TABLE supla_temperature_log CHANGE date date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\'');
        $this->addSql('ALTER TABLE supla_temphumidity_log CHANGE date date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\'');
        $this->addSql('ALTER TABLE supla_user CHANGE reg_date reg_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE last_login last_login DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE current_login current_login DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE iodevice_reg_enabled iodevice_reg_enabled DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', CHANGE client_reg_enabled client_reg_enabled DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\'');
        $this->addSql('ALTER TABLE supla_rel_cg DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE supla_rel_cg ADD PRIMARY KEY (group_id, channel_id)');
    }
}
