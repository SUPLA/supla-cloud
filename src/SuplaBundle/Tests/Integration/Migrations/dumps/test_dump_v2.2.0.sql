/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `esp_update` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `device_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `platform` tinyint(4) NOT NULL,
  `latest_software_version` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fparam1` int(11) NOT NULL,
  `fparam2` int(11) NOT NULL,
  `protocols` tinyint(4) NOT NULL,
  `host` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `port` int(11) NOT NULL,
  `path` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `device_name` (`device_name`),
  KEY `latest_software_version` (`latest_software_version`),
  KEY `fparam1` (`fparam1`),
  KEY `fparam2` (`fparam2`),
  KEY `platform` (`platform`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40000 ALTER TABLE `esp_update` DISABLE KEYS */;
/*!40000 ALTER TABLE `esp_update` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `esp_update_log` (
  `date` datetime NOT NULL,
  `device_id` int(11) NOT NULL,
  `platform` tinyint(4) NOT NULL,
  `fparam1` int(11) NOT NULL,
  `fparam2` int(11) NOT NULL,
  `fparam3` int(11) NOT NULL,
  `fparam4` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40000 ALTER TABLE `esp_update_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `esp_update_log` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` (`version`) VALUES
	('20170101000000'),
	('20170414101854'),
	('20170612204116'),
	('20170818114139'),
	('20171013140904'),
	('20171208222022'),
	('20171210105120'),
	('20180108224520'),
	('20180113234138'),
	('20180116184415'),
	('20180203231115'),
	('20180208145738'),
	('20180224184251'),
	('20180324222844'),
	('20180326134725'),
	('20180403175932'),
	('20180403203101'),
	('20180403211558'),
	('20180411202101'),
	('20180411203913'),
	('20180416201401'),
	('20180423121539'),
	('20180507095139');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_accessid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `caption` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A5549B6CA76ED395` (`user_id`),
  CONSTRAINT `FK_A5549B6CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_accessid` DISABLE KEYS */;
INSERT INTO `supla_accessid` (`id`, `user_id`, `password`, `caption`, `enabled`) VALUES
	(1, 1, 'd5e0ff42', 'Access Identifier #1', 1),
	(2, 1, '7fc1f4d7', 'Wspólny', 1),
	(3, 1, '1b6aaff1', 'Dzieci', 1);
/*!40000 ALTER TABLE `supla_accessid` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `event` smallint(5) unsigned NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `ipv4` int(10) unsigned DEFAULT NULL,
  `text_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `int_param` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EFE348F4A76ED395` (`user_id`),
  KEY `supla_audit_event_idx` (`event`),
  KEY `supla_audit_ipv4_idx` (`ipv4`),
  KEY `supla_audit_created_at_idx` (`created_at`),
  CONSTRAINT `FK_EFE348F4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_audit` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_id` int(11) DEFAULT NULL,
  `guid` varbinary(16) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `reg_ipv4` int(10) unsigned DEFAULT NULL,
  `reg_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `last_access_ipv4` int(10) unsigned DEFAULT NULL,
  `last_access_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `software_version` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `protocol_version` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auth_key` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `caption` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `disable_after_date` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_CLIENTAPP` (`user_id`,`guid`),
  KEY `IDX_5430007F4FEA67CF` (`access_id`),
  KEY `IDX_5430007FA76ED395` (`user_id`),
  CONSTRAINT `FK_5430007F4FEA67CF` FOREIGN KEY (`access_id`) REFERENCES `supla_accessid` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5430007FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_client` DISABLE KEYS */;
INSERT INTO `supla_client` (`id`, `access_id`, `guid`, `name`, `enabled`, `reg_ipv4`, `reg_date`, `last_access_ipv4`, `last_access_date`, `software_version`, `protocol_version`, `user_id`, `auth_key`, `caption`, `disable_after_date`) VALUES
	(1, 3, _binary 0x39313833343933, 'HTC One M8', 0, 434198927, '2018-04-18 10:05:33', 1970269087, '2018-06-10 11:01:52', '1.50', 66, 1, NULL, NULL, NULL),
	(2, 2, _binary 0x32323130353633, 'iPhone 6s', 1, 932163803, '2018-04-18 21:24:41', 925696667, '2018-06-15 08:11:47', '1.34', 60, 1, NULL, NULL, NULL),
	(3, 3, _binary 0x32363432343437, 'Nokia 3310', 1, 18032074, '2018-05-21 17:02:29', 1536664980, '2018-06-15 12:43:40', '1.45', 59, 1, NULL, NULL, NULL),
	(4, NULL, _binary 0x373333313339, 'Samsung Galaxy Tab S2', 0, 1652207132, '2018-05-18 05:35:22', 1664248719, '2018-06-13 22:01:02', '1.87', 3, 1, NULL, NULL, NULL),
	(5, 2, _binary 0x37373237363430, 'Apple iPad', 1, 1480037901, '2018-04-23 05:10:50', 114146799, '2018-06-16 04:54:08', '1.68', 22, 1, NULL, NULL, NULL);
/*!40000 ALTER TABLE `supla_client` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_dev_channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iodevice_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `channel_number` int(11) NOT NULL,
  `caption` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` int(11) NOT NULL,
  `func` int(11) NOT NULL,
  `flist` int(11) DEFAULT NULL,
  `param1` int(11) NOT NULL,
  `param2` int(11) NOT NULL,
  `param3` int(11) NOT NULL,
  `alt_icon` int(11) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `location_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_CHANNEL` (`iodevice_id`,`channel_number`),
  KEY `IDX_81E928C9125F95D6` (`iodevice_id`),
  KEY `IDX_81E928C9A76ED395` (`user_id`),
  KEY `IDX_81E928C964D218E` (`location_id`),
  CONSTRAINT `FK_81E928C9125F95D6` FOREIGN KEY (`iodevice_id`) REFERENCES `supla_iodevice` (`id`),
  CONSTRAINT `FK_81E928C964D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
  CONSTRAINT `FK_81E928C9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_dev_channel` DISABLE KEYS */;
INSERT INTO `supla_dev_channel` (`id`, `iodevice_id`, `user_id`, `channel_number`, `caption`, `type`, `func`, `flist`, `param1`, `param2`, `param3`, `alt_icon`, `hidden`, `location_id`) VALUES
	(1, 1, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, 0, 0, NULL),
	(2, 1, 1, 1, NULL, 3000, 40, NULL, 0, 0, 0, 0, 0, NULL),
	(3, 2, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, 0, 0, NULL),
	(4, 2, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, 0, 0, NULL),
	(5, 2, 1, 2, NULL, 2900, 20, 15, 0, 0, 0, 0, 0, NULL),
	(6, 2, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, 0, 0, NULL),
	(7, 2, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, 0, 0, NULL),
	(8, 2, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, 0, 0, NULL),
	(9, 2, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, 0, 0, NULL),
	(10, 3, 1, 0, NULL, 4010, 200, NULL, 0, 0, 0, 0, 0, NULL),
	(11, 3, 1, 1, NULL, 4010, 190, NULL, 0, 0, 0, 0, 0, NULL),
	(12, 4, 1, 0, NULL, 1000, 50, NULL, 0, 0, 0, 0, 0, NULL),
	(13, 4, 1, 1, NULL, 1000, 60, NULL, 0, 0, 0, 0, 0, NULL),
	(14, 4, 1, 2, NULL, 1000, 70, NULL, 0, 0, 0, 0, 0, NULL),
	(15, 4, 1, 3, NULL, 1000, 100, NULL, 0, 0, 0, 0, 0, NULL),
	(16, 4, 1, 4, NULL, 1000, 80, NULL, 0, 0, 0, 0, 0, NULL),
	(17, 4, 1, 5, NULL, 1000, 120, NULL, 0, 0, 0, 0, 0, NULL),
	(18, 4, 1, 6, NULL, 1000, 230, NULL, 0, 0, 0, 0, 0, NULL),
	(19, 4, 1, 7, NULL, 1000, 240, NULL, 0, 0, 0, 0, 0, NULL),
	(20, 4, 1, 8, NULL, 1010, 50, NULL, 0, 0, 0, 0, 0, NULL),
	(21, 4, 1, 9, NULL, 1010, 60, NULL, 0, 0, 0, 0, 0, NULL),
	(22, 4, 1, 10, NULL, 1010, 70, NULL, 0, 0, 0, 0, 0, NULL),
	(23, 4, 1, 11, NULL, 1010, 100, NULL, 0, 0, 0, 0, 0, NULL),
	(24, 4, 1, 12, NULL, 1010, 80, NULL, 0, 0, 0, 0, 0, NULL),
	(25, 4, 1, 13, NULL, 1010, 120, NULL, 0, 0, 0, 0, 0, NULL),
	(26, 4, 1, 14, NULL, 1010, 230, NULL, 0, 0, 0, 0, 0, NULL),
	(27, 4, 1, 15, NULL, 1010, 240, NULL, 0, 0, 0, 0, 0, NULL),
	(28, 4, 1, 16, NULL, 1020, 210, NULL, 0, 0, 0, 0, 0, NULL),
	(29, 4, 1, 17, NULL, 1020, 220, NULL, 0, 0, 0, 0, 0, NULL),
	(30, 4, 1, 18, NULL, 2000, 10, NULL, 0, 0, 0, 0, 0, NULL),
	(31, 4, 1, 19, NULL, 2000, 20, NULL, 0, 0, 0, 0, 0, NULL),
	(32, 4, 1, 20, NULL, 2000, 30, NULL, 0, 0, 0, 0, 0, NULL),
	(33, 4, 1, 21, NULL, 2000, 90, NULL, 0, 0, 0, 0, 0, NULL),
	(34, 4, 1, 22, NULL, 2010, 10, NULL, 0, 0, 0, 0, 0, NULL),
	(35, 4, 1, 23, NULL, 2010, 20, NULL, 0, 0, 0, 0, 0, NULL),
	(36, 4, 1, 24, NULL, 2010, 30, NULL, 0, 0, 0, 0, 0, NULL),
	(37, 4, 1, 25, NULL, 2010, 90, NULL, 0, 0, 0, 0, 0, NULL),
	(38, 4, 1, 26, NULL, 2010, 130, NULL, 0, 0, 0, 0, 0, NULL),
	(39, 4, 1, 27, NULL, 2010, 140, NULL, 0, 0, 0, 0, 0, NULL),
	(40, 4, 1, 28, NULL, 2010, 300, NULL, 0, 0, 0, 0, 0, NULL),
	(41, 4, 1, 29, NULL, 2020, 10, NULL, 0, 0, 0, 0, 0, NULL),
	(42, 4, 1, 30, NULL, 2020, 20, NULL, 0, 0, 0, 0, 0, NULL),
	(43, 4, 1, 31, NULL, 2020, 30, NULL, 0, 0, 0, 0, 0, NULL),
	(44, 4, 1, 32, NULL, 2020, 90, NULL, 0, 0, 0, 0, 0, NULL),
	(45, 4, 1, 33, NULL, 2020, 130, NULL, 0, 0, 0, 0, 0, NULL),
	(46, 4, 1, 34, NULL, 2020, 140, NULL, 0, 0, 0, 0, 0, NULL),
	(47, 4, 1, 35, NULL, 2020, 110, NULL, 0, 0, 0, 0, 0, NULL),
	(48, 4, 1, 36, NULL, 2020, 300, NULL, 0, 0, 0, 0, 0, NULL),
	(49, 4, 1, 37, NULL, 3000, 40, NULL, 0, 0, 0, 0, 0, NULL),
	(50, 4, 1, 38, NULL, 3010, 45, NULL, 0, 0, 0, 0, 0, NULL),
	(51, 4, 1, 39, NULL, 3022, 45, NULL, 0, 0, 0, 0, 0, NULL),
	(52, 4, 1, 40, NULL, 3020, 45, NULL, 0, 0, 0, 0, 0, NULL),
	(53, 4, 1, 41, NULL, 3032, 45, NULL, 0, 0, 0, 0, 0, NULL),
	(54, 4, 1, 42, NULL, 3030, 45, NULL, 0, 0, 0, 0, 0, NULL),
	(55, 4, 1, 43, NULL, 3034, 40, NULL, 0, 0, 0, 0, 0, NULL),
	(56, 4, 1, 44, NULL, 3036, 42, NULL, 0, 0, 0, 0, 0, NULL),
	(57, 4, 1, 45, NULL, 3038, 45, NULL, 0, 0, 0, 0, 0, NULL),
	(58, 4, 1, 46, NULL, 3042, 250, NULL, 0, 0, 0, 0, 0, NULL),
	(59, 4, 1, 47, NULL, 3044, 260, NULL, 0, 0, 0, 0, 0, NULL),
	(60, 4, 1, 48, NULL, 3048, 270, NULL, 0, 0, 0, 0, 0, NULL),
	(61, 4, 1, 49, NULL, 3050, 280, NULL, 0, 0, 0, 0, 0, NULL),
	(62, 4, 1, 50, NULL, 3100, 290, NULL, 0, 0, 0, 0, 0, NULL),
	(63, 4, 1, 51, NULL, 4000, 180, NULL, 0, 0, 0, 0, 0, NULL),
	(64, 4, 1, 52, NULL, 4010, 190, NULL, 0, 0, 0, 0, 0, NULL),
	(65, 4, 1, 53, NULL, 4020, 200, NULL, 0, 0, 0, 0, 0, NULL);
/*!40000 ALTER TABLE `supla_dev_channel` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_dev_channel_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `caption` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `func` int(11) NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL,
  `alt_icon` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6B2EFCE5A76ED395` (`user_id`),
  KEY `IDX_6B2EFCE564D218E` (`location_id`),
  CONSTRAINT `FK_6B2EFCE564D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
  CONSTRAINT `FK_6B2EFCE5A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_dev_channel_group` DISABLE KEYS */;
INSERT INTO `supla_dev_channel_group` (`id`, `user_id`, `caption`, `func`, `hidden`, `location_id`, `alt_icon`) VALUES
	(1, 1, 'Światła na parterze', 140, 0, 3, 0);
/*!40000 ALTER TABLE `supla_dev_channel_group` ENABLE KEYS */;

DELIMITER //
CREATE PROCEDURE `supla_get_device_firmware_url`(IN `device_id` INT, IN `platform` TINYINT, IN `fparam1` INT, IN `fparam2` INT, IN `fparam3` INT, IN `fparam4` INT, OUT `protocols` TINYINT, OUT `host` VARCHAR(100), OUT `port` INT, OUT `path` VARCHAR(100))
    NO SQL
BEGIN
                SET @protocols = 0;
                SET @host = '';
                SET @port = 0;
                SET @path = '';

                SET @fparam1 = fparam1;
                SET @fparam2 = fparam2;
                SET @platform = platform;
                SET @device_id = device_id;

                INSERT INTO `esp_update_log`(`date`, `device_id`, `platform`, `fparam1`, `fparam2`, `fparam3`, `fparam4`) VALUES (NOW(),device_id,platform,fparam1,fparam2,fparam3,fparam4);

                SELECT u.`protocols`, u.`host`, u.`port`, u.`path` INTO @protocols, @host, @port, @path FROM supla_iodevice AS d, esp_update AS u WHERE d.id = @device_id AND u.`platform` = @platform AND u.`fparam1` = @fparam1 AND u.`fparam2` = @fparam2 AND u.`device_name` = d.name AND u.`latest_software_version` != d.`software_version` AND ( u.`device_id` = 0 OR u.`device_id` = device_id ) LIMIT 1;

                SET protocols = @protocols;
                SET host = @host;
                SET port = @port;
                SET path = @path;
            END//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `supla_iodevice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guid` varbinary(16) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `comment` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reg_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `reg_ipv4` int(10) unsigned DEFAULT NULL,
  `last_connected` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `last_ipv4` int(10) unsigned DEFAULT NULL,
  `software_version` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `protocol_version` int(11) NOT NULL,
  `original_location_id` int(11) DEFAULT NULL,
  `auth_key` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_793D49D2B6FCFB2` (`guid`),
  KEY `IDX_793D49D64D218E` (`location_id`),
  KEY `IDX_793D49DA76ED395` (`user_id`),
  KEY `IDX_793D49DF142C1A4` (`original_location_id`),
  CONSTRAINT `FK_793D49D64D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
  CONSTRAINT `FK_793D49DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`),
  CONSTRAINT `FK_793D49DF142C1A4` FOREIGN KEY (`original_location_id`) REFERENCES `supla_location` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_iodevice` DISABLE KEYS */;
INSERT INTO `supla_iodevice` (`id`, `location_id`, `user_id`, `guid`, `name`, `enabled`, `comment`, `reg_date`, `reg_ipv4`, `last_connected`, `last_ipv4`, `software_version`, `protocol_version`, `original_location_id`, `auth_key`) VALUES
	(1, 3, 1, _binary 0x33363131363232, 'SONOFF-DS', 1, NULL, '2018-06-17 08:45:18', 6448129, '2018-06-17 08:45:18', NULL, '2.18', 2, NULL, NULL),
	(2, 4, 1, _binary 0x33373732323238, 'UNI-MODULE', 1, NULL, '2018-06-17 08:45:18', 8055537, '2018-06-17 08:45:18', NULL, '2.29', 2, NULL, NULL),
	(3, 2, 1, _binary 0x33373335363234, 'RGB-801', 1, NULL, '2018-06-17 08:45:18', 2177002, '2018-06-17 08:45:18', NULL, '2.27', 2, NULL, NULL),
	(4, 3, 1, _binary 0x37333231383133, 'ALL-IN-ONE MEGA DEVICE', 1, NULL, '2018-06-17 08:45:18', 9495492, '2018-06-17 08:45:18', NULL, '2.38', 2, NULL, NULL);
/*!40000 ALTER TABLE `supla_iodevice` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `caption` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3698128EA76ED395` (`user_id`),
  CONSTRAINT `FK_3698128EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_location` DISABLE KEYS */;
