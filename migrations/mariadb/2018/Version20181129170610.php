<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Remove table supla_alexa_egc if exists
 * Create table supla_amazon_alexa
 */
class Version20181129170610 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DELETE FROM `migration_versions` WHERE version = \'20181107180148\'');
        $this->addSql('DELETE FROM `migration_versions` WHERE version = \'20181115211850\'');
        $this->addSql('DELETE FROM `migration_versions` WHERE version = \'20181116145751\'');
        $this->addSql('DELETE FROM `migration_versions` WHERE version = \'20181117124914\'');
        $this->addSql('DELETE FROM `migration_versions` WHERE version = \'20181119085610\'');
        $this->addSql('DELETE FROM `migration_versions` WHERE version = \'20181119121610\'');
        $this->addSql('DROP TABLE IF EXISTS supla_alexa_egc');
        $this->addSql('DROP TABLE IF EXISTS supla_amazon_alexa');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_alexa_egc`');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_amazon_alexa`');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_delete_alexa_egc`');
        $this->addSql('CREATE TABLE supla_amazon_alexa (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, reg_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', access_token VARCHAR(1024) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', refresh_token VARCHAR(1024) NOT NULL, region VARCHAR(5) DEFAULT NULL, UNIQUE INDEX UNIQ_290228F0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_amazon_alexa ADD CONSTRAINT FK_290228F0A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('CREATE PROCEDURE `supla_update_amazon_alexa`(IN `_access_token` VARCHAR(1024) CHARSET utf8, IN `_refresh_token` VARCHAR(1024) CHARSET utf8, IN `_expires_in` INT, IN `_user_id` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN UPDATE supla_amazon_alexa SET `access_token` = _access_token, `refresh_token` = _refresh_token, `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _expires_in second) WHERE `user_id` = _user_id; END');
    }
}
