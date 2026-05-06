<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Solves the problem of too small version field size. #353
 */
class Version20200204170901 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE `esp_update` CHANGE `latest_software_version` `latest_software_version` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL');
        $this->addSql('ALTER TABLE `supla_iodevice` CHANGE `software_version` `software_version` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL');
    }
}
