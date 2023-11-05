<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * DELETE CASCADE for FK_EFE348F4A76ED395
 */
class Version20180507095139 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE `supla_audit` DROP FOREIGN KEY `FK_EFE348F4A76ED395`');
        $this->addSql('ALTER TABLE `supla_audit` ADD CONSTRAINT `FK_EFE348F4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user`(`id`) ON DELETE CASCADE');
    }
}
