<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171013140904 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE supla_rel_cg (channel_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_BE981CD772F5A1AA (channel_id), INDEX IDX_BE981CD7FE54D947 (group_id), PRIMARY KEY(channel_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_dev_channel_group (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, enabled TINYINT(1) NOT NULL, caption VARCHAR(255) DEFAULT NULL, func INT NOT NULL, INDEX IDX_6B2EFCE5A76ED395 (user_id), INDEX enabled_idx (enabled), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_rel_cg ADD CONSTRAINT FK_BE981CD772F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id)');
        $this->addSql('ALTER TABLE supla_rel_cg ADD CONSTRAINT FK_BE981CD7FE54D947 FOREIGN KEY (group_id) REFERENCES supla_dev_channel_group (id)');
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD CONSTRAINT FK_6B2EFCE5A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_dev_channel ADD alt_icon INT DEFAULT NULL, ADD hidden TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE supla_rel_cg DROP FOREIGN KEY FK_BE981CD7FE54D947');
        $this->addSql('DROP TABLE supla_rel_cg');
        $this->addSql('DROP TABLE supla_dev_channel_group');
        $this->addSql('ALTER TABLE supla_dev_channel DROP alt_icon, DROP hidden');
    }
}
