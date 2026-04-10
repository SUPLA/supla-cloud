<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

class Version20171013140904 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_rel_cg (channel_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_BE981CD772F5A1AA (channel_id), INDEX IDX_BE981CD7FE54D947 (group_id), PRIMARY KEY(channel_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_dev_channel_group (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, enabled TINYINT(1) NOT NULL, caption VARCHAR(255) DEFAULT NULL, func INT NOT NULL, INDEX IDX_6B2EFCE5A76ED395 (user_id), INDEX enabled_idx (enabled), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_rel_cg ADD CONSTRAINT FK_BE981CD772F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id)');
        $this->addSql('ALTER TABLE supla_rel_cg ADD CONSTRAINT FK_BE981CD7FE54D947 FOREIGN KEY (group_id) REFERENCES supla_dev_channel_group (id)');
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD CONSTRAINT FK_6B2EFCE5A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_dev_channel ADD alt_icon INT DEFAULT NULL, ADD hidden TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE supla_client ADD caption VARCHAR(100) DEFAULT NULL, CHANGE reg_date reg_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', CHANGE last_access_date last_access_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\'');

        $this->addSql('ALTER TABLE supla_user ADD last_ipv4_tmp  INT UNSIGNED');
        $this->addSql('UPDATE supla_user SET last_ipv4_tmp = last_ipv4 WHERE last_ipv4 >= 0');
        $this->addSql('UPDATE supla_user SET last_ipv4_tmp = CONVERT(last_ipv4 & 0x00000000FFFFFFFF, UNSIGNED INT)  WHERE last_ipv4 < 0');
        $this->addSql('ALTER TABLE supla_user CHANGE last_ipv4 last_ipv4 INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE supla_user SET last_ipv4 = last_ipv4_tmp');
        $this->addSql('ALTER TABLE supla_user DROP last_ipv4_tmp');

        $this->addSql('ALTER TABLE supla_user ADD current_ipv4_tmp  INT UNSIGNED');
        $this->addSql('UPDATE supla_user SET current_ipv4_tmp = current_ipv4 WHERE current_ipv4 >= 0');
        $this->addSql('UPDATE supla_user SET current_ipv4_tmp = CONVERT(current_ipv4 & 0x00000000FFFFFFFF, UNSIGNED INT)  WHERE current_ipv4 < 0');
        $this->addSql('ALTER TABLE supla_user CHANGE current_ipv4 current_ipv4 INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE supla_user SET current_ipv4 = current_ipv4_tmp');
        $this->addSql('ALTER TABLE supla_user DROP current_ipv4_tmp');
    }
}