INSERT INTO `supla_location` (`id`, `user_id`, `password`, `caption`, `enabled`) VALUES
	(1, 1, 'a9eb', 'Location #1', 1),
	(2, 1, 'fb2b', 'Sypialnia', 1),
	(3, 1, '436f', 'Na zewnątrz', 1),
	(4, 1, '80cc', 'Garaż', 1);
/*!40000 ALTER TABLE `supla_location` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_oauth_access_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2402564B5F37A13B` (`token`),
  KEY `IDX_2402564B19EB6921` (`client_id`),
  KEY `IDX_2402564BA76ED395` (`user_id`),
  CONSTRAINT `FK_2402564B19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`),
  CONSTRAINT `FK_2402564BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_oauth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_oauth_access_tokens` DISABLE KEYS */;
INSERT INTO `supla_oauth_access_tokens` (`id`, `client_id`, `user_id`, `token`, `expires_at`, `scope`) VALUES
	(1, 1, 1, '0123456789012345678901234567890123456789', 2051218800, 'restapi');
/*!40000 ALTER TABLE `supla_oauth_access_tokens` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_oauth_auth_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uri` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_48E00E5D5F37A13B` (`token`),
  KEY `IDX_48E00E5D19EB6921` (`client_id`),
  KEY `IDX_48E00E5DA76ED395` (`user_id`),
  CONSTRAINT `FK_48E00E5D19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`),
  CONSTRAINT `FK_48E00E5DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_oauth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_oauth_auth_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_oauth_auth_codes` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_oauth_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `random_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uris` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `secret` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allowed_grant_types` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4035AD80727ACA70` (`parent_id`),
  CONSTRAINT `FK_4035AD80727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_oauth_clients` DISABLE KEYS */;
