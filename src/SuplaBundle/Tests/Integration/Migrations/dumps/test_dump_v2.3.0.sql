-- mysqldump -h 127.0.0.1 --routines -u root -p supla > test_dump_v2.3.0.sql

-- MySQL dump 10.13  Distrib 5.7.28, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: supla
-- ------------------------------------------------------
-- Server version	5.7.28

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `esp_update`
--

DROP TABLE IF EXISTS `esp_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esp_update` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esp_update`
--

LOCK TABLES `esp_update` WRITE;
/*!40000 ALTER TABLE `esp_update` DISABLE KEYS */;
/*!40000 ALTER TABLE `esp_update` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `esp_update_log`
--

DROP TABLE IF EXISTS `esp_update_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `esp_update_log` (
  `date` datetime NOT NULL,
  `device_id` int(11) NOT NULL,
  `platform` tinyint(4) NOT NULL,
  `fparam1` int(11) NOT NULL,
  `fparam2` int(11) NOT NULL,
  `fparam3` int(11) NOT NULL,
  `fparam4` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `esp_update_log`
--

LOCK TABLES `esp_update_log` WRITE;
/*!40000 ALTER TABLE `esp_update_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `esp_update_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('20170101000000'),('20170414101854'),('20170612204116'),('20170818114139'),('20171013140904'),('20171208222022'),('20171210105120'),('20180108224520'),('20180113234138'),('20180116184415'),('20180203231115'),('20180208145738'),('20180224184251'),('20180324222844'),('20180326134725'),('20180403175932'),('20180403203101'),('20180403211558'),('20180411202101'),('20180411203913'),('20180416201401'),('20180423121539'),('20180507095139'),('20180518131234'),('20180707221458'),('20180717094843'),('20180723132652'),('20180807083217'),('20180812205513'),('20180814155501'),('20180914222230'),('20181001221229'),('20181007112610'),('20181019115859'),('20181024164957'),('20181025171850'),('20181026171557'),('20181105144611'),('20181126225634'),('20181129170610'),('20181129195431'),('20181129231132'),('20181204174603'),('20181205092324'),('20181222001450'),('20190105130410'),('20190117075805'),('20190219184847'),('20190325215115'),('20190401151822'),('20190720215803'),('20190813232026');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_accessid`
--

DROP TABLE IF EXISTS `supla_accessid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_accessid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `caption` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A5549B6CA76ED395` (`user_id`),
  CONSTRAINT `FK_A5549B6CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_accessid`
--

LOCK TABLES `supla_accessid` WRITE;
/*!40000 ALTER TABLE `supla_accessid` DISABLE KEYS */;
INSERT INTO `supla_accessid`
VALUES (1, 1, '827f7d3e', 'Access Identifier #1', 1),
       (2, 2, 'fe4f2e56', 'Access Identifier #1', 1),
       (3, 1, '0e730b9e', 'Wspólny', 1),
       (4, 1, 'b2bc2f01', 'Dzieci', 1),
       (5, 2, '23106237', 'Supler Access ID', 1);
/*!40000 ALTER TABLE `supla_accessid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_amazon_alexa`
--

DROP TABLE IF EXISTS `supla_amazon_alexa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_amazon_alexa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reg_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `access_token` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `refresh_token` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `region` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_290228F0A76ED395` (`user_id`),
  CONSTRAINT `FK_290228F0A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_amazon_alexa`
--

LOCK TABLES `supla_amazon_alexa` WRITE;
/*!40000 ALTER TABLE `supla_amazon_alexa` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_amazon_alexa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_audit`
--

DROP TABLE IF EXISTS `supla_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_audit`(
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
  KEY `supla_audit_int_param` (`int_param`),
  CONSTRAINT `FK_EFE348F4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE SET NULL
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_audit`
--

LOCK TABLES `supla_audit` WRITE;
/*!40000 ALTER TABLE `supla_audit` DISABLE KEYS */;
INSERT INTO `supla_audit`
VALUES (1, 1, 1, '2019-12-28 17:48:31', 2130706433, 'user@supla.org', NULL);
/*!40000 ALTER TABLE `supla_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_client`
--

DROP TABLE IF EXISTS `supla_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_client` (
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_client`
--

LOCK TABLES `supla_client` WRITE;
/*!40000 ALTER TABLE `supla_client` DISABLE KEYS */;
INSERT INTO `supla_client`
VALUES (1, 3, _binary '7636290', 'HTC One M8', 1, 4277859565, '2019-11-08 14:46:56', 2074964767, '2019-12-24 19:52:28', '1.81', 2, 1, NULL,
        NULL, NULL),
       (2, 3, _binary '5288378', 'iPhone 6s', 1, 881026976, '2019-11-08 14:12:25', 1873489289, '2019-12-25 17:32:55', '1.16', 75, 1, NULL,
        NULL, NULL),
       (3, 3, _binary '4574482', 'Nokia 3310', 0, 1764108946, '2019-12-17 12:21:47', 1719734664, '2019-12-22 20:23:36', '1.70', 9, 1, NULL,
        NULL, NULL),
       (4, 3, _binary '4014998', 'Samsung Galaxy Tab S2', 1, 330720602, '2019-12-18 06:30:34', 2057438377, '2019-12-26 19:16:15', '1.88',
        87, 1, NULL, NULL, NULL),
       (5, 3, _binary '6822997', 'Apple iPad', 0, 2138678420, '2019-12-14 20:01:13', 1141314063, '2019-12-27 11:51:54', '1.19', 8, 1, NULL,
        NULL, NULL),
       (6, 5, _binary '6237779', 'SUPLER PHONE', 0, 1345785055, '2019-11-25 07:10:18', 617895272, '2019-12-24 09:18:56', '1.57', 20, 2,
        NULL, NULL, NULL);
/*!40000 ALTER TABLE `supla_client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_dev_channel`
--

DROP TABLE IF EXISTS `supla_dev_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_dev_channel` (
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
  `text_param1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text_param2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text_param3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alt_icon` int(11) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `location_id` int(11) DEFAULT NULL,
  `flags` int(11) DEFAULT NULL,
  `user_icon_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_CHANNEL` (`iodevice_id`,`channel_number`),
  KEY `IDX_81E928C9125F95D6` (`iodevice_id`),
  KEY `IDX_81E928C9A76ED395` (`user_id`),
  KEY `IDX_81E928C964D218E` (`location_id`),
  KEY `IDX_81E928C9CB4C938` (`user_icon_id`),
  CONSTRAINT `FK_81E928C9125F95D6` FOREIGN KEY (`iodevice_id`) REFERENCES `supla_iodevice` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_81E928C964D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
  CONSTRAINT `FK_81E928C9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`),
  CONSTRAINT `FK_81E928C9CB4C938` FOREIGN KEY (`user_icon_id`) REFERENCES `supla_user_icons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=307 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_dev_channel`
--

LOCK TABLES `supla_dev_channel` WRITE;
/*!40000 ALTER TABLE `supla_dev_channel` DISABLE KEYS */;
INSERT INTO `supla_dev_channel`
VALUES (1, 1, 1, 0, 'Ducimus tempora reprehenderit.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (2, 1, 1, 1, 'Qui nam commodi aut at.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (3, 2, 1, 0, 'Quis dolorum mollitia provident.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (4, 2, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (5, 2, 1, 2, 'Officiis molestias.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (6, 2, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (7, 2, 1, 4, 'Vitae recusandae sunt.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (8, 2, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (9, 2, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (10, 3, 1, 0, 'Dignissimos omnis vero assumenda.', 4010, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (11, 3, 1, 1, 'Aut dolorem ducimus.', 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (12, 4, 1, 0, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (13, 4, 1, 1, 'In voluptas.', 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (14, 4, 1, 2, 'Nihil explicabo alias aut.', 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (15, 4, 1, 3, NULL, 1000, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (16, 4, 1, 4, 'Magnam in ea deleniti.', 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (17, 4, 1, 5, 'Praesentium similique consequatur expedita.', 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (18, 4, 1, 6, NULL, 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (19, 4, 1, 7, 'Autem consequatur dolorem mollitia.', 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (20, 4, 1, 8, 'Incidunt nisi sit.', 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (21, 4, 1, 9, 'Odit maxime.', 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (22, 4, 1, 10, NULL, 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (23, 4, 1, 11, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (24, 4, 1, 12, NULL, 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (25, 4, 1, 13, 'Consequatur et id.', 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (26, 4, 1, 14, NULL, 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (27, 4, 1, 15, 'Deserunt ea.', 1010, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (28, 4, 1, 16, 'Sint harum consectetur.', 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (29, 4, 1, 17, NULL, 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (30, 4, 1, 18, NULL, 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (31, 4, 1, 19, 'Veniam porro labore.', 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (32, 4, 1, 20, 'Cumque officia rem.', 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (33, 4, 1, 21, NULL, 2000, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (34, 4, 1, 22, 'Ut aut illo amet.', 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (35, 4, 1, 23, NULL, 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (36, 4, 1, 24, NULL, 2010, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (37, 4, 1, 25, 'Rem occaecati sed.', 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (38, 4, 1, 26, 'Reiciendis itaque repudiandae autem et.', 2010, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (39, 4, 1, 27, NULL, 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (40, 4, 1, 28, 'Odio ut culpa similique.', 2010, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (41, 4, 1, 29, 'Nulla sit.', 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (42, 4, 1, 30, NULL, 2020, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (43, 4, 1, 31, 'Ut qui aliquid facilis cumque.', 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (44, 4, 1, 32, NULL, 2020, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (45, 4, 1, 33, NULL, 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (46, 4, 1, 34, 'Ullam suscipit et iusto.', 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (47, 4, 1, 35, 'Sed maxime voluptatem recusandae.', 2020, 110, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (48, 4, 1, 36, 'Quod officia minima neque.', 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (49, 4, 1, 37, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (50, 4, 1, 38, 'Architecto doloribus saepe.', 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (51, 4, 1, 39, 'Facilis commodi sed fugit.', 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (52, 4, 1, 40, NULL, 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (53, 4, 1, 41, 'Consequatur nemo tenetur.', 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (54, 4, 1, 42, NULL, 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (55, 4, 1, 43, 'Sit maxime quae.', 3034, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (56, 4, 1, 44, 'Neque et dolorem.', 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (57, 4, 1, 45, NULL, 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (58, 4, 1, 46, NULL, 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (59, 4, 1, 47, NULL, 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (60, 4, 1, 48, 'Quae placeat.', 3048, 270, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (61, 4, 1, 49, 'Exercitationem eligendi dicta.', 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (62, 4, 1, 50, 'Dolorem quia.', 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (63, 4, 1, 51, 'A aspernatur incidunt.', 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (64, 4, 1, 52, 'Quo consequatur autem voluptatem.', 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (65, 4, 1, 53, 'Rerum culpa qui.', 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (66, 4, 1, 54, NULL, 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, 1),
       (67, 4, 1, 55, 'Ea eos molestias.', 5010, 310, NULL, 103, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, 1),
       (68, 4, 1, 56, 'Quidem odit mollitia eaque.', 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (69, 4, 1, 57, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (70, 4, 1, 58, NULL, 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (71, 4, 1, 59, NULL, 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (72, 5, 1, 0, 'Aut aliquam exercitationem.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (73, 5, 1, 1, NULL, 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (74, 5, 1, 2, NULL, 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (75, 5, 1, 3, NULL, 1000, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (76, 5, 1, 4, NULL, 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (77, 5, 1, 5, 'Aut sapiente laudantium.', 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (78, 5, 1, 6, 'Qui ullam dolor magnam.', 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (79, 5, 1, 7, NULL, 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (80, 5, 1, 8, 'Velit at et voluptas.', 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (81, 5, 1, 9, 'Eligendi facere non est.', 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (82, 5, 1, 10, 'Id necessitatibus qui cumque.', 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (83, 5, 1, 11, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (84, 5, 1, 12, 'Voluptatibus praesentium iure.', 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (85, 5, 1, 13, NULL, 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (86, 5, 1, 14, 'Quia aliquam nihil non.', 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (87, 5, 1, 15, 'Et et quasi.', 1010, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (88, 5, 1, 16, NULL, 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (89, 5, 1, 17, NULL, 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (90, 5, 1, 18, NULL, 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (91, 5, 1, 19, 'Officiis et natus.', 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (92, 5, 1, 20, 'Doloremque est accusamus.', 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (93, 5, 1, 21, NULL, 2000, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (94, 5, 1, 22, 'Omnis cupiditate aut at.', 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (95, 5, 1, 23, NULL, 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (96, 5, 1, 24, NULL, 2010, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (97, 5, 1, 25, NULL, 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (98, 5, 1, 26, NULL, 2010, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (99, 5, 1, 27, NULL, 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (100, 5, 1, 28, 'Omnis dolorem sunt.', 2010, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (101, 5, 1, 29, 'Dolor quia ea.', 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (102, 5, 1, 30, NULL, 2020, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (103, 5, 1, 31, 'Incidunt quo.', 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (104, 5, 1, 32, 'Nulla labore optio qui.', 2020, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (105, 5, 1, 33, NULL, 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (106, 5, 1, 34, 'Enim nihil voluptatem.', 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (107, 5, 1, 35, 'Illum dolorem eaque.', 2020, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (108, 5, 1, 36, 'Consequatur sapiente iure ut.', 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (109, 5, 1, 37, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (110, 5, 1, 38, NULL, 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (111, 5, 1, 39, 'Eligendi dolorum quo expedita.', 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (112, 5, 1, 40, 'Ipsam aut voluptate tenetur qui.', 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (113, 5, 1, 41, NULL, 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (114, 5, 1, 42, 'Maxime aut facilis cupiditate.', 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (115, 5, 1, 43, NULL, 3034, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (116, 5, 1, 44, 'Alias dignissimos officiis.', 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (117, 5, 1, 45, 'Expedita veritatis sed ut.', 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (118, 5, 1, 46, 'Minus cum amet.', 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (119, 5, 1, 47, 'Ducimus soluta a perferendis.', 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (120, 5, 1, 48, 'Totam et qui.', 3048, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (121, 5, 1, 49, NULL, 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (122, 5, 1, 50, 'Qui quae accusamus odio.', 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (123, 5, 1, 51, 'Iste et quia.', 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (124, 5, 1, 52, NULL, 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (125, 5, 1, 53, NULL, 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (126, 5, 1, 54, 'Cupiditate hic aperiam doloremque accusantium.', 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (127, 5, 1, 55, NULL, 5010, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (128, 5, 1, 56, NULL, 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (129, 5, 1, 57, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (130, 5, 1, 58, NULL, 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (131, 5, 1, 59, NULL, 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (132, 6, 1, 0, NULL, 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (133, 6, 1, 1, 'Nam cupiditate voluptatem corrupti.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (134, 6, 1, 2, 'Quam odit quia nam.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (135, 6, 1, 3, 'Delectus nobis et.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (136, 6, 1, 4, 'Officiis non dolor optio.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (137, 6, 1, 5, NULL, 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (138, 6, 1, 6, NULL, 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (139, 6, 1, 7, 'Corporis doloribus consequatur hic.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (140, 6, 1, 8, 'Est nemo inventore voluptas.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (141, 6, 1, 9, NULL, 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (142, 7, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (143, 7, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (144, 7, 1, 2, 'Corporis sit unde sit.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (145, 7, 1, 3, 'Quidem soluta quis repellat.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (146, 7, 1, 4, 'Veritatis laudantium dolor eligendi.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (147, 7, 1, 5, 'Aspernatur ut ea vel corporis.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (148, 7, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (149, 8, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (150, 8, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (151, 8, 1, 2, 'Commodi beatae adipisci.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (152, 8, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (153, 8, 1, 4, 'Sit vel nobis maiores.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (154, 8, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (155, 8, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (156, 9, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (157, 9, 1, 1, 'Accusamus vel possimus.', 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (158, 9, 1, 2, 'Quo aliquam dolore.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (159, 9, 1, 3, 'Vel illum cum asperiores.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (160, 9, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (161, 9, 1, 5, 'Atque tempore omnis commodi.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (162, 9, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (163, 10, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (164, 10, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (165, 10, 1, 2, 'Consectetur voluptatem dicta sit.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (166, 10, 1, 3, 'Est voluptate vel.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (167, 10, 1, 4, 'Velit tempore.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (168, 10, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (169, 10, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (170, 11, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (171, 11, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (172, 11, 1, 2, 'Assumenda quia molestias.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (173, 11, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (174, 11, 1, 4, 'Magnam non iusto aut.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (175, 11, 1, 5, 'Quas natus temporibus expedita enim.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (176, 11, 1, 6, 'Odit iure itaque voluptatem.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (177, 12, 1, 0, 'Odit porro nostrum sit.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (178, 12, 1, 1, 'Fuga molestiae.', 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (179, 12, 1, 2, 'Odio quos minima non odit.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (180, 12, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (181, 12, 1, 4, 'Beatae et architecto.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (182, 12, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (183, 12, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (184, 13, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (185, 13, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (186, 13, 1, 2, NULL, 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (187, 13, 1, 3, 'Commodi quo.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (188, 13, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (189, 13, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (190, 13, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (191, 14, 1, 0, 'Eveniet vel optio.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (192, 14, 1, 1, 'Deleniti voluptatem repellendus.', 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (193, 14, 1, 2, 'Vitae voluptates impedit quod.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (194, 14, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (195, 14, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (196, 14, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (197, 14, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (198, 15, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (199, 15, 1, 1, 'Optio soluta et sunt.', 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (200, 15, 1, 2, 'Esse molestiae quia voluptas.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (201, 15, 1, 3, 'Ut libero et.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (202, 15, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (203, 15, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (204, 15, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (205, 16, 1, 0, 'Illum eum iste voluptate.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (206, 16, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (207, 16, 1, 2, NULL, 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (208, 16, 1, 3, 'Eum voluptatem consequatur.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (209, 16, 1, 4, 'Dignissimos consequatur qui aspernatur.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (210, 16, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (211, 16, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (212, 17, 1, 0, 'Nobis vel ut.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (213, 17, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (214, 17, 1, 2, NULL, 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (215, 17, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (216, 17, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (217, 17, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (218, 17, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (219, 18, 1, 0, 'Natus velit commodi eos.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (220, 18, 1, 1, 'Omnis nisi quod omnis.', 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (221, 18, 1, 2, 'Qui rerum nihil.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (222, 18, 1, 3, 'Dicta in quas maiores.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (223, 18, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (224, 18, 1, 5, 'Eaque odio sint modi.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (225, 18, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (226, 19, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (227, 19, 1, 1, 'Et accusamus quia.', 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (228, 19, 1, 2, 'Voluptatibus eaque et aperiam ipsa.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (229, 19, 1, 3, 'Veniam ratione corporis.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (230, 19, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (231, 19, 1, 5, 'Dolores et.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (232, 19, 1, 6, 'Dolor quo et.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (233, 20, 1, 0, 'Maiores architecto sed sit.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (234, 20, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (235, 20, 1, 2, 'Qui et et.', 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (236, 20, 1, 3, 'Voluptas non quas quo deserunt.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (237, 20, 1, 4, 'Enim accusantium tempora temporibus et.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (238, 20, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (239, 20, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (240, 21, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (241, 21, 1, 1, NULL, 2900, 90, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (242, 21, 1, 2, NULL, 2900, 20, 15, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (243, 21, 1, 3, 'Optio suscipit rerum vitae.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (244, 21, 1, 4, 'Occaecati nulla non.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL),
       (245, 21, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL),
       (246, 21, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (247, 22, 2, 0, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (248, 22, 2, 1, NULL, 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (249, 22, 2, 2, 'Ex et possimus.', 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (250, 22, 2, 3, NULL, 1000, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (251, 22, 2, 4, NULL, 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (252, 22, 2, 5, NULL, 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (253, 22, 2, 6, 'Sapiente aut ipsum aliquid.', 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (254, 22, 2, 7, NULL, 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (255, 22, 2, 8, 'Vero sed quos.', 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (256, 22, 2, 9, NULL, 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (257, 22, 2, 10, 'Sequi dicta eaque.', 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (258, 22, 2, 11, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (259, 22, 2, 12, 'Amet delectus eum.', 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (260, 22, 2, 13, 'Omnis tempore delectus.', 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (261, 22, 2, 14, 'Enim rerum dolores enim a.', 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (262, 22, 2, 15, 'Aperiam dolorem omnis quisquam.', 1010, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (263, 22, 2, 16, NULL, 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (264, 22, 2, 17, NULL, 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (265, 22, 2, 18, 'Ipsa voluptatem.', 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (266, 22, 2, 19, NULL, 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (267, 22, 2, 20, NULL, 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (268, 22, 2, 21, 'Dolorum aut ratione hic.', 2000, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (269, 22, 2, 22, NULL, 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (270, 22, 2, 23, 'Aut et non commodi.', 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (271, 22, 2, 24, NULL, 2010, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (272, 22, 2, 25, NULL, 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (273, 22, 2, 26, NULL, 2010, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (274, 22, 2, 27, 'Excepturi expedita animi qui.', 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (275, 22, 2, 28, 'Totam quis.', 2010, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (276, 22, 2, 29, NULL, 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (277, 22, 2, 30, NULL, 2020, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (278, 22, 2, 31, 'Autem quia exercitationem.', 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (279, 22, 2, 32, NULL, 2020, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (280, 22, 2, 33, 'Sunt vel sapiente dignissimos.', 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (281, 22, 2, 34, NULL, 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (282, 22, 2, 35, 'Ipsam rerum.', 2020, 110, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (283, 22, 2, 36, 'Itaque excepturi in et.', 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (284, 22, 2, 37, 'Perferendis autem.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (285, 22, 2, 38, 'Inventore dolorem distinctio a.', 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (286, 22, 2, 39, 'Nam sapiente quis.', 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (287, 22, 2, 40, 'Autem ipsam delectus.', 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (288, 22, 2, 41, 'Quasi aut quibusdam.', 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (289, 22, 2, 42, NULL, 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (290, 22, 2, 43, 'Provident animi illum et eum.', 3034, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (291, 22, 2, 44, 'Temporibus porro repellat aut.', 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (292, 22, 2, 45, NULL, 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (293, 22, 2, 46, NULL, 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (294, 22, 2, 47, 'Et voluptatem ullam similique.', 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (295, 22, 2, 48, 'Aut veniam dignissimos.', 3048, 270, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (296, 22, 2, 49, 'Maxime sed dolore consequatur.', 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (297, 22, 2, 50, NULL, 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (298, 22, 2, 51, 'Rem voluptas rerum quis dolores.', 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (299, 22, 2, 52, NULL, 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (300, 22, 2, 53, 'Voluptas nihil voluptatibus.', 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (301, 22, 2, 54, 'Quam voluptate.', 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (302, 22, 2, 55, NULL, 5010, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (303, 22, 2, 56, NULL, 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (304, 22, 2, 57, 'Libero enim recusandae.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (305, 22, 2, 58, NULL, 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL),
       (306, 22, 2, 59, 'Harum suscipit.', 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL);
/*!40000 ALTER TABLE `supla_dev_channel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_dev_channel_group`
--

DROP TABLE IF EXISTS `supla_dev_channel_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_dev_channel_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `caption` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `func` int(11) NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL,
  `alt_icon` int(11) DEFAULT NULL,
  `user_icon_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6B2EFCE5A76ED395` (`user_id`),
  KEY `IDX_6B2EFCE564D218E` (`location_id`),
  KEY `IDX_6B2EFCE5CB4C938` (`user_icon_id`),
  CONSTRAINT `FK_6B2EFCE564D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
  CONSTRAINT `FK_6B2EFCE5A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`),
  CONSTRAINT `FK_6B2EFCE5CB4C938` FOREIGN KEY (`user_icon_id`) REFERENCES `supla_user_icons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_dev_channel_group`
--

LOCK TABLES `supla_dev_channel_group` WRITE;
/*!40000 ALTER TABLE `supla_dev_channel_group` DISABLE KEYS */;
INSERT INTO `supla_dev_channel_group`
VALUES (1, 1, 'Światła na parterze', 140, 0, 4, 0, NULL),
       (2, 1, 'Et sit autem.', 140, 0, 3, 0, NULL),
       (3, 1, 'Ea laudantium accusamus corporis.', 140, 0, 3, 0, NULL),
       (4, 1, 'Officia recusandae incidunt.', 110, 0, 3, 0, NULL),
       (5, 1, 'Saepe omnis molestias modi.', 90, 0, 4, 0, NULL),
       (6, 1, 'Ex quod sit.', 140, 0, 4, 0, NULL),
       (7, 1, 'Sit est velit ut modi.', 20, 0, 3, 0, NULL),
       (8, 1, 'Ipsa sit expedita voluptas.', 20, 0, 4, 0, NULL),
       (9, 1, 'Aut aperiam.', 20, 0, 5, 0, NULL),
       (10, 1, 'Consequuntur consectetur dolores alias.', 90, 0, 3, 0, NULL),
       (11, 1, 'Nisi est perspiciatis.', 110, 0, 5, 0, NULL);
/*!40000 ALTER TABLE `supla_dev_channel_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_direct_link`
--

DROP TABLE IF EXISTS `supla_direct_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_direct_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `channel_group_id` int(11) DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `allowed_actions` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active_from` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `active_to` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `executions_limit` int(11) DEFAULT NULL,
  `last_used` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `last_ipv4` int(10) unsigned DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `disable_http_get` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_6AE7809FA76ED395` (`user_id`),
  KEY `IDX_6AE7809F72F5A1AA` (`channel_id`),
  KEY `IDX_6AE7809F89E4AAEE` (`channel_group_id`),
  CONSTRAINT `FK_6AE7809F72F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6AE7809F89E4AAEE` FOREIGN KEY (`channel_group_id`) REFERENCES `supla_dev_channel_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6AE7809FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_direct_link`
--

LOCK TABLES `supla_direct_link` WRITE;
/*!40000 ALTER TABLE `supla_direct_link` DISABLE KEYS */;
INSERT INTO `supla_direct_link`
VALUES (1, 1, 183, NULL, 'MkzU3DwEYWYMcyD', 'Gold', '[1000]', NULL, NULL, NULL, NULL, 3647206537, 1, 0),
       (2, 1, 168, NULL, 'MwNTdYzkNQZT4', 'OldLace', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (3, 1, 193, NULL, 'RcUiGZZ2WYDN', 'MediumVioletRed', '[20]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (4, 1, 232, NULL, '5DEM2mNWyNjNYE', 'Snow', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (5, 1, 239, NULL, 'E2RTDMMmYG5', 'Cyan', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (6, 1, 146, NULL, 'YUiQJwM2ZGDZ', 'SteelBlue', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (7, 1, 217, NULL, 'jDMwNY3WjVENU', 'SaddleBrown', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (8, 1, 239, NULL, 'TMjxAZmi2WEVZ', 'PeachPuff', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (9, 1, 232, NULL, '4zMmyU3NMQMYTmZ', 'Chocolate', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (10, 1, 181, NULL, 'Q5WNz3TMcM', 'Fuchsia', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (11, 1, 234, NULL, 'k4TAk4jZNjcwjMM', 'IndianRed', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (12, 1, 142, NULL, 'zzNiMNBQTj4RW', 'LightGreen', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (13, 1, 202, NULL, 'YG5kN2MMWYZG', 'DarkMagenta', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (14, 1, 173, NULL, 'Z3NZDTYkUMhMDjT', 'PaleTurquoise', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (15, 1, 203, NULL, 'EUmc5YNDmkDZ44', 'Cyan', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (16, 1, 175, NULL, 'TykNTNUG2M', 'LightSkyBlue', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (17, 1, 143, NULL, 'MDZkUkTJMGydTh', 'MediumSpringGreen', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (18, 1, 185, NULL, 'EUTMcGY4Nz', 'GhostWhite', '[10]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (19, 1, 197, NULL, 'NDRMiMzNhZZhT', 'LemonChiffon', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (20, 1, 219, NULL, 'YEzY2mjc2YZEz', 'Indigo', '[70]', NULL, NULL, NULL, NULL, NULL, 1, 0),
       (21, 2, 247, NULL, 'zjjNMmNNhV', 'SUPLAER Direct Link', '[1000]', NULL, NULL, NULL, NULL, NULL, 1, 0);
/*!40000 ALTER TABLE `supla_direct_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_em_log`
--

DROP TABLE IF EXISTS `supla_em_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_em_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `phase1_fae` bigint(20) DEFAULT NULL,
  `phase1_rae` bigint(20) DEFAULT NULL,
  `phase1_fre` bigint(20) DEFAULT NULL,
  `phase1_rre` bigint(20) DEFAULT NULL,
  `phase2_fae` bigint(20) DEFAULT NULL,
  `phase2_rae` bigint(20) DEFAULT NULL,
  `phase2_fre` bigint(20) DEFAULT NULL,
  `phase2_rre` bigint(20) DEFAULT NULL,
  `phase3_fae` bigint(20) DEFAULT NULL,
  `phase3_rae` bigint(20) DEFAULT NULL,
  `phase3_fre` bigint(20) DEFAULT NULL,
  `phase3_rre` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id_idx` (`channel_id`),
  KEY `date_idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_em_log`
--

LOCK TABLES `supla_em_log` WRITE;
/*!40000 ALTER TABLE `supla_em_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_em_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_google_home`
--

DROP TABLE IF EXISTS `supla_google_home`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_google_home` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reg_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `access_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_98090074A76ED395` (`user_id`),
  CONSTRAINT `FK_98090074A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_google_home`
--

LOCK TABLES `supla_google_home` WRITE;
/*!40000 ALTER TABLE `supla_google_home` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_google_home` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_ic_log`
--

DROP TABLE IF EXISTS `supla_ic_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_ic_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `counter` bigint(20) NOT NULL,
  `calculated_value` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id_idx` (`channel_id`),
  KEY `date_idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_ic_log`
--

LOCK TABLES `supla_ic_log` WRITE;
/*!40000 ALTER TABLE `supla_ic_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_ic_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_iodevice`
--

DROP TABLE IF EXISTS `supla_iodevice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_iodevice` (
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
  `flags` int(11) DEFAULT NULL,
  `manufacturer_id` smallint(6) DEFAULT NULL,
  `product_id` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_793D49D2B6FCFB2` (`guid`),
  KEY `IDX_793D49D64D218E` (`location_id`),
  KEY `IDX_793D49DA76ED395` (`user_id`),
  KEY `IDX_793D49DF142C1A4` (`original_location_id`),
  CONSTRAINT `FK_793D49D64D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
  CONSTRAINT `FK_793D49DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`),
  CONSTRAINT `FK_793D49DF142C1A4` FOREIGN KEY (`original_location_id`) REFERENCES `supla_location` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_iodevice`
--

LOCK TABLES `supla_iodevice` WRITE;
/*!40000 ALTER TABLE `supla_iodevice` DISABLE KEYS */;
INSERT INTO `supla_iodevice`
VALUES (1, 4, 1, _binary '1411530', 'SONOFF-DS', 1, NULL, '2019-12-28 17:48:20', 5257721, '2019-12-28 17:48:20', 3771266185, '2.1', 2, NULL, NULL,
        0, NULL, NULL),
       (2, 5, 1, _binary '9951457', 'UNI-MODULE', 1, NULL, '2019-12-28 17:48:20', 5876265, '2019-12-28 17:48:20', NULL, '2.44', 2, NULL,
        NULL, 0, NULL, NULL),
       (3, 3, 1, _binary '1129042', 'RGB-801', 1, NULL, '2019-12-28 17:48:20', 4361368, '2019-12-28 17:48:20', NULL, '2.41', 2, NULL, NULL,
        0, NULL, NULL),
       (4, 4, 1, _binary '3662496', 'ALL-IN-ONE MEGA DEVICE', 1, NULL, '2019-12-28 17:48:20', 3042415, '2019-12-28 17:48:20', NULL, '2.38',
        2, NULL, NULL, 0, NULL, NULL),
       (5, 4, 1, _binary '974371', 'SECOND MEGA DEVICE', 1, NULL, '2019-12-28 17:48:20', 5528243, '2019-12-28 17:48:20', NULL, '2.43', 2,
        NULL, NULL, 0, NULL, NULL),
       (6, 4, 1, _binary '2659181', 'OH-MY-GATES. This device also has ridiculously long name!', 1, NULL, '2019-12-28 17:48:21', 2745687,
        '2019-12-28 17:48:21', NULL, '2.31', 2, NULL, NULL, 0, NULL, NULL),
       (7, 5, 1, _binary '1803003', 'CONSEQUATUR-EIUS', 1, NULL, '2019-12-28 17:48:21', 2104791, '2019-12-28 17:48:21', NULL, '2.0', 2,
        NULL, NULL, 0, NULL, NULL),
       (8, 5, 1, _binary '8373987', 'SIT', 1, NULL, '2019-12-28 17:48:21', 7728423, '2019-12-28 17:48:21', NULL, '2.29', 2, NULL, NULL, 0,
        NULL, NULL),
       (9, 5, 1, _binary '7688570', 'NESCIUNT-QUIBUSDAM-ARCHITECTO', 1, NULL, '2019-12-28 17:48:21', 792497, '2019-12-28 17:48:21', NULL,
        '2.29', 2, NULL, NULL, 0, NULL, NULL),
       (10, 5, 1, _binary '8991464', 'AD', 1, NULL, '2019-12-28 17:48:21', 6574522, '2019-12-28 17:48:21', NULL, '2.24', 2, NULL, NULL, 0,
        NULL, NULL),
       (11, 5, 1, _binary '3321364', 'SIT', 1, NULL, '2019-12-28 17:48:21', 926107, '2019-12-28 17:48:21', NULL, '2.32', 2, NULL, NULL, 0,
        NULL, NULL),
       (12, 5, 1, _binary '3339313', 'ANIMI', 1, NULL, '2019-12-28 17:48:21', 1454393, '2019-12-28 17:48:21', NULL, '2.9', 2, NULL, NULL, 0,
        NULL, NULL),
       (13, 5, 1, _binary '7106215', 'NEQUE-TENETUR-EST', 1, NULL, '2019-12-28 17:48:22', 5243, '2019-12-28 17:48:22', NULL, '2.0', 2, NULL,
        NULL, 0, NULL, NULL),
       (14, 5, 1, _binary '79915', 'DICTA-EXPEDITA-AUT', 1, NULL, '2019-12-28 17:48:22', 8886513, '2019-12-28 17:48:22', NULL, '2.13', 2,
        NULL, NULL, 0, NULL, NULL),
       (15, 5, 1, _binary '8575088', 'PERSPICIATIS-NEMO-LAUDANTIUM', 1, NULL, '2019-12-28 17:48:22', 1004992, '2019-12-28 17:48:22', NULL,
        '2.9', 2, NULL, NULL, 0, NULL, NULL),
       (16, 5, 1, _binary '2994201', 'EAQUE-UT', 1, NULL, '2019-12-28 17:48:22', 6783890, '2019-12-28 17:48:22', NULL, '2.19', 2, NULL,
        NULL, 0, NULL, NULL),
       (17, 5, 1, _binary '9659363', 'FACILIS', 1, NULL, '2019-12-28 17:48:22', 6635793, '2019-12-28 17:48:22', NULL, '2.41', 2, NULL, NULL,
        0, NULL, NULL),
       (18, 5, 1, _binary '3398953', 'EXPLICABO', 1, NULL, '2019-12-28 17:48:22', 2422559, '2019-12-28 17:48:22', NULL, '2.8', 2, NULL,
        NULL, 0, NULL, NULL),
       (19, 5, 1, _binary '654455', 'QUI-QUIA-NESCIUNT', 1, NULL, '2019-12-28 17:48:22', 7638536, '2019-12-28 17:48:22', NULL, '2.49', 2,
        NULL, NULL, 0, NULL, NULL),
       (20, 5, 1, _binary '5813439', 'AMET-ET-QUAS', 1, NULL, '2019-12-28 17:48:22', 433403, '2019-12-28 17:48:22', NULL, '2.36', 2, NULL,
        NULL, 0, NULL, NULL),
       (21, 5, 1, _binary '4963946', 'TEMPORE-SIMILIQUE', 1, NULL, '2019-12-28 17:48:23', 2531111, '2019-12-28 17:48:23', NULL, '2.20', 2,
        NULL, NULL, 0, NULL, NULL),
       (22, 6, 2, _binary '7708406', 'SUPLER MEGA DEVICE', 1, NULL, '2019-12-28 17:48:23', 9043228, '2019-12-28 17:48:23', NULL, '2.23', 2,
        NULL, NULL, 0, NULL, NULL);
/*!40000 ALTER TABLE `supla_iodevice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_location`
--

DROP TABLE IF EXISTS `supla_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `caption` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3698128EA76ED395` (`user_id`),
  CONSTRAINT `FK_3698128EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_location`
--

LOCK TABLES `supla_location` WRITE;
/*!40000 ALTER TABLE `supla_location` DISABLE KEYS */;
INSERT INTO `supla_location`
VALUES (1, 1, 'cfb5', 'Location #1', 1),
       (2, 2, '2931', 'Location #1', 1),
       (3, 1, '8519', 'Sypialnia', 1),
       (4, 1, 'efa5', 'Na zewnątrz', 1),
       (5, 1, '056b', 'Garaż', 1),
       (6, 2, '6dc8', 'Supler\'s location', 1);
/*!40000 ALTER TABLE `supla_location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_oauth_access_tokens`
--

DROP TABLE IF EXISTS `supla_oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_oauth_access_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2402564B5F37A13B` (`token`),
  KEY `IDX_2402564B19EB6921` (`client_id`),
  KEY `IDX_2402564BA76ED395` (`user_id`),
  KEY `IDX_2402564B4FEA67CF` (`access_id`),
  CONSTRAINT `FK_2402564B19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564B4FEA67CF` FOREIGN KEY (`access_id`) REFERENCES `supla_accessid` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_oauth_access_tokens`
--

LOCK TABLES `supla_oauth_access_tokens` WRITE;
/*!40000 ALTER TABLE `supla_oauth_access_tokens` DISABLE KEYS */;
INSERT INTO `supla_oauth_access_tokens`
VALUES (1, 1, 1, '0123456789012345678901234567890123456789', 2051218800,
        'offline_access channels_ea channelgroups_ea channels_files accessids_r accessids_rw account_r account_rw channels_r channels_rw channelgroups_r channelgroups_rw clientapps_r clientapps_rw directlinks_r directlinks_rw iodevices_r iodevices_rw locations_r locations_rw schedules_r schedules_rw',
        NULL, NULL),
       (2, 1, 1, 'MzU1ZDVkMmM2YjY4MGMzMzQ2ZmUwOTA4YjdlNGMyNmE5MDk5MTRmMjc4MWU2OWVlY2NjZjIxNmFhMWFhYmIwMg.aHR0cDovL3N1cGxhLmxvY2Fs',
        1577556575,
        'channels_ea channelgroups_ea channels_files accessids_r accessids_rw account_r account_rw channels_r channels_rw channelgroups_r channelgroups_rw clientapps_r clientapps_rw directlinks_r directlinks_rw iodevices_r iodevices_rw locations_r locations_rw schedules_r schedules_rw',
        NULL, NULL),
       (3, 1, 1, 'YWM2ZDFmMWViMzUxYzdkMTAyMWQ4ODRhNTM2YjBlMDViMjcwYjM3ZThlYmFmNGJkNjQ3NzgwNmY0YWJmMDRkNw.aHR0cDovL3N1cGxhLmxvY2Fs',
        1577556575, 'channels_files', NULL, NULL);
/*!40000 ALTER TABLE `supla_oauth_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_oauth_auth_codes`
--

DROP TABLE IF EXISTS `supla_oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_oauth_auth_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uri` longtext COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_48E00E5D5F37A13B` (`token`),
  KEY `IDX_48E00E5D19EB6921` (`client_id`),
  KEY `IDX_48E00E5DA76ED395` (`user_id`),
  CONSTRAINT `FK_48E00E5D19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_48E00E5DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_oauth_auth_codes`
--

LOCK TABLES `supla_oauth_auth_codes` WRITE;
/*!40000 ALTER TABLE `supla_oauth_auth_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_oauth_auth_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_oauth_client_authorizations`
--

DROP TABLE IF EXISTS `supla_oauth_client_authorizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_oauth_client_authorizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `scope` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `authorization_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_USER_CLIENT` (`user_id`,`client_id`),
  KEY `IDX_6B787396A76ED395` (`user_id`),
  KEY `IDX_6B78739619EB6921` (`client_id`),
  CONSTRAINT `FK_6B78739619EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6B787396A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_oauth_client_authorizations`
--

LOCK TABLES `supla_oauth_client_authorizations` WRITE;
/*!40000 ALTER TABLE `supla_oauth_client_authorizations` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_oauth_client_authorizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_oauth_clients`
--

DROP TABLE IF EXISTS `supla_oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_oauth_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `random_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uris` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `secret` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allowed_grant_types` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `type` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `public_client_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_4035AD80A76ED395` (`user_id`),
  KEY `supla_oauth_clients_random_id_idx` (`random_id`),
  KEY `supla_oauth_clients_type_idx` (`type`),
  CONSTRAINT `FK_4035AD80A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_oauth_clients`
--

LOCK TABLES `supla_oauth_clients` WRITE;
/*!40000 ALTER TABLE `supla_oauth_clients` DISABLE KEYS */;
INSERT INTO `supla_oauth_clients`
VALUES (1, '21x1kkpkcngg4skksw0skckcs8w8o00c4csock4os4ow0k8808', 'a:0:{}', '361bqznvirac8g0ok8c4gc0cwko0o048cwws84kscg4kwsw4wo',
        'a:2:{i:0;s:8:\"password\";i:1;s:13:\"refresh_token\";}', 1, NULL, NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `supla_oauth_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `supla_oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_oauth_refresh_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B809538C5F37A13B` (`token`),
  KEY `IDX_B809538C19EB6921` (`client_id`),
  KEY `IDX_B809538CA76ED395` (`user_id`),
  CONSTRAINT `FK_B809538C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B809538CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_oauth_refresh_tokens`
--

LOCK TABLES `supla_oauth_refresh_tokens` WRITE;
/*!40000 ALTER TABLE `supla_oauth_refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_oauth_refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_rel_aidloc`
--

DROP TABLE IF EXISTS `supla_rel_aidloc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_rel_aidloc` (
  `location_id` int(11) NOT NULL,
  `access_id` int(11) NOT NULL,
  PRIMARY KEY (`location_id`,`access_id`),
  KEY `IDX_2B15904164D218E` (`location_id`),
  KEY `IDX_2B1590414FEA67CF` (`access_id`),
  CONSTRAINT `FK_2B1590414FEA67CF` FOREIGN KEY (`access_id`) REFERENCES `supla_accessid` (`id`),
  CONSTRAINT `FK_2B15904164D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_rel_aidloc`
--

LOCK TABLES `supla_rel_aidloc` WRITE;
/*!40000 ALTER TABLE `supla_rel_aidloc` DISABLE KEYS */;
INSERT INTO `supla_rel_aidloc` VALUES (1,1),(2,2),(3,1),(4,1),(5,1),(6,2);
/*!40000 ALTER TABLE `supla_rel_aidloc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_rel_cg`
--

DROP TABLE IF EXISTS `supla_rel_cg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_rel_cg` (
  `channel_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`channel_id`),
  KEY `IDX_BE981CD772F5A1AA` (`channel_id`),
  KEY `IDX_BE981CD7FE54D947` (`group_id`),
  CONSTRAINT `FK_BE981CD772F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`),
  CONSTRAINT `FK_BE981CD7FE54D947` FOREIGN KEY (`group_id`) REFERENCES `supla_dev_channel_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_rel_cg`
--

LOCK TABLES `supla_rel_cg` WRITE;
/*!40000 ALTER TABLE `supla_rel_cg` DISABLE KEYS */;
INSERT INTO `supla_rel_cg`
VALUES (1, 1),
       (142, 3),
       (143, 5),
       (143, 10),
       (144, 9),
       (145, 11),
       (149, 6),
       (150, 5),
       (151, 7),
       (151, 8),
       (151, 9),
       (152, 11),
       (156, 6),
       (157, 5),
       (158, 8),
       (158, 9),
       (163, 3),
       (164, 5),
       (165, 8),
       (165, 9),
       (166, 4),
       (166, 11),
       (171, 5),
       (172, 8),
       (172, 9),
       (173, 4),
       (177, 3),
       (177, 6),
       (178, 5),
       (179, 8),
       (179, 9),
       (180, 11),
       (184, 6),
       (185, 5),
       (186, 8),
       (186, 9),
       (192, 5),
       (193, 7),
       (193, 9),
       (194, 4),
       (198, 3),
       (199, 5),
       (199, 10),
       (200, 9),
       (201, 4),
       (205, 6),
       (207, 9),
       (208, 11),
       (212, 6),
       (213, 5),
       (214, 7),
       (214, 9),
       (219, 3),
       (220, 5),
       (221, 7),
       (221, 8),
       (221, 9),
       (226, 3),
       (226, 6),
       (227, 5),
       (228, 9),
       (229, 11),
       (233, 2),
       (233, 6),
       (234, 5),
       (235, 7),
       (235, 9),
       (236, 4),
       (240, 1),
       (240, 2),
       (240, 6),
       (242, 7),
       (242, 9),
       (243, 4),
       (243, 11);
/*!40000 ALTER TABLE `supla_rel_cg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_schedule`
--

DROP TABLE IF EXISTS `supla_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `channel_id` int(11) DEFAULT NULL,
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
  `channel_group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_323E8ABEA76ED395` (`user_id`),
  KEY `IDX_323E8ABE72F5A1AA` (`channel_id`),
  KEY `next_calculation_date_idx` (`next_calculation_date`),
  KEY `enabled_idx` (`enabled`),
  KEY `date_start_idx` (`date_start`),
  KEY `IDX_323E8ABE89E4AAEE` (`channel_group_id`),
  CONSTRAINT `FK_323E8ABE72F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`),
  CONSTRAINT `FK_323E8ABE89E4AAEE` FOREIGN KEY (`channel_group_id`) REFERENCES `supla_dev_channel_group` (`id`),
  CONSTRAINT `FK_323E8ABEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_schedule`
--

LOCK TABLES `supla_schedule` WRITE;
/*!40000 ALTER TABLE `supla_schedule` DISABLE KEYS */;
INSERT INTO `supla_schedule`
VALUES (1, 1, 236, 'SS10 * * * *', 30, NULL, 'daily', '2019-12-29 01:43:53', NULL, 1, '2019-12-29 15:10:00', 'Non quo dolorem distinctio.',
        1, NULL),
       (2, 1, 166, '*/10 * * * *', 30, NULL, 'minutely', '2019-12-29 00:38:33', NULL, 1, '2019-12-28 17:50:00', 'Quo exercitationem ipsa.',
        1, NULL),
       (3, 1, 226, '*/15 * * * *', 110, NULL, 'minutely', '2019-12-29 22:18:24', NULL, 1, '2019-12-28 17:50:00', 'Delectus quia vitae.', 1,
        NULL),
       (4, 1, 170, '*/60 * * * *', 60, NULL, 'minutely', '2019-12-29 00:32:44', NULL, 1, '2019-12-28 18:35:00', 'Minima sequi et.', 1,
        NULL),
       (5, 1, 205, '*/30 * * * *', 60, NULL, 'minutely', '2020-01-03 20:01:55', NULL, 1, '2020-01-01 20:30:00', 'Vel dignissimos ut.', 1,
        NULL),
       (6, 1, 299, 'SR10 * * * 1,2,3', 80, '{"hue":196,"color_brightness":20}', 'daily', '2019-12-31 11:24:09', NULL, 1, '2019-12-30 07:25:00', 'Maxime ratione voluptas.', 1,
        NULL),
       (7, 1, 299, '15 14,19 * * *', 80, '{"hue":196,"color_brightness":20}', 'hourly', '2019-12-31 08:21:36', NULL, 1, '2019-12-29 08:30:00', 'Accusamus eum laudantium.',
        1, NULL),
       (8, 1, 150, 'SS10 * * * *', 10, NULL, 'daily', '2019-12-30 05:29:50', NULL, 1, '2019-12-29 15:10:00', 'Sapiente est qui commodi.', 1,
        NULL),
       (9, 1, 205, 'SR-10 * * * *', 60, NULL, 'daily', '2020-01-03 10:05:13', NULL, 1, '2020-01-02 07:05:00',
        'Totam perspiciatis cum earum vel.', 1, NULL),
       (10, 1, 235, '*/30 * * * *', 10, NULL, 'minutely', '2019-12-31 23:40:20', NULL, 1, '2019-12-30 00:10:00',
        'Vero pariatur amet tempore similique.', 1, NULL),
       (11, 1, 150, '*/5 * * * *', 10, NULL, 'minutely', '2019-12-29 18:54:50', NULL, 1, '2019-12-28 17:50:00',
        'Voluptate accusantium et possimus.', 1, NULL),
       (12, 1, 193, 'SR10 * * * *', 10, NULL, 'daily', '2020-01-03 09:21:10', NULL, 1, '2020-01-02 07:25:00',
        'Debitis perspiciatis quibusdam.', 1, NULL),
       (13, 1, 145, '*/15 * * * *', 50, NULL, 'minutely', '2019-12-28 18:41:55', NULL, 1, '2019-12-28 17:55:00', 'Minus nihil possimus.', 1,
        NULL),
       (14, 1, 145, 'SS10 * * * *', 50, NULL, 'daily', '2020-01-02 11:54:13', NULL, 1, '2019-12-31 15:15:00', 'Nemo atque.', 1, NULL),
       (15, 1, 173, 'SR10 * * * *', 50, NULL, 'daily', '2019-12-31 16:23:20', NULL, 1, '2019-12-30 07:25:00', 'Voluptas ut totam.', 1,
        NULL),
       (16, 1, 207, '*/5 * * * *', 90, NULL, 'minutely', '2020-01-02 22:30:26', NULL, 1, '2019-12-31 22:35:00',
        'Incidunt perspiciatis praesentium eum dolorem.', 1, NULL),
       (17, 1, 240, '*/10 * * * *', 70, NULL, 'minutely', '2019-12-30 08:24:19', NULL, 1, '2019-12-28 17:55:00',
        'Pariatur omnis illo animi.', 1, NULL),
       (18, 1, 201, '*/15 * * * *', 50, NULL, 'minutely', '2020-01-04 00:09:32', NULL, 1, '2020-01-02 00:25:00', 'Ad dolore earum.', 1,
        NULL),
       (19, 1, 235, 'SR-10 * * * *', 20, NULL, 'daily', '2019-12-30 14:18:49', NULL, 1, '2019-12-29 07:05:00', 'Nihil recusandae.', 1,
        NULL),
       (20, 1, 165, '*/15 * * * *', 20, NULL, 'minutely', '2019-12-31 21:31:32', NULL, 1, '2019-12-29 21:45:00', 'Tempore dolorum.', 1,
        NULL);
/*!40000 ALTER TABLE `supla_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_scheduled_executions`
--

DROP TABLE IF EXISTS `supla_scheduled_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_scheduled_executions`(
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
) ENGINE = InnoDB
  AUTO_INCREMENT = 906
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_scheduled_executions`
--

LOCK TABLES `supla_scheduled_executions` WRITE;
/*!40000 ALTER TABLE `supla_scheduled_executions` DISABLE KEYS */;
INSERT INTO `supla_scheduled_executions`
VALUES (1, 1, '2019-12-29 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (2, 1, '2019-12-30 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (3, 1, '2019-12-31 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (4, 2, '2019-12-29 00:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (5, 2, '2019-12-29 01:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (6, 2, '2019-12-29 01:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (7, 2, '2019-12-29 01:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (8, 2, '2019-12-29 01:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (9, 2, '2019-12-29 01:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (10, 2, '2019-12-29 01:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (11, 2, '2019-12-29 02:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (12, 2, '2019-12-29 02:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (13, 2, '2019-12-29 02:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (14, 2, '2019-12-29 02:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (15, 2, '2019-12-29 02:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (16, 2, '2019-12-29 02:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (17, 2, '2019-12-29 03:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (18, 2, '2019-12-29 03:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (19, 2, '2019-12-29 03:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (20, 2, '2019-12-29 03:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (21, 2, '2019-12-29 03:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (22, 2, '2019-12-29 03:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (23, 2, '2019-12-29 04:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (24, 2, '2019-12-29 04:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (25, 2, '2019-12-29 04:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (26, 2, '2019-12-29 04:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (27, 2, '2019-12-29 04:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (28, 2, '2019-12-29 04:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (29, 2, '2019-12-29 05:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (30, 2, '2019-12-29 05:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (31, 2, '2019-12-29 05:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (32, 2, '2019-12-29 05:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (33, 2, '2019-12-29 05:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (34, 2, '2019-12-29 05:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (35, 2, '2019-12-29 06:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (36, 2, '2019-12-29 06:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (37, 2, '2019-12-29 06:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (38, 2, '2019-12-29 06:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (39, 2, '2019-12-29 06:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (40, 2, '2019-12-29 06:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (41, 2, '2019-12-29 07:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (42, 2, '2019-12-29 07:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (43, 2, '2019-12-29 07:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (44, 2, '2019-12-29 07:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (45, 2, '2019-12-29 07:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (46, 2, '2019-12-29 07:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (47, 2, '2019-12-29 08:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (48, 2, '2019-12-29 08:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (49, 2, '2019-12-29 08:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (50, 2, '2019-12-29 08:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (51, 2, '2019-12-29 08:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (52, 2, '2019-12-29 08:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (53, 2, '2019-12-29 09:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (54, 2, '2019-12-29 09:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (55, 2, '2019-12-29 09:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (56, 2, '2019-12-29 09:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (57, 2, '2019-12-29 09:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (58, 2, '2019-12-29 09:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (59, 2, '2019-12-29 10:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (60, 2, '2019-12-29 10:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (61, 2, '2019-12-29 10:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (62, 2, '2019-12-29 10:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (63, 2, '2019-12-29 10:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (64, 2, '2019-12-29 10:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (65, 2, '2019-12-29 11:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (66, 2, '2019-12-29 11:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (67, 2, '2019-12-29 11:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (68, 2, '2019-12-29 11:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (69, 2, '2019-12-29 11:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (70, 2, '2019-12-29 11:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (71, 2, '2019-12-29 12:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (72, 2, '2019-12-29 12:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (73, 2, '2019-12-29 12:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (74, 2, '2019-12-29 12:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (75, 2, '2019-12-29 12:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (76, 2, '2019-12-29 12:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (77, 2, '2019-12-29 13:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (78, 2, '2019-12-29 13:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (79, 2, '2019-12-29 13:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (80, 2, '2019-12-29 13:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (81, 2, '2019-12-29 13:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (82, 2, '2019-12-29 13:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (83, 2, '2019-12-29 14:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (84, 2, '2019-12-29 14:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (85, 2, '2019-12-29 14:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (86, 2, '2019-12-29 14:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (87, 2, '2019-12-29 14:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (88, 2, '2019-12-29 14:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (89, 2, '2019-12-29 15:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (90, 2, '2019-12-29 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (91, 2, '2019-12-29 15:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (92, 2, '2019-12-29 15:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (93, 2, '2019-12-29 15:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (94, 2, '2019-12-29 15:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (95, 2, '2019-12-29 16:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (96, 2, '2019-12-29 16:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (97, 2, '2019-12-29 16:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (98, 2, '2019-12-29 16:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (99, 2, '2019-12-29 16:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (100, 2, '2019-12-29 16:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (101, 2, '2019-12-29 17:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (102, 2, '2019-12-29 17:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (103, 2, '2019-12-29 17:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (104, 2, '2019-12-29 17:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (105, 2, '2019-12-29 17:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (106, 2, '2019-12-29 17:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (107, 2, '2019-12-29 18:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (108, 2, '2019-12-29 18:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (109, 2, '2019-12-29 18:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (110, 2, '2019-12-29 18:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (111, 2, '2019-12-29 18:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (112, 2, '2019-12-29 18:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (113, 2, '2019-12-29 19:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (114, 2, '2019-12-29 19:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (115, 2, '2019-12-29 19:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (116, 2, '2019-12-29 19:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (117, 2, '2019-12-29 19:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (118, 2, '2019-12-29 19:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (119, 2, '2019-12-29 20:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (120, 2, '2019-12-29 20:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (121, 2, '2019-12-29 20:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (122, 2, '2019-12-29 20:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (123, 2, '2019-12-29 20:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (124, 2, '2019-12-29 20:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (125, 2, '2019-12-29 21:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (126, 2, '2019-12-29 21:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (127, 2, '2019-12-29 21:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (128, 2, '2019-12-29 21:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (129, 2, '2019-12-29 21:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (130, 2, '2019-12-29 21:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (131, 2, '2019-12-29 22:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (132, 2, '2019-12-29 22:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (133, 2, '2019-12-29 22:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (134, 2, '2019-12-29 22:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (135, 2, '2019-12-29 22:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (136, 2, '2019-12-29 22:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (137, 2, '2019-12-29 23:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (138, 2, '2019-12-29 23:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (139, 2, '2019-12-29 23:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (140, 2, '2019-12-29 23:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (141, 2, '2019-12-29 23:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (142, 2, '2019-12-29 23:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (143, 2, '2019-12-30 00:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (144, 2, '2019-12-30 00:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (145, 2, '2019-12-30 00:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (146, 2, '2019-12-30 00:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (147, 2, '2019-12-30 00:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (148, 2, '2019-12-30 00:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (149, 2, '2019-12-30 01:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (150, 2, '2019-12-30 01:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (151, 2, '2019-12-30 01:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (152, 2, '2019-12-30 01:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (153, 2, '2019-12-30 01:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (154, 2, '2019-12-30 01:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (155, 2, '2019-12-30 02:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (156, 2, '2019-12-30 02:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (157, 2, '2019-12-30 02:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (158, 2, '2019-12-30 02:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (159, 2, '2019-12-30 02:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (160, 2, '2019-12-30 02:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (161, 2, '2019-12-30 03:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (162, 2, '2019-12-30 03:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (163, 2, '2019-12-30 03:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (164, 2, '2019-12-30 03:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (165, 2, '2019-12-30 03:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (166, 2, '2019-12-30 03:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (167, 2, '2019-12-30 04:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (168, 2, '2019-12-30 04:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (169, 2, '2019-12-30 04:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (170, 2, '2019-12-30 04:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (171, 2, '2019-12-30 04:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (172, 2, '2019-12-30 04:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (173, 2, '2019-12-30 05:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (174, 2, '2019-12-30 05:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (175, 2, '2019-12-30 05:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (176, 2, '2019-12-30 05:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (177, 2, '2019-12-30 05:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (178, 2, '2019-12-30 05:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (179, 2, '2019-12-30 06:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (180, 2, '2019-12-30 06:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (181, 2, '2019-12-30 06:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (182, 2, '2019-12-30 06:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (183, 2, '2019-12-30 06:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (184, 2, '2019-12-30 06:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (185, 2, '2019-12-30 07:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (186, 2, '2019-12-30 07:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (187, 2, '2019-12-30 07:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (188, 2, '2019-12-30 07:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (189, 2, '2019-12-30 07:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (190, 2, '2019-12-30 07:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (191, 2, '2019-12-30 08:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (192, 2, '2019-12-30 08:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (193, 2, '2019-12-30 08:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (194, 2, '2019-12-30 08:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (195, 2, '2019-12-30 08:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (196, 2, '2019-12-30 08:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (197, 2, '2019-12-30 09:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (198, 2, '2019-12-30 09:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (199, 2, '2019-12-30 09:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (200, 2, '2019-12-30 09:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (201, 2, '2019-12-30 09:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (202, 2, '2019-12-30 09:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (203, 2, '2019-12-30 10:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (204, 2, '2019-12-30 10:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (205, 2, '2019-12-30 10:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (206, 2, '2019-12-30 10:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (207, 2, '2019-12-30 10:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (208, 2, '2019-12-30 10:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (209, 2, '2019-12-30 11:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (210, 2, '2019-12-30 11:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (211, 2, '2019-12-30 11:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (212, 2, '2019-12-30 11:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (213, 2, '2019-12-30 11:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (214, 2, '2019-12-30 11:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (215, 2, '2019-12-30 12:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (216, 2, '2019-12-30 12:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (217, 2, '2019-12-30 12:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (218, 2, '2019-12-30 12:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (219, 2, '2019-12-30 12:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (220, 2, '2019-12-30 12:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (221, 2, '2019-12-30 13:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (222, 2, '2019-12-30 13:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (223, 2, '2019-12-30 13:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (224, 2, '2019-12-30 13:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (225, 2, '2019-12-30 13:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (226, 2, '2019-12-30 13:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (227, 2, '2019-12-30 14:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (228, 2, '2019-12-30 14:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (229, 2, '2019-12-30 14:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (230, 2, '2019-12-30 14:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (231, 2, '2019-12-30 14:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (232, 2, '2019-12-30 14:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (233, 2, '2019-12-30 15:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (234, 2, '2019-12-30 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (235, 2, '2019-12-30 15:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (236, 2, '2019-12-30 15:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (237, 2, '2019-12-30 15:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (238, 2, '2019-12-30 15:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (239, 2, '2019-12-30 16:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (240, 2, '2019-12-30 16:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (241, 2, '2019-12-30 16:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (242, 2, '2019-12-30 16:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (243, 2, '2019-12-30 16:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (244, 2, '2019-12-30 16:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (245, 2, '2019-12-30 17:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (246, 2, '2019-12-30 17:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (247, 2, '2019-12-30 17:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (248, 2, '2019-12-30 17:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (249, 2, '2019-12-30 17:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (250, 2, '2019-12-30 17:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (251, 3, '2019-12-29 22:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (252, 3, '2019-12-29 22:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (253, 3, '2019-12-29 23:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (254, 3, '2019-12-29 23:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (255, 3, '2019-12-29 23:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (256, 3, '2019-12-29 23:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (257, 3, '2019-12-30 00:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (258, 3, '2019-12-30 00:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (259, 3, '2019-12-30 00:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (260, 3, '2019-12-30 00:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (261, 3, '2019-12-30 01:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (262, 3, '2019-12-30 01:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (263, 3, '2019-12-30 01:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (264, 3, '2019-12-30 01:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (265, 3, '2019-12-30 02:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (266, 3, '2019-12-30 02:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (267, 3, '2019-12-30 02:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (268, 3, '2019-12-30 02:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (269, 3, '2019-12-30 03:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (270, 3, '2019-12-30 03:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (271, 3, '2019-12-30 03:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (272, 3, '2019-12-30 03:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (273, 3, '2019-12-30 04:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (274, 3, '2019-12-30 04:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (275, 3, '2019-12-30 04:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (276, 3, '2019-12-30 04:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (277, 3, '2019-12-30 05:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (278, 3, '2019-12-30 05:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (279, 3, '2019-12-30 05:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (280, 3, '2019-12-30 05:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (281, 3, '2019-12-30 06:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (282, 3, '2019-12-30 06:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (283, 3, '2019-12-30 06:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (284, 3, '2019-12-30 06:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (285, 3, '2019-12-30 07:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (286, 3, '2019-12-30 07:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (287, 3, '2019-12-30 07:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (288, 3, '2019-12-30 07:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (289, 3, '2019-12-30 08:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (290, 3, '2019-12-30 08:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (291, 3, '2019-12-30 08:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (292, 3, '2019-12-30 08:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (293, 3, '2019-12-30 09:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (294, 3, '2019-12-30 09:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (295, 3, '2019-12-30 09:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (296, 3, '2019-12-30 09:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (297, 3, '2019-12-30 10:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (298, 3, '2019-12-30 10:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (299, 3, '2019-12-30 10:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (300, 3, '2019-12-30 10:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (301, 3, '2019-12-30 11:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (302, 3, '2019-12-30 11:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (303, 3, '2019-12-30 11:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (304, 3, '2019-12-30 11:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (305, 3, '2019-12-30 12:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (306, 3, '2019-12-30 12:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (307, 3, '2019-12-30 12:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (308, 3, '2019-12-30 12:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (309, 3, '2019-12-30 13:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (310, 3, '2019-12-30 13:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (311, 3, '2019-12-30 13:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (312, 3, '2019-12-30 13:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (313, 3, '2019-12-30 14:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (314, 3, '2019-12-30 14:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (315, 3, '2019-12-30 14:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (316, 3, '2019-12-30 14:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (317, 3, '2019-12-30 15:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (318, 3, '2019-12-30 15:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (319, 3, '2019-12-30 15:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (320, 3, '2019-12-30 15:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (321, 3, '2019-12-30 16:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (322, 3, '2019-12-30 16:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (323, 3, '2019-12-30 16:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (324, 3, '2019-12-30 16:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (325, 3, '2019-12-30 17:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (326, 3, '2019-12-30 17:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (327, 3, '2019-12-30 17:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (328, 3, '2019-12-30 17:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (329, 4, '2019-12-29 01:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (330, 4, '2019-12-29 02:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (331, 4, '2019-12-29 03:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (332, 4, '2019-12-29 04:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (333, 4, '2019-12-29 05:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (334, 4, '2019-12-29 06:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (335, 4, '2019-12-29 07:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (336, 4, '2019-12-29 08:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (337, 4, '2019-12-29 09:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (338, 4, '2019-12-29 10:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (339, 4, '2019-12-29 11:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (340, 4, '2019-12-29 12:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (341, 4, '2019-12-29 13:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (342, 4, '2019-12-29 14:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (343, 4, '2019-12-29 15:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (344, 4, '2019-12-29 16:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (345, 4, '2019-12-29 17:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (346, 4, '2019-12-29 18:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (347, 4, '2019-12-29 19:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (348, 4, '2019-12-29 20:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (349, 4, '2019-12-29 21:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (350, 4, '2019-12-29 22:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (351, 4, '2019-12-29 23:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (352, 4, '2019-12-30 00:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (353, 4, '2019-12-30 01:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (354, 4, '2019-12-30 02:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (355, 4, '2019-12-30 03:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (356, 4, '2019-12-30 04:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (357, 4, '2019-12-30 05:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (358, 4, '2019-12-30 06:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (359, 4, '2019-12-30 07:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (360, 4, '2019-12-30 08:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (361, 4, '2019-12-30 09:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (362, 4, '2019-12-30 10:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (363, 4, '2019-12-30 11:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (364, 4, '2019-12-30 12:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (365, 4, '2019-12-30 13:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (366, 4, '2019-12-30 14:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (367, 4, '2019-12-30 15:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (368, 4, '2019-12-30 16:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (369, 4, '2019-12-30 17:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (370, 4, '2019-12-30 18:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (371, 5, '2020-01-03 20:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (372, 6, '2020-01-01 07:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (373, 7, '2019-12-31 08:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (374, 8, '2019-12-30 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (375, 8, '2019-12-31 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (376, 9, '2020-01-04 07:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (377, 10, '2020-01-01 00:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (378, 11, '2019-12-29 19:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (379, 11, '2019-12-29 19:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (380, 11, '2019-12-29 19:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (381, 11, '2019-12-29 19:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (382, 11, '2019-12-29 19:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (383, 11, '2019-12-29 19:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (384, 11, '2019-12-29 19:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (385, 11, '2019-12-29 19:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (386, 11, '2019-12-29 19:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (387, 11, '2019-12-29 19:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (388, 11, '2019-12-29 19:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (389, 11, '2019-12-29 19:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (390, 11, '2019-12-29 20:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (391, 11, '2019-12-29 20:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (392, 11, '2019-12-29 20:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (393, 11, '2019-12-29 20:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (394, 11, '2019-12-29 20:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (395, 11, '2019-12-29 20:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (396, 11, '2019-12-29 20:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (397, 11, '2019-12-29 20:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (398, 11, '2019-12-29 20:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (399, 11, '2019-12-29 20:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (400, 11, '2019-12-29 20:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (401, 11, '2019-12-29 20:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (402, 11, '2019-12-29 21:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (403, 11, '2019-12-29 21:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (404, 11, '2019-12-29 21:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (405, 11, '2019-12-29 21:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (406, 11, '2019-12-29 21:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (407, 11, '2019-12-29 21:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (408, 11, '2019-12-29 21:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (409, 11, '2019-12-29 21:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (410, 11, '2019-12-29 21:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (411, 11, '2019-12-29 21:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (412, 11, '2019-12-29 21:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (413, 11, '2019-12-29 21:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (414, 11, '2019-12-29 22:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (415, 11, '2019-12-29 22:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (416, 11, '2019-12-29 22:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (417, 11, '2019-12-29 22:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (418, 11, '2019-12-29 22:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (419, 11, '2019-12-29 22:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (420, 11, '2019-12-29 22:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (421, 11, '2019-12-29 22:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (422, 11, '2019-12-29 22:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (423, 11, '2019-12-29 22:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (424, 11, '2019-12-29 22:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (425, 11, '2019-12-29 22:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (426, 11, '2019-12-29 23:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (427, 11, '2019-12-29 23:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (428, 11, '2019-12-29 23:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (429, 11, '2019-12-29 23:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (430, 11, '2019-12-29 23:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (431, 11, '2019-12-29 23:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (432, 11, '2019-12-29 23:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (433, 11, '2019-12-29 23:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (434, 11, '2019-12-29 23:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (435, 11, '2019-12-29 23:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (436, 11, '2019-12-29 23:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (437, 11, '2019-12-29 23:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (438, 11, '2019-12-30 00:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (439, 11, '2019-12-30 00:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (440, 11, '2019-12-30 00:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (441, 11, '2019-12-30 00:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (442, 11, '2019-12-30 00:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (443, 11, '2019-12-30 00:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (444, 11, '2019-12-30 00:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (445, 11, '2019-12-30 00:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (446, 11, '2019-12-30 00:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (447, 11, '2019-12-30 00:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (448, 11, '2019-12-30 00:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (449, 11, '2019-12-30 00:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (450, 11, '2019-12-30 01:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (451, 11, '2019-12-30 01:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (452, 11, '2019-12-30 01:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (453, 11, '2019-12-30 01:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (454, 11, '2019-12-30 01:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (455, 11, '2019-12-30 01:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (456, 11, '2019-12-30 01:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (457, 11, '2019-12-30 01:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (458, 11, '2019-12-30 01:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (459, 11, '2019-12-30 01:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (460, 11, '2019-12-30 01:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (461, 11, '2019-12-30 01:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (462, 11, '2019-12-30 02:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (463, 11, '2019-12-30 02:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (464, 11, '2019-12-30 02:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (465, 11, '2019-12-30 02:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (466, 11, '2019-12-30 02:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (467, 11, '2019-12-30 02:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (468, 11, '2019-12-30 02:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (469, 11, '2019-12-30 02:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (470, 11, '2019-12-30 02:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (471, 11, '2019-12-30 02:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (472, 11, '2019-12-30 02:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (473, 11, '2019-12-30 02:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (474, 11, '2019-12-30 03:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (475, 11, '2019-12-30 03:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (476, 11, '2019-12-30 03:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (477, 11, '2019-12-30 03:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (478, 11, '2019-12-30 03:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (479, 11, '2019-12-30 03:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (480, 11, '2019-12-30 03:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (481, 11, '2019-12-30 03:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (482, 11, '2019-12-30 03:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (483, 11, '2019-12-30 03:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (484, 11, '2019-12-30 03:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (485, 11, '2019-12-30 03:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (486, 11, '2019-12-30 04:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (487, 11, '2019-12-30 04:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (488, 11, '2019-12-30 04:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (489, 11, '2019-12-30 04:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (490, 11, '2019-12-30 04:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (491, 11, '2019-12-30 04:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (492, 11, '2019-12-30 04:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (493, 11, '2019-12-30 04:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (494, 11, '2019-12-30 04:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (495, 11, '2019-12-30 04:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (496, 11, '2019-12-30 04:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (497, 11, '2019-12-30 04:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (498, 11, '2019-12-30 05:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (499, 11, '2019-12-30 05:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (500, 11, '2019-12-30 05:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (501, 11, '2019-12-30 05:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (502, 11, '2019-12-30 05:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (503, 11, '2019-12-30 05:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (504, 11, '2019-12-30 05:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (505, 11, '2019-12-30 05:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (506, 11, '2019-12-30 05:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (507, 11, '2019-12-30 05:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (508, 11, '2019-12-30 05:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (509, 11, '2019-12-30 05:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (510, 11, '2019-12-30 06:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (511, 11, '2019-12-30 06:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (512, 11, '2019-12-30 06:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (513, 11, '2019-12-30 06:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (514, 11, '2019-12-30 06:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (515, 11, '2019-12-30 06:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (516, 11, '2019-12-30 06:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (517, 11, '2019-12-30 06:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (518, 11, '2019-12-30 06:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (519, 11, '2019-12-30 06:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (520, 11, '2019-12-30 06:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (521, 11, '2019-12-30 06:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (522, 11, '2019-12-30 07:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (523, 11, '2019-12-30 07:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (524, 11, '2019-12-30 07:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (525, 11, '2019-12-30 07:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (526, 11, '2019-12-30 07:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (527, 11, '2019-12-30 07:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (528, 11, '2019-12-30 07:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (529, 11, '2019-12-30 07:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (530, 11, '2019-12-30 07:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (531, 11, '2019-12-30 07:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (532, 11, '2019-12-30 07:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (533, 11, '2019-12-30 07:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (534, 11, '2019-12-30 08:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (535, 11, '2019-12-30 08:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (536, 11, '2019-12-30 08:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (537, 11, '2019-12-30 08:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (538, 11, '2019-12-30 08:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (539, 11, '2019-12-30 08:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (540, 11, '2019-12-30 08:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (541, 11, '2019-12-30 08:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (542, 11, '2019-12-30 08:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (543, 11, '2019-12-30 08:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (544, 11, '2019-12-30 08:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (545, 11, '2019-12-30 08:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (546, 11, '2019-12-30 09:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (547, 11, '2019-12-30 09:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (548, 11, '2019-12-30 09:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (549, 11, '2019-12-30 09:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (550, 11, '2019-12-30 09:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (551, 11, '2019-12-30 09:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (552, 11, '2019-12-30 09:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (553, 11, '2019-12-30 09:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (554, 11, '2019-12-30 09:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (555, 11, '2019-12-30 09:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (556, 11, '2019-12-30 09:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (557, 11, '2019-12-30 09:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (558, 11, '2019-12-30 10:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (559, 11, '2019-12-30 10:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (560, 11, '2019-12-30 10:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (561, 11, '2019-12-30 10:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (562, 11, '2019-12-30 10:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (563, 11, '2019-12-30 10:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (564, 11, '2019-12-30 10:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (565, 11, '2019-12-30 10:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (566, 11, '2019-12-30 10:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (567, 11, '2019-12-30 10:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (568, 11, '2019-12-30 10:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (569, 11, '2019-12-30 10:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (570, 11, '2019-12-30 11:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (571, 11, '2019-12-30 11:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (572, 11, '2019-12-30 11:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (573, 11, '2019-12-30 11:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (574, 11, '2019-12-30 11:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (575, 11, '2019-12-30 11:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (576, 11, '2019-12-30 11:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (577, 11, '2019-12-30 11:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (578, 11, '2019-12-30 11:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (579, 11, '2019-12-30 11:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (580, 11, '2019-12-30 11:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (581, 11, '2019-12-30 11:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (582, 11, '2019-12-30 12:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (583, 11, '2019-12-30 12:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (584, 11, '2019-12-30 12:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (585, 11, '2019-12-30 12:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (586, 11, '2019-12-30 12:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (587, 11, '2019-12-30 12:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (588, 11, '2019-12-30 12:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (589, 11, '2019-12-30 12:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (590, 11, '2019-12-30 12:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (591, 11, '2019-12-30 12:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (592, 11, '2019-12-30 12:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (593, 11, '2019-12-30 12:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (594, 11, '2019-12-30 13:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (595, 11, '2019-12-30 13:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (596, 11, '2019-12-30 13:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (597, 11, '2019-12-30 13:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (598, 11, '2019-12-30 13:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (599, 11, '2019-12-30 13:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (600, 11, '2019-12-30 13:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (601, 11, '2019-12-30 13:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (602, 11, '2019-12-30 13:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (603, 11, '2019-12-30 13:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (604, 11, '2019-12-30 13:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (605, 11, '2019-12-30 13:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (606, 11, '2019-12-30 14:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (607, 11, '2019-12-30 14:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (608, 11, '2019-12-30 14:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (609, 11, '2019-12-30 14:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (610, 11, '2019-12-30 14:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (611, 11, '2019-12-30 14:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (612, 11, '2019-12-30 14:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (613, 11, '2019-12-30 14:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (614, 11, '2019-12-30 14:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (615, 11, '2019-12-30 14:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (616, 11, '2019-12-30 14:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (617, 11, '2019-12-30 14:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (618, 11, '2019-12-30 15:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (619, 11, '2019-12-30 15:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (620, 11, '2019-12-30 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (621, 11, '2019-12-30 15:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (622, 11, '2019-12-30 15:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (623, 11, '2019-12-30 15:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (624, 11, '2019-12-30 15:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (625, 11, '2019-12-30 15:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (626, 11, '2019-12-30 15:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (627, 11, '2019-12-30 15:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (628, 11, '2019-12-30 15:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (629, 11, '2019-12-30 15:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (630, 11, '2019-12-30 16:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (631, 11, '2019-12-30 16:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (632, 11, '2019-12-30 16:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (633, 11, '2019-12-30 16:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (634, 11, '2019-12-30 16:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (635, 11, '2019-12-30 16:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (636, 11, '2019-12-30 16:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (637, 11, '2019-12-30 16:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (638, 11, '2019-12-30 16:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (639, 11, '2019-12-30 16:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (640, 11, '2019-12-30 16:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (641, 11, '2019-12-30 16:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (642, 11, '2019-12-30 17:00:00', NULL, NULL, NULL, NULL, 0, NULL),
       (643, 11, '2019-12-30 17:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (644, 11, '2019-12-30 17:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (645, 11, '2019-12-30 17:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (646, 11, '2019-12-30 17:20:00', NULL, NULL, NULL, NULL, 0, NULL),
       (647, 11, '2019-12-30 17:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (648, 11, '2019-12-30 17:30:00', NULL, NULL, NULL, NULL, 0, NULL),
       (649, 11, '2019-12-30 17:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (650, 11, '2019-12-30 17:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (651, 11, '2019-12-30 17:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (652, 11, '2019-12-30 17:50:00', NULL, NULL, NULL, NULL, 0, NULL),
       (653, 12, '2020-01-04 07:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (654, 13, '2019-12-28 18:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (655, 13, '2019-12-28 19:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (656, 13, '2019-12-28 19:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (657, 13, '2019-12-28 19:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (658, 13, '2019-12-28 19:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (659, 13, '2019-12-28 20:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (660, 13, '2019-12-28 20:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (661, 13, '2019-12-28 20:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (662, 13, '2019-12-28 20:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (663, 13, '2019-12-28 21:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (664, 13, '2019-12-28 21:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (665, 13, '2019-12-28 21:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (666, 13, '2019-12-28 21:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (667, 13, '2019-12-28 22:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (668, 13, '2019-12-28 22:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (669, 13, '2019-12-28 22:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (670, 13, '2019-12-28 22:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (671, 13, '2019-12-28 23:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (672, 13, '2019-12-28 23:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (673, 13, '2019-12-28 23:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (674, 13, '2019-12-28 23:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (675, 13, '2019-12-29 00:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (676, 13, '2019-12-29 00:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (677, 13, '2019-12-29 00:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (678, 13, '2019-12-29 00:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (679, 13, '2019-12-29 01:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (680, 13, '2019-12-29 01:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (681, 13, '2019-12-29 01:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (682, 13, '2019-12-29 01:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (683, 13, '2019-12-29 02:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (684, 13, '2019-12-29 02:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (685, 13, '2019-12-29 02:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (686, 13, '2019-12-29 02:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (687, 13, '2019-12-29 03:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (688, 13, '2019-12-29 03:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (689, 13, '2019-12-29 03:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (690, 13, '2019-12-29 03:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (691, 13, '2019-12-29 04:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (692, 13, '2019-12-29 04:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (693, 13, '2019-12-29 04:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (694, 13, '2019-12-29 04:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (695, 13, '2019-12-29 05:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (696, 13, '2019-12-29 05:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (697, 13, '2019-12-29 05:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (698, 13, '2019-12-29 05:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (699, 13, '2019-12-29 06:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (700, 13, '2019-12-29 06:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (701, 13, '2019-12-29 06:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (702, 13, '2019-12-29 06:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (703, 13, '2019-12-29 07:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (704, 13, '2019-12-29 07:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (705, 13, '2019-12-29 07:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (706, 13, '2019-12-29 07:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (707, 13, '2019-12-29 08:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (708, 13, '2019-12-29 08:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (709, 13, '2019-12-29 08:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (710, 13, '2019-12-29 08:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (711, 13, '2019-12-29 09:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (712, 13, '2019-12-29 09:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (713, 13, '2019-12-29 09:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (714, 13, '2019-12-29 09:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (715, 13, '2019-12-29 10:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (716, 13, '2019-12-29 10:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (717, 13, '2019-12-29 10:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (718, 13, '2019-12-29 10:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (719, 13, '2019-12-29 11:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (720, 13, '2019-12-29 11:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (721, 13, '2019-12-29 11:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (722, 13, '2019-12-29 11:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (723, 13, '2019-12-29 12:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (724, 13, '2019-12-29 12:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (725, 13, '2019-12-29 12:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (726, 13, '2019-12-29 12:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (727, 13, '2019-12-29 13:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (728, 13, '2019-12-29 13:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (729, 13, '2019-12-29 13:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (730, 13, '2019-12-29 13:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (731, 13, '2019-12-29 14:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (732, 13, '2019-12-29 14:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (733, 13, '2019-12-29 14:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (734, 13, '2019-12-29 14:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (735, 13, '2019-12-29 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (736, 13, '2019-12-29 15:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (737, 13, '2019-12-29 15:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (738, 13, '2019-12-29 15:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (739, 13, '2019-12-29 16:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (740, 13, '2019-12-29 16:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (741, 13, '2019-12-29 16:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (742, 13, '2019-12-29 16:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (743, 13, '2019-12-29 17:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (744, 13, '2019-12-29 17:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (745, 13, '2019-12-29 17:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (746, 13, '2019-12-29 17:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (747, 13, '2019-12-29 18:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (748, 13, '2019-12-29 18:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (749, 13, '2019-12-29 18:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (750, 13, '2019-12-29 18:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (751, 13, '2019-12-29 19:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (752, 13, '2019-12-29 19:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (753, 13, '2019-12-29 19:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (754, 13, '2019-12-29 19:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (755, 13, '2019-12-29 20:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (756, 13, '2019-12-29 20:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (757, 13, '2019-12-29 20:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (758, 13, '2019-12-29 20:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (759, 13, '2019-12-29 21:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (760, 13, '2019-12-29 21:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (761, 13, '2019-12-29 21:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (762, 13, '2019-12-29 21:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (763, 13, '2019-12-29 22:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (764, 13, '2019-12-29 22:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (765, 13, '2019-12-29 22:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (766, 13, '2019-12-29 22:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (767, 13, '2019-12-29 23:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (768, 13, '2019-12-29 23:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (769, 13, '2019-12-29 23:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (770, 13, '2019-12-29 23:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (771, 13, '2019-12-30 00:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (772, 13, '2019-12-30 00:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (773, 13, '2019-12-30 00:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (774, 13, '2019-12-30 00:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (775, 13, '2019-12-30 01:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (776, 13, '2019-12-30 01:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (777, 13, '2019-12-30 01:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (778, 13, '2019-12-30 01:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (779, 13, '2019-12-30 02:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (780, 13, '2019-12-30 02:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (781, 13, '2019-12-30 02:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (782, 13, '2019-12-30 02:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (783, 13, '2019-12-30 03:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (784, 13, '2019-12-30 03:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (785, 13, '2019-12-30 03:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (786, 13, '2019-12-30 03:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (787, 13, '2019-12-30 04:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (788, 13, '2019-12-30 04:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (789, 13, '2019-12-30 04:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (790, 13, '2019-12-30 04:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (791, 13, '2019-12-30 05:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (792, 13, '2019-12-30 05:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (793, 13, '2019-12-30 05:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (794, 13, '2019-12-30 05:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (795, 13, '2019-12-30 06:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (796, 13, '2019-12-30 06:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (797, 13, '2019-12-30 06:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (798, 13, '2019-12-30 06:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (799, 13, '2019-12-30 07:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (800, 13, '2019-12-30 07:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (801, 13, '2019-12-30 07:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (802, 13, '2019-12-30 07:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (803, 13, '2019-12-30 08:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (804, 13, '2019-12-30 08:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (805, 13, '2019-12-30 08:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (806, 13, '2019-12-30 08:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (807, 13, '2019-12-30 09:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (808, 13, '2019-12-30 09:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (809, 13, '2019-12-30 09:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (810, 13, '2019-12-30 09:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (811, 13, '2019-12-30 10:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (812, 13, '2019-12-30 10:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (813, 13, '2019-12-30 10:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (814, 13, '2019-12-30 10:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (815, 13, '2019-12-30 11:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (816, 13, '2019-12-30 11:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (817, 13, '2019-12-30 11:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (818, 13, '2019-12-30 11:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (819, 13, '2019-12-30 12:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (820, 13, '2019-12-30 12:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (821, 13, '2019-12-30 12:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (822, 13, '2019-12-30 12:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (823, 13, '2019-12-30 13:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (824, 13, '2019-12-30 13:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (825, 13, '2019-12-30 13:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (826, 13, '2019-12-30 13:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (827, 13, '2019-12-30 14:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (828, 13, '2019-12-30 14:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (829, 13, '2019-12-30 14:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (830, 13, '2019-12-30 14:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (831, 13, '2019-12-30 15:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (832, 13, '2019-12-30 15:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (833, 13, '2019-12-30 15:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (834, 13, '2019-12-30 15:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (835, 13, '2019-12-30 16:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (836, 13, '2019-12-30 16:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (837, 13, '2019-12-30 16:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (838, 13, '2019-12-30 16:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (839, 13, '2019-12-30 17:10:00', NULL, NULL, NULL, NULL, 0, NULL),
       (840, 13, '2019-12-30 17:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (841, 13, '2019-12-30 17:40:00', NULL, NULL, NULL, NULL, 0, NULL),
       (842, 13, '2019-12-30 17:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (843, 14, '2020-01-02 15:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (844, 15, '2020-01-01 07:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (845, 16, '2020-01-02 22:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (846, 17, '2019-12-30 08:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (847, 17, '2019-12-30 08:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (848, 17, '2019-12-30 08:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (849, 17, '2019-12-30 09:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (850, 17, '2019-12-30 09:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (851, 17, '2019-12-30 09:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (852, 17, '2019-12-30 09:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (853, 17, '2019-12-30 09:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (854, 17, '2019-12-30 09:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (855, 17, '2019-12-30 10:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (856, 17, '2019-12-30 10:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (857, 17, '2019-12-30 10:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (858, 17, '2019-12-30 10:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (859, 17, '2019-12-30 10:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (860, 17, '2019-12-30 10:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (861, 17, '2019-12-30 11:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (862, 17, '2019-12-30 11:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (863, 17, '2019-12-30 11:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (864, 17, '2019-12-30 11:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (865, 17, '2019-12-30 11:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (866, 17, '2019-12-30 11:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (867, 17, '2019-12-30 12:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (868, 17, '2019-12-30 12:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (869, 17, '2019-12-30 12:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (870, 17, '2019-12-30 12:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (871, 17, '2019-12-30 12:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (872, 17, '2019-12-30 12:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (873, 17, '2019-12-30 13:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (874, 17, '2019-12-30 13:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (875, 17, '2019-12-30 13:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (876, 17, '2019-12-30 13:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (877, 17, '2019-12-30 13:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (878, 17, '2019-12-30 13:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (879, 17, '2019-12-30 14:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (880, 17, '2019-12-30 14:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (881, 17, '2019-12-30 14:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (882, 17, '2019-12-30 14:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (883, 17, '2019-12-30 14:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (884, 17, '2019-12-30 14:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (885, 17, '2019-12-30 15:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (886, 17, '2019-12-30 15:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (887, 17, '2019-12-30 15:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (888, 17, '2019-12-30 15:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (889, 17, '2019-12-30 15:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (890, 17, '2019-12-30 15:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (891, 17, '2019-12-30 16:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (892, 17, '2019-12-30 16:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (893, 17, '2019-12-30 16:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (894, 17, '2019-12-30 16:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (895, 17, '2019-12-30 16:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (896, 17, '2019-12-30 16:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (897, 17, '2019-12-30 17:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (898, 17, '2019-12-30 17:15:00', NULL, NULL, NULL, NULL, 0, NULL),
       (899, 17, '2019-12-30 17:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (900, 17, '2019-12-30 17:35:00', NULL, NULL, NULL, NULL, 0, NULL),
       (901, 17, '2019-12-30 17:45:00', NULL, NULL, NULL, NULL, 0, NULL),
       (902, 17, '2019-12-30 17:55:00', NULL, NULL, NULL, NULL, 0, NULL),
       (903, 18, '2020-01-04 00:25:00', NULL, NULL, NULL, NULL, 0, NULL),
       (904, 19, '2019-12-31 07:05:00', NULL, NULL, NULL, NULL, 0, NULL),
       (905, 20, '2019-12-31 21:45:00', NULL, NULL, NULL, NULL, 0, NULL);
/*!40000 ALTER TABLE `supla_scheduled_executions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_temperature_log`
--

DROP TABLE IF EXISTS `supla_temperature_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_temperature_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `temperature` decimal(8,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id_idx` (`channel_id`),
  KEY `date_idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_temperature_log`
--

LOCK TABLES `supla_temperature_log` WRITE;
/*!40000 ALTER TABLE `supla_temperature_log` DISABLE KEYS */;
INSERT INTO `supla_temperature_log` VALUES (NULL, 2, '2020-04-30 14:32:21', 38),(NULL, 2, '2020-04-30 14:32:21', 38),(NULL, 2, '2020-04-30 14:42:21', 38);
/*!40000 ALTER TABLE `supla_temperature_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_temphumidity_log`
--

DROP TABLE IF EXISTS `supla_temphumidity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_temphumidity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `temperature` decimal(8,4) NOT NULL,
  `humidity` decimal(8,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id_idx` (`channel_id`),
  KEY `date_idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_temphumidity_log`
--

LOCK TABLES `supla_temphumidity_log` WRITE;
/*!40000 ALTER TABLE `supla_temphumidity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_temphumidity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_thermostat_log`
--

DROP TABLE IF EXISTS `supla_thermostat_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_thermostat_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `on` tinyint(1) NOT NULL,
  `measured_temperature` decimal(5,2) NOT NULL,
  `preset_temperature` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id_idx` (`channel_id`),
  KEY `date_idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_thermostat_log`
--

LOCK TABLES `supla_thermostat_log` WRITE;
/*!40000 ALTER TABLE `supla_thermostat_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_thermostat_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_user`
--

DROP TABLE IF EXISTS `supla_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_unique_id` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `long_unique_id` char(200) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `reg_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `oauth_compat_username` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'For backward compatibility purpose',
  `oauth_compat_password` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'For backward compatibility purpose',
  `limit_direct_link` int(11) NOT NULL DEFAULT '50',
  `limit_oauth_client` int(11) NOT NULL DEFAULT '20',
  `locale` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_removal_requested_at` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_71BAEAC6E7927C74` (`email`),
  UNIQUE KEY `UNIQ_71BAEAC69DAF5974` (`short_unique_id`),
  UNIQUE KEY `UNIQ_71BAEAC6AB4C1E2D` (`long_unique_id`),
  KEY `client_reg_enabled_idx` (`client_reg_enabled`),
  KEY `iodevice_reg_enabled_idx` (`iodevice_reg_enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_user`
--

LOCK TABLES `supla_user` WRITE;
/*!40000 ALTER TABLE `supla_user` DISABLE KEYS */;
INSERT INTO `supla_user`
VALUES (1, '9e536fa0d8f34edeac6626cf0de8ca2b',
        '56b195ab8e4195a36847e05f6fbb2025743fa318cc1fe05556557fa5106fb3806c28b24b6b9e5f9dbeeff0eb6a69a6d654faa7f11d553989ceb4b6654321a6bc1434254ec24ae5ef20efc6df1b12f0985bc82a3c965fbd2c6c70ca812b16bd9fd76a643d',
        'gv7z5aenjy0w4k4cs8os00c8gw0csg4', 'user@supla.org', '$2y$13$xfiVhmW/Htpxv8qORuyZ6OjoGBsv8d8ZuDn1GXGPKn0LYDeBrWwVm', 1,
        '2019-12-28 17:48:19', NULL, NULL, 10, 10, 100, 200, 'Europe/Berlin', 20, NULL, '2020-01-04 17:48:19', '2020-01-04 17:48:19', 20,
        10, 1, 1, NULL, NULL, 50, 20, 'en', NULL),
       (2, '1314edb274896a419ab44295c03c0891',
        'f1a97bd5cfc438d70cc859e968daa6c88d6352335ad1697681fe4e29a2b3cb7c6a18333da02c9345690ddd9424800eb158ea57366715835477e2f935c6e8eb7fd56dafb12cffa94a3d72c8a12edbe28e3c1247a62d7c1fccff78f3836ee4b9edcd9e762a',
        '4psilw99sl4wg0wk8swk8kos40c44gs', 'supler@supla.org', '$2y$13$k4FAMVxnaDg2S9q9ABmh7.gkY/MQgPvcE3R4qBzSel5d9EB8pv/9C', 1,
        '2019-12-28 17:48:19', NULL, NULL, 10, 10, 100, 200, 'Europe/Berlin', 20, NULL, '2020-01-04 17:48:20', '2020-01-04 17:48:20', 20,
        10, 1, 1, NULL, NULL, 50, 20, '', NULL);
/*!40000 ALTER TABLE `supla_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_user_icons`
--

DROP TABLE IF EXISTS `supla_user_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_user_icons`(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `func` int(11) NOT NULL,
  `image1` longblob NOT NULL,
  `image2` longblob,
  `image3` longblob,
  `image4` longblob,
  PRIMARY KEY (`id`),
  KEY `IDX_27B32ACA76ED395` (`user_id`),
  CONSTRAINT `FK_EEB07467A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_user_icons`
--

LOCK TABLES `supla_user_icons` WRITE;
/*!40000 ALTER TABLE `supla_user_icons` DISABLE KEYS */;
INSERT INTO `supla_user_icons`
VALUES (1, 1, 310,
        _binary '�PNG\r\n\Z\n\0\0\0\rIHDR\0\0\0\�\0\0\0�\0\0M@\�\�\0\0\0	pHYs\0\0\�\0\0\��+\0\0 \0IDATx�\�y�eW]\��]kOg�sN�]U\�\�s�\�t�3\�21	WAQ@�\��\�}\\��^}p?�y�\"\�UTЏ\"�b\�E��B&B I\'\�N����\�\�\Z\�|\�\�{��\�Z\�T�\�\�\����\�\�\��}>I�>{\�5�\�a�%t�5\02&%@H\0��6Q\nڋi#(\���i\��\��T�\�\\�\�8�E\�IJ\������j�G�R(r)\��\�A\��\�\�\�A��E�8~ĉ\�3�v\�Zi;\�\�u����\"���C\�	���\�u�}(>�v\�xa�D@X\�@ɇB�	#�/TdD�<�Ѐ\�\���D�\�7?�wۆ$?�7R;\�Yh\�:�\�?\�\�\�?	B+|\���0�A\�1��|��>h�\�f\��I!�\�P=d.e6�1>C��s�)YP�\"�t�<-\�\"A�$,\�[ �Od\�\�R�F��闑\�����B�@\�3\�mP�.�pj�S\�\��\�t\���en�����uW2�\�E�j�Mҍ/�\0�pl�����s�\�k_\�I5ɺ����s\�U��o�\�#g�LL��F&9�\����\r[\�{�k9Qz��|�n�u\�G�Y?�/�\�\�^�GH�0\����|\�\��ل4E*\�\�r�����\�G\�&\�\�C�ґ�\\�f���*��rɮ�hm����iF&G�5�\�\�F:Nt�z����\�\��k\�\��=˯�\�6n���\��\�ΰ0{�ѩKy\�\�\�@nTp��\�\���J\�-5�L	�?��o�\��#A\�Tk��-$_�\�(\�t�\�B�3T\�f[�\��\�<K\�։\�a\�T�p�DP�\�^�P>�\�uI�\�\���č�Nr$HԛU<��\�\�\��\�L[�\�c�|!��w��t�O��w3��]�s�+��t\Z$\Z\�%$R\�\0E@�XD��0IȠH@�߬Ӛ;�̗\���t��\Z_7\�\�M�\�|#M��jR\�9˼�T&\�R�!�\�Jw\ru�@�B\��L�bRK�8�oyL�\\\�/�ׯ��\'�\�r|D�Yjv){x\n\��a$�fu�@x\0�X\�\�C\"��\�c�%��o�m��\�d}.\��IZ1\�˔��̩�nۧ�t)�!��X��Cr*es�\�\�uU��x=B��G�11�\n|\�\�M:w�|TdI-�P]\�\��!nz�5lj�&MS���Tk\Z���Χ\�|\�o�/�d#*���\"5�=Ix\��q:��tg�]�R\Z�\�\�5\�\�\��NΌma���N;��\�UO��\rz\�\���=L\�Z��\0�RZmŽ0��ç��Lo��CMʕ\n\�^=Eq)%8z����_aˎ]\�Ln`\�k�\�fϜ$*\����35]���H�\�FQ)@�I|���1�a;]��f]������u\�\��٧y\����\�\�?fl\�$_q1������BH#E�cۉ-PJ��@�>���|\�>\�\�\�\r\�x[�mG��Ss\�\�w�f�}\�^.�\�\n��F�_\Z1�\0�趖HIN�\�I?�HR� e@;V\�/,Ш�پe#:�B�LPJ#e�\�C Acԇ�+��I�z�<b�TJ[��h�: ]��DH4y�86c�M!�\�)��#D�\��\��m<u�ъB(/	�:\�_\�(\�\"^\Z\�Z)��\�.\'�=����� ��	a\�$�F��|��u3��F\�<\Z��I��\�	:@���E\�\�\��EW\�Q�\�tbL4HECD|�;\�\�n����\�ͯ���~�\�cCĳ\�\������\�l^?E�U%\�WH�@\�\��\�,�\0?m��\ny#/5$\�G�T���.\�ɭ�\��<5\�旞GR��etf�Fa���\�s@�@��\�=C\�ZK�i5\�ry>�g�S.y|�\�\��˜�I^�\��I7W\�ر\'ر\�\"�϶\�69ɰX\�E�2?}݋�v�Ғl#�)2\������\�\��pO[\�\�\�1�\�~|߷HED\���\�C�q�\�d\"7-�۰8\�ϼ���<��|\�n\��&_\Zgz\�B�y\�\�^A!\0I\�\�T�;=���|\'_��;��w��d�\�\�\'Ǚ��!�\����eF\��\�\��˯e\�5?E �t\�:I�\��\��� R�M\'A.\�]?y-Ϟbۅנkm.۱�z\0�]b���\���暟㺋/cV\�Q���3!i^\�#\0�\��ϵ/�m� j�K:(�\�\�;�4x��S�%N�>ŉ�*C\�2���O��\�q\�S��x�$���M�H�\�K�\"dX48�t�\�_;M>A\rWÔ�_\�+�\�\�\"\'�E*��}\�K�Z��\�;\�\�\�B\�\�?�uH�x�G\�7�J��\"��D( \n>r�(����c ��s�\�X�\�\rXJ!�n�Ȧ|�s��,--�>]!�@Ʉ\�S�B�(H\�۶n��d> 4�B�.4\�\�tS��E������ѣ�w�\�8��$,�\�.A߼+5X�۪6\�fy�u��S\��@bd�H@�\�\��\�CA�\���Y\Z\�\�W�$̓O\�-\��=|\�\�7	J��\n\�~���y#E<�`:�c�bT���_�)vO�{P�\�n\���q\�o��\�C\�\�k,P\�/!A�@�\�Ql��\��\Z�ŉ@��<\�^���ۏ�x�iR����ʱ\�m�\�\�\�\��\�?�y�I��\�$�x �\nD�\�\��\"W^<��c��\0���\�ƳJ�z~��\�½�\"\�}��K�M�\��\���v\��v��p\�E(�pz\�$\�ʼ\�g���[\�	 \�b�u��\�j�[Z\�_�Ͽ�9���Z?|���\�j��c���G�\���̯\�\�g.\�;&�\�/��\��\�4\�:\�Z+��N���\��:H\��o\����(_�\�׸\�U�d\�\��!\�f\�M���S�=\�;\��.�BH�\�!\"\�*=C�t�gu<,W�)WJ(I�\�&\�hub\�\���#�~�֊$\�7����\�Wt�\�\�\�8ؚ��RϱD\�3@�\�\0�f\�\�y�\�\�UB��>�$\�+/i�\�\�$�($�VL�Ć\�\0R�N/VI��P\���n���QHޗF6���@�� \�\�C��K���˴\0&z� \�??���YG�)�*�N%B�f�xi\�P����\0I�*t\�\�ՠ\ne\�\�\�v���x\�\��$\�\��\�05�i\r>	h�\�\��\�$!HD��\�.ړlj:t�o\��}�>Hu��]~)~�@G�$	�\"\�\"36O\�*\�\�2Z�\�t;-�a�\�ː�9A�������u#�\�\�F\�\�#�emCHB@Ǯd@\'�V-��n��\�xy:v��\���\��\��*̥)\�\�կ���\�34dD[�AK\�\ZB�(z5ƒefo�\Z��\�?\�Ɖa���(�ŏb��eD�$D�V�H�Bٳ��NC��j\rE�\�4��&�\�\'��\�ć>�\�>�H	\�v\�W�Q\ZBSCKA\�?\�xi�HIrHT3�\�:K\rEC\�c\�\�T�8\�\�\�>�\0��C\��ЭU9r�\0)	\�|HW\'�\�\n/ ��֑���h\�Pȁhk���\�>�5�_�\n�\�\��C��ｎ������64���?}��\�\�R\�&��\�={���q|J\�-V��e\�s\�K^L\�i�\�\�\n�\�n3{\���R	��N�A����\�ǹp����?M\�C�\�b|(O\�iS�B\�\0~��x�\�?�,\�![.��3s��<�8C#S�\�y5u1�\�G��q\�}�\�\��\Z\�\�?\�\�q;�?q�e\�ql�\�\��V&�y\�b\r\�<\�tZ�ˏ�$1Q�́G��\�\�\'�\�e�\�ӣ�l\�p�3W��\Zb��R\�M�6\�$�o�\�W�~\�.\��ʝ�┤�H1(��\0�\�FF��\�{�\�\�q\�+\�N\��\���\n\�mh4ZLOV�\�7>\�~\�g��.��y:t�n�D\�Ԏ<2`t\�F�\Z̵Xn@�\�\�\�\�q�&6��\rt\�~2D�ڀ@\�r���>��&\�\�\�Rɧ<z\�7ٵ>\�#\�{=~RǗ\nd�L$mu�CZ�&�R���\�\�|\�M\�2}�>�/ɋ&�\�Y�֗�>?x\�Q��;�\�\\��\�|¤Õ/���\�\�!M�E\�	Aѧ�\�\�!t\�ݔ�=��n�Q2\�O|<\r��F\�-��xHb�d�{o�2��\�\�f�ր��D!*cb\�T{��\�광Q�.��e��m��{�X�&_�\�Ѵ��ˇ�\�\�cy��y�>��\�~;g\�\��~�2�*�܍ѾO$%:\�I	B\�\�-$B�/�F��	\ZI��\�A����\�s3/\�5\��\��\n$U|F|<I�\�f(��\"\�9\n�!|%Hd��#��oh��v��\rʫ\��-r�(Z�S�\�՗��U\�~\��\�{%?>P\�\�n���mW&�cP]��?!\�v�����\�QB�J�H��ZQ�x\�G��$\�\"=M����g(\"��|>G��\��(E�xi�|�@)���h/�\�*MK�b�a��M��\Z\�U��C\��O\�`\�\�yJ#3�\�\���\�~�\��.�\���F\� $$BB0d�`\Z\n%�LI%(�\�\�H�#%�\�t��\�\�}>����	He\�N�l\�\�3صuO=�C�#�U�\�O\�c�R��4;L�\Z\�i�\�7pωm|m\�4�\�%\\v\�{i\�	�]Eܩ�ϛQ\�\�6\�2\�1=\�0��r��6���sP�\Z<�\��l\�X\�K6ӨՌ�\�&\�bh	�D��1��\�\";��9�\�݌�*\��>^[ҩ��\n�:-\��\�\�\�j�\�or\�\�������Ӿ�gY?$hT\�x�O�\�s\�T�$A[gE\�\�Ck�\�T�S���\�wN1�̽����<*VTJ�$�\�x�@\�\�\�T��\�G��%	]\�w~\��\����#��NP�\Z�k�	=4D�\��&29\��\�q~�5�����3_���\�b��\�4�U?��|\�jE*�ax\�\�(�� Lb&����*�>���\�m\n��\�N񽨇>��\�\�\�֊u\�I �\�s_�&_�廼\�\��l�q�ú�<�\r���g\�$\�M\�D=�;z����G(�\�C\�\n�.{3_A�J(���B\�y\�7��@�)\"\� U�o�\�\��\���[�D\�֘�@��\�BV\��\���\�n��f��\Z\�t�a\�f>���g\���x\�;\�E4:�B��#\n!$4D\�F�\�|�nԸ�[\�\�\�7�3\�\�2Õ!\�?�D3\Z�<=\�bu�f�\�\�_}=\�y߻\�s\�&\r�v=��:]є\�\0	C3\"Θ�\�}�\'�Yq\n�0��XU��=\�M���\��_~)\�ޅ��_H���F��b�\�䃐\�J�\�\�2�N\��>�g���g\�\�3�g\��ҫ���$\Z?\\�Р�\0r�X�Dw��\�=\�`g��mm�N�\\\nM�\�!NQ��}Tj^�==\�?����\�hP�\�	�E\�n� �\�\�r�Z\�8axx���\n/�r7�6�#�\"R\ra>o��\�aB\�\�:\�ҍK\�\�����^\�\�\�BĆ�Ы��:;f<\�sm\�\�S\"EIE�B�I)!�S���Q(Bk�+�\�Uլru5t��\�\�՚Dz\�:�>����i�R�=|;F\Z`�Skumo\�\�.�;*Vx=�\�C\�\��|v\'\r�9\�ᑢV��>\�V�(*���̀|\�,.\��|�\�*�}\�\�>{\�_�y~$~o2�\���ъ���:-$Zi�V=�\�!$��|P�\�R.�A��B�	hƊ�f\��L�gyaw	G�	�� 0���xZ+�q�\�\����#\�\nPʬ�\�\�C\0�@[�N�\�x\�BA$$x\�l4\�\�C�\�I�\�C ��<>����.xaw\�y�\���J�d5ɨ�A�*�X\�\n\�j\Z��\�A@P\��Ӈ��t\�\�%r\�\n�rDއ��^d\�2�\"\�t��O�FۤKj\�lt�DS\�	\rA�	C\�[]_XR\�HR�8�V�XR\�L��� !|<�\�Rp�����L�hT�)M\�b\�c�j�(�\��o0ۣu>�H�Pf�\�l\�G���^\�%��Oj����/\��]>s��n)��a�8Li���g/�TDz�%jM�\�d��<\�3G\�7N���\��\��6�_o�X\�j7�ry����\�r�4�E����%\�\'����\�(�Y�.ppv�\'\�\�`t���	\�\�\�H=el�l\�A^I�8Jaj=Eok/��\�\�P�\n��Q��/#�J��\�A�Ǳi�\�c�\�V&=I�(XjB��8\�\��\�L\�Q\0�\�-��\�!\�O.k��M�BSA3�4�Xl+ښNP��@�yRGƪC ! & �\'���<II�k�\�$\0�N=�B�Z����]o\�	��\�%|\�o\���X��Q�o1\�\�c\�h�)Q���\�tiU�L��)*EA��\��)��U\�F�0*�Ɖ�C\�\nhki\�Z2�R��Qkr��!�  �\�\�]�\�y\�UbJ\�\�X�i�K �z��P��\'��$?I8��\\r���\�z\�.B�D;H\�q��y�>J�<A02M�դS�c�:\�5\�]\�\�ebQ\�\�\�y�M͘��n0,\Z��pݥ�c\�4m\�ȎMSL� \��$�Y\�j��\�ytZmF\�%[;`�\0~˚\�^��\0]1�jk]\�\�R\���/��}{\�1>atHRO�\�F�J��j�CK�^��\�*O\��`/@\�b�\��\�^|^�\��#��Ν<o�.�Au�eޱ:\�\�~\�C�\rg\�,r��\�\��\�Sx^��_�\nNWDy=~y\n\�\�Ba��\�N�}f��\����m��\� �:\�\�LL�\r�\'`n�(?Le�L�\�a\��\�\��~�z�C�%x��\�9\n\�9Q���cG\�S\�.`f\���6u\�y\�[^M�ϣ�B\�\�.\���\�)C9���\��E\Z?�W_bhd��/a�\�A\�\"��9q�2��\�2�F���|	���X3Q\�\��8���13A����pϭw�E\Zg6\�`ˆ\�\��\�\�LN���T�\�l�ȘRA�\���\�}O�W\��ԗ\�-\�l>?�S\�f\�ޫ�\�k\�{9qS9�켂����\�\�X��z�E�S\n���诿ȏf�Q,��Hs\�iC��D޺�Z@+�\�:M�ـ\\H4T\"�n�b/ӓ�mT\"�e�f�Қ�$b�<D7�ŤΦWp\�W��W^����G�3��F�`\�\� q\�uWs\�-ߥY��z�f����kYn\��\�y�\'+\�;ɞ+~����q\�\�ݡ�Bc΄L\�GYXZ�!\�6s\�\�\�\�q\�\�g�L\��M�ˣS�J<R%	�Y�\�B�!\�\�:\�&��7wy\�\�	ry�+��\�,�\�\���7򇟽����\�����T264Dc�Ynx\�^*\�\�^t\�N\�}jan\�T[V�\��\�>h,3ו\�\�t1[w^\�E�36R�!j\�T�e\�AK2�\n\nb�X*�8�S&o;\�1\�	�\"5ů(��.�w_\�}�~�\�6�熈\�e�K|��\�\�#\�4y\�5Ro���o�v_\�Wi\�P.`q\��p\�~��\��\'�\�Y�9\�<Ջ�n�~\�\�o;4�D�� B\�\�5V��D\�BOc���(Sϊ�&���_\��ɘ\��Ҷz\�z��\�2C��\�\�\�\�p\�G�\�8��\�������IӔ�\�e��6��8��\�P�B�e�F\n�B\��.\�\�.QP���n\r���\�L\���\�h!��\�ӬDe��V���ۥ\���\��֟��7�r��H>,>~�*�*	J������_�0A\�O.Q��\�\�\�*� ��\�� �\�ҙ\�b�p)Ct\�!\�\Z��x�c1�bg����\�(�4Eg-\"�<\'O�D���4�H<\�urQ���VUʘ\�i.��4�8U�i���w�Z�\�\�sbq�T7غ1\�\��|������z\��6@}� J7	s�I�B�hb\��-L\n-S�P(!I�Gj\�(@7Zz�v��s8zI5\�Zm�E�gh�j�����4��\Zr\�2^��\n��B�e�c˩&\Z�ۭ�g�:���Os�=_\�7\��F.\�1\�r}�n\�5�\����TH�\�FG��؄2��&/�\n\�\��@)E�ۦPȑ\���\�\�&%<Z\�(\�\��\�\0ڊ< ��\�r��Ґ�\�7e�I\�!(A+�ZS\�\'��}]�k^z�|\��FH�Ȕ\�[-��%s�J\0%Z��\�\�D�R&&�Ҙ�\�fjr\�/V�5�ϙ��nW�F\�e)��d�ՁN�-�o\�0��\n\�f�aQ\���`\\J�\nyx\�w��iv\�}:7D���@H\��\�\�!\rZ\Z�Jmye�L@\��:��\�\��0���H\�_\�Ț��� ?���9\�]�����F��\\J.M\�L�\�R\'⯿�$]�)Nl`\�޷\�\�G*I.�FO��`�\�!E6\�\�\�Cؠ\�J .Bu�\�\�D���H��̞ݛĤiګ��W(p��\�y�\�o\�Ư\�\�n�ȴK\�\�z>Ji��\�\�d��\�\Z\�\�\�عw�\�Q	�݌IB�٢R*��L~ez}�3�\�<Vh�MPF\�\�\�i�\��T9�\�v\�\�D��z�F��_\�\�\'K��B$ [P7�\�\�,^\�`\��	e@mI�|!�#��TX\n\�\�R\�D�\��6bd��r�n���Oe8ϩS]\�N\�V�k\�O\�ԂLi,�bxH�����\�\�\\�\0\�C�������!IA�f2�+pޖI\�O`8H)�6SB��R�\�v�\�$-\n\�\�&�M\�\�F�\�3�ǁ;��mEMQ��[-\�� M�6��l-�\��=<>\Z���\�\�\�\���iz�+/\�H\Z+��Hb\�R\��O��\�a\�\�(i\�ǯx\�U<��\�a,b�\�a\�J�\�j����b�\�`l�Eş�P}����y�\�?��\�=�u�\�T�v\'%^E\�\0\0 \0IDAT�\\@���ۅք�\�>��\Z4]ʹ����_x�\��0��\�K�\�h��0�AJxĘ\��K�\�\�\�T�}\�\�\�HDҍIRPVta�@A\�,\���Oy\�[_\���a�Ep��Bzt?\"\�P�\�o�M)�\�h�M\�Jx!���A$	\�ŀ۾u3\�_{)/�\�|�n㓒�vر9D\�m6��  \�\��}\�W\��v�|�>*��ڙg��&_!R�Z��I�݈���l�\�|^�׳c\�\�\\>���݄#��\0\�.g���\�\�%)RJ�,m�	�v9�\�a�j�_�9�n\�gCo%@\�2\�+�Xc��g\�.�`o~\�\���M\�\��&�\�3�BwZ� �ڤ\��\�\�2EBKx�\����F�>~�\�÷�3s?�\�\�&\�Y6-F\�*��N%�RI딓CI�|2G\���;��\��/�\�pN�>�*��Wk�\�\�\��X	\�iLP\"Q�\��*?r�׽�m>9��\n$\ZF�+\\p�S \�Ph�L�g�:\�g?�)b��nE�y4���\�)�͐�ފ���O\'n�X]$N\���J�}\�5\r�PK#�e�\�Y�G9D\�w>���ia\�\�88&�PZ#��^K(%W_}1\�f\�]w\�Ύ�[ٲ~��\"\�_kt\�E\�*\�\"��z�+^|/�\�\Z\�i\�r�E\ZY�\�̱*��N��\�H�\�p�\�lf˶<�@��\rC\"aY�\�\�\�W24\�$C-6���J5\��H���\��\�<�У\�~ǝ,\�k\�p\'\�gfa�\�F\�04\�\nA�Ѡ]o�!�j���\�\�\���!�]q9]r1~.\�̶�\�0Dۊr)WR�R\n�����n\�![\'ޥ��g`��L��\���\�\n\��骄n\'!�7R��(�XXZBŚV�\�P��\�\��d\n�Vm��ݤTj�����ߏ\�\\�\��\'dؤ/=�\Zz�����X}��\�^ӽ\��wimm۞ㅼ}A؊pecW]ۢ�\�Ҷ墫w\�(m~O��\�{\�\��#7\�\�\��|7�ĎSff\�\�ځ���v�iګu:�=\�\�׏]�Ď\�7��\�[ǕlT\"kU� 3w�\��:��\�\����͵\�Y\�\�\�~b��y.\�\��w\�<fk�x��\�}+c\�\�}�k\�o��\0&\�o:<���#Hs��T3\��\�1�\�ht;O��}\���+�\'+�V�\�Q^O󸉙	e\� \�vَ]��7�Z�\�#x�]h�,\�\�)�����χ0�@\�?Ғ�V�޷�\�s\�Y���i	i�i8BYD�D\�4��\�ul8+6#�+wpлR\�DL�\'\�;J�}w{�q.X��\�E\'j\\\���E�_;_����\�q��\�\��*\�<�\�\�\�\�\"�\�h\0&l\n m(��;\�\�~=�m\�\\ؿ\��	3l\�ځ#�v�0d�c���g\�u��\�:=G�F�yଅ\�~����(\�N�9�+�8-c9 0n\�_��r\�<}\���\� _4@\�s]6:%o�\�Ս\�9\�Ҷ�\�3\�K���\�V�Y\�\�VYM���e&\0�y�����,��aO\��)CYu\�-�\�h�Zډ%\�A�7�E�����ԉa�\�s��\��`\�(Ӈ\�\�\�\�p\��A\0��\'\0�\��K\�x�F�wl{	F����;Q4g&\�6O\Ze<U\��e�b�+\�\�Z�h�z`N\��\�8%\�L�{�j\��%�Jn0Is�[��ISNnA�pV��\�껦\�Ƽ\�\�8�s-b7���\���\��}#*�|\�w�\�\��p�e\�;f������I�pޞ�f\\%�\�\��R\�1��\�\�0��~\���\�\0�\�\0ll����\�\\�_o\�\�/Zm#ڢ�A�fܡ}\��p\�Zm���#dˀ�\��p\�-���~\��9\�-��\�\�\�\nKZ��,�88k\�\�3\�jat����\�Լ17o8\�H\�\\���h\��t\�3\�.=Z.�P�2]����\�\�\�m_3�\r\n}�\�Z�d��)UZ��RA�\�י\�kE��k�\��3�Cg�`�|�B�\�\�+��e��Έ,��SuK\�%+Ľ�q�zx=503i����\�\rOL�3\�\�p�\�\0\�M[L�\�\�s1`;\�f\�\�甹Y@\�6\�Y0�$+\�v��\��\�\�fϑ�2\�X�C[��{Ǝ���\�=�\�C��媙\�\�\�j�uj\Z�\n��jV�\�FD�\�\n���\�S\rfE�-\Z�\�\�}	��]���\�\0�\Z5�-FR�I�\�\�1c�`ELT10ʜ[X�\�ؤ�\�\�l\�R�Et\�\�\"ߚg\�y>��FVה\�L�ت9\�u\�\�p��u���\�\�v�\0��n3�YB��M�%+\�R\'02\"G\�zU|Sv�\�3\�yj�z\�\�ُfB�\�\��\�\'\�G%\�3\0�<\rL�\Z@\�\��vbb��cU��7\�fe��`d\�L\�:p��ᐹ�\�	\�\\w5\0���3�C\'\��mS��ЖU�Y�ai�q�\0\�]z1\0;6��.ں\�^O0i�M\�鷒�#\�\�\�6\�Y�\�2ףv;����֖���߲�vV�g�h���m\�Q(\� �e�CG\��X2���hZ��\�C�pఱ�.\�b���g=s\��\�F\�?Jé�-5\�@�M[V_4���\�As\���\�wz\�\0\\��wL{I��苯3���\�!\0�s\�\0T\�\�_�˴ӱ~K�j8�\�i�\�.\�m\��\�w�t\�p\\���N\��S��\�\�!\�\"�3g%\�~\��\08p�����o~�+\08]������ˆ��eM\nF�=f\�\�>c���hu侓�\�\�\�3f\�=�\�3n(ubڴ\�tV\�(jΌ3ʛv+C�-��\��V�\���\�z\�\�o�L;\�Z�\�i�k8\�i6��cG\�8�C:�\� hf\��]J\�����\�\0�\�-�6\�\�\�W��]p�Ze\�9\�X�B�\r�\��}�O��\�\�<�\�\�k\08c\�et\�ЈA�Ď��ىʜ�jZ�\�\�I�]f&��\�\�v\0\�\�\�O�d�A�D\�\��\"s&�n\�#��\�\�8�._�\Z�\�\'\��[\�0\�\0\��\�<e\�1�i��\�S��U&\Z\�\�\��%�\�vӈ\�D��J6d�\��\0�\�}O�W\�4\�\�f�\�\�F\�L\�\���ȷ4\�߽W_�׾gD��001e��W\0��`vވ�\�����	l�P0\�@\�r\���L)\'\�\�5���H-\�X\�ƾ\�+�t\��\�V\�ZƯ\�6-�rfA�!\�nd�{7\\a��\�\�\rf���v\����n��\�i\�ǖYG�ԄB�\�\�o\�\�k11�ڴì\�M_�\0^y�A\�T\�Z�9\'\�\\4�^�L��R�\rVI\�t�qԚU\�Qj\�צ�\�\�˯`ٮۼU�ӓ\�o\�7g(q\�?@\�2@~\�Pv�l8�k,v�۰3���\�q\�G,,\�9�g;���v��\�\�\�r\06�o\��\���6oײ1\r\��ik=�ąL\�=\�\�ӆH�(\n��X,\Z�fl\���7w\0y\�\�\�s��\�%���__4`��w��?�\�\0���o7\�Z.�g<6d&ژ}�^f(��\�P��\�JC1�>e��07e۱e���?�\�\�\�p\�\\\���c����\�4����\�M�#\�AƱ�)��ee)�e\��^f��]�\��\�K�\�5\���Q�p�\�y\�\�f͈\�ݻ�\�[��w�;���\�f^A\�1T�%]�f\�1��U\���\�z\07\�j�\�\��.\0S��tِ\rg/.��n\�o:*�\��?a�vgѰ챦\�1,��)�綍�~勯`\�y{\0\�Eּ��ҭ/��l[�7\�8Th��\�؆��\�\������4V9�\�C�	;�\0���˿h\���ȓv�\�O������.Xdb���a�j06T1\"�c\��:44o\'r\�\�\Z��=���3G��)\nO���l\�mZ�\�2k��\Z*T\�H\0ز\�\����\�%\��A\�@{\����g�u\�b�\�[\�\Z�V\��ּ\�M��0��\r�\0��[\�r�C�\�D\Z��_���,}+b��\�*�A�\��*\0o�I�[\��JC\�\��e�\�\Z���]��\�\�v�\�J��\���rl�=j�;�\�\0Uk\�\�a�!ۮU.\��:i\�\�c\�#g�`i\�p\��\'\��\�\�`\�\��\0\\\�O\0\�U���RZ\���~��\�I+�{qV\�\�<\�stߵC��Bz�o\�n`93ou\�ɓF�JBV���\���3n��Q\�Ek^&�w����!;�\�q�<�cK�ժ��\�\�r6�b�4�����>�h�\�z\�\�6\ZD~\�_`�П�O\0f�\�g���\�k0̙����Z�ճ�=3�\�Y9\�\\v�J�,���\�1�دp	9�\"�b\�\�R\��u�M\��JK2���\�\�Z\�z�CEg�j�\��\�\�SFǔ�Y�lR\�	\�\��ݶ�\�Nu�9;.+\�Y���\ZQ�|\�i\0�l7���/}\Z�\��.�f�\�)?>`\�\�\�\�d�i0�q�\�	6B`o$�ME\�(�채\�ZD��\�U.uݟbu�\�\�J�	wm\"�`c��\r\�J[\�qVN}%gkVΔ�C\�\�\�Y\�[�\�E\�lp/\�j,֬R�\�,��;��軫&	�K�2m0iBk_.�8�5W\Z\�a\�:\�X\�\��\���	�%c��6�\�r�\�\�T�ܺ%�\"��R\"t!���l�\�ezF�uPc�;�&\'\�p�<w^�W�\�>Mp\�\�]\�\�A\�\�h�.ؐDЋ�\ZhY\�Ա0Z�\"�6�l��\�\�vӼ?lC\��\0\�v\�\nF�=�\�\0\���\�{���9ٶ\�x�3zlE��2ۄ�K�8\�{��CH�9g��KShW$a�R�>7���N�u���\�bUGge�,B|;��y\�Э�\� l\�\�m�R\�l$g~\�Y%+ڦېɰUKC��U�\�Z3�8a��&�ߴ\�[Z.\���͍[�\�[\�[�U\"�ȴ\�+�&�E��aeT�P�3�|r\��]5cE���g�fێݖ\�\��\���1����G|\�\�\�`�\�i�gIǬPh)RمV�䜌�f\�Yc��vw\�5]�Y/�m�m3P\�ƼjM�\02\�db=��\r[O�7�vf\�6�AD\�=\�.3h�{,d�k��`>eHL��\�3F�\�ڹ	\0\�^Q�\�\��Α����\�ѫ\\T��\�\'�|\�{fd\��G�\�P`.\Z�5\�ז�\�\�FT�\��+�|������\��Ek%-N\\���-{��9n��ʰ���]�h\��n��:eu`袬�\�a祲���b\�;\�\nl�dc\�`xr\�୏�ڀ\��\�L����s\�*14\�v�\�y��\�֫^̖[��\�\�y[\�B̟0�1��\�\�6jϙe������<�\�>u!H+�M���F)F\�!��\�9�\�\0x՛^���o\0`цL���P4\�9�\�>iH��\�Y����\�UګV�UP:D���ȲȐ%����h\�i\�G7�����[.T\�2���\'�V�\�2ʣK�$��\�+^ab7���\� �\�bc=q\�0\0cSF�,\�`l\��}?��g\���g9i؊ \����?d(���9|^\�P�\�.\�\��\�\��Rv!\"k��DN\�/�V$�\�픰��\�	�0\�,\�̸\�x\�\0~�C\�V\�\�Z\���ө\�\�3�V��3Q\�^Y�3�\�\�\�-���ҫL\���\�\�\�}Gnz�ATӊ�$\r�z�V�\�1	,Br�U\�\'mFnƴ�����\��\�\��o�\0\�h농�C\�Ց\�|Os\�pP�hDK\�\�{\�ՕC�0c��\�r\��\�m߼�\�5�b/�\�\'\�F<|\�t���U\���T�\��b�e���\�n��~�=&*�;�\�\'ع�E\0�:nB\"Ѹ�ζ�_\�8Ŋ��\r���҈\�n��D�\�\�l�\�_5a\�\�r�M\���\0l}�-��1e?ǖ\�\�\�\�Jk؄���kz�\�/�o{\�M�3N<c$����=��s\�9[\�\���[}\�tW�#\�e\�GT�<��\�F_�T܀K���\��M\0�d�*\�x\�\�N�\0�q\�bk��7�e�.�\�߳\�\��l\�k�ne�Q\���|���S궖v\�&�J����\��e8M	\�!Nt����Mt�SVD;3\�\�~�2\����|�?���\0عՌ#��c�\�Ӳ��Ɖ]�I��!\�*�\�P\�pE���\�1\�·11�׽\���mBK\�\�\0�G?:l��,GL\�\� \�����lp\�\�S�(᳟�\0�\�<\�\�[\�]N\�Z���\�Jc�\Z%7m�S��&�ՉM��Uc�\�6g>d\�xJ%\�Y�}�\�Y6Đ���\�\�\�ڃ�J\�W=\�\"$��sK�WO8pJQZs���\�kڅ�\�~�}��\�\�x\0Ѩ�\n���\��U�^�\�ZG�X9rj뷆m�]7�}���\���\��l޷\�A\�\�z�\���\�M����dy\�\�_�6\���W_�{\��n\0�\\bD�# ��B;0?+�������\�\���2tp\�\�\�����ާ\�՚A`�\���\�/\������z���ݺ\�P����m\�ߝ#�\�\�+V������\�\���\�s\�_��\�&���\�\�oؐ͏75K\�̂F��G\rǆV\�\�\�i�\�o�����\�&\�:Ȇ\�\�\�\�6\�\�M\��\�\�\�z\�\��\�U�\�~�1v�\�E \�Hm�\�Y�\�\�s_\0�R1y��j�p�����Vk�\�͖͉[N�\�m��y\�q��Ϋ�dq\�q\\C�[�7㘷\�\�sg\"g6����\�J��K,k��鳾�q:ܹk\�\�Kg񲂐�99d幬�\�K\"��D�ux��\�@��\�:�\�X���Cƿ���;X��\�BcN��14<:b��\�f<n���-hkכ}\�5��݃�\�\�a\�Ȗl\�\�+L�\�E��ܽ�۶~˵��#i�\0��\�Ys�\�V!��\��Kg��?ז�\�-�\�}�;\�\��Ŗ�#K�yK�.<\�ޛ=m(����\�cU\�\�BWk��0oDW\�J`C��3\"�e]�\�z\�\�\�&\�:5f���]��M��ؠ��\�`N劌�{\�\�\�F�E�Y\�\�>\'�w?�t\�~��E\�9��\�]n\�w�\�d2p��\\�\�d3�]kG\���VR\�\�Bec/KF9+\�\�C6a399\�7\�ԥ\rz8Y�\���\�\r\���\"z~��\'.�\�\�q��u��L�C���\�\�\�\��Y���\0��\�\�;�5����C\�6Xc8d��`\'�5��ÀC\��\�Xc[k8d��`��5�\�ɀC\�6�Yc�\�8d��`7�5�ЀC\��gZc\�3\r8d��`��5�_րC\�60[c�\r8d��`G�5��܀C\���[c[�\r8d��`\�\�5�\�\�C\�6��ͬ�M0�\�`�+\�\Zەt�!k\�Į�mb�\�`�o\�\Z۷w�!k)�����\�`���\Z\�\�z�!k[����\��\�`���\Z\��}�!k��\���\�`p:\�\Z;a�!k\�U�\�Ӱ�\�*�\�`p~H�3zi���C�\�`p�����7>\�e�!k\'쬱v�\�`p\�\�\Z;�h�!kgP��3��\�`p(\�\Z;l�!k����S\��\�`pl\�\Z;6o�!k\���s�\�`p�\�\Z;Xr�!k\'}���>�\�`p�\�\Z;zu�!kg᮱�p�\�`p8�\Z;�x�!k\�}Z�9\�\�^\�\������\�N���#�|\�\�\�g�2\�糧Sg��_֯\�g:p�X��\�\�{\�͌0[�3#\�v\�{ 3\���;�w}V��\"�\�\�\���\�g<ɬ�|u��\"����s;}8蝾\�\��{%w\Z��,�\�$�\�~��\�\�o��8�X���\�\�G�+O؃s\�|���};{�}>+��u��p\�4w\�Twg�f�eLV\"����9$�]�\���\�\�U^��4ӟ\��x��\r���\�\��N�\��Ͽ2/�Y���;�\�A�\�\�t�^s��$\�i�ܩ��Q&��\�i���\0����ĳ\�Y&g�8{��h�\�4J^\�azA�ό� �%\�9S?2�˸���V�\��g���ܕ\'=\�\�\"\�~(�Q���RϿ��\�\�g���\�u�=�]�졌�\�͌\�^j��*�qF�ʢc\�\�K�\�\��>O82\���gQ�a�A0�灳\�G��5\�y\�(J\�&\��g����Ax\�\�J�٨Q\��\�\�X<�uF\0dF,2\�g\�3\�Y\r�f\�댓#2㍲�\�h�f�\�w\�v\�t 3����12\�\�v\�\�/�i�\�\\�\�\'��U\�\��\Z4\�{�Vq�\�\��\Z)\���7�k\�\�\�@�`\0\�B\�e\�,x~/\�%�9\�a?�Pb$#Bu6��I6���!�~	\��9;J�\�Ӈ��?o�^t�/���s\�J��r�$�Y��<Ϯf�_�\�\�;\���_�\�cxA\�\�\�D\�2>�\�D�2Y���X\��ld_H�\�/\�2\Z\'�� �r\�\�6/@�\r2�<�\��PY	�\��l�8\�S�Y�p�\��B��z��s\�i6\�#�e��\�8�\���z\����F�\�]\�\�>r�`\�uu�D\��e�bف�\�\�;t2\�K\�\��A��D��zf�_m�\�\�\\O�s}\�[F���(��\'\�f\�3�� #�ef��\�\�f��u\�\�\��$d:��+���\�(�fz!��A\�\�\0��|t+�SV\�fk��\��\��l\� �F6���̄\�L��\�\�_�\�����͗o�n\��gn��\���m\�܂\�(\�3\�=\�s>j\�]iuX$X	!�lYFLl\�I�R�`\�	�|\�\\8U�Ta_\�\�M�R΁8e��*#�\���\�6\�$�\�XF���v�]\�\�\��\�3�K\�����5�\�\�ޝힿ���\��\�\���SjR8\���\�FT\"G7oɩ��3PWm�JK��RS�\��oj[.�?9%<U;\'|UC\�����������\�T�؅���w�~���a�\���\r1)<\�K�\� ��]�\��\�#�\��+�\0\03IDAT�*��,Ջ\�7b\\�^\�q���3*Q\�<��B\���\�\�\�\�g��\r�sj�LU������\Z\�E7��9��\�3\�ӣ*�3\�	�>_�o\�ǅ�1\�x�\�md\�\��N�\�\�\��\��T\�x�^f#�K\�\�_��O\�e�o��\rz�\�����G\�m������!�2\"��	�b\�KT�\�\�!\r��@\Z{V\��\�	\��\�\'fԆY��;�\�OO\�ϯ^+\�g�\Z\"���u���.#\�*�#M�?�Z0\�\�%+\���\�z�\�&�RG���C�G~���y�\�S\�\��G)�Cc,����[��w�\� �.L���hZ����sy�\�4!\�y\�a\�T\�|\�ۏoC�\��7w	�3V�˃\n\�BeQB�\rjn�/Q��r8�{\�\�y\���k\�~�\�~\�T�G�ƫ#.\�!��Y\��\�`\���䆄w)\�\r�\�\�\��i�\�#��\�~^\��\�̝�f\�\�1h\�jSW���K\�F\�-,~w�����W�\r�m}#cF.�Ѩ\�/m����\�W�S��\Z_�{��^���\�\���\'5�\���A�Kt��\Z\��/\�/��W\r[6&��\n�VYm�ٲ\�)vްC�9\�7t\\�_Ǧ��KF\�f�؅\�\"W�\\����~\�UW\n�\\�\�w\�\�*|>*|Do�㷂s�X\�\�\rG��;�(��[\r�| �0��\�zX�HX\rba\0��-!sY�\�\�\�\���X\�:I�Kr\�J\�3%\�#��t��:\�$o\�C��\�\�t\�Z\�\�1r\�Ps\���\�2�C��6\�<l�c%�\���\�ExM��7�����\�-��\�\�&/~���\�i\���jU��u7�Bb�:&��=�����\\\�\�B�I��6\�\�s\Z\'ڸI��g>�\�y{z-Fj��^O:�WX\rba\0�\Z[:\��y\�\�?�\�g�8+�~\�\�o�Y�����n^#�^^��=x%��z}N�\�\�_7��\�3]�\�\�z��{@�q~L�(ΐ\�ɇ\�t���$�ǝ\���H\�zY\�X\rD�g�;V�F�<��jE�o4t=o\��;yB\�\�F�l\"4qޘ\��\�\n\�\�\�a\���|=����~\��\nO{��K\�\�\�\"��Fav���Ż\�\�7\�\�&\�Kp�W�&|��o\�ګv\n?	��oj\\#�L5\��䇄� �\"I��D�͙i\� �\�\�\�\�8<#�;?}V�K��_\�#yq�\�\�4*Bg��3�\�)jܡE5������j�\�cS\����\�O�=��G\�ҚQ}\�\'��]�Fx\n\Z��͝��a�K%\�pͺz���^_]L��W�{\�~\�-���=WՋ88�ܶ��iV�XX�Kx�\�u\�ʷ\�\�%��)2�{\�\�QT�m�F�k�\�>3�^�\�\Z)�64�C#�tZ#�5\����\�	�\�\�$i�\��ч�0Z\�q��éw��|}\�$p#6\ZZ�ѮC\�$U�\'��\�:\�x�֟���\�r�5��\�F��^o\�s\r؎\�ծ#ټ�6�ׂ�\Zg���~���߲C5\�h\n��I��\�[8�yY\rba\0�v{�\�}�	������\�\"����i\�G�/B \�!R<6��fuO���?ނ	�\Z\�=|Qm�6$p�^�\ZD\"�B\ri=\�|I5\�,M�\�ٲ�glL%�䖭\�W]�\Z\�\��TB{��ʜj�ڀ���ܹ�\�.&*c���.\�.%�:\�Pc�\�h&\�\��^�|�	\�_�ܧ�|�\'V��_\�[X|�\�S��\�\�\0�h���O��・�\���O\�\�ԫ\�h�u����Y�x����\��\�SV螘��ݦ{\�_����zq��ޏ��\�)\�㝚\�P�m��\�՚\�4�A�~W�Z\'|p*]�h��T-��2��\�\0iW\�oՇƏ���W�e���-\��%\��kԔ\�	\���m\�t��O���;��$���\�+�\"����׃\"W�\�\�o����+Sm|R�cOk\�3;v��\�\�z]A7��.�\�\��\���\'�2z�Ç4\�ZP/Ʃ�\�Q�Eݣ���|�j$}\�uۄO�\�,<�@v���\�\�J\�&T`��W�\�\�T\��;\�J�D&W�\"��(\�[XaTt�Ȩ!��ќ\�G\�\�(�h\�\�\�\��}\�	=��c!:\�j�\03\n�eݣe\�\�ia�C?�vk\�˚\�I�W�ZW�t�F��\�\�\�B���\�#N��w\�\�\�˔M���E\�k\�i�Å����0ͪހ\��j�T\�u0�\�V$�8��\�\�~]5R��� !\�\�&@$6\"5k���z�?a�t\Zʃ�\�E=R!�\Z\�\�~\�j\�\�n\�L�B�82#`�[\rba\0���JTH��J舣{\��JܴCsy���\��y�$��l\�TZ5P2��\"��y+\�$p�u�\����Ο^*�\rt�\�\�\�_z�y\�\�/�\�\�?-hÍ�\�%��@�\�m�а\����4I��6?�\�`}5�Ѻ\��\�J!\�1=�q���\Z�6v������g\�Uk\Z)\� ;\�wT�kE>�.)���\"�\���\\\�=h�\0�h���CN�8IH�3Z�\�EM�%�Tc=�\���\�\�}Q�}��o\�\�5P\�\�[\�O�)\�m�\'����\�E6m*�\�\�\�3	\�\��Zq���1G1:�B�)��Ap��\�jcJ-*D�-���h�A,,\�q\�]5\�\��\�u�\�l�͐I![��q��AUbG ����v�$��\n\�\�\"n@a@�f�g~-|�:�)~�\�ׅ�;�q��\�5N�GT#?�z���V2D\�\�2N�\Z|\�w1u�\Zy�\r�@/���߻�w\��A\r\���z��ۘؕF_7�=#.\�~N0����g��U�\�h\����H˥�M\�\�u\�,��J��=t�\�\� T\�30�΁�qܘ\Z!��\�\�Z׉`�R\Z驋g��\�ܦ��˵�����v1�\�4W��^��\�R\0\�t\�ra����4I�\�74Hpט�	Ol\�o�z�²��\�\��-\�<s&c0����\�:�\�\�TE��o$\�N)��\��;�.1�����T6\�@^,o�	\�Q�u��\"�x\�\��!H\�lZ�b�M	\��\�4�\�5���\�\�&��\r�\�\�\�\�\�\�^Ę�\� ��\�KE\�w�\� K�l\�iP�}t�\�u\�]HmH;\"\�Ų��=\�[\�Y\'��`�\�~�\0H�ҜV�-ߨ\Zh\�Ǚ�J�JM%ʲ���f��\�w\�Ť�^�RKm�o=�]6\�\�\��Bp�v��3�zI�\�\�0�q\"6\�B]%n*�\�.u$�\�\Za4#t\�Qb\�g\�\�92�z��\�R5Q��y�fn��?\�\\�hH`\�j�\0x�\�WЯ��A\\�\Z\�\�?y^�&\�$t\��[*b\�\�c� \�]N�t!�tjF�i�E]\�\�\�\�\��\�Ĉ(}�*uNH\�\�S5\�-4K[�6\�y-�w�\Z\�#��mx�h����\�\���\�\�^\�\�\'5nt��\�\�Q�\�T+��\�)�\�#<s\�\�x�(qX�f\0���Uk\�K\�EU�4\�Z�u�L�ׇ�\�JI5\�`^�V�� R{��	N�s8�H�P�jSͣ+\�\�\�\ZG9�z�BQӏ\�mj<����g���-�z<�^�LE\\%\n�m�XD��VR72�;��\�\��/�\�=\���\�3\�;,���O-,>\�����f�<t2��\�q�\"\�Y�V%\�\�\�s�\��~4�w\�/b&j��g�!s���\�TC\�9b/��\�*\��<�]_������\��	\�w\�(|]L\�s\�]\�D���\\�(4�Q\��\�R�\�b\�@�\�\���,L���7\�\�1�\�m[�ⳋ�W7�\�\�\��!�b�\r�^*�����0\�^`\�\�+��f\�{��\�u�\�Wj�ӡcǅ��Wi�m\�\�\�\��;����~\"�MS��	�#\�򯄧�\��\�ݚ��>�6\�\�\�\�G�\�\�T\�D.$f���\�\�!s\�iCF��T�s,ϟ���\�\�\��=\'�\�\���p\�qD�F�!���A,,`$RqCc�\0�c\�\�Tޡ\�\�\��^;!��\�\�`�V\�]�\Z���\�\�WA\\$���4H2�H�4f\�\�z\�S{\�\�>�5\��)}�\�¯@oڕ�\��E*���.��\�0��z�|\�5��\rmT\�P)\�BLV6P1�\�\�\'�\�W	�v�f6�\�#�C�I�ka�s\�j�\0�\"\�D/\�?�9�[�X\�`ly��^\���\�@��M\�\n?{Z��$���m��\"�I\�%J�Sa\�\�=ln�\�(\\s���]�F#��\�n\�\�`$u0�o\�ٟ\n���J\�����S�z��V\�/7<�\�\�!�TH.�\��\�\�8sRm\��v��\�\�����<#r���8\�>a6`5��E\0\�^O\����G\��ˡ�\�֩Q��\�އ����G0�\�\�\�~Q��X9�V�u�\�|H�B��}Wk\�7oָ\�z�\�\Z�\"\�pB;M~\�_�C=I5�~�9L�\�]�\Zt�J\�,\�M\�s\��W��M�k�NT�\�G\�`�\�\�h+\�\��{\�\�	�\��O��	�	X�Z8c��\��ܐ�\�V�XX`\�\Zd�ba\�aqz$�:>�ڿko\�\�^\�>S��]\�/�c�U?��5\�E\r�_cTK��#a􌢳\����	��~�H\�L�\�\�\�9\'�n#j#\�ǅ\'\��|��fĠ\�[����\�u0\�<�\��\�rj�|\�\�\Z\��x\'�z���Q\rbza������x\�04��a04\��`$7�\�6�x�\�\�|�;\�~K��w\�#<1��~ݘnJ�E�Z��g����ƐN�6XMs��\�\�ޯi���^����~��V\��;V�׾c�\�i�zBg,\�\�t�	5D�6o�u��{?�W\�7oQ/54\�0q\�0v5!\\\��y݌\�opgQ�A,,\��,\�\�\�E\�a@\�\�x\���rE5T:�lT�������_�Ƿ\�!|b�\�\�3\�\�T`T,���cԿ �\\�Ŕ\�!��-\�j}\�c��s\�}Hk\�k\�\��˃:\�tJ%plX�^��\�\�\�oؠ��\���:�\�&�)\�\�\��~g���1\"�!�\Z\�\�\�\�=h����0��	4R�(b :�\��\�+�P\�/8;��=~\�\��\�[�j.\�\�M�E�\�E�ǃ�70�6I\���\�����4P@���j��\�Z�G��9�ss��fϫ\�\Z_�^�\�\�\\�\�@>���N0�@9ˋ��\�_�\�j��\�\�w�d�\'M\�1az�`|pBQ\�daq�^�\��k�Ƴ\�\��XU/\�\�F\�>^1��8��k��+PC\�j���U\�@<_��\�=z���3ǵb3��\\W_�U�[t\�\�N�\�4\�zù\�>+��\���>\�\��	b5��E\0\\�\�~�\Z$\�/\�ك�ΕE��\��OR3\�@c��	\�\�Sؓ�;<�o\�\�\����5\�Ǧ4ת�_�\��\�S\�\�\�`�k,Ι|\�Ej����\�bQ{Վjf�G�m�z�֟\�Љ�^\�x*�\�\�lnH�P\����\�ui�C�\�׋fa�A�\���!\Z$�2z�\Z�0\�\�؝�s˽�Ӈ\�\�\�fi��\�F�q2�Ղۭ��*�%�l�`RCd1`edD糘5\�\�\�,\�fz+\�1�{?*F�ri3���\��\�*\\#\�ٓ�aBZ$\n\�փXX�\��ƀ$��lo\0\0\0\0IEND�B`�',
        NULL, NULL, NULL);
/*!40000 ALTER TABLE `supla_user_icons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `supla_v_client`
--

DROP TABLE IF EXISTS `supla_v_client`;
/*!50001 DROP VIEW IF EXISTS `supla_v_client`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_client` AS SELECT 
 1 AS `id`,
 1 AS `access_id`,
 1 AS `guid`,
 1 AS `name`,
 1 AS `reg_ipv4`,
 1 AS `reg_date`,
 1 AS `last_access_ipv4`,
 1 AS `last_access_date`,
 1 AS `software_version`,
 1 AS `protocol_version`,
 1 AS `enabled`,
 1 AS `user_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `supla_v_client_channel`
--

DROP TABLE IF EXISTS `supla_v_client_channel`;
/*!50001 DROP VIEW IF EXISTS `supla_v_client_channel`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_client_channel` AS SELECT 
 1 AS `id`,
 1 AS `type`,
 1 AS `func`,
 1 AS `param1`,
 1 AS `param2`,
 1 AS `caption`,
 1 AS `param3`,
 1 AS `text_param1`,
 1 AS `text_param2`,
 1 AS `text_param3`,
 1 AS `manufacturer_id`,
 1 AS `product_id`,
 1 AS `user_icon_id`,
 1 AS `user_id`,
 1 AS `channel_number`,
 1 AS `iodevice_id`,
 1 AS `client_id`,
 1 AS `location_id`,
 1 AS `alt_icon`,
 1 AS `protocol_version`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `supla_v_client_channel_group`
--

DROP TABLE IF EXISTS `supla_v_client_channel_group`;
/*!50001 DROP VIEW IF EXISTS `supla_v_client_channel_group`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_client_channel_group` AS SELECT 
 1 AS `id`,
 1 AS `func`,
 1 AS `caption`,
 1 AS `user_id`,
 1 AS `location_id`,
 1 AS `alt_icon`,
 1 AS `user_icon_id`,
 1 AS `client_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `supla_v_client_location`
--

DROP TABLE IF EXISTS `supla_v_client_location`;
/*!50001 DROP VIEW IF EXISTS `supla_v_client_location`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_client_location` AS SELECT 
 1 AS `id`,
 1 AS `caption`,
 1 AS `client_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `supla_v_device_accessid`
--

DROP TABLE IF EXISTS `supla_v_device_accessid`;
/*!50001 DROP VIEW IF EXISTS `supla_v_device_accessid`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_device_accessid` AS SELECT 
 1 AS `id`,
 1 AS `user_id`,
 1 AS `enabled`,
 1 AS `password`,
 1 AS `limit_client`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `supla_v_device_location`
--

DROP TABLE IF EXISTS `supla_v_device_location`;
/*!50001 DROP VIEW IF EXISTS `supla_v_device_location`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_device_location` AS SELECT 
 1 AS `id`,
 1 AS `user_id`,
 1 AS `enabled`,
 1 AS `limit_iodev`,
 1 AS `password`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `supla_v_rel_cg`
--

DROP TABLE IF EXISTS `supla_v_rel_cg`;
/*!50001 DROP VIEW IF EXISTS `supla_v_rel_cg`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_rel_cg` AS SELECT 
 1 AS `group_id`,
 1 AS `channel_id`,
 1 AS `iodevice_id`,
 1 AS `protocol_version`,
 1 AS `client_id`,
 1 AS `channel_hidden`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `supla_v_user_channel_group`
--

DROP TABLE IF EXISTS `supla_v_user_channel_group`;
/*!50001 DROP VIEW IF EXISTS `supla_v_user_channel_group`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_user_channel_group` AS SELECT 
 1 AS `id`,
 1 AS `func`,
 1 AS `caption`,
 1 AS `user_id`,
 1 AS `location_id`,
 1 AS `alt_icon`,
 1 AS `channel_id`,
 1 AS `iodevice_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'supla'
--
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_channel` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_add_channel`(IN `_type` INT, IN `_func` INT, IN `_param1` INT, IN `_param2` INT, IN `_param3` INT, 
IN `_user_id` INT, IN `_channel_number` INT, IN `_iodevice_id` INT, IN `_flist` INT, IN `_flags` INT)
    NO SQL
BEGIN

INSERT INTO `supla_dev_channel` (`type`, `func`, `param1`, `param2`, `param3`, `user_id`, `channel_number`, 
`iodevice_id`, `flist`, `flags`) 
VALUES (_type, _func, _param1, _param2, _param3, _user_id, _channel_number, _iodevice_id, _flist, _flags);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_client` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_add_client`(IN `_access_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8, 
IN `_reg_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11), IN `_user_id` INT(11), 
IN `_auth_key` VARCHAR(64) CHARSET utf8, OUT `_id` INT(11))
    NO SQL
BEGIN

IF EXISTS(SELECT 1 FROM `supla_user` WHERE `id` = _user_id
         AND client_reg_enabled IS NOT NULL AND client_reg_enabled >= UTC_TIMESTAMP()) THEN

INSERT INTO `supla_client`(`access_id`, `guid`, `name`, `enabled`, `reg_ipv4`, `reg_date`, `last_access_ipv4`, 
`last_access_date`,`software_version`, `protocol_version`, `user_id`, `auth_key`) 
VALUES (_access_id, _guid, _name, 1, _reg_ipv4, UTC_TIMESTAMP(), _reg_ipv4, UTC_TIMESTAMP(), _software_version, _protocol_version, 
_user_id, _auth_key);

SELECT LAST_INSERT_ID() INTO _id;

END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_em_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_add_em_log_item`(IN `_channel_id` INT(11), IN `_phase1_fae` BIGINT, IN `_phase1_rae` BIGINT, 
IN `_phase1_fre` BIGINT, IN `_phase1_rre` BIGINT, IN `_phase2_fae` BIGINT, IN `_phase2_rae` BIGINT, IN `_phase2_fre` BIGINT, 
IN `_phase2_rre` BIGINT, IN `_phase3_fae` BIGINT, IN `_phase3_rae` BIGINT, IN `_phase3_fre` BIGINT, IN `_phase3_rre` BIGINT)
    NO SQL
BEGIN

INSERT INTO `supla_em_log`(`channel_id`, `date`, `phase1_fae`, `phase1_rae`, `phase1_fre`, `phase1_rre`, `phase2_fae`, 
`phase2_rae`, `phase2_fre`, `phase2_rre`, `phase3_fae`, `phase3_rae`, `phase3_fre`, `phase3_rre`) 
VALUES (_channel_id, UTC_TIMESTAMP(), _phase1_fae, _phase1_rae, _phase1_fre, _phase1_rre, 
_phase2_fae, _phase2_rae, _phase2_fre, _phase2_rre, _phase3_fae, _phase3_rae, _phase3_fre, _phase3_rre);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_ic_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_add_ic_log_item`(IN `_channel_id` INT(11), IN `_counter` BIGINT, IN `_calculated_value` BIGINT)
    NO SQL
BEGIN

INSERT INTO `supla_ic_log`(`channel_id`, `date`, `counter`, `calculated_value`) 
VALUES (_channel_id,UTC_TIMESTAMP(),_counter, _calculated_value);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_iodevice` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_add_iodevice`(IN `_location_id` INT(11), IN `_user_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8, IN `_reg_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(10), IN `_protocol_version` INT(11), IN `_product_id` SMALLINT, IN `_manufacturer_id` SMALLINT, IN `_original_location_id` INT(11), IN `_auth_key` VARCHAR(64), IN `_flags` INT(11), OUT `_id` INT(11))
    NO SQL
BEGIN

SET @mfr_id = _manufacturer_id;

IF _manufacturer_id = 0 THEN
  IF _name LIKE '%sonoff%' THEN SELECT 6 INTO @mfr_id; END IF;
  IF _name LIKE 'NICE %' THEN SELECT  5 INTO @mfr_id; END IF;
  IF _name LIKE 'ZAMEL %' THEN SELECT 4 INTO @mfr_id; END IF;
END IF;

INSERT INTO `supla_iodevice`(`location_id`, `user_id`, `guid`, `name`, `enabled`, `reg_date`, `reg_ipv4`, `last_connected`, `last_ipv4`, 
`software_version`, `protocol_version`, `manufacturer_id`, `product_id`, `original_location_id`, `auth_key`, `flags`) 
VALUES (_location_id, _user_id, _guid, _name, 1, UTC_TIMESTAMP(), _reg_ipv4, UTC_TIMESTAMP(), _reg_ipv4, _software_version, 
_protocol_version, @mfr_id, _product_id, _original_location_id, _auth_key, _flags);

SELECT LAST_INSERT_ID() INTO _id;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_temperature_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_add_temperature_log_item`(IN `_channel_id` INT(11), IN `_temperature` DECIMAL(8,4))
    NO SQL
BEGIN

INSERT INTO `supla_temperature_log`(`channel_id`, `date`, `temperature`) VALUES (_channel_id,UTC_TIMESTAMP(),_temperature);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_temphumidity_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_add_temphumidity_log_item`(IN `_channel_id` INT(11), IN `_temperature` DECIMAL(8,4), 
IN `_humidity` DECIMAL(8,4))
    NO SQL
BEGIN

INSERT INTO `supla_temphumidity_log`(`channel_id`, `date`, `temperature`, `humidity`) 
VALUES (_channel_id,UTC_TIMESTAMP(),_temperature, _humidity);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_thermostat_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_add_thermostat_log_item`(IN `_channel_id` INT(11), IN `_measured_temperature` DECIMAL(5,2), IN `_preset_temperature` DECIMAL(5,2))
    NO SQL
BEGIN INSERT INTO `supla_thermostat_log`(`channel_id`, `date`, `measured_temperature`, `preset_temperature`) VALUES (_channel_id,UTC_TIMESTAMP(),_measured_temperature, _preset_temperature); END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_get_device_firmware_url` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
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
            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_oauth_add_client_for_app` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_oauth_add_client_for_app`(IN `_random_id` VARCHAR(255) CHARSET utf8, 
IN `_secret` VARCHAR(255) CHARSET utf8, OUT `_id` INT(11))
    NO SQL
BEGIN

SET @lck = 0;
SET @id_exists = 0;

SELECT GET_LOCK('oauth_add_client', 2) INTO @lck;

IF @lck = 1 THEN

 SELECT id INTO @id_exists FROM `supla_oauth_clients` WHERE `type` = 2 LIMIT 1;

  IF @id_exists <> 0 THEN
     SELECT @id_exists INTO _id;
  ELSE 
     INSERT INTO `supla_oauth_clients`(
         `random_id`, `redirect_uris`, 
         `secret`, `allowed_grant_types`, `type`) VALUES 
     (_random_id, 'a:0:{}', _secret,'a:2:{i:0;s:8:"password";i:1;s:13:"refresh_token";}',2);
     
     SELECT RELEASE_LOCK('oauth_add_client');
  END IF;

END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_oauth_add_token_for_app` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_oauth_add_token_for_app`(IN `_user_id` INT(11), IN `_token` VARCHAR(255) CHARSET utf8, 
IN `_expires_at` INT(11), IN `_access_id` INT(11), OUT `_id` INT(11))
    NO SQL
BEGIN

SET @client_id = 0;

SELECT `id` INTO @client_id FROM `supla_oauth_clients` WHERE `type` = 2 LIMIT 1;

IF @client_id <> 0 AND EXISTS(SELECT 1 FROM `supla_accessid` WHERE `user_id` = _user_id AND `id` = _access_id) THEN 

  INSERT INTO `supla_oauth_access_tokens`(`client_id`, `user_id`, `token`, `expires_at`, `scope`, `access_id`) VALUES 
   (@client_id, _user_id, _token, _expires_at, 'channels_r channels_files', _access_id);

END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_on_channeladded` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_on_channeladded`(IN `_device_id` INT, IN `_channel_id` INT)
    NO SQL
BEGIN
                SET @type = NULL;
                SELECT type INTO @type FROM supla_dev_channel WHERE `func` = 0 AND id = _channel_id;
                IF @type = 3000 THEN
                    UPDATE supla_dev_channel SET `func` = 40 WHERE id = _channel_id;
                END IF;
            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_on_newclient` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_on_newclient`(IN `_client_id` INT)
    NO SQL
BEGIN	
			END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_on_newdevice` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_on_newdevice`(IN `_device_id` INT)
    MODIFIES SQL DATA
BEGIN
            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_amazon_alexa` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_update_amazon_alexa`(IN `_access_token` VARCHAR(1024) CHARSET utf8, IN `_refresh_token` VARCHAR(1024) CHARSET utf8, IN `_expires_in` INT, IN `_user_id` INT)
    NO SQL
BEGIN UPDATE supla_amazon_alexa SET `access_token` = _access_token, `refresh_token` = _refresh_token, `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _expires_in second) WHERE `user_id` = _user_id; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_client` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_update_client`(IN `_access_id` INT(11), IN `_name` VARCHAR(100) CHARSET utf8, 
IN `_last_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20) CHARSET utf8, 
IN `_protocol_version` INT(11), IN `_auth_key` VARCHAR(64) CHARSET utf8, IN `_id` INT(11))
    NO SQL
BEGIN

UPDATE `supla_client` 

SET 
`access_id` = _access_id,
`name` = _name, 
`last_access_date` = UTC_TIMESTAMP(),
`last_access_ipv4` = _last_ipv4, 
`software_version` = _software_version, 
`protocol_version` = _protocol_version WHERE `id` = _id;

IF _auth_key IS NOT NULL THEN
  UPDATE `supla_client` 
  SET `auth_key` = _auth_key WHERE `id` = _id AND `auth_key` IS NULL;
END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_google_home` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_update_google_home`(IN `_access_token` VARCHAR(255) CHARSET utf8, IN `_user_id` INT)
    NO SQL
BEGIN UPDATE supla_google_home SET `access_token` = _access_token WHERE `user_id` = _user_id; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_iodevice` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE PROCEDURE `supla_update_iodevice`(IN `_name` VARCHAR(100) CHARSET utf8, IN `_last_ipv4` INT(10) UNSIGNED, 
IN `_software_version` VARCHAR(10) CHARSET utf8, IN `_protocol_version` INT(11), IN `_original_location_id` INT(11), 
IN `_auth_key` VARCHAR(64) CHARSET utf8, IN `_id` INT(11))
    NO SQL
BEGIN

UPDATE `supla_iodevice` 
SET 
`name` = _name, 
`last_connected` = UTC_TIMESTAMP(),
`last_ipv4` = _last_ipv4, 
`software_version` = _software_version, 
`protocol_version` = _protocol_version, 
original_location_id = _original_location_id WHERE `id` = _id;

IF _auth_key IS NOT NULL THEN
  UPDATE `supla_iodevice` 
  SET `auth_key` = _auth_key WHERE `id` = _id AND `auth_key` IS NULL;
END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `supla_v_client`
--

/*!50001 DROP VIEW IF EXISTS `supla_v_client`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_client` AS select `c`.`id` AS `id`,`c`.`access_id` AS `access_id`,`c`.`guid` AS `guid`,`c`.`name` AS `name`,`c`.`reg_ipv4` AS `reg_ipv4`,`c`.`reg_date` AS `reg_date`,`c`.`last_access_ipv4` AS `last_access_ipv4`,`c`.`last_access_date` AS `last_access_date`,`c`.`software_version` AS `software_version`,`c`.`protocol_version` AS `protocol_version`,`c`.`enabled` AS `enabled`,`a`.`user_id` AS `user_id` from (`supla_client` `c` join `supla_accessid` `a` on((`a`.`id` = `c`.`access_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `supla_v_client_channel`
--

/*!50001 DROP VIEW IF EXISTS `supla_v_client_channel`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_client_channel` AS select `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`,ifnull(`c`.`param1`,0) AS `param1`,ifnull(`c`.`param2`,0) AS `param2`,`c`.`caption` AS `caption`,ifnull(`c`.`param3`,0) AS `param3`,`c`.`text_param1` AS `text_param1`,`c`.`text_param2` AS `text_param2`,`c`.`text_param3` AS `text_param3`,ifnull(`d`.`manufacturer_id`,0) AS `manufacturer_id`,ifnull(`d`.`product_id`,0) AS `product_id`,ifnull(`c`.`user_icon_id`,0) AS `user_icon_id`,`c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,`c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,(case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end) AS `location_id`,ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version` from (((((`supla_dev_channel` `c` join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) join `supla_location` `l` on((`l`.`id` = (case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end)))) join `supla_rel_aidloc` `r` on((`r`.`location_id` = `l`.`id`))) join `supla_accessid` `a` on((`a`.`id` = `r`.`access_id`))) join `supla_client` `cl` on((`cl`.`access_id` = `r`.`access_id`))) where ((`c`.`func` is not null) and (ifnull(`c`.`hidden`,0) = 0) and (`c`.`func` <> 0) and (`d`.`enabled` = 1) and (`l`.`enabled` = 1) and (`a`.`enabled` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `supla_v_client_channel_group`
--

/*!50001 DROP VIEW IF EXISTS `supla_v_client_channel_group`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_client_channel_group` AS select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,`g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,ifnull(`g`.`user_icon_id`,0) AS `user_icon_id`,`cl`.`id` AS `client_id` from ((((`supla_dev_channel_group` `g` join `supla_location` `l` on((`l`.`id` = `g`.`location_id`))) join `supla_rel_aidloc` `r` on((`r`.`location_id` = `l`.`id`))) join `supla_accessid` `a` on((`a`.`id` = `r`.`access_id`))) join `supla_client` `cl` on((`cl`.`access_id` = `r`.`access_id`))) where ((`g`.`func` is not null) and (ifnull(`g`.`hidden`,0) = 0) and (`g`.`func` <> 0) and (`l`.`enabled` = 1) and (`a`.`enabled` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `supla_v_client_location`
--

/*!50001 DROP VIEW IF EXISTS `supla_v_client_location`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_client_location` AS select `l`.`id` AS `id`,`l`.`caption` AS `caption`,`c`.`id` AS `client_id` from ((`supla_rel_aidloc` `al` join `supla_location` `l` on((`l`.`id` = `al`.`location_id`))) join `supla_client` `c` on((`c`.`access_id` = `al`.`access_id`))) where (`l`.`enabled` = 1) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `supla_v_device_accessid`
--

/*!50001 DROP VIEW IF EXISTS `supla_v_device_accessid`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_device_accessid` AS select `a`.`id` AS `id`,`a`.`user_id` AS `user_id`,cast(`a`.`enabled` as unsigned) AS `enabled`,`a`.`password` AS `password`,`u`.`limit_client` AS `limit_client` from (`supla_accessid` `a` join `supla_user` `u` on((`u`.`id` = `a`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `supla_v_device_location`
--

/*!50001 DROP VIEW IF EXISTS `supla_v_device_location`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_device_location` AS select `l`.`id` AS `id`,`l`.`user_id` AS `user_id`,cast(`l`.`enabled` as unsigned) AS `enabled`,`u`.`limit_iodev` AS `limit_iodev`,`l`.`password` AS `password` from (`supla_location` `l` join `supla_user` `u` on((`u`.`id` = `l`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `supla_v_rel_cg`
--

/*!50001 DROP VIEW IF EXISTS `supla_v_rel_cg`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_rel_cg` AS select `r`.`group_id` AS `group_id`,`r`.`channel_id` AS `channel_id`,`c`.`iodevice_id` AS `iodevice_id`,`d`.`protocol_version` AS `protocol_version`,`g`.`client_id` AS `client_id`,`c`.`hidden` AS `channel_hidden` from (((`supla_v_client_channel_group` `g` join `supla_rel_cg` `r` on((`r`.`group_id` = `g`.`id`))) join `supla_dev_channel` `c` on((`c`.`id` = `r`.`channel_id`))) join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) where (`d`.`enabled` = 1) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `supla_v_user_channel_group`
--

/*!50001 DROP VIEW IF EXISTS `supla_v_user_channel_group`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_user_channel_group` AS select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,`g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,`rel`.`channel_id` AS `channel_id`,`c`.`iodevice_id` AS `iodevice_id` from ((((`supla_dev_channel_group` `g` join `supla_location` `l` on((`l`.`id` = `g`.`location_id`))) join `supla_rel_cg` `rel` on((`rel`.`group_id` = `g`.`id`))) join `supla_dev_channel` `c` on((`c`.`id` = `rel`.`channel_id`))) join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) where ((`g`.`func` is not null) and (ifnull(`g`.`hidden`,0) = 0) and (`g`.`func` <> 0) and (`l`.`enabled` = 1) and (`d`.`enabled` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-12-28 18:49:58
