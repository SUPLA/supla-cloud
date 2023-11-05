<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add user_id column to supla_client.
 * Add auth_key to supla_client and supla_iodevice.
 * Add iodevice_reg_enabled and client_reg_enabled to supla_user.
 */
class Version20170818114139 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_client ADD user_id INT NULL');
        $this->addSql('UPDATE supla_client SET user_id=(SELECT user_id FROM supla_accessid WHERE id=access_id)');
        $this->addSql('ALTER TABLE supla_client CHANGE COLUMN user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE supla_client ADD CONSTRAINT FK_5430007FA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id)');
        $this->addSql('CREATE INDEX IDX_5430007FA76ED395 ON supla_client (user_id)');
        $this->addSql('ALTER TABLE supla_client ADD auth_key VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_client DROP FOREIGN KEY FK_5430007F4FEA67CF');
        $this->addSql('ALTER TABLE supla_client CHANGE access_id access_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_client ADD CONSTRAINT FK_5430007F4FEA67CF FOREIGN KEY (access_id) REFERENCES supla_accessid (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE supla_iodevice ADD auth_key VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_user ADD iodevice_reg_enabled DATETIME DEFAULT NULL, ADD client_reg_enabled DATETIME DEFAULT NULL');
        $this->addSql('DELETE c1 FROM supla_client c1, supla_client c2 WHERE c1.id < c2.id AND c1.guid = c2.guid');
        $this->addSql('DROP INDEX UNIQUE_CLIENTAPP ON supla_client');
        $this->addSql('CREATE UNIQUE INDEX UNIQUE_CLIENTAPP ON supla_client (user_id, guid)');
        $this->addSql('CREATE INDEX client_reg_enabled_idx ON supla_user (client_reg_enabled)');
        $this->addSql('CREATE INDEX iodevice_reg_enabled_idx ON supla_user (iodevice_reg_enabled)');
        $this->addSql('UPDATE supla_iodevice SET original_location_id = location_id WHERE IFNULL(original_location_id, 0) = 0');

        $this->addSql('ALTER TABLE supla_client ADD reg_ipv4_tmp INT UNSIGNED');
        $this->addSql('UPDATE supla_client SET reg_ipv4_tmp = reg_ipv4 WHERE reg_ipv4 >= 0');
        $this->addSql('UPDATE supla_client SET reg_ipv4_tmp = CONVERT(reg_ipv4 & 0x00000000FFFFFFFF, UNSIGNED INT)  WHERE reg_ipv4 < 0');
        $this->addSql('ALTER TABLE supla_client CHANGE reg_ipv4 reg_ipv4 INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE supla_client SET reg_ipv4 = reg_ipv4_tmp');
        $this->addSql('ALTER TABLE supla_client DROP reg_ipv4_tmp');

        $this->addSql('ALTER TABLE supla_client ADD last_access_ipv4_tmp INT UNSIGNED');
        $this->addSql('UPDATE supla_client SET last_access_ipv4_tmp = last_access_ipv4 WHERE last_access_ipv4 >= 0');
        $this->addSql('UPDATE supla_client SET last_access_ipv4_tmp = CONVERT(last_access_ipv4 & 0x00000000FFFFFFFF, UNSIGNED INT)  WHERE last_access_ipv4 < 0');
        $this->addSql('ALTER TABLE supla_client CHANGE last_access_ipv4 last_access_ipv4 INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE supla_client SET last_access_ipv4 = last_access_ipv4_tmp');
        $this->addSql('ALTER TABLE supla_client DROP last_access_ipv4_tmp');

        $this->addSql('ALTER TABLE supla_iodevice ADD reg_ipv4_tmp  INT UNSIGNED');
        $this->addSql('UPDATE supla_iodevice SET reg_ipv4_tmp = reg_ipv4 WHERE reg_ipv4 >= 0');
        $this->addSql('UPDATE supla_iodevice SET reg_ipv4_tmp = CONVERT(reg_ipv4 & 0x00000000FFFFFFFF, UNSIGNED INT)  WHERE reg_ipv4 < 0');
        $this->addSql(' ALTER TABLE supla_iodevice CHANGE reg_ipv4 reg_ipv4 INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE supla_iodevice SET reg_ipv4 = reg_ipv4_tmp');
        $this->addSql('ALTER TABLE supla_iodevice DROP reg_ipv4_tmp');

        $this->addSql('ALTER TABLE supla_iodevice ADD last_ipv4_tmp INT UNSIGNED');
        $this->addSql('UPDATE supla_iodevice SET last_ipv4_tmp = last_ipv4 WHERE last_ipv4 >= 0');
        $this->addSql('UPDATE supla_iodevice SET last_ipv4_tmp = CONVERT(last_ipv4 & 0x00000000FFFFFFFF, UNSIGNED INT)  WHERE last_ipv4 < 0');
        $this->addSql('ALTER TABLE supla_iodevice CHANGE last_ipv4 last_ipv4 INT UNSIGNED DEFAULT NULL');
        $this->addSql('UPDATE supla_iodevice SET last_ipv4 = last_ipv4_tmp');
        $this->addSql('ALTER TABLE supla_iodevice DROP last_ipv4_tmp');

        $this->addSql('UPDATE supla_client SET reg_ipv4 = ( ((reg_ipv4 & 0xFF) << 24) | ((reg_ipv4 & 0xFF00) << 8) | ((reg_ipv4 & 0xFF0000) >> 8) | ((reg_ipv4 & 0xFF000000) >> 24) )');
        $this->addSql('UPDATE supla_client SET last_access_ipv4 = ( ((last_access_ipv4 & 0xFF) << 24) | ((last_access_ipv4 & 0xFF00) << 8) | ((last_access_ipv4 & 0xFF0000) >> 8) | ((last_access_ipv4 & 0xFF000000) >> 24) )');

        $this->addSql('UPDATE supla_iodevice SET reg_ipv4 = ( ((reg_ipv4 & 0xFF) << 24) | ((reg_ipv4 & 0xFF00) << 8) | ((reg_ipv4 & 0xFF0000) >> 8) | ((reg_ipv4 & 0xFF000000) >> 24) )');
        $this->addSql('UPDATE supla_iodevice SET last_ipv4 = ( ((last_ipv4 & 0xFF) << 24) | ((last_ipv4 & 0xFF00) << 8) | ((last_ipv4 & 0xFF0000) >> 8) | ((last_ipv4 & 0xFF000000) >> 24) )');

        $this->addSql('UPDATE `supla_temperature_log` SET date = CONVERT_TZ(date, \'SYSTEM\', \'+00:00\')');
        $this->addSql('UPDATE `supla_temphumidity_log` SET date = CONVERT_TZ(date, \'SYSTEM\', \'+00:00\')');

        $this->addSql('UPDATE `supla_user` SET `iodevice_reg_enabled` = UTC_TIMESTAMP() + INTERVAL 1 DAY,`client_reg_enabled` = UTC_TIMESTAMP() + INTERVAL 1 DAY');
    }
}
