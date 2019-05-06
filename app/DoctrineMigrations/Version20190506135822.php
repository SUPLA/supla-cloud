<?php

namespace Supla\Migrations;

/**
 * Scenes.
 */
class Version20190506135822 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_scene (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, caption VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, INDEX IDX_A4825857A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_scene_operation (id INT AUTO_INCREMENT NOT NULL, scene_id INT NOT NULL, channel_id INT DEFAULT NULL, channel_group_id INT DEFAULT NULL, action INT NOT NULL, action_param VARCHAR(255) DEFAULT NULL, delay_ms INT DEFAULT 0 NOT NULL, INDEX IDX_64A50CF5166053B4 (scene_id), INDEX IDX_64A50CF572F5A1AA (channel_id), INDEX IDX_64A50CF589E4AAEE (channel_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_scene ADD CONSTRAINT FK_A4825857A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_scene_operation ADD CONSTRAINT FK_64A50CF5166053B4 FOREIGN KEY (scene_id) REFERENCES supla_scene (id)');
        $this->addSql('ALTER TABLE supla_scene_operation ADD CONSTRAINT FK_64A50CF572F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_scene_operation ADD CONSTRAINT FK_64A50CF589E4AAEE FOREIGN KEY (channel_group_id) REFERENCES supla_dev_channel_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_user ADD limit_scenes INT DEFAULT 50 NOT NULL');
    }
}