INSERT INTO `supla_oauth_clients` (`id`, `parent_id`, `random_id`, `redirect_uris`, `secret`, `allowed_grant_types`, `type`) VALUES
	(1, 1, '69uz20cdx6w4wg8sk0kow4ckwgsgco08s0gccwgwc4sw4kck80', 'a:1:{i:0;s:1:"/";}', '661l0f9ql7wokkk84wg888gww80w8gko4cs0gcc4gs44gooowg', 'a:1:{i:0;s:8:"password";}', 0);
/*!40000 ALTER TABLE `supla_oauth_clients` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_oauth_refresh_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B809538C5F37A13B` (`token`),
  KEY `IDX_B809538C19EB6921` (`client_id`),
  KEY `IDX_B809538CA76ED395` (`user_id`),
  CONSTRAINT `FK_B809538C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`),
  CONSTRAINT `FK_B809538CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_oauth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_oauth_refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_oauth_refresh_tokens` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_oauth_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `accessId_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6C098C52727ACA70` (`parent_id`),
  KEY `IDX_6C098C521579A74E` (`accessId_id`),
  CONSTRAINT `FK_6C098C521579A74E` FOREIGN KEY (`accessId_id`) REFERENCES `supla_accessid` (`id`),
  CONSTRAINT `FK_6C098C52727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_oauth_user` DISABLE KEYS */;
