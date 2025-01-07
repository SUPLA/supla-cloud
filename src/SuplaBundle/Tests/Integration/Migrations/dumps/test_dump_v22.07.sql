-- mysqldump -h 127.0.0.1 --routines -u root -p supla > test_dump_v22.07.sql
--
-- MySQL dump 10.13  Distrib 5.7.28, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: supla
-- ------------------------------------------------------
-- Server version	5.7.37

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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `device_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `platform` tinyint(4) NOT NULL,
  `latest_software_version` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fparam1` int(11) NOT NULL,
  `fparam2` int(11) NOT NULL,
  `fparam3` int(11) NOT NULL DEFAULT '0',
  `fparam4` int(11) NOT NULL DEFAULT '0',
  `protocols` tinyint(4) NOT NULL,
  `host` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `port` int(11) NOT NULL,
  `path` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `is_synced` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `device_name` (`device_name`),
  KEY `latest_software_version` (`latest_software_version`),
  KEY `fparam1` (`fparam1`),
  KEY `fparam2` (`fparam2`),
  KEY `platform` (`platform`),
  KEY `device_id` (`device_id`),
  KEY `fparam3` (`fparam3`),
  KEY `fparam4` (`fparam4`)
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
  `version` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('20170101000000','2022-10-01 12:21:33'),('20170414101854','2022-10-01 12:21:34'),('20170612204116','2022-10-01 12:21:34'),('20170818114139','2022-10-01 12:21:35'),('20171013140904','2022-10-01 12:21:36'),('20171208222022','2022-10-01 12:21:36'),('20171210105120','2022-10-01 12:21:36'),('20180108224520','2022-10-01 12:21:36'),('20180113234138','2022-10-01 12:21:36'),('20180116184415','2022-10-01 12:21:36'),('20180203231115','2022-10-01 12:21:36'),('20180208145738','2022-10-01 12:21:36'),('20180224184251','2022-10-01 12:21:36'),('20180324222844','2022-10-01 12:21:36'),('20180326134725','2022-10-01 12:21:36'),('20180403175932','2022-10-01 12:21:36'),('20180403203101','2022-10-01 12:21:36'),('20180403211558','2022-10-01 12:21:37'),('20180411202101','2022-10-01 12:21:37'),('20180411203913','2022-10-01 12:21:37'),('20180416201401','2022-10-01 12:21:37'),('20180423121539','2022-10-01 12:21:37'),('20180507095139','2022-10-01 12:21:37'),('20180518131234','2022-10-01 12:21:37'),('20180707221458','2022-10-01 12:21:37'),('20180717094843','2022-10-01 12:21:37'),('20180723132652','2022-10-01 12:21:37'),('20180807083217','2022-10-01 12:21:38'),('20180812205513','2022-10-01 12:21:38'),('20180814155501','2022-10-01 12:21:38'),('20180914222230','2022-10-01 12:21:38'),('20181001221229','2022-10-01 12:21:38'),('20181007112610','2022-10-01 12:21:38'),('20181019115859','2022-10-01 12:21:39'),('20181024164957','2022-10-01 12:21:39'),('20181025171850','2022-10-01 12:21:39'),('20181026171557','2022-10-01 12:21:39'),('20181105144611','2022-10-01 12:21:39'),('20181126225634','2022-10-01 12:21:40'),('20181129170610','2022-10-01 12:21:40'),('20181129195431','2022-10-01 12:21:40'),('20181129231132','2022-10-01 12:21:40'),('20181204174603','2022-10-01 12:21:40'),('20181205092324','2022-10-01 12:21:40'),('20181222001450','2022-10-01 12:21:40'),('20190105130410','2022-10-01 12:21:40'),('20190117075805','2022-10-01 12:21:40'),('20190219184847','2022-10-01 12:21:40'),('20190325215115','2022-10-01 12:21:40'),('20190401151822','2022-10-01 12:21:40'),('20190720215803','2022-10-01 12:21:40'),('20190813232026','2022-10-01 12:21:40'),('20190815154016','2022-10-01 12:21:41'),('20191226160845','2022-10-01 12:21:41'),('20200108201101','2022-10-01 12:21:41'),('20200123235701','2022-10-01 12:21:41'),('20200124084227','2022-10-01 12:21:41'),('20200204170901','2022-10-01 12:21:41'),('20200210145902','2022-10-01 12:21:41'),('20200229122103','2022-10-01 12:21:42'),('20200322123636','2022-10-01 12:21:42'),('20200412183701','2022-10-01 12:21:42'),('20200414213205','2022-10-01 12:21:42'),('20200416225304','2022-10-01 12:21:42'),('20200419190150','2022-10-01 12:21:42'),('20200430113342','2022-10-01 12:21:42'),('20200514132030','2022-10-01 12:21:42'),('20200515102311','2022-10-01 12:21:42'),('20200518171230','2022-10-01 12:21:42'),('20200724155001','2022-10-01 12:21:42'),('20200807131101','2022-10-01 12:21:42'),('20200811141801','2022-10-01 12:21:42'),('20200813113801','2022-10-01 12:21:42'),('20200813133501','2022-10-01 12:21:42'),('20200911231401','2022-10-01 12:21:42'),('20201113112233','2022-10-01 12:21:43'),('20201213133718','2022-10-01 12:21:43'),('20201214102230','2022-10-01 12:21:43'),('20210105164727','2022-10-01 12:21:43'),('20210118124714','2022-10-01 12:21:43'),('20210228201414','2022-10-01 12:21:43'),('20210323095216','2022-10-01 12:21:43'),('20210419201821','2022-10-01 12:21:43'),('20210525104812','2022-10-01 12:21:43'),('20210915221319','2022-10-01 12:21:43'),('20210917203710','2022-10-01 12:21:43'),('20211005074509','2022-10-01 12:21:44'),('20211108120835','2022-10-01 12:21:44'),('20211123193415','2022-10-01 12:21:44'),('20211205215406','2022-10-01 12:21:44'),('20211218174444','2022-10-01 12:21:44'),('20220208164512','2022-10-01 12:21:44'),('20220222110707','2022-10-01 12:21:44'),('20220309061811','2022-10-01 12:21:44'),('20220309061812','2022-10-01 12:21:44'),('20220404100406','2022-10-01 12:21:44'),('20220718203129','2022-10-01 12:21:44'),('20220719210858','2022-10-01 12:21:44');
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
  `caption` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `active_from` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `active_to` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `active_hours` varchar(768) COLLATE utf8_unicode_ci DEFAULT NULL,
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
INSERT INTO `supla_accessid` VALUES (1,1,'d229579a','Access Identifier #2',1,NULL,NULL,NULL),(2,2,'9e713699','Access Identifier #2',1,NULL,NULL,NULL),(3,1,'ca97875f','Wspólny',1,NULL,NULL,NULL),(4,1,'007cddc9','Dzieci',1,NULL,NULL,NULL),(5,2,'6cb5aea7','Supler Access ID',1,NULL,NULL,NULL);
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
CREATE TABLE `supla_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `event` smallint(5) unsigned NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `ipv4` int(10) unsigned DEFAULT NULL COMMENT '(DC2Type:ipaddress)',
  `text_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `int_param` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EFE348F4A76ED395` (`user_id`),
  KEY `supla_audit_event_idx` (`event`),
  KEY `supla_audit_ipv4_idx` (`ipv4`),
  KEY `supla_audit_created_at_idx` (`created_at`),
  KEY `supla_audit_int_param` (`int_param`),
  CONSTRAINT `FK_EFE348F4A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_audit`
--

LOCK TABLES `supla_audit` WRITE;
/*!40000 ALTER TABLE `supla_audit` DISABLE KEYS */;
INSERT INTO `supla_audit` VALUES (1,NULL,2,'2022-10-01 12:22:05',2130706433,'',0);
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
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `reg_ipv4` int(10) unsigned DEFAULT NULL COMMENT '(DC2Type:ipaddress)',
  `reg_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `last_access_ipv4` int(10) unsigned DEFAULT NULL COMMENT '(DC2Type:ipaddress)',
  `last_access_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `software_version` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `protocol_version` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auth_key` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `caption` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `supla_client` VALUES (1,4,_binary '3886228','HTC One M8',1,384420790,'2022-08-05 23:06:18',1999945248,'2022-09-26 20:47:00','1.33',50,1,NULL,NULL,NULL),(2,4,_binary '1666703','iPhone 6s',0,966931705,'2022-09-09 10:25:17',3691610994,'2022-09-25 20:52:42','1.5',12,1,NULL,NULL,NULL),(3,4,_binary '7945692','Nokia 3310',1,308040682,'2022-08-25 14:04:50',1467105717,'2022-09-29 06:21:26','1.21',16,1,NULL,NULL,NULL),(4,4,_binary '905478','Samsung Galaxy Tab S2',1,2543719861,'2022-09-05 16:07:40',1500726494,'2022-09-29 15:47:09','1.62',3,1,NULL,NULL,NULL),(5,NULL,_binary '9451166','Apple iPad',1,1483876280,'2022-09-18 00:15:02',2995745180,'2022-09-25 23:59:45','1.77',47,1,NULL,NULL,NULL),(6,5,_binary '5196075','SUPLER PHONE',0,2184125199,'2022-09-22 21:01:20',1940428994,'2022-09-25 10:51:39','1.41',35,2,NULL,NULL,NULL);
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
                                     `caption` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                     `type` int(11) NOT NULL,
                                     `func` int(11) NOT NULL,
                                     `flist` int(11) DEFAULT NULL,
                                     `param1` int(11) NOT NULL,
                                     `param2` int(11) NOT NULL,
                                     `param3` int(11) NOT NULL,
                                     `text_param1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                     `text_param2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                     `text_param3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                     `alt_icon` int(11) DEFAULT NULL,
                                     `hidden` tinyint(1) NOT NULL DEFAULT '0',
                                     `location_id` int(11) DEFAULT NULL,
                                     `flags` int(11) DEFAULT NULL,
                                     `user_icon_id` int(11) DEFAULT NULL,
                                     `user_config` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
                                     `param4` int(11) NOT NULL DEFAULT '0',
                                     `properties` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
                                     PRIMARY KEY (`id`),
                                     UNIQUE KEY `UNIQUE_CHANNEL` (`iodevice_id`,`channel_number`),
                                     KEY `IDX_81E928C9125F95D6` (`iodevice_id`),
                                     KEY `IDX_81E928C9A76ED395` (`user_id`),
                                     KEY `IDX_81E928C964D218E` (`location_id`),
                                     KEY `IDX_81E928C9CB4C938` (`user_icon_id`),
                                     KEY `supla_dev_channel_param1_idx` (`param1`),
                                     KEY `supla_dev_channel_param2_idx` (`param2`),
                                     KEY `supla_dev_channel_param3_idx` (`param3`),
                                     KEY `supla_dev_channel_param4_idx` (`param4`),
                                     CONSTRAINT `FK_81E928C9125F95D6` FOREIGN KEY (`iodevice_id`) REFERENCES `supla_iodevice` (`id`) ON DELETE CASCADE,
                                     CONSTRAINT `FK_81E928C964D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
                                     CONSTRAINT `FK_81E928C9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`),
                                     CONSTRAINT `FK_81E928C9CB4C938` FOREIGN KEY (`user_icon_id`) REFERENCES `supla_user_icons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=386 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_dev_channel`
--

