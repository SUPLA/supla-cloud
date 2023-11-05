<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Channel icons.
 */
class Version20181001221229 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_user_icons (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, func INT NOT NULL, image1 LONGBLOB NOT NULL, image2 LONGBLOB DEFAULT NULL, image3 LONGBLOB DEFAULT NULL, image4 LONGBLOB DEFAULT NULL, INDEX IDX_EEB07467A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_user_icons ADD CONSTRAINT FK_EEB07467A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_dev_channel ADD user_icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel ADD CONSTRAINT FK_81E928C9CB4C938 FOREIGN KEY (user_icon_id) REFERENCES supla_user_icons (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_81E928C9CB4C938 ON supla_dev_channel (user_icon_id)');
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD user_icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel_group ADD CONSTRAINT FK_6B2EFCE5CB4C938 FOREIGN KEY (user_icon_id) REFERENCES supla_user_icons (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6B2EFCE5CB4C938 ON supla_dev_channel_group (user_icon_id)');
    }
}