INSERT INTO `supla_oauth_user` (`id`, `parent_id`, `password`, `enabled`, `accessId_id`) VALUES
	(1, 1, '$2y$04$0ydWylMOTNDnSA/GNhl.nulSldoCVbKCo4AyT3wrXnZwncnA2iqaa', 1, NULL);
/*!40000 ALTER TABLE `supla_oauth_user` ENABLE KEYS */;

DELIMITER //
CREATE PROCEDURE `supla_on_channeladded`(IN `_device_id` INT, IN `_channel_id` INT)
    NO SQL
BEGIN
                SET @type = NULL;
                SELECT type INTO @type FROM supla_dev_channel WHERE `func` = 0 AND id = _channel_id;
                IF @type = 3000 THEN
                    UPDATE supla_dev_channel SET `func` = 40 WHERE id = _channel_id;
                END IF;
            END//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE `supla_on_newclient`(IN `_client_id` INT)
    NO SQL
BEGIN
			END//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE `supla_on_newdevice`(IN `_device_id` INT)
    MODIFIES SQL DATA
BEGIN
            END//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `supla_rel_aidloc` (
  `location_id` int(11) NOT NULL,
  `access_id` int(11) NOT NULL,
  PRIMARY KEY (`location_id`,`access_id`),
  KEY `IDX_2B15904164D218E` (`location_id`),
  KEY `IDX_2B1590414FEA67CF` (`access_id`),
  CONSTRAINT `FK_2B1590414FEA67CF` FOREIGN KEY (`access_id`) REFERENCES `supla_accessid` (`id`),
  CONSTRAINT `FK_2B15904164D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_rel_aidloc` DISABLE KEYS */;
