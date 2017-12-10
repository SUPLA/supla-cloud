<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add default value to supla_dev_channel#hidden.
 */
class Version20171210105120 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `supla_dev_channel` CHANGE COLUMN `hidden` `hidden` TINYINT(1) NOT NULL DEFAULT \'0\'');
    }

    public function down(Schema $schema) {
    }
}