LOCK
TABLES `supla_dev_channel` WRITE;
/*!40000 ALTER TABLE `supla_dev_channel` DISABLE KEYS */;
INSERT INTO `supla_dev_channel`
VALUES (1, 1, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (2, 1, 1, 1, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (3, 1, 1, 2, NULL, 11000, 700, NULL, 1, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"HOLD\",\"TURN_ON\",\"TOGGLE_X4\"]}'),
       (4, 2, 1, 0, 'Dolorem quod reprehenderit.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (5, 2, 1, 1, NULL, 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (6, 2, 1, 2, 'Qui quaerat sit.', 2900, 20, 65535, 500, 8, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (7, 2, 1, 3, 'Velit dolorum nobis.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL, NULL, 0, NULL),
       (8, 2, 1, 4, 'Velit cum earum.', 1000, 60, NULL, 6, 0, 0, NULL, NULL, NULL, 0, 0, 2, 0, NULL, NULL, 0, NULL),
       (9, 2, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 1, 0, NULL, NULL, 0, NULL),
       (10, 2, 1, 6, 'Ut veritatis eum.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (11, 2, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"TURN_OFF\",\"TOGGLE_X4\",\"SHORT_PRESS_X5\"]}'),
       (12, 2, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL, NULL, 0, NULL),
       (13, 2, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL, NULL, 0, NULL),
       (14, 3, 1, 0, 'Quibusdam sint non voluptas.', 4010, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (15, 3, 1, 1, 'Nemo vitae eos in aliquam.', 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (16, 4, 1, 0, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (17, 4, 1, 1, NULL, 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (18, 4, 1, 2, NULL, 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (19, 4, 1, 3, NULL, 1000, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (20, 4, 1, 4, NULL, 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (21, 4, 1, 5, 'Ipsum neque et.', 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (22, 4, 1, 6, 'Vel qui aspernatur expedita.', 1000, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (23, 4, 1, 7, 'Ipsam alias et.', 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (24, 4, 1, 8, 'Tempora distinctio consequatur eius.', 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        NULL),
       (25, 4, 1, 9, 'Aut similique omnis.', 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (26, 4, 1, 10, 'In expedita placeat.', 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (27, 4, 1, 11, 'Ipsam eveniet blanditiis.', 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (28, 4, 1, 12, 'Dicta architecto.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (29, 4, 1, 13, 'Fuga doloribus illum.', 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (30, 4, 1, 14, 'Iusto laboriosam et.', 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (31, 4, 1, 15, 'Dolorum itaque libero.', 1010, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (32, 4, 1, 16, NULL, 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (33, 4, 1, 17, NULL, 1010, 240, NULL, 0, 0, 1, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (34, 4, 1, 18, NULL, 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (35, 4, 1, 19, 'Perspiciatis et earum velit.', 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (36, 4, 1, 20, NULL, 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (37, 4, 1, 21, NULL, 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (38, 4, 1, 22, NULL, 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (39, 4, 1, 23, 'Laudantium et placeat.', 2000, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (40, 4, 1, 24, 'Delectus iste facilis.', 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (41, 4, 1, 25, 'Reprehenderit voluptate sint.', 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (42, 4, 1, 26, 'Natus qui consequatur eaque voluptates.', 2010, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        NULL),
       (43, 4, 1, 27, NULL, 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (44, 4, 1, 28, 'Est eius reprehenderit voluptas necessitatibus.', 2010, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        NULL, 0, NULL),
       (45, 4, 1, 29, 'Tempora corrupti aperiam.', 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (46, 4, 1, 30, NULL, 2010, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (47, 4, 1, 31, NULL, 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (48, 4, 1, 32, NULL, 2020, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (49, 4, 1, 33, 'Sunt quia nostrum ut.', 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (50, 4, 1, 34, 'Possimus voluptatem explicabo quia non.', 2020, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        NULL),
       (51, 4, 1, 35, 'Sequi libero dolorum nisi.', 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (52, 4, 1, 36, NULL, 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (53, 4, 1, 37, 'Saepe aperiam eos vitae.', 2020, 110, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (54, 4, 1, 38, 'Ut neque mollitia.', 2020, 115, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (55, 4, 1, 39, NULL, 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (56, 4, 1, 40, 'Et labore tenetur eaque.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (57, 4, 1, 41, 'Saepe animi tempore fugiat.', 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (58, 4, 1, 42, NULL, 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (59, 4, 1, 43, NULL, 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (60, 4, 1, 44, NULL, 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (61, 4, 1, 45, NULL, 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (62, 4, 1, 46, NULL, 3034, 40, NULL, 0, 12, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (63, 4, 1, 47, 'Commodi quis impedit vel.', 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (64, 4, 1, 48, NULL, 3038, 45, NULL, 0, 123, -123, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (65, 4, 1, 49, 'Eius sequi laudantium vel.', 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (66, 4, 1, 50, 'Suscipit incidunt consequatur sint.', 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        NULL),
       (67, 4, 1, 51, NULL, 3048, 270, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (68, 4, 1, 52, NULL, 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (69, 4, 1, 53, 'Illo qui magnam nesciunt.', 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (70, 4, 1, 54, NULL, 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (71, 4, 1, 55, 'Libero qui explicabo.', 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (72, 4, 1, 56, 'Tempore expedita numquam.', 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (73, 4, 1, 57, 'Est placeat similique sunt.', 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"countersAvailable\":[\"reverseActiveEnergyBalanced\",\"reverseActiveEnergy\"]}'),
       (74, 4, 1, 58, NULL, 5010, 315, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 1, 0, NULL, NULL, 114, NULL),
       (75, 4, 1, 59, 'Ex sunt et.', 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (76, 4, 1, 60, 'Autem velit dolorem.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (77, 4, 1, 61, NULL, 5010, 340, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (78, 4, 1, 62, 'Quisquam inventore voluptas quos.', 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (79, 4, 1, 63, NULL, 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (80, 4, 1, 64, 'Dolorum deleniti ullam.', 7000, 500, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (81, 4, 1, 65, NULL, 7010, 510, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (82, 4, 1, 66, 'Velit ratione dignissimos.', 9000, 520, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (83, 4, 1, 67, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"SHORT_PRESS_X3\",\"SHORT_PRESS_X5\",\"TOGGLE_X2\"]}'),
       (84, 4, 1, 68, 'Architecto error cum ea.', 12000, 810, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (85, 4, 1, 69, 'Sed ut maxime fugiat ut.', 12000, 800, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (86, 5, 1, 0, 'Non dolores modi dolores.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (87, 5, 1, 1, NULL, 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (88, 5, 1, 2, NULL, 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (89, 5, 1, 3, 'Velit non et.', 1000, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (90, 5, 1, 4, 'Officiis voluptatibus quam quaerat.', 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (91, 5, 1, 5, 'Sint consequuntur voluptatem.', 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (92, 5, 1, 6, 'Deserunt mollitia quos quia.', 1000, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (93, 5, 1, 7, 'Totam at pariatur.', 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (94, 5, 1, 8, NULL, 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (95, 5, 1, 9, 'Magnam quisquam qui aspernatur.', 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (96, 5, 1, 10, 'Quia consequatur molestiae.', 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (97, 5, 1, 11, 'Tenetur et ut.', 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (98, 5, 1, 12, 'Consectetur aut soluta.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (99, 5, 1, 13, 'Non qui consequuntur rem.', 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (100, 5, 1, 14, 'Praesentium suscipit quam.', 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (101, 5, 1, 15, 'Laudantium vel.', 1010, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (102, 5, 1, 16, NULL, 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (103, 5, 1, 17, 'Numquam sit corrupti quisquam totam.', 1010, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        NULL),
       (104, 5, 1, 18, NULL, 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (105, 5, 1, 19, 'Vel at quia.', 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (106, 5, 1, 20, NULL, 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (107, 5, 1, 21, 'Totam consequatur tenetur voluptatum.', 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        NULL),
       (108, 5, 1, 22, 'Est eos est et.', 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (109, 5, 1, 23, 'Velit tempora voluptatibus est.', 2000, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (110, 5, 1, 24, NULL, 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (111, 5, 1, 25, NULL, 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (112, 5, 1, 26, NULL, 2010, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (113, 5, 1, 27, NULL, 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (114, 5, 1, 28, 'Rerum ad vitae officiis.', 2010, 130, NULL, 74, 0, 0, NULL, NULL, NULL, 0, 0, 2, 0, NULL, NULL, 0, NULL),
       (115, 5, 1, 29, NULL, 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (116, 5, 1, 30, NULL, 2010, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (117, 5, 1, 31, 'Est debitis non consequatur.', 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (118, 5, 1, 32, NULL, 2020, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (119, 5, 1, 33, 'Tempora ea quibusdam dolorem.', 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (120, 5, 1, 34, 'Eos odio aspernatur nostrum minus.', 2020, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (121, 5, 1, 35, 'Laudantium amet dolor incidunt hic.', 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{"relatedMeterChannelId": 143}', 0,
        NULL),
       (122, 5, 1, 36, NULL, 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (123, 5, 1, 37, 'Dolor earum quia omnis quia.', 2020, 110, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (124, 5, 1, 38, NULL, 2020, 115, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (125, 5, 1, 39, 'In et ex hic ratione.', 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (126, 5, 1, 40, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (127, 5, 1, 41, 'Doloremque eos et qui.', 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (128, 5, 1, 42, 'Ullam voluptatem placeat delectus.', 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (129, 5, 1, 43, 'Quae incidunt laboriosam et.', 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (130, 5, 1, 44, 'Dolores deserunt deleniti quas.', 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (131, 5, 1, 45, NULL, 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (132, 5, 1, 46, NULL, 3034, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (133, 5, 1, 47, NULL, 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (134, 5, 1, 48, 'Accusantium voluptatem quis.', 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (135, 5, 1, 49, NULL, 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (136, 5, 1, 50, NULL, 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (137, 5, 1, 51, NULL, 3048, 270, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (138, 5, 1, 52, 'Quia soluta et itaque.', 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (139, 5, 1, 53, NULL, 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (140, 5, 1, 54, NULL, 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (141, 5, 1, 55, NULL, 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (142, 5, 1, 56, 'Est impedit et placeat.', 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (143, 5, 1, 57, 'Sint eos repudiandae.', 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 1, NULL, 0, NULL,
        '{"relatedRelayChannelId":121}', 0,
        '{\"countersAvailable\":[\"reverseActiveEnergyBalanced\",\"forwardActiveEnergyBalanced\",\"reverseActiveEnergy\",\"forwardReactiveEnergy\"]}'),
       (144, 5, 1, 58, 'Consequatur temporibus ipsam eos.', 5010, 315, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 9999,
        NULL),
       (145, 5, 1, 59, 'Sequi optio illo dolores harum.', 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (146, 5, 1, 60, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (147, 5, 1, 61, 'Qui culpa doloremque eligendi sit.', 5010, 340, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        NULL),
       (148, 5, 1, 62, 'Ad voluptatem eum.', 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (149, 5, 1, 63, 'Sed aliquid rem sint.', 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (150, 5, 1, 64, NULL, 7000, 500, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (151, 5, 1, 65, NULL, 7010, 510, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (152, 5, 1, 66, 'Eius et.', 9000, 520, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (153, 5, 1, 67, 'Minus et voluptas.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X5\",\"TOGGLE_X4\",\"SHORT_PRESS_X2\",\"SHORT_PRESS_X4\"]}'),
       (154, 5, 1, 68, NULL, 12000, 810, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (155, 5, 1, 69, NULL, 12000, 800, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (156, 6, 1, 0, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (157, 6, 1, 1, 'Et non harum consequatur.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (158, 6, 1, 2, 'Quia ut.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (159, 6, 1, 3, 'Et quam voluptates ipsum neque.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (160, 6, 1, 4, 'Omnis quia eos architecto.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (161, 6, 1, 5, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (162, 6, 1, 6, 'Et dignissimos est quae.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (163, 6, 1, 7, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (164, 6, 1, 8, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (165, 6, 1, 9, 'Nam qui error.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (166, 7, 1, 0, 'Voluptatem aperiam nihil veniam.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (167, 7, 1, 1, 'Ea quis quam aliquam.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (168, 7, 1, 2, 'Error eveniet est.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (169, 7, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 16384, NULL, NULL, 0, NULL),
       (170, 7, 1, 4, 'Aspernatur voluptatum possimus.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (171, 7, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (172, 7, 1, 6, 'Ipsa qui qui deserunt.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (173, 7, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X5\",\"TOGGLE_X2\",\"SHORT_PRESS_X2\"]}'),
       (174, 7, 1, 8, 'Adipisci et commodi pariatur.', 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (175, 7, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL, NULL, 0, NULL),
       (176, 8, 1, 0, 'Hic dolore est nihil.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (177, 8, 1, 1, 'Aut impedit vel rerum.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (178, 8, 1, 2, 'Est debitis ut hic.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (179, 8, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 16384, NULL, NULL, 0, NULL),
       (180, 8, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (181, 8, 1, 5, 'Temporibus unde.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (182, 8, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (183, 8, 1, 7, 'Optio non sit labore.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X2\",\"HOLD\"]}'),
       (184, 8, 1, 8, 'Perferendis non.', 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (185, 8, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL, NULL, 0, NULL),
       (186, 9, 1, 0, 'Quasi autem beatae.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (187, 9, 1, 1, NULL, 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (188, 9, 1, 2, 'Molestiae iste delectus in.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (189, 9, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL, NULL, 0, NULL),
       (190, 9, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (191, 9, 1, 5, 'Eaque facere quae quo.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (192, 9, 1, 6, 'Non dolores in.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (193, 9, 1, 7, 'Consectetur id molestias molestiae.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\",\"HOLD\",\"TOGGLE_X5\",\"TURN_OFF\",\"TOGGLE_X3\"]}'),
       (194, 9, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (195, 9, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 8192, NULL, NULL, 0, NULL),
       (196, 10, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (197, 10, 1, 1, 'Fugiat sapiente tenetur.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (198, 10, 1, 2, 'Numquam rerum et quam nemo.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (199, 10, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL, NULL, 0, NULL),
       (200, 10, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (201, 10, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (202, 10, 1, 6, 'Ullam quia deserunt.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (203, 10, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X5\",\"SHORT_PRESS_X4\",\"SHORT_PRESS_X5\",\"TOGGLE_X2\",\"HOLD\"]}'),
       (204, 10, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (205, 10, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL, NULL, 0, NULL),
       (206, 11, 1, 0, 'Sunt earum quis ut.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (207, 11, 1, 1, NULL, 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (208, 11, 1, 2, 'Id minima ipsam.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (209, 11, 1, 3, 'Ad tempora.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 16384, NULL, NULL, 0, NULL),
       (210, 11, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (211, 11, 1, 5, 'Consequuntur et est.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (212, 11, 1, 6, 'Dolores est debitis.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (213, 11, 1, 7, 'Impedit atque vitae laborum.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X3\",\"TOGGLE_X2\",\"SHORT_PRESS_X2\",\"SHORT_PRESS_X1\",\"HOLD\"]}'),
       (214, 11, 1, 8, 'Quia beatae optio.', 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL, NULL, 0, NULL),
       (215, 11, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL, NULL, 0, NULL),
       (216, 12, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (217, 12, 1, 1, 'Blanditiis accusantium temporibus.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (218, 12, 1, 2, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (219, 12, 1, 3, 'Ipsa in a.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL, NULL, 0, NULL),
       (220, 12, 1, 4, 'Vero earum et.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (221, 12, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (222, 12, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (223, 12, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"TURN_OFF\"]}'),
       (224, 12, 1, 8, 'Sit temporibus doloribus assumenda.', 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL, NULL, 0,
        NULL),
       (225, 12, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 8192, NULL, NULL, 0, NULL),
       (226, 13, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (227, 13, 1, 1, 'Et alias ratione.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (228, 13, 1, 2, 'Nulla molestiae hic neque.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (229, 13, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 16384, NULL, NULL, 0, NULL),
       (230, 13, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (231, 13, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (232, 13, 1, 6, 'Et molestiae dolorem aliquam quia.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (233, 13, 1, 7, 'Eaque consectetur possimus numquam.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X3\",\"SHORT_PRESS_X4\",\"TOGGLE_X4\"]}'),
       (234, 13, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (235, 13, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 8192, NULL, NULL, 0, NULL),
       (236, 14, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (237, 14, 1, 1, NULL, 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (238, 14, 1, 2, 'Maxime optio dolorem beatae.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (239, 14, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL, NULL, 0, NULL),
       (240, 14, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (241, 14, 1, 5, 'Ratione est voluptatem ad.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (242, 14, 1, 6, 'Veritatis consequatur aut.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (243, 14, 1, 7, 'Dolores dolorem hic delectus.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X2\",\"TOGGLE_X5\",\"TURN_OFF\"]}'),
       (244, 14, 1, 8, 'Magnam est ut nemo.', 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (245, 14, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL, NULL, 0, NULL),
       (246, 15, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (247, 15, 1, 1, 'Asperiores sequi sed tempore.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (248, 15, 1, 2, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (249, 15, 1, 3, 'Officia odio quia consequatur.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL, NULL, 0, NULL),
       (250, 15, 1, 4, 'Itaque ab architecto.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (251, 15, 1, 5, 'Qui amet voluptas voluptas.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (252, 15, 1, 6, 'Eos veritatis tenetur.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (253, 15, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X2\",\"TURN_OFF\",\"SHORT_PRESS_X2\"]}'),
       (254, 15, 1, 8, 'Est nam officiis fuga.', 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 4096, NULL, NULL, 0, NULL),
       (255, 15, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL, NULL, 0, NULL),
       (256, 16, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (257, 16, 1, 1, NULL, 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (258, 16, 1, 2, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (259, 16, 1, 3, 'Ipsum et beatae.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL, NULL, 0, NULL),
       (260, 16, 1, 4, 'Qui laboriosam et eos.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (261, 16, 1, 5, 'Cumque voluptatem voluptatem nostrum.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0,
        NULL),
       (262, 16, 1, 6, 'In adipisci sequi dolorem.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (263, 16, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\"]}'),
       (264, 16, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (265, 16, 1, 9, 'Quia minima voluptas.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL, NULL, 0, NULL),
       (266, 17, 1, 0, 'Id sint ut.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (267, 17, 1, 1, 'Porro voluptatem ea.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (268, 17, 1, 2, 'Dolorem perspiciatis ut rerum.', 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (269, 17, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL, NULL, 0, NULL),
       (270, 17, 1, 4, 'Quia ex aliquid.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (271, 17, 1, 5, 'Et mollitia iusto cum.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (272, 17, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (273, 17, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X3\",\"SHORT_PRESS_X2\",\"SHORT_PRESS_X4\"]}'),
       (274, 17, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL, NULL, 0, NULL),
       (275, 17, 1, 9, 'Placeat molestiae omnis rerum.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL, NULL, 0, NULL),
       (276, 18, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (277, 18, 1, 1, 'Distinctio et quas esse.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (278, 18, 1, 2, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (279, 18, 1, 3, 'Et eum dolorum numquam.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL, NULL, 0, NULL),
       (280, 18, 1, 4, 'Enim eos hic.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (281, 18, 1, 5, 'Assumenda autem alias beatae et.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (282, 18, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (283, 18, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X2\"]}'),
       (284, 18, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL, NULL, 0, NULL),
       (285, 18, 1, 9, 'Delectus enim iste exercitationem.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL, NULL, 0,
        NULL),
       (286, 19, 1, 0, 'Non non id in.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (287, 19, 1, 1, 'Qui eum suscipit.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (288, 19, 1, 2, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (289, 19, 1, 3, 'Quia qui quaerat sint.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL, NULL, 0, NULL),
       (290, 19, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (291, 19, 1, 5, 'Impedit aliquam quo adipisci.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (292, 19, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (293, 19, 1, 7, 'Quisquam reprehenderit accusamus.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"HOLD\",\"SHORT_PRESS_X1\",\"TURN_OFF\"]}'),
       (294, 19, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (295, 19, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL, NULL, 0, NULL),
       (296, 20, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (297, 20, 1, 1, NULL, 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (298, 20, 1, 2, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (299, 20, 1, 3, 'Consequatur vel quia.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL, NULL, 0, NULL),
       (300, 20, 1, 4, 'Et nulla perferendis consequatur.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (301, 20, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (302, 20, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (303, 20, 1, 7, 'Voluptate et ducimus.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X3\"]}'),
       (304, 20, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 4096, NULL, NULL, 0, NULL),
       (305, 20, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 8192, NULL, NULL, 0, NULL),
       (306, 21, 1, 0, 'Omnis architecto eaque et.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (307, 21, 1, 1, 'Nihil voluptatem magni.', 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (308, 21, 1, 2, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (309, 21, 1, 3, 'Facilis id illum quos.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL, NULL, 0, NULL),
       (310, 21, 1, 4, 'Vero occaecati illum impedit.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0, NULL),
       (311, 21, 1, 5, 'Qui natus et pariatur.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (312, 21, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL, NULL, 0, NULL),
       (313, 21, 1, 7, 'Aut praesentium et iste.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X2\",\"TURN_OFF\",\"SHORT_PRESS_X3\",\"TOGGLE_X1\",\"SHORT_PRESS_X4\"]}'),
       (314, 21, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL, NULL, 0, NULL),
       (315, 21, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL, NULL, 0, NULL),
       (316, 22, 2, 0, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (317, 22, 2, 1, NULL, 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (318, 22, 2, 2, 'Temporibus culpa qui.', 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (319, 22, 2, 3, 'Sit facere culpa consequatur.', 1000, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (320, 22, 2, 4, NULL, 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (321, 22, 2, 5, 'Modi et totam cum.', 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (322, 22, 2, 6, NULL, 1000, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (323, 22, 2, 7, 'Dolores alias et.', 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (324, 22, 2, 8, 'Id quae in.', 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (325, 22, 2, 9, NULL, 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (326, 22, 2, 10, 'Rem placeat et.', 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (327, 22, 2, 11, 'Eaque quis saepe.', 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (328, 22, 2, 12, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (329, 22, 2, 13, 'Dignissimos facere non.', 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (330, 22, 2, 14, 'Deserunt rem eos in.', 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (331, 22, 2, 15, 'Dolores natus accusantium.', 1010, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (332, 22, 2, 16, 'Suscipit id doloribus.', 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (333, 22, 2, 17, NULL, 1010, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (334, 22, 2, 18, NULL, 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (335, 22, 2, 19, NULL, 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (336, 22, 2, 20, 'Aut odit architecto voluptas.', 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (337, 22, 2, 21, 'Commodi numquam.', 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (338, 22, 2, 22, NULL, 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (339, 22, 2, 23, 'Qui quod nihil officia.', 2000, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (340, 22, 2, 24, NULL, 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (341, 22, 2, 25, NULL, 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (342, 22, 2, 26, 'Aliquid quas.', 2010, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (343, 22, 2, 27, NULL, 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (344, 22, 2, 28, 'Dicta nihil et.', 2010, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (345, 22, 2, 29, 'Velit nihil et.', 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (346, 22, 2, 30, NULL, 2010, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (347, 22, 2, 31, NULL, 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (348, 22, 2, 32, 'Maiores minus in aperiam.', 2020, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (349, 22, 2, 33, NULL, 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (350, 22, 2, 34, NULL, 2020, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (351, 22, 2, 35, NULL, 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (352, 22, 2, 36, 'Porro suscipit perspiciatis non.', 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (353, 22, 2, 37, 'Totam unde est ea fugiat.', 2020, 110, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (354, 22, 2, 38, NULL, 2020, 115, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (355, 22, 2, 39, 'Aut ipsum fuga vitae omnis.', 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (356, 22, 2, 40, 'Commodi ex non itaque neque.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (357, 22, 2, 41, NULL, 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (358, 22, 2, 42, NULL, 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (359, 22, 2, 43, 'Qui explicabo excepturi.', 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (360, 22, 2, 44, 'Doloremque aliquid.', 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (361, 22, 2, 45, 'Quod perferendis quis quia ea.', 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (362, 22, 2, 46, 'Iure qui animi.', 3034, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (363, 22, 2, 47, 'Ratione suscipit vel repellendus repudiandae.', 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        NULL, 0, NULL),
       (364, 22, 2, 48, NULL, 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (365, 22, 2, 49, NULL, 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (366, 22, 2, 50, 'Qui maiores.', 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (367, 22, 2, 51, 'Velit aut ut.', 3048, 270, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (368, 22, 2, 52, NULL, 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (369, 22, 2, 53, NULL, 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (370, 22, 2, 54, NULL, 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (371, 22, 2, 55, 'Perspiciatis quis temporibus.', 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (372, 22, 2, 56, 'Voluptas magni perspiciatis temporibus.', 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        NULL),
       (373, 22, 2, 57, NULL, 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"countersAvailable\":[\"reverseActiveEnergy\",\"forwardReactiveEnergy\",\"forwardActiveEnergy\",\"reverseReactiveEnergy\",\"forwardActiveEnergyBalanced\",\"reverseActiveEnergyBalanced\"]}'),
       (374, 22, 2, 58, NULL, 5010, 315, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (375, 22, 2, 59, 'Dolorem aut quis.', 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (376, 22, 2, 60, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (377, 22, 2, 61, 'Minus enim omnis.', 5010, 340, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (378, 22, 2, 62, NULL, 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (379, 22, 2, 63, NULL, 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (380, 22, 2, 64, NULL, 7000, 500, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (381, 22, 2, 65, NULL, 7010, 510, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (382, 22, 2, 66, 'Culpa est sit culpa.', 9000, 520, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (383, 22, 2, 67, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{\"actionTriggerCapabilities\":[\"HOLD\",\"SHORT_PRESS_X5\"]}'),
       (384, 22, 2, 68, NULL, 12000, 810, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (385, 22, 2, 69, 'Minus sed voluptas.', 12000, 800, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL);
/*!40000 ALTER TABLE `supla_dev_channel` ENABLE KEYS */;
UNLOCK
TABLES;

--
-- Table structure for table `supla_dev_channel_group`
--

DROP TABLE IF EXISTS `supla_dev_channel_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_dev_channel_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `caption` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `supla_dev_channel_group` VALUES (1,1,'Światła na parterze',140,0,4,0,NULL),(2,1,'Ipsa eos qui vitae enim.',20,0,3,0,NULL),(3,1,'Ab error fugit deleniti.',110,0,5,0,NULL),(4,1,'Magnam similique sint.',110,0,3,0,NULL),(5,1,'Vitae quos cum cumque.',20,0,5,0,NULL),(6,1,'Quas rerum nihil.',110,0,5,0,NULL),(7,1,'Ut eum commodi.',110,0,3,0,NULL),(8,1,'Et asperiores ut adipisci.',90,0,3,0,NULL),(9,1,'Officia ut a aut.',110,0,4,0,NULL),(10,1,'Ut possimus eligendi.',20,0,5,0,NULL),(11,1,'Odio dolores doloremque quae.',140,0,5,0,NULL);
/*!40000 ALTER TABLE `supla_dev_channel_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_dev_channel_value`
--

DROP TABLE IF EXISTS `supla_dev_channel_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_dev_channel_value` (
  `channel_id` int(11) NOT NULL,
  `update_time` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `valid_to` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `value` varbinary(8) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`channel_id`),
  KEY `IDX_1B99E014A76ED395` (`user_id`),
  CONSTRAINT `FK_1B99E01472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1B99E014A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_dev_channel_value`
--

LOCK TABLES `supla_dev_channel_value` WRITE;
/*!40000 ALTER TABLE `supla_dev_channel_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_dev_channel_value` ENABLE KEYS */;
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
  `caption` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allowed_actions` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active_from` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `active_to` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `executions_limit` int(11) DEFAULT NULL,
  `last_used` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `last_ipv4` int(10) unsigned DEFAULT NULL COMMENT '(DC2Type:ipaddress)',
  `enabled` tinyint(1) NOT NULL,
  `disable_http_get` tinyint(1) NOT NULL DEFAULT '0',
  `scene_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6AE7809FA76ED395` (`user_id`),
  KEY `IDX_6AE7809F72F5A1AA` (`channel_id`),
  KEY `IDX_6AE7809F89E4AAEE` (`channel_group_id`),
  KEY `IDX_6AE7809F166053B4` (`scene_id`),
  CONSTRAINT `FK_6AE7809F166053B4` FOREIGN KEY (`scene_id`) REFERENCES `supla_scene` (`id`) ON DELETE CASCADE,
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
INSERT INTO `supla_direct_link` VALUES (1,1,180,NULL,'HSvwQnsq4oc_Rh','Tan','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(2,1,229,NULL,'ZD3NgJPeBZiJ','Green','[40]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(3,1,301,NULL,'xY5Bw8qgvzNVdM6','Gold','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(4,1,304,NULL,'xJDRUXEVht7Bm2of','CadetBlue','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(5,1,186,NULL,'eXVKCEgNZUXd95Wd','DarkSlateGray','[10100]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(6,1,176,NULL,'WNVAQpiiLkZqcHvW','OliveDrab','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(7,1,315,NULL,'umXqzNrxKBct','ForestGreen','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(8,1,268,NULL,'xUHEWBJWBo2S9','DarkTurquoise','[10]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(9,1,212,NULL,'BohRGsAd7YP7c','LightGoldenRodYellow','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(10,1,289,NULL,'AMUDBuitepiyB','Olive','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(11,1,312,NULL,'oMowXXzqwnoLXnF','DarkSalmon','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(12,1,307,NULL,'qo8jcrBLFzU','Aquamarine','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(13,1,291,NULL,'LiRQzMN6NocB','OrangeRed','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(14,1,204,NULL,'B4YECxHNPGKKYq','Purple','[100]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(15,1,266,NULL,'BfmtAbXdW_P','SlateBlue','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(16,1,218,NULL,'MdWnNEmpAhqcTn','FireBrick','[10100]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(17,1,275,NULL,'MQToxgTXHjzk','WhiteSmoke','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(18,1,300,NULL,'U7ngRvc6HmMuui','DarkGreen','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(19,1,223,NULL,'udZf5WExYt','GreenYellow','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(20,1,272,NULL,'75A5QFoDC2NRJ','LightSkyBlue','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL),(21,2,316,NULL,'ALz2Ft7Cd5iurcJ','SUPLAER Direct Link','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL);
/*!40000 ALTER TABLE `supla_direct_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_em_log`
--

DROP TABLE IF EXISTS `supla_em_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_em_log` (
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:stringdatetime)',
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
  `fae_balanced` bigint(20) DEFAULT NULL,
  `rae_balanced` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`channel_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_em_log`
--

LOCK TABLES `supla_em_log` WRITE;
/*!40000 ALTER TABLE `supla_em_log` DISABLE KEYS */;
INSERT INTO `supla_em_log` VALUES (73,'2022-09-30 12:21:50',8,2,9,5,1,4,5,8,6,9,10,10,12,12),(73,'2022-09-30 12:31:50',8,12,14,14,10,4,8,12,9,10,20,17,22,22),(73,'2022-09-30 12:41:50',11,15,23,17,12,11,15,21,17,20,29,18,27,32),(73,'2022-09-30 12:51:50',16,23,29,24,22,19,24,28,20,27,39,24,28,39),(73,'2022-09-30 13:01:50',20,33,34,34,31,22,29,29,24,29,42,33,33,43),(73,'2022-09-30 13:11:50',27,38,43,37,39,31,37,38,30,33,46,42,36,48),(73,'2022-09-30 13:21:50',34,42,51,42,45,32,42,43,38,39,51,47,37,50),(73,'2022-09-30 13:31:50',42,48,56,46,49,39,49,46,48,39,57,56,37,52),(73,'2022-09-30 13:41:50',43,54,62,51,59,43,57,52,54,42,60,59,44,59),(73,'2022-09-30 13:51:50',46,58,66,51,62,48,62,59,58,44,62,61,50,65),(73,'2022-09-30 14:01:50',56,65,72,57,64,51,69,64,61,50,66,66,55,67),(73,'2022-09-30 14:11:50',65,73,79,61,73,54,73,70,68,57,75,76,65,69),(73,'2022-09-30 14:21:50',70,83,89,70,77,62,74,72,74,59,85,83,68,76),(73,'2022-09-30 14:31:50',74,86,98,76,86,72,79,79,76,66,93,90,75,85),(73,'2022-09-30 14:41:50',75,96,106,82,92,79,81,82,77,75,102,92,80,95),(73,'2022-09-30 14:51:50',85,98,108,83,93,82,91,83,78,82,102,101,82,105),(73,'2022-09-30 15:01:50',91,105,116,89,101,83,99,93,83,86,111,105,83,106),(73,'2022-09-30 15:11:50',100,112,124,95,101,86,104,101,87,95,117,113,90,110),(73,'2022-09-30 15:21:50',110,117,129,105,110,90,112,111,88,100,123,119,100,117),(73,'2022-09-30 15:31:50',120,117,135,113,113,92,118,121,92,110,132,129,106,125),(73,'2022-09-30 15:41:50',122,126,142,121,118,100,127,125,101,118,139,136,112,127),(73,'2022-09-30 15:51:50',125,130,147,130,127,105,129,129,106,122,147,141,113,133),(73,'2022-09-30 16:01:50',127,140,150,139,133,114,137,131,113,131,152,143,123,141),(73,'2022-09-30 16:11:50',130,144,155,148,139,120,139,139,123,141,157,149,133,145),(73,'2022-09-30 16:21:50',137,153,160,158,147,130,147,144,129,147,158,152,135,147),(73,'2022-09-30 16:31:50',139,156,167,166,150,136,151,145,139,154,168,157,144,151),(73,'2022-09-30 16:41:50',143,162,177,174,160,136,160,155,148,162,173,163,151,158),(73,'2022-09-30 16:51:50',148,169,180,183,165,141,168,164,155,172,177,168,159,163),(73,'2022-09-30 17:01:50',152,175,186,188,175,151,178,172,165,181,179,170,166,173),(73,'2022-09-30 17:11:50',156,179,192,195,177,161,182,173,174,190,183,180,175,182),(73,'2022-09-30 17:21:50',165,188,199,202,187,168,191,174,184,197,187,190,185,185),(73,'2022-09-30 17:31:50',175,195,207,208,196,178,198,176,193,203,188,199,192,192),(73,'2022-09-30 17:51:50',183,204,223,220,203,190,203,182,202,213,195,211,205,197),(73,'2022-09-30 18:01:50',186,212,231,223,211,200,210,186,212,221,202,217,209,205),(73,'2022-09-30 18:11:50',193,222,238,232,215,208,220,190,212,227,208,226,219,212),(73,'2022-09-30 18:21:50',203,225,244,234,224,215,222,197,221,237,217,229,228,216),(73,'2022-09-30 18:31:50',210,233,252,241,225,218,228,202,230,241,222,238,230,222),(73,'2022-09-30 18:41:50',217,235,259,249,234,219,233,212,236,247,225,245,232,227),(73,'2022-09-30 18:51:50',222,245,268,251,235,227,241,217,246,257,234,247,241,232),(73,'2022-09-30 19:01:50',230,249,276,261,244,232,246,222,255,261,243,255,251,242),(73,'2022-09-30 19:11:50',237,258,284,268,252,240,255,229,262,261,247,263,260,249),(73,'2022-09-30 19:21:50',239,260,291,277,256,243,256,238,267,264,253,267,269,251),(73,'2022-09-30 19:31:50',249,269,291,285,259,251,266,244,275,268,260,274,276,254),(73,'2022-09-30 19:41:50',252,269,301,293,268,257,268,247,278,278,268,282,286,263),(73,'2022-09-30 19:51:50',256,274,307,301,275,262,272,252,283,280,272,288,291,267),(73,'2022-09-30 20:01:50',263,283,314,302,285,264,277,254,293,288,282,291,297,275),(73,'2022-09-30 20:11:50',268,291,318,310,290,273,282,255,298,297,286,298,304,285),(73,'2022-09-30 20:21:50',273,293,323,314,294,278,292,263,305,300,295,303,310,285),(73,'2022-09-30 20:31:50',280,300,328,321,299,285,301,273,314,310,305,308,320,286),(73,'2022-09-30 20:41:50',282,304,335,325,306,293,308,277,324,314,311,318,325,292),(73,'2022-09-30 20:51:50',290,314,338,334,315,298,315,287,328,324,321,323,331,295),(73,'2022-09-30 21:01:50',292,324,348,340,323,305,322,297,336,334,323,329,340,304),(73,'2022-09-30 21:11:50',298,330,350,341,326,310,331,304,339,343,332,337,348,309),(73,'2022-09-30 21:21:50',303,338,360,341,336,313,338,313,345,350,339,345,357,314),(73,'2022-09-30 21:31:50',311,346,369,348,345,322,341,323,353,358,345,354,362,324),(73,'2022-09-30 21:41:50',321,352,379,357,355,329,351,323,363,368,352,361,369,329),(73,'2022-09-30 21:51:50',331,353,386,365,365,338,361,331,369,373,362,371,374,339),(73,'2022-09-30 22:01:50',335,363,394,375,371,346,368,337,373,381,372,379,382,343),(73,'2022-09-30 22:11:50',343,370,403,384,379,356,368,344,380,391,379,383,384,347),(73,'2022-09-30 22:21:50',348,374,406,388,387,359,370,350,382,391,382,387,392,355),(73,'2022-09-30 22:31:50',355,377,416,398,394,365,375,356,383,401,386,395,397,363),(73,'2022-09-30 22:41:50',357,387,424,406,400,373,379,364,388,408,389,399,407,368),(73,'2022-09-30 22:51:50',366,396,433,412,410,374,384,373,396,413,399,406,414,375),(73,'2022-09-30 23:01:50',374,401,441,419,414,381,390,378,402,423,406,416,415,384),(73,'2022-09-30 23:11:50',383,410,448,423,419,388,396,383,411,430,414,419,416,387),(73,'2022-09-30 23:21:50',391,410,456,426,429,398,404,388,419,437,423,428,421,393),(73,'2022-09-30 23:31:50',401,416,462,434,435,405,409,393,428,447,424,431,429,400),(73,'2022-09-30 23:41:50',407,425,471,437,445,408,418,394,435,450,431,440,438,410),(73,'2022-09-30 23:51:50',417,430,476,446,455,418,426,397,435,451,440,442,440,417),(73,'2022-10-01 00:01:50',419,431,482,456,465,426,427,407,443,459,446,447,445,420),(73,'2022-10-01 00:11:50',426,440,484,464,473,427,435,409,449,469,456,456,448,422),(73,'2022-10-01 00:21:50',436,443,491,468,473,436,440,417,458,472,460,463,451,429),(73,'2022-10-01 00:31:50',444,445,499,478,477,442,444,421,464,480,468,466,459,431),(73,'2022-10-01 00:41:50',449,454,501,483,480,443,453,424,473,485,477,471,465,439),(73,'2022-10-01 00:51:50',456,457,505,493,483,451,463,429,483,489,483,480,469,439),(73,'2022-10-01 01:01:50',458,466,509,500,485,461,465,436,484,490,491,490,473,448),(73,'2022-10-01 01:11:50',467,475,515,509,495,464,466,443,494,495,495,500,481,453),(73,'2022-10-01 01:21:50',467,480,522,519,502,473,472,450,501,503,503,505,483,462),(73,'2022-10-01 01:31:50',475,490,529,522,508,482,479,454,505,511,509,511,492,470),(73,'2022-10-01 01:41:50',479,499,539,528,512,490,485,463,511,521,511,517,495,478),(73,'2022-10-01 01:51:50',481,507,545,529,519,490,486,471,519,521,515,518,500,485),(73,'2022-10-01 02:01:50',490,512,550,538,529,494,495,479,529,523,523,528,507,495),(73,'2022-10-01 02:11:50',499,515,558,544,532,496,501,487,538,530,530,537,514,495),(73,'2022-10-01 02:21:50',503,519,562,544,539,498,511,490,548,537,538,539,520,497),(73,'2022-10-01 02:31:50',503,522,569,549,539,502,516,495,557,544,541,546,525,506),(73,'2022-10-01 02:41:50',513,526,572,557,541,506,524,501,558,554,551,552,526,514),(73,'2022-10-01 02:51:50',522,530,580,562,550,512,526,510,568,559,561,552,535,523),(73,'2022-10-01 03:01:50',531,537,583,572,553,513,529,515,576,563,568,554,541,531),(73,'2022-10-01 03:11:50',538,541,592,576,558,513,531,525,580,571,575,559,545,536),(73,'2022-10-01 03:21:50',548,546,602,582,565,521,537,532,584,580,582,569,553,546),(73,'2022-10-01 03:31:50',552,554,605,588,573,527,537,537,594,589,590,574,563,553),(73,'2022-10-01 03:41:50',559,556,614,597,583,531,545,546,602,596,600,581,569,562),(73,'2022-10-01 03:51:50',569,562,618,599,593,536,550,553,611,600,609,590,579,567),(73,'2022-10-01 04:01:50',579,572,622,602,596,545,558,561,613,601,615,599,585,571),(73,'2022-10-01 04:11:50',586,576,631,609,603,549,563,570,621,608,625,608,593,580),(73,'2022-10-01 04:21:50',592,583,637,615,612,559,569,580,629,617,625,611,603,589),(73,'2022-10-01 04:31:50',597,585,646,624,621,565,571,588,636,627,630,617,609,594),(73,'2022-10-01 04:41:50',605,590,655,633,628,575,572,594,640,637,637,626,618,604),(73,'2022-10-01 04:51:50',614,600,661,633,633,585,582,601,648,641,646,634,625,609),(73,'2022-10-01 05:01:50',622,610,667,643,638,591,591,604,655,650,651,639,635,617),(73,'2022-10-01 05:11:50',624,617,668,647,640,599,598,612,660,652,657,648,645,618),(73,'2022-10-01 05:21:50',631,623,674,657,644,608,607,618,665,658,663,654,654,619),(73,'2022-10-01 05:31:50',639,631,684,666,654,612,611,625,675,661,668,662,662,624),(73,'2022-10-01 05:41:50',643,641,691,673,662,620,616,627,681,670,671,667,671,631),(73,'2022-10-01 05:51:50',652,645,699,682,666,630,626,635,691,674,675,674,676,639),(73,'2022-10-01 06:01:50',658,655,701,690,668,633,630,642,700,681,681,679,686,647),(73,'2022-10-01 06:11:50',666,662,709,691,678,638,638,642,708,683,684,683,688,653),(73,'2022-10-01 06:21:50',673,669,713,696,688,639,646,649,717,693,694,693,698,658),(73,'2022-10-01 06:31:50',677,674,722,705,693,649,649,659,724,700,697,701,702,668),(73,'2022-10-01 06:41:50',684,684,725,715,703,656,654,665,734,708,703,711,702,676),(73,'2022-10-01 06:51:50',690,691,735,721,713,660,660,674,739,717,711,711,703,685),(73,'2022-10-01 07:11:50',699,709,749,733,720,670,668,676,747,731,720,722,713,692),(73,'2022-10-01 07:21:50',699,715,754,743,729,670,674,684,757,739,722,730,716,694),(73,'2022-10-01 07:31:50',709,724,763,753,739,672,675,692,762,745,727,739,723,701),(73,'2022-10-01 07:41:50',718,724,766,760,748,678,681,698,770,754,731,744,730,711),(73,'2022-10-01 07:51:50',727,734,776,766,756,684,685,700,777,759,740,752,737,721),(73,'2022-10-01 08:01:50',731,737,780,774,761,687,688,702,784,765,745,762,738,731),(73,'2022-10-01 08:11:50',732,747,789,783,768,694,697,705,792,766,754,769,742,738),(73,'2022-10-01 08:21:50',736,756,790,788,768,701,705,707,796,770,754,776,745,746),(73,'2022-10-01 08:31:50',739,760,800,791,771,707,710,712,802,777,758,781,751,751),(73,'2022-10-01 08:41:50',740,766,810,801,774,711,718,715,810,784,767,783,760,752),(73,'2022-10-01 08:51:50',743,768,815,808,782,719,725,720,819,790,775,789,763,761),(73,'2022-10-01 09:01:50',753,778,818,812,789,729,728,728,821,797,783,793,772,771),(73,'2022-10-01 09:11:50',763,782,819,817,797,735,737,732,828,799,784,803,778,772),(73,'2022-10-01 09:21:50',764,783,829,820,801,736,741,738,830,807,787,806,778,779),(73,'2022-10-01 09:41:50',775,799,835,830,809,744,753,741,845,822,804,815,790,791),(73,'2022-10-01 09:51:50',780,806,838,836,815,747,763,749,851,829,811,818,798,792),(73,'2022-10-01 10:01:50',782,815,846,844,822,756,771,759,855,832,820,825,804,797),(73,'2022-10-01 10:11:50',784,821,855,846,830,765,778,763,855,842,828,830,806,802),(73,'2022-10-01 10:21:50',787,826,862,852,830,766,779,766,858,846,829,833,810,812),(73,'2022-10-01 10:31:50',796,834,870,857,839,771,785,774,859,852,839,841,814,815),(73,'2022-10-01 10:41:50',797,834,876,867,839,771,788,779,862,860,847,851,815,822),(73,'2022-10-01 10:51:50',807,843,886,869,845,776,792,781,871,865,849,861,820,823),(73,'2022-10-01 11:01:50',817,851,895,879,854,786,801,788,881,872,849,870,826,824),(73,'2022-10-01 11:21:50',820,865,911,889,869,797,818,798,895,887,856,884,835,835),(73,'2022-10-01 11:31:50',830,874,919,897,870,804,827,803,896,892,859,889,845,845),(73,'2022-10-01 11:41:50',837,884,924,907,877,807,836,813,906,901,869,897,850,855),(73,'2022-10-01 11:51:50',839,890,934,912,883,811,843,813,913,904,879,900,851,865),(73,'2022-10-01 12:01:50',840,900,943,921,888,821,848,822,923,907,882,908,856,873),(73,'2022-10-01 12:11:50',845,910,953,925,898,827,852,828,924,917,891,911,859,878);
/*!40000 ALTER TABLE `supla_em_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_email_notifications`
--

DROP TABLE IF EXISTS `supla_email_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_email_notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7C77A74CFB7336F0` (`queue_name`),
  KEY `IDX_7C77A74CE3BD61CE` (`available_at`),
  KEY `IDX_7C77A74C16BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_email_notifications`
--

LOCK TABLES `supla_email_notifications` WRITE;
/*!40000 ALTER TABLE `supla_email_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_email_notifications` ENABLE KEYS */;
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
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:stringdatetime)',
  `counter` bigint(20) NOT NULL,
  `calculated_value` bigint(20) NOT NULL,
  PRIMARY KEY (`channel_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_ic_log`
--

LOCK TABLES `supla_ic_log` WRITE;
/*!40000 ALTER TABLE `supla_ic_log` DISABLE KEYS */;
INSERT INTO `supla_ic_log` VALUES (74,'2022-09-30 12:21:50',2,20),(74,'2022-09-30 12:31:50',10,100),(74,'2022-09-30 12:41:50',15,150),(74,'2022-09-30 12:51:50',17,170),(74,'2022-09-30 13:01:50',25,250),(74,'2022-09-30 13:11:50',35,350),(74,'2022-09-30 13:21:50',43,430),(74,'2022-09-30 13:31:50',52,520),(74,'2022-09-30 13:41:50',59,590),(74,'2022-09-30 13:51:50',65,650),(74,'2022-09-30 14:01:50',66,660),(74,'2022-09-30 14:11:50',72,720),(74,'2022-09-30 14:21:50',81,810),(74,'2022-09-30 14:31:50',87,870),(74,'2022-09-30 14:41:50',95,950),(74,'2022-09-30 14:51:50',97,970),(74,'2022-09-30 15:01:50',100,1000),(74,'2022-09-30 15:11:50',106,1060),(74,'2022-09-30 15:21:50',115,1150),(74,'2022-09-30 15:31:50',121,1210),(74,'2022-09-30 15:41:50',126,1260),(74,'2022-09-30 15:51:50',130,1300),(74,'2022-09-30 16:01:50',139,1390),(74,'2022-09-30 16:11:50',145,1450),(74,'2022-09-30 16:21:50',153,1530),(74,'2022-09-30 16:31:50',154,1540),(74,'2022-09-30 16:41:50',164,1640),(74,'2022-09-30 16:51:50',168,1680),(74,'2022-09-30 17:01:50',177,1770),(74,'2022-09-30 17:11:50',185,1850),(74,'2022-09-30 17:21:50',191,1910),(74,'2022-09-30 17:31:50',193,1930),(74,'2022-09-30 17:41:50',194,1940),(74,'2022-09-30 17:51:50',198,1980),(74,'2022-09-30 18:11:50',211,2110),(74,'2022-09-30 18:21:50',212,2120),(74,'2022-09-30 18:31:50',219,2190),(74,'2022-09-30 18:41:50',225,2250),(74,'2022-09-30 18:51:50',227,2270),(74,'2022-09-30 19:01:50',230,2300),(74,'2022-09-30 19:11:50',239,2390),(74,'2022-09-30 19:31:50',252,2520),(74,'2022-09-30 19:41:50',257,2570),(74,'2022-09-30 19:51:50',262,2620),(74,'2022-09-30 20:01:50',266,2660),(74,'2022-09-30 20:11:50',272,2720),(74,'2022-09-30 20:21:50',282,2820),(74,'2022-09-30 20:31:50',284,2840),(74,'2022-09-30 20:41:50',286,2860),(74,'2022-09-30 20:51:50',291,2910),(74,'2022-09-30 21:01:50',298,2980),(74,'2022-09-30 21:11:50',8,80),(74,'2022-09-30 21:21:50',9,90),(74,'2022-09-30 21:31:50',19,190),(74,'2022-09-30 21:41:50',23,230),(74,'2022-09-30 21:51:50',31,310),(74,'2022-09-30 22:01:50',37,370),(74,'2022-09-30 22:11:50',1,10),(74,'2022-09-30 22:21:50',5,50),(74,'2022-09-30 22:31:50',11,110),(74,'2022-09-30 22:41:50',19,190),(74,'2022-09-30 22:51:50',27,270),(74,'2022-09-30 23:01:50',35,350),(74,'2022-09-30 23:11:50',36,360),(74,'2022-09-30 23:21:50',44,440),(74,'2022-09-30 23:31:50',51,510),(74,'2022-09-30 23:41:50',60,600),(74,'2022-09-30 23:51:50',68,680),(74,'2022-10-01 00:11:50',78,780),(74,'2022-10-01 00:21:50',86,860),(74,'2022-10-01 00:31:50',96,960),(74,'2022-10-01 00:41:50',104,1040),(74,'2022-10-01 00:51:50',108,1080),(74,'2022-10-01 01:01:50',116,1160),(74,'2022-10-01 01:11:50',125,1250),(74,'2022-10-01 01:21:50',130,1300),(74,'2022-10-01 01:31:50',140,1400),(74,'2022-10-01 01:41:50',145,1450),(74,'2022-10-01 01:51:50',145,1450),(74,'2022-10-01 02:01:50',150,1500),(74,'2022-10-01 02:11:50',156,1560),(74,'2022-10-01 02:21:50',157,1570),(74,'2022-10-01 02:31:50',165,1650),(74,'2022-10-01 02:41:50',168,1680),(74,'2022-10-01 02:51:50',178,1780),(74,'2022-10-01 03:01:50',182,1820),(74,'2022-10-01 03:11:50',192,1920),(74,'2022-10-01 03:21:50',195,1950),(74,'2022-10-01 03:31:50',201,2010),(74,'2022-10-01 03:41:50',209,2090),(74,'2022-10-01 03:51:50',212,2120),(74,'2022-10-01 04:01:50',216,2160),(74,'2022-10-01 04:11:50',223,2230),(74,'2022-10-01 04:31:50',234,2340),(74,'2022-10-01 04:41:50',243,2430),(74,'2022-10-01 04:51:50',246,2460),(74,'2022-10-01 05:01:50',252,2520),(74,'2022-10-01 05:11:50',252,2520),(74,'2022-10-01 05:21:50',261,2610),(74,'2022-10-01 05:31:50',264,2640),(74,'2022-10-01 05:41:50',270,2700),(74,'2022-10-01 05:51:50',270,2700),(74,'2022-10-01 06:01:50',274,2740),(74,'2022-10-01 06:11:50',283,2830),(74,'2022-10-01 06:21:50',289,2890),(74,'2022-10-01 06:31:50',299,2990),(74,'2022-10-01 06:41:50',305,3050),(74,'2022-10-01 06:51:50',306,3060),(74,'2022-10-01 07:01:50',311,3110),(74,'2022-10-01 07:11:50',318,3180),(74,'2022-10-01 07:31:50',329,3290),(74,'2022-10-01 07:41:50',337,3370),(74,'2022-10-01 07:51:50',344,3440),(74,'2022-10-01 08:01:50',352,3520),(74,'2022-10-01 08:11:50',356,3560),(74,'2022-10-01 08:21:50',366,3660),(74,'2022-10-01 08:31:50',371,3710),(74,'2022-10-01 08:41:50',379,3790),(74,'2022-10-01 08:51:50',382,3820),(74,'2022-10-01 09:01:50',386,3860),(74,'2022-10-01 09:21:50',400,4000),(74,'2022-10-01 09:31:50',410,4100),(74,'2022-10-01 09:41:50',412,4120),(74,'2022-10-01 09:51:50',421,4210),(74,'2022-10-01 10:01:50',430,4300),(74,'2022-10-01 10:11:50',438,4380),(74,'2022-10-01 10:21:50',443,4430),(74,'2022-10-01 10:31:50',446,4460),(74,'2022-10-01 10:41:50',455,4550),(74,'2022-10-01 10:51:50',462,4620),(74,'2022-10-01 11:01:50',462,4620),(74,'2022-10-01 11:11:50',472,4720),(74,'2022-10-01 11:21:50',476,4760),(74,'2022-10-01 11:31:50',486,4860),(74,'2022-10-01 11:41:50',495,4950),(74,'2022-10-01 11:51:50',505,5050),(74,'2022-10-01 12:01:50',512,5120),(74,'2022-10-01 12:11:50',521,5210),(75,'2022-09-30 12:21:50',3,30),(75,'2022-09-30 12:31:50',12,120),(75,'2022-09-30 12:41:50',18,180),(75,'2022-09-30 12:51:50',26,260),(75,'2022-09-30 13:01:50',30,300),(75,'2022-09-30 13:11:50',34,340),(75,'2022-09-30 13:21:50',40,400),(75,'2022-09-30 13:31:50',49,490),(75,'2022-09-30 13:41:50',55,550),(75,'2022-09-30 13:51:50',57,570),(75,'2022-09-30 14:01:50',64,640),(75,'2022-09-30 14:11:50',69,690),(75,'2022-09-30 14:21:50',73,730),(75,'2022-09-30 14:31:50',77,770),(75,'2022-09-30 14:41:50',86,860),(75,'2022-09-30 14:51:50',92,920),(75,'2022-09-30 15:01:50',96,960),(75,'2022-09-30 15:11:50',97,970),(75,'2022-09-30 15:21:50',106,1060),(75,'2022-09-30 15:31:50',113,1130),(75,'2022-09-30 15:41:50',120,1200),(75,'2022-09-30 15:51:50',120,1200),(75,'2022-09-30 16:01:50',121,1210),(75,'2022-09-30 16:11:50',121,1210),(75,'2022-09-30 16:21:50',128,1280),(75,'2022-09-30 16:31:50',138,1380),(75,'2022-09-30 16:41:50',144,1440),(75,'2022-09-30 16:51:50',148,1480),(75,'2022-09-30 17:01:50',158,1580),(75,'2022-09-30 17:11:50',165,1650),(75,'2022-09-30 17:21:50',175,1750),(75,'2022-09-30 17:31:50',177,1770),(75,'2022-09-30 17:41:50',184,1840),(75,'2022-09-30 17:51:50',185,1850),(75,'2022-09-30 18:01:50',190,1900),(75,'2022-09-30 18:11:50',197,1970),(75,'2022-09-30 18:21:50',204,2040),(75,'2022-09-30 18:31:50',206,2060),(75,'2022-09-30 18:41:50',212,2120),(75,'2022-09-30 19:01:50',220,2200),(75,'2022-09-30 19:11:50',227,2270),(75,'2022-09-30 19:21:50',235,2350),(75,'2022-09-30 19:31:50',241,2410),(75,'2022-09-30 19:41:50',251,2510),(75,'2022-09-30 19:51:50',257,2570),(75,'2022-09-30 20:01:50',258,2580),(75,'2022-09-30 20:21:50',271,2710),(75,'2022-09-30 20:31:50',280,2800),(75,'2022-09-30 20:41:50',284,2840),(75,'2022-09-30 20:51:50',291,2910),(75,'2022-09-30 21:01:50',298,2980),(75,'2022-09-30 21:11:50',308,3080),(75,'2022-09-30 21:21:50',309,3090),(75,'2022-09-30 21:41:50',324,3240),(75,'2022-09-30 21:51:50',331,3310),(75,'2022-09-30 22:01:50',339,3390),(75,'2022-09-30 22:11:50',349,3490),(75,'2022-09-30 22:21:50',359,3590),(75,'2022-09-30 22:31:50',368,3680),(75,'2022-09-30 22:41:50',377,3770),(75,'2022-09-30 22:51:50',0,0),(75,'2022-09-30 23:11:50',12,120),(75,'2022-09-30 23:21:50',21,210),(75,'2022-09-30 23:31:50',25,250),(75,'2022-09-30 23:41:50',34,340),(75,'2022-10-01 00:01:50',50,500),(75,'2022-10-01 00:11:50',60,600),(75,'2022-10-01 00:21:50',65,650),(75,'2022-10-01 00:41:50',82,820),(75,'2022-10-01 00:51:50',89,890),(75,'2022-10-01 01:01:50',95,950),(75,'2022-10-01 01:11:50',105,1050),(75,'2022-10-01 01:21:50',107,1070),(75,'2022-10-01 01:31:50',111,1110),(75,'2022-10-01 01:41:50',121,1210),(75,'2022-10-01 01:51:50',125,1250),(75,'2022-10-01 02:01:50',129,1290),(75,'2022-10-01 02:21:50',147,1470),(75,'2022-10-01 02:31:50',157,1570),(75,'2022-10-01 02:41:50',166,1660),(75,'2022-10-01 02:51:50',171,1710),(75,'2022-10-01 03:01:50',174,1740),(75,'2022-10-01 03:11:50',183,1830),(75,'2022-10-01 03:21:50',189,1890),(75,'2022-10-01 03:31:50',196,1960),(75,'2022-10-01 03:41:50',199,1990),(75,'2022-10-01 03:51:50',208,2080),(75,'2022-10-01 04:01:50',217,2170),(75,'2022-10-01 04:11:50',227,2270),(75,'2022-10-01 04:21:50',234,2340),(75,'2022-10-01 04:31:50',243,2430),(75,'2022-10-01 04:41:50',252,2520),(75,'2022-10-01 04:51:50',257,2570),(75,'2022-10-01 05:01:50',260,2600),(75,'2022-10-01 05:11:50',267,2670),(75,'2022-10-01 05:21:50',272,2720),(75,'2022-10-01 05:31:50',281,2810),(75,'2022-10-01 05:41:50',285,2850),(75,'2022-10-01 05:51:50',288,2880),(75,'2022-10-01 06:01:50',294,2940),(75,'2022-10-01 06:11:50',294,2940),(75,'2022-10-01 06:21:50',299,2990),(75,'2022-10-01 06:31:50',304,3040),(75,'2022-10-01 06:41:50',312,3120),(75,'2022-10-01 06:51:50',316,3160),(75,'2022-10-01 07:01:50',321,3210),(75,'2022-10-01 07:11:50',324,3240),(75,'2022-10-01 07:21:50',328,3280),(75,'2022-10-01 07:31:50',335,3350),(75,'2022-10-01 07:41:50',344,3440),(75,'2022-10-01 07:51:50',348,3480),(75,'2022-10-01 08:01:50',352,3520),(75,'2022-10-01 08:11:50',359,3590),(75,'2022-10-01 08:21:50',366,3660),(75,'2022-10-01 08:31:50',374,3740),(75,'2022-10-01 08:41:50',381,3810),(75,'2022-10-01 09:01:50',391,3910),(75,'2022-10-01 09:11:50',399,3990),(75,'2022-10-01 09:21:50',401,4010),(75,'2022-10-01 09:41:50',410,4100),(75,'2022-10-01 09:51:50',418,4180),(75,'2022-10-01 10:01:50',425,4250),(75,'2022-10-01 10:11:50',428,4280),(75,'2022-10-01 10:21:50',432,4320),(75,'2022-10-01 10:31:50',439,4390),(75,'2022-10-01 10:41:50',442,4420),(75,'2022-10-01 10:51:50',443,4430),(75,'2022-10-01 11:01:50',449,4490),(75,'2022-10-01 11:11:50',458,4580),(75,'2022-10-01 11:21:50',463,4630),(75,'2022-10-01 11:31:50',466,4660),(75,'2022-10-01 11:41:50',474,4740),(75,'2022-10-01 11:51:50',482,4820),(75,'2022-10-01 12:01:50',489,4890),(75,'2022-10-01 12:11:50',498,4980),(77,'2022-09-30 12:21:50',3,30),(77,'2022-09-30 12:31:50',12,120),(77,'2022-09-30 12:41:50',22,220),(77,'2022-09-30 12:51:50',30,300),(77,'2022-09-30 13:01:50',31,310),(77,'2022-09-30 13:11:50',40,400),(77,'2022-09-30 13:31:50',59,590),(77,'2022-09-30 13:41:50',67,670),(77,'2022-09-30 13:51:50',74,740),(77,'2022-09-30 14:01:50',82,820),(77,'2022-09-30 14:11:50',91,910),(77,'2022-09-30 14:21:50',98,980),(77,'2022-09-30 14:31:50',106,1060),(77,'2022-09-30 14:41:50',113,1130),(77,'2022-09-30 14:51:50',122,1220),(77,'2022-09-30 15:01:50',127,1270),(77,'2022-09-30 15:11:50',135,1350),(77,'2022-09-30 15:21:50',140,1400),(77,'2022-09-30 15:31:50',146,1460),(77,'2022-09-30 15:41:50',151,1510),(77,'2022-09-30 15:51:50',154,1540),(77,'2022-09-30 16:01:50',164,1640),(77,'2022-09-30 16:11:50',174,1740),(77,'2022-09-30 16:21:50',174,1740),(77,'2022-09-30 16:31:50',178,1780),(77,'2022-09-30 16:41:50',179,1790),(77,'2022-09-30 16:51:50',183,1830),(77,'2022-09-30 17:01:50',187,1870),(77,'2022-09-30 17:11:50',196,1960),(77,'2022-09-30 17:21:50',204,2040),(77,'2022-09-30 17:31:50',210,2100),(77,'2022-09-30 17:41:50',217,2170),(77,'2022-09-30 17:51:50',218,2180),(77,'2022-09-30 18:11:50',230,2300),(77,'2022-09-30 18:31:50',249,2490),(77,'2022-09-30 18:41:50',253,2530),(77,'2022-09-30 18:51:50',258,2580),(77,'2022-09-30 19:21:50',279,2790),(77,'2022-09-30 19:31:50',288,2880),(77,'2022-09-30 19:41:50',296,2960),(77,'2022-09-30 19:51:50',306,3060),(77,'2022-09-30 20:01:50',312,3120),(77,'2022-09-30 20:11:50',320,3200),(77,'2022-09-30 20:21:50',325,3250),(77,'2022-09-30 20:41:50',342,3420),(77,'2022-09-30 20:51:50',2,20),(77,'2022-09-30 21:01:50',12,120),(77,'2022-09-30 21:11:50',16,160),(77,'2022-09-30 21:21:50',22,220),(77,'2022-09-30 21:31:50',32,320),(77,'2022-09-30 21:41:50',35,350),(77,'2022-09-30 22:01:50',47,470),(77,'2022-09-30 22:11:50',57,570),(77,'2022-09-30 22:21:50',61,610),(77,'2022-09-30 22:31:50',68,680),(77,'2022-09-30 22:41:50',76,760),(77,'2022-09-30 22:51:50',82,820),(77,'2022-09-30 23:01:50',89,890),(77,'2022-09-30 23:11:50',99,990),(77,'2022-09-30 23:21:50',108,1080),(77,'2022-09-30 23:31:50',113,1130),(77,'2022-09-30 23:41:50',122,1220),(77,'2022-10-01 00:01:50',139,1390),(77,'2022-10-01 00:11:50',143,1430),(77,'2022-10-01 00:21:50',150,1500),(77,'2022-10-01 00:31:50',151,1510),(77,'2022-10-01 00:41:50',152,1520),(77,'2022-10-01 00:51:50',158,1580),(77,'2022-10-01 01:01:50',167,1670),(77,'2022-10-01 01:11:50',177,1770),(77,'2022-10-01 01:21:50',187,1870),(77,'2022-10-01 01:31:50',197,1970),(77,'2022-10-01 01:41:50',207,2070),(77,'2022-10-01 02:01:50',225,2250),(77,'2022-10-01 02:11:50',230,2300),(77,'2022-10-01 02:31:50',244,2440),(77,'2022-10-01 02:41:50',248,2480),(77,'2022-10-01 02:51:50',253,2530),(77,'2022-10-01 03:01:50',260,2600),(77,'2022-10-01 03:11:50',267,2670),(77,'2022-10-01 03:21:50',276,2760),(77,'2022-10-01 03:31:50',285,2850),(77,'2022-10-01 03:41:50',295,2950),(77,'2022-10-01 03:51:50',297,2970),(77,'2022-10-01 04:01:50',304,3040),(77,'2022-10-01 04:11:50',312,3120),(77,'2022-10-01 04:21:50',313,3130),(77,'2022-10-01 04:31:50',323,3230),(77,'2022-10-01 04:41:50',326,3260),(77,'2022-10-01 04:51:50',327,3270),(77,'2022-10-01 05:11:50',339,3390),(77,'2022-10-01 05:21:50',345,3450),(77,'2022-10-01 05:31:50',354,3540),(77,'2022-10-01 05:41:50',364,3640),(77,'2022-10-01 05:51:50',373,3730),(77,'2022-10-01 06:01:50',375,3750),(77,'2022-10-01 06:11:50',383,3830),(77,'2022-10-01 06:21:50',385,3850),(77,'2022-10-01 06:31:50',390,3900),(77,'2022-10-01 06:41:50',395,3950),(77,'2022-10-01 07:01:50',405,4050),(77,'2022-10-01 07:11:50',411,4110),(77,'2022-10-01 07:21:50',417,4170),(77,'2022-10-01 07:31:50',427,4270),(77,'2022-10-01 07:41:50',437,4370),(77,'2022-10-01 07:51:50',446,4460),(77,'2022-10-01 08:01:50',453,4530),(77,'2022-10-01 08:11:50',455,4550),(77,'2022-10-01 08:21:50',465,4650),(77,'2022-10-01 08:31:50',475,4750),(77,'2022-10-01 08:41:50',476,4760),(77,'2022-10-01 08:51:50',481,4810),(77,'2022-10-01 09:01:50',486,4860),(77,'2022-10-01 09:11:50',486,4860),(77,'2022-10-01 09:21:50',494,4940),(77,'2022-10-01 09:31:50',503,5030),(77,'2022-10-01 09:41:50',510,5100),(77,'2022-10-01 09:51:50',518,5180),(77,'2022-10-01 10:01:50',528,5280),(77,'2022-10-01 10:11:50',535,5350),(77,'2022-10-01 10:21:50',536,5360),(77,'2022-10-01 10:31:50',542,5420),(77,'2022-10-01 10:41:50',544,5440),(77,'2022-10-01 10:51:50',549,5490),(77,'2022-10-01 11:01:50',553,5530),(77,'2022-10-01 11:11:50',561,5610),(77,'2022-10-01 11:21:50',561,5610),(77,'2022-10-01 11:31:50',571,5710),(77,'2022-10-01 11:41:50',574,5740),(77,'2022-10-01 11:51:50',576,5760),(77,'2022-10-01 12:01:50',582,5820),(77,'2022-10-01 12:11:50',592,5920),(315,'2022-09-30 12:21:50',6,60),(315,'2022-09-30 12:31:50',7,70),(315,'2022-09-30 12:41:50',11,110),(315,'2022-09-30 12:51:50',21,210),(315,'2022-09-30 13:01:50',23,230),(315,'2022-09-30 13:11:50',33,330),(315,'2022-09-30 13:21:50',40,400),(315,'2022-09-30 13:31:50',47,470),(315,'2022-09-30 13:41:50',51,510),(315,'2022-09-30 13:51:50',61,610),(315,'2022-09-30 14:01:50',63,630),(315,'2022-09-30 14:11:50',69,690),(315,'2022-09-30 14:21:50',75,750),(315,'2022-09-30 14:31:50',78,780),(315,'2022-09-30 14:41:50',81,810),(315,'2022-09-30 14:51:50',82,820),(315,'2022-09-30 15:01:50',87,870),(315,'2022-09-30 15:11:50',94,940),(315,'2022-09-30 15:21:50',104,1040),(315,'2022-09-30 15:31:50',106,1060),(315,'2022-09-30 15:41:50',110,1100),(315,'2022-09-30 15:51:50',113,1130),(315,'2022-09-30 16:01:50',123,1230),(315,'2022-09-30 16:11:50',127,1270),(315,'2022-09-30 16:21:50',134,1340),(315,'2022-09-30 16:31:50',144,1440),(315,'2022-09-30 16:41:50',151,1510),(315,'2022-09-30 16:51:50',153,1530),(315,'2022-09-30 17:01:50',158,1580),(315,'2022-09-30 17:11:50',160,1600),(315,'2022-09-30 17:21:50',166,1660),(315,'2022-09-30 17:31:50',173,1730),(315,'2022-09-30 17:41:50',180,1800),(315,'2022-09-30 17:51:50',183,1830),(315,'2022-09-30 18:01:50',185,1850),(315,'2022-09-30 18:11:50',194,1940),(315,'2022-09-30 18:21:50',203,2030),(315,'2022-09-30 18:31:50',210,2100),(315,'2022-09-30 18:41:50',218,2180),(315,'2022-09-30 18:51:50',221,2210),(315,'2022-09-30 19:01:50',221,2210),(315,'2022-09-30 19:11:50',230,2300),(315,'2022-09-30 19:21:50',235,2350),(315,'2022-09-30 19:31:50',236,2360),(315,'2022-09-30 19:41:50',246,2460),(315,'2022-09-30 19:51:50',251,2510),(315,'2022-09-30 20:01:50',259,2590),(315,'2022-09-30 20:11:50',260,2600),(315,'2022-09-30 20:21:50',260,2600),(315,'2022-09-30 20:31:50',268,2680),(315,'2022-09-30 20:41:50',272,2720),(315,'2022-09-30 20:51:50',282,2820),(315,'2022-09-30 21:01:50',284,2840),(315,'2022-09-30 21:11:50',286,2860),(315,'2022-09-30 21:21:50',291,2910),(315,'2022-09-30 21:31:50',296,2960),(315,'2022-09-30 21:41:50',305,3050),(315,'2022-09-30 21:51:50',314,3140),(315,'2022-09-30 22:01:50',323,3230),(315,'2022-09-30 22:11:50',325,3250),(315,'2022-09-30 22:21:50',326,3260),(315,'2022-09-30 22:31:50',336,3360),(315,'2022-09-30 22:41:50',344,3440),(315,'2022-09-30 22:51:50',344,3440),(315,'2022-09-30 23:01:50',350,3500),(315,'2022-09-30 23:11:50',359,3590),(315,'2022-09-30 23:31:50',364,3640),(315,'2022-09-30 23:41:50',371,3710),(315,'2022-09-30 23:51:50',379,3790),(315,'2022-10-01 00:01:50',389,3890),(315,'2022-10-01 00:11:50',396,3960),(315,'2022-10-01 00:21:50',405,4050),(315,'2022-10-01 00:31:50',408,4080),(315,'2022-10-01 00:41:50',416,4160),(315,'2022-10-01 00:51:50',423,4230),(315,'2022-10-01 01:01:50',430,4300),(315,'2022-10-01 01:11:50',439,4390),(315,'2022-10-01 01:21:50',443,4430),(315,'2022-10-01 01:31:50',452,4520),(315,'2022-10-01 01:41:50',459,4590),(315,'2022-10-01 01:51:50',469,4690),(315,'2022-10-01 02:01:50',471,4710),(315,'2022-10-01 02:11:50',476,4760),(315,'2022-10-01 02:31:50',490,4900),(315,'2022-10-01 02:41:50',496,4960),(315,'2022-10-01 02:51:50',503,5030),(315,'2022-10-01 03:01:50',510,5100),(315,'2022-10-01 03:11:50',513,5130),(315,'2022-10-01 03:21:50',519,5190),(315,'2022-10-01 03:31:50',528,5280),(315,'2022-10-01 03:41:50',534,5340),(315,'2022-10-01 03:51:50',543,5430),(315,'2022-10-01 04:01:50',552,5520),(315,'2022-10-01 04:21:50',561,5610),(315,'2022-10-01 04:31:50',568,5680),(315,'2022-10-01 04:41:50',569,5690),(315,'2022-10-01 04:51:50',572,5720),(315,'2022-10-01 05:01:50',575,5750),(315,'2022-10-01 05:11:50',581,5810),(315,'2022-10-01 05:21:50',584,5840),(315,'2022-10-01 05:31:50',591,5910),(315,'2022-10-01 05:41:50',601,6010),(315,'2022-10-01 06:01:50',611,6110),(315,'2022-10-01 06:11:50',617,6170),(315,'2022-10-01 06:21:50',623,6230),(315,'2022-10-01 06:31:50',629,6290),(315,'2022-10-01 06:41:50',638,6380),(315,'2022-10-01 06:51:50',642,6420),(315,'2022-10-01 07:01:50',651,6510),(315,'2022-10-01 07:11:50',657,6570),(315,'2022-10-01 07:21:50',660,6600),(315,'2022-10-01 07:31:50',665,6650),(315,'2022-10-01 07:41:50',675,6750),(315,'2022-10-01 07:51:50',680,6800),(315,'2022-10-01 08:01:50',689,6890),(315,'2022-10-01 08:11:50',694,6940),(315,'2022-10-01 08:21:50',696,6960),(315,'2022-10-01 08:31:50',698,6980),(315,'2022-10-01 08:41:50',701,7010),(315,'2022-10-01 08:51:50',705,7050),(315,'2022-10-01 09:01:50',706,7060),(315,'2022-10-01 09:11:50',711,7110),(315,'2022-10-01 09:21:50',713,7130),(315,'2022-10-01 09:31:50',717,7170),(315,'2022-10-01 09:41:50',720,7200),(315,'2022-10-01 09:51:50',725,7250),(315,'2022-10-01 10:01:50',735,7350),(315,'2022-10-01 10:21:50',748,7480),(315,'2022-10-01 10:31:50',751,7510),(315,'2022-10-01 10:41:50',756,7560),(315,'2022-10-01 10:51:50',765,7650),(315,'2022-10-01 11:01:50',775,7750),(315,'2022-10-01 11:11:50',779,7790),(315,'2022-10-01 11:21:50',785,7850),(315,'2022-10-01 11:31:50',795,7950),(315,'2022-10-01 11:41:50',805,8050),(315,'2022-10-01 11:51:50',808,8080),(315,'2022-10-01 12:01:50',817,8170),(315,'2022-10-01 12:11:50',827,8270);
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
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `comment` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_date` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `reg_ipv4` int(10) unsigned DEFAULT NULL COMMENT '(DC2Type:ipaddress)',
  `last_connected` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `last_ipv4` int(10) unsigned DEFAULT NULL COMMENT '(DC2Type:ipaddress)',
  `software_version` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `protocol_version` int(11) NOT NULL,
  `original_location_id` int(11) DEFAULT NULL,
  `auth_key` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `flags` int(11) DEFAULT NULL,
  `manufacturer_id` smallint(6) DEFAULT NULL,
  `product_id` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_USER_GUID` (`user_id`,`guid`),
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
INSERT INTO `supla_iodevice` VALUES (1,4,1,_binary '1224915','SONOFF-DS',1,NULL,'2022-10-01 12:21:46',1064819281,'2022-10-01 12:21:46',NULL,'2.25',2,NULL,NULL,16,NULL,NULL),(2,5,1,_binary '3266935','UNI-MODULE',1,NULL,'2022-10-01 12:21:46',2844642367,'2022-10-01 12:21:46',NULL,'2.23',2,NULL,NULL,16,NULL,NULL),(3,3,1,_binary '3828150','RGB-801',1,NULL,'2022-10-01 12:21:46',216867552,'2022-10-01 12:21:46',NULL,'2.41',2,NULL,NULL,16,NULL,NULL),(4,4,1,_binary '6278283','ALL-IN-ONE MEGA DEVICE',1,NULL,'2022-10-01 12:21:46',3913571423,'2022-10-01 12:21:46',NULL,'2.15',2,NULL,NULL,16,NULL,NULL),(5,4,1,_binary '3779301','SECOND MEGA DEVICE',1,NULL,'2022-10-01 12:21:46',3554879938,'2022-10-01 12:21:46',NULL,'2.39',2,NULL,NULL,16,NULL,NULL),(6,4,1,_binary '6536427','OH-MY-GATES. This device also has ridiculously long name!',1,NULL,'2022-10-01 12:21:46',613813813,'2022-10-01 12:21:46',NULL,'2.18',2,NULL,NULL,16,NULL,NULL),(7,5,1,_binary '2430728','CONSECTETUR-VEL-EIUS',1,NULL,'2022-10-01 12:21:46',2939256730,'2022-10-01 12:21:46',NULL,'2.41',2,NULL,NULL,16,NULL,NULL),(8,5,1,_binary '5928928','DOLORES-REPREHENDERIT',1,NULL,'2022-10-01 12:21:46',342138033,'2022-10-01 12:21:46',NULL,'2.15',2,NULL,NULL,16,NULL,NULL),(9,5,1,_binary '6475786','ELIGENDI-UT',1,NULL,'2022-10-01 12:21:47',3876830504,'2022-10-01 12:21:47',NULL,'2.33',2,NULL,NULL,16,NULL,NULL),(10,5,1,_binary '8066171','NAM-ASPERNATUR',1,NULL,'2022-10-01 12:21:47',3806475459,'2022-10-01 12:21:47',NULL,'2.28',2,NULL,NULL,16,NULL,NULL),(11,5,1,_binary '2695538','DIGNISSIMOS-AUTEM-TENETUR',1,NULL,'2022-10-01 12:21:47',2800783499,'2022-10-01 12:21:47',NULL,'2.36',2,NULL,NULL,16,NULL,NULL),(12,5,1,_binary '6255225','QUAS',1,NULL,'2022-10-01 12:21:47',2422559688,'2022-10-01 12:21:47',NULL,'2.41',2,NULL,NULL,16,NULL,NULL),(13,5,1,_binary '3505682','UT-REM',1,NULL,'2022-10-01 12:21:47',1868481383,'2022-10-01 12:21:47',NULL,'2.44',2,NULL,NULL,16,NULL,NULL),(14,5,1,_binary '2731188','VOLUPTATEM',1,NULL,'2022-10-01 12:21:47',2502673715,'2022-10-01 12:21:47',NULL,'2.37',2,NULL,NULL,16,NULL,NULL),(15,5,1,_binary '6867909','NON-NON-NAM',1,NULL,'2022-10-01 12:21:47',1459666866,'2022-10-01 12:21:47',NULL,'2.48',2,NULL,NULL,16,NULL,NULL),(16,5,1,_binary '1013911','NIHIL',1,NULL,'2022-10-01 12:21:47',99406860,'2022-10-01 12:21:47',NULL,'2.42',2,NULL,NULL,16,NULL,NULL),(17,5,1,_binary '3613114','ALIQUID',1,NULL,'2022-10-01 12:21:48',1171302951,'2022-10-01 12:21:48',NULL,'2.8',2,NULL,NULL,16,NULL,NULL),(18,5,1,_binary '1513671','EST-HARUM-PERSPICIATIS',1,NULL,'2022-10-01 12:21:48',1909606466,'2022-10-01 12:21:48',NULL,'2.15',2,NULL,NULL,16,NULL,NULL),(19,5,1,_binary '3305326','QUOD-EST-QUIA',1,NULL,'2022-10-01 12:21:48',2136764353,'2022-10-01 12:21:48',NULL,'2.4',2,NULL,NULL,16,NULL,NULL),(20,5,1,_binary '2475178','QUIS-OPTIO',1,NULL,'2022-10-01 12:21:48',2806815145,'2022-10-01 12:21:48',NULL,'2.33',2,NULL,NULL,16,NULL,NULL),(21,5,1,_binary '5233381','VELIT',1,NULL,'2022-10-01 12:21:48',1043719676,'2022-10-01 12:21:48',NULL,'2.40',2,NULL,NULL,16,NULL,NULL),(22,6,2,_binary '7961653','SUPLER MEGA DEVICE',1,NULL,'2022-10-01 12:21:48',200081959,'2022-10-01 12:21:48',NULL,'2.32',2,NULL,NULL,16,NULL,NULL);
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
  `caption` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `supla_location` VALUES (1,1,'ceb6','Location #2',1),(2,2,'40ad','Location #2',1),(3,1,'a2bb','Sypialnia',1),(4,1,'63e0','Na zewnątrz',1),(5,1,'0414','Garaż',1),(6,2,'a4d5','Supler\'s location',1);
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
  `api_client_authorization_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2402564B5F37A13B` (`token`),
  KEY `IDX_2402564B19EB6921` (`client_id`),
  KEY `IDX_2402564BA76ED395` (`user_id`),
  KEY `IDX_2402564B4FEA67CF` (`access_id`),
  KEY `IDX_2402564BCA22CF77` (`api_client_authorization_id`),
  CONSTRAINT `FK_2402564B19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564B4FEA67CF` FOREIGN KEY (`access_id`) REFERENCES `supla_accessid` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564BCA22CF77` FOREIGN KEY (`api_client_authorization_id`) REFERENCES `supla_oauth_client_authorizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_oauth_access_tokens`
--

LOCK TABLES `supla_oauth_access_tokens` WRITE;
/*!40000 ALTER TABLE `supla_oauth_access_tokens` DISABLE KEYS */;
INSERT INTO `supla_oauth_access_tokens` VALUES (1,1,1,'0123456789012345678901234567890123456789',2051218800,'offline_access channels_ea channelgroups_ea channels_files state_webhook mqtt_broker scenes_ea accessids_r accessids_rw account_r account_rw channels_r channels_rw channelgroups_r channelgroups_rw clientapps_r clientapps_rw directlinks_r directlinks_rw iodevices_r iodevices_rw locations_r locations_rw scenes_r scenes_rw schedules_r schedules_rw',NULL,NULL,NULL);
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
  `mqtt_broker_auth_password` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_oauth_clients`
--

LOCK TABLES `supla_oauth_clients` WRITE;
/*!40000 ALTER TABLE `supla_oauth_clients` DISABLE KEYS */;
INSERT INTO `supla_oauth_clients` VALUES (1,'2tuzcrmzm96oko8s4g0o00oocw4wsww0scksowog44w8ogg8o4','a:0:{}','5um09157qqkg0cw8wg80gso0ss84kkk4ckwco08ssgkkwsoocw','a:2:{i:0;s:8:\"password\";i:1;s:13:\"refresh_token\";}',1,NULL,NULL,NULL,NULL,NULL),(2,'2v9sx1qc6wqocows4ocoo80s4gggg4wcgogk40k4ocosk4o0ck','a:1:{i:0;s:35:\"http://suplascripts.local/authorize\";}','658cikjp0n40wows4c8sgwcwcow0wk44wcsw84ooks44cc8koo','a:2:{i:0;s:18:\"authorization_code\";i:1;s:13:\"refresh_token\";}',4,1,'SUPLA Scripts Tester',NULL,NULL,NULL),(3,'CALLERzqczpc4wgk0oo4wsoss040k88sks4goc0osow4sk8cgc','a:1:{i:0;s:31:\"http://localhost:8080/authorize\";}','CALLERgd2oowo408gws84kwwo88k8ck8kwk4w0kccog444wocc','a:2:{i:0;s:18:\"authorization_code\";i:1;s:13:\"refresh_token\";}',4,1,'SUPLA Caller Tester',NULL,NULL,NULL),(4,'ICONSpzqczpc4wgk0oo4wsoss040k88sks4goc0osow4sk8cgc','a:1:{i:0;s:31:\"http://localhost:8080/authorize\";}','ICONSpgd2oowo408gws84kwwo88k8ck8kwk4w0kccog444wocc','a:2:{i:0;s:18:\"authorization_code\";i:1;s:13:\"refresh_token\";}',4,1,'SUPLA Icons Tester',NULL,NULL,NULL);
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
  `api_client_authorization_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B809538C5F37A13B` (`token`),
  KEY `IDX_B809538C19EB6921` (`client_id`),
  KEY `IDX_B809538CA76ED395` (`user_id`),
  KEY `IDX_B809538CCA22CF77` (`api_client_authorization_id`),
  CONSTRAINT `FK_B809538C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B809538CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_B809538CCA22CF77` FOREIGN KEY (`api_client_authorization_id`) REFERENCES `supla_oauth_client_authorizations` (`id`) ON DELETE CASCADE
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
INSERT INTO `supla_rel_cg` VALUES (1,1),(166,11),(167,8),(168,10),(169,6),(169,7),(177,8),(178,2),(178,5),(179,7),(179,9),(186,11),(187,8),(188,10),(189,4),(189,6),(189,7),(189,9),(196,11),(197,8),(198,10),(199,6),(199,7),(199,9),(206,11),(207,8),(209,4),(209,9),(216,11),(217,8),(218,10),(219,6),(219,7),(219,9),(226,11),(227,8),(228,10),(229,6),(229,7),(229,9),(236,11),(237,8),(238,2),(238,10),(239,3),(239,4),(239,6),(239,9),(246,11),(249,6),(249,9),(256,11),(257,8),(258,2),(258,10),(259,6),(259,7),(259,9),(266,11),(267,8),(268,2),(268,10),(269,4),(269,7),(269,9),(278,10),(279,6),(279,7),(279,9),(286,11),(288,2),(288,10),(289,3),(289,4),(289,6),(289,7),(289,9),(296,11),(297,8),(298,10),(299,6),(299,7),(299,9),(306,1),(306,11),(307,8),(308,2),(309,6),(309,7),(309,9);
/*!40000 ALTER TABLE `supla_rel_cg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_scene`
--

DROP TABLE IF EXISTS `supla_scene`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_scene` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `caption` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `user_icon_id` int(11) DEFAULT NULL,
  `alt_icon` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '(DC2Type:tinyint)',
  PRIMARY KEY (`id`),
  KEY `IDX_A4825857A76ED395` (`user_id`),
  KEY `IDX_A482585764D218E` (`location_id`),
  KEY `IDX_A4825857CB4C938` (`user_icon_id`),
  CONSTRAINT `FK_A482585764D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
  CONSTRAINT `FK_A4825857A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`),
  CONSTRAINT `FK_A4825857CB4C938` FOREIGN KEY (`user_icon_id`) REFERENCES `supla_user_icons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_scene`
--

LOCK TABLES `supla_scene` WRITE;
/*!40000 ALTER TABLE `supla_scene` DISABLE KEYS */;
INSERT INTO `supla_scene` VALUES (1,1,1,'My scene',1,NULL,0),(2,1,3,'DimGray',0,NULL,0),(3,1,3,'OrangeRed',0,NULL,0),(4,1,4,'GreenYellow',1,NULL,0),(5,1,4,'DeepSkyBlue',1,NULL,0),(6,1,3,'DeepPink',1,NULL,0),(7,1,4,'Lime',1,NULL,0),(8,1,5,'Plum',1,NULL,0),(9,1,5,'LightGray',1,NULL,0),(10,1,3,'Moccasin',1,NULL,0),(11,1,5,'Peru',1,NULL,0),(12,1,3,'Chocolate',0,NULL,0),(13,1,4,'BlueViolet',1,NULL,0),(14,1,5,'Fuchsia',1,NULL,0),(15,1,5,'DeepSkyBlue',1,NULL,0),(16,1,5,'Snow',0,NULL,0);
/*!40000 ALTER TABLE `supla_scene` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_scene_operation`
--

DROP TABLE IF EXISTS `supla_scene_operation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_scene_operation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owning_scene_id` int(11) NOT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `channel_group_id` int(11) DEFAULT NULL,
  `scene_id` int(11) DEFAULT NULL,
  `action` int(11) NOT NULL,
  `action_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delay_ms` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_64A50CF5E019BC26` (`owning_scene_id`),
  KEY `IDX_64A50CF572F5A1AA` (`channel_id`),
  KEY `IDX_64A50CF589E4AAEE` (`channel_group_id`),
  KEY `IDX_64A50CF5166053B4` (`scene_id`),
  CONSTRAINT `FK_64A50CF5166053B4` FOREIGN KEY (`scene_id`) REFERENCES `supla_scene` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64A50CF572F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64A50CF589E4AAEE` FOREIGN KEY (`channel_group_id`) REFERENCES `supla_dev_channel_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64A50CF5E019BC26` FOREIGN KEY (`owning_scene_id`) REFERENCES `supla_scene` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_scene_operation`
--

LOCK TABLES `supla_scene_operation` WRITE;
/*!40000 ALTER TABLE `supla_scene_operation` DISABLE KEYS */;
INSERT INTO `supla_scene_operation` VALUES (1,1,1,NULL,NULL,110,NULL,0),(2,1,72,NULL,NULL,80,'{\"brightness\":55}',2000),(3,1,37,NULL,NULL,90,NULL,0),(4,2,NULL,6,NULL,10100,NULL,30000),(5,2,NULL,3,NULL,40,NULL,30000),(6,2,NULL,10,NULL,20,NULL,0),(7,2,NULL,4,NULL,100,NULL,0),(8,2,NULL,2,NULL,20,NULL,1000),(9,2,113,NULL,NULL,10,NULL,0),(10,2,NULL,4,NULL,100,NULL,0),(11,2,NULL,9,NULL,50,NULL,1000),(12,2,53,NULL,NULL,10100,NULL,0),(13,3,NULL,NULL,1,3002,NULL,1000),(14,3,NULL,1,NULL,60,NULL,30000),(15,3,NULL,4,NULL,51,NULL,30000),(16,4,NULL,NULL,3,3002,NULL,0),(17,4,158,NULL,NULL,10,NULL,0),(18,4,NULL,9,NULL,40,NULL,0),(19,4,219,NULL,NULL,30,NULL,1000),(20,4,NULL,NULL,1,3001,NULL,1000),(21,4,NULL,5,NULL,10100,NULL,1000),(22,4,NULL,2,NULL,10100,NULL,0),(23,5,NULL,8,NULL,10,NULL,1000),(24,5,166,NULL,NULL,70,NULL,1000),(25,5,NULL,9,NULL,50,NULL,0),(26,5,NULL,8,NULL,10,NULL,0),(27,5,NULL,NULL,1,3000,NULL,30000),(28,6,NULL,NULL,2,3001,NULL,1000),(29,6,NULL,10,NULL,90,NULL,0),(30,6,NULL,NULL,1,3000,NULL,0),(31,6,14,NULL,NULL,10100,NULL,30000),(32,6,NULL,NULL,5,3001,NULL,1000),(33,6,NULL,NULL,1,3000,NULL,1000),(34,6,189,NULL,NULL,100,NULL,0),(35,6,NULL,1,NULL,110,NULL,0),(36,6,NULL,NULL,5,3002,NULL,30000),(37,7,NULL,NULL,5,3002,NULL,0),(38,7,NULL,10,NULL,20,NULL,30000),(39,7,NULL,NULL,1,3002,NULL,0),(40,8,NULL,5,NULL,10100,NULL,0),(41,8,NULL,11,NULL,110,NULL,0),(42,8,249,NULL,NULL,30,NULL,1000),(43,8,NULL,NULL,1,3000,NULL,1000),(44,8,NULL,NULL,6,3002,NULL,0),(45,9,NULL,4,NULL,30,NULL,0),(46,9,NULL,NULL,8,3000,NULL,1000),(47,9,165,NULL,NULL,20,NULL,0),(48,10,NULL,NULL,5,3001,NULL,0),(49,10,NULL,10,NULL,10,NULL,1000),(50,10,NULL,4,NULL,40,NULL,30000),(51,10,NULL,2,NULL,20,NULL,0),(52,10,267,NULL,NULL,10,NULL,1000),(53,10,NULL,9,NULL,10100,NULL,0),(54,10,274,NULL,NULL,10100,NULL,0),(55,10,165,NULL,NULL,10100,NULL,30000),(56,10,NULL,NULL,1,3002,NULL,30000),(57,11,NULL,NULL,2,3002,NULL,1000),(58,12,44,NULL,NULL,70,NULL,0),(59,12,41,NULL,NULL,90,NULL,1000),(60,12,306,NULL,NULL,110,NULL,0),(61,12,NULL,10,NULL,10,NULL,0),(62,12,157,NULL,NULL,10,NULL,0),(63,12,167,NULL,NULL,10,NULL,0),(64,12,NULL,1,NULL,70,NULL,1000),(65,13,NULL,NULL,5,3001,NULL,1000),(66,13,NULL,10,NULL,20,NULL,1000),(67,13,NULL,NULL,6,3001,NULL,0),(68,13,40,NULL,NULL,10,NULL,1000),(69,13,124,NULL,NULL,30,NULL,0),(70,13,NULL,5,NULL,20,NULL,0),(71,14,179,NULL,NULL,10100,NULL,30000),(72,14,219,NULL,NULL,40,NULL,0),(73,14,174,NULL,NULL,40,NULL,1000),(74,14,NULL,2,NULL,10,NULL,0),(75,14,NULL,NULL,3,3000,NULL,0),(76,14,162,NULL,NULL,20,NULL,30000),(77,14,NULL,NULL,11,3002,NULL,0),(78,14,NULL,5,NULL,10,NULL,0),(79,14,NULL,NULL,11,3000,NULL,30000),(80,15,44,NULL,NULL,10100,NULL,0),(81,15,256,NULL,NULL,70,NULL,0),(82,15,NULL,NULL,2,3001,NULL,0),(83,15,164,NULL,NULL,10,NULL,1000),(84,15,NULL,NULL,11,3001,NULL,0),(85,15,NULL,10,NULL,20,NULL,0),(86,16,NULL,4,NULL,30,NULL,1000),(87,16,198,NULL,NULL,10,NULL,0),(88,16,NULL,2,NULL,90,NULL,0),(89,16,NULL,2,NULL,10100,NULL,1000),(90,16,NULL,9,NULL,51,NULL,30000),(91,16,199,NULL,NULL,30,NULL,0),(92,16,NULL,4,NULL,10100,NULL,1000),(93,16,NULL,NULL,9,3001,NULL,0),(94,16,NULL,3,NULL,40,NULL,0);
/*!40000 ALTER TABLE `supla_scene_operation` ENABLE KEYS */;
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
  `mode` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `date_start` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `date_end` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `enabled` tinyint(1) NOT NULL,
  `next_calculation_date` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `caption` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `retry` tinyint(1) NOT NULL DEFAULT '1',
  `channel_group_id` int(11) DEFAULT NULL,
  `scene_id` int(11) DEFAULT NULL,
  `config` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_323E8ABEA76ED395` (`user_id`),
  KEY `IDX_323E8ABE72F5A1AA` (`channel_id`),
  KEY `next_calculation_date_idx` (`next_calculation_date`),
  KEY `enabled_idx` (`enabled`),
  KEY `date_start_idx` (`date_start`),
  KEY `IDX_323E8ABE89E4AAEE` (`channel_group_id`),
  KEY `IDX_323E8ABE166053B4` (`scene_id`),
  CONSTRAINT `FK_323E8ABE166053B4` FOREIGN KEY (`scene_id`) REFERENCES `supla_scene` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_323E8ABE72F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_323E8ABE89E4AAEE` FOREIGN KEY (`channel_group_id`) REFERENCES `supla_dev_channel_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_323E8ABEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_schedule`
--

LOCK TABLES `supla_schedule` WRITE;
/*!40000 ALTER TABLE `supla_schedule` DISABLE KEYS */;
INSERT INTO `supla_schedule` VALUES (1,1,236,'minutely','2022-10-02 03:40:08',NULL,1,'2022-10-01 12:25:00','Itaque vitae rem.',1,NULL,NULL,'[{\"crontab\":\"*\\/5 * * * *\",\"action\":{\"id\":70}}]'),(2,1,208,'daily','2022-10-07 05:32:45',NULL,1,'2022-10-06 05:08:00','Aut sequi eos beatae quasi.',1,NULL,NULL,'[{\"crontab\":\"SR-10 * * * *\",\"action\":{\"id\":20}}]'),(3,1,258,'minutely','2022-10-07 11:06:48',NULL,1,'2022-10-05 12:07:00','Harum et beatae.',1,NULL,NULL,'[{\"crontab\":\"*\\/60 * * * *\",\"action\":{\"id\":20}}]'),(4,1,168,'daily','2022-10-04 14:48:03',NULL,1,'2022-10-03 05:23:00','Veritatis iusto mollitia.',1,NULL,NULL,'[{\"crontab\":\"SR10 * * * *\",\"action\":{\"id\":10}}]'),(5,1,177,'minutely','2022-10-03 13:32:40',NULL,1,'2022-10-01 13:48:00','Et itaque aliquam assumenda quo.',1,NULL,NULL,'[{\"crontab\":\"*\\/15 * * * *\",\"action\":{\"id\":10}}]'),(6,1,218,'minutely','2022-10-06 21:17:03',NULL,1,'2022-10-04 21:47:00','Repellat quis dolorem quis et.',1,NULL,NULL,'[{\"crontab\":\"*\\/30 * * * *\",\"action\":{\"id\":20}}]'),(7,1,229,'minutely','2022-10-08 01:18:17',NULL,1,'2022-10-06 02:48:00','Quos veritatis expedita dicta aperiam.',1,NULL,NULL,'[{\"crontab\":\"*\\/90 * * * *\",\"action\":{\"id\":100}}]'),(8,1,279,'daily','2022-10-02 13:23:03',NULL,1,'2022-10-02 05:23:00','At maxime.',1,NULL,NULL,'[{\"crontab\":\"SR10 * * * *\",\"action\":{\"id\":30}}]'),(9,1,229,'daily','2022-10-04 18:06:15',NULL,1,'2022-10-03 16:28:00','Harum nostrum.',1,NULL,NULL,'[{\"crontab\":\"SS-10 * * * *\",\"action\":{\"id\":40}}]'),(10,1,246,'daily','2022-10-01 17:54:54',NULL,1,'2022-10-01 16:32:00','Sed quia.',1,NULL,NULL,'[{\"crontab\":\"SS-10 * * * *\",\"action\":{\"id\":10100}}]'),(11,1,288,'minutely','2022-10-07 18:57:07',NULL,1,'2022-10-05 19:27:00','Libero nihil error soluta.',1,NULL,NULL,'[{\"crontab\":\"*\\/30 * * * *\",\"action\":{\"id\":10100}}]'),(12,1,209,'daily','2022-10-04 11:30:47',NULL,1,'2022-10-02 16:48:00','Tempora autem reprehenderit.',1,NULL,NULL,'[{\"crontab\":\"SS10 * * * *\",\"action\":{\"id\":10100}}]'),(13,1,188,'daily','2022-10-07 00:15:23',NULL,1,'2022-10-05 05:18:00','Et ut laborum.',1,NULL,NULL,'[{\"crontab\":\"SR0 * * * *\",\"action\":{\"id\":10100}}]'),(14,1,269,'daily','2022-10-03 14:43:09',NULL,1,'2022-10-01 16:30:00','Voluptatem itaque.',1,NULL,NULL,'[{\"crontab\":\"SS-10 * * * *\",\"action\":{\"id\":51}}]'),(15,1,188,'minutely','2022-10-08 05:54:36',NULL,1,'2022-10-06 06:25:00','Omnis excepturi atque.',1,NULL,NULL,'[{\"crontab\":\"*\\/30 * * * *\",\"action\":{\"id\":10100}}]');
/*!40000 ALTER TABLE `supla_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_scheduled_executions`
--

DROP TABLE IF EXISTS `supla_scheduled_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_scheduled_executions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `planned_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `fetched_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `retry_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `retry_count` int(11) DEFAULT NULL,
  `result_timestamp` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `consumed` tinyint(1) NOT NULL,
  `result` int(11) DEFAULT NULL,
  `action` int(11) NOT NULL,
  `action_param` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FB21DBDCA40BC2D5` (`schedule_id`),
  KEY `result_idx` (`result`),
  KEY `result_timestamp_idx` (`result_timestamp`),
  KEY `planned_timestamp_idx` (`planned_timestamp`),
  KEY `retry_timestamp_idx` (`retry_timestamp`),
  KEY `fetched_timestamp_idx` (`fetched_timestamp`),
  KEY `consumed_idx` (`consumed`),
  CONSTRAINT `FK_FB21DBDCA40BC2D5` FOREIGN KEY (`schedule_id`) REFERENCES `supla_schedule` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=410 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_scheduled_executions`
--

LOCK TABLES `supla_scheduled_executions` WRITE;
/*!40000 ALTER TABLE `supla_scheduled_executions` DISABLE KEYS */;
INSERT INTO `supla_scheduled_executions` VALUES (1,1,'2022-10-02 03:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(2,1,'2022-10-02 03:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(3,1,'2022-10-02 03:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(4,1,'2022-10-02 04:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(5,1,'2022-10-02 04:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(6,1,'2022-10-02 04:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(7,1,'2022-10-02 04:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(8,1,'2022-10-02 04:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(9,1,'2022-10-02 04:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(10,1,'2022-10-02 04:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(11,1,'2022-10-02 04:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(12,1,'2022-10-02 04:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(13,1,'2022-10-02 04:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(14,1,'2022-10-02 04:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(15,1,'2022-10-02 04:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(16,1,'2022-10-02 05:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(17,1,'2022-10-02 05:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(18,1,'2022-10-02 05:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(19,1,'2022-10-02 05:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(20,1,'2022-10-02 05:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(21,1,'2022-10-02 05:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(22,1,'2022-10-02 05:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(23,1,'2022-10-02 05:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(24,1,'2022-10-02 05:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(25,1,'2022-10-02 05:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(26,1,'2022-10-02 05:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(27,1,'2022-10-02 05:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(28,1,'2022-10-02 06:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(29,1,'2022-10-02 06:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(30,1,'2022-10-02 06:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(31,1,'2022-10-02 06:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(32,1,'2022-10-02 06:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(33,1,'2022-10-02 06:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(34,1,'2022-10-02 06:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(35,1,'2022-10-02 06:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(36,1,'2022-10-02 06:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(37,1,'2022-10-02 06:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(38,1,'2022-10-02 06:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(39,1,'2022-10-02 06:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(40,1,'2022-10-02 07:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(41,1,'2022-10-02 07:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(42,1,'2022-10-02 07:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(43,1,'2022-10-02 07:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(44,1,'2022-10-02 07:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(45,1,'2022-10-02 07:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(46,1,'2022-10-02 07:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(47,1,'2022-10-02 07:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(48,1,'2022-10-02 07:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(49,1,'2022-10-02 07:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(50,1,'2022-10-02 07:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(51,1,'2022-10-02 07:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(52,1,'2022-10-02 08:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(53,1,'2022-10-02 08:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(54,1,'2022-10-02 08:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(55,1,'2022-10-02 08:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(56,1,'2022-10-02 08:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(57,1,'2022-10-02 08:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(58,1,'2022-10-02 08:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(59,1,'2022-10-02 08:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(60,1,'2022-10-02 08:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(61,1,'2022-10-02 08:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(62,1,'2022-10-02 08:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(63,1,'2022-10-02 08:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(64,1,'2022-10-02 09:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(65,1,'2022-10-02 09:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(66,1,'2022-10-02 09:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(67,1,'2022-10-02 09:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(68,1,'2022-10-02 09:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(69,1,'2022-10-02 09:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(70,1,'2022-10-02 09:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(71,1,'2022-10-02 09:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(72,1,'2022-10-02 09:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(73,1,'2022-10-02 09:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(74,1,'2022-10-02 09:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(75,1,'2022-10-02 09:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(76,1,'2022-10-02 10:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(77,1,'2022-10-02 10:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(78,1,'2022-10-02 10:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(79,1,'2022-10-02 10:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(80,1,'2022-10-02 10:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(81,1,'2022-10-02 10:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(82,1,'2022-10-02 10:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(83,1,'2022-10-02 10:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(84,1,'2022-10-02 10:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(85,1,'2022-10-02 10:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(86,1,'2022-10-02 10:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(87,1,'2022-10-02 10:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(88,1,'2022-10-02 11:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(89,1,'2022-10-02 11:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(90,1,'2022-10-02 11:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(91,1,'2022-10-02 11:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(92,1,'2022-10-02 11:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(93,1,'2022-10-02 11:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(94,1,'2022-10-02 11:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(95,1,'2022-10-02 11:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(96,1,'2022-10-02 11:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(97,1,'2022-10-02 11:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(98,1,'2022-10-02 11:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(99,1,'2022-10-02 11:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(100,1,'2022-10-02 12:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(101,1,'2022-10-02 12:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(102,1,'2022-10-02 12:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(103,1,'2022-10-02 12:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(104,1,'2022-10-02 12:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(105,1,'2022-10-02 12:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(106,1,'2022-10-02 12:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(107,1,'2022-10-02 12:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(108,1,'2022-10-02 12:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(109,1,'2022-10-02 12:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(110,1,'2022-10-02 12:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(111,1,'2022-10-02 12:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(112,1,'2022-10-02 13:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(113,1,'2022-10-02 13:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(114,1,'2022-10-02 13:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(115,1,'2022-10-02 13:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(116,1,'2022-10-02 13:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(117,1,'2022-10-02 13:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(118,1,'2022-10-02 13:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(119,1,'2022-10-02 13:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(120,1,'2022-10-02 13:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(121,1,'2022-10-02 13:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(122,1,'2022-10-02 13:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(123,1,'2022-10-02 13:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(124,1,'2022-10-02 14:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(125,1,'2022-10-02 14:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(126,1,'2022-10-02 14:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(127,1,'2022-10-02 14:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(128,1,'2022-10-02 14:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(129,1,'2022-10-02 14:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(130,1,'2022-10-02 14:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(131,1,'2022-10-02 14:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(132,1,'2022-10-02 14:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(133,1,'2022-10-02 14:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(134,1,'2022-10-02 14:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(135,1,'2022-10-02 14:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(136,1,'2022-10-02 15:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(137,1,'2022-10-02 15:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(138,1,'2022-10-02 15:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(139,1,'2022-10-02 15:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(140,1,'2022-10-02 15:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(141,1,'2022-10-02 15:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(142,1,'2022-10-02 15:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(143,1,'2022-10-02 15:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(144,1,'2022-10-02 15:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(145,1,'2022-10-02 15:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(146,1,'2022-10-02 15:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(147,1,'2022-10-02 15:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(148,1,'2022-10-02 16:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(149,1,'2022-10-02 16:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(150,1,'2022-10-02 16:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(151,1,'2022-10-02 16:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(152,1,'2022-10-02 16:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(153,1,'2022-10-02 16:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(154,1,'2022-10-02 16:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(155,1,'2022-10-02 16:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(156,1,'2022-10-02 16:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(157,1,'2022-10-02 16:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(158,1,'2022-10-02 16:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(159,1,'2022-10-02 16:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(160,1,'2022-10-02 17:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(161,1,'2022-10-02 17:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(162,1,'2022-10-02 17:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(163,1,'2022-10-02 17:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(164,1,'2022-10-02 17:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(165,1,'2022-10-02 17:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(166,1,'2022-10-02 17:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(167,1,'2022-10-02 17:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(168,1,'2022-10-02 17:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(169,1,'2022-10-02 17:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(170,1,'2022-10-02 17:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(171,1,'2022-10-02 17:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(172,1,'2022-10-02 18:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(173,1,'2022-10-02 18:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(174,1,'2022-10-02 18:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(175,1,'2022-10-02 18:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(176,1,'2022-10-02 18:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(177,1,'2022-10-02 18:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(178,1,'2022-10-02 18:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(179,1,'2022-10-02 18:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(180,1,'2022-10-02 18:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(181,1,'2022-10-02 18:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(182,1,'2022-10-02 18:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(183,1,'2022-10-02 18:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(184,1,'2022-10-02 19:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(185,1,'2022-10-02 19:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(186,1,'2022-10-02 19:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(187,1,'2022-10-02 19:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(188,1,'2022-10-02 19:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(189,1,'2022-10-02 19:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(190,1,'2022-10-02 19:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(191,1,'2022-10-02 19:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(192,1,'2022-10-02 19:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(193,1,'2022-10-02 19:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(194,1,'2022-10-02 19:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(195,1,'2022-10-02 19:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(196,1,'2022-10-02 20:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(197,1,'2022-10-02 20:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(198,1,'2022-10-02 20:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(199,1,'2022-10-02 20:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(200,1,'2022-10-02 20:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(201,1,'2022-10-02 20:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(202,1,'2022-10-02 20:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(203,1,'2022-10-02 20:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(204,1,'2022-10-02 20:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(205,1,'2022-10-02 20:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(206,1,'2022-10-02 20:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(207,1,'2022-10-02 20:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(208,1,'2022-10-02 21:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(209,1,'2022-10-02 21:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(210,1,'2022-10-02 21:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(211,1,'2022-10-02 21:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(212,1,'2022-10-02 21:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(213,1,'2022-10-02 21:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(214,1,'2022-10-02 21:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(215,1,'2022-10-02 21:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(216,1,'2022-10-02 21:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(217,1,'2022-10-02 21:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(218,1,'2022-10-02 21:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(219,1,'2022-10-02 21:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(220,1,'2022-10-02 22:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(221,1,'2022-10-02 22:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(222,1,'2022-10-02 22:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(223,1,'2022-10-02 22:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(224,1,'2022-10-02 22:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(225,1,'2022-10-02 22:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(226,1,'2022-10-02 22:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(227,1,'2022-10-02 22:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(228,1,'2022-10-02 22:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(229,1,'2022-10-02 22:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(230,1,'2022-10-02 22:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(231,1,'2022-10-02 22:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(232,1,'2022-10-02 23:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(233,1,'2022-10-02 23:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(234,1,'2022-10-02 23:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(235,1,'2022-10-02 23:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(236,1,'2022-10-02 23:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(237,1,'2022-10-02 23:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(238,1,'2022-10-02 23:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(239,1,'2022-10-02 23:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(240,1,'2022-10-02 23:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(241,1,'2022-10-02 23:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(242,1,'2022-10-02 23:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(243,1,'2022-10-02 23:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(244,1,'2022-10-03 00:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(245,1,'2022-10-03 00:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(246,1,'2022-10-03 00:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(247,1,'2022-10-03 00:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(248,1,'2022-10-03 00:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(249,1,'2022-10-03 00:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(250,1,'2022-10-03 00:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(251,1,'2022-10-03 00:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(252,1,'2022-10-03 00:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(253,1,'2022-10-03 00:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(254,1,'2022-10-03 00:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(255,1,'2022-10-03 00:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(256,1,'2022-10-03 01:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(257,1,'2022-10-03 01:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(258,1,'2022-10-03 01:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(259,1,'2022-10-03 01:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(260,1,'2022-10-03 01:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(261,1,'2022-10-03 01:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(262,1,'2022-10-03 01:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(263,1,'2022-10-03 01:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(264,1,'2022-10-03 01:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(265,1,'2022-10-03 01:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(266,1,'2022-10-03 01:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(267,1,'2022-10-03 01:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(268,1,'2022-10-03 02:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(269,1,'2022-10-03 02:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(270,1,'2022-10-03 02:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(271,1,'2022-10-03 02:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(272,1,'2022-10-03 02:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(273,1,'2022-10-03 02:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(274,1,'2022-10-03 02:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(275,1,'2022-10-03 02:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(276,1,'2022-10-03 02:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(277,1,'2022-10-03 02:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(278,1,'2022-10-03 02:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(279,1,'2022-10-03 02:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(280,1,'2022-10-03 03:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(281,1,'2022-10-03 03:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(282,1,'2022-10-03 03:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(283,1,'2022-10-03 03:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(284,1,'2022-10-03 03:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(285,1,'2022-10-03 03:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(286,1,'2022-10-03 03:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(287,1,'2022-10-03 03:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(288,1,'2022-10-03 03:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(289,1,'2022-10-03 03:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(290,1,'2022-10-03 03:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(291,1,'2022-10-03 03:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(292,1,'2022-10-03 04:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(293,1,'2022-10-03 04:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(294,1,'2022-10-03 04:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(295,1,'2022-10-03 04:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(296,1,'2022-10-03 04:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(297,1,'2022-10-03 04:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(298,1,'2022-10-03 04:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(299,1,'2022-10-03 04:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(300,1,'2022-10-03 04:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(301,1,'2022-10-03 04:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(302,1,'2022-10-03 04:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(303,1,'2022-10-03 04:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(304,1,'2022-10-03 05:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(305,1,'2022-10-03 05:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(306,1,'2022-10-03 05:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(307,1,'2022-10-03 05:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(308,1,'2022-10-03 05:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(309,1,'2022-10-03 05:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(310,1,'2022-10-03 05:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(311,1,'2022-10-03 05:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(312,1,'2022-10-03 05:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(313,1,'2022-10-03 05:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(314,1,'2022-10-03 05:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(315,1,'2022-10-03 05:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(316,1,'2022-10-03 06:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(317,1,'2022-10-03 06:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(318,1,'2022-10-03 06:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(319,1,'2022-10-03 06:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(320,1,'2022-10-03 06:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(321,1,'2022-10-03 06:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(322,1,'2022-10-03 06:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(323,1,'2022-10-03 06:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(324,1,'2022-10-03 06:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(325,1,'2022-10-03 06:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(326,1,'2022-10-03 06:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(327,1,'2022-10-03 06:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(328,1,'2022-10-03 07:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(329,1,'2022-10-03 07:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(330,1,'2022-10-03 07:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(331,1,'2022-10-03 07:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(332,1,'2022-10-03 07:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(333,1,'2022-10-03 07:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(334,1,'2022-10-03 07:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(335,1,'2022-10-03 07:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(336,1,'2022-10-03 07:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(337,1,'2022-10-03 07:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(338,1,'2022-10-03 07:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(339,1,'2022-10-03 07:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(340,1,'2022-10-03 08:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(341,1,'2022-10-03 08:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(342,1,'2022-10-03 08:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(343,1,'2022-10-03 08:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(344,1,'2022-10-03 08:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(345,1,'2022-10-03 08:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(346,1,'2022-10-03 08:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(347,1,'2022-10-03 08:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(348,1,'2022-10-03 08:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(349,1,'2022-10-03 08:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(350,1,'2022-10-03 08:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(351,1,'2022-10-03 08:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(352,1,'2022-10-03 09:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(353,1,'2022-10-03 09:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(354,1,'2022-10-03 09:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(355,1,'2022-10-03 09:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(356,1,'2022-10-03 09:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(357,1,'2022-10-03 09:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(358,1,'2022-10-03 09:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(359,1,'2022-10-03 09:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(360,1,'2022-10-03 09:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(361,1,'2022-10-03 09:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(362,1,'2022-10-03 09:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(363,1,'2022-10-03 09:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(364,1,'2022-10-03 10:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(365,1,'2022-10-03 10:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(366,1,'2022-10-03 10:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(367,1,'2022-10-03 10:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(368,1,'2022-10-03 10:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(369,1,'2022-10-03 10:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(370,1,'2022-10-03 10:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(371,1,'2022-10-03 10:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(372,1,'2022-10-03 10:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(373,1,'2022-10-03 10:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(374,1,'2022-10-03 10:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(375,1,'2022-10-03 10:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(376,1,'2022-10-03 11:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(377,1,'2022-10-03 11:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(378,1,'2022-10-03 11:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(379,1,'2022-10-03 11:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(380,1,'2022-10-03 11:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(381,1,'2022-10-03 11:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(382,1,'2022-10-03 11:30:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(383,1,'2022-10-03 11:35:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(384,1,'2022-10-03 11:40:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(385,1,'2022-10-03 11:45:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(386,1,'2022-10-03 11:50:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(387,1,'2022-10-03 11:55:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(388,1,'2022-10-03 12:00:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(389,1,'2022-10-03 12:05:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(390,1,'2022-10-03 12:10:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(391,1,'2022-10-03 12:15:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(392,1,'2022-10-03 12:20:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(393,1,'2022-10-03 12:25:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(394,2,'2022-10-08 05:08:00',NULL,NULL,NULL,NULL,0,NULL,20,NULL),(395,3,'2022-10-07 12:07:00',NULL,NULL,NULL,NULL,0,NULL,20,NULL),(396,4,'2022-10-05 05:23:00',NULL,NULL,NULL,NULL,0,NULL,10,NULL),(397,5,'2022-10-03 13:48:00',NULL,NULL,NULL,NULL,0,NULL,10,NULL),(398,6,'2022-10-06 21:47:00',NULL,NULL,NULL,NULL,0,NULL,20,NULL),(399,7,'2022-10-08 02:48:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(400,8,'2022-10-03 05:19:00',NULL,NULL,NULL,NULL,0,NULL,30,NULL),(401,8,'2022-10-04 05:23:00',NULL,NULL,NULL,NULL,0,NULL,30,NULL),(402,9,'2022-10-05 16:28:00',NULL,NULL,NULL,NULL,0,NULL,40,NULL),(403,10,'2022-10-02 16:35:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL),(404,10,'2022-10-03 16:32:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL),(405,11,'2022-10-07 19:27:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL),(406,12,'2022-10-04 16:48:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL),(407,13,'2022-10-07 05:18:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL),(408,14,'2022-10-03 16:30:00',NULL,NULL,NULL,NULL,0,NULL,51,NULL),(409,15,'2022-10-08 06:25:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL);
/*!40000 ALTER TABLE `supla_scheduled_executions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_state_webhooks`
--

DROP TABLE IF EXISTS `supla_state_webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_state_webhooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:utcdatetime)',
  `functions_ids` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `IDX_3C9361E019EB6921` (`client_id`),
  KEY `IDX_3C9361E0A76ED395` (`user_id`),
  CONSTRAINT `FK_3C9361E019EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3C9361E0A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_state_webhooks`
--

LOCK TABLES `supla_state_webhooks` WRITE;
/*!40000 ALTER TABLE `supla_state_webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_state_webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_temperature_log`
--

DROP TABLE IF EXISTS `supla_temperature_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_temperature_log` (
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:stringdatetime)',
  `temperature` decimal(8,4) NOT NULL,
  PRIMARY KEY (`channel_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_temperature_log`
--

LOCK TABLES `supla_temperature_log` WRITE;
/*!40000 ALTER TABLE `supla_temperature_log` DISABLE KEYS */;
INSERT INTO `supla_temperature_log` VALUES (2,'2022-09-30 12:21:50',9.5100),(2,'2022-09-30 12:31:50',10.3900),(2,'2022-09-30 12:41:50',11.2500),(2,'2022-09-30 12:51:50',10.7900),(2,'2022-09-30 13:01:50',11.2900),(2,'2022-09-30 13:11:50',11.9400),(2,'2022-09-30 13:21:50',11.9100),(2,'2022-09-30 13:31:50',11.4900),(2,'2022-09-30 13:41:50',12.1000),(2,'2022-09-30 13:51:50',13.0100),(2,'2022-09-30 14:01:50',13.8500),(2,'2022-09-30 14:11:50',14.7100),(2,'2022-09-30 14:21:50',13.7700),(2,'2022-09-30 14:31:50',13.5100),(2,'2022-09-30 14:41:50',14.0700),(2,'2022-09-30 14:51:50',13.9200),(2,'2022-09-30 15:01:50',13.1500),(2,'2022-09-30 15:11:50',12.5400),(2,'2022-09-30 15:21:50',12.4900),(2,'2022-09-30 15:31:50',13.4300),(2,'2022-09-30 15:41:50',13.8600),(2,'2022-09-30 15:51:50',13.0700),(2,'2022-09-30 16:01:50',12.8000),(2,'2022-09-30 16:21:50',11.3500),(2,'2022-09-30 16:31:50',12.0100),(2,'2022-09-30 16:41:50',11.8600),(2,'2022-09-30 16:51:50',11.2900),(2,'2022-09-30 17:01:50',10.8000),(2,'2022-09-30 17:11:50',11.6800),(2,'2022-09-30 17:21:50',10.6900),(2,'2022-09-30 17:51:50',9.6300),(2,'2022-09-30 18:01:50',10.2300),(2,'2022-09-30 18:11:50',10.8800),(2,'2022-09-30 18:21:50',9.8900),(2,'2022-09-30 18:31:50',8.9700),(2,'2022-09-30 18:41:50',9.6300),(2,'2022-09-30 18:51:50',9.7100),(2,'2022-09-30 19:01:50',10.2700),(2,'2022-09-30 19:11:50',11.1700),(2,'2022-09-30 19:21:50',10.3300),(2,'2022-09-30 19:41:50',10.6100),(2,'2022-09-30 19:51:50',11.6100),(2,'2022-09-30 20:01:50',11.2800),(2,'2022-09-30 20:11:50',12.1100),(2,'2022-09-30 20:21:50',11.3300),(2,'2022-09-30 20:31:50',11.6900),(2,'2022-09-30 20:41:50',12.4600),(2,'2022-09-30 20:51:50',11.5200),(2,'2022-09-30 21:01:50',11.1000),(2,'2022-09-30 21:11:50',10.2100),(2,'2022-09-30 21:21:50',10.0400),(2,'2022-09-30 21:31:50',10.9000),(2,'2022-09-30 21:41:50',11.6000),(2,'2022-09-30 21:51:50',11.1500),(2,'2022-09-30 22:01:50',10.9500),(2,'2022-09-30 22:11:50',10.4900),(2,'2022-09-30 22:21:50',9.8300),(2,'2022-09-30 22:31:50',10.5700),(2,'2022-09-30 22:41:50',9.8000),(2,'2022-09-30 22:51:50',10.8000),(2,'2022-09-30 23:01:50',11.6000),(2,'2022-09-30 23:11:50',11.1000),(2,'2022-09-30 23:21:50',11.2600),(2,'2022-09-30 23:31:50',11.5500),(2,'2022-09-30 23:41:50',12.3400),(2,'2022-09-30 23:51:50',12.4400),(2,'2022-10-01 00:01:50',12.9700),(2,'2022-10-01 00:11:50',13.6400),(2,'2022-10-01 00:21:50',14.4600),(2,'2022-10-01 00:31:50',15.0500),(2,'2022-10-01 00:41:50',15.5800),(2,'2022-10-01 00:51:50',14.9200),(2,'2022-10-01 01:01:50',14.1200),(2,'2022-10-01 01:11:50',14.9400),(2,'2022-10-01 01:21:50',14.1600),(2,'2022-10-01 01:31:50',14.6100),(2,'2022-10-01 01:41:50',13.8000),(2,'2022-10-01 01:51:50',14.3200),(2,'2022-10-01 02:01:50',13.8500),(2,'2022-10-01 02:11:50',13.1500),(2,'2022-10-01 02:21:50',12.8800),(2,'2022-10-01 02:31:50',13.4800),(2,'2022-10-01 02:41:50',14.4700),(2,'2022-10-01 02:51:50',13.7000),(2,'2022-10-01 03:01:50',14.2200),(2,'2022-10-01 03:11:50',14.0000),(2,'2022-10-01 03:21:50',13.1100),(2,'2022-10-01 03:31:50',13.8700),(2,'2022-10-01 03:41:50',12.9300),(2,'2022-10-01 03:51:50',12.7800),(2,'2022-10-01 04:01:50',11.8500),(2,'2022-10-01 04:11:50',12.7300),(2,'2022-10-01 04:21:50',13.7100),(2,'2022-10-01 04:31:50',14.6600),(2,'2022-10-01 04:41:50',13.8900),(2,'2022-10-01 04:51:50',14.4800),(2,'2022-10-01 05:01:50',14.6800),(2,'2022-10-01 05:11:50',14.7900),(2,'2022-10-01 05:21:50',15.2100),(2,'2022-10-01 05:31:50',16.1000),(2,'2022-10-01 05:41:50',15.5200),(2,'2022-10-01 05:51:50',16.3100),(2,'2022-10-01 06:11:50',17.7700),(2,'2022-10-01 06:21:50',16.7900),(2,'2022-10-01 06:31:50',15.8300),(2,'2022-10-01 06:41:50',15.0300),(2,'2022-10-01 06:51:50',15.5000),(2,'2022-10-01 07:01:50',14.8900),(2,'2022-10-01 07:11:50',14.3100),(2,'2022-10-01 07:21:50',15.1600),(2,'2022-10-01 07:31:50',15.4200),(2,'2022-10-01 07:41:50',14.5200),(2,'2022-10-01 07:51:50',15.1500),(2,'2022-10-01 08:01:50',14.2200),(2,'2022-10-01 08:11:50',14.3600),(2,'2022-10-01 08:21:50',14.0700),(2,'2022-10-01 08:31:50',13.2800),(2,'2022-10-01 08:41:50',14.2100),(2,'2022-10-01 08:51:50',15.0200),(2,'2022-10-01 09:01:50',15.2900),(2,'2022-10-01 09:11:50',16.1300),(2,'2022-10-01 09:21:50',15.2100),(2,'2022-10-01 09:31:50',14.3800),(2,'2022-10-01 09:41:50',14.6600),(2,'2022-10-01 09:51:50',13.7100),(2,'2022-10-01 10:01:50',14.6700),(2,'2022-10-01 10:11:50',15.3800),(2,'2022-10-01 10:21:50',16.1700),(2,'2022-10-01 10:31:50',15.1700),(2,'2022-10-01 10:41:50',14.9000),(2,'2022-10-01 10:51:50',14.3200),(2,'2022-10-01 11:01:50',13.4600),(2,'2022-10-01 11:11:50',13.2400),(2,'2022-10-01 11:31:50',13.9600),(2,'2022-10-01 11:41:50',14.4700),(2,'2022-10-01 11:51:50',14.2200),(2,'2022-10-01 12:01:50',14.7500),(2,'2022-10-01 12:11:50',14.3100);
/*!40000 ALTER TABLE `supla_temperature_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_temphumidity_log`
--

DROP TABLE IF EXISTS `supla_temphumidity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_temphumidity_log` (
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:stringdatetime)',
  `temperature` decimal(8,4) NOT NULL,
  `humidity` decimal(8,4) NOT NULL,
  PRIMARY KEY (`channel_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_temphumidity_log`
--

LOCK TABLES `supla_temphumidity_log` WRITE;
/*!40000 ALTER TABLE `supla_temphumidity_log` DISABLE KEYS */;
INSERT INTO `supla_temphumidity_log` VALUES (57,'2022-09-30 12:21:50',10.6400,50.6800),(57,'2022-09-30 12:31:50',11.4100,49.7000),(57,'2022-09-30 12:41:50',10.8600,50.6700),(57,'2022-09-30 12:51:50',10.0100,51.6100),(57,'2022-09-30 13:01:50',9.3800,52.4400),(57,'2022-09-30 13:11:50',9.0000,52.7700),(57,'2022-09-30 13:21:50',9.5200,52.6700),(57,'2022-09-30 13:31:50',10.4900,53.4400),(57,'2022-09-30 13:41:50',10.9300,54.1600),(57,'2022-09-30 13:51:50',11.7900,53.9800),(57,'2022-09-30 14:01:50',10.9900,54.7400),(57,'2022-09-30 14:11:50',10.7400,55.1100),(57,'2022-09-30 14:21:50',10.1500,55.8600),(57,'2022-09-30 14:31:50',9.7700,55.5200),(57,'2022-09-30 14:51:50',9.4700,55.7300),(57,'2022-09-30 15:01:50',10.4000,55.4500),(57,'2022-09-30 15:11:50',9.8600,54.7300),(57,'2022-09-30 15:21:50',10.3800,53.8700),(57,'2022-09-30 15:41:50',10.2500,53.4200),(57,'2022-09-30 15:51:50',10.6400,53.6600),(57,'2022-09-30 16:01:50',11.1700,54.2700),(57,'2022-09-30 16:11:50',12.1700,53.8700),(57,'2022-09-30 16:21:50',11.9800,54.0200),(57,'2022-09-30 16:31:50',12.8300,54.8000),(57,'2022-09-30 16:51:50',11.3900,55.2800),(57,'2022-09-30 17:01:50',12.3700,54.7300),(57,'2022-09-30 17:11:50',11.7600,53.9900),(57,'2022-09-30 17:21:50',11.1100,54.4700),(57,'2022-09-30 17:31:50',10.9400,53.7200),(57,'2022-09-30 17:41:50',11.9200,52.8100),(57,'2022-09-30 17:51:50',11.0100,53.7400),(57,'2022-09-30 18:01:50',10.0300,54.5900),(57,'2022-09-30 18:11:50',9.0400,54.2900),(57,'2022-09-30 18:21:50',9.8400,53.3300),(57,'2022-09-30 18:31:50',9.2300,52.3800),(57,'2022-09-30 18:41:50',10.1500,52.0400),(57,'2022-09-30 18:51:50',9.2600,52.8100),(57,'2022-09-30 19:01:50',8.9200,52.1400),(57,'2022-09-30 19:11:50',9.6000,52.0600),(57,'2022-09-30 19:21:50',8.9500,52.4500),(57,'2022-09-30 19:31:50',9.7100,51.5700),(57,'2022-09-30 19:41:50',10.0400,52.4800),(57,'2022-09-30 19:51:50',10.5300,52.8600),(57,'2022-09-30 20:01:50',10.7800,53.3800),(57,'2022-09-30 20:11:50',10.7000,52.8700),(57,'2022-09-30 20:21:50',9.9700,53.4200),(57,'2022-09-30 20:31:50',10.5800,54.2000),(57,'2022-09-30 20:41:50',11.5100,53.6900),(57,'2022-09-30 20:51:50',12.0400,54.2000),(57,'2022-09-30 21:01:50',11.6400,53.2700),(57,'2022-09-30 21:11:50',10.7000,54.2500),(57,'2022-09-30 21:21:50',10.9100,53.3800),(57,'2022-09-30 21:31:50',11.6000,52.5100),(57,'2022-09-30 21:41:50',12.4500,52.7600),(57,'2022-09-30 21:51:50',12.7900,53.4700),(57,'2022-09-30 22:01:50',13.2800,52.7400),(57,'2022-09-30 22:11:50',12.3200,53.3000),(57,'2022-09-30 22:21:50',11.6500,52.4100),(57,'2022-09-30 22:41:50',11.5300,53.9400),(57,'2022-09-30 23:01:50',10.7200,54.2600),(57,'2022-09-30 23:11:50',11.2600,55.0700),(57,'2022-09-30 23:21:50',11.6100,54.5000),(57,'2022-09-30 23:31:50',10.6200,55.2100),(57,'2022-09-30 23:41:50',10.0900,54.4100),(57,'2022-09-30 23:51:50',10.1000,55.3400),(57,'2022-10-01 00:01:50',9.6000,55.2000),(57,'2022-10-01 00:11:50',10.4500,54.2000),(57,'2022-10-01 00:21:50',9.8000,53.9200),(57,'2022-10-01 00:31:50',10.2300,54.3500),(57,'2022-10-01 00:41:50',11.0000,53.5400),(57,'2022-10-01 00:51:50',10.1700,54.1600),(57,'2022-10-01 01:01:50',10.8100,53.5100),(57,'2022-10-01 01:11:50',11.6600,54.1200),(57,'2022-10-01 01:21:50',10.8300,53.6000),(57,'2022-10-01 01:31:50',10.9400,54.4100),(57,'2022-10-01 01:41:50',11.2900,54.5300),(57,'2022-10-01 01:51:50',12.2800,54.1000),(57,'2022-10-01 02:01:50',11.7400,54.9700),(57,'2022-10-01 02:11:50',12.7400,54.7800),(57,'2022-10-01 02:21:50',13.5400,54.1600),(57,'2022-10-01 02:31:50',13.0700,53.8100),(57,'2022-10-01 02:41:50',12.3800,54.5100),(57,'2022-10-01 02:51:50',13.3500,55.2400),(57,'2022-10-01 03:01:50',12.4800,54.5600),(57,'2022-10-01 03:11:50',12.8600,54.4500),(57,'2022-10-01 03:21:50',13.0100,55.3400),(57,'2022-10-01 03:31:50',13.8800,55.1600),(57,'2022-10-01 03:41:50',12.9900,54.3900),(57,'2022-10-01 03:51:50',12.9200,54.9200),(57,'2022-10-01 04:01:50',12.3400,53.9300),(57,'2022-10-01 04:11:50',11.9900,52.9400),(57,'2022-10-01 04:21:50',12.3700,53.5500),(57,'2022-10-01 04:31:50',11.5200,53.7700),(57,'2022-10-01 04:41:50',10.5200,53.1700),(57,'2022-10-01 04:51:50',9.7900,53.8900),(57,'2022-10-01 05:01:50',10.3100,54.3000),(57,'2022-10-01 05:11:50',11.1900,54.5300),(57,'2022-10-01 05:21:50',11.1200,54.3600),(57,'2022-10-01 05:31:50',11.7000,53.5500),(57,'2022-10-01 05:41:50',12.6400,53.9000),(57,'2022-10-01 05:51:50',12.7700,54.1600),(57,'2022-10-01 06:01:50',12.6800,54.7100),(57,'2022-10-01 06:11:50',13.2900,53.8600),(57,'2022-10-01 06:21:50',12.8300,53.5900),(57,'2022-10-01 06:31:50',13.6000,54.5000),(57,'2022-10-01 06:41:50',14.5900,55.2100),(57,'2022-10-01 06:51:50',15.5500,55.5000),(57,'2022-10-01 07:01:50',14.8900,54.5700),(57,'2022-10-01 07:11:50',15.8100,53.8500),(57,'2022-10-01 07:21:50',16.4100,53.0500),(57,'2022-10-01 07:31:50',17.4100,52.8500),(57,'2022-10-01 07:41:50',16.7700,53.1900),(57,'2022-10-01 07:51:50',15.8900,52.5900),(57,'2022-10-01 08:01:50',16.2200,52.1600),(57,'2022-10-01 08:21:50',16.0400,52.1800),(57,'2022-10-01 08:31:50',16.3200,51.9700),(57,'2022-10-01 08:41:50',16.9900,52.9400),(57,'2022-10-01 08:51:50',16.8800,52.5900),(57,'2022-10-01 09:01:50',16.2300,52.9500),(57,'2022-10-01 09:11:50',16.0900,53.8600),(57,'2022-10-01 09:31:50',15.8000,52.4400),(57,'2022-10-01 09:41:50',14.8600,53.4000),(57,'2022-10-01 09:51:50',14.0800,54.2900),(57,'2022-10-01 10:01:50',14.1300,53.6400),(57,'2022-10-01 10:11:50',13.4700,53.2500),(57,'2022-10-01 10:21:50',14.3200,54.2400),(57,'2022-10-01 10:31:50',13.4000,55.0700),(57,'2022-10-01 10:41:50',13.3300,54.3200),(57,'2022-10-01 10:51:50',13.8800,53.7700),(57,'2022-10-01 11:01:50',14.1900,53.3800),(57,'2022-10-01 11:11:50',15.0100,52.3800),(57,'2022-10-01 11:21:50',14.5700,52.4500),(57,'2022-10-01 11:41:50',13.0100,51.9400),(57,'2022-10-01 11:51:50',12.7900,51.4900),(57,'2022-10-01 12:01:50',12.2800,52.3100),(57,'2022-10-01 12:11:50',13.0000,51.5600),(63,'2022-09-30 12:21:50',0.0000,10.6800),(63,'2022-09-30 12:31:50',0.0000,9.9900),(63,'2022-09-30 12:41:50',0.0000,10.3800),(63,'2022-09-30 12:51:50',0.0000,11.3200),(63,'2022-09-30 13:01:50',0.0000,10.7900),(63,'2022-09-30 13:11:50',0.0000,11.5900),(63,'2022-09-30 13:21:50',0.0000,10.6900),(63,'2022-09-30 13:31:50',0.0000,10.3600),(63,'2022-09-30 13:41:50',0.0000,11.1100),(63,'2022-09-30 13:51:50',0.0000,10.1400),(63,'2022-09-30 14:01:50',0.0000,10.2600),(63,'2022-09-30 14:11:50',0.0000,10.5300),(63,'2022-09-30 14:21:50',0.0000,9.7600),(63,'2022-09-30 14:31:50',0.0000,10.2800),(63,'2022-09-30 14:41:50',0.0000,9.6600),(63,'2022-09-30 14:51:50',0.0000,10.3000),(63,'2022-09-30 15:01:50',0.0000,10.9300),(63,'2022-09-30 15:11:50',0.0000,10.2400),(63,'2022-09-30 15:21:50',0.0000,9.2800),(63,'2022-09-30 15:31:50',0.0000,8.2800),(63,'2022-09-30 15:41:50',0.0000,7.5900),(63,'2022-09-30 15:51:50',0.0000,8.1000),(63,'2022-09-30 16:01:50',0.0000,8.7700),(63,'2022-09-30 16:11:50',0.0000,8.1700),(63,'2022-09-30 16:21:50',0.0000,7.3200),(63,'2022-09-30 16:31:50',0.0000,6.5500),(63,'2022-09-30 16:41:50',0.0000,7.1000),(63,'2022-09-30 16:51:50',0.0000,6.3800),(63,'2022-09-30 17:01:50',0.0000,7.1900),(63,'2022-09-30 17:11:50',0.0000,7.5200),(63,'2022-09-30 17:21:50',0.0000,7.1200),(63,'2022-09-30 17:31:50',0.0000,7.3600),(63,'2022-09-30 17:51:50',0.0000,5.7500),(63,'2022-09-30 18:01:50',0.0000,5.3100),(63,'2022-09-30 18:11:50',0.0000,6.1700),(63,'2022-09-30 18:21:50',0.0000,7.1500),(63,'2022-09-30 18:31:50',0.0000,8.1200),(63,'2022-09-30 18:41:50',0.0000,8.6800),(63,'2022-09-30 18:51:50',0.0000,9.0800),(63,'2022-09-30 19:01:50',0.0000,10.0000),(63,'2022-09-30 19:11:50',0.0000,9.1700),(63,'2022-09-30 19:21:50',0.0000,10.0900),(63,'2022-09-30 19:31:50',0.0000,9.1700),(63,'2022-09-30 19:41:50',0.0000,9.0900),(63,'2022-09-30 19:51:50',0.0000,10.0400),(63,'2022-09-30 20:01:50',0.0000,10.8400),(63,'2022-09-30 20:11:50',0.0000,11.6300),(63,'2022-09-30 20:21:50',0.0000,12.5700),(63,'2022-09-30 20:31:50',0.0000,13.4300),(63,'2022-09-30 20:41:50',0.0000,12.9600),(63,'2022-09-30 20:51:50',0.0000,12.1400),(63,'2022-09-30 21:01:50',0.0000,11.1800),(63,'2022-09-30 21:11:50',0.0000,10.1800),(63,'2022-09-30 21:21:50',0.0000,9.7300),(63,'2022-09-30 21:31:50',0.0000,9.7200),(63,'2022-09-30 21:41:50',0.0000,8.9800),(63,'2022-09-30 21:51:50',0.0000,8.4900),(63,'2022-09-30 22:01:50',0.0000,9.3900),(63,'2022-09-30 22:11:50',0.0000,8.9200),(63,'2022-09-30 22:21:50',0.0000,8.2500),(63,'2022-09-30 22:31:50',0.0000,8.7000),(63,'2022-09-30 22:41:50',0.0000,7.8800),(63,'2022-09-30 22:51:50',0.0000,6.9700),(63,'2022-09-30 23:01:50',0.0000,6.7400),(63,'2022-09-30 23:11:50',0.0000,6.0000),(63,'2022-09-30 23:21:50',0.0000,5.0000),(63,'2022-09-30 23:41:50',0.0000,3.7400),(63,'2022-09-30 23:51:50',0.0000,2.8200),(63,'2022-10-01 00:01:50',0.0000,3.2100),(63,'2022-10-01 00:11:50',0.0000,3.4200),(63,'2022-10-01 00:21:50',0.0000,3.7100),(63,'2022-10-01 00:31:50',0.0000,3.5900),(63,'2022-10-01 00:41:50',0.0000,2.8400),(63,'2022-10-01 00:51:50',0.0000,2.5800),(63,'2022-10-01 01:01:50',0.0000,3.4700),(63,'2022-10-01 01:11:50',0.0000,2.5700),(63,'2022-10-01 01:21:50',0.0000,2.7100),(63,'2022-10-01 01:31:50',0.0000,3.5100),(63,'2022-10-01 01:41:50',0.0000,2.7400),(63,'2022-10-01 01:51:50',0.0000,2.1900),(63,'2022-10-01 02:01:50',0.0000,1.8700),(63,'2022-10-01 02:11:50',0.0000,0.9500),(63,'2022-10-01 02:21:50',0.0000,1.3100),(63,'2022-10-01 02:31:50',0.0000,0.9100),(63,'2022-10-01 02:41:50',0.0000,1.2200),(63,'2022-10-01 02:51:50',0.0000,1.4500),(63,'2022-10-01 03:01:50',0.0000,1.2600),(63,'2022-10-01 03:11:50',0.0000,1.7100),(63,'2022-10-01 03:21:50',0.0000,1.1400),(63,'2022-10-01 03:31:50',0.0000,0.7300),(63,'2022-10-01 03:41:50',0.0000,1.4600),(63,'2022-10-01 03:51:50',0.0000,1.7200),(63,'2022-10-01 04:01:50',0.0000,0.8000),(63,'2022-10-01 04:11:50',0.0000,0.3900),(63,'2022-10-01 04:21:50',0.0000,0.8600),(63,'2022-10-01 04:31:50',0.0000,1.5200),(63,'2022-10-01 04:41:50',0.0000,2.2800),(63,'2022-10-01 04:51:50',0.0000,2.5900),(63,'2022-10-01 05:11:50',0.0000,2.2800),(63,'2022-10-01 05:21:50',0.0000,1.3000),(63,'2022-10-01 05:41:50',0.0000,0.0000),(63,'2022-10-01 05:51:50',0.0000,0.6900),(63,'2022-10-01 06:01:50',0.0000,1.5000),(63,'2022-10-01 06:11:50',0.0000,2.3200),(63,'2022-10-01 06:21:50',0.0000,2.1500),(63,'2022-10-01 06:31:50',0.0000,3.0200),(63,'2022-10-01 06:51:50',0.0000,1.8400),(63,'2022-10-01 07:01:50',0.0000,1.1000),(63,'2022-10-01 07:11:50',0.0000,0.8900),(63,'2022-10-01 07:21:50',0.0000,0.0000),(63,'2022-10-01 07:31:50',0.0000,0.2100),(63,'2022-10-01 07:41:50',0.0000,1.1600),(63,'2022-10-01 07:51:50',0.0000,1.5200),(63,'2022-10-01 08:01:50',0.0000,2.0200),(63,'2022-10-01 08:11:50',0.0000,1.6400),(63,'2022-10-01 08:21:50',0.0000,0.7400),(63,'2022-10-01 08:31:50',0.0000,0.0000),(63,'2022-10-01 08:41:50',0.0000,0.1700),(63,'2022-10-01 08:51:50',0.0000,0.8700),(63,'2022-10-01 09:01:50',0.0000,1.0500),(63,'2022-10-01 09:11:50',0.0000,0.4600),(63,'2022-10-01 09:21:50',0.0000,0.0000),(63,'2022-10-01 09:31:50',0.0000,0.9600),(63,'2022-10-01 09:41:50',0.0000,1.6200),(63,'2022-10-01 09:51:50',0.0000,1.0300),(63,'2022-10-01 10:01:50',0.0000,1.9500),(63,'2022-10-01 10:11:50',0.0000,2.2100),(63,'2022-10-01 10:21:50',0.0000,2.4600),(63,'2022-10-01 10:31:50',0.0000,2.0100),(63,'2022-10-01 10:41:50',0.0000,2.1800),(63,'2022-10-01 10:51:50',0.0000,1.4000),(63,'2022-10-01 11:01:50',0.0000,0.9100),(63,'2022-10-01 11:11:50',0.0000,1.8600),(63,'2022-10-01 11:21:50',0.0000,1.3900),(63,'2022-10-01 11:31:50',0.0000,0.6700),(63,'2022-10-01 11:41:50',0.0000,1.4700),(63,'2022-10-01 12:01:50',0.0000,2.4400),(63,'2022-10-01 12:11:50',0.0000,3.3300);
/*!40000 ALTER TABLE `supla_temphumidity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_thermostat_log`
--

DROP TABLE IF EXISTS `supla_thermostat_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_thermostat_log` (
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:stringdatetime)',
  `on` tinyint(1) NOT NULL,
  `measured_temperature` decimal(5,2) NOT NULL,
  `preset_temperature` decimal(5,2) NOT NULL,
  PRIMARY KEY (`channel_id`,`date`)
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
  `limit_aid` int(11) NOT NULL DEFAULT '10',
  `limit_loc` int(11) NOT NULL DEFAULT '10',
  `limit_iodev` int(11) NOT NULL DEFAULT '100',
  `limit_client` int(11) NOT NULL DEFAULT '200',
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
  `limit_scene` int(11) NOT NULL DEFAULT '50',
  `api_rate_limit` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mqtt_broker_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `mqtt_broker_auth_password` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `limit_actions_per_schedule` int(11) NOT NULL DEFAULT '20',
  `preferences` varchar(4096) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{}',
  `limit_operations_per_scene` int(11) NOT NULL DEFAULT '20',
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
INSERT INTO `supla_user` VALUES (1,'0a2768ccc1916b1d1779826d1b055665','3b23a71d16732414c1c700f6234140e7eb5c68267eaab16635d1884c2a341987ff652c7f1bd0d33020be51fad85bd7971e7b5b779b03795419dfdb918a51bb9a0029e1ace149fb06d56a46d341fe1945ce1f8ddb3a17c44a7425ea046b488f0983938f0c','h4pkk6j14v4k8swo48s804w0oskwc0k','user@supla.org','$2y$13$FzCUjeh69igVW0m6lfSgyusRVE/bAiVLJgVezmRnVhnPtkm6BSew6',1,'2022-10-01 12:21:44',NULL,NULL,10,10,100,200,'Europe/Berlin',20,NULL,'2022-10-08 12:21:45','2022-10-08 12:21:45',20,10,1,1,NULL,NULL,50,20,NULL,NULL,50,NULL,0,NULL,20,'{}',20),(2,'571c87bec95789dfc6615675b8d7cd1a','c9612d70370946f16690d9e958d7441c5ba23f80af5eeb54d47fceec830031f8bb90e1e0e2a84057bc6ae01113dc61d3cf1cb1522863f82d9c54cc41cc469bba004e862d1519a637c1fd69d6f7394dc32516813148177bc53c668cefe21f5efd48dd63c1','los0lbb0qr4skc0ocwck0c80480scck','supler@supla.org','$2y$13$BV92vnbhcEC5fDt67o0a1.aSzmRWHCYA1D.r4yc2Q4UY7uQI36jF2',1,'2022-10-01 12:21:45',NULL,NULL,10,10,100,200,'Europe/Berlin',20,NULL,'2022-10-08 12:21:46','2022-10-08 12:21:46',20,10,1,1,NULL,NULL,50,20,NULL,NULL,50,NULL,0,NULL,20,'{}',20);
/*!40000 ALTER TABLE `supla_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_user_icons`
--

DROP TABLE IF EXISTS `supla_user_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_user_icons` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_user_icons`
--

LOCK TABLES `supla_user_icons` WRITE;
/*!40000 ALTER TABLE `supla_user_icons` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_user_icons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `supla_v_accessid_active`
--

DROP TABLE IF EXISTS `supla_v_accessid_active`;
/*!50001 DROP VIEW IF EXISTS `supla_v_accessid_active`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_accessid_active` AS SELECT
 1 AS `id`,
 1 AS `user_id`,
 1 AS `password`,
 1 AS `caption`,
 1 AS `enabled`,
 1 AS `active_from`,
 1 AS `active_to`,
 1 AS `active_hours`,
 1 AS `is_now_active`*/;
SET character_set_client = @saved_cs_client;

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
 1 AS `param4`,
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
 1 AS `protocol_version`,
 1 AS `flags`,
 1 AS `value`,
 1 AS `validity_time_sec`*/;
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
 1 AS `iodevice_id`,
 1 AS `hidden`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'supla'
--
/*!50003 DROP FUNCTION IF EXISTS `supla_current_weekday_hour` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` FUNCTION `supla_current_weekday_hour`(`user_timezone` VARCHAR(50)) RETURNS varchar(3) CHARSET latin1
BEGIN
            DECLARE current_weekday INT;
            DECLARE current_hour INT;
            DECLARE current_time_in_user_timezone DATETIME;
            SELECT COALESCE(CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', `user_timezone`), UTC_TIMESTAMP) INTO current_time_in_user_timezone;
            SELECT (WEEKDAY(current_time_in_user_timezone) + 1) INTO current_weekday;
            SELECT HOUR(current_time_in_user_timezone) INTO current_hour;
            RETURN CONCAT(current_weekday, current_hour);
        END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `supla_is_access_id_now_active` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` FUNCTION `supla_is_access_id_now_active`(`active_from` DATETIME, `active_to` DATETIME, `active_hours` VARCHAR(768), `user_timezone` VARCHAR(50)) RETURNS int(11)
BEGIN
            DECLARE res INT DEFAULT 1;

            IF `active_from` IS NOT NULL THEN
              SELECT (active_from <= UTC_TIMESTAMP) INTO res;
            END IF;

            IF res = 1 AND `active_to` IS NOT NULL THEN
              SELECT (active_to >= UTC_TIMESTAMP) INTO res;
            END IF;

            IF res = 1 AND `active_hours` IS NOT NULL THEN
              SELECT (`active_hours` LIKE CONCAT('%,', supla_current_weekday_hour(`user_timezone`) ,',%') COLLATE utf8mb4_unicode_ci) INTO res;
            END IF;

            RETURN res;
        END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `version_to_int` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` FUNCTION `version_to_int`(`version` VARCHAR(20)) RETURNS int(11)
    NO SQL
BEGIN
DECLARE result INT DEFAULT 0;
DECLARE n INT DEFAULT 0;
DECLARE m INT DEFAULT 0;
DECLARE dot_count INT DEFAULT 0;
DECLARE last_dot_pos INT DEFAULT 0;
DECLARE c VARCHAR(1);

WHILE n < LENGTH(version) DO
    SET n = n+1;
    SET c = SUBSTRING(version, n, 1);

    IF c <> '.' AND ( ASCII(c) < 48 OR ASCII(c) > 57 )
      THEN
         SET result = 0;
         RETURN 0;
      END IF;


   IF c = '.' THEN
     SET dot_count = dot_count+1;
     IF dot_count > 2 THEN
        SET result = 0;
        RETURN 0;
     END IF;
   END IF;

   IF c = '.' OR n = LENGTH(version) THEN

      SET m = n-last_dot_pos-1;

      IF c <> '.' THEN
        SET m = n-last_dot_pos;
        SET dot_count = dot_count+1;
      END IF;

          SET result = result + POWER(10, 9-dot_count*3) * SUBSTRING(version, last_dot_pos+1, m);

      SET last_dot_pos = n;
   END IF;

END WHILE;
RETURN result;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_channel` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_channel`(IN `_type` INT, IN `_func` INT, IN `_param1` INT, IN `_param2` INT, IN `_param3` INT,
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_client`(IN `_access_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8mb4,
IN `_reg_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11), IN `_user_id` INT(11),
IN `_auth_key` VARCHAR(64) CHARSET utf8, OUT `_id` INT(11))
    NO SQL
BEGIN

INSERT INTO `supla_client`(`access_id`, `guid`, `name`, `enabled`, `reg_ipv4`, `reg_date`, `last_access_ipv4`,
`last_access_date`,`software_version`, `protocol_version`, `user_id`, `auth_key`)
VALUES (_access_id, _guid, _name, 1, _reg_ipv4, UTC_TIMESTAMP(), _reg_ipv4, UTC_TIMESTAMP(), _software_version, _protocol_version,
_user_id, _auth_key);

SELECT LAST_INSERT_ID() INTO _id;

SELECT CONCAT('{"template": "new_client_app", "userId": ', _user_id, ', "data": {"clientAppId": ', _id, '}}') INTO @notification_data;
INSERT INTO `supla_email_notifications` (`body`, `headers`, `queue_name`, `created_at`, `available_at`)
VALUES(@notification_data, '[]', 'supla-server', NOW(), NOW());

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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_em_log_item`(IN `_channel_id` INT(11), IN `_phase1_fae` BIGINT, IN `_phase1_rae` BIGINT,
IN `_phase1_fre` BIGINT, IN `_phase1_rre` BIGINT, IN `_phase2_fae` BIGINT, IN `_phase2_rae` BIGINT, IN `_phase2_fre` BIGINT,
IN `_phase2_rre` BIGINT, IN `_phase3_fae` BIGINT, IN `_phase3_rae` BIGINT, IN `_phase3_fre` BIGINT, IN `_phase3_rre` BIGINT,
IN `_fae_balanced` BIGINT, IN `_rae_balanced` BIGINT)
    NO SQL
BEGIN

INSERT INTO `supla_em_log`(`channel_id`, `date`, `phase1_fae`, `phase1_rae`, `phase1_fre`, `phase1_rre`, `phase2_fae`,
`phase2_rae`, `phase2_fre`, `phase2_rre`, `phase3_fae`, `phase3_rae`, `phase3_fre`, `phase3_rre`, `fae_balanced`, `rae_balanced`)
VALUES (_channel_id, UTC_TIMESTAMP(), _phase1_fae, _phase1_rae, _phase1_fre, _phase1_rre,
_phase2_fae, _phase2_rae, _phase2_fre, _phase2_rre, _phase3_fae, _phase3_rae, _phase3_fre, _phase3_rre,
_fae_balanced, _rae_balanced);

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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_ic_log_item`(IN `_channel_id` INT(11), IN `_counter` BIGINT, IN `_calculated_value` BIGINT)
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_iodevice`(IN `_location_id` INT(11), IN `_user_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8mb4, IN `_reg_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20), IN `_protocol_version` INT(11), IN `_product_id` SMALLINT, IN `_manufacturer_id` SMALLINT, IN `_original_location_id` INT(11), IN `_auth_key` VARCHAR(64), IN `_flags` INT(11), OUT `_id` INT(11))
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
SELECT CONCAT('{"template": "new_io_device", "userId": ', _user_id, ', "data": {"ioDeviceId": ', _id, '}}') INTO @notification_data;
INSERT INTO `supla_email_notifications` (`body`, `headers`, `queue_name`, `created_at`, `available_at`)
VALUES(@notification_data, '[]', 'supla-server', NOW(), NOW());
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_temperature_log_item`(IN `_channel_id` INT(11), IN `_temperature` DECIMAL(8,4))
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_temphumidity_log_item`(IN `_channel_id` INT(11), IN `_temperature` DECIMAL(8,4),
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_thermostat_log_item`(IN `_channel_id` INT(11), IN `_measured_temperature` DECIMAL(5,2), IN `_preset_temperature` DECIMAL(5,2), IN `_on` TINYINT)
    NO SQL
BEGIN INSERT INTO `supla_thermostat_log`(`channel_id`, `date`, `measured_temperature`, `preset_temperature`, `on`) VALUES (_channel_id,UTC_TIMESTAMP(),_measured_temperature, _preset_temperature, _on); END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_get_device_firmware_url` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_get_device_firmware_url`(IN `in_device_id` INT, IN `in_platform` TINYINT, IN `in_fparam1` INT, IN `in_fparam2` INT, IN `in_fparam3` INT, IN `in_fparam4` INT, OUT `out_protocols` TINYINT, OUT `out_host` VARCHAR(100), OUT `out_port` INT, OUT `out_path` VARCHAR(100))
    NO SQL
BEGIN

SET @var_protocols = 0;
SET @var_host = '';
SET @var_port = 0;
SET @var_path = '';

SET @fparam1 = in_fparam1;
SET @fparam2 = in_fparam2;
SET @fparam3 = in_fparam3;
SET @fparam4 = in_fparam4;
SET @platform = in_platform;
SET @device_id = in_device_id;

SET @update_count = 0;
SELECT COUNT(*) INTO @update_count FROM `esp_update_log` WHERE `device_id` = @device_id AND `date`  + INTERVAL 30 MINUTE >= NOW();

IF @update_count = 0 THEN

SELECT u.`protocols`, u.`host`, u.`port`, u.`path` INTO @var_protocols, @var_host, @var_port, @var_path FROM supla_iodevice AS d, esp_update AS u WHERE d.id = @device_id AND u.`platform` = @platform AND u.`fparam1` = @fparam1 AND u.`fparam2` = @fparam2 AND @fparam3 = u.`fparam3` AND @fparam4 = u.`fparam4` AND u.`device_name` = d.name AND u.`latest_software_version` != d.`software_version` AND

(
version_to_int(d.`software_version`) = 0 OR
version_to_int(u.`latest_software_version`) = 0 OR
version_to_int(u.`latest_software_version`) > version_to_int(d.`software_version`)
)

AND ( u.`device_id` = 0 OR u.`device_id` = @device_id ) LIMIT 1;

END IF;

INSERT INTO `esp_update_log`(`date`, `device_id`, `platform`, `fparam1`, `fparam2`, `fparam3`, `fparam4`) VALUES (NOW(),in_device_id,in_platform,in_fparam1,in_fparam2,in_fparam3,in_fparam4);

SET out_protocols = @var_protocols;
SET out_host = @var_host;
SET out_port = @var_port;
SET out_path = @var_path;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_mqtt_broker_auth` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_mqtt_broker_auth`(IN `in_suid` VARCHAR(255) CHARSET utf8, IN `in_password` VARCHAR(255) CHARSET utf8)
    NO SQL
BEGIN
                SET @hashed_password = SHA2(in_password, 512);
            SELECT 1 FROM supla_user su
            LEFT JOIN supla_oauth_client_authorizations soca ON su.id = soca.user_id
            WHERE mqtt_broker_enabled = 1 AND short_unique_id = BINARY in_suid AND(
                su.mqtt_broker_auth_password = @hashed_password OR soca.mqtt_broker_auth_password = @hashed_password
            )
            LIMIT 1;
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_oauth_add_client_for_app`(IN `_random_id` VARCHAR(255) CHARSET utf8,
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

SELECT LAST_INSERT_ID() INTO _id;
     SELECT RELEASE_LOCK('oauth_add_client') INTO @lck;
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE
DEFINER=`root`@`%` PROCEDURE `supla_oauth_add_token_for_app`(IN `_user_id` INT(11), IN `_token` VARCHAR(255) CHARSET utf8,
IN `_expires_at` INT(11), IN `_access_id` INT(11), OUT `_id` INT(11))
    NO SQL
BEGIN

SET
@client_id = 0;

SELECT `id`
INTO @client_id
FROM `supla_oauth_clients`
WHERE `type` = 2 LIMIT 1;

IF
@client_id <> 0 AND EXISTS(SELECT 1 FROM `supla_accessid` WHERE `user_id` = _user_id AND `id` = _access_id) THEN

  INSERT INTO `supla_oauth_access_tokens`(`client_id`, `user_id`, `token`, `expires_at`, `scope`, `access_id`) VALUES
   (@client_id, _user_id, _token, _expires_at, 'channels_r channels_files', _access_id);
SELECT LAST_INSERT_ID()
INTO _id;

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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_on_channeladded`(IN `_device_id` INT, IN `_channel_id` INT)
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_on_newclient`(IN `_client_id` INT)
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_on_newdevice`(IN `_device_id` INT)
    MODIFIES SQL DATA
BEGIN
            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_channel_caption` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_channel_caption`(IN `_user_id` INT, IN `_channel_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4)
    NO SQL
UPDATE supla_dev_channel SET caption = _caption WHERE id = _channel_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_channel_function` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_channel_function`(IN `_user_id` INT, IN `_channel_id` INT, IN `_func` INT)
    NO SQL
UPDATE supla_dev_channel SET func = _func WHERE id = _channel_id AND user_id = _user_id AND type = 8000 ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_location_caption` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_location_caption`(IN `_user_id` INT, IN `_location_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4)
    NO SQL
UPDATE supla_location SET caption = _caption WHERE id = _location_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_registration_enabled` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_registration_enabled`(IN `user_id` INT, IN `iodevice_sec` INT, IN `client_sec` INT)
    NO SQL
BEGIN IF iodevice_sec >= 0 THEN SET @date = NULL; IF iodevice_sec > 0 THEN SET @date = DATE_ADD(UTC_TIMESTAMP, INTERVAL iodevice_sec SECOND); END IF; UPDATE supla_user SET iodevice_reg_enabled = @date WHERE id = user_id; END IF; IF client_sec >= 0 THEN SET @date = NULL; IF client_sec > 0 THEN SET @date = DATE_ADD(UTC_TIMESTAMP, INTERVAL client_sec SECOND); END IF; UPDATE supla_user SET client_reg_enabled = @date WHERE id = user_id; END IF; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_amazon_alexa` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_amazon_alexa`(IN `_access_token` VARCHAR(1024) CHARSET utf8, IN `_refresh_token` VARCHAR(1024) CHARSET utf8, IN `_expires_in` INT, IN `_user_id` INT)
    NO SQL
BEGIN UPDATE supla_amazon_alexa SET `access_token` = _access_token, `refresh_token` = _refresh_token, `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _expires_in second) WHERE `user_id` = _user_id; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_flags` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_channel_flags`(IN `_channel_id` INT, IN `_user_id` INT, IN `_flags` INT)
    NO SQL
UPDATE supla_dev_channel SET flags = IFNULL(flags, 0) | IFNULL(_flags, 0) WHERE id = _channel_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_params` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_channel_params`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_param1` INT,
    IN `_param2` INT,
    IN `_param3` INT,
    IN `_param4` INT
)
    NO SQL
BEGIN
UPDATE
    supla_dev_channel
SET param1 = _param1,
    param2 = _param2,
    param3 = _param3,
    param4 = _param4
WHERE id = _id
  AND user_id = _user_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_properties` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_channel_properties`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_properties` VARCHAR(2048)
)
    NO SQL
BEGIN
    UPDATE supla_dev_channel SET properties = _properties WHERE id = _id AND user_id = _user_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_value` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE
DEFINER=`root`@`%` PROCEDURE `supla_update_channel_value`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_value` VARBINARY(8),
    IN `_validity_time_sec` INT
)
    NO SQL
BEGIN
    IF
_validity_time_sec > 0 THEN
        SET @valid_to = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _validity_time_sec SECOND);

INSERT INTO `supla_dev_channel_value` (`channel_id`, `user_id`, `update_time`, `valid_to`, `value`)
VALUES (_id, _user_id, UTC_TIMESTAMP(), @valid_to, _value) ON DUPLICATE KEY
UPDATE `value` = _value, update_time = UTC_TIMESTAMP(), valid_to = @valid_to;

ELSE
DELETE
FROM `supla_dev_channel_value`
WHERE `channel_id` = _id;
END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_client` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_client`(IN `_access_id` INT(11), IN `_name` VARCHAR(100) CHARSET utf8mb4, IN `_last_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11), IN `_auth_key` VARCHAR(64) CHARSET utf8, IN `_id` INT(11))
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_google_home`(IN `_access_token` VARCHAR(255) CHARSET utf8, IN `_user_id` INT)
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
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE
DEFINER=`root`@`%` PROCEDURE `supla_update_iodevice`(IN `_name` VARCHAR(100) CHARSET utf8mb4, IN `_last_ipv4` INT(10) UNSIGNED,
  IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11), IN `_original_location_id` INT(11),
  IN `_auth_key` VARCHAR(64) CHARSET utf8, IN `_id` INT(11), IN `_flags` INT(11))
    NO SQL
BEGIN
UPDATE `supla_iodevice`
SET `name`             = _name,
    `last_connected`   = UTC_TIMESTAMP(),
    `last_ipv4`        = _last_ipv4,
    `software_version` = _software_version,
    `protocol_version` = _protocol_version,
original_location_id = _original_location_id,
`flags` = IFNULL(`flags`, 0) | IFNULL(_flags, 0) WHERE `id` = _id;
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
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_state_webhook` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_state_webhook`(IN `_access_token` VARCHAR(255) CHARSET utf8, IN `_refresh_token` VARCHAR(255) CHARSET utf8, IN `_expires_in` INT, IN `_user_id` INT)
    NO SQL
BEGIN UPDATE supla_state_webhooks SET `access_token` = _access_token, `refresh_token` = _refresh_token, `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _expires_in second) WHERE `user_id` = _user_id; END ;;
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
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_client_channel` AS select `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`,ifnull(`c`.`param1`,0) AS `param1`,ifnull(`c`.`param2`,0) AS `param2`,`c`.`caption` AS `caption`,ifnull(`c`.`param3`,0) AS `param3`,ifnull(`c`.`param4`,0) AS `param4`,`c`.`text_param1` AS `text_param1`,`c`.`text_param2` AS `text_param2`,`c`.`text_param3` AS `text_param3`,ifnull(`d`.`manufacturer_id`,0) AS `manufacturer_id`,ifnull(`d`.`product_id`,0) AS `product_id`,ifnull(`c`.`user_icon_id`,0) AS `user_icon_id`,`c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,`c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,(case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end) AS `location_id`,ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version`,(((ifnull(`c`.`flags`,0) | (ifnull(`em_subc`.`flags`,0) & 0x020000)) | (ifnull(`em_subc`.`flags`,0) & 0x040000)) | (ifnull(`em_subc`.`flags`,0) & 0x080000)) AS `flags`,`v`.`value` AS `value`,time_to_sec(timediff(`v`.`valid_to`,utc_timestamp())) AS `validity_time_sec` from (((((((`supla_dev_channel` `c` join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) join `supla_location` `l` on((`l`.`id` = (case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end)))) join `supla_rel_aidloc` `r` on((`r`.`location_id` = `l`.`id`))) join `supla_accessid` `a` on((`a`.`id` = `r`.`access_id`))) join `supla_client` `cl` on((`cl`.`access_id` = `r`.`access_id`))) left join `supla_dev_channel_value` `v` on(((`c`.`id` = `v`.`channel_id`) and (`v`.`valid_to` >= utc_timestamp())))) left join `supla_dev_channel` `em_subc` on(((`em_subc`.`user_id` = `c`.`user_id`) and (`em_subc`.`type` = 5000) and ((`c`.`func` = 130) or (`c`.`func` = 140)) and (`c`.`param1` = `em_subc`.`id`)))) where ((((`c`.`func` is not null) and (`c`.`func` <> 0)) or (`c`.`type` = 8000)) and (ifnull(`c`.`hidden`,0) = 0) and (`d`.`enabled` = 1) and (`l`.`enabled` = 1) and (`a`.`enabled` = 1)) */;
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
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `supla_v_user_channel_group` AS select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,`g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,`rel`.`channel_id` AS `channel_id`,`c`.`iodevice_id` AS `iodevice_id`,ifnull(`g`.`hidden`,0) AS `hidden` from ((((`supla_dev_channel_group` `g` join `supla_location` `l` on((`l`.`id` = `g`.`location_id`))) join `supla_rel_cg` `rel` on((`rel`.`group_id` = `g`.`id`))) join `supla_dev_channel` `c` on((`c`.`id` = `rel`.`channel_id`))) join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) where ((`g`.`func` is not null) and (`g`.`func` <> 0) and (`l`.`enabled` = 1) and (`d`.`enabled` = 1)) */;
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

-- Dump completed on 2022-10-01 14:22:26