INSERT INTO `supla_rel_aidloc` (`location_id`, `access_id`) VALUES
	(1, 1),
	(2, 1),
	(3, 1),
	(4, 1);
/*!40000 ALTER TABLE `supla_rel_aidloc` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_rel_cg` (
  `channel_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`channel_id`),
  KEY `IDX_BE981CD772F5A1AA` (`channel_id`),
  KEY `IDX_BE981CD7FE54D947` (`group_id`),
  CONSTRAINT `FK_BE981CD772F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`),
  CONSTRAINT `FK_BE981CD7FE54D947` FOREIGN KEY (`group_id`) REFERENCES `supla_dev_channel_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_rel_cg` DISABLE KEYS */;
INSERT INTO `supla_rel_cg` (`channel_id`, `group_id`) VALUES
	(1, 1),
	(3, 1);
/*!40000 ALTER TABLE `supla_rel_cg` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `time_expression` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `action` int(11) NOT NULL,
  `action_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mode` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `date_start` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `date_end` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `enabled` tinyint(1) NOT NULL,
  `next_calculation_date` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `caption` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `retry` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDX_323E8ABEA76ED395` (`user_id`),
  KEY `IDX_323E8ABE72F5A1AA` (`channel_id`),
  KEY `next_calculation_date_idx` (`next_calculation_date`),
  KEY `enabled_idx` (`enabled`),
  KEY `date_start_idx` (`date_start`),
  CONSTRAINT `FK_323E8ABE72F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`),
  CONSTRAINT `FK_323E8ABEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_schedule` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_scheduled_executions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `planned_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `fetched_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `retry_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `retry_count` int(11) DEFAULT NULL,
  `result_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `consumed` tinyint(1) NOT NULL,
  `result` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FB21DBDCA40BC2D5` (`schedule_id`),
  KEY `result_idx` (`result`),
  KEY `result_timestamp_idx` (`result_timestamp`),
  KEY `planned_timestamp_idx` (`planned_timestamp`),
  KEY `retry_timestamp_idx` (`retry_timestamp`),
  KEY `fetched_timestamp_idx` (`fetched_timestamp`),
  KEY `consumed_idx` (`consumed`),
  CONSTRAINT `FK_FB21DBDCA40BC2D5` FOREIGN KEY (`schedule_id`) REFERENCES `supla_schedule` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_scheduled_executions` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_scheduled_executions` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_temperature_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `temperature` decimal(8,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id_idx` (`channel_id`),
  KEY `date_idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_temperature_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_temperature_log` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_temphumidity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `temperature` decimal(8,4) NOT NULL,
  `humidity` decimal(8,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id_idx` (`channel_id`),
  KEY `date_idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_temphumidity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_temphumidity_log` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `supla_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salt` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `reg_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_requested_at` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `limit_aid` int(11) NOT NULL,
  `limit_loc` int(11) NOT NULL,
  `limit_iodev` int(11) NOT NULL,
  `limit_client` int(11) NOT NULL,
  `timezone` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `limit_schedule` int(11) NOT NULL DEFAULT '20',
  `legacy_password` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `iodevice_reg_enabled` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `client_reg_enabled` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `limit_channel_group` int(11) NOT NULL DEFAULT '20',
  `limit_channel_per_group` int(11) NOT NULL DEFAULT '10',
  `rules_agreement` tinyint(1) NOT NULL DEFAULT '0',
  `cookies_agreement` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_71BAEAC6E7927C74` (`email`),
  KEY `client_reg_enabled_idx` (`client_reg_enabled`),
  KEY `iodevice_reg_enabled_idx` (`iodevice_reg_enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40000 ALTER TABLE `supla_user` DISABLE KEYS */;
