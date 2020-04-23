<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adding the missing AUTO_INCREMENT flag in the esp_update table
 */
class Version20200423130550 extends NoWayBackMigration
{
    public function migrate() {
        $this->addSql('ALTER TABLE `esp_update` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT');
    }

}
