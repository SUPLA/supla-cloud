<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adds locale to user.
 */
class Version20181007112610 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE supla_user ADD locale VARCHAR(5) DEFAULT NULL');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
