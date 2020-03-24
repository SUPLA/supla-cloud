<?php

namespace Supla\Migrations;

/**
 * API rate limits.
 */
class Version20200322123636 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD api_rate_limit VARCHAR(100) DEFAULT NULL');
        $this->addSql('CREATE TABLE supla_api_rate_limit_excess (id INT AUTO_INCREMENT NOT NULL, rule_name VARCHAR(50) NOT NULL, excess SMALLINT UNSIGNED NOT NULL, reset_time DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }
}
