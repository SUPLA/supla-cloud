<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add user_id column to supla_client.
 * Add auth_key to supla_client and supla_iodevice.
 * Add iodevice_reg_enabled and client_reg_enabled to supla_user.
 */
class Version20170818114139 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->addSql('ALTER TABLE supla_client ADD user_id INT NULL');
        $this->addSql('UPDATE supla_client SET user_id=(SELECT user_id FROM supla_accessid WHERE id=access_id)');
        $this->addSql('ALTER TABLE supla_client CHANGE COLUMN user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE supla_client ADD CONSTRAINT FK_5430007FA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('CREATE INDEX IDX_5430007FA76ED395 ON supla_client (user_id)');
        $this->addSql('ALTER TABLE supla_client ADD auth_key VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_client DROP FOREIGN KEY FK_5430007F4FEA67CF');
        $this->addSql('ALTER TABLE supla_client CHANGE access_id access_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_client ADD CONSTRAINT FK_5430007F4FEA67CF FOREIGN KEY (access_id) REFERENCES supla_accessid (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE supla_iodevice ADD auth_key VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_user ADD iodevice_reg_enabled DATETIME DEFAULT NULL, ADD client_reg_enabled DATETIME DEFAULT NULL');
        $this->addSql('DELETE c1 FROM supla_client c1, supla_client c2 WHERE c1.id < c2.id AND c1.guid = c2.guid');
        $this->addSql('DROP INDEX UNIQUE_CLIENTAPP ON supla_client');
        $this->addSql('CREATE UNIQUE INDEX UNIQUE_CLIENTAPP ON supla_client (user_id, guid)');
        $this->addSql('CREATE INDEX client_reg_enabled_idx ON supla_user (client_reg_enabled)');
        $this->addSql('CREATE INDEX iodevice_reg_enabled_idx ON supla_user (iodevice_reg_enabled)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) {
        $this->addSql('ALTER TABLE supla_client DROP FOREIGN KEY FK_5430007FA76ED395');
        $this->addSql('DROP INDEX IDX_5430007FA76ED395 ON supla_client');
        $this->addSql('DROP INDEX client_reg_enabled_idx ON supla_user');
        $this->addSql('DROP INDEX iodevice_reg_enabled_idx ON supla_user');
        $this->addSql('ALTER TABLE supla_client DROP user_id');
        $this->addSql('ALTER TABLE supla_client DROP auth_key');
        $this->addSql('ALTER TABLE supla_iodevice DROP auth_key');
        $this->addSql('ALTER TABLE supla_user DROP iodevice_reg_enabled');
        $this->addSql('ALTER TABLE supla_user DROP client_reg_enabled');
    }
}
