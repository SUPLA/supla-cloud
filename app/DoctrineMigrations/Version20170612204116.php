<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add legacy_password column in order to migrate users to BCrypt.
 */
class Version20170612204116 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->addSql('ALTER TABLE supla_user ADD legacy_password VARCHAR(64) DEFAULT NULL, CHANGE password password VARCHAR(64) DEFAULT NULL');
        $this->addSql('UPDATE supla_user SET legacy_password=password');
        $this->addSql('UPDATE supla_user SET password=NULL');
    }

    public function down(Schema $schema) {
        $this->addSql('UPDATE supla_user SET password=legacy_password');
        $this->addSql('ALTER TABLE supla_user DROP legacy_password, CHANGE password password VARCHAR(64) NOT NULL COLLATE utf8_unicode_ci');
    }
}
