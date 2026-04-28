<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Initial DB structure from SUPLA-Cloud v1.1.0.
 */
class Version20170101000000 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql("ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_unicode_ci");

        $userTableExists = !!$this->getConnection()->fetchOne('SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "supla_user";');

        if (!$userTableExists) {
            $this->addSql(<<<INITIAL_SCHEMA
    CREATE TABLE supla_accessid (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, password VARCHAR(32) NOT NULL, caption VARCHAR(100) DEFAULT NULL, enabled TINYINT(1) NOT NULL, INDEX IDX_A5549B6CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    CREATE TABLE supla_client (id INT AUTO_INCREMENT NOT NULL, access_id INT NOT NULL, guid BINARY(16) NOT NULL, name VARCHAR(100) DEFAULT NULL, enabled TINYINT(1) NOT NULL, reg_ipv4 INT UNSIGNED NOT NULL, reg_date DATETIME NOT NULL, last_access_ipv4 INT UNSIGNED NOT NULL, last_access_date DATETIME NOT NULL, software_version VARCHAR(20) NOT NULL, protocol_version INT NOT NULL, INDEX IDX_5430007F4FEA67CF (access_id), UNIQUE INDEX UNIQUE_CLIENTAPP (id, guid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    CREATE TABLE supla_iodevice (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, user_id INT NOT NULL, guid BINARY(16) NOT NULL, name VARCHAR(100) DEFAULT NULL, enabled TINYINT(1) NOT NULL, comment VARCHAR(200) DEFAULT NULL, reg_date DATETIME NOT NULL, reg_ipv4 INT UNSIGNED NOT NULL, last_connected DATETIME DEFAULT NULL, last_ipv4 INT DEFAULT NULL, software_version VARCHAR(10) NOT NULL, protocol_version INT NOT NULL, UNIQUE INDEX UNIQ_793D49D2B6FCFB2 (guid), INDEX IDX_793D49D64D218E (location_id), INDEX IDX_793D49DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    CREATE TABLE supla_dev_channel (id INT AUTO_INCREMENT NOT NULL, iodevice_id INT NOT NULL, user_id INT NOT NULL, channel_number INT NOT NULL, caption VARCHAR(100) DEFAULT NULL, type INT NOT NULL, func INT NOT NULL, flist INT DEFAULT NULL, param1 INT NOT NULL, param2 INT NOT NULL, param3 INT NOT NULL, INDEX IDX_81E928C9125F95D6 (iodevice_id), INDEX IDX_81E928C9A76ED395 (user_id), UNIQUE INDEX UNIQUE_CHANNEL (iodevice_id, channel_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    CREATE TABLE supla_location (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, password VARCHAR(32) NOT NULL, caption VARCHAR(100) NOT NULL, enabled TINYINT(1) NOT NULL, INDEX IDX_3698128EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    CREATE TABLE supla_rel_aidloc (location_id INT NOT NULL, access_id INT NOT NULL, INDEX IDX_2B15904164D218E (location_id), INDEX IDX_2B1590414FEA67CF (access_id), PRIMARY KEY(location_id, access_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    CREATE TABLE supla_temperature_log (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, date DATETIME NOT NULL, temperature NUMERIC(8, 4) NOT NULL, INDEX channel_id_idx (channel_id), INDEX date_idx (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    CREATE TABLE supla_temphumidity_log (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, date DATETIME NOT NULL, temperature NUMERIC(8, 4) NOT NULL, humidity NUMERIC(8, 4) NOT NULL, INDEX channel_id_idx (channel_id), INDEX date_idx (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    CREATE TABLE supla_user (id INT AUTO_INCREMENT NOT NULL, salt VARCHAR(32) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(64) NOT NULL, enabled TINYINT(1) NOT NULL, reg_date DATETIME NOT NULL, last_login DATETIME DEFAULT NULL, last_ipv4 INT DEFAULT NULL, current_login DATETIME DEFAULT NULL, current_ipv4 INT DEFAULT NULL, token VARCHAR(255) NOT NULL, password_requested_at DATETIME DEFAULT NULL, limit_aid INT NOT NULL, limit_loc INT NOT NULL, limit_iodev INT NOT NULL, limit_client INT NOT NULL, UNIQUE INDEX UNIQ_71BAEAC6E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    ALTER TABLE supla_accessid ADD CONSTRAINT FK_A5549B6CA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id);
    ALTER TABLE supla_client ADD CONSTRAINT FK_5430007F4FEA67CF FOREIGN KEY (access_id) REFERENCES supla_accessid (id);
    ALTER TABLE supla_iodevice ADD CONSTRAINT FK_793D49D64D218E FOREIGN KEY (location_id) REFERENCES supla_location (id);
    ALTER TABLE supla_iodevice ADD CONSTRAINT FK_793D49DA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id);
    ALTER TABLE supla_dev_channel ADD CONSTRAINT FK_81E928C9125F95D6 FOREIGN KEY (iodevice_id) REFERENCES supla_iodevice (id);
    ALTER TABLE supla_dev_channel ADD CONSTRAINT FK_81E928C9A76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id);
    ALTER TABLE supla_location ADD CONSTRAINT FK_3698128EA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id);
    ALTER TABLE supla_rel_aidloc ADD CONSTRAINT FK_2B15904164D218E FOREIGN KEY (location_id) REFERENCES supla_location (id);
    ALTER TABLE supla_rel_aidloc ADD CONSTRAINT FK_2B1590414FEA67CF FOREIGN KEY (access_id) REFERENCES supla_accessid (id);
INITIAL_SCHEMA
            );
        }
    }
}
