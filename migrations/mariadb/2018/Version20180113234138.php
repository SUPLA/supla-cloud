<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add possibly missing tables required for Over The Air updates of IOs' software.
 */
class Version20180113234138 extends NoWayBackMigration {
    public function migrate() {
        $this->createRequiredTables();
    }

    private function createRequiredTables() {
        $this->addSql(<<<TABLE
        CREATE TABLE IF NOT EXISTS `esp_update` (
            `id` INT(11) NOT NULL,
            `device_id` INT(11) NOT NULL,
            `device_name` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
            `platform` TINYINT(4) NOT NULL,
            `latest_software_version` VARCHAR(10) NOT NULL COLLATE 'utf8_unicode_ci',
            `fparam1` INT(11) NOT NULL,
            `fparam2` INT(11) NOT NULL,
            `protocols` TINYINT(4) NOT NULL,
            `host` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
            `port` INT(11) NOT NULL,
            `path` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
            PRIMARY KEY (`id`),
            INDEX `device_name` (`device_name`),
            INDEX `latest_software_version` (`latest_software_version`),
            INDEX `fparam1` (`fparam1`),
            INDEX `fparam2` (`fparam2`),
            INDEX `platform` (`platform`),
            INDEX `device_id` (`device_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
TABLE
        );
        $this->addSql(<<<TABLE
        CREATE TABLE IF NOT EXISTS `esp_update_log` (
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
    }
}
