<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add supla_dev_channel_group.location_id field with index and foreign key
 */
class Version20180208145738 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD location_id INT NOT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD CONSTRAINT FK_6B2EFCE564D218E FOREIGN KEY (location_id) REFERENCES supla_location (id)');
        $this->addSql('CREATE INDEX IDX_6B2EFCE564D218E ON supla_dev_channel_group (location_id)');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