INSERT INTO `supla_user` (`id`, `salt`, `email`, `password`, `enabled`, `reg_date`, `token`, `password_requested_at`, `limit_aid`, `limit_loc`, `limit_iodev`, `limit_client`, `timezone`, `limit_schedule`, `legacy_password`, `iodevice_reg_enabled`, `client_reg_enabled`, `limit_channel_group`, `limit_channel_per_group`, `rules_agreement`, `cookies_agreement`) VALUES
	(1, '44fjlg6ztgo4s8sowsossoogsgcc0kk', 'user@supla.org', '$2y$13$tIWgYzuWB90Z4jB34JfZBeHHYbzs1XExgtt7DCasVTwgktkko1QIe', 1, '2018-06-17 08:45:13', '', NULL, 10, 10, 100, 200, 'Europe/Berlin', 20, NULL, '2018-06-24 08:45:16', '2018-06-24 08:45:16', 20, 10, 1, 1);
/*!40000 ALTER TABLE `supla_user` ENABLE KEYS */;

CREATE TABLE `supla_v_client` (
	`id` INT(11) NOT NULL,
	`access_id` INT(11) NULL,
	`guid` VARBINARY(16) NOT NULL,
	`name` VARCHAR(100) NULL COLLATE 'utf8_unicode_ci',
	`reg_ipv4` INT(10) UNSIGNED NULL,
	`reg_date` DATETIME NOT NULL COMMENT '(DC2Type:utcdatetime)',
	`last_access_ipv4` INT(10) UNSIGNED NULL,
	`last_access_date` DATETIME NOT NULL COMMENT '(DC2Type:utcdatetime)',
	`software_version` VARCHAR(20) NOT NULL COLLATE 'utf8_unicode_ci',
	`protocol_version` INT(11) NOT NULL,
	`enabled` TINYINT(1) NOT NULL,
	`user_id` INT(11) NOT NULL
) ENGINE=MyISAM;

