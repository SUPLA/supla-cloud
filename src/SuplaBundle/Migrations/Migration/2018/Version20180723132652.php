<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Electricity Meter Log
 */
class Version20180723132652 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_em_log (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', phase1_fae BIGINT DEFAULT NULL, phase1_rae BIGINT DEFAULT NULL, phase1_fre BIGINT DEFAULT NULL, phase1_rre BIGINT DEFAULT NULL, phase2_fae BIGINT DEFAULT NULL, phase2_rae BIGINT DEFAULT NULL, phase2_fre BIGINT DEFAULT NULL, phase2_rre BIGINT DEFAULT NULL, phase3_fae BIGINT NOT NULL, phase3_rae BIGINT NOT NULL, phase3_fre BIGINT NOT NULL, phase3_rre BIGINT NOT NULL, INDEX channel_id_idx (channel_id), INDEX date_idx (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
