<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Channel icons.
 */
class Version20181001221229 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE supla_channel_icons (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, func INT NOT NULL, image1 LONGBLOB NOT NULL, image2 LONGBLOB DEFAULT NULL, image3 LONGBLOB DEFAULT NULL, image4 LONGBLOB DEFAULT NULL, INDEX IDX_EEB07467A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_channel_icons ADD CONSTRAINT FK_EEB07467A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
    }

    public function down(Schema $schema) {
        $this->abortIf(true, 'There is no way back');
    }
}
