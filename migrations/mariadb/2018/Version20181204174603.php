<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New Entity::GoogleHome
 */
class Version20181204174603 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_google_home (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, reg_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_98090074A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_google_home ADD CONSTRAINT FK_98090074A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_user CHANGE short_unique_id short_unique_id CHAR(32) NOT NULL');
    }
}
