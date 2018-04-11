<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Drop supla_dev_channel_group.enabled column.
 */
class Version20180411203913 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP INDEX enabled_idx ON supla_dev_channel_group');
        $this->addSql('ALTER TABLE supla_dev_channel_group DROP enabled');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