CREATE TABLE `supla_v_client_channel` (
	`id` INT(11) NOT NULL,
	`type` INT(11) NOT NULL,
	`func` INT(11) NOT NULL,
	`param1` INT(11) NOT NULL,
	`param2` INT(11) NOT NULL,
	`caption` VARCHAR(100) NULL COLLATE 'utf8_unicode_ci',
	`param3` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`channel_number` INT(11) NOT NULL,
	`iodevice_id` INT(11) NOT NULL,
	`client_id` INT(11) NOT NULL,
	`location_id` BIGINT(11) NULL,
	`alt_icon` BIGINT(11) NOT NULL,
	`protocol_version` INT(11) NOT NULL
) ENGINE=MyISAM;

CREATE TABLE `supla_v_client_channel_group` (
	`id` INT(11) NOT NULL,
	`func` INT(11) NOT NULL,
	`caption` VARCHAR(255) NULL COLLATE 'utf8_unicode_ci',
	`user_id` INT(11) NOT NULL,
	`location_id` INT(11) NOT NULL,
	`alt_icon` BIGINT(11) NOT NULL,
	`client_id` INT(11) NOT NULL
) ENGINE=MyISAM;

CREATE TABLE `supla_v_client_location` (
	`id` INT(11) NOT NULL,
	`caption` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`client_id` INT(11) NOT NULL
) ENGINE=MyISAM;

CREATE TABLE `supla_v_device_accessid` (
	`id` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`enabled` INT(4) UNSIGNED NOT NULL,
	`password` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci',
	`limit_client` INT(11) NOT NULL
) ENGINE=MyISAM;

