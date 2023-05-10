<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Supla\Migrations;

/**
 * supla_push_notification
 * supla_client push columns
 * supla_value_based_trigger
 * supla_remove_push_recipients procedure
 * Add supla_user.limit_push_notifications.
 */
class Version20230427222824 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_push_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, channel_id INT DEFAULT NULL, iodevice_id INT DEFAULT NULL, managed_by_device TINYINT(1) DEFAULT \'0\' NOT NULL, title VARCHAR(100) DEFAULT NULL, body VARCHAR(255) DEFAULT NULL, sound INT DEFAULT NULL, INDEX IDX_2B227408A76ED395 (user_id), INDEX IDX_2B22740872F5A1AA (channel_id), INDEX IDX_2B227408125F95D6 (iodevice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_rel_aid_pushnotification (push_notification_id INT NOT NULL, access_id INT NOT NULL, INDEX IDX_4A24B3E04E328CBE (push_notification_id), INDEX IDX_4A24B3E04FEA67CF (access_id), PRIMARY KEY(push_notification_id, access_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_value_based_trigger (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, owning_channel_id INT NOT NULL, channel_id INT DEFAULT NULL, channel_group_id INT DEFAULT NULL, scene_id INT DEFAULT NULL, schedule_id INT DEFAULT NULL, push_notification_id INT DEFAULT NULL, `trigger` VARCHAR(2048) DEFAULT NULL, action INT NOT NULL, action_param VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, INDEX IDX_1DFF99CAA76ED395 (user_id), INDEX IDX_1DFF99CA13740A2 (owning_channel_id), INDEX IDX_1DFF99CA72F5A1AA (channel_id), INDEX IDX_1DFF99CA89E4AAEE (channel_group_id), INDEX IDX_1DFF99CA166053B4 (scene_id), INDEX IDX_1DFF99CAA40BC2D5 (schedule_id), INDEX IDX_1DFF99CA4E328CBE (push_notification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_push_notification ADD CONSTRAINT FK_2B227408A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_push_notification ADD CONSTRAINT FK_2B22740872F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_push_notification ADD CONSTRAINT FK_2B227408125F95D6 FOREIGN KEY (iodevice_id) REFERENCES supla_iodevice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_rel_aid_pushnotification ADD CONSTRAINT FK_4A24B3E04E328CBE FOREIGN KEY (push_notification_id) REFERENCES supla_push_notification (id)');
        $this->addSql('ALTER TABLE supla_rel_aid_pushnotification ADD CONSTRAINT FK_4A24B3E04FEA67CF FOREIGN KEY (access_id) REFERENCES supla_accessid (id)');
        $this->addSql('ALTER TABLE supla_value_based_trigger ADD CONSTRAINT FK_1DFF99CAA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_value_based_trigger ADD CONSTRAINT FK_1DFF99CA13740A2 FOREIGN KEY (owning_channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_value_based_trigger ADD CONSTRAINT FK_1DFF99CA72F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_value_based_trigger ADD CONSTRAINT FK_1DFF99CA89E4AAEE FOREIGN KEY (channel_group_id) REFERENCES supla_dev_channel_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_value_based_trigger ADD CONSTRAINT FK_1DFF99CA166053B4 FOREIGN KEY (scene_id) REFERENCES supla_scene (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_value_based_trigger ADD CONSTRAINT FK_1DFF99CAA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES supla_schedule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_value_based_trigger ADD CONSTRAINT FK_1DFF99CA4E328CBE FOREIGN KEY (push_notification_id) REFERENCES supla_push_notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_client ADD push_token VARCHAR(255) DEFAULT NULL, ADD platform TINYINT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:tinyint)\', ADD app_id INT DEFAULT \'0\' NOT NULL, ADD devel_env TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('CREATE PROCEDURE `supla_remove_push_recipients`(IN `_user_id` INT, IN `_client_id` INT) UPDATE supla_client SET push_token = NULL WHERE id = _client_id AND user_id = _user_id');
        $this->addSql('ALTER TABLE supla_user ADD limit_push_notifications INT DEFAULT 20 NOT NULL');
    }
}
