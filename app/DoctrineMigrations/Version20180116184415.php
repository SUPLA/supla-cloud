<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add `retry` column to `supla_schedule` table
 */
class Version20180116184415 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE supla_schedule ADD COLUMN retry TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
