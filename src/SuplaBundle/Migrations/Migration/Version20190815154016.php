<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Scenes.
 */
class Version20190815154016 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_scene (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, location_id INT NOT NULL, caption VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, user_icon_id INT DEFAULT NULL, INDEX IDX_A4825857A76ED395 (user_id), INDEX IDX_A482585764D218E (location_id), INDEX IDX_A4825857CB4C938 (user_icon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_scene_operation (id INT AUTO_INCREMENT NOT NULL, owning_scene_id INT NOT NULL, channel_id INT DEFAULT NULL, channel_group_id INT DEFAULT NULL, scene_id INT DEFAULT NULL, action INT NOT NULL, action_param VARCHAR(255) DEFAULT NULL, delay_ms INT DEFAULT 0 NOT NULL, INDEX IDX_64A50CF5E019BC26 (owning_scene_id), INDEX IDX_64A50CF572F5A1AA (channel_id), INDEX IDX_64A50CF589E4AAEE (channel_group_id), INDEX IDX_64A50CF5166053B4 (scene_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_scene ADD CONSTRAINT FK_A4825857A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('ALTER TABLE supla_scene ADD CONSTRAINT FK_A482585764D218E FOREIGN KEY (location_id) REFERENCES supla_location (id)');
        $this->addSql('ALTER TABLE supla_scene ADD CONSTRAINT FK_A4825857CB4C938 FOREIGN KEY (user_icon_id) REFERENCES supla_user_icons (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE supla_scene_operation ADD CONSTRAINT FK_64A50CF5E019BC26 FOREIGN KEY (owning_scene_id) REFERENCES supla_scene (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_scene_operation ADD CONSTRAINT FK_64A50CF572F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_scene_operation ADD CONSTRAINT FK_64A50CF589E4AAEE FOREIGN KEY (channel_group_id) REFERENCES supla_dev_channel_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_scene_operation ADD CONSTRAINT FK_64A50CF5166053B4 FOREIGN KEY (scene_id) REFERENCES supla_scene (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_user ADD limit_scene INT DEFAULT 50 NOT NULL');
        $this->addSql('ALTER TABLE supla_direct_link ADD scene_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_direct_link ADD CONSTRAINT FK_6AE7809F166053B4 FOREIGN KEY (scene_id) REFERENCES supla_scene (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6AE7809F166053B4 ON supla_direct_link (scene_id)');
        $this->addSql('ALTER TABLE supla_schedule ADD scene_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_schedule ADD CONSTRAINT FK_323E8ABE166053B4 FOREIGN KEY (scene_id) REFERENCES supla_scene (id) ON DELETE CASCADE ');
        $this->addSql('CREATE INDEX IDX_323E8ABE166053B4 ON supla_schedule (scene_id)');
        $this->addSql('ALTER TABLE supla_schedule DROP FOREIGN KEY FK_323E8ABE72F5A1AA');
        $this->addSql('ALTER TABLE supla_schedule DROP FOREIGN KEY FK_323E8ABE89E4AAEE');
        $this->addSql('ALTER TABLE supla_schedule ADD CONSTRAINT FK_323E8ABE72F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_schedule ADD CONSTRAINT FK_323E8ABE89E4AAEE FOREIGN KEY (channel_group_id) REFERENCES supla_dev_channel_group (id) ON DELETE CASCADE');
    }
}
