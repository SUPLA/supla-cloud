<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Channel groups limit.
 */
class Version20180324222844 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE supla_user ADD limit_channel_group INT DEFAULT 20 NOT NULL');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
