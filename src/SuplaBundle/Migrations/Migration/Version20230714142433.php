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

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Delete zombie notifications left after VBT edits.
 * Added supla_dev_channel_extended_value table.
 * Added supla_update_channel_extended_value procedure.
 */
class Version20230714142433 extends NoWayBackMigration {
    public function migrate() {
        $this->updateVbtPushNotifications();
        $this->createSuplaDevChannelExtendedValueTable();
        $this->createSuplaDevChannelUpdateExtendedValueProcedure();
    }

    /**
     * @return void
     */
    public function updateVbtPushNotifications(): void {
        $this->addSql(<<<SQL
        DELETE FROM supla_push_notification WHERE id IN(
            SELECT pn.id FROM (SELECT * FROM supla_push_notification) pn
            LEFT JOIN supla_value_based_trigger vbt ON pn.id = vbt.push_notification_id
            LEFT JOIN supla_scene_operation so ON pn.id = so.push_notification_id 
            WHERE pn.channel_id IS NULL AND pn.iodevice_id IS NULL AND vbt.id IS NULL AND so.id IS NULL
        );
SQL
        );
        $this->addSql(<<<SQL
        UPDATE supla_push_notification pn SET channel_id = (
            SELECT owning_channel_id FROM supla_value_based_trigger WHERE push_notification_id = pn.id
        )
        WHERE channel_id IS NULL AND iodevice_id IS NULL;
SQL
        );
    }

    /**
     * @return void
     */
    public function createSuplaDevChannelExtendedValueTable(): void {
        $this->addSql('CREATE TABLE supla_dev_channel_extended_value (channel_id INT NOT NULL, user_id INT NOT NULL, update_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', type TINYINT NOT NULL COMMENT \'(DC2Type:tinyint)\', value VARBINARY(1024) DEFAULT NULL, INDEX IDX_3207F134A76ED395 (user_id), PRIMARY KEY(channel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_dev_channel_extended_value ADD CONSTRAINT FK_3207F13472F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_dev_channel_extended_value ADD CONSTRAINT FK_3207F134A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
    }

    /**
     * @return void
     */
    public function createSuplaDevChannelUpdateExtendedValueProcedure(): void {
        $this->addSql(<<<SQL
CREATE PROCEDURE `supla_update_channel_extended_value`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_type` TINYINT,
    IN `_value` VARBINARY(1024)
) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER
BEGIN
    UPDATE `supla_dev_channel_extended_value` SET 
        `update_time` = UTC_TIMESTAMP(), `type` = _type, `value` = _value
         WHERE user_id = _user_id AND channel_id = _id;
    
    IF ROW_COUNT() = 0 THEN
      INSERT INTO `supla_dev_channel_extended_value` (`channel_id`, `user_id`, `update_time`, `type`, `value`) 
         VALUES(_id, _user_id, UTC_TIMESTAMP(), _type, _value)
      ON DUPLICATE KEY UPDATE `type` = _type, `value` = _value, `update_time` = UTC_TIMESTAMP();
     END IF;
END
SQL
        );
    }
}
