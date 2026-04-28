<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170414101854 extends NoWayBackMigration {
    public function migrate() {
        $timezone = date_default_timezone_get();

        if (strlen($timezone) == 0) {
            $timezone = "UTC";
        }

        $this->addSql('CREATE TABLE supla_oauth_refresh_tokens (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B809538C5F37A13B (token), INDEX IDX_B809538C19EB6921 (client_id), INDEX IDX_B809538CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_oauth_auth_codes (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_48E00E5D5F37A13B (token), INDEX IDX_48E00E5D19EB6921 (client_id), INDEX IDX_48E00E5DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_oauth_access_tokens (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_2402564B5F37A13B (token), INDEX IDX_2402564B19EB6921 (client_id), INDEX IDX_2402564BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_oauth_user (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, password VARCHAR(64) NOT NULL, enabled TINYINT(1) NOT NULL, accessId_id INT DEFAULT NULL, INDEX IDX_6C098C52727ACA70 (parent_id), INDEX IDX_6C098C521579A74E (accessId_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_oauth_clients (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', type INT NOT NULL, INDEX IDX_4035AD80727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_scheduled_executions (id INT AUTO_INCREMENT NOT NULL, schedule_id INT NOT NULL, planned_timestamp DATETIME DEFAULT NULL, fetched_timestamp DATETIME DEFAULT NULL, retry_timestamp DATETIME DEFAULT NULL, retry_count INT DEFAULT NULL, result_timestamp DATETIME DEFAULT NULL, consumed TINYINT(1) NOT NULL, result INT DEFAULT NULL, INDEX IDX_FB21DBDCA40BC2D5 (schedule_id), INDEX result_idx (result), INDEX result_timestamp_idx (result_timestamp), INDEX planned_timestamp_idx (planned_timestamp), INDEX retry_timestamp_idx (retry_timestamp), INDEX fetched_timestamp_idx (fetched_timestamp), INDEX consumed_idx (consumed), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_schedule (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, channel_id INT NOT NULL, time_expression VARCHAR(100) NOT NULL, action INT NOT NULL, action_param VARCHAR(255) DEFAULT NULL, mode VARCHAR(15) NOT NULL, date_start DATETIME NOT NULL, date_end DATETIME DEFAULT NULL, enabled TINYINT(1) NOT NULL, next_calculation_date DATETIME DEFAULT NULL, caption VARCHAR(255) DEFAULT NULL, INDEX IDX_323E8ABEA76ED395 (user_id), INDEX IDX_323E8ABE72F5A1AA (channel_id), INDEX next_calculation_date_idx (next_calculation_date), INDEX enabled_idx (enabled), INDEX date_start_idx (date_start), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens ADD CONSTRAINT FK_B809538C19EB6921 FOREIGN KEY (client_id) REFERENCES supla_oauth_clients (id)');
        $this->addSql('ALTER TABLE supla_oauth_refresh_tokens ADD CONSTRAINT FK_B809538CA76ED395 FOREIGN KEY (user_id) REFERENCES supla_oauth_user (id)');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes ADD CONSTRAINT FK_48E00E5D19EB6921 FOREIGN KEY (client_id) REFERENCES supla_oauth_clients (id)');
        $this->addSql('ALTER TABLE supla_oauth_auth_codes ADD CONSTRAINT FK_48E00E5DA76ED395 FOREIGN KEY (user_id) REFERENCES supla_oauth_user (id)');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564B19EB6921 FOREIGN KEY (client_id) REFERENCES supla_oauth_clients (id)');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564BA76ED395 FOREIGN KEY (user_id) REFERENCES supla_oauth_user (id)');
        $this->addSql('ALTER TABLE supla_oauth_user ADD CONSTRAINT FK_6C098C52727ACA70 FOREIGN KEY (parent_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_oauth_user ADD CONSTRAINT FK_6C098C521579A74E FOREIGN KEY (accessId_id) REFERENCES supla_accessid (id)');
        $this->addSql('ALTER TABLE supla_oauth_clients ADD CONSTRAINT FK_4035AD80727ACA70 FOREIGN KEY (parent_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_scheduled_executions ADD CONSTRAINT FK_FB21DBDCA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES supla_schedule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_schedule ADD CONSTRAINT FK_323E8ABEA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_schedule ADD CONSTRAINT FK_323E8ABE72F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id)');
        $this->addSql('ALTER TABLE supla_iodevice ADD original_location_id INT DEFAULT NULL, CHANGE guid guid VARBINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE supla_iodevice ADD CONSTRAINT FK_793D49DF142C1A4 FOREIGN KEY (original_location_id) REFERENCES supla_location (id)');
        $this->addSql('CREATE INDEX IDX_793D49DF142C1A4 ON supla_iodevice (original_location_id)');
        $this->addSql('ALTER TABLE supla_user ADD timezone VARCHAR(50) NOT NULL, ADD limit_schedule INT DEFAULT 20 NOT NULL');
        $this->addSql("UPDATE supla_user SET timezone = '" . addslashes($timezone) . "'");
        $this->addSql('ALTER TABLE supla_client CHANGE guid guid VARBINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE supla_client DROP FOREIGN KEY FK_5430007F4FEA67CF');
        $this->addSql('ALTER TABLE supla_client ADD CONSTRAINT FK_5430007F4FEA67CF FOREIGN KEY (access_id) REFERENCES supla_accessid (id) ON DELETE CASCADE');
    }
}
