<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New table supla_channel_value
 */
class Version20200807131101 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql("CREATE TABLE supla_channel_value (channel_id INT NOT NULL, update_time DATETIME DEFAULT NULL COMMENT '(DC2Type:utcdatetime)', valid_to DATETIME DEFAULT NULL COMMENT '(DC2Type:utcdatetime)', guid VARBINARY(8) NOT NULL, PRIMARY KEY(channel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB");
    }
}
