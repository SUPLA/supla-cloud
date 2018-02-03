<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * IODeviceChannel#location
 */
class Version20180203231115 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE supla_dev_channel ADD location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel ADD CONSTRAINT FK_81E928C964D218E FOREIGN KEY (location_id) REFERENCES supla_location (id)');
        $this->addSql('CREATE INDEX IDX_81E928C964D218E ON supla_dev_channel (location_id)');
    }

    public function down(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE supla_dev_channel DROP FOREIGN KEY FK_81E928C964D218E');
        $this->addSql('DROP INDEX IDX_81E928C964D218E ON supla_dev_channel');
        $this->addSql('ALTER TABLE supla_dev_channel DROP location_id');
    }
}
