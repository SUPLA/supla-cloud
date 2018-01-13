<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add possibly missing tables required for Over The Air updates of IOs' software.
 */
class Version20180113234138 extends AbstractMigration {

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->createRequiredTables();
    }

    public function down(Schema $schema) {
    }

    private function createRequiredTables() {
        $this->addSql(<<<TABLE
        CREATE TABLE IF NOT EXISTS `esp_update` (
          `id` int(11) NOT NULL,
          `device_id` int(11) NOT NULL,
          `device_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `platform` tinyint(4) NOT NULL,
          `latest_software_version` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `fparam1` int(11) NOT NULL,
          `fparam2` int(11) NOT NULL,
          `protocols` tinyint(4) NOT NULL,
          `host` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `port` int(11) NOT NULL,
          `path` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
TABLE
        );
        $this->addSql(<<<TABLE
        CREATE TABLE `esp_update_log` (
          `date` datetime NOT NULL,
          `device_id` int(11) NOT NULL,
          `platform` tinyint(4) NOT NULL,
          `fparam1` int(11) NOT NULL,
          `fparam2` int(11) NOT NULL,
          `fparam3` int(11) NOT NULL,
          `fparam4` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
TABLE
        );
        $this->addSql(<<<INDEXES
        ALTER TABLE `esp_update`
          ADD PRIMARY KEY (`id`),
          ADD KEY `device_name` (`device_name`),
          ADD KEY `latest_software_version` (`latest_software_version`),
          ADD KEY `fparam1` (`fparam1`),
          ADD KEY `fparam2` (`fparam2`),
          ADD KEY `platform` (`platform`),
          ADD KEY `device_id` (`device_id`);
INDEXES
        );
    }
}