CREATE TABLE `supla_v_device_location` (
	`id` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`enabled` INT(4) UNSIGNED NOT NULL,
	`limit_iodev` INT(11) NOT NULL,
	`password` VARCHAR(32) NOT NULL COLLATE 'utf8_unicode_ci'
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `supla_v_client`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client` AS SELECT `c`.`id` AS `id`,`c`.`access_id` AS `access_id`,`c`.`guid` AS `guid`,`c`.`name` AS `name`,`c`.`reg_ipv4` AS `reg_ipv4`,
               `c`.`reg_date` AS `reg_date`,`c`.`last_access_ipv4` AS `last_access_ipv4`,`c`.`last_access_date` AS `last_access_date`,
               `c`.`software_version` AS `software_version`,`c`.`protocol_version` AS `protocol_version`,`c`.`enabled` AS `enabled`,
                                                                                `a`.`user_id` AS `user_id`
        FROM (`supla_client` `c` JOIN `supla_accessid` `a` ON((`a`.`id` = `c`.`access_id`))) ;

DROP TABLE IF EXISTS `supla_v_client_channel`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_channel` AS SELECT `c`.`id` AS `id`,
						       `c`.`type` AS `type`,
						       `c`.`func` AS `func`,
						       `c`.`param1` AS `param1`,
						       `c`.`param2` AS `param2`,
						       `c`.`caption` AS `caption`,
						       `c`.`param3` AS `param3`,
						       `c`.`user_id` AS `user_id`,
						       `c`.`channel_number` AS `channel_number`,
						       `c`.`iodevice_id` AS `iodevice_id`,
						       `cl`.`id` AS `client_id`,
						       (CASE ifnull(`c`.`location_id`,0)
						            WHEN 0 THEN `d`.`location_id`
						            ELSE `c`.`location_id`
						        END) AS `location_id`,
						       ifnull(`c`.`alt_icon`,0) AS `alt_icon`,
						       `d`.`protocol_version` AS `protocol_version`
						FROM `supla_dev_channel` `c`
						      JOIN `supla_iodevice` `d` ON (`d`.`id` = `c`.`iodevice_id`)
						      JOIN `supla_location` `l` ON (`l`.`id` = CASE ifnull(`c`.`location_id`,0)
						                                                        WHEN 0 THEN `d`.`location_id`
						                                                        ELSE `c`.`location_id`
						                                                    END)
						      JOIN `supla_rel_aidloc` `r` ON (`r`.`location_id` = `l`.`id`)
						      JOIN `supla_accessid` `a` ON (`a`.`id` = `r`.`access_id`)
						      JOIN `supla_client` `cl` ON (`cl`.`access_id` = `r`.`access_id`)
						WHERE (`c`.`func` IS NOT NULL)
						       AND (ifnull(`c`.`hidden`,0) = 0)
						       AND (`c`.`func` <> 0)
						       AND (`d`.`enabled` = 1)
						       AND (`l`.`enabled` = 1)
						       AND (`a`.`enabled` = 1) ;

DROP TABLE IF EXISTS `supla_v_client_channel_group`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_channel_group` AS SELECT `g`.`id` AS `id`,
				 `g`.`func` AS `func`,
				 `g`.`caption` AS `caption`,
				 `g`.`user_id` AS `user_id`,
				 `g`.`location_id`,
				 ifnull(`g`.`alt_icon`,0) AS `alt_icon`,
				 `cl`.`id` AS `client_id`
                                                                                       FROM `supla_dev_channel_group` `g`
                                                                                                JOIN `supla_location` `l` on (`l`.`id` = `g`.`location_id`)
                                                                                                JOIN `supla_rel_aidloc` `r` on (`r`.`location_id` = `l`.`id`)
                                                                                                JOIN `supla_accessid` `a` on (`a`.`id` = `r`.`access_id`)
                                                                                                JOIN `supla_client` `cl` on (`cl`.`access_id` = `r`.`access_id`)
				WHERE ((`g`.`func` IS NOT NULL)
				  AND (ifnull(`g`.`hidden`,0) = 0)
				  AND (`g`.`func` <> 0)
				  AND (`l`.`enabled` = 1)
				  AND (`a`.`enabled` = 1)) ;

DROP TABLE IF EXISTS `supla_v_client_location`;
CREATE
ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_location` AS
SELECT `l`.`id` AS `id`, `l`.`caption` AS `caption`, `c`.`id` AS `client_id`
FROM ((`supla_rel_aidloc` `al` JOIN `supla_location` `l` ON ((`l`.`id` = `al`.`location_id`)))
    JOIN `supla_client` `c` ON ((`c`.`access_id` = `al`.`access_id`)))
        WHERE (`l`.`enabled` = 1) ;

DROP TABLE IF EXISTS `supla_v_device_accessid`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_device_accessid` AS SELECT `a`.`id` AS `id`,`a`.`user_id` AS `user_id`,cast(`a`.`enabled` as unsigned) AS `enabled`,`a`.`password` AS `password`,
                                                                                         `u`.`limit_client` AS `limit_client`
        FROM (`supla_accessid` `a` JOIN `supla_user` `u` ON((`u`.`id` = `a`.`user_id`))) ;

DROP TABLE IF EXISTS `supla_v_device_location`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_device_location` AS SELECT `l`.`id` AS `id`,`l`.`user_id` AS `user_id`,cast(`l`.`enabled` as unsigned) AS `enabled`,`u`.`limit_iodev` AS `limit_iodev`,
               `l`.`password` AS `password`
        FROM (`supla_location` `l` JOIN `supla_user` `u` ON((`u`.`id` = `l`.`user_id`))) ;

DROP TABLE IF EXISTS `supla_v_rel_cg`;
CREATE ALGORITHM=UNDEFINED VIEW `supla_v_rel_cg` AS SELECT `r`.`group_id` AS `group_id`,`r`.`channel_id` AS `channel_id`,
                 `c`.`iodevice_id` AS `iodevice_id`,`d`.`protocol_version` AS `protocol_version`,
                                                           `g`.`client_id` AS `client_id`,
                                                           `c`.`hidden`    AS `channel_hidden`
                                                    from (((`supla_v_client_channel_group` `g` join `supla_rel_cg` `r`
                                                            on ((`r`.`group_id` = `g`.`id`)))
                                                        join `supla_dev_channel` `c` on ((`c`.`id` = `r`.`channel_id`)))
                                                        join `supla_iodevice` `d` on ((`d`.`id` = `c`.`iodevice_id`)))
                                                    where `d`.`enabled` = 1;

CREATE ALGORITHM=UNDEFINED VIEW `supla_v_user_channel_group` AS select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,
				 `g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,
                                                                       `rel`.`channel_id` AS `channel_id`,
                                                                       `c`.`iodevice_id`  AS `iodevice_id`
                                                                from ((((`supla_dev_channel_group` `g` join `supla_location` `l`
                                                                         on ((`l`.`id` = `g`.`location_id`)))
                                                                    join `supla_rel_cg` `rel` on ((`rel`.`group_id` = `g`.`id`)))
                                                                    join `supla_dev_channel` `c` on ((`c`.`id` = `rel`.`channel_id`)))
                                                                    join `supla_iodevice` `d` on ((`d`.`id` = `c`.`iodevice_id`)))
                                                                where ((`g`.`func` is not null) and (ifnull(`g`.`hidden`, 0) = 0) and
                                                                       (`g`.`func` <> 0)
				 and (`l`.`enabled` = 1) and (`d`.`enabled` = 1)) ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
