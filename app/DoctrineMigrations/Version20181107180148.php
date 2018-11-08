<?php

namespace Supla\Migrations;

/**
 * Alexa Event Gateway Credentials entity
 */
class Version20181107180148  extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_alexa_egc (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, reg_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', access_token VARCHAR(1024) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', refresh_token VARCHAR(1024) NOT NULL, UNIQUE INDEX UNIQ_9553EE97A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_alexa_egc ADD CONSTRAINT FK_9553EE97A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
    }
}

