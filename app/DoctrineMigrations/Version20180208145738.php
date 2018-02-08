<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add supla_dev_channel_group.location_id field with index and foreign key
 */
class Version20180208145738 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE supla_dev_channel_group ADD location_id INT NOT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD CONSTRAINT FK_6B2EFCE564D218E FOREIGN KEY (location_id) REFERENCES supla_location (id)');
        $this->addSql('CREATE INDEX IDX_6B2EFCE564D218E ON supla_dev_channel_group (location_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE supla_dev_channel_group DROP FOREIGN KEY FK_6B2EFCE564D218E');
        $this->addSql('DROP INDEX IDX_6B2EFCE564D218E ON supla_dev_channel_group');
        $this->addSql('ALTER TABLE supla_dev_channel_group DROP location_id');
    }
}
