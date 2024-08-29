-- mysqldump -h 127.0.0.1 --routines -u root -p supla > test_dump_v23.12.sql
--
-- MariaDB dump 10.19  Distrib 10.6.11-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: supla
-- ------------------------------------------------------
-- Server version	5.7.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
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
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('SuplaBundle\\Migrations\\Migration\\Version20170101000000','2023-12-19 09:52:14',747),('SuplaBundle\\Migrations\\Migration\\Version20170414101854','2023-12-19 09:52:15',1228),('SuplaBundle\\Migrations\\Migration\\Version20170612204116','2023-12-19 09:52:16',45),('SuplaBundle\\Migrations\\Migration\\Version20170818114139','2023-12-19 09:52:16',1038),('SuplaBundle\\Migrations\\Migration\\Version20171013140904','2023-12-19 09:52:18',648),('SuplaBundle\\Migrations\\Migration\\Version20171208222022','2023-12-19 09:52:18',26),('SuplaBundle\\Migrations\\Migration\\Version20171210105120','2023-12-19 09:52:18',4),('SuplaBundle\\Migrations\\Migration\\Version20180108224520','2023-12-19 09:52:18',40),('SuplaBundle\\Migrations\\Migration\\Version20180113234138','2023-12-19 09:52:18',64),('SuplaBundle\\Migrations\\Migration\\Version20180116184415','2023-12-19 09:52:18',49),('SuplaBundle\\Migrations\\Migration\\Version20180203231115','2023-12-19 09:52:18',132),('SuplaBundle\\Migrations\\Migration\\Version20180208145738','2023-12-19 09:52:19',128),('SuplaBundle\\Migrations\\Migration\\Version20180224184251','2023-12-19 09:52:19',4),('SuplaBundle\\Migrations\\Migration\\Version20180324222844','2023-12-19 09:52:19',50),('SuplaBundle\\Migrations\\Migration\\Version20180326134725','2023-12-19 09:52:19',53),('SuplaBundle\\Migrations\\Migration\\Version20180403175932','2023-12-19 09:52:19',136),('SuplaBundle\\Migrations\\Migration\\Version20180403203101','2023-12-19 09:52:19',44),('SuplaBundle\\Migrations\\Migration\\Version20180403211558','2023-12-19 09:52:19',139),('SuplaBundle\\Migrations\\Migration\\Version20180411202101','2023-12-19 09:52:19',8),('SuplaBundle\\Migrations\\Migration\\Version20180411203913','2023-12-19 09:52:19',56),('SuplaBundle\\Migrations\\Migration\\Version20180416201401','2023-12-19 09:52:19',3),('SuplaBundle\\Migrations\\Migration\\Version20180423121539','2023-12-19 09:52:19',45),('SuplaBundle\\Migrations\\Migration\\Version20180507095139','2023-12-19 09:52:19',71),('SuplaBundle\\Migrations\\Migration\\Version20180518131234','2023-12-19 09:52:19',317),('SuplaBundle\\Migrations\\Migration\\Version20180707221458','2023-12-19 09:52:20',1),('SuplaBundle\\Migrations\\Migration\\Version20180717094843','2023-12-19 09:52:20',156),('SuplaBundle\\Migrations\\Migration\\Version20180723132652','2023-12-19 09:52:20',29),('SuplaBundle\\Migrations\\Migration\\Version20180807083217','2023-12-19 09:52:20',439),('SuplaBundle\\Migrations\\Migration\\Version20180812205513','2023-12-19 09:52:20',316),('SuplaBundle\\Migrations\\Migration\\Version20180814155501','2023-12-19 09:52:21',100),('SuplaBundle\\Migrations\\Migration\\Version20180914222230','2023-12-19 09:52:21',165),('SuplaBundle\\Migrations\\Migration\\Version20181001221229','2023-12-19 09:52:21',332),('SuplaBundle\\Migrations\\Migration\\Version20181007112610','2023-12-19 09:52:21',40),('SuplaBundle\\Migrations\\Migration\\Version20181019115859','2023-12-19 09:52:21',256),('SuplaBundle\\Migrations\\Migration\\Version20181024164957','2023-12-19 09:52:21',553),('SuplaBundle\\Migrations\\Migration\\Version20181025171850','2023-12-19 09:52:22',180),('SuplaBundle\\Migrations\\Migration\\Version20181026171557','2023-12-19 09:52:22',94),('SuplaBundle\\Migrations\\Migration\\Version20181105144611','2023-12-19 09:52:22',1),('SuplaBundle\\Migrations\\Migration\\Version20181126225634','2023-12-19 09:52:22',107),('SuplaBundle\\Migrations\\Migration\\Version20181129170610','2023-12-19 09:52:22',76),('SuplaBundle\\Migrations\\Migration\\Version20181129195431','2023-12-19 09:52:23',225),('SuplaBundle\\Migrations\\Migration\\Version20181129231132','2023-12-19 09:52:23',9),('SuplaBundle\\Migrations\\Migration\\Version20181204174603','2023-12-19 09:52:23',87),('SuplaBundle\\Migrations\\Migration\\Version20181205092324','2023-12-19 09:52:23',36),('SuplaBundle\\Migrations\\Migration\\Version20181222001450','2023-12-19 09:52:23',55),('SuplaBundle\\Migrations\\Migration\\Version20190105130410','2023-12-19 09:52:23',1),('SuplaBundle\\Migrations\\Migration\\Version20190117075805','2023-12-19 09:52:23',48),('SuplaBundle\\Migrations\\Migration\\Version20190219184847','2023-12-19 09:52:23',28),('SuplaBundle\\Migrations\\Migration\\Version20190325215115','2023-12-19 09:52:23',1),('SuplaBundle\\Migrations\\Migration\\Version20190401151822','2023-12-19 09:52:23',1),('SuplaBundle\\Migrations\\Migration\\Version20190720215803','2023-12-19 09:52:23',53),('SuplaBundle\\Migrations\\Migration\\Version20190813232026','2023-12-19 09:52:23',52),('SuplaBundle\\Migrations\\Migration\\Version20190815154016','2023-12-19 09:52:23',988),('SuplaBundle\\Migrations\\Migration\\Version20191226160845','2023-12-19 09:52:24',60),('SuplaBundle\\Migrations\\Migration\\Version20200108201101','2023-12-19 09:52:24',5),('SuplaBundle\\Migrations\\Migration\\Version20200123235701','2023-12-19 09:52:24',1),('SuplaBundle\\Migrations\\Migration\\Version20200124084227','2023-12-19 09:52:24',17),('SuplaBundle\\Migrations\\Migration\\Version20200204170901','2023-12-19 09:52:24',17),('SuplaBundle\\Migrations\\Migration\\Version20200210145902','2023-12-19 09:52:24',1),('SuplaBundle\\Migrations\\Migration\\Version20200229122103','2023-12-19 09:52:24',493),('SuplaBundle\\Migrations\\Migration\\Version20200322123636','2023-12-19 09:52:25',57),('SuplaBundle\\Migrations\\Migration\\Version20200412183701','2023-12-19 09:52:25',1),('SuplaBundle\\Migrations\\Migration\\Version20200414213205','2023-12-19 09:52:25',5),('SuplaBundle\\Migrations\\Migration\\Version20200416225304','2023-12-19 09:52:25',1),('SuplaBundle\\Migrations\\Migration\\Version20200419190150','2023-12-19 09:52:25',2),('SuplaBundle\\Migrations\\Migration\\Version20200430113342','2023-12-19 09:52:25',552),('SuplaBundle\\Migrations\\Migration\\Version20200514132030','2023-12-19 09:52:25',25),('SuplaBundle\\Migrations\\Migration\\Version20200515102311','2023-12-19 09:52:25',132),('SuplaBundle\\Migrations\\Migration\\Version20200518171230','2023-12-19 09:52:26',38),('SuplaBundle\\Migrations\\Migration\\Version20200724155001','2023-12-19 09:52:26',1),('SuplaBundle\\Migrations\\Migration\\Version20200807131101','2023-12-19 09:52:26',24),('SuplaBundle\\Migrations\\Migration\\Version20200811141801','2023-12-19 09:52:26',19),('SuplaBundle\\Migrations\\Migration\\Version20200813113801','2023-12-19 09:52:26',161),('SuplaBundle\\Migrations\\Migration\\Version20200813133501','2023-12-19 09:52:26',4),('SuplaBundle\\Migrations\\Migration\\Version20200911231401','2023-12-19 09:52:26',1),('SuplaBundle\\Migrations\\Migration\\Version20201113112233','2023-12-19 09:52:26',64),('SuplaBundle\\Migrations\\Migration\\Version20201213133718','2023-12-19 09:52:26',124),('SuplaBundle\\Migrations\\Migration\\Version20201214102230','2023-12-19 09:52:26',172),('SuplaBundle\\Migrations\\Migration\\Version20210105164727','2023-12-19 09:52:26',61),('SuplaBundle\\Migrations\\Migration\\Version20210118124714','2023-12-19 09:52:26',304),('SuplaBundle\\Migrations\\Migration\\Version20210228201414','2023-12-19 09:52:27',2),('SuplaBundle\\Migrations\\Migration\\Version20210323095216','2023-12-19 09:52:27',75),('SuplaBundle\\Migrations\\Migration\\Version20210419201821','2023-12-19 09:52:27',77),('SuplaBundle\\Migrations\\Migration\\Version20210525104812','2023-12-19 09:52:27',68),('SuplaBundle\\Migrations\\Migration\\Version20210915221319','2023-12-19 09:52:27',140),('SuplaBundle\\Migrations\\Migration\\Version20210917203710','2023-12-19 09:52:27',1),('SuplaBundle\\Migrations\\Migration\\Version20211005074509','2023-12-19 09:52:27',56),('SuplaBundle\\Migrations\\Migration\\Version20211108120835','2023-12-19 09:52:27',1),('SuplaBundle\\Migrations\\Migration\\Version20211123193415','2023-12-19 09:52:27',90),('SuplaBundle\\Migrations\\Migration\\Version20211205215406','2023-12-19 09:52:27',51),('SuplaBundle\\Migrations\\Migration\\Version20211218174444','2023-12-19 09:52:27',1),('SuplaBundle\\Migrations\\Migration\\Version20220208164512','2023-12-19 09:52:27',3),('SuplaBundle\\Migrations\\Migration\\Version20220222110707','2023-12-19 09:52:27',1),('SuplaBundle\\Migrations\\Migration\\Version20220309061811','2023-12-19 09:52:27',37),('SuplaBundle\\Migrations\\Migration\\Version20220309061812','2023-12-19 09:52:27',5),('SuplaBundle\\Migrations\\Migration\\Version20220404100406','2023-12-19 09:52:27',1),('SuplaBundle\\Migrations\\Migration\\Version20220718203129','2023-12-19 09:52:27',53),('SuplaBundle\\Migrations\\Migration\\Version20220719210858','2023-12-19 09:52:27',45),('SuplaBundle\\Migrations\\Migration\\Version20220929090847','2023-12-19 09:52:27',93),('SuplaBundle\\Migrations\\Migration\\Version20221005003914','2023-12-19 09:52:27',52),('SuplaBundle\\Migrations\\Migration\\Version20221010103958','2023-12-19 09:52:28',25),('SuplaBundle\\Migrations\\Migration\\Version20221020225729','2023-12-19 09:52:28',177),('SuplaBundle\\Migrations\\Migration\\Version20221124222346','2023-12-19 09:52:28',239),('SuplaBundle\\Migrations\\Migration\\Version20221219113615','2023-12-19 09:52:28',4),('SuplaBundle\\Migrations\\Migration\\Version20230322172549','2023-12-19 09:52:28',145),('SuplaBundle\\Migrations\\Migration\\Version20230426212833','2023-12-19 09:52:28',5),('SuplaBundle\\Migrations\\Migration\\Version20230427200016','2023-12-19 09:52:28',283),('SuplaBundle\\Migrations\\Migration\\Version20230427222824','2023-12-19 09:52:28',1133),('SuplaBundle\\Migrations\\Migration\\Version20230529143433','2023-12-19 09:52:30',1),('SuplaBundle\\Migrations\\Migration\\Version20230604004315','2023-12-19 09:52:30',60),('SuplaBundle\\Migrations\\Migration\\Version20230612185931','2023-12-19 09:52:30',39),('SuplaBundle\\Migrations\\Migration\\Version20230714142433','2023-12-19 09:52:30',137),('SuplaBundle\\Migrations\\Migration\\Version20230815145146','2023-12-19 09:52:30',213),('SuplaBundle\\Migrations\\Migration\\Version20230926065848','2023-12-19 09:52:30',1),('SuplaBundle\\Migrations\\Migration\\Version20231103121340','2023-12-19 09:52:30',1);
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
INSERT INTO `supla_accessid` VALUES (1,1,'0b38c777','Access Identifier #2',1,NULL,NULL,NULL),(2,2,'872dd24b','Access Identifier #2',1,NULL,NULL,NULL),(3,1,'54b4020f','Wspólny',1,NULL,NULL,NULL),(4,1,'2b5d3cc1','Dzieci',1,NULL,NULL,NULL),(5,2,'fd50649e','Supler Access ID',1,NULL,NULL,NULL);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_audit`
--

LOCK TABLES `supla_audit` WRITE;
/*!40000 ALTER TABLE `supla_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_auto_gate_closing`
--

DROP TABLE IF EXISTS `supla_auto_gate_closing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_auto_gate_closing` (
  `channel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `active_from` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `active_to` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `active_hours` varchar(768) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_time_open` int(11) NOT NULL,
  `seconds_open` int(11) DEFAULT NULL,
  `closing_attempt` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `last_seen_open` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  PRIMARY KEY (`channel_id`),
  KEY `IDX_E176CB9FA76ED395` (`user_id`),
  CONSTRAINT `FK_E176CB9F72F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E176CB9FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_auto_gate_closing`
--

LOCK TABLES `supla_auto_gate_closing` WRITE;
/*!40000 ALTER TABLE `supla_auto_gate_closing` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_auto_gate_closing` ENABLE KEYS */;
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
  `push_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `push_token_update_time` datetime DEFAULT NULL,
  `platform` tinyint(3) unsigned DEFAULT NULL COMMENT '(DC2Type:tinyint)',
  `app_id` int(11) NOT NULL DEFAULT '0',
  `devel_env` tinyint(1) NOT NULL DEFAULT '0',
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
INSERT INTO `supla_client` VALUES (1,3,'6749522','HTC One M8',0,1193724777,'2023-12-10 10:37:28',3979545796,'2023-12-15 17:36:43','1.11',10,1,NULL,NULL,NULL,NULL,NULL,NULL,0,0),(2,NULL,'4285423','iPhone 6s',1,3599046711,'2023-11-19 00:47:38',1794806108,'2023-12-15 05:01:21','1.11',96,1,NULL,NULL,NULL,NULL,NULL,NULL,0,0),(3,3,'4337939','Nokia 3310',0,597938716,'2023-10-30 21:04:39',4045585677,'2023-12-13 16:42:29','1.89',68,1,NULL,NULL,NULL,NULL,NULL,NULL,0,0),(4,4,'2862132','Samsung Galaxy Tab S2',1,1102210216,'2023-12-08 20:35:36',3917861746,'2023-12-15 14:39:02','1.69',37,1,NULL,NULL,NULL,NULL,NULL,NULL,0,0),(5,NULL,'1795652','Apple iPad',1,1996410513,'2023-11-10 03:24:37',1973941428,'2023-12-16 04:04:27','1.85',23,1,NULL,NULL,NULL,NULL,NULL,NULL,0,0),(6,5,'1375465','SUPLER PHONE',0,2882683218,'2023-11-07 22:17:47',2277206697,'2023-12-12 13:25:06','1.80',29,2,NULL,NULL,NULL,NULL,NULL,NULL,0,0);
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
  `user_config` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=435 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_dev_channel`
--

LOCK TABLES `supla_dev_channel` WRITE;
/*!40000 ALTER TABLE `supla_dev_channel` DISABLE KEYS */;
INSERT INTO `supla_dev_channel`
VALUES (1, 1, 1, 0, 'Nemo qui dolorem qui❤️.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (2, 1, 1, 1, 'Ipsum maxime nemo dolorem animi.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (3, 1, 1, 2, 'Commodi tempora libero perferendis.', 11000, 700, NULL, 1, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\"],\"disablesLocalOperation\":[],\"relatedChannelId\":1,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\"]}'),
       (4, 2, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (5, 2, 1, 1, 'Et occaecati aut.', 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (6, 2, 1, 2, 'Dolorem eos quia.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (7, 2, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (8, 2, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (9, 2, 1, 5, 'Quibusdam qui impedit et.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (10, 2, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (11, 2, 1, 7, 'Corporis qui officiis.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X5\",\"TOGGLE_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X5\",\"TOGGLE_X4\"]}'),
       (12, 2, 1, 8, NULL, 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (13, 2, 1, 9, 'Unde tempora et a sed.', 5010, 330, NULL, 0, 55, 0, 'PLN', NULL, NULL, 0, 0, NULL, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":\"❤️\",\"initialValue\":100.33,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (14, 2, 1, 10, 'Impedit quod ut at.', 11000, 700, NULL, 8, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X3\",\"TOGGLE_X5\"],\"disablesLocalOperation\":[],\"relatedChannelId\":8,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X3\",\"TOGGLE_X5\"]}'),
       (15, 3, 1, 0, NULL, 4010, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (16, 3, 1, 1, NULL, 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (17, 4, 1, 0, 'Molestiae autem aut suscipit id.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (18, 4, 1, 1, 'Magni quas ea.', 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (19, 4, 1, 2, 'Quis commodi.', 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (20, 4, 1, 3, NULL, 1000, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (21, 4, 1, 4, 'Sapiente qui et.', 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"invertedLogic\":false}', 0,
        NULL),
       (22, 4, 1, 5, NULL, 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (23, 4, 1, 6, 'Et vel.', 1000, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (24, 4, 1, 7, 'Iure nihil voluptas quisquam.', 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false}', 0, NULL),
       (25, 4, 1, 8, NULL, 1000, 235, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (26, 4, 1, 9, 'Nisi voluptatem labore.', 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false}', 0, NULL),
       (27, 4, 1, 10, NULL, 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (28, 4, 1, 11, NULL, 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (29, 4, 1, 12, 'Aperiam ex illo aliquid.', 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (30, 4, 1, 13, 'Sint quia quam.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (31, 4, 1, 14, 'Autem quis suscipit sapiente.', 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false}', 0, NULL),
       (32, 4, 1, 15, 'Expedita quia dolorem cupiditate assumenda.', 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (33, 4, 1, 16, NULL, 1010, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (34, 4, 1, 17, NULL, 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false}', 0, NULL),
       (35, 4, 1, 18, NULL, 1010, 235, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (36, 4, 1, 19, NULL, 1010, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"invertedLogic\":false}', 0, NULL),
       (37, 4, 1, 20, NULL, 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (38, 4, 1, 21, NULL, 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (39, 4, 1, 22, NULL, 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (40, 4, 1, 23, 'Quam consequatur eos.', 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (41, 4, 1, 24, 'Id porro.', 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (42, 4, 1, 25, NULL, 2000, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (43, 4, 1, 26, NULL, 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (44, 4, 1, 27, NULL, 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (45, 4, 1, 28, NULL, 2010, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (46, 4, 1, 29, 'Itaque occaecati itaque perferendis.', 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (47, 4, 1, 30, 'Commodi quae et.', 2010, 130, NULL, 78, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (48, 4, 1, 31, 'Et ipsum laudantium.', 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (49, 4, 1, 32, 'Quidem reiciendis ratione nulla.', 2010, 300, NULL, 250, 80, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null,\"relayTimeS\":0,\"timeSettingAvailable\":true}',
        0, NULL),
       (50, 4, 1, 33, NULL, 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (51, 4, 1, 34, NULL, 2020, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (52, 4, 1, 35, 'Et perspiciatis.', 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (53, 4, 1, 36, 'Ipsa saepe.', 2020, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (54, 4, 1, 37, NULL, 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (55, 4, 1, 38, 'Maiores veniam asperiores adipisci.', 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (56, 4, 1, 39, NULL, 2020, 110, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (57, 4, 1, 40, 'Itaque aut.', 2020, 115, NULL, 120, 0, 100, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingTimeS\":12,\"closingTimeS\":10,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (58, 4, 1, 41, 'Enim voluptatem quas.', 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null,\"relayTimeS\":0,\"timeSettingAvailable\":true}',
        0, NULL),
       (59, 4, 1, 42, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (60, 4, 1, 43, NULL, 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (61, 4, 1, 44, NULL, 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (62, 4, 1, 45, NULL, 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (63, 4, 1, 46, NULL, 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (64, 4, 1, 47, 'Laudantium labore ut.', 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (65, 4, 1, 48, 'Et officiis reiciendis distinctio.', 3034, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (66, 4, 1, 49, 'Distinctio est.', 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"humidityAdjustment\":0}', 0,
        NULL),
       (67, 4, 1, 50, NULL, 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (68, 4, 1, 51, 'At et fugit.', 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (69, 4, 1, 52, 'Consectetur consequatur reprehenderit illo ullam.', 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{}', 0, NULL),
       (70, 4, 1, 53, 'Necessitatibus est deleniti.', 3048, 270, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (71, 4, 1, 54, NULL, 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (72, 4, 1, 55, NULL, 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (73, 4, 1, 56, NULL, 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (74, 4, 1, 57, NULL, 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (75, 4, 1, 58, 'Aut excepturi debitis.', 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (76, 4, 1, 59, NULL, 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"currency\":null,\"resetCountersAvailable\":false,\"countersAvailable\":[\"forwardReactiveEnergy\",\"reverseReactiveEnergy\",\"reverseActiveEnergy\",\"reverseActiveEnergyBalanced\",\"forwardActiveEnergyBalanced\"],\"electricityMeterInitialValues\":{},\"addToHistory\":false,\"lowerVoltageThreshold\":null,\"upperVoltageThreshold\":null,\"disabledPhases\":[],\"enabledPhases\":[1,2,3],\"availablePhases\":[1,2,3],\"relatedChannelId\":null}',
        0,
        '{\"countersAvailable\":[\"forwardReactiveEnergy\",\"reverseReactiveEnergy\",\"reverseActiveEnergy\",\"reverseActiveEnergyBalanced\",\"forwardActiveEnergyBalanced\"]}'),
       (77, 4, 1, 60, NULL, 5010, 315, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (78, 4, 1, 61, 'Repudiandae necessitatibus totam.', 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 47, NULL,
        '{\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (79, 4, 1, 62, 'Voluptatem veniam.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (80, 4, 1, 63, 'Laborum et in eos ipsam.', 5010, 340, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 49, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (81, 4, 1, 64, 'Sint quo id.', 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (82, 4, 1, 65, 'Sit asperiores perferendis voluptatem.', 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0,
        NULL),
       (83, 4, 1, 66, 'Fugiat ratione quibusdam ea ipsa.', 6100, 420, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"waitingForConfigInit\":true}', 0, NULL),
       (84, 4, 1, 67, 'Voluptas deserunt molestiae quos.', 6100, 422, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"waitingForConfigInit\":true}', 0, NULL),
       (85, 4, 1, 68, 'Id neque et.', 6100, 423, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (86, 4, 1, 69, 'Repellendus suscipit dolore temporibus soluta.', 6100, 424, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{}', 0, NULL),
       (87, 4, 1, 70, NULL, 6100, 425, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"waitingForConfigInit\":true}', 0, NULL),
       (88, 4, 1, 71, 'Officiis fugit ipsa.', 6100, 426, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"waitingForConfigInit\":true}', 0, NULL),
       (89, 4, 1, 72, 'Dolorum nostrum esse perferendis.', 7000, 500, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (90, 4, 1, 73, NULL, 7010, 510, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (91, 4, 1, 74, NULL, 9000, 520, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"initialValue\":0,\"impulsesPerUnit\":0,\"unitPrefix\":null,\"unitSuffix\":null,\"precision\":0,\"storeMeasurementHistory\":false,\"chartType\":0,\"chartDataSourceType\":0,\"interpolateMeasurements\":false}',
        0, NULL),
       (92, 4, 1, 75, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TURN_OFF\",\"SHORT_PRESS_X3\",\"TOGGLE_X4\",\"SHORT_PRESS_X1\",\"TOGGLE_X1\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TURN_OFF\",\"SHORT_PRESS_X3\",\"TOGGLE_X4\",\"SHORT_PRESS_X1\",\"TOGGLE_X1\"]}'),
       (93, 4, 1, 76, NULL, 12000, 810, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"sectionsCount\":0,\"regenerationTimeStart\":0}', 0, NULL),
       (94, 4, 1, 77, NULL, 12000, 800, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"sectionsCount\":0,\"regenerationTimeStart\":0}', 0, NULL),
       (95, 5, 1, 0, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (96, 5, 1, 1, NULL, 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (97, 5, 1, 2, 'Hic enim omnis.', 6100, 420, 1179648, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"subfunction\":\"HEAT\",\"mainThermometerChannelNo\":1,\"auxThermometerChannelNo\":null,\"usedAlgorithm\":\"ON_OFF_SETPOINT_MIDDLE\",\"temperatures\":{\"freezeProtection\":1000,\"heatProtection\":3300,\"histeresis\":200,\"auxMinSetpoint\":550,\"auxMaxSetpoint\":4000},\"weeklySchedule\":{\"programSettings\":{\"1\":{\"mode\":\"HEAT\",\"setpointTemperatureHeat\":2400,\"setpointTemperatureCool\":0},\"2\":{\"mode\":\"HEAT\",\"setpointTemperatureHeat\":2100,\"setpointTemperatureCool\":0},\"3\":{\"mode\":\"HEAT\",\"setpointTemperatureHeat\":1800,\"setpointTemperatureCool\":0},\"4\":{\"mode\":\"HEAT\",\"setpointTemperatureHeat\":2800,\"setpointTemperatureCool\":0}},\"quarters\":[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,0,0,0,0,0,0]},\"altWeeklySchedule\":{\"programSettings\":{\"1\":{\"mode\":\"COOL\",\"setpointTemperatureHeat\":0,\"setpointTemperatureCool\":2400},\"2\":{\"mode\":\"COOL\",\"setpointTemperatureHeat\":0,\"setpointTemperatureCool\":2100},\"3\":{\"mode\":\"COOL\",\"setpointTemperatureHeat\":0,\"setpointTemperatureCool\":1800},\"4\":{\"mode\":\"COOL\",\"setpointTemperatureHeat\":0,\"setpointTemperatureCool\":2800}},\"quarters\":[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,4,4,4,4,4,4,4,4,0,0,0,0,0,0]}}',
        0,
        '{\"availableAlgorithms\":[\"ON_OFF_SETPOINT_MIDDLE\",\"ON_OFF_SETPOINT_AT_MOST\"],\"temperatures\":{\"roomMin\":1000,\"roomMax\":3900,\"auxMin\":500,\"auxMax\":5000,\"histeresisMin\":100,\"histeresisMax\":500,\"autoOffsetMin\":100,\"autoOffsetMax\":200}}'),
       (98, 5, 1, 3, 'Blanditiis consequatur saepe.', 6100, 422, 262144, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"mainThermometerChannelNo\":0,\"auxThermometerChannelNo\":1,\"auxThermometerType\":\"FLOOR\",\"antiFreezeAndOverheatProtectionEnabled\":true,\"auxMinMaxSetpointEnabled\":true,\"temperatureSetpointChangeSwitchesToManualMode\":true,\"usedAlgorithm\":\"ON_OFF_SETPOINT_MIDDLE\",\"minOnTimeS\":60,\"minOffTimeS\":120,\"outputValueOnError\":42,\"temperatures\":{\"freezeProtection\":1100,\"eco\":1800,\"comfort\":2000,\"boost\":2500,\"heatProtection\":3300,\"histeresis\":200,\"belowAlarm\":1200,\"aboveAlarm\":3600,\"auxMinSetpoint\":1000,\"auxMaxSetpoint\":2000},\"weeklySchedule\":{\"programSettings\":{\"1\":{\"mode\":\"HEAT\",\"setpointTemperatureHeat\":2100,\"setpointTemperatureCool\":0},\"2\":{\"mode\":\"COOL\",\"setpointTemperatureHeat\":0,\"setpointTemperatureCool\":2400},\"3\":{\"mode\":\"AUTO\",\"setpointTemperatureHeat\":1800,\"setpointTemperatureCool\":2200},\"4\":{\"mode\":\"NOT_SET\",\"setpointTemperatureHeat\":0,\"setpointTemperatureCool\":0}},\"quarters\":[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,0,0,0,0,0,0]}}',
        0,
        '{\"availableAlgorithms\":[\"ON_OFF_SETPOINT_MIDDLE\"],\"temperatures\":{\"roomMin\":1100,\"roomMax\":4000,\"auxMin\":500,\"auxMax\":5000,\"histeresisMin\":100,\"histeresisMax\":500,\"autoOffsetMin\":100,\"autoOffsetMax\":2000}}'),
       (99, 5, 1, 4, NULL, 6100, 426, 1966080, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"mainThermometerChannelNo\":null,\"auxThermometerChannelNo\":null,\"binarySensorChannelNo\":5,\"usedAlgorithm\":\"ON_OFF_SETPOINT_AT_MOST\",\"weeklySchedule\":{\"programSettings\":{\"1\":{\"mode\":\"HEAT\",\"setpointTemperatureHeat\":2400,\"setpointTemperatureCool\":0},\"2\":{\"mode\":\"HEAT\",\"setpointTemperatureHeat\":2100,\"setpointTemperatureCool\":0},\"3\":{\"mode\":\"HEAT\",\"setpointTemperatureHeat\":1800,\"setpointTemperatureCool\":0},\"4\":{\"mode\":\"NOT_SET\",\"setpointTemperatureHeat\":2200,\"setpointTemperatureCool\":0}},\"quarters\":[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,0,0,0,0,0,0]}}',
        0,
        '{\"availableAlgorithms\":[\"ON_OFF_SETPOINT_MIDDLE\",\"ON_OFF_SETPOINT_AT_MOST\"],\"temperatures\":{\"roomMin\":1000,\"roomMax\":4000,\"auxMin\":500,\"auxMax\":5000,\"histeresisMin\":100,\"histeresisMax\":500,\"autoOffsetMin\":100,\"autoOffsetMax\":2000}}'),
       (100, 5, 1, 5, 'Dolorem nihil cupiditate.', 1000, 235, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (101, 5, 1, 6, 'Illum excepturi.', 1000, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (102, 6, 1, 0, 'Dolor facilis excepturi.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (103, 6, 1, 1, NULL, 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (104, 6, 1, 2, 'Adipisci quaerat sed minus.', 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (105, 6, 1, 3, NULL, 1000, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (106, 6, 1, 4, 'Quia a voluptatibus quas.', 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false}', 0, NULL),
       (107, 6, 1, 5, NULL, 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (108, 6, 1, 6, 'Est molestias ullam.', 1000, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (109, 6, 1, 7, 'Et tempore.', 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false}', 0, NULL),
       (110, 6, 1, 8, NULL, 1000, 235, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (111, 6, 1, 9, 'Reiciendis eos aut.', 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"invertedLogic\":false}',
        0, NULL),
       (112, 6, 1, 10, 'Expedita commodi doloremque sint.', 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (113, 6, 1, 11, NULL, 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (114, 6, 1, 12, NULL, 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (115, 6, 1, 13, 'Nulla non culpa aut.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (116, 6, 1, 14, 'Repudiandae id perferendis.', 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false}', 0, NULL),
       (117, 6, 1, 15, 'Eveniet rem laborum.', 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (118, 6, 1, 16, 'Quibusdam laudantium aut et.', 1010, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (119, 6, 1, 17, 'Repellat maiores tempora et.', 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false}', 0, NULL),
       (120, 6, 1, 18, 'Praesentium molestias beatae.', 1010, 235, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (121, 6, 1, 19, NULL, 1010, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"invertedLogic\":false}', 0, NULL),
       (122, 6, 1, 20, 'Perspiciatis velit illum.', 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (123, 6, 1, 21, NULL, 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (124, 6, 1, 22, 'Possimus voluptates est sed.', 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (125, 6, 1, 23, 'Beatae fuga non rerum.', 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (126, 6, 1, 24, NULL, 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (127, 6, 1, 25, NULL, 2000, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (128, 6, 1, 26, NULL, 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (129, 6, 1, 27, 'Ut facere quod.', 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (130, 6, 1, 28, 'Libero ut reprehenderit voluptatem.', 2010, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (131, 6, 1, 29, 'Temporibus et.', 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (132, 6, 1, 30, NULL, 2010, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (133, 6, 1, 31, NULL, 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (134, 6, 1, 32, 'Alias vero est.', 2010, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null,\"relayTimeS\":0,\"timeSettingAvailable\":true}',
        0, NULL),
       (135, 6, 1, 33, 'Provident totam voluptas.', 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (136, 6, 1, 34, 'Non officia nihil.', 2020, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (137, 6, 1, 35, NULL, 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (138, 6, 1, 36, 'Officia deleniti laboriosam.', 2020, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (139, 6, 1, 37, 'Voluptatem fugiat qui in.', 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (140, 6, 1, 38, 'Sapiente omnis.', 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (141, 6, 1, 39, NULL, 2020, 110, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (142, 6, 1, 40, 'Hic id.', 2020, 115, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (143, 6, 1, 41, NULL, 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null,\"relayTimeS\":0,\"timeSettingAvailable\":true}',
        0, NULL),
       (144, 6, 1, 42, 'Eum minus non vitae suscipit.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (145, 6, 1, 43, 'Voluptatem qui consectetur.', 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (146, 6, 1, 44, NULL, 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (147, 6, 1, 45, NULL, 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (148, 6, 1, 46, 'Libero optio placeat assumenda.', 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (149, 6, 1, 47, 'Consequatur molestiae nihil similique.', 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (150, 6, 1, 48, 'Dolores qui consequatur quis.', 3034, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (151, 6, 1, 49, 'Molestiae cumque.', 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"humidityAdjustment\":0}', 0,
        NULL),
       (152, 6, 1, 50, 'Qui quo nihil maxime.', 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (153, 6, 1, 51, 'Aliquid quasi earum aliquam.', 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (154, 6, 1, 52, NULL, 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (155, 6, 1, 53, NULL, 3048, 270, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (156, 6, 1, 54, NULL, 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (157, 6, 1, 55, 'Doloremque eaque incidunt.', 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (158, 6, 1, 56, 'Ullam quidem fuga nihil.', 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (159, 6, 1, 57, 'Porro natus atque facilis.', 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (160, 6, 1, 58, NULL, 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (161, 6, 1, 59, NULL, 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"currency\":null,\"resetCountersAvailable\":false,\"countersAvailable\":[\"forwardActiveEnergy\",\"reverseActiveEnergy\",\"reverseReactiveEnergy\",\"forwardReactiveEnergy\",\"reverseActiveEnergyBalanced\"],\"electricityMeterInitialValues\":{},\"addToHistory\":false,\"lowerVoltageThreshold\":null,\"upperVoltageThreshold\":null,\"disabledPhases\":[],\"enabledPhases\":[1,2,3],\"availablePhases\":[1,2,3],\"relatedChannelId\":null}',
        0,
        '{\"countersAvailable\":[\"forwardActiveEnergy\",\"reverseActiveEnergy\",\"reverseReactiveEnergy\",\"forwardReactiveEnergy\",\"reverseActiveEnergyBalanced\"]}'),
       (162, 6, 1, 60, NULL, 5010, 315, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (163, 6, 1, 61, 'Quisquam impedit aut.', 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (164, 6, 1, 62, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (165, 6, 1, 63, 'A consequuntur et.', 5010, 340, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (166, 6, 1, 64, NULL, 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (167, 6, 1, 65, NULL, 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (168, 6, 1, 66, 'Nisi cum.', 6100, 420, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"waitingForConfigInit\":true}', 0,
        NULL),
       (169, 6, 1, 67, 'Eveniet ea et et.', 6100, 422, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"waitingForConfigInit\":true}', 0, NULL),
       (170, 6, 1, 68, 'Et inventore dolore.', 6100, 423, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (171, 6, 1, 69, NULL, 6100, 424, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (172, 6, 1, 70, 'Eligendi veniam mollitia.', 6100, 425, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"waitingForConfigInit\":true}', 0, NULL),
       (173, 6, 1, 71, NULL, 6100, 426, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"waitingForConfigInit\":true}', 0, NULL),
       (174, 6, 1, 72, NULL, 7000, 500, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (175, 6, 1, 73, 'Deserunt cumque voluptatibus iure.', 7010, 510, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0,
        NULL),
       (176, 6, 1, 74, NULL, 9000, 520, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"initialValue\":0,\"impulsesPerUnit\":0,\"unitPrefix\":null,\"unitSuffix\":null,\"precision\":0,\"storeMeasurementHistory\":false,\"chartType\":0,\"chartDataSourceType\":0,\"interpolateMeasurements\":false}',
        0, NULL),
       (177, 6, 1, 75, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"SHORT_PRESS_X3\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"SHORT_PRESS_X3\"]}'),
       (178, 6, 1, 76, NULL, 12000, 810, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"sectionsCount\":0,\"regenerationTimeStart\":0}', 0, NULL),
       (179, 6, 1, 77, 'Exercitationem et nostrum optio.', 12000, 800, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"sectionsCount\":0,\"regenerationTimeStart\":0}', 0, NULL),
       (180, 7, 1, 0, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (181, 7, 1, 1, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (182, 7, 1, 2, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (183, 7, 1, 3, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (184, 7, 1, 4, 'Sapiente dolore tenetur.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (185, 7, 1, 5, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (186, 7, 1, 6, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (187, 7, 1, 7, 'Architecto error aut.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (188, 7, 1, 8, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (189, 7, 1, 9, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (190, 8, 1, 0, 'Natus distinctio quis.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (191, 8, 1, 1, NULL, 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (192, 8, 1, 2, 'Nostrum modi quod sit eum.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (193, 8, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (194, 8, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (195, 8, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (196, 8, 1, 6, 'Numquam aut et.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (197, 8, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"TOGGLE_X2\",\"TURN_OFF\",\"TOGGLE_X3\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"TOGGLE_X2\",\"TURN_OFF\",\"TOGGLE_X3\"]}'),
       (198, 8, 1, 8, 'Soluta sed.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (199, 8, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (200, 8, 1, 10, NULL, 11000, 700, NULL, 194, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"TOGGLE_X1\",\"TOGGLE_X2\",\"TOGGLE_X3\"],\"disablesLocalOperation\":[],\"relatedChannelId\":194,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"TOGGLE_X1\",\"TOGGLE_X2\",\"TOGGLE_X3\"]}'),
       (201, 9, 1, 0, 'Provident aut ut.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (202, 9, 1, 1, 'Id et aut tenetur.', 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (203, 9, 1, 2, 'Eius ad libero.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (204, 9, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (205, 9, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (206, 9, 1, 5, 'Rerum asperiores omnis dolores.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (207, 9, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (208, 9, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X5\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X5\"]}'),
       (209, 9, 1, 8, 'Laudantium totam ut.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (210, 9, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (211, 9, 1, 10, 'Iure iste quis dicta.', 11000, 700, NULL, 205, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\",\"TOGGLE_X1\",\"SHORT_PRESS_X1\",\"SHORT_PRESS_X4\",\"HOLD\"],\"disablesLocalOperation\":[],\"relatedChannelId\":205,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\",\"TOGGLE_X1\",\"SHORT_PRESS_X1\",\"SHORT_PRESS_X4\",\"HOLD\"]}'),
       (212, 10, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (213, 10, 1, 1, 'Quae iusto iure.', 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (214, 10, 1, 2, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (215, 10, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (216, 10, 1, 4, 'Aut debitis omnis sequi.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (217, 10, 1, 5, 'Reiciendis id.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (218, 10, 1, 6, 'Minima sed asperiores.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (219, 10, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\"]}'),
       (220, 10, 1, 8, 'Totam ut nulla optio.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (221, 10, 1, 9, 'Laudantium voluptatem porro.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (222, 10, 1, 10, 'Ut magni perspiciatis.', 11000, 700, NULL, 216, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\",\"TURN_OFF\"],\"disablesLocalOperation\":[],\"relatedChannelId\":216,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\",\"TURN_OFF\"]}'),
       (223, 11, 1, 0, 'Hic odit consequatur.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (224, 11, 1, 1, 'Praesentium omnis nostrum nisi.', 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (225, 11, 1, 2, 'Culpa aut ut quaerat.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (226, 11, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (227, 11, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (228, 11, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (229, 11, 1, 6, 'Consequatur id autem.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (230, 11, 1, 7, 'Natus delectus consectetur a.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TURN_OFF\",\"SHORT_PRESS_X5\",\"TOGGLE_X3\",\"TOGGLE_X5\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TURN_OFF\",\"SHORT_PRESS_X5\",\"TOGGLE_X3\",\"TOGGLE_X5\"]}'),
       (231, 11, 1, 8, NULL, 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (232, 11, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (233, 11, 1, 10, NULL, 11000, 700, NULL, 227, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TURN_OFF\",\"SHORT_PRESS_X3\",\"TOGGLE_X1\"],\"disablesLocalOperation\":[],\"relatedChannelId\":227,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TURN_OFF\",\"SHORT_PRESS_X3\",\"TOGGLE_X1\"]}'),
       (234, 12, 1, 0, 'Dolores voluptate quasi necessitatibus.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (235, 12, 1, 1, NULL, 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (236, 12, 1, 2, 'Aut totam quis.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (237, 12, 1, 3, 'Veritatis accusamus molestiae.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (238, 12, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (239, 12, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (240, 12, 1, 6, 'Laborum est ea velit.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (241, 12, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\"]}'),
       (242, 12, 1, 8, 'Voluptatem iusto harum.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (243, 12, 1, 9, 'Autem odit aspernatur quia amet.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (244, 12, 1, 10, NULL, 11000, 700, NULL, 238, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TURN_ON\"],\"disablesLocalOperation\":[],\"relatedChannelId\":238,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TURN_ON\"]}'),
       (245, 13, 1, 0, 'Natus est qui.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (246, 13, 1, 1, NULL, 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (247, 13, 1, 2, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (248, 13, 1, 3, 'Quod magnam non dignissimos.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (249, 13, 1, 4, 'Autem animi magnam porro.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (250, 13, 1, 5, 'Mollitia eveniet quam.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (251, 13, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (252, 13, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X3\",\"SHORT_PRESS_X1\",\"TOGGLE_X5\",\"HOLD\",\"TURN_OFF\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X3\",\"SHORT_PRESS_X1\",\"TOGGLE_X5\",\"HOLD\",\"TURN_OFF\"]}'),
       (253, 13, 1, 8, 'Incidunt quasi est cumque eaque.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (254, 13, 1, 9, 'Sed tempora ut omnis.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (255, 13, 1, 10, 'Doloribus inventore qui.', 11000, 700, NULL, 249, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\",\"TOGGLE_X1\",\"SHORT_PRESS_X2\",\"TOGGLE_X5\",\"SHORT_PRESS_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":249,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X5\",\"TOGGLE_X1\",\"SHORT_PRESS_X2\",\"TOGGLE_X5\",\"SHORT_PRESS_X4\"]}'),
       (256, 14, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (257, 14, 1, 1, 'Sed soluta atque.', 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (258, 14, 1, 2, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (259, 14, 1, 3, 'Explicabo rerum doloribus.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (260, 14, 1, 4, 'Eum ipsa dolores harum.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (261, 14, 1, 5, 'Nihil praesentium et qui.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (262, 14, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (263, 14, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TURN_OFF\",\"SHORT_PRESS_X3\",\"TOGGLE_X5\",\"TOGGLE_X2\",\"TOGGLE_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TURN_OFF\",\"SHORT_PRESS_X3\",\"TOGGLE_X5\",\"TOGGLE_X2\",\"TOGGLE_X4\"]}'),
       (264, 14, 1, 8, 'Et quis nobis.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (265, 14, 1, 9, 'Quia aut ea sint aut.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (266, 14, 1, 10, 'Dolorem et fuga eos.', 11000, 700, NULL, 260, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TURN_ON\"],\"disablesLocalOperation\":[],\"relatedChannelId\":260,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TURN_ON\"]}'),
       (267, 15, 1, 0, 'Vel adipisci.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (268, 15, 1, 1, 'Aperiam fuga eos.', 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (269, 15, 1, 2, 'Laudantium minima necessitatibus.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (270, 15, 1, 3, 'Accusantium labore incidunt.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (271, 15, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (272, 15, 1, 5, 'Totam exercitationem quia cum.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (273, 15, 1, 6, 'Totam consequatur.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (274, 15, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X2\",\"TURN_ON\",\"HOLD\",\"TOGGLE_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X2\",\"TURN_ON\",\"HOLD\",\"TOGGLE_X4\"]}'),
       (275, 15, 1, 8, NULL, 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (276, 15, 1, 9, 'Aut est dolor.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (277, 15, 1, 10, 'Accusantium animi ut qui.', 11000, 700, NULL, 271, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":271,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X4\"]}'),
       (278, 16, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (279, 16, 1, 1, NULL, 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (280, 16, 1, 2, 'Animi fuga velit.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (281, 16, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (282, 16, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (283, 16, 1, 5, 'Et voluptas tempore ut.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (284, 16, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (285, 16, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X2\",\"SHORT_PRESS_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X2\",\"SHORT_PRESS_X4\"]}'),
       (286, 16, 1, 8, 'Blanditiis nostrum fugit nostrum.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (287, 16, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (288, 16, 1, 10, NULL, 11000, 700, NULL, 282, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X3\",\"SHORT_PRESS_X4\",\"SHORT_PRESS_X1\"],\"disablesLocalOperation\":[],\"relatedChannelId\":282,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X3\",\"SHORT_PRESS_X4\",\"SHORT_PRESS_X1\"]}'),
       (289, 17, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (290, 17, 1, 1, NULL, 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (291, 17, 1, 2, 'Deleniti vel veniam.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (292, 17, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (293, 17, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (294, 17, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (295, 17, 1, 6, 'Voluptatem possimus quo iure.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (296, 17, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"SHORT_PRESS_X5\",\"TOGGLE_X1\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"SHORT_PRESS_X5\",\"TOGGLE_X1\"]}'),
       (297, 17, 1, 8, NULL, 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (298, 17, 1, 9, 'Facere consequatur aut aspernatur.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (299, 17, 1, 10, 'Non ullam vel accusantium.', 11000, 700, NULL, 293, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X3\"],\"disablesLocalOperation\":[],\"relatedChannelId\":293,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X3\"]}'),
       (300, 18, 1, 0, 'Quia enim odio architecto.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (301, 18, 1, 1, NULL, 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (302, 18, 1, 2, 'Molestiae soluta.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (303, 18, 1, 3, 'Placeat vel minus.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (304, 18, 1, 4, 'Explicabo in quis vero.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (305, 18, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (306, 18, 1, 6, 'Fugit eveniet cumque ipsam maiores.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (307, 18, 1, 7, 'Aut iusto voluptatum.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"SHORT_PRESS_X2\",\"TOGGLE_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"SHORT_PRESS_X2\",\"TOGGLE_X4\"]}'),
       (308, 18, 1, 8, 'Excepturi ex ipsum.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (309, 18, 1, 9, 'Sit nobis neque repellendus omnis.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (310, 18, 1, 10, 'Explicabo ratione deserunt ut.', 11000, 700, NULL, 304, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X1\",\"TURN_OFF\",\"TOGGLE_X2\",\"SHORT_PRESS_X5\"],\"disablesLocalOperation\":[],\"relatedChannelId\":304,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X1\",\"TURN_OFF\",\"TOGGLE_X2\",\"SHORT_PRESS_X5\"]}'),
       (311, 19, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (312, 19, 1, 1, NULL, 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (313, 19, 1, 2, 'Ipsa mollitia error nostrum.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (314, 19, 1, 3, 'Quos dolorum porro.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (315, 19, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (316, 19, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (317, 19, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (318, 19, 1, 7, 'Officiis illo quasi.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"TOGGLE_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"TOGGLE_X4\"]}'),
       (319, 19, 1, 8, NULL, 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (320, 19, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (321, 19, 1, 10, 'Fuga ut quasi et.', 11000, 700, NULL, 315, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TURN_ON\",\"TOGGLE_X1\",\"SHORT_PRESS_X2\",\"HOLD\"],\"disablesLocalOperation\":[],\"relatedChannelId\":315,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TURN_ON\",\"TOGGLE_X1\",\"SHORT_PRESS_X2\",\"HOLD\"]}'),
       (322, 20, 1, 0, 'Voluptatum id quisquam.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (323, 20, 1, 1, 'Consequuntur iste voluptas sit.', 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (324, 20, 1, 2, 'Quod est quibusdam.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (325, 20, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (326, 20, 1, 4, 'Ut sint.', 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (327, 20, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (328, 20, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (329, 20, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"TOGGLE_X2\",\"SHORT_PRESS_X4\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"TOGGLE_X2\",\"SHORT_PRESS_X4\"]}'),
       (330, 20, 1, 8, 'Natus quod deserunt non.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (331, 20, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (332, 20, 1, 10, NULL, 11000, 700, NULL, 326, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TURN_ON\",\"TOGGLE_X1\"],\"disablesLocalOperation\":[],\"relatedChannelId\":326,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TURN_ON\",\"TOGGLE_X1\"]}'),
       (333, 21, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (334, 21, 1, 1, NULL, 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (335, 21, 1, 2, 'Sed amet quia.', 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (336, 21, 1, 3, 'Dolor sit nisi quia.', 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (337, 21, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (338, 21, 1, 5, 'Reiciendis fuga voluptatem voluptatem.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (339, 21, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (340, 21, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X2\",\"TOGGLE_X1\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X2\",\"TOGGLE_X1\"]}'),
       (341, 21, 1, 8, 'A omnis dolor porro culpa.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (342, 21, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (343, 21, 1, 10, 'Ipsa dicta quia recusandae.', 11000, 700, NULL, 337, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"TOGGLE_X2\",\"HOLD\"],\"disablesLocalOperation\":[],\"relatedChannelId\":337,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"TOGGLE_X2\",\"HOLD\"]}'),
       (344, 22, 1, 0, 'Impedit voluptatibus voluptatem temporibus.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (345, 22, 1, 1, 'Quae dolor.', 2900, 90, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (346, 22, 1, 2, NULL, 2900, 20, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (347, 22, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 16384, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":true,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (348, 22, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (349, 22, 1, 5, 'Tempora dolorem enim nesciunt.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (350, 22, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (351, 22, 1, 7, 'Tempora velit minima.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"TOGGLE_X2\",\"TURN_ON\",\"TOGGLE_X3\",\"SHORT_PRESS_X2\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X4\",\"TOGGLE_X2\",\"TURN_ON\",\"TOGGLE_X3\",\"SHORT_PRESS_X2\"]}'),
       (352, 22, 1, 8, 'Voluptatem culpa a cum.', 8000, 110, 2031615, 0, 0, 0, NULL, NULL, NULL, 0, 0, 3, 4096, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":true,\"openingSensorChannelId\":null}',
        0, NULL),
       (353, 22, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, 4, 8192, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":true,\"relatedChannelId\":null}',
        0, NULL),
       (354, 22, 1, 10, 'Rerum delectus.', 11000, 700, NULL, 348, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X3\"],\"disablesLocalOperation\":[],\"relatedChannelId\":348,\"hideInChannelsList\":true,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X3\"]}'),
       (355, 23, 2, 0, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (356, 23, 2, 1, 'Veniam blanditiis totam.', 1000, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (357, 23, 2, 2, NULL, 1000, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (358, 23, 2, 3, 'Autem magnam consequuntur non.', 1000, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (359, 23, 2, 4, NULL, 1000, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"invertedLogic\":false}', 0, NULL),
       (360, 23, 2, 5, 'Ut inventore suscipit.', 1000, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (361, 23, 2, 6, NULL, 1000, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (362, 23, 2, 7, 'Eius consequatur.', 1000, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false}', 0, NULL),
       (363, 23, 2, 8, 'Perferendis ullam voluptatum tempore.', 1000, 235, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0,
        NULL),
       (364, 23, 2, 9, 'Laboriosam qui facere vitae.', 1000, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false}', 0, NULL),
       (365, 23, 2, 10, NULL, 1010, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (366, 23, 2, 11, NULL, 1010, 60, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (367, 23, 2, 12, NULL, 1010, 70, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null}',
        0, NULL),
       (368, 23, 2, 13, 'Et quod autem laboriosam.', 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (369, 23, 2, 14, NULL, 1010, 80, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"invertedLogic\":false}', 0, NULL),
       (370, 23, 2, 15, NULL, 1010, 120, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (371, 23, 2, 16, NULL, 1010, 125, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false,\"openingSensorChannelId\":null}', 0, NULL),
       (372, 23, 2, 17, NULL, 1010, 230, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"invertedLogic\":false}', 0, NULL),
       (373, 23, 2, 18, 'Laborum fugiat ut perspiciatis.', 1010, 235, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (374, 23, 2, 19, 'Ea aut adipisci eaque.', 1010, 240, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"invertedLogic\":false}', 0, NULL),
       (375, 23, 2, 20, 'Sunt ducimus suscipit rerum.', 1020, 210, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (376, 23, 2, 21, 'Fuga odio ea.', 1020, 220, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (377, 23, 2, 22, 'Reprehenderit enim veniam minus.', 2000, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (378, 23, 2, 23, NULL, 2000, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (379, 23, 2, 24, NULL, 2000, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (380, 23, 2, 25, 'Quo et nulla optio.', 2000, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (381, 23, 2, 26, 'Et voluptas quis.', 2010, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (382, 23, 2, 27, NULL, 2010, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (383, 23, 2, 28, 'Nihil provident sed.', 2010, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (384, 23, 2, 29, 'Optio eos asperiores aliquam.', 2010, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (385, 23, 2, 30, 'Ab molestiae soluta.', 2010, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (386, 23, 2, 31, NULL, 2010, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (387, 23, 2, 32, NULL, 2010, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null,\"relayTimeS\":0,\"timeSettingAvailable\":true}',
        0, NULL),
       (388, 23, 2, 33, 'Ut expedita amet veritatis.', 2020, 10, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (389, 23, 2, 34, NULL, 2020, 20, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (390, 23, 2, 35, NULL, 2020, 30, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"closingRule\":{},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"numberOfAttemptsToOpen\":5,\"numberOfAttemptsToClose\":5,\"stateVerificationMethodActive\":false,\"openingSensorChannelId\":null,\"openingSensorSecondaryChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}',
        0, NULL),
       (391, 23, 2, 36, 'Reiciendis minus laudantium.', 2020, 90, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingSensorChannelId\":null,\"relayTimeMs\":500,\"timeSettingAvailable\":true}', 0, NULL),
       (392, 23, 2, 37, NULL, 2020, 130, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (393, 23, 2, 38, 'Et non rerum ut.', 2020, 140, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null}',
        0, NULL),
       (394, 23, 2, 39, 'Voluptates quia provident tempore.', 2020, 110, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (395, 23, 2, 40, NULL, 2020, 115, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"openingTimeS\":0,\"closingTimeS\":0,\"bottomPosition\":0,\"timeSettingAvailable\":true,\"recalibrateAvailable\":false,\"autoCalibrationAvailable\":false,\"openingSensorChannelId\":null}',
        0, NULL),
       (396, 23, 2, 41, NULL, 2020, 300, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"relatedChannelId\":null,\"relayTimeS\":0,\"timeSettingAvailable\":true}',
        0, NULL),
       (397, 23, 2, 42, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (398, 23, 2, 43, NULL, 3010, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (399, 23, 2, 44, NULL, 3022, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (400, 23, 2, 45, 'Inventore perspiciatis.', 3020, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (401, 23, 2, 46, 'Magni nemo odit.', 3032, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (402, 23, 2, 47, 'Et nostrum error.', 3030, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (403, 23, 2, 48, NULL, 3034, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"temperatureAdjustment\":0}', 0, NULL),
       (404, 23, 2, 49, 'Provident doloribus totam voluptates.', 3036, 42, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"humidityAdjustment\":0}', 0, NULL),
       (405, 23, 2, 50, NULL, 3038, 45, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"humidityAdjustment\":0,\"temperatureAdjustment\":0}', 0, NULL),
       (406, 23, 2, 51, 'Et et voluptatem magni.', 3042, 250, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (407, 23, 2, 52, 'Suscipit dolor molestias repellendus qui.', 3044, 260, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}',
        0, NULL),
       (408, 23, 2, 53, 'Quia dolorem atque.', 3048, 270, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (409, 23, 2, 54, NULL, 3050, 280, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (410, 23, 2, 55, NULL, 3100, 290, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (411, 23, 2, 56, NULL, 4000, 180, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (412, 23, 2, 57, NULL, 4010, 190, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (413, 23, 2, 58, 'Dolorem sint quis.', 4020, 200, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false}}',
        0, NULL),
       (414, 23, 2, 59, NULL, 5000, 310, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"currency\":null,\"resetCountersAvailable\":false,\"countersAvailable\":[\"forwardActiveEnergy\",\"forwardActiveEnergyBalanced\",\"forwardReactiveEnergy\",\"reverseActiveEnergy\",\"reverseReactiveEnergy\",\"reverseActiveEnergyBalanced\"],\"electricityMeterInitialValues\":{},\"addToHistory\":false,\"lowerVoltageThreshold\":null,\"upperVoltageThreshold\":null,\"disabledPhases\":[],\"enabledPhases\":[1,2,3],\"availablePhases\":[1,2,3],\"relatedChannelId\":null}',
        0,
        '{\"countersAvailable\":[\"forwardActiveEnergy\",\"forwardActiveEnergyBalanced\",\"forwardReactiveEnergy\",\"reverseActiveEnergy\",\"reverseReactiveEnergy\",\"reverseActiveEnergyBalanced\"]}'),
       (415, 23, 2, 60, NULL, 5010, 315, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (416, 23, 2, 61, NULL, 5010, 320, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (417, 23, 2, 62, 'Possimus error doloremque atque.', 5010, 330, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (418, 23, 2, 63, 'Deleniti rerum consequatur.', 5010, 340, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"pricePerUnit\":0,\"impulsesPerUnit\":0,\"currency\":null,\"unit\":null,\"initialValue\":0,\"addToHistory\":false,\"resetCountersAvailable\":false,\"relatedChannelId\":null}',
        0, NULL),
       (419, 23, 2, 64, NULL, 6000, 400, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (420, 23, 2, 65, 'Iusto dolor pariatur neque amet.', 6010, 410, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (421, 23, 2, 66, NULL, 6100, 420, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"waitingForConfigInit\":true}', 0, NULL),
       (422, 23, 2, 67, NULL, 6100, 422, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"waitingForConfigInit\":true}', 0, NULL),
       (423, 23, 2, 68, 'Voluptas exercitationem enim.', 6100, 423, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (424, 23, 2, 69, NULL, 6100, 424, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (425, 23, 2, 70, 'Cum amet sint illo.', 6100, 425, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"waitingForConfigInit\":true}', 0, NULL),
       (426, 23, 2, 71, NULL, 6100, 426, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{\"waitingForConfigInit\":true}', 0, NULL),
       (427, 23, 2, 72, NULL, 7000, 500, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (428, 23, 2, 73, 'Reiciendis totam.', 7010, 510, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, '{}', 0, NULL),
       (429, 23, 2, 74, 'Eius numquam ut.', 9000, 520, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"initialValue\":0,\"impulsesPerUnit\":0,\"unitPrefix\":null,\"unitSuffix\":null,\"precision\":0,\"storeMeasurementHistory\":false,\"chartType\":0,\"chartDataSourceType\":0,\"interpolateMeasurements\":false}',
        0, NULL),
       (430, 23, 2, 75, 'Et nisi omnis saepe corrupti.', 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"TOGGLE_X2\"],\"disablesLocalOperation\":[],\"relatedChannelId\":null,\"hideInChannelsList\":false,\"actions\":{}}',
        0, '{\"actionTriggerCapabilities\":[\"SHORT_PRESS_X1\",\"TOGGLE_X2\"]}'),
       (431, 23, 2, 76, NULL, 12000, 810, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"sectionsCount\":0,\"regenerationTimeStart\":0}', 0, NULL),
       (432, 23, 2, 77, NULL, 12000, 800, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"sectionsCount\":0,\"regenerationTimeStart\":0}', 0, NULL),
       (433, 24, 1, 0, 'Alias commodi voluptas et.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"googleHome\":{\"googleHomeDisabled\":false,\"needsUserConfirmation\":false,\"pin\":null,\"pinSet\":false},\"hideInChannelsList\":true,\"relatedChannelId\":null}',
        0, NULL),
       (434, 24, 1, 1, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{\"alexa\":{\"alexaDisabled\":false},\"hideInChannelsList\":true,\"temperatureAdjustment\":0}', 0, NULL);
/*!40000 ALTER TABLE `supla_dev_channel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_dev_channel_extended_value`
--

DROP TABLE IF EXISTS `supla_dev_channel_extended_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_dev_channel_extended_value` (
  `channel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `update_time` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `type` tinyint(4) NOT NULL COMMENT '(DC2Type:tinyint)',
  `value` varbinary(1024) DEFAULT NULL,
  PRIMARY KEY (`channel_id`),
  KEY `IDX_3207F134A76ED395` (`user_id`),
  CONSTRAINT `FK_3207F13472F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3207F134A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_dev_channel_extended_value`
--

LOCK TABLES `supla_dev_channel_extended_value` WRITE;
/*!40000 ALTER TABLE `supla_dev_channel_extended_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_dev_channel_extended_value` ENABLE KEYS */;
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
INSERT INTO `supla_dev_channel_group` VALUES (1,1,'Światła na parterze',140,0,4,0,NULL),(2,1,'Sit eaque animi quo.',110,0,3,0,NULL),(3,1,'Nemo autem quos autem laborum.',90,0,4,0,NULL),(4,1,'Molestiae tempore tenetur sed quis.',20,0,3,0,NULL),(5,1,'Quia doloribus commodi.',20,0,4,0,NULL),(6,1,'Libero itaque labore est.',90,0,3,0,NULL),(7,1,'Libero provident vel unde.',90,0,3,0,NULL),(8,1,'Commodi ut et.',140,0,3,0,NULL),(9,1,'Quod delectus.',110,0,3,0,NULL),(10,1,'Tempora praesentium doloremque suscipit.',140,0,3,0,NULL),(11,1,'Nihil possimus aut qui.',20,0,4,0,NULL);
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
  `schedule_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6AE7809FA76ED395` (`user_id`),
  KEY `IDX_6AE7809F72F5A1AA` (`channel_id`),
  KEY `IDX_6AE7809F89E4AAEE` (`channel_group_id`),
  KEY `IDX_6AE7809F166053B4` (`scene_id`),
  KEY `IDX_6AE7809FA40BC2D5` (`schedule_id`),
  CONSTRAINT `FK_6AE7809F166053B4` FOREIGN KEY (`scene_id`) REFERENCES `supla_scene` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6AE7809F72F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6AE7809F89E4AAEE` FOREIGN KEY (`channel_group_id`) REFERENCES `supla_dev_channel_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6AE7809FA40BC2D5` FOREIGN KEY (`schedule_id`) REFERENCES `supla_schedule` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6AE7809FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_direct_link`
--

LOCK TABLES `supla_direct_link` WRITE;
/*!40000 ALTER TABLE `supla_direct_link` DISABLE KEYS */;
INSERT INTO `supla_direct_link` VALUES (1,1,209,NULL,'xE2t8p_H6tvaV','LightGray','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(2,1,250,NULL,'NY7uYG7Noo94','Magenta','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(3,1,217,NULL,'hnR56iU_uYWdYa','Snow','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(4,1,265,NULL,'B_cwZcQYqiU3Y','Turquoise','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(5,1,240,NULL,'RJXB7uDFNahx38Z8','GoldenRod','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(6,1,245,NULL,'3Pbh9HeA6j','OrangeRed','[70]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(7,1,336,NULL,'45h3RJScbUE5J_','PaleVioletRed','[10100]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(8,1,211,NULL,'DaAoyyfc95sXiaz','SkyBlue','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(9,1,338,NULL,'UdG3inUPjtnVKS','HotPink','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(10,1,228,NULL,'QmZswWgMoFWkDR','MintCream','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(11,1,319,NULL,'_XT8azv5pVc','Sienna','[51]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(12,1,352,NULL,'UC8RJANBMBw9','SkyBlue','[40]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(13,1,319,NULL,'AfbxeW6JJsGo9w','DarkSlateGray','[100]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(14,1,275,NULL,'YsiqbEknNCZ','DarkOliveGreen','[50]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(15,1,283,NULL,'LxNgSgYqerB','Cornsilk','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(16,1,208,NULL,'iJZFmEkMn8F','Gainsboro','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(17,1,281,NULL,'68hjUcNDvzUW','Red','[10100]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(18,1,311,NULL,'itNNkCapQNaPf','SlateBlue','[10100]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(19,1,238,NULL,'bruzqdUjewAFBm','LawnGreen','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(20,1,305,NULL,'T2K_Az5ZbZ','MistyRose','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL),(21,2,355,NULL,'H6DKDyQ9SPU8Afk','SUPLAER Direct Link','[1000]',NULL,NULL,NULL,NULL,NULL,1,0,NULL,NULL);
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
INSERT INTO `supla_em_log` VALUES (76,'2023-12-16 08:52:38',5,10,6,6,4,6,9,10,6,7,11,5,5,5),(76,'2023-12-16 09:02:38',11,17,7,11,8,10,19,14,15,15,14,9,14,14),(76,'2023-12-16 09:12:38',13,27,17,17,13,14,25,24,23,23,19,16,20,21),(76,'2023-12-16 09:22:38',16,31,20,19,22,21,34,32,29,29,25,22,30,31),(76,'2023-12-16 09:32:38',17,39,28,25,26,23,38,40,39,35,25,31,40,34),(76,'2023-12-16 09:42:38',21,48,37,34,34,26,39,48,45,40,35,41,44,43),(76,'2023-12-16 09:52:38',27,52,45,40,41,35,48,54,53,49,40,50,52,49),(76,'2023-12-16 10:02:38',35,54,55,47,46,40,52,63,63,56,50,60,61,54),(76,'2023-12-16 10:12:38',42,57,63,55,48,44,54,71,73,64,56,62,67,64),(76,'2023-12-16 10:22:38',51,67,65,61,55,52,63,80,77,72,63,70,69,65),(76,'2023-12-16 10:32:38',53,69,68,71,64,62,65,88,78,78,70,80,70,74),(76,'2023-12-16 10:42:38',62,71,72,78,72,65,69,93,85,81,77,85,73,77),(76,'2023-12-16 10:52:38',67,76,82,80,82,69,78,97,91,89,85,88,76,87),(76,'2023-12-16 11:02:38',76,86,89,86,90,79,87,105,91,89,95,97,84,93),(76,'2023-12-16 11:12:38',83,94,97,93,100,86,90,110,94,97,100,106,88,101),(76,'2023-12-16 11:22:38',85,100,105,97,106,89,100,118,99,106,102,110,91,108),(76,'2023-12-16 11:32:38',93,110,110,102,109,96,109,127,105,111,107,116,101,118),(76,'2023-12-16 11:42:38',98,116,119,102,118,106,118,131,113,118,107,119,106,120),(76,'2023-12-16 11:52:38',108,118,122,112,123,116,118,141,120,127,111,129,114,130),(76,'2023-12-16 12:02:38',115,121,131,112,130,119,125,151,128,127,119,131,118,134),(76,'2023-12-16 12:12:38',124,128,137,118,140,120,129,158,135,136,129,140,127,143),(76,'2023-12-16 12:22:38',127,133,142,122,146,127,131,163,143,139,134,145,134,150),(76,'2023-12-16 12:32:38',134,140,148,127,148,131,140,166,147,141,141,153,141,156),(76,'2023-12-16 12:42:38',142,147,158,131,149,140,144,175,148,150,141,160,148,166),(76,'2023-12-16 12:52:38',144,156,164,132,156,141,153,177,158,160,145,166,156,172),(76,'2023-12-16 13:02:38',152,164,173,139,160,146,160,185,164,164,153,170,159,181),(76,'2023-12-16 13:12:38',159,165,176,148,164,147,167,191,171,169,161,177,166,184),(76,'2023-12-16 13:22:38',166,168,180,158,168,154,176,200,176,173,169,186,172,194),(76,'2023-12-16 13:32:38',172,175,184,167,175,157,182,209,183,181,178,196,182,201),(76,'2023-12-16 13:42:38',182,182,188,176,184,161,191,219,190,190,182,202,188,211),(76,'2023-12-16 13:52:38',190,186,195,183,188,166,192,226,199,198,190,204,198,220),(76,'2023-12-16 14:02:38',198,193,200,184,195,175,200,236,203,207,195,204,203,229),(76,'2023-12-16 14:12:38',203,200,204,194,205,177,205,245,211,216,199,206,213,238),(76,'2023-12-16 14:22:38',209,202,205,203,212,187,214,249,219,222,203,210,218,243),(76,'2023-12-16 14:32:38',219,209,212,208,221,194,224,253,228,232,208,217,228,253),(76,'2023-12-16 14:42:38',223,215,214,211,230,201,230,261,234,236,216,226,232,258),(76,'2023-12-16 15:02:38',236,222,226,229,246,213,250,279,246,253,235,239,244,267),(76,'2023-12-16 15:12:38',239,223,230,237,254,222,257,288,248,256,245,242,245,269),(76,'2023-12-16 15:22:38',246,231,240,243,260,229,263,298,257,263,255,246,254,273),(76,'2023-12-16 15:32:38',250,238,249,250,263,231,266,307,266,266,265,254,261,282),(76,'2023-12-16 15:42:38',254,248,258,256,270,237,269,313,271,269,270,259,261,292),(76,'2023-12-16 15:52:38',263,254,265,262,278,246,276,319,279,277,274,262,266,292),(76,'2023-12-16 16:02:38',270,260,273,267,284,253,281,323,288,287,282,269,273,297),(76,'2023-12-16 16:12:38',280,270,281,269,289,259,288,326,297,294,288,276,276,303),(76,'2023-12-16 16:22:38',288,272,282,276,299,267,297,328,306,298,296,281,286,308),(76,'2023-12-16 16:32:38',293,278,290,285,308,276,307,334,307,298,302,286,291,309),(76,'2023-12-16 16:42:38',302,284,298,290,310,283,308,343,310,305,309,296,300,317),(76,'2023-12-16 16:52:38',311,292,301,300,317,290,318,350,312,311,316,302,308,324),(76,'2023-12-16 17:02:38',315,302,307,305,319,296,323,356,319,319,326,307,311,334),(76,'2023-12-16 17:12:38',320,309,317,306,326,304,330,362,329,326,330,315,313,344),(76,'2023-12-16 17:22:38',322,314,320,314,333,307,337,370,334,334,336,325,323,345),(76,'2023-12-16 17:32:38',332,315,325,324,340,316,344,375,343,338,346,334,331,350),(76,'2023-12-16 17:42:38',337,316,327,327,347,317,351,384,344,346,355,341,341,360),(76,'2023-12-16 17:52:38',347,324,337,337,350,323,359,393,348,354,359,351,342,365),(76,'2023-12-16 18:02:38',347,331,346,344,360,333,367,401,350,362,367,358,351,371),(76,'2023-12-16 18:12:38',351,338,349,348,360,342,373,410,353,367,371,360,356,377),(76,'2023-12-16 18:22:38',361,346,356,351,367,350,377,417,360,372,376,370,365,383),(76,'2023-12-16 18:42:38',371,363,366,355,377,361,388,425,370,385,385,387,372,398),(76,'2023-12-16 18:52:38',381,368,369,365,386,368,396,434,379,386,390,394,378,406),(76,'2023-12-16 19:02:38',385,369,370,370,390,377,401,443,381,388,394,402,384,412),(76,'2023-12-16 19:22:38',397,379,378,378,403,384,420,453,391,398,405,413,401,422),(76,'2023-12-16 19:32:38',404,389,387,386,408,389,429,455,399,405,412,421,406,429),(76,'2023-12-16 19:42:38',407,399,395,396,412,396,435,462,404,413,419,426,416,436),(76,'2023-12-16 19:52:38',417,409,398,404,421,403,439,468,412,423,419,432,425,442),(76,'2023-12-16 20:02:38',427,418,400,405,426,403,441,477,417,425,427,435,426,443),(76,'2023-12-16 20:12:38',437,425,408,414,434,405,445,479,424,429,435,442,427,447),(76,'2023-12-16 20:22:38',443,433,410,417,435,411,447,484,434,437,442,452,437,456),(76,'2023-12-16 20:32:38',453,441,419,427,442,416,457,486,441,440,448,458,438,464),(76,'2023-12-16 20:42:38',462,443,422,436,449,421,467,492,449,440,448,466,443,470),(76,'2023-12-16 20:52:38',471,446,432,443,456,424,474,496,456,442,457,476,450,472),(76,'2023-12-16 21:02:38',481,453,438,450,464,433,481,505,466,445,466,483,456,482),(76,'2023-12-16 21:12:38',481,460,444,452,474,440,488,510,466,451,475,484,466,489),(76,'2023-12-16 21:22:38',484,463,448,461,479,445,492,520,469,461,484,493,470,498),(76,'2023-12-16 21:32:38',486,465,453,467,482,454,499,527,476,462,491,503,480,506),(76,'2023-12-16 21:42:38',488,473,458,470,486,464,501,531,478,462,500,512,490,507),(76,'2023-12-16 21:52:38',492,481,460,473,494,465,507,541,488,466,506,521,491,510),(76,'2023-12-16 22:02:38',496,487,461,480,503,470,512,545,497,471,516,530,493,512),(76,'2023-12-16 22:12:38',501,493,468,487,507,478,513,554,504,476,525,535,503,520),(76,'2023-12-16 22:22:38',511,494,472,497,517,487,514,559,509,480,534,540,506,524),(76,'2023-12-16 22:32:38',514,495,477,503,526,494,523,562,511,487,539,549,514,529),(76,'2023-12-16 22:42:38',519,504,477,513,535,502,530,565,519,496,543,559,524,538),(76,'2023-12-16 22:52:38',523,511,485,520,537,505,539,574,528,503,549,568,532,543),(76,'2023-12-16 23:02:38',526,517,495,530,545,515,539,584,535,513,557,576,537,549),(76,'2023-12-16 23:12:38',536,522,500,540,549,518,549,589,541,513,563,584,539,556),(76,'2023-12-16 23:22:38',544,529,506,546,558,527,551,598,547,520,568,591,543,564),(76,'2023-12-16 23:32:38',554,534,508,555,565,535,556,607,551,526,573,601,553,571),(76,'2023-12-16 23:42:38',559,536,518,559,575,537,562,616,553,527,580,606,557,574),(76,'2023-12-16 23:52:38',564,539,518,563,583,545,567,625,558,537,582,616,563,579),(76,'2023-12-17 00:02:38',570,549,521,567,592,554,568,633,564,545,585,623,573,588),(76,'2023-12-17 00:12:38',571,559,527,577,599,562,568,634,573,554,594,625,575,591),(76,'2023-12-17 00:22:38',581,568,530,587,601,571,576,641,576,557,603,633,583,600),(76,'2023-12-17 00:32:38',587,574,533,593,606,581,582,651,583,564,607,640,583,605),(76,'2023-12-17 00:42:38',593,583,542,598,607,587,588,657,592,574,617,649,585,612),(76,'2023-12-17 00:52:38',602,591,547,601,611,590,595,667,599,580,626,654,585,618),(76,'2023-12-17 01:02:38',612,597,552,611,619,594,604,677,604,584,631,661,591,623),(76,'2023-12-17 01:12:38',618,607,559,615,621,602,606,684,610,594,639,668,598,632),(76,'2023-12-17 01:22:38',621,609,565,621,628,611,606,691,614,599,641,677,599,639),(76,'2023-12-17 01:32:38',628,611,569,631,630,617,609,700,620,605,650,677,605,640),(76,'2023-12-17 01:42:38',638,618,578,639,630,622,617,709,630,610,656,683,612,648),(76,'2023-12-17 01:52:38',641,618,584,647,639,629,621,718,639,612,664,689,618,656),(76,'2023-12-17 02:02:38',647,624,586,657,643,635,631,726,647,617,673,690,627,666),(76,'2023-12-17 02:12:38',649,628,590,658,650,640,639,734,654,619,681,699,637,674),(76,'2023-12-17 02:22:38',655,631,595,661,658,649,644,737,655,629,690,706,643,675),(76,'2023-12-17 02:42:38',667,643,611,676,674,659,652,748,663,647,702,711,655,690),(76,'2023-12-17 02:52:38',668,653,620,680,680,666,657,758,669,649,705,718,660,696),(76,'2023-12-17 03:02:38',678,660,621,690,690,670,665,768,675,657,711,720,666,702),(76,'2023-12-17 03:12:38',687,666,630,694,696,672,671,775,683,667,717,728,674,711),(76,'2023-12-17 03:22:38',697,669,633,698,703,682,679,783,684,672,722,734,679,711),(76,'2023-12-17 03:32:38',703,676,638,707,709,687,683,792,691,676,731,743,685,716),(76,'2023-12-17 03:42:38',709,681,644,713,718,693,693,792,695,685,740,750,690,721),(76,'2023-12-17 03:52:38',709,691,647,718,724,703,700,799,699,690,749,759,697,724),(76,'2023-12-17 04:02:38',712,701,655,721,728,706,703,807,709,698,752,760,705,731),(76,'2023-12-17 04:12:38',721,707,659,724,732,714,707,812,709,703,760,767,706,740),(76,'2023-12-17 04:22:38',726,713,663,734,734,720,717,819,719,708,766,769,712,750),(76,'2023-12-17 04:32:38',730,723,668,740,742,727,723,828,728,715,772,779,720,757),(76,'2023-12-17 04:42:38',740,729,670,748,751,736,726,837,737,716,779,787,725,762),(76,'2023-12-17 04:52:38',744,734,675,757,756,738,732,844,738,720,785,797,734,766),(76,'2023-12-17 05:12:38',753,745,681,773,771,758,745,857,749,734,798,807,750,782),(76,'2023-12-17 05:22:38',761,749,683,779,777,767,750,863,755,740,806,817,755,784),(76,'2023-12-17 05:32:38',771,756,688,780,782,773,754,870,760,746,815,827,762,787),(76,'2023-12-17 05:42:38',781,764,693,789,789,779,758,879,765,755,823,833,770,790),(76,'2023-12-17 05:52:38',790,769,696,795,795,788,764,884,770,758,830,840,774,793),(76,'2023-12-17 06:02:38',796,776,698,805,804,796,771,889,770,761,840,847,782,797),(76,'2023-12-17 06:12:38',800,784,703,815,811,801,773,896,778,770,846,849,789,805),(76,'2023-12-17 06:22:38',802,788,707,822,820,809,782,903,779,776,856,851,794,808),(76,'2023-12-17 06:32:38',802,793,713,827,829,817,790,912,781,784,866,856,800,818),(76,'2023-12-17 06:42:38',808,803,721,831,834,826,797,922,782,788,874,866,806,827),(76,'2023-12-17 06:52:38',810,810,731,840,838,832,805,930,791,794,876,871,811,837),(76,'2023-12-17 07:12:38',825,822,748,852,850,851,814,938,802,799,890,881,826,849),(76,'2023-12-17 07:22:38',832,828,754,854,855,860,822,944,810,809,898,883,829,854),(76,'2023-12-17 07:32:38',832,831,760,858,863,867,832,953,812,819,905,884,837,861),(76,'2023-12-17 07:42:38',841,835,761,863,871,874,839,962,818,825,906,894,837,869),(76,'2023-12-17 07:52:38',850,835,764,867,879,882,849,967,822,826,916,900,847,873),(76,'2023-12-17 08:02:38',853,841,773,869,881,891,853,974,831,831,922,901,856,878),(76,'2023-12-17 08:12:38',860,847,777,871,884,898,860,977,840,836,928,904,862,883),(76,'2023-12-17 08:22:38',865,855,783,877,888,901,865,983,850,838,934,913,865,893),(76,'2023-12-17 08:32:38',872,864,792,883,894,911,867,987,853,843,943,915,870,900),(76,'2023-12-17 08:42:38',876,871,799,890,895,917,877,991,863,852,949,924,879,900),(76,'2023-12-17 08:52:38',881,875,800,890,898,926,882,995,872,854,958,928,888,906),(76,'2023-12-17 09:02:38',889,885,807,892,905,931,892,996,882,861,964,938,898,916),(76,'2023-12-17 09:12:38',896,889,812,900,914,936,897,1006,889,870,964,940,908,922),(76,'2023-12-17 09:22:38',906,899,818,902,923,941,898,1014,893,877,974,947,913,929),(76,'2023-12-17 09:32:38',915,904,823,903,931,950,905,1021,900,883,980,952,922,938),(76,'2023-12-17 09:42:38',921,913,827,907,936,960,908,1029,910,892,983,960,932,942),(76,'2023-12-17 09:52:38',931,921,836,915,946,969,910,1034,912,899,987,968,936,948),(76,'2023-12-17 10:02:38',933,926,844,924,956,969,913,1044,921,905,996,976,944,957),(76,'2023-12-17 10:12:38',942,929,852,926,965,973,923,1052,925,905,1003,980,953,959),(76,'2023-12-17 10:22:38',951,936,861,935,968,979,929,1055,928,907,1012,989,959,967),(76,'2023-12-17 10:32:38',955,937,864,942,978,983,936,1065,929,917,1019,998,962,977),(76,'2023-12-17 10:42:38',959,938,866,946,982,988,939,1074,936,921,1022,1008,968,981),(76,'2023-12-17 10:52:38',961,942,875,951,987,995,942,1077,945,926,1023,1014,971,988),(76,'2023-12-17 11:02:38',966,945,882,956,994,1003,944,1082,954,934,1032,1021,978,996),(76,'2023-12-17 11:12:38',972,946,890,962,998,1012,949,1091,962,941,1035,1024,982,1004),(76,'2023-12-17 11:22:38',982,952,896,971,1003,1018,959,1100,965,946,1042,1026,983,1007),(76,'2023-12-17 11:32:38',985,961,897,981,1008,1025,965,1104,965,953,1051,1032,991,1017),(76,'2023-12-17 11:52:38',992,975,909,996,1027,1041,978,1119,976,971,1060,1042,998,1029),(76,'2023-12-17 12:02:38',1001,979,910,1006,1037,1048,982,1127,986,975,1068,1044,1002,1033),(76,'2023-12-17 12:12:38',1003,987,916,1016,1045,1052,983,1128,988,982,1070,1050,1009,1038),(76,'2023-12-17 12:32:38',1016,997,924,1033,1055,1066,1000,1140,1000,995,1080,1062,1023,1051),(76,'2023-12-17 12:42:38',1025,1004,930,1040,1056,1068,1006,1144,1005,997,1088,1070,1030,1057),(76,'2023-12-17 12:52:38',1027,1007,933,1048,1061,1070,1010,1148,1015,1003,1091,1075,1032,1066),(76,'2023-12-17 13:02:38',1032,1012,934,1054,1062,1076,1011,1156,1024,1008,1096,1083,1036,1069),(76,'2023-12-17 13:12:38',1040,1017,943,1055,1072,1080,1015,1166,1024,1018,1100,1092,1042,1079),(76,'2023-12-17 13:22:38',1047,1027,950,1058,1079,1089,1015,1174,1034,1020,1110,1098,1046,1087),(76,'2023-12-17 13:32:38',1056,1034,958,1062,1088,1099,1021,1183,1036,1027,1111,1106,1056,1087),(76,'2023-12-17 13:42:38',1066,1036,961,1069,1095,1103,1024,1189,1043,1036,1119,1108,1065,1087),(76,'2023-12-17 13:52:38',1074,1046,967,1075,1095,1113,1033,1194,1049,1045,1129,1112,1072,1093),(76,'2023-12-17 14:02:38',1080,1052,968,1078,1097,1119,1040,1204,1050,1051,1131,1115,1073,1102),(76,'2023-12-17 14:12:38',1090,1054,973,1082,1107,1129,1046,1214,1055,1054,1137,1123,1080,1105),(76,'2023-12-17 14:22:38',1093,1059,977,1084,1117,1130,1053,1218,1065,1063,1139,1133,1090,1107),(76,'2023-12-17 14:32:38',1095,1065,977,1090,1119,1140,1063,1218,1068,1066,1144,1140,1095,1116),(76,'2023-12-17 14:42:38',1100,1068,986,1091,1127,1141,1068,1225,1069,1067,1148,1148,1105,1118),(76,'2023-12-17 14:52:38',1103,1074,994,1095,1137,1151,1075,1231,1071,1074,1155,1158,1114,1125),(76,'2023-12-17 15:02:38',1112,1079,995,1101,1146,1161,1085,1238,1076,1084,1164,1166,1123,1132),(76,'2023-12-17 15:12:38',1119,1084,1004,1108,1155,1164,1090,1238,1084,1087,1174,1166,1130,1140),(76,'2023-12-17 15:22:38',1127,1094,1010,1118,1161,1171,1096,1248,1090,1094,1182,1176,1135,1149),(76,'2023-12-17 15:32:38',1132,1095,1016,1125,1165,1177,1104,1253,1100,1103,1189,1182,1139,1150),(76,'2023-12-17 15:42:38',1141,1097,1026,1129,1175,1183,1113,1257,1108,1108,1196,1191,1147,1154),(76,'2023-12-17 15:52:38',1150,1098,1027,1133,1181,1184,1118,1263,1110,1117,1206,1198,1157,1161),(76,'2023-12-17 16:02:38',1152,1101,1033,1141,1187,1190,1127,1269,1118,1123,1214,1206,1163,1166),(76,'2023-12-17 16:12:38',1161,1108,1042,1142,1196,1195,1133,1271,1125,1133,1219,1209,1165,1169),(76,'2023-12-17 16:22:38',1166,1114,1047,1149,1202,1204,1141,1277,1135,1134,1224,1218,1175,1169),(76,'2023-12-17 16:32:38',1176,1119,1051,1155,1211,1209,1146,1283,1143,1140,1228,1226,1183,1178),(76,'2023-12-17 16:42:38',1182,1129,1056,1157,1220,1211,1154,1289,1147,1149,1230,1228,1192,1179),(76,'2023-12-17 16:52:38',1185,1139,1059,1162,1229,1220,1157,1291,1154,1157,1238,1234,1199,1189),(76,'2023-12-17 17:02:38',1193,1148,1069,1165,1231,1224,1164,1292,1157,1162,1248,1243,1205,1192),(76,'2023-12-17 17:12:38',1202,1156,1077,1174,1233,1232,1169,1300,1157,1162,1251,1252,1215,1195),(76,'2023-12-17 17:22:38',1203,1162,1082,1180,1242,1241,1174,1306,1159,1166,1257,1262,1218,1204),(76,'2023-12-17 17:32:38',1211,1169,1092,1184,1252,1246,1182,1313,1164,1173,1267,1270,1221,1207),(76,'2023-12-17 17:42:38',1219,1172,1095,1192,1258,1251,1190,1314,1174,1180,1273,1275,1231,1212),(76,'2023-12-17 17:52:38',1225,1177,1103,1196,1264,1253,1199,1317,1182,1190,1275,1282,1237,1218),(76,'2023-12-17 18:02:38',1233,1184,1111,1204,1270,1259,1208,1327,1188,1197,1281,1292,1247,1220),(76,'2023-12-17 18:12:38',1242,1190,1120,1214,1277,1262,1210,1335,1194,1204,1285,1302,1253,1226),(76,'2023-12-17 18:22:38',1250,1196,1128,1215,1279,1265,1217,1338,1197,1212,1288,1312,1262,1230),(76,'2023-12-17 18:32:38',1254,1205,1130,1219,1281,1271,1220,1338,1202,1220,1296,1312,1264,1239),(76,'2023-12-17 18:42:38',1260,1214,1134,1228,1287,1271,1224,1343,1206,1230,1300,1321,1264,1240),(76,'2023-12-17 18:52:38',1264,1223,1139,1232,1291,1275,1234,1346,1215,1240,1309,1330,1273,1249),(76,'2023-12-17 19:02:38',1267,1229,1147,1237,1296,1284,1236,1351,1222,1249,1317,1334,1283,1252),(76,'2023-12-17 19:12:38',1276,1238,1155,1245,1302,1292,1244,1357,1226,1253,1324,1340,1292,1254),(76,'2023-12-17 19:22:38',1284,1243,1158,1247,1311,1301,1248,1364,1233,1263,1329,1344,1301,1258),(76,'2023-12-17 19:32:38',1291,1252,1158,1254,1318,1305,1252,1373,1233,1271,1334,1354,1305,1261),(76,'2023-12-17 19:42:38',1298,1262,1166,1260,1326,1309,1259,1379,1234,1279,1336,1360,1307,1265),(76,'2023-12-17 19:52:38',1307,1271,1170,1261,1336,1316,1268,1386,1241,1284,1345,1362,1316,1271),(76,'2023-12-17 20:02:38',1310,1275,1173,1266,1343,1326,1276,1387,1251,1288,1355,1367,1323,1272),(76,'2023-12-17 20:12:38',1319,1279,1177,1267,1344,1330,1283,1397,1252,1293,1361,1373,1326,1282),(76,'2023-12-17 20:22:38',1328,1289,1180,1271,1353,1333,1292,1404,1259,1299,1366,1382,1334,1289),(76,'2023-12-17 20:32:38',1337,1299,1184,1280,1360,1340,1302,1412,1267,1308,1369,1386,1342,1297),(76,'2023-12-17 20:52:38',1345,1310,1197,1296,1370,1359,1322,1425,1282,1317,1378,1399,1353,1310),(76,'2023-12-17 21:02:38',1350,1311,1205,1301,1374,1367,1332,1434,1287,1320,1384,1409,1360,1314),(76,'2023-12-17 21:12:38',1359,1318,1214,1307,1378,1369,1342,1444,1288,1322,1394,1414,1361,1323),(76,'2023-12-17 21:22:38',1363,1326,1220,1314,1383,1379,1350,1452,1294,1326,1401,1418,1368,1327),(76,'2023-12-17 21:32:38',1364,1336,1223,1317,1387,1387,1357,1456,1302,1329,1411,1419,1378,1336),(76,'2023-12-17 21:42:38',1373,1344,1228,1319,1389,1395,1365,1466,1309,1334,1418,1425,1383,1343),(76,'2023-12-17 21:52:38',1376,1345,1238,1325,1392,1403,1375,1470,1318,1340,1421,1428,1384,1346),(76,'2023-12-17 22:02:38',1383,1354,1248,1335,1395,1408,1382,1477,1325,1342,1423,1431,1393,1354),(76,'2023-12-17 22:12:38',1389,1364,1257,1339,1400,1415,1387,1483,1329,1351,1432,1436,1398,1358),(76,'2023-12-17 22:22:38',1399,1372,1261,1347,1409,1425,1393,1489,1337,1353,1434,1436,1408,1360),(76,'2023-12-17 22:32:38',1403,1380,1266,1352,1414,1429,1403,1498,1345,1363,1438,1437,1413,1362),(76,'2023-12-17 22:42:38',1412,1390,1273,1361,1423,1436,1405,1504,1349,1366,1446,1442,1417,1368),(76,'2023-12-17 22:52:38',1418,1400,1282,1362,1431,1444,1407,1508,1352,1376,1455,1445,1423,1374),(76,'2023-12-17 23:02:38',1426,1408,1282,1370,1438,1445,1414,1510,1361,1382,1460,1453,1433,1376),(76,'2023-12-17 23:12:38',1436,1411,1287,1374,1448,1449,1416,1514,1369,1390,1465,1459,1442,1384),(76,'2023-12-17 23:22:38',1442,1418,1295,1384,1457,1451,1424,1520,1378,1397,1473,1463,1447,1392),(76,'2023-12-17 23:32:38',1450,1424,1304,1389,1461,1460,1434,1529,1384,1406,1480,1472,1449,1395),(76,'2023-12-17 23:42:38',1457,1432,1313,1392,1465,1470,1440,1536,1392,1407,1488,1475,1450,1399),(76,'2023-12-17 23:52:38',1465,1437,1313,1394,1472,1472,1450,1539,1399,1417,1496,1481,1452,1406),(76,'2023-12-18 00:02:38',1470,1442,1316,1394,1474,1472,1460,1548,1409,1425,1505,1485,1459,1411),(76,'2023-12-18 00:12:38',10,3,9,4,11,3,6,12,6,8,3,9,12,7),(76,'2023-12-18 00:22:38',14,7,14,9,13,13,11,20,16,11,12,16,19,12),(76,'2023-12-18 00:32:38',21,7,24,16,19,20,18,26,25,16,19,25,26,12),(76,'2023-12-18 00:42:38',21,9,25,22,24,30,28,32,28,22,24,26,34,22),(76,'2023-12-18 00:52:38',29,15,31,32,27,37,31,40,36,26,32,34,42,27),(76,'2023-12-18 01:02:38',36,18,33,41,36,45,36,44,46,31,34,36,48,36),(76,'2023-12-18 01:12:38',40,27,34,42,42,55,45,54,54,31,40,40,52,42),(76,'2023-12-18 01:22:38',43,36,41,48,50,58,52,62,64,35,48,45,60,44),(76,'2023-12-18 01:32:38',53,44,50,57,60,66,55,71,74,39,51,49,62,47),(76,'2023-12-18 01:42:38',62,54,56,66,63,76,60,77,83,39,59,55,72,49),(76,'2023-12-18 01:52:38',64,54,62,69,71,86,67,82,89,40,65,65,76,59),(76,'2023-12-18 02:02:38',70,59,63,78,78,88,71,91,99,45,73,70,80,64),(76,'2023-12-18 02:22:38',77,70,79,97,93,107,86,108,103,47,78,80,91,82),(76,'2023-12-18 02:32:38',83,78,85,107,99,109,94,115,105,54,78,82,101,92),(76,'2023-12-18 02:42:38',91,80,93,112,107,118,100,118,106,58,82,92,102,98),(76,'2023-12-18 02:52:38',95,90,103,119,112,124,109,126,106,66,87,97,109,108),(76,'2023-12-18 03:02:38',100,93,109,120,119,130,109,128,112,67,95,102,117,114),(76,'2023-12-18 03:12:38',104,100,115,125,126,138,114,138,120,74,104,107,119,120),(76,'2023-12-18 03:22:38',114,108,125,135,130,146,117,140,124,80,107,111,126,128),(76,'2023-12-18 03:32:38',118,114,131,137,133,153,123,148,131,88,110,113,129,134),(76,'2023-12-18 03:42:38',125,119,136,143,141,161,130,152,133,96,117,123,134,139),(76,'2023-12-18 03:52:38',132,123,141,151,143,171,140,158,140,96,120,127,143,146),(76,'2023-12-18 04:02:38',142,128,145,156,151,177,150,164,144,104,130,131,151,153),(76,'2023-12-18 04:12:38',145,135,145,165,154,179,153,171,147,112,136,136,152,162),(76,'2023-12-18 04:22:38',153,142,153,172,154,189,160,173,152,122,140,145,162,163),(76,'2023-12-18 04:32:38',159,152,161,178,164,197,161,177,161,129,146,147,167,170),(76,'2023-12-18 04:42:38',168,156,167,181,168,204,164,181,165,134,149,150,177,175),(76,'2023-12-18 04:52:38',170,161,176,190,169,214,167,190,167,137,151,156,185,183),(76,'2023-12-18 05:02:38',179,163,185,199,173,217,171,193,175,146,157,163,192,193),(76,'2023-12-18 05:12:38',186,169,192,207,182,218,178,198,184,152,157,167,193,195),(76,'2023-12-18 05:22:38',190,178,195,213,189,220,179,203,187,155,161,176,198,199),(76,'2023-12-18 05:32:38',194,188,197,216,192,225,187,211,191,160,171,181,202,207),(76,'2023-12-18 05:42:38',202,197,204,219,202,235,197,218,195,163,177,191,212,215),(76,'2023-12-18 05:52:38',212,200,210,225,204,240,200,222,204,170,179,200,215,225),(76,'2023-12-18 06:02:38',217,208,214,232,213,249,205,231,212,179,189,202,219,233),(76,'2023-12-18 06:12:38',227,216,223,233,218,257,209,236,216,185,197,212,223,241),(76,'2023-12-18 06:22:38',230,226,225,243,222,267,212,244,226,193,198,220,233,244),(76,'2023-12-18 06:32:38',231,233,229,250,228,275,222,252,233,197,201,221,243,246),(76,'2023-12-18 06:42:38',239,240,233,256,231,285,226,254,237,202,210,231,250,252),(76,'2023-12-18 06:52:38',241,246,241,265,240,295,232,259,245,211,218,238,253,256),(76,'2023-12-18 07:02:38',248,249,247,275,241,305,237,261,250,220,226,246,256,261),(76,'2023-12-18 07:12:38',257,253,249,281,249,311,239,266,256,227,232,250,263,262),(76,'2023-12-18 07:22:38',267,257,249,285,257,315,249,266,260,230,241,259,265,272),(76,'2023-12-18 07:32:38',275,266,253,288,267,323,259,269,260,239,250,261,269,281),(76,'2023-12-18 07:42:38',285,276,259,296,275,333,264,277,265,249,260,271,276,291),(76,'2023-12-18 07:52:38',293,280,265,303,280,335,273,283,274,249,262,280,285,301),(76,'2023-12-18 08:02:38',302,285,270,308,284,344,281,291,283,251,270,283,288,308),(76,'2023-12-18 08:12:38',311,286,273,317,289,349,285,301,289,260,277,285,289,316),(76,'2023-12-18 08:22:38',319,295,282,327,295,359,295,311,291,269,285,295,294,321),(76,'2023-12-18 08:32:38',326,301,282,337,299,364,299,315,296,279,289,303,301,330),(76,'2023-12-18 08:42:38',328,311,287,342,307,373,306,318,303,286,294,303,309,336),(76,'2023-12-18 08:52:38',333,318,294,352,314,382,313,322,307,288,301,310,318,338),(76,'2023-12-18 09:02:38',334,328,300,355,317,388,316,326,317,295,310,315,326,347),(76,'2023-12-18 09:12:38',343,333,303,362,322,394,318,333,325,304,312,323,327,350),(76,'2023-12-18 09:22:38',346,335,311,366,332,398,321,339,328,306,319,330,332,358),(76,'2023-12-18 09:32:38',352,343,311,372,340,401,322,349,334,312,324,340,332,359),(76,'2023-12-18 09:42:38',357,349,321,377,349,407,329,352,344,315,330,349,339,367),(76,'2023-12-18 09:52:38',365,357,326,385,359,409,339,361,354,320,336,358,345,370),(76,'2023-12-18 10:02:38',371,360,334,392,361,414,348,367,356,324,336,366,353,375),(76,'2023-12-18 10:12:38',377,367,344,401,370,424,356,369,365,324,344,370,359,383),(76,'2023-12-18 10:22:38',387,375,349,404,375,434,366,378,367,334,351,375,366,390),(76,'2023-12-18 10:32:38',388,383,358,411,379,443,374,386,369,336,361,376,374,395),(76,'2023-12-18 10:42:38',398,393,362,419,389,448,378,395,373,343,369,384,384,404),(76,'2023-12-18 10:52:38',408,397,371,428,397,458,379,396,375,349,377,386,394,413),(76,'2023-12-18 11:02:38',410,403,373,432,400,466,381,402,382,355,382,393,397,423),(76,'2023-12-18 11:12:38',414,409,383,440,404,469,388,407,384,365,385,398,401,427),(76,'2023-12-18 11:22:38',419,417,389,441,414,475,398,411,385,372,395,408,410,433),(76,'2023-12-18 11:32:38',419,426,399,447,415,482,402,420,391,377,402,409,416,441),(76,'2023-12-18 11:52:38',430,440,413,462,427,488,408,432,400,388,421,422,424,453),(76,'2023-12-18 12:02:38',440,446,414,467,435,492,417,435,410,390,423,431,431,461),(76,'2023-12-18 12:12:38',450,451,417,473,445,498,425,442,418,399,424,433,439,466),(76,'2023-12-18 12:22:38',452,461,425,477,455,502,435,451,424,406,425,441,443,474),(76,'2023-12-18 12:32:38',456,468,427,487,461,512,441,460,429,415,431,444,453,480),(76,'2023-12-18 12:52:38',470,485,437,507,469,520,458,475,442,421,447,464,463,492),(76,'2023-12-18 13:02:38',479,485,441,515,475,530,459,480,448,424,450,473,471,494),(76,'2023-12-18 13:12:38',484,492,447,524,483,531,468,490,456,433,454,480,479,504),(76,'2023-12-18 13:22:38',491,499,451,528,488,534,471,492,460,436,463,486,483,504),(76,'2023-12-18 13:32:38',491,503,459,538,495,543,477,502,470,446,469,489,489,514),(76,'2023-12-18 13:42:38',495,511,461,541,503,544,483,510,478,455,470,497,499,516),(76,'2023-12-18 13:52:38',500,514,462,541,505,551,489,516,479,462,473,501,506,521),(76,'2023-12-18 14:02:38',503,524,470,543,511,558,489,525,484,464,483,504,511,526),(76,'2023-12-18 14:12:38',512,525,476,550,516,568,499,535,490,471,484,509,519,530),(76,'2023-12-18 14:22:38',522,526,477,554,523,568,509,539,495,475,490,518,529,539),(76,'2023-12-18 14:32:38',528,526,486,562,530,574,509,542,500,483,498,528,534,545),(76,'2023-12-18 14:42:38',538,536,493,571,531,582,513,551,509,489,507,538,542,545),(76,'2023-12-18 14:52:38',541,540,497,575,540,588,518,551,514,495,514,540,548,552),(76,'2023-12-18 15:02:38',546,542,499,583,544,597,519,559,521,496,521,546,557,557),(76,'2023-12-18 15:12:38',553,552,500,585,550,601,520,560,525,505,529,550,562,560),(76,'2023-12-18 15:22:38',559,561,508,593,555,610,530,569,529,513,536,558,568,564),(76,'2023-12-18 15:32:38',568,570,512,603,558,620,536,573,536,518,542,560,578,566),(76,'2023-12-18 15:42:38',574,573,522,610,567,627,536,578,545,524,545,561,581,574),(76,'2023-12-18 16:02:38',587,589,534,620,585,647,550,589,557,534,555,562,596,588),(76,'2023-12-18 16:12:38',597,597,544,627,591,657,557,597,563,538,561,572,604,590),(76,'2023-12-18 16:22:38',602,604,550,635,593,667,564,602,572,543,568,574,609,597),(76,'2023-12-18 16:32:38',604,611,555,640,595,677,572,602,580,553,578,580,612,599),(76,'2023-12-18 16:42:38',604,621,564,643,603,685,577,611,589,558,578,590,621,606),(76,'2023-12-18 16:52:38',607,627,574,652,611,693,583,617,598,568,579,590,626,613),(76,'2023-12-18 17:02:38',615,636,576,662,618,703,589,627,602,573,589,592,635,620),(76,'2023-12-18 17:12:38',623,636,579,671,621,710,598,635,607,574,592,601,638,627),(76,'2023-12-18 17:22:38',633,643,583,679,629,719,601,639,611,582,599,602,646,629),(76,'2023-12-18 17:32:38',638,645,593,688,632,724,605,641,619,586,606,607,653,630),(76,'2023-12-18 17:42:38',642,654,596,698,639,727,615,649,622,590,609,611,661,639),(76,'2023-12-18 17:52:38',649,664,604,705,649,735,625,651,624,597,614,619,667,647),(76,'2023-12-18 18:02:38',657,670,609,707,654,745,634,652,625,600,621,622,670,650),(76,'2023-12-18 18:22:38',667,686,621,717,657,760,638,655,645,613,627,624,683,655),(76,'2023-12-18 18:32:38',676,691,627,722,662,770,645,660,651,618,633,634,687,658),(76,'2023-12-18 18:42:38',685,700,632,724,667,771,648,666,655,628,638,641,688,664),(76,'2023-12-18 18:52:38',695,710,641,727,671,772,654,676,664,636,647,643,693,667),(76,'2023-12-18 19:02:38',704,713,650,734,677,778,658,680,667,641,653,648,693,674),(76,'2023-12-18 19:12:38',713,722,655,744,680,780,668,690,672,643,660,658,702,677),(76,'2023-12-18 19:22:38',718,727,659,744,684,787,676,696,680,650,660,668,711,686),(76,'2023-12-18 19:32:38',727,736,661,753,692,791,681,705,684,650,664,672,720,696),(76,'2023-12-18 19:42:38',730,738,667,754,702,796,685,708,687,658,670,676,725,703),(76,'2023-12-18 19:52:38',737,738,672,755,706,797,688,710,696,667,671,681,728,711),(76,'2023-12-18 20:02:38',745,747,682,765,711,807,695,715,696,675,677,689,738,715),(76,'2023-12-18 20:22:38',758,764,689,774,717,822,703,729,704,689,694,701,756,722),(76,'2023-12-18 20:32:38',765,774,697,774,721,831,707,739,707,699,702,711,765,728),(76,'2023-12-18 20:42:38',775,784,704,782,724,841,709,747,711,706,705,716,775,737),(76,'2023-12-18 20:52:38',785,785,713,787,734,841,712,747,721,708,708,723,785,747),(76,'2023-12-18 21:02:38',791,790,717,791,739,851,718,756,728,715,716,732,788,754),(76,'2023-12-18 21:12:38',795,796,725,791,749,856,719,760,732,718,726,737,791,756),(76,'2023-12-18 21:22:38',805,806,731,795,755,862,729,762,742,727,729,741,800,763),(76,'2023-12-18 21:32:38',810,814,741,805,763,865,736,766,749,731,729,751,807,764),(76,'2023-12-18 21:42:38',813,824,744,809,764,874,739,772,751,732,736,751,814,768),(76,'2023-12-18 21:52:38',820,829,750,816,772,877,742,777,754,742,743,752,817,778),(76,'2023-12-18 22:02:38',829,837,755,825,773,884,750,782,756,745,752,759,825,783),(76,'2023-12-18 22:12:38',836,839,763,832,783,894,757,792,762,748,759,768,832,792),(76,'2023-12-18 22:22:38',846,843,764,838,792,899,763,800,772,752,763,772,837,794),(76,'2023-12-18 22:32:38',852,853,772,848,793,907,771,802,776,758,770,775,844,803),(76,'2023-12-18 22:42:38',862,862,778,857,799,911,777,807,777,764,775,776,849,808),(76,'2023-12-18 22:52:38',868,871,788,862,809,919,782,815,782,772,778,780,855,815),(76,'2023-12-18 23:12:38',884,883,804,869,819,931,796,828,794,784,796,781,864,831),(76,'2023-12-18 23:22:38',894,886,812,871,823,940,798,838,797,791,804,788,873,839),(76,'2023-12-18 23:32:38',894,890,817,876,828,945,805,848,801,800,812,798,881,843),(76,'2023-12-18 23:42:38',901,898,826,876,830,954,810,854,803,810,822,804,886,845),(76,'2023-12-18 23:52:38',905,906,834,886,836,962,811,862,809,813,828,806,895,853),(76,'2023-12-19 00:02:38',915,915,839,895,841,969,817,865,819,822,835,811,905,861),(76,'2023-12-19 00:12:38',919,924,845,903,845,979,817,871,826,830,838,818,911,866),(76,'2023-12-19 00:32:38',929,936,857,914,852,991,829,877,838,836,853,837,925,885),(76,'2023-12-19 00:42:38',936,937,863,918,858,1000,838,881,841,844,857,841,928,887),(76,'2023-12-19 00:52:38',946,941,871,927,860,1005,846,890,850,853,858,849,934,887),(76,'2023-12-19 01:02:38',953,942,873,933,865,1011,854,895,852,861,866,856,943,888),(76,'2023-12-19 01:12:38',954,943,881,942,867,1016,863,901,855,866,872,857,947,894),(76,'2023-12-19 01:32:38',967,951,891,955,872,1033,868,914,866,883,878,872,964,901),(76,'2023-12-19 01:42:38',975,959,899,961,875,1035,873,918,875,887,888,873,972,907),(76,'2023-12-19 01:52:38',980,963,900,971,880,1045,880,924,884,897,891,879,980,911),(76,'2023-12-19 02:02:38',984,973,909,980,881,1055,884,934,891,906,900,887,986,919),(76,'2023-12-19 02:12:38',990,978,912,986,890,1060,889,944,901,910,910,894,995,925),(76,'2023-12-19 02:22:38',995,986,918,996,895,1064,899,945,904,916,915,900,1000,932),(76,'2023-12-19 02:32:38',1005,992,927,1002,901,1067,909,951,912,924,925,909,1008,938),(76,'2023-12-19 02:42:38',1014,1001,937,1012,908,1073,913,961,914,933,930,913,1013,941),(76,'2023-12-19 02:52:38',1021,1009,944,1021,915,1080,919,970,923,940,934,923,1020,945),(76,'2023-12-19 03:02:38',1026,1011,951,1024,925,1087,919,976,930,942,940,932,1024,946),(76,'2023-12-19 03:12:38',1032,1016,957,1027,925,1092,923,985,931,951,944,941,1033,952),(76,'2023-12-19 03:32:38',1042,1024,962,1042,936,1105,929,992,937,969,964,954,1044,963),(76,'2023-12-19 03:42:38',1044,1034,970,1046,944,1111,938,997,947,977,973,957,1048,964),(76,'2023-12-19 03:52:38',1054,1036,980,1054,951,1114,947,1003,955,983,983,967,1052,968),(76,'2023-12-19 04:02:38',1062,1045,987,1063,957,1122,955,1003,963,988,993,975,1056,977),(76,'2023-12-19 04:12:38',1065,1055,988,1070,961,1132,964,1013,970,989,994,982,1056,981),(76,'2023-12-19 04:22:38',1069,1057,993,1074,965,1133,969,1017,978,995,995,992,1062,991),(76,'2023-12-19 04:32:38',1076,1060,998,1082,971,1137,978,1022,978,997,1004,996,1069,991),(76,'2023-12-19 04:42:38',1083,1062,1005,1090,979,1142,986,1030,988,1007,1007,1000,1076,998),(76,'2023-12-19 05:02:38',1098,1075,1013,1104,992,1154,999,1045,1004,1011,1024,1014,1085,1012),(76,'2023-12-19 05:12:38',1106,1079,1018,1111,998,1164,1003,1051,1014,1015,1030,1022,1094,1016),(76,'2023-12-19 05:22:38',1116,1083,1025,1118,1005,1166,1012,1057,1017,1018,1039,1030,1100,1023),(76,'2023-12-19 05:32:38',1118,1091,1032,1124,1007,1171,1020,1066,1021,1026,1048,1039,1104,1030),(76,'2023-12-19 05:42:38',1122,1096,1039,1134,1015,1177,1025,1071,1031,1027,1050,1048,1112,1035),(76,'2023-12-19 06:02:38',1137,1103,1052,1141,1021,1195,1044,1080,1047,1037,1066,1060,1123,1040),(76,'2023-12-19 06:12:38',1143,1104,1062,1145,1026,1201,1050,1081,1054,1043,1076,1064,1127,1042),(76,'2023-12-19 06:22:38',1150,1107,1063,1155,1028,1209,1056,1088,1064,1049,1085,1073,1135,1045),(76,'2023-12-19 06:32:38',1157,1113,1064,1162,1029,1219,1066,1097,1073,1053,1093,1079,1136,1054),(76,'2023-12-19 06:42:38',1161,1119,1073,1169,1034,1224,1075,1104,1083,1060,1098,1088,1141,1054),(76,'2023-12-19 06:52:38',1170,1120,1080,1176,1041,1229,1081,1106,1091,1070,1108,1097,1149,1059),(76,'2023-12-19 07:02:38',1177,1129,1081,1186,1047,1237,1089,1114,1101,1079,1113,1106,1157,1060),(76,'2023-12-19 07:12:38',1180,1133,1084,1191,1050,1242,1094,1120,1103,1082,1121,1115,1159,1069),(76,'2023-12-19 07:22:38',1190,1138,1091,1192,1054,1246,1102,1130,1112,1092,1129,1125,1166,1077),(76,'2023-12-19 07:32:38',1195,1145,1094,1193,1064,1256,1112,1136,1119,1098,1131,1134,1167,1084),(76,'2023-12-19 07:42:38',1201,1154,1100,1203,1067,1259,1121,1139,1127,1108,1139,1136,1173,1085),(76,'2023-12-19 07:52:38',1211,1158,1107,1210,1075,1260,1128,1141,1131,1116,1148,1146,1181,1094),(76,'2023-12-19 08:02:38',1221,1168,1111,1214,1081,1262,1131,1149,1140,1124,1154,1155,1190,1103),(76,'2023-12-19 08:12:38',1231,1172,1119,1220,1082,1271,1140,1159,1150,1131,1163,1160,1192,1111),(76,'2023-12-19 08:22:38',1240,1176,1125,1227,1088,1277,1150,1168,1150,1131,1173,1161,1200,1119),(76,'2023-12-19 08:32:38',1243,1176,1134,1235,1088,1287,1151,1170,1155,1136,1175,1165,1209,1123),(76,'2023-12-19 08:42:38',1253,1186,1140,1240,1095,1295,1158,1178,1160,1142,1180,1169,1215,1132);
/*!40000 ALTER TABLE `supla_em_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_em_voltage_log`
--

DROP TABLE IF EXISTS `supla_em_voltage_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_em_voltage_log` (
  `channel_id` int(11) NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:stringdatetime)',
  `phase_no` tinyint(4) NOT NULL COMMENT '(DC2Type:tinyint)',
  `count_total` int(11) NOT NULL,
  `count_above` int(11) NOT NULL,
  `count_below` int(11) NOT NULL,
  `sec_above` int(11) NOT NULL,
  `sec_below` int(11) NOT NULL,
  `max_sec_above` int(11) NOT NULL,
  `max_sec_below` int(11) NOT NULL,
  `min_voltage` decimal(7,2) NOT NULL,
  `max_voltage` decimal(7,2) NOT NULL,
  `avg_voltage` decimal(7,2) NOT NULL,
  `measurement_time_sec` int(11) NOT NULL,
  PRIMARY KEY (`channel_id`,`date`,`phase_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_em_voltage_log`
--

LOCK TABLES `supla_em_voltage_log` WRITE;
/*!40000 ALTER TABLE `supla_em_voltage_log` DISABLE KEYS */;
INSERT INTO `supla_em_voltage_log` VALUES (76,'2023-12-17 03:32:38',3,2,2,0,83,0,61,0,230.00,263.51,245.16,601),(76,'2023-12-17 04:32:38',3,9,9,0,268,0,104,0,231.77,265.24,237.98,597),(76,'2023-12-17 07:52:38',2,10,6,4,76,270,5,69,229.73,265.12,251.77,599),(76,'2023-12-17 09:52:38',1,4,0,4,0,273,0,144,220.37,239.35,220.68,604),(76,'2023-12-17 13:22:38',1,4,0,4,0,121,0,99,225.94,234.10,226.96,604),(76,'2023-12-17 16:12:38',3,10,10,0,171,0,47,0,233.90,252.68,248.72,599),(76,'2023-12-17 22:32:38',3,3,3,0,129,0,120,0,234.45,262.38,252.13,603),(76,'2023-12-18 13:52:38',3,3,0,3,0,171,0,138,225.05,237.50,233.94,598),(76,'2023-12-18 22:12:38',1,7,7,0,9,0,7,0,233.60,254.70,247.66,602),(76,'2023-12-19 00:02:38',1,1,1,0,263,0,263,0,233.21,264.10,245.90,602);
/*!40000 ALTER TABLE `supla_em_voltage_log` ENABLE KEYS */;
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
INSERT INTO `supla_ic_log` VALUES (77,'2023-12-16 08:52:37',5,50),(77,'2023-12-16 09:02:37',7,70),(77,'2023-12-16 09:12:37',14,140),(77,'2023-12-16 09:22:37',22,220),(77,'2023-12-16 09:32:37',28,280),(77,'2023-12-16 09:42:37',36,360),(77,'2023-12-16 09:52:37',44,440),(77,'2023-12-16 10:02:37',48,480),(77,'2023-12-16 10:12:37',50,500),(77,'2023-12-16 10:22:37',52,520),(77,'2023-12-16 10:32:37',58,580),(77,'2023-12-16 10:42:37',63,630),(77,'2023-12-16 11:02:37',79,790),(77,'2023-12-16 11:12:37',86,860),(77,'2023-12-16 11:22:37',94,940),(77,'2023-12-16 11:32:37',95,950),(77,'2023-12-16 11:42:37',103,1030),(77,'2023-12-16 11:52:37',110,1100),(77,'2023-12-16 12:02:37',118,1180),(77,'2023-12-16 12:12:37',126,1260),(77,'2023-12-16 12:22:37',133,1330),(77,'2023-12-16 12:32:37',136,1360),(77,'2023-12-16 12:42:37',143,1430),(77,'2023-12-16 12:52:37',153,1530),(77,'2023-12-16 13:02:37',159,1590),(77,'2023-12-16 13:12:37',162,1620),(77,'2023-12-16 13:22:37',169,1690),(77,'2023-12-16 13:32:37',178,1780),(77,'2023-12-16 13:42:37',182,1820),(77,'2023-12-16 13:52:37',189,1890),(77,'2023-12-16 14:02:37',195,1950),(77,'2023-12-16 14:12:37',196,1960),(77,'2023-12-16 14:22:37',197,1970),(77,'2023-12-16 14:32:37',207,2070),(77,'2023-12-16 14:42:37',208,2080),(77,'2023-12-16 14:52:37',213,2130),(77,'2023-12-16 15:02:37',216,2160),(77,'2023-12-16 15:12:37',223,2230),(77,'2023-12-16 15:22:37',224,2240),(77,'2023-12-16 15:32:37',234,2340),(77,'2023-12-16 15:42:37',240,2400),(77,'2023-12-16 15:52:37',247,2470),(77,'2023-12-16 16:02:37',249,2490),(77,'2023-12-16 16:12:37',250,2500),(77,'2023-12-16 16:22:37',253,2530),(77,'2023-12-16 16:32:37',259,2590),(77,'2023-12-16 16:42:37',264,2640),(77,'2023-12-16 16:52:37',271,2710),(77,'2023-12-16 17:02:37',276,2760),(77,'2023-12-16 17:12:37',286,2860),(77,'2023-12-16 17:22:37',292,2920),(77,'2023-12-16 17:32:37',295,2950),(77,'2023-12-16 17:42:37',305,3050),(77,'2023-12-16 17:52:37',307,3070),(77,'2023-12-16 18:02:37',311,3110),(77,'2023-12-16 18:12:37',319,3190),(77,'2023-12-16 18:22:37',320,3200),(77,'2023-12-16 18:32:37',329,3290),(77,'2023-12-16 18:42:37',334,3340),(77,'2023-12-16 18:52:37',342,3420),(77,'2023-12-16 19:02:37',352,3520),(77,'2023-12-16 19:12:37',357,3570),(77,'2023-12-16 19:22:37',360,3600),(77,'2023-12-16 19:32:37',369,3690),(77,'2023-12-16 19:42:37',373,3730),(77,'2023-12-16 20:02:37',389,3890),(77,'2023-12-16 20:12:37',393,3930),(77,'2023-12-16 20:22:37',399,3990),(77,'2023-12-16 20:32:37',406,4060),(77,'2023-12-16 20:42:37',413,4130),(77,'2023-12-16 20:52:37',420,4200),(77,'2023-12-16 21:02:37',429,4290),(77,'2023-12-16 21:12:37',439,4390),(77,'2023-12-16 21:32:37',443,4430),(77,'2023-12-16 21:42:37',452,4520),(77,'2023-12-16 21:52:37',456,4560),(77,'2023-12-16 22:02:37',465,4650),(77,'2023-12-16 22:12:37',472,4720),(77,'2023-12-16 22:22:37',475,4750),(77,'2023-12-16 22:32:37',482,4820),(77,'2023-12-16 22:42:37',492,4920),(77,'2023-12-16 22:52:37',498,4980),(77,'2023-12-16 23:02:37',506,5060),(77,'2023-12-16 23:12:37',515,5150),(77,'2023-12-16 23:22:37',523,5230),(77,'2023-12-16 23:32:37',524,5240),(77,'2023-12-16 23:42:37',531,5310),(77,'2023-12-16 23:52:37',533,5330),(77,'2023-12-17 00:02:37',534,5340),(77,'2023-12-17 00:12:37',539,5390),(77,'2023-12-17 00:22:37',544,5440),(77,'2023-12-17 00:32:37',554,5540),(77,'2023-12-17 00:42:37',562,5620),(77,'2023-12-17 00:52:37',568,5680),(77,'2023-12-17 01:02:37',571,5710),(77,'2023-12-17 01:12:37',580,5800),(77,'2023-12-17 01:22:37',583,5830),(77,'2023-12-17 01:32:37',588,5880),(77,'2023-12-17 01:42:37',592,5920),(77,'2023-12-17 01:52:37',601,6010),(77,'2023-12-17 02:02:37',607,6070),(77,'2023-12-17 02:12:37',609,6090),(77,'2023-12-17 02:22:37',613,6130),(77,'2023-12-17 02:32:37',613,6130),(77,'2023-12-17 02:42:37',617,6170),(77,'2023-12-17 02:52:37',624,6240),(77,'2023-12-17 03:02:37',629,6290),(77,'2023-12-17 03:12:37',636,6360),(77,'2023-12-17 03:22:37',637,6370),(77,'2023-12-17 03:32:37',644,6440),(77,'2023-12-17 03:52:37',655,6550),(77,'2023-12-17 04:02:37',659,6590),(77,'2023-12-17 04:12:37',669,6690),(77,'2023-12-17 04:22:37',675,6750),(77,'2023-12-17 04:32:37',682,6820),(77,'2023-12-17 04:42:37',685,6850),(77,'2023-12-17 04:52:37',686,6860),(77,'2023-12-17 05:12:37',701,7010),(77,'2023-12-17 05:22:37',704,7040),(77,'2023-12-17 05:32:37',712,7120),(77,'2023-12-17 05:42:37',720,7200),(77,'2023-12-17 05:52:37',730,7300),(77,'2023-12-17 06:02:37',733,7330),(77,'2023-12-17 06:12:37',743,7430),(77,'2023-12-17 06:22:37',747,7470),(77,'2023-12-17 06:32:37',751,7510),(77,'2023-12-17 06:42:37',759,7590),(77,'2023-12-17 06:52:37',761,7610),(77,'2023-12-17 07:02:37',764,7640),(77,'2023-12-17 07:22:37',773,7730),(77,'2023-12-17 07:32:37',775,7750),(77,'2023-12-17 07:42:37',781,7810),(77,'2023-12-17 07:52:37',785,7850),(77,'2023-12-17 08:02:37',794,7940),(77,'2023-12-17 08:12:37',799,7990),(77,'2023-12-17 08:22:37',802,8020),(77,'2023-12-17 08:32:37',806,8060),(77,'2023-12-17 08:42:37',809,8090),(77,'2023-12-17 08:52:37',811,8110),(77,'2023-12-17 09:02:37',821,8210),(77,'2023-12-17 09:12:37',829,8290),(77,'2023-12-17 09:22:37',838,8380),(77,'2023-12-17 09:32:37',847,8470),(77,'2023-12-17 09:52:37',859,8590),(77,'2023-12-17 10:02:37',862,8620),(77,'2023-12-17 10:12:37',871,8710),(77,'2023-12-17 10:22:37',880,8800),(77,'2023-12-17 10:32:37',888,8880),(77,'2023-12-17 10:52:37',905,9050),(77,'2023-12-17 11:02:37',912,9120),(77,'2023-12-17 11:12:37',921,9210),(77,'2023-12-17 11:22:37',927,9270),(77,'2023-12-17 11:32:37',937,9370),(77,'2023-12-17 11:42:37',943,9430),(77,'2023-12-17 11:52:37',952,9520),(77,'2023-12-17 12:02:37',958,9580),(77,'2023-12-17 12:12:37',963,9630),(77,'2023-12-17 12:22:37',968,9680),(77,'2023-12-17 12:32:37',970,9700),(77,'2023-12-17 12:42:37',976,9760),(77,'2023-12-17 12:52:37',986,9860),(77,'2023-12-17 13:02:37',991,9910),(77,'2023-12-17 13:12:37',9,90),(77,'2023-12-17 13:22:37',13,130),(77,'2023-12-17 13:32:37',18,180),(77,'2023-12-17 13:42:37',19,190),(77,'2023-12-17 13:52:37',26,260),(77,'2023-12-17 14:02:37',32,320),(77,'2023-12-17 14:12:37',42,420),(77,'2023-12-17 14:22:37',52,520),(77,'2023-12-17 14:32:37',58,580),(77,'2023-12-17 14:42:37',61,610),(77,'2023-12-17 14:52:37',69,690),(77,'2023-12-17 15:02:37',72,720),(77,'2023-12-17 15:12:37',80,800),(77,'2023-12-17 15:22:37',84,840),(77,'2023-12-17 15:32:37',92,920),(77,'2023-12-17 15:42:37',94,940),(77,'2023-12-17 15:52:37',104,1040),(77,'2023-12-17 16:02:37',104,1040),(77,'2023-12-17 16:12:37',105,1050),(77,'2023-12-17 16:22:37',109,1090),(77,'2023-12-17 16:32:37',113,1130),(77,'2023-12-17 16:42:37',121,1210),(77,'2023-12-17 16:52:37',131,1310),(77,'2023-12-17 17:02:37',141,1410),(77,'2023-12-17 17:12:37',146,1460),(77,'2023-12-17 17:22:37',156,1560),(77,'2023-12-17 17:32:37',158,1580),(77,'2023-12-17 17:42:37',166,1660),(77,'2023-12-17 17:52:37',174,1740),(77,'2023-12-17 18:02:37',182,1820),(77,'2023-12-17 18:12:37',191,1910),(77,'2023-12-17 18:22:37',195,1950),(77,'2023-12-17 18:32:37',200,2000),(77,'2023-12-17 18:42:37',206,2060),(77,'2023-12-17 18:52:37',210,2100),(77,'2023-12-17 19:02:37',220,2200),(77,'2023-12-17 19:12:37',230,2300),(77,'2023-12-17 19:22:37',233,2330),(77,'2023-12-17 19:32:37',236,2360),(77,'2023-12-17 19:42:37',243,2430),(77,'2023-12-17 19:52:37',251,2510),(77,'2023-12-17 20:02:37',260,2600),(77,'2023-12-17 20:12:37',263,2630),(77,'2023-12-17 20:22:37',271,2710),(77,'2023-12-17 20:32:37',277,2770),(77,'2023-12-17 20:42:37',277,2770),(77,'2023-12-17 20:52:37',279,2790),(77,'2023-12-17 21:02:37',282,2820),(77,'2023-12-17 21:22:37',292,2920),(77,'2023-12-17 21:32:37',302,3020),(77,'2023-12-17 21:42:37',302,3020),(77,'2023-12-17 21:52:37',304,3040),(77,'2023-12-17 22:02:37',310,3100),(77,'2023-12-17 22:12:37',314,3140),(77,'2023-12-17 22:22:37',322,3220),(77,'2023-12-17 22:32:37',325,3250),(77,'2023-12-17 22:42:37',331,3310),(77,'2023-12-17 22:52:37',340,3400),(77,'2023-12-17 23:02:37',349,3490),(77,'2023-12-17 23:12:37',357,3570),(77,'2023-12-17 23:22:37',357,3570),(77,'2023-12-17 23:32:37',362,3620),(77,'2023-12-17 23:42:37',365,3650),(77,'2023-12-17 23:52:37',368,3680),(77,'2023-12-18 00:12:37',383,3830),(77,'2023-12-18 00:32:37',401,4010),(77,'2023-12-18 00:42:37',406,4060),(77,'2023-12-18 00:52:37',409,4090),(77,'2023-12-18 01:02:37',415,4150),(77,'2023-12-18 01:12:37',425,4250),(77,'2023-12-18 01:22:37',430,4300),(77,'2023-12-18 01:32:37',433,4330),(77,'2023-12-18 01:42:37',436,4360),(77,'2023-12-18 01:52:37',444,4440),(77,'2023-12-18 02:02:37',446,4460),(77,'2023-12-18 02:12:37',456,4560),(77,'2023-12-18 02:22:37',465,4650),(77,'2023-12-18 02:32:37',474,4740),(77,'2023-12-18 02:42:37',478,4780),(77,'2023-12-18 02:52:37',487,4870),(77,'2023-12-18 03:02:37',491,4910),(77,'2023-12-18 03:12:37',499,4990),(77,'2023-12-18 03:22:37',506,5060),(77,'2023-12-18 03:32:37',516,5160),(77,'2023-12-18 03:42:37',518,5180),(77,'2023-12-18 03:52:37',521,5210),(77,'2023-12-18 04:02:37',522,5220),(77,'2023-12-18 04:12:37',531,5310),(77,'2023-12-18 04:22:37',540,5400),(77,'2023-12-18 04:32:37',547,5470),(77,'2023-12-18 04:42:37',556,5560),(77,'2023-12-18 04:52:37',565,5650),(77,'2023-12-18 05:02:37',570,5700),(77,'2023-12-18 05:12:37',573,5730),(77,'2023-12-18 05:22:37',580,5800),(77,'2023-12-18 05:32:37',581,5810),(77,'2023-12-18 05:42:37',590,5900),(77,'2023-12-18 05:52:37',600,6000),(77,'2023-12-18 06:02:37',601,6010),(77,'2023-12-18 06:12:37',610,6100),(77,'2023-12-18 06:22:37',614,6140),(77,'2023-12-18 06:32:37',624,6240),(77,'2023-12-18 06:42:37',631,6310),(77,'2023-12-18 06:52:37',638,6380),(77,'2023-12-18 07:02:37',641,6410),(77,'2023-12-18 07:12:37',641,6410),(77,'2023-12-18 07:22:37',645,6450),(77,'2023-12-18 07:32:37',653,6530),(77,'2023-12-18 07:42:37',655,6550),(77,'2023-12-18 07:52:37',662,6620),(77,'2023-12-18 08:02:37',667,6670),(77,'2023-12-18 08:12:37',677,6770),(77,'2023-12-18 08:22:37',682,6820),(77,'2023-12-18 08:32:37',683,6830),(77,'2023-12-18 08:42:37',690,6900),(77,'2023-12-18 08:52:37',696,6960),(77,'2023-12-18 09:02:37',703,7030),(77,'2023-12-18 09:12:37',705,7050),(77,'2023-12-18 09:32:37',715,7150),(77,'2023-12-18 09:42:37',724,7240),(77,'2023-12-18 09:52:37',734,7340),(77,'2023-12-18 10:02:37',741,7410),(77,'2023-12-18 10:12:37',748,7480),(77,'2023-12-18 10:22:37',752,7520),(77,'2023-12-18 10:32:37',753,7530),(77,'2023-12-18 10:42:37',763,7630),(77,'2023-12-18 10:52:37',770,7700),(77,'2023-12-18 11:02:37',777,7770),(77,'2023-12-18 11:12:37',783,7830),(77,'2023-12-18 11:22:37',790,7900),(77,'2023-12-18 11:32:37',795,7950),(77,'2023-12-18 11:42:37',801,8010),(77,'2023-12-18 11:52:37',808,8080),(77,'2023-12-18 12:02:37',815,8150),(77,'2023-12-18 12:12:37',824,8240),(77,'2023-12-18 12:22:37',828,8280),(77,'2023-12-18 12:32:37',830,8300),(77,'2023-12-18 12:42:37',839,8390),(77,'2023-12-18 12:52:37',848,8480),(77,'2023-12-18 13:02:37',851,8510),(77,'2023-12-18 13:12:37',856,8560),(77,'2023-12-18 13:22:37',858,8580),(77,'2023-12-18 13:32:37',864,8640),(77,'2023-12-18 13:42:37',874,8740),(77,'2023-12-18 13:52:37',884,8840),(77,'2023-12-18 14:02:37',891,8910),(77,'2023-12-18 14:12:37',898,8980),(77,'2023-12-18 14:22:37',906,9060),(77,'2023-12-18 14:32:37',914,9140),(77,'2023-12-18 14:42:37',915,9150),(77,'2023-12-18 14:52:37',921,9210),(77,'2023-12-18 15:12:37',937,9370),(77,'2023-12-18 15:22:37',938,9380),(77,'2023-12-18 15:32:37',947,9470),(77,'2023-12-18 15:42:37',952,9520),(77,'2023-12-18 15:52:37',958,9580),(77,'2023-12-18 16:02:37',966,9660),(77,'2023-12-18 16:12:37',972,9720),(77,'2023-12-18 16:32:37',981,9810),(77,'2023-12-18 16:42:37',986,9860),(77,'2023-12-18 16:52:37',990,9900),(77,'2023-12-18 17:02:37',994,9940),(77,'2023-12-18 17:12:37',1004,10040),(77,'2023-12-18 17:22:37',1014,10140),(77,'2023-12-18 17:32:37',1022,10220),(77,'2023-12-18 17:42:37',1032,10320),(77,'2023-12-18 17:52:37',1037,10370),(77,'2023-12-18 18:02:37',1041,10410),(77,'2023-12-18 18:12:37',1045,10450),(77,'2023-12-18 18:22:37',1054,10540),(77,'2023-12-18 18:32:37',1063,10630),(77,'2023-12-18 18:42:37',1070,10700),(77,'2023-12-18 18:52:37',1078,10780),(77,'2023-12-18 19:02:37',1081,10810),(77,'2023-12-18 19:12:37',1091,10910),(77,'2023-12-18 19:22:37',1094,10940),(77,'2023-12-18 19:32:37',1098,10980),(77,'2023-12-18 20:12:37',1126,11260),(77,'2023-12-18 20:22:37',1133,11330),(77,'2023-12-18 20:32:37',1133,11330),(77,'2023-12-18 20:42:37',1140,11400),(77,'2023-12-18 20:52:37',1144,11440),(77,'2023-12-18 21:02:37',1150,11500),(77,'2023-12-18 21:12:37',1154,11540),(77,'2023-12-18 21:22:37',1155,11550),(77,'2023-12-18 21:32:37',1163,11630),(77,'2023-12-18 21:52:37',1167,11670),(77,'2023-12-18 22:02:37',1172,11720),(77,'2023-12-18 22:12:37',1179,11790),(77,'2023-12-18 22:22:37',1183,11830),(77,'2023-12-18 22:32:37',1190,11900),(77,'2023-12-18 22:42:37',1200,12000),(77,'2023-12-18 22:52:37',1205,12050),(77,'2023-12-18 23:02:37',1211,12110),(77,'2023-12-18 23:12:37',1215,12150),(77,'2023-12-18 23:22:37',1223,12230),(77,'2023-12-18 23:32:37',1225,12250),(77,'2023-12-18 23:42:37',1230,12300),(77,'2023-12-18 23:52:37',1232,12320),(77,'2023-12-19 00:02:37',1238,12380),(77,'2023-12-19 00:12:37',1246,12460),(77,'2023-12-19 00:22:37',1251,12510),(77,'2023-12-19 00:32:37',1256,12560),(77,'2023-12-19 00:42:37',1262,12620),(77,'2023-12-19 00:52:37',1272,12720),(77,'2023-12-19 01:02:37',1274,12740),(77,'2023-12-19 01:22:37',1292,12920),(77,'2023-12-19 01:32:37',1299,12990),(77,'2023-12-19 01:42:37',1303,13030),(77,'2023-12-19 01:52:37',1306,13060),(77,'2023-12-19 02:02:37',1316,13160),(77,'2023-12-19 02:12:37',1326,13260),(77,'2023-12-19 02:22:37',1329,13290),(77,'2023-12-19 02:32:37',1336,13360),(77,'2023-12-19 02:42:37',1346,13460),(77,'2023-12-19 02:52:37',1352,13520),(77,'2023-12-19 03:02:37',1358,13580),(77,'2023-12-19 03:22:37',1372,13720),(77,'2023-12-19 03:32:37',1375,13750),(77,'2023-12-19 03:42:37',1378,13780),(77,'2023-12-19 03:52:37',1379,13790),(77,'2023-12-19 04:02:37',1387,13870),(77,'2023-12-19 04:12:37',1392,13920),(77,'2023-12-19 04:22:37',1397,13970),(77,'2023-12-19 04:32:37',1403,14030),(77,'2023-12-19 04:42:37',3,30),(77,'2023-12-19 04:52:37',12,120),(77,'2023-12-19 05:02:37',16,160),(77,'2023-12-19 05:12:37',18,180),(77,'2023-12-19 05:22:37',27,270),(77,'2023-12-19 05:32:37',27,270),(77,'2023-12-19 05:42:37',31,310),(77,'2023-12-19 05:52:37',35,350),(77,'2023-12-19 06:02:37',43,430),(77,'2023-12-19 06:12:37',49,490),(77,'2023-12-19 06:22:37',59,590),(77,'2023-12-19 06:32:37',61,610),(77,'2023-12-19 06:42:37',63,630),(77,'2023-12-19 06:52:37',73,730),(77,'2023-12-19 07:02:37',83,830),(77,'2023-12-19 07:12:37',90,900),(77,'2023-12-19 07:22:37',99,990),(77,'2023-12-19 07:32:37',109,1090),(77,'2023-12-19 07:42:37',117,1170),(77,'2023-12-19 07:52:37',126,1260),(77,'2023-12-19 08:02:37',135,1350),(77,'2023-12-19 08:12:37',142,1420),(77,'2023-12-19 08:22:37',150,1500),(77,'2023-12-19 08:32:37',5,50),(77,'2023-12-19 08:42:37',7,70),(78,'2023-12-16 08:52:37',10,100),(78,'2023-12-16 09:02:37',18,180),(78,'2023-12-16 09:12:37',20,200),(78,'2023-12-16 09:22:37',25,250),(78,'2023-12-16 09:32:37',28,280),(78,'2023-12-16 09:42:37',35,350),(78,'2023-12-16 09:52:37',45,450),(78,'2023-12-16 10:02:37',48,480),(78,'2023-12-16 10:12:37',58,580),(78,'2023-12-16 10:22:37',62,620),(78,'2023-12-16 10:32:37',9,90),(78,'2023-12-16 10:42:37',19,190),(78,'2023-12-16 10:52:37',20,200),(78,'2023-12-16 11:02:37',25,250),(78,'2023-12-16 11:12:37',35,350),(78,'2023-12-16 11:22:37',44,440),(78,'2023-12-16 11:32:37',50,500),(78,'2023-12-16 11:42:37',57,570),(78,'2023-12-16 11:52:37',61,610),(78,'2023-12-16 12:02:37',67,670),(78,'2023-12-16 12:12:37',71,710),(78,'2023-12-16 12:22:37',74,740),(78,'2023-12-16 12:32:37',79,790),(78,'2023-12-16 12:42:37',89,890),(78,'2023-12-16 12:52:37',95,950),(78,'2023-12-16 13:02:37',99,990),(78,'2023-12-16 13:12:37',104,1040),(78,'2023-12-16 13:22:37',112,1120),(78,'2023-12-16 13:32:37',120,1200),(78,'2023-12-16 13:42:37',127,1270),(78,'2023-12-16 13:52:37',133,1330),(78,'2023-12-16 14:02:37',143,1430),(78,'2023-12-16 14:12:37',149,1490),(78,'2023-12-16 14:22:37',158,1580),(78,'2023-12-16 14:32:37',159,1590),(78,'2023-12-16 14:42:37',168,1680),(78,'2023-12-16 14:52:37',174,1740),(78,'2023-12-16 15:02:37',182,1820),(78,'2023-12-16 15:12:37',192,1920),(78,'2023-12-16 15:22:37',201,2010),(78,'2023-12-16 15:32:37',207,2070),(78,'2023-12-16 15:42:37',208,2080),(78,'2023-12-16 15:52:37',215,2150),(78,'2023-12-16 16:02:37',224,2240),(78,'2023-12-16 16:22:37',231,2310),(78,'2023-12-16 16:32:37',240,2400),(78,'2023-12-16 16:42:37',246,2460),(78,'2023-12-16 16:52:37',256,2560),(78,'2023-12-16 17:02:37',259,2590),(78,'2023-12-16 17:12:37',268,2680),(78,'2023-12-16 17:22:37',275,2750),(78,'2023-12-16 17:32:37',285,2850),(78,'2023-12-16 17:42:37',292,2920),(78,'2023-12-16 17:52:37',295,2950),(78,'2023-12-16 18:02:37',305,3050),(78,'2023-12-16 18:12:37',310,3100),(78,'2023-12-16 18:22:37',319,3190),(78,'2023-12-16 18:42:37',333,3330),(78,'2023-12-16 18:52:37',333,3330),(78,'2023-12-16 19:02:37',338,3380),(78,'2023-12-16 19:12:37',346,3460),(78,'2023-12-16 19:22:37',351,3510),(78,'2023-12-16 19:32:37',361,3610),(78,'2023-12-16 19:42:37',370,3700),(78,'2023-12-16 19:52:37',377,3770),(78,'2023-12-16 20:02:37',386,3860),(78,'2023-12-16 20:12:37',389,3890),(78,'2023-12-16 20:32:37',403,4030),(78,'2023-12-16 20:42:37',409,4090),(78,'2023-12-16 20:52:37',416,4160),(78,'2023-12-16 21:02:37',426,4260),(78,'2023-12-16 21:12:37',432,4320),(78,'2023-12-16 21:22:37',440,4400),(78,'2023-12-16 21:32:37',447,4470),(78,'2023-12-16 21:42:37',452,4520),(78,'2023-12-16 21:52:37',453,4530),(78,'2023-12-16 22:02:37',453,4530),(78,'2023-12-16 22:12:37',461,4610),(78,'2023-12-16 22:22:37',471,4710),(78,'2023-12-16 22:32:37',481,4810),(78,'2023-12-16 22:42:37',491,4910),(78,'2023-12-16 22:52:37',496,4960),(78,'2023-12-16 23:02:37',505,5050),(78,'2023-12-16 23:22:37',513,5130),(78,'2023-12-16 23:32:37',10,100),(78,'2023-12-16 23:42:37',20,200),(78,'2023-12-16 23:52:37',25,250),(78,'2023-12-17 00:02:37',29,290),(78,'2023-12-17 00:12:37',38,380),(78,'2023-12-17 00:22:37',47,470),(78,'2023-12-17 00:32:37',54,540),(78,'2023-12-17 00:42:37',64,640),(78,'2023-12-17 00:52:37',74,740),(78,'2023-12-17 01:02:37',79,790),(78,'2023-12-17 01:12:37',86,860),(78,'2023-12-17 01:22:37',92,920),(78,'2023-12-17 01:32:37',102,1020),(78,'2023-12-17 01:42:37',107,1070),(78,'2023-12-17 01:52:37',111,1110),(78,'2023-12-17 02:02:37',120,1200),(78,'2023-12-17 02:12:37',126,1260),(78,'2023-12-17 02:22:37',136,1360),(78,'2023-12-17 02:32:37',145,1450),(78,'2023-12-17 02:42:37',153,1530),(78,'2023-12-17 02:52:37',155,1550),(78,'2023-12-17 03:02:37',165,1650),(78,'2023-12-17 03:12:37',171,1710),(78,'2023-12-17 03:22:37',176,1760),(78,'2023-12-17 03:32:37',179,1790),(78,'2023-12-17 03:42:37',180,1800),(78,'2023-12-17 03:52:37',182,1820),(78,'2023-12-17 04:02:37',189,1890),(78,'2023-12-17 04:12:37',193,1930),(78,'2023-12-17 04:22:37',195,1950),(78,'2023-12-17 04:32:37',202,2020),(78,'2023-12-17 04:42:37',204,2040),(78,'2023-12-17 04:52:37',210,2100),(78,'2023-12-17 05:02:37',214,2140),(78,'2023-12-17 05:12:37',216,2160),(78,'2023-12-17 05:22:37',218,2180),(78,'2023-12-17 05:42:37',226,2260),(78,'2023-12-17 05:52:37',9,90),(78,'2023-12-17 06:02:37',14,140),(78,'2023-12-17 06:12:37',22,220),(78,'2023-12-17 06:22:37',25,250),(78,'2023-12-17 06:32:37',34,340),(78,'2023-12-17 06:42:37',5,50),(78,'2023-12-17 06:52:37',13,130),(78,'2023-12-17 07:02:37',22,220),(78,'2023-12-17 07:12:37',26,260),(78,'2023-12-17 07:22:37',27,270),(78,'2023-12-17 07:32:37',36,360),(78,'2023-12-17 07:42:37',44,440),(78,'2023-12-17 07:52:37',51,510),(78,'2023-12-17 08:02:37',61,610),(78,'2023-12-17 08:12:37',66,660),(78,'2023-12-17 08:22:37',71,710),(78,'2023-12-17 08:32:37',77,770),(78,'2023-12-17 08:42:37',82,820),(78,'2023-12-17 08:52:37',86,860),(78,'2023-12-17 09:02:37',91,910),(78,'2023-12-17 09:22:37',111,1110),(78,'2023-12-17 09:32:37',112,1120),(78,'2023-12-17 09:42:37',112,1120),(78,'2023-12-17 09:52:37',116,1160),(78,'2023-12-17 10:02:37',123,1230),(78,'2023-12-17 10:12:37',133,1330),(78,'2023-12-17 10:22:37',138,1380),(78,'2023-12-17 10:32:37',140,1400),(78,'2023-12-17 10:42:37',144,1440),(78,'2023-12-17 10:52:37',145,1450),(78,'2023-12-17 11:02:37',153,1530),(78,'2023-12-17 11:12:37',159,1590),(78,'2023-12-17 11:22:37',165,1650),(78,'2023-12-17 11:32:37',175,1750),(78,'2023-12-17 11:42:37',185,1850),(78,'2023-12-17 11:52:37',190,1900),(78,'2023-12-17 12:02:37',196,1960),(78,'2023-12-17 12:12:37',206,2060),(78,'2023-12-17 12:22:37',214,2140),(78,'2023-12-17 12:32:37',215,2150),(78,'2023-12-17 12:42:37',224,2240),(78,'2023-12-17 12:52:37',233,2330),(78,'2023-12-17 13:02:37',243,2430),(78,'2023-12-17 13:12:37',252,2520),(78,'2023-12-17 13:22:37',257,2570),(78,'2023-12-17 13:32:37',267,2670),(78,'2023-12-17 13:42:37',273,2730),(78,'2023-12-17 13:52:37',280,2800),(78,'2023-12-17 14:02:37',281,2810),(78,'2023-12-17 14:12:37',291,2910),(78,'2023-12-17 14:22:37',291,2910),(78,'2023-12-17 14:32:37',297,2970),(78,'2023-12-17 14:42:37',303,3030),(78,'2023-12-17 14:52:37',310,3100),(78,'2023-12-17 15:02:37',319,3190),(78,'2023-12-17 15:12:37',320,3200),(78,'2023-12-17 15:22:37',326,3260),(78,'2023-12-17 15:32:37',335,3350),(78,'2023-12-17 15:42:37',338,3380),(78,'2023-12-17 15:52:37',345,3450),(78,'2023-12-17 16:02:37',352,3520),(78,'2023-12-17 16:12:37',361,3610),(78,'2023-12-17 16:22:37',365,3650),(78,'2023-12-17 16:32:37',373,3730),(78,'2023-12-17 16:42:37',380,3800),(78,'2023-12-17 16:52:37',387,3870),(78,'2023-12-17 17:02:37',396,3960),(78,'2023-12-17 17:12:37',400,4000),(78,'2023-12-17 17:22:37',406,4060),(78,'2023-12-17 17:32:37',411,4110),(78,'2023-12-17 17:42:37',417,4170),(78,'2023-12-17 17:52:37',420,4200),(78,'2023-12-17 18:02:37',425,4250),(78,'2023-12-17 18:12:37',429,4290),(78,'2023-12-17 18:22:37',432,4320),(78,'2023-12-17 18:32:37',437,4370),(78,'2023-12-17 18:42:37',445,4450),(78,'2023-12-17 18:52:37',451,4510),(78,'2023-12-17 19:02:37',460,4600),(78,'2023-12-17 19:12:37',461,4610),(78,'2023-12-17 19:22:37',470,4700),(78,'2023-12-17 19:32:37',478,4780),(78,'2023-12-17 19:42:37',488,4880),(78,'2023-12-17 19:52:37',491,4910),(78,'2023-12-17 20:02:37',500,5000),(78,'2023-12-17 20:12:37',506,5060),(78,'2023-12-17 20:22:37',516,5160),(78,'2023-12-17 20:32:37',523,5230),(78,'2023-12-17 20:42:37',532,5320),(78,'2023-12-17 20:52:37',538,5380),(78,'2023-12-17 21:02:37',545,5450),(78,'2023-12-17 21:12:37',548,5480),(78,'2023-12-17 21:22:37',552,5520),(78,'2023-12-17 21:32:37',558,5580),(78,'2023-12-17 21:42:37',568,5680),(78,'2023-12-17 21:52:37',571,5710),(78,'2023-12-17 22:02:37',580,5800),(78,'2023-12-17 22:12:37',586,5860),(78,'2023-12-17 22:22:37',596,5960),(78,'2023-12-17 22:32:37',606,6060),(78,'2023-12-17 22:42:37',616,6160),(78,'2023-12-17 22:52:37',623,6230),(78,'2023-12-17 23:12:37',630,6300),(78,'2023-12-17 23:22:37',639,6390),(78,'2023-12-17 23:32:37',647,6470),(78,'2023-12-17 23:42:37',653,6530),(78,'2023-12-17 23:52:37',653,6530),(78,'2023-12-18 00:02:37',659,6590),(78,'2023-12-18 00:12:37',666,6660),(78,'2023-12-18 00:22:37',673,6730),(78,'2023-12-18 00:32:37',676,6760),(78,'2023-12-18 00:42:37',684,6840),(78,'2023-12-18 00:52:37',686,6860),(78,'2023-12-18 01:02:37',687,6870),(78,'2023-12-18 01:12:37',694,6940),(78,'2023-12-18 01:22:37',701,7010),(78,'2023-12-18 01:42:37',709,7090),(78,'2023-12-18 01:52:37',713,7130),(78,'2023-12-18 02:02:37',718,7180),(78,'2023-12-18 02:12:37',728,7280),(78,'2023-12-18 02:22:37',732,7320),(78,'2023-12-18 02:32:37',737,7370),(78,'2023-12-18 02:42:37',743,7430),(78,'2023-12-18 02:52:37',748,7480),(78,'2023-12-18 03:02:37',752,7520),(78,'2023-12-18 03:12:37',759,7590),(78,'2023-12-18 03:22:37',766,7660),(78,'2023-12-18 03:32:37',776,7760),(78,'2023-12-18 03:42:37',778,7780),(78,'2023-12-18 03:52:37',784,7840),(78,'2023-12-18 04:02:37',794,7940),(78,'2023-12-18 04:12:37',798,7980),(78,'2023-12-18 04:22:37',805,8050),(78,'2023-12-18 04:32:37',815,8150),(78,'2023-12-18 04:42:37',817,8170),(78,'2023-12-18 04:52:37',818,8180),(78,'2023-12-18 05:02:37',823,8230),(78,'2023-12-18 05:12:37',823,8230),(78,'2023-12-18 05:22:37',828,8280),(78,'2023-12-18 05:32:37',834,8340),(78,'2023-12-18 05:42:37',841,8410),(78,'2023-12-18 05:52:37',849,8490),(78,'2023-12-18 06:02:37',851,8510),(78,'2023-12-18 06:12:37',861,8610),(78,'2023-12-18 06:22:37',866,8660),(78,'2023-12-18 06:32:37',876,8760),(78,'2023-12-18 06:42:37',882,8820),(78,'2023-12-18 06:52:37',889,8890),(78,'2023-12-18 07:02:37',897,8970),(78,'2023-12-18 07:12:37',897,8970),(78,'2023-12-18 07:22:37',902,9020),(78,'2023-12-18 07:32:37',911,9110),(78,'2023-12-18 07:42:37',918,9180),(78,'2023-12-18 07:52:37',926,9260),(78,'2023-12-18 08:02:37',927,9270),(78,'2023-12-18 08:12:37',929,9290),(78,'2023-12-18 08:22:37',937,9370),(78,'2023-12-18 08:32:37',940,9400),(78,'2023-12-18 08:42:37',950,9500),(78,'2023-12-18 08:52:37',955,9550),(78,'2023-12-18 09:02:37',964,9640),(78,'2023-12-18 09:12:37',966,9660),(78,'2023-12-18 09:22:37',974,9740),(78,'2023-12-18 09:32:37',982,9820),(78,'2023-12-18 09:42:37',988,9880),(78,'2023-12-18 09:52:37',991,9910),(78,'2023-12-18 10:02:37',992,9920),(78,'2023-12-18 10:12:37',1001,10010),(78,'2023-12-18 10:22:37',1009,10090),(78,'2023-12-18 10:32:37',1011,10110),(78,'2023-12-18 10:42:37',1021,10210),(78,'2023-12-18 10:52:37',1024,10240),(78,'2023-12-18 11:02:37',1034,10340),(78,'2023-12-18 11:12:37',1042,10420),(78,'2023-12-18 11:22:37',1047,10470),(78,'2023-12-18 11:32:37',1051,10510),(78,'2023-12-18 11:42:37',1061,10610),(78,'2023-12-18 11:52:37',1071,10710),(78,'2023-12-18 12:02:37',1079,10790),(78,'2023-12-18 12:12:37',1086,10860),(78,'2023-12-18 12:22:37',1094,10940),(78,'2023-12-18 12:32:37',1103,11030),(78,'2023-12-18 12:42:37',1106,11060),(78,'2023-12-18 12:52:37',1112,11120),(78,'2023-12-18 13:02:37',1116,11160),(78,'2023-12-18 13:12:37',1124,11240),(78,'2023-12-18 13:22:37',1133,11330),(78,'2023-12-18 13:42:37',1150,11500),(78,'2023-12-18 13:52:37',1151,11510),(78,'2023-12-18 14:02:37',1161,11610),(78,'2023-12-18 14:12:37',1169,11690),(78,'2023-12-18 14:22:37',1177,11770),(78,'2023-12-18 14:32:37',1180,11800),(78,'2023-12-18 14:42:37',1187,11870),(78,'2023-12-18 14:52:37',1190,11900),(78,'2023-12-18 15:02:37',1194,11940),(78,'2023-12-18 15:12:37',1197,11970),(78,'2023-12-18 15:22:37',1197,11970),(78,'2023-12-18 15:32:37',1204,12040),(78,'2023-12-18 15:42:37',1214,12140),(78,'2023-12-18 15:52:37',1224,12240),(78,'2023-12-18 16:02:37',1228,12280),(78,'2023-12-18 16:12:37',1229,12290),(78,'2023-12-18 16:22:37',1236,12360),(78,'2023-12-18 16:32:37',1245,12450),(78,'2023-12-18 16:42:37',1255,12550),(78,'2023-12-18 16:52:37',1262,12620),(78,'2023-12-18 17:02:37',1264,12640),(78,'2023-12-18 17:12:37',1265,12650),(78,'2023-12-18 17:22:37',1271,12710),(78,'2023-12-18 17:32:37',1279,12790),(78,'2023-12-18 17:42:37',1281,12810),(78,'2023-12-18 17:52:37',1282,12820),(78,'2023-12-18 18:02:37',1290,12900),(78,'2023-12-18 18:12:37',1292,12920),(78,'2023-12-18 18:22:37',1296,12960),(78,'2023-12-18 18:32:37',1305,13050),(78,'2023-12-18 18:42:37',1314,13140),(78,'2023-12-18 18:52:37',1319,13190),(78,'2023-12-18 19:02:37',1321,13210),(78,'2023-12-18 19:12:37',1327,13270),(78,'2023-12-18 19:22:37',1329,13290),(78,'2023-12-18 19:32:37',1337,13370),(78,'2023-12-18 19:42:37',1339,13390),(78,'2023-12-18 19:52:37',1341,13410),(78,'2023-12-18 20:02:37',1347,13470),(78,'2023-12-18 20:12:37',1353,13530),(78,'2023-12-18 20:22:37',1362,13620),(78,'2023-12-18 20:32:37',1368,13680),(78,'2023-12-18 20:42:37',1372,13720),(78,'2023-12-18 20:52:37',1379,13790),(78,'2023-12-18 21:12:37',1389,13890),(78,'2023-12-18 21:32:37',1404,14040),(78,'2023-12-18 21:42:37',1413,14130),(78,'2023-12-18 21:52:37',1415,14150),(78,'2023-12-18 22:02:37',1420,14200),(78,'2023-12-18 22:12:37',1428,14280),(78,'2023-12-18 22:22:37',1431,14310),(78,'2023-12-18 22:32:37',1440,14400),(78,'2023-12-18 22:42:37',1441,14410),(78,'2023-12-18 22:52:37',1446,14460),(78,'2023-12-18 23:02:37',1446,14460),(78,'2023-12-18 23:12:37',1448,14480),(78,'2023-12-18 23:22:37',1450,14500),(78,'2023-12-18 23:32:37',1451,14510),(78,'2023-12-18 23:42:37',1455,14550),(78,'2023-12-18 23:52:37',1459,14590),(78,'2023-12-19 00:02:37',1466,14660),(78,'2023-12-19 00:12:37',1469,14690),(78,'2023-12-19 00:22:37',1473,14730),(78,'2023-12-19 00:32:37',1481,14810),(78,'2023-12-19 00:42:37',1491,14910),(78,'2023-12-19 00:52:37',1498,14980),(78,'2023-12-19 01:02:37',1508,15080),(78,'2023-12-19 01:12:37',1509,15090),(78,'2023-12-19 01:22:37',1518,15180),(78,'2023-12-19 01:32:37',1524,15240),(78,'2023-12-19 01:42:37',1529,15290),(78,'2023-12-19 01:52:37',1539,15390),(78,'2023-12-19 02:02:37',1547,15470),(78,'2023-12-19 02:12:37',1550,15500),(78,'2023-12-19 02:22:37',1557,15570),(78,'2023-12-19 02:32:37',1563,15630),(78,'2023-12-19 03:02:37',1585,15850),(78,'2023-12-19 03:12:37',1593,15930),(78,'2023-12-19 03:22:37',1597,15970),(78,'2023-12-19 03:32:37',1598,15980),(78,'2023-12-19 03:52:37',1609,16090),(78,'2023-12-19 04:02:37',1611,16110),(78,'2023-12-19 04:12:37',1615,16150),(78,'2023-12-19 04:22:37',1620,16200),(78,'2023-12-19 04:32:37',1621,16210),(78,'2023-12-19 04:42:37',1625,16250),(78,'2023-12-19 04:52:37',1628,16280),(78,'2023-12-19 05:02:37',1634,16340),(78,'2023-12-19 05:12:37',1636,16360),(78,'2023-12-19 05:22:37',1641,16410),(78,'2023-12-19 05:32:37',1649,16490),(78,'2023-12-19 05:42:37',1656,16560),(78,'2023-12-19 05:52:37',1661,16610),(78,'2023-12-19 06:02:37',1669,16690),(78,'2023-12-19 06:12:37',1679,16790),(78,'2023-12-19 06:22:37',1689,16890),(78,'2023-12-19 06:32:37',1695,16950),(78,'2023-12-19 06:42:37',1704,17040),(78,'2023-12-19 06:52:37',1705,17050),(78,'2023-12-19 07:02:37',1710,17100),(78,'2023-12-19 07:12:37',1714,17140),(78,'2023-12-19 07:22:37',1716,17160),(78,'2023-12-19 07:32:37',1723,17230),(78,'2023-12-19 07:42:37',1730,17300),(78,'2023-12-19 07:52:37',1732,17320),(78,'2023-12-19 08:02:37',1732,17320),(78,'2023-12-19 08:12:37',1742,17420),(78,'2023-12-19 08:22:37',1748,17480),(78,'2023-12-19 08:32:37',1757,17570),(78,'2023-12-19 08:42:37',1760,17600),(80,'2023-12-16 08:52:37',10,100),(80,'2023-12-16 09:02:37',18,180),(80,'2023-12-16 09:12:37',18,180),(80,'2023-12-16 09:22:37',18,180),(80,'2023-12-16 09:32:37',24,240),(80,'2023-12-16 09:42:37',31,310),(80,'2023-12-16 09:52:37',37,370),(80,'2023-12-16 10:02:37',45,450),(80,'2023-12-16 10:12:37',53,530),(80,'2023-12-16 10:22:37',56,560),(80,'2023-12-16 10:32:37',64,640),(80,'2023-12-16 10:42:37',9,90),(80,'2023-12-16 10:52:37',18,180),(80,'2023-12-16 11:02:37',26,260),(80,'2023-12-16 11:12:37',35,350),(80,'2023-12-16 11:22:37',43,430),(80,'2023-12-16 11:32:37',52,520),(80,'2023-12-16 11:42:37',58,580),(80,'2023-12-16 11:52:37',60,600),(80,'2023-12-16 12:02:37',70,700),(80,'2023-12-16 12:12:37',80,800),(80,'2023-12-16 12:22:37',85,850),(80,'2023-12-16 12:32:37',91,910),(80,'2023-12-16 12:42:37',94,940),(80,'2023-12-16 12:52:37',100,1000),(80,'2023-12-16 13:02:37',107,1070),(80,'2023-12-16 13:12:37',110,1100),(80,'2023-12-16 13:32:37',124,1240),(80,'2023-12-16 13:52:37',139,1390),(80,'2023-12-16 14:02:37',139,1390),(80,'2023-12-16 14:12:37',147,1470),(80,'2023-12-16 14:22:37',157,1570),(80,'2023-12-16 14:32:37',167,1670),(80,'2023-12-16 14:42:37',172,1720),(80,'2023-12-16 14:52:37',175,1750),(80,'2023-12-16 15:02:37',185,1850),(80,'2023-12-16 15:12:37',188,1880),(80,'2023-12-16 15:22:37',188,1880),(80,'2023-12-16 15:32:37',191,1910),(80,'2023-12-16 15:42:37',198,1980),(80,'2023-12-16 15:52:37',200,2000),(80,'2023-12-16 16:02:37',203,2030),(80,'2023-12-16 16:12:37',205,2050),(80,'2023-12-16 16:22:37',210,2100),(80,'2023-12-16 16:32:37',212,2120),(80,'2023-12-16 16:42:37',213,2130),(80,'2023-12-16 16:52:37',221,2210),(80,'2023-12-16 17:02:37',229,2290),(80,'2023-12-16 17:12:37',236,2360),(80,'2023-12-16 17:22:37',243,2430),(80,'2023-12-16 17:32:37',251,2510),(80,'2023-12-16 17:42:37',259,2590),(80,'2023-12-16 17:52:37',268,2680),(80,'2023-12-16 18:02:37',276,2760),(80,'2023-12-16 18:12:37',286,2860),(80,'2023-12-16 18:22:37',291,2910),(80,'2023-12-16 18:32:37',295,2950),(80,'2023-12-16 18:42:37',298,2980),(80,'2023-12-16 18:52:37',304,3040),(80,'2023-12-16 19:02:37',305,3050),(80,'2023-12-16 19:12:37',309,3090),(80,'2023-12-16 19:22:37',311,3110),(80,'2023-12-16 19:42:37',329,3290),(80,'2023-12-16 19:52:37',336,3360),(80,'2023-12-16 20:02:37',346,3460),(80,'2023-12-16 20:12:37',346,3460),(80,'2023-12-16 20:22:37',356,3560),(80,'2023-12-16 20:32:37',3,30),(80,'2023-12-16 20:42:37',5,50),(80,'2023-12-16 20:52:37',11,110),(80,'2023-12-16 21:02:37',20,200),(80,'2023-12-16 21:12:37',28,280),(80,'2023-12-16 21:22:37',37,370),(80,'2023-12-16 21:32:37',42,420),(80,'2023-12-16 21:42:37',50,500),(80,'2023-12-16 21:52:37',56,560),(80,'2023-12-16 22:02:37',64,640),(80,'2023-12-16 22:12:37',73,730),(80,'2023-12-16 22:22:37',79,790),(80,'2023-12-16 22:32:37',83,830),(80,'2023-12-16 22:42:37',89,890),(80,'2023-12-16 22:52:37',97,970),(80,'2023-12-16 23:12:37',109,1090),(80,'2023-12-16 23:22:37',119,1190),(80,'2023-12-16 23:32:37',124,1240),(80,'2023-12-16 23:42:37',128,1280),(80,'2023-12-16 23:52:37',130,1300),(80,'2023-12-17 00:12:37',133,1330),(80,'2023-12-17 00:22:37',137,1370),(80,'2023-12-17 00:32:37',142,1420),(80,'2023-12-17 00:42:37',147,1470),(80,'2023-12-17 00:52:37',156,1560),(80,'2023-12-17 01:02:37',164,1640),(80,'2023-12-17 01:12:37',169,1690),(80,'2023-12-17 01:22:37',177,1770),(80,'2023-12-17 01:32:37',187,1870),(80,'2023-12-17 01:42:37',196,1960),(80,'2023-12-17 01:52:37',202,2020),(80,'2023-12-17 02:02:37',205,2050),(80,'2023-12-17 02:12:37',206,2060),(80,'2023-12-17 02:22:37',209,2090),(80,'2023-12-17 02:32:37',212,2120),(80,'2023-12-17 02:42:37',219,2190),(80,'2023-12-17 02:52:37',2,20),(80,'2023-12-17 03:12:37',14,140),(80,'2023-12-17 03:22:37',22,220),(80,'2023-12-17 03:32:37',26,260),(80,'2023-12-17 03:42:37',31,310),(80,'2023-12-17 03:52:37',40,400),(80,'2023-12-17 04:02:37',48,480),(80,'2023-12-17 04:12:37',53,530),(80,'2023-12-17 04:32:37',61,610),(80,'2023-12-17 04:42:37',65,650),(80,'2023-12-17 04:52:37',69,690),(80,'2023-12-17 05:02:37',69,690),(80,'2023-12-17 05:12:37',71,710),(80,'2023-12-17 05:22:37',76,760),(80,'2023-12-17 05:32:37',79,790),(80,'2023-12-17 05:42:37',87,870),(80,'2023-12-17 05:52:37',95,950),(80,'2023-12-17 06:02:37',95,950),(80,'2023-12-17 06:12:37',99,990),(80,'2023-12-17 06:22:37',106,1060),(80,'2023-12-17 06:32:37',116,1160),(80,'2023-12-17 06:42:37',121,1210),(80,'2023-12-17 06:52:37',130,1300),(80,'2023-12-17 07:02:37',138,1380),(80,'2023-12-17 07:12:37',142,1420),(80,'2023-12-17 07:22:37',143,1430),(80,'2023-12-17 07:32:37',153,1530),(80,'2023-12-17 07:42:37',160,1600),(80,'2023-12-17 07:52:37',169,1690),(80,'2023-12-17 08:02:37',172,1720),(80,'2023-12-17 08:12:37',173,1730),(80,'2023-12-17 08:22:37',180,1800),(80,'2023-12-17 08:32:37',181,1810),(80,'2023-12-17 08:42:37',191,1910),(80,'2023-12-17 08:52:37',195,1950),(80,'2023-12-17 09:02:37',197,1970),(80,'2023-12-17 09:12:37',200,2000),(80,'2023-12-17 09:22:37',201,2010),(80,'2023-12-17 09:32:37',204,2040),(80,'2023-12-17 09:42:37',211,2110),(80,'2023-12-17 09:52:37',217,2170),(80,'2023-12-17 10:02:37',221,2210),(80,'2023-12-17 10:12:37',229,2290),(80,'2023-12-17 10:22:37',231,2310),(80,'2023-12-17 10:32:37',238,2380),(80,'2023-12-17 10:42:37',248,2480),(80,'2023-12-17 10:52:37',258,2580),(80,'2023-12-17 11:02:37',261,2610),(80,'2023-12-17 11:12:37',266,2660),(80,'2023-12-17 11:22:37',275,2750),(80,'2023-12-17 11:32:37',285,2850),(80,'2023-12-17 11:42:37',287,2870),(80,'2023-12-17 11:52:37',294,2940),(80,'2023-12-17 12:02:37',298,2980),(80,'2023-12-17 12:12:37',301,3010),(80,'2023-12-17 12:22:37',310,3100),(80,'2023-12-17 12:32:37',312,3120),(80,'2023-12-17 12:42:37',313,3130),(80,'2023-12-17 12:52:37',321,3210),(80,'2023-12-17 13:02:37',331,3310),(80,'2023-12-17 13:12:37',341,3410),(80,'2023-12-17 13:22:37',349,3490),(80,'2023-12-17 13:32:37',352,3520),(80,'2023-12-17 14:02:37',371,3710),(80,'2023-12-17 14:12:37',374,3740),(80,'2023-12-17 14:22:37',382,3820),(80,'2023-12-17 14:32:37',389,3890),(80,'2023-12-17 14:42:37',391,3910),(80,'2023-12-17 14:52:37',398,3980),(80,'2023-12-17 15:02:37',402,4020),(80,'2023-12-17 15:12:37',406,4060),(80,'2023-12-17 15:22:37',406,4060),(80,'2023-12-17 15:32:37',411,4110),(80,'2023-12-17 15:42:37',421,4210),(80,'2023-12-17 15:52:37',424,4240),(80,'2023-12-17 16:02:37',432,4320),(80,'2023-12-17 16:12:37',442,4420),(80,'2023-12-17 16:22:37',452,4520),(80,'2023-12-17 16:32:37',456,4560),(80,'2023-12-17 16:42:37',465,4650),(80,'2023-12-17 16:52:37',471,4710),(80,'2023-12-17 17:02:37',473,4730),(80,'2023-12-17 17:12:37',483,4830),(80,'2023-12-17 17:22:37',493,4930),(80,'2023-12-17 17:32:37',496,4960),(80,'2023-12-17 17:42:37',501,5010),(80,'2023-12-17 17:52:37',509,5090),(80,'2023-12-17 18:02:37',515,5150),(80,'2023-12-17 18:12:37',522,5220),(80,'2023-12-17 18:22:37',532,5320),(80,'2023-12-17 18:32:37',539,5390),(80,'2023-12-17 18:42:37',544,5440),(80,'2023-12-17 18:52:37',550,5500),(80,'2023-12-17 19:02:37',554,5540),(80,'2023-12-17 19:12:37',563,5630),(80,'2023-12-17 19:22:37',572,5720),(80,'2023-12-17 19:32:37',581,5810),(80,'2023-12-17 19:52:37',593,5930),(80,'2023-12-17 20:02:37',597,5970),(80,'2023-12-17 20:12:37',604,6040),(80,'2023-12-17 20:22:37',613,6130),(80,'2023-12-17 20:32:37',616,6160),(80,'2023-12-17 20:42:37',624,6240),(80,'2023-12-17 20:52:37',631,6310),(80,'2023-12-17 21:02:37',635,6350),(80,'2023-12-17 21:12:37',644,6440),(80,'2023-12-17 21:22:37',645,6450),(80,'2023-12-17 21:32:37',654,6540),(80,'2023-12-17 21:42:37',664,6640),(80,'2023-12-17 21:52:37',669,6690),(80,'2023-12-17 22:02:37',672,6720),(80,'2023-12-17 22:12:37',673,6730),(80,'2023-12-17 22:22:37',677,6770),(80,'2023-12-17 22:32:37',687,6870),(80,'2023-12-17 22:42:37',691,6910),(80,'2023-12-17 22:52:37',697,6970),(80,'2023-12-17 23:02:37',698,6980),(80,'2023-12-17 23:12:37',704,7040),(80,'2023-12-17 23:22:37',710,7100),(80,'2023-12-17 23:32:37',717,7170),(80,'2023-12-17 23:42:37',719,7190),(80,'2023-12-17 23:52:37',729,7290),(80,'2023-12-18 00:02:37',733,7330),(80,'2023-12-18 00:12:37',736,7360),(80,'2023-12-18 00:22:37',746,7460),(80,'2023-12-18 00:32:37',756,7560),(80,'2023-12-18 00:42:37',760,7600),(80,'2023-12-18 00:52:37',769,7690),(80,'2023-12-18 01:02:37',779,7790),(80,'2023-12-18 01:12:37',787,7870),(80,'2023-12-18 01:22:37',793,7930),(80,'2023-12-18 01:32:37',795,7950),(80,'2023-12-18 01:42:37',799,7990),(80,'2023-12-18 01:52:37',803,8030),(80,'2023-12-18 02:02:37',806,8060),(80,'2023-12-18 02:12:37',811,8110),(80,'2023-12-18 02:22:37',812,8120),(80,'2023-12-18 02:32:37',8,80),(80,'2023-12-18 02:42:37',11,110),(80,'2023-12-18 02:52:37',13,130),(80,'2023-12-18 03:02:37',16,160),(80,'2023-12-18 03:12:37',17,170),(80,'2023-12-18 03:22:37',23,230),(80,'2023-12-18 03:32:37',31,310),(80,'2023-12-18 03:42:37',41,410),(80,'2023-12-18 03:52:37',45,450),(80,'2023-12-18 04:02:37',54,540),(80,'2023-12-18 04:12:37',56,560),(80,'2023-12-18 04:32:37',68,680),(80,'2023-12-18 04:42:37',75,750),(80,'2023-12-18 04:52:37',84,840),(80,'2023-12-18 05:02:37',89,890),(80,'2023-12-18 05:12:37',99,990),(80,'2023-12-18 05:22:37',106,1060),(80,'2023-12-18 05:32:37',113,1130),(80,'2023-12-18 05:42:37',120,1200),(80,'2023-12-18 05:52:37',128,1280),(80,'2023-12-18 06:02:37',135,1350),(80,'2023-12-18 06:12:37',145,1450),(80,'2023-12-18 06:22:37',155,1550),(80,'2023-12-18 06:32:37',161,1610),(80,'2023-12-18 06:42:37',165,1650),(80,'2023-12-18 06:52:37',172,1720),(80,'2023-12-18 07:02:37',175,1750),(80,'2023-12-18 07:12:37',183,1830),(80,'2023-12-18 07:22:37',191,1910),(80,'2023-12-18 07:32:37',195,1950),(80,'2023-12-18 07:42:37',201,2010),(80,'2023-12-18 08:02:37',215,2150),(80,'2023-12-18 08:12:37',216,2160),(80,'2023-12-18 08:32:37',226,2260),(80,'2023-12-18 08:42:37',231,2310),(80,'2023-12-18 08:52:37',235,2350),(80,'2023-12-18 09:02:37',236,2360),(80,'2023-12-18 09:12:37',244,2440),(80,'2023-12-18 09:22:37',254,2540),(80,'2023-12-18 09:32:37',260,2600),(80,'2023-12-18 09:42:37',269,2690),(80,'2023-12-18 09:52:37',273,2730),(80,'2023-12-18 10:02:37',274,2740),(80,'2023-12-18 10:12:37',274,2740),(80,'2023-12-18 10:22:37',281,2810),(80,'2023-12-18 10:32:37',290,2900),(80,'2023-12-18 10:42:37',293,2930),(80,'2023-12-18 10:52:37',300,3000),(80,'2023-12-18 11:02:37',308,3080),(80,'2023-12-18 11:12:37',315,3150),(80,'2023-12-18 11:22:37',321,3210),(80,'2023-12-18 11:32:37',325,3250),(80,'2023-12-18 11:42:37',327,3270),(80,'2023-12-18 11:52:37',336,3360),(80,'2023-12-18 12:02:37',337,3370),(80,'2023-12-18 12:12:37',340,3400),(80,'2023-12-18 12:32:37',352,3520),(80,'2023-12-18 12:42:37',355,3550),(80,'2023-12-18 12:52:37',360,3600),(80,'2023-12-18 13:02:37',368,3680),(80,'2023-12-18 13:12:37',376,3760),(80,'2023-12-18 13:22:37',379,3790),(80,'2023-12-18 13:32:37',384,3840),(80,'2023-12-18 13:42:37',391,3910),(80,'2023-12-18 13:52:37',393,3930),(80,'2023-12-18 14:02:37',402,4020),(80,'2023-12-18 14:12:37',410,4100),(80,'2023-12-18 14:22:37',414,4140),(80,'2023-12-18 14:32:37',420,4200),(80,'2023-12-18 14:42:37',427,4270),(80,'2023-12-18 14:52:37',433,4330),(80,'2023-12-18 15:02:37',438,4380),(80,'2023-12-18 15:12:37',445,4450),(80,'2023-12-18 15:22:37',450,4500),(80,'2023-12-18 15:32:37',453,4530),(80,'2023-12-18 15:42:37',460,4600),(80,'2023-12-18 15:52:37',463,4630),(80,'2023-12-18 16:02:37',467,4670),(80,'2023-12-18 16:12:37',475,4750),(80,'2023-12-18 16:22:37',481,4810),(80,'2023-12-18 16:32:37',487,4870),(80,'2023-12-18 16:42:37',490,4900),(80,'2023-12-18 16:52:37',9,90),(80,'2023-12-18 17:02:37',12,120),(80,'2023-12-18 17:12:37',20,200),(80,'2023-12-18 17:22:37',27,270),(80,'2023-12-18 17:32:37',31,310),(80,'2023-12-18 17:42:37',33,330),(80,'2023-12-18 17:52:37',42,420),(80,'2023-12-18 18:02:37',51,510),(80,'2023-12-18 18:22:37',66,660),(80,'2023-12-18 18:32:37',74,740),(80,'2023-12-18 18:42:37',78,780),(80,'2023-12-18 18:52:37',84,840),(80,'2023-12-18 19:02:37',88,880),(80,'2023-12-18 19:12:37',97,970),(80,'2023-12-18 19:22:37',106,1060),(80,'2023-12-18 19:32:37',115,1150),(80,'2023-12-18 19:52:37',127,1270),(80,'2023-12-18 20:02:37',134,1340),(80,'2023-12-18 20:12:37',143,1430),(80,'2023-12-18 20:22:37',145,1450),(80,'2023-12-18 20:32:37',147,1470),(80,'2023-12-18 20:42:37',157,1570),(80,'2023-12-18 20:52:37',159,1590),(80,'2023-12-18 21:02:37',169,1690),(80,'2023-12-18 21:12:37',177,1770),(80,'2023-12-18 21:22:37',186,1860),(80,'2023-12-18 21:32:37',196,1960),(80,'2023-12-18 21:42:37',205,2050),(80,'2023-12-18 21:52:37',215,2150),(80,'2023-12-18 22:02:37',217,2170),(80,'2023-12-18 22:12:37',225,2250),(80,'2023-12-18 22:22:37',229,2290),(80,'2023-12-18 22:32:37',232,2320),(80,'2023-12-18 22:42:37',237,2370),(80,'2023-12-18 22:52:37',245,2450),(80,'2023-12-18 23:02:37',255,2550),(80,'2023-12-18 23:12:37',265,2650),(80,'2023-12-18 23:32:37',283,2830),(80,'2023-12-18 23:42:37',293,2930),(80,'2023-12-18 23:52:37',302,3020),(80,'2023-12-19 00:02:37',306,3060),(80,'2023-12-19 00:12:37',306,3060),(80,'2023-12-19 00:22:37',314,3140),(80,'2023-12-19 00:32:37',318,3180),(80,'2023-12-19 00:42:37',326,3260),(80,'2023-12-19 00:52:37',334,3340),(80,'2023-12-19 01:02:37',343,3430),(80,'2023-12-19 01:12:37',353,3530),(80,'2023-12-19 01:22:37',358,3580),(80,'2023-12-19 01:42:37',371,3710),(80,'2023-12-19 01:52:37',373,3730),(80,'2023-12-19 02:02:37',381,3810),(80,'2023-12-19 02:12:37',390,3900),(80,'2023-12-19 02:22:37',393,3930),(80,'2023-12-19 02:32:37',396,3960),(80,'2023-12-19 02:42:37',398,3980),(80,'2023-12-19 02:52:37',408,4080),(80,'2023-12-19 03:02:37',417,4170),(80,'2023-12-19 03:12:37',427,4270),(80,'2023-12-19 03:22:37',429,4290),(80,'2023-12-19 03:32:37',436,4360),(80,'2023-12-19 03:52:37',448,4480),(80,'2023-12-19 04:12:37',464,4640),(80,'2023-12-19 04:22:37',470,4700),(80,'2023-12-19 04:32:37',475,4750),(80,'2023-12-19 04:42:37',477,4770),(80,'2023-12-19 04:52:37',477,4770),(80,'2023-12-19 05:02:37',487,4870),(80,'2023-12-19 05:12:37',493,4930),(80,'2023-12-19 05:22:37',501,5010),(80,'2023-12-19 05:32:37',507,5070),(80,'2023-12-19 05:42:37',515,5150),(80,'2023-12-19 05:52:37',522,5220),(80,'2023-12-19 06:02:37',530,5300),(80,'2023-12-19 06:12:37',537,5370),(80,'2023-12-19 06:22:37',541,5410),(80,'2023-12-19 06:32:37',544,5440),(80,'2023-12-19 06:42:37',550,5500),(80,'2023-12-19 07:02:37',563,5630),(80,'2023-12-19 07:12:37',565,5650),(80,'2023-12-19 07:22:37',568,5680),(80,'2023-12-19 07:32:37',572,5720),(80,'2023-12-19 07:52:37',579,5790),(80,'2023-12-19 08:02:37',586,5860),(80,'2023-12-19 08:12:37',594,5940),(80,'2023-12-19 08:22:37',603,6030),(80,'2023-12-19 08:32:37',608,6080),(80,'2023-12-19 08:42:37',614,6140),(353,'2023-12-16 08:52:37',2,20),(353,'2023-12-16 09:02:37',7,70),(353,'2023-12-16 09:22:37',24,240),(353,'2023-12-16 09:32:37',34,340),(353,'2023-12-16 09:42:37',35,350),(353,'2023-12-16 09:52:37',45,450),(353,'2023-12-16 10:02:37',54,540),(353,'2023-12-16 10:12:37',54,540),(353,'2023-12-16 10:22:37',62,620),(353,'2023-12-16 10:32:37',71,710),(353,'2023-12-16 10:42:37',75,750),(353,'2023-12-16 10:52:37',81,810),(353,'2023-12-16 11:02:37',91,910),(353,'2023-12-16 11:12:37',100,1000),(353,'2023-12-16 11:22:37',108,1080),(353,'2023-12-16 11:32:37',118,1180),(353,'2023-12-16 11:42:37',123,1230),(353,'2023-12-16 11:52:37',127,1270),(353,'2023-12-16 12:02:37',137,1370),(353,'2023-12-16 12:12:37',144,1440),(353,'2023-12-16 12:22:37',153,1530),(353,'2023-12-16 12:32:37',161,1610),(353,'2023-12-16 12:42:37',167,1670),(353,'2023-12-16 12:52:37',177,1770),(353,'2023-12-16 13:02:37',178,1780),(353,'2023-12-16 13:12:37',181,1810),(353,'2023-12-16 13:22:37',184,1840),(353,'2023-12-16 13:32:37',193,1930),(353,'2023-12-16 13:42:37',202,2020),(353,'2023-12-16 13:52:37',205,2050),(353,'2023-12-16 14:02:37',210,2100),(353,'2023-12-16 14:12:37',220,2200),(353,'2023-12-16 14:22:37',225,2250),(353,'2023-12-16 14:32:37',230,2300),(353,'2023-12-16 14:42:37',236,2360),(353,'2023-12-16 14:52:37',240,2400),(353,'2023-12-16 15:02:37',246,2460),(353,'2023-12-16 15:12:37',246,2460),(353,'2023-12-16 15:22:37',252,2520),(353,'2023-12-16 15:32:37',259,2590),(353,'2023-12-16 15:42:37',259,2590),(353,'2023-12-16 15:52:37',269,2690),(353,'2023-12-16 16:02:37',278,2780),(353,'2023-12-16 16:12:37',284,2840),(353,'2023-12-16 16:22:37',291,2910),(353,'2023-12-16 16:32:37',299,2990),(353,'2023-12-16 16:42:37',308,3080),(353,'2023-12-16 16:52:37',314,3140),(353,'2023-12-16 17:02:37',314,3140),(353,'2023-12-16 17:12:37',323,3230),(353,'2023-12-16 17:22:37',329,3290),(353,'2023-12-16 17:32:37',333,3330),(353,'2023-12-16 17:42:37',339,3390),(353,'2023-12-16 17:52:37',341,3410),(353,'2023-12-16 18:02:37',350,3500),(353,'2023-12-16 18:12:37',356,3560),(353,'2023-12-16 18:22:37',359,3590),(353,'2023-12-16 18:32:37',359,3590),(353,'2023-12-16 18:42:37',363,3630),(353,'2023-12-16 18:52:37',371,3710),(353,'2023-12-16 19:02:37',375,3750),(353,'2023-12-16 19:12:37',385,3850),(353,'2023-12-16 19:22:37',392,3920),(353,'2023-12-16 19:32:37',399,3990),(353,'2023-12-16 19:42:37',403,4030),(353,'2023-12-16 19:52:37',409,4090),(353,'2023-12-16 20:02:37',416,4160),(353,'2023-12-16 20:12:37',426,4260),(353,'2023-12-16 20:22:37',431,4310),(353,'2023-12-16 20:32:37',440,4400),(353,'2023-12-16 20:52:37',457,4570),(353,'2023-12-16 21:02:37',465,4650),(353,'2023-12-16 21:12:37',466,4660),(353,'2023-12-16 21:22:37',470,4700),(353,'2023-12-16 21:32:37',477,4770),(353,'2023-12-16 21:42:37',482,4820),(353,'2023-12-16 21:52:37',486,4860),(353,'2023-12-16 22:02:37',495,4950),(353,'2023-12-16 22:12:37',504,5040),(353,'2023-12-16 22:22:37',509,5090),(353,'2023-12-16 22:32:37',515,5150),(353,'2023-12-16 22:42:37',524,5240),(353,'2023-12-16 23:02:37',540,5400),(353,'2023-12-16 23:12:37',546,5460),(353,'2023-12-16 23:22:37',554,5540),(353,'2023-12-16 23:32:37',556,5560),(353,'2023-12-16 23:42:37',562,5620),(353,'2023-12-16 23:52:37',568,5680),(353,'2023-12-17 00:12:37',577,5770),(353,'2023-12-17 00:22:37',586,5860),(353,'2023-12-17 00:32:37',587,5870),(353,'2023-12-17 00:42:37',591,5910),(353,'2023-12-17 00:52:37',595,5950),(353,'2023-12-17 01:02:37',602,6020),(353,'2023-12-17 01:12:37',605,6050),(353,'2023-12-17 01:22:37',609,6090),(353,'2023-12-17 01:32:37',616,6160),(353,'2023-12-17 01:42:37',617,6170),(353,'2023-12-17 01:52:37',626,6260),(353,'2023-12-17 02:02:37',635,6350),(353,'2023-12-17 02:12:37',642,6420),(353,'2023-12-17 02:22:37',648,6480),(353,'2023-12-17 02:32:37',655,6550),(353,'2023-12-17 02:42:37',664,6640),(353,'2023-12-17 02:52:37',666,6660),(353,'2023-12-17 03:02:37',673,6730),(353,'2023-12-17 03:12:37',675,6750),(353,'2023-12-17 03:22:37',684,6840),(353,'2023-12-17 03:32:37',686,6860),(353,'2023-12-17 03:52:37',696,6960),(353,'2023-12-17 04:02:37',705,7050),(353,'2023-12-17 04:12:37',714,7140),(353,'2023-12-17 04:22:37',722,7220),(353,'2023-12-17 04:32:37',732,7320),(353,'2023-12-17 04:42:37',740,7400),(353,'2023-12-17 04:52:37',9,90),(353,'2023-12-17 05:02:37',17,170),(353,'2023-12-17 05:12:37',25,250),(353,'2023-12-17 05:22:37',30,300),(353,'2023-12-17 05:32:37',35,350),(353,'2023-12-17 05:52:37',42,420),(353,'2023-12-17 06:02:37',45,450),(353,'2023-12-17 06:12:37',50,500),(353,'2023-12-17 06:22:37',59,590),(353,'2023-12-17 06:32:37',67,670),(353,'2023-12-17 06:42:37',68,680),(353,'2023-12-17 06:52:37',71,710),(353,'2023-12-17 07:02:37',78,780),(353,'2023-12-17 07:12:37',81,810),(353,'2023-12-17 07:22:37',86,860),(353,'2023-12-17 07:32:37',94,940),(353,'2023-12-17 07:42:37',100,1000),(353,'2023-12-17 07:52:37',109,1090),(353,'2023-12-17 08:02:37',118,1180),(353,'2023-12-17 08:12:37',124,1240),(353,'2023-12-17 08:22:37',132,1320),(353,'2023-12-17 08:32:37',138,1380),(353,'2023-12-17 08:42:37',147,1470),(353,'2023-12-17 08:52:37',154,1540),(353,'2023-12-17 09:02:37',164,1640),(353,'2023-12-17 09:12:37',165,1650),(353,'2023-12-17 09:22:37',175,1750),(353,'2023-12-17 09:32:37',175,1750),(353,'2023-12-17 09:42:37',180,1800),(353,'2023-12-17 09:52:37',183,1830),(353,'2023-12-17 10:02:37',188,1880),(353,'2023-12-17 10:12:37',195,1950),(353,'2023-12-17 10:22:37',205,2050),(353,'2023-12-17 10:32:37',213,2130),(353,'2023-12-17 10:42:37',216,2160),(353,'2023-12-17 10:52:37',10,100),(353,'2023-12-17 11:02:37',11,110),(353,'2023-12-17 11:12:37',16,160),(353,'2023-12-17 11:22:37',25,250),(353,'2023-12-17 11:32:37',35,350),(353,'2023-12-17 11:42:37',43,430),(353,'2023-12-17 11:52:37',49,490),(353,'2023-12-17 12:02:37',54,540),(353,'2023-12-17 12:12:37',57,570),(353,'2023-12-17 12:22:37',65,650),(353,'2023-12-17 12:32:37',71,710),(353,'2023-12-17 12:42:37',77,770),(353,'2023-12-17 13:02:37',91,910),(353,'2023-12-17 13:12:37',100,1000),(353,'2023-12-17 13:22:37',110,1100),(353,'2023-12-17 13:32:37',118,1180),(353,'2023-12-17 13:42:37',125,1250),(353,'2023-12-17 13:52:37',127,1270),(353,'2023-12-17 14:02:37',133,1330),(353,'2023-12-17 14:12:37',135,1350),(353,'2023-12-17 14:22:37',139,1390),(353,'2023-12-17 14:32:37',148,1480),(353,'2023-12-17 14:42:37',154,1540),(353,'2023-12-17 14:52:37',9,90),(353,'2023-12-17 15:02:37',15,150),(353,'2023-12-17 15:12:37',19,190),(353,'2023-12-17 15:32:37',30,300),(353,'2023-12-17 15:42:37',40,400),(353,'2023-12-17 15:52:37',41,410),(353,'2023-12-17 16:02:37',43,430),(353,'2023-12-17 16:12:37',53,530),(353,'2023-12-17 16:22:37',60,600),(353,'2023-12-17 16:32:37',66,660),(353,'2023-12-17 16:42:37',76,760),(353,'2023-12-17 16:52:37',79,790),(353,'2023-12-17 17:02:37',85,850),(353,'2023-12-17 17:12:37',91,910),(353,'2023-12-17 17:22:37',100,1000),(353,'2023-12-17 17:32:37',104,1040),(353,'2023-12-17 17:52:37',118,1180),(353,'2023-12-17 18:02:37',122,1220),(353,'2023-12-17 18:12:37',132,1320),(353,'2023-12-17 18:22:37',136,1360),(353,'2023-12-17 18:32:37',144,1440),(353,'2023-12-17 18:42:37',154,1540),(353,'2023-12-17 18:52:37',156,1560),(353,'2023-12-17 19:02:37',160,1600),(353,'2023-12-17 19:12:37',170,1700),(353,'2023-12-17 19:22:37',176,1760),(353,'2023-12-17 19:32:37',179,1790),(353,'2023-12-17 19:42:37',189,1890),(353,'2023-12-17 19:52:37',198,1980),(353,'2023-12-17 20:02:37',204,2040),(353,'2023-12-17 20:12:37',213,2130),(353,'2023-12-17 20:22:37',215,2150),(353,'2023-12-17 20:32:37',221,2210),(353,'2023-12-17 20:42:37',225,2250),(353,'2023-12-17 20:52:37',235,2350),(353,'2023-12-17 21:02:37',242,2420),(353,'2023-12-17 21:12:37',247,2470),(353,'2023-12-17 21:22:37',255,2550),(353,'2023-12-17 21:42:37',8,80),(353,'2023-12-17 21:52:37',18,180),(353,'2023-12-17 22:02:37',21,210),(353,'2023-12-17 22:12:37',31,310),(353,'2023-12-17 22:22:37',37,370),(353,'2023-12-17 22:32:37',46,460),(353,'2023-12-17 22:42:37',56,560),(353,'2023-12-17 22:52:37',59,590),(353,'2023-12-17 23:02:37',66,660),(353,'2023-12-17 23:12:37',69,690),(353,'2023-12-17 23:22:37',79,790),(353,'2023-12-17 23:32:37',84,840),(353,'2023-12-17 23:42:37',94,940),(353,'2023-12-17 23:52:37',103,1030),(353,'2023-12-18 00:02:37',105,1050),(353,'2023-12-18 00:12:37',107,1070),(353,'2023-12-18 00:22:37',111,1110),(353,'2023-12-18 00:32:37',116,1160),(353,'2023-12-18 00:42:37',124,1240),(353,'2023-12-18 00:52:37',126,1260),(353,'2023-12-18 01:02:37',133,1330),(353,'2023-12-18 01:12:37',134,1340),(353,'2023-12-18 01:22:37',142,1420),(353,'2023-12-18 01:32:37',151,1510),(353,'2023-12-18 01:42:37',159,1590),(353,'2023-12-18 01:52:37',166,1660),(353,'2023-12-18 02:02:37',166,1660),(353,'2023-12-18 02:12:37',169,1690),(353,'2023-12-18 02:22:37',174,1740),(353,'2023-12-18 02:32:37',183,1830),(353,'2023-12-18 02:42:37',192,1920),(353,'2023-12-18 02:52:37',201,2010),(353,'2023-12-18 03:02:37',206,2060),(353,'2023-12-18 03:12:37',209,2090),(353,'2023-12-18 03:22:37',218,2180),(353,'2023-12-18 03:32:37',226,2260),(353,'2023-12-18 03:42:37',230,2300),(353,'2023-12-18 03:52:37',235,2350),(353,'2023-12-18 04:02:37',239,2390),(353,'2023-12-18 04:12:37',244,2440),(353,'2023-12-18 04:22:37',246,2460),(353,'2023-12-18 04:32:37',246,2460),(353,'2023-12-18 04:42:37',250,2500),(353,'2023-12-18 04:52:37',255,2550),(353,'2023-12-18 05:02:37',259,2590),(353,'2023-12-18 05:12:37',267,2670),(353,'2023-12-18 05:22:37',277,2770),(353,'2023-12-18 05:32:37',287,2870),(353,'2023-12-18 05:42:37',294,2940),(353,'2023-12-18 05:52:37',301,3010),(353,'2023-12-18 06:02:37',309,3090),(353,'2023-12-18 06:12:37',313,3130),(353,'2023-12-18 06:22:37',313,3130),(353,'2023-12-18 06:32:37',320,3200),(353,'2023-12-18 06:42:37',320,3200),(353,'2023-12-18 07:02:37',334,3340),(353,'2023-12-18 07:12:37',334,3340),(353,'2023-12-18 07:22:37',344,3440),(353,'2023-12-18 07:32:37',352,3520),(353,'2023-12-18 07:42:37',362,3620),(353,'2023-12-18 07:52:37',369,3690),(353,'2023-12-18 08:02:37',373,3730),(353,'2023-12-18 08:12:37',380,3800),(353,'2023-12-18 08:22:37',387,3870),(353,'2023-12-18 08:32:37',389,3890),(353,'2023-12-18 08:42:37',396,3960),(353,'2023-12-18 08:52:37',398,3980),(353,'2023-12-18 09:02:37',403,4030),(353,'2023-12-18 09:12:37',407,4070),(353,'2023-12-18 09:22:37',410,4100),(353,'2023-12-18 09:32:37',416,4160),(353,'2023-12-18 09:42:37',420,4200),(353,'2023-12-18 09:52:37',427,4270),(353,'2023-12-18 10:02:37',430,4300),(353,'2023-12-18 10:12:37',438,4380),(353,'2023-12-18 10:22:37',439,4390),(353,'2023-12-18 10:32:37',443,4430),(353,'2023-12-18 10:42:37',451,4510),(353,'2023-12-18 10:52:37',461,4610),(353,'2023-12-18 11:02:37',465,4650),(353,'2023-12-18 11:12:37',466,4660),(353,'2023-12-18 11:22:37',471,4710),(353,'2023-12-18 11:32:37',477,4770),(353,'2023-12-18 11:42:37',480,4800),(353,'2023-12-18 11:52:37',489,4890),(353,'2023-12-18 12:02:37',495,4950),(353,'2023-12-18 12:12:37',499,4990),(353,'2023-12-18 12:22:37',500,5000),(353,'2023-12-18 12:32:37',509,5090),(353,'2023-12-18 12:42:37',519,5190),(353,'2023-12-18 12:52:37',523,5230),(353,'2023-12-18 13:02:37',525,5250),(353,'2023-12-18 13:12:37',533,5330),(353,'2023-12-18 13:22:37',536,5360),(353,'2023-12-18 13:32:37',543,5430),(353,'2023-12-18 13:42:37',550,5500),(353,'2023-12-18 13:52:37',560,5600),(353,'2023-12-18 14:02:37',569,5690),(353,'2023-12-18 14:12:37',572,5720),(353,'2023-12-18 14:22:37',579,5790),(353,'2023-12-18 14:32:37',583,5830),(353,'2023-12-18 14:42:37',593,5930),(353,'2023-12-18 14:52:37',599,5990),(353,'2023-12-18 15:02:37',607,6070),(353,'2023-12-18 15:12:37',616,6160),(353,'2023-12-18 15:22:37',621,6210),(353,'2023-12-18 15:32:37',630,6300),(353,'2023-12-18 15:42:37',633,6330),(353,'2023-12-18 15:52:37',643,6430),(353,'2023-12-18 16:02:37',646,6460),(353,'2023-12-18 16:12:37',653,6530),(353,'2023-12-18 16:22:37',661,6610),(353,'2023-12-18 16:32:37',670,6700),(353,'2023-12-18 16:42:37',672,6720),(353,'2023-12-18 16:52:37',678,6780),(353,'2023-12-18 17:02:37',685,6850),(353,'2023-12-18 17:12:37',691,6910),(353,'2023-12-18 17:22:37',699,6990),(353,'2023-12-18 17:32:37',7,70),(353,'2023-12-18 17:42:37',7,70),(353,'2023-12-18 17:52:37',17,170),(353,'2023-12-18 18:02:37',24,240),(353,'2023-12-18 18:12:37',9,90),(353,'2023-12-18 18:22:37',16,160),(353,'2023-12-18 18:32:37',19,190),(353,'2023-12-18 18:42:37',4,40),(353,'2023-12-18 18:52:37',4,40),(353,'2023-12-18 19:02:37',6,60),(353,'2023-12-18 19:12:37',8,80),(353,'2023-12-18 19:22:37',15,150),(353,'2023-12-18 19:32:37',21,210),(353,'2023-12-18 19:42:37',29,290),(353,'2023-12-18 19:52:37',31,310),(353,'2023-12-18 20:02:37',35,350),(353,'2023-12-18 20:12:37',39,390),(353,'2023-12-18 20:22:37',49,490),(353,'2023-12-18 20:32:37',53,530),(353,'2023-12-18 20:42:37',59,590),(353,'2023-12-18 20:52:37',64,640),(353,'2023-12-18 21:02:37',73,730),(353,'2023-12-18 21:12:37',77,770),(353,'2023-12-18 21:22:37',83,830),(353,'2023-12-18 21:32:37',87,870),(353,'2023-12-18 21:42:37',93,930),(353,'2023-12-18 21:52:37',97,970),(353,'2023-12-18 22:02:37',105,1050),(353,'2023-12-18 22:12:37',111,1110),(353,'2023-12-18 22:22:37',121,1210),(353,'2023-12-18 22:32:37',125,1250),(353,'2023-12-18 22:42:37',130,1300),(353,'2023-12-18 22:52:37',139,1390),(353,'2023-12-18 23:02:37',145,1450),(353,'2023-12-18 23:12:37',155,1550),(353,'2023-12-18 23:22:37',157,1570),(353,'2023-12-18 23:32:37',163,1630),(353,'2023-12-18 23:42:37',171,1710),(353,'2023-12-18 23:52:37',178,1780),(353,'2023-12-19 00:02:37',180,1800),(353,'2023-12-19 00:12:37',187,1870),(353,'2023-12-19 00:22:37',193,1930),(353,'2023-12-19 00:32:37',202,2020),(353,'2023-12-19 00:42:37',204,2040),(353,'2023-12-19 00:52:37',204,2040),(353,'2023-12-19 01:12:37',221,2210),(353,'2023-12-19 01:22:37',228,2280),(353,'2023-12-19 01:32:37',233,2330),(353,'2023-12-19 01:42:37',242,2420),(353,'2023-12-19 01:52:37',246,2460),(353,'2023-12-19 02:02:37',248,2480),(353,'2023-12-19 02:12:37',249,2490),(353,'2023-12-19 02:22:37',251,2510),(353,'2023-12-19 02:32:37',254,2540),(353,'2023-12-19 02:42:37',262,2620),(353,'2023-12-19 02:52:37',268,2680),(353,'2023-12-19 03:02:37',268,2680),(353,'2023-12-19 03:12:37',274,2740),(353,'2023-12-19 03:22:37',279,2790),(353,'2023-12-19 03:32:37',289,2890),(353,'2023-12-19 03:42:37',297,2970),(353,'2023-12-19 03:52:37',298,2980),(353,'2023-12-19 04:02:37',303,3030),(353,'2023-12-19 04:12:37',311,3110),(353,'2023-12-19 04:22:37',319,3190),(353,'2023-12-19 04:32:37',329,3290),(353,'2023-12-19 04:42:37',329,3290),(353,'2023-12-19 04:52:37',330,3300),(353,'2023-12-19 05:02:37',332,3320),(353,'2023-12-19 05:12:37',339,3390),(353,'2023-12-19 05:22:37',340,3400),(353,'2023-12-19 05:32:37',347,3470),(353,'2023-12-19 05:42:37',357,3570),(353,'2023-12-19 05:52:37',365,3650),(353,'2023-12-19 06:02:37',370,3700),(353,'2023-12-19 06:12:37',377,3770),(353,'2023-12-19 06:22:37',380,3800),(353,'2023-12-19 06:32:37',381,3810),(353,'2023-12-19 06:42:37',390,3900),(353,'2023-12-19 06:52:37',398,3980),(353,'2023-12-19 07:02:37',405,4050),(353,'2023-12-19 07:12:37',408,4080),(353,'2023-12-19 07:22:37',417,4170),(353,'2023-12-19 07:32:37',426,4260),(353,'2023-12-19 07:42:37',428,4280),(353,'2023-12-19 07:52:37',437,4370),(353,'2023-12-19 08:02:37',443,4430),(353,'2023-12-19 08:12:37',452,4520),(353,'2023-12-19 08:22:37',460,4600),(353,'2023-12-19 08:32:37',463,4630),(353,'2023-12-19 08:42:37',473,4730);
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
  `user_config` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `properties` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_USER_GUID` (`user_id`,`guid`),
  KEY `IDX_793D49D64D218E` (`location_id`),
  KEY `IDX_793D49DA76ED395` (`user_id`),
  KEY `IDX_793D49DF142C1A4` (`original_location_id`),
  CONSTRAINT `FK_793D49D64D218E` FOREIGN KEY (`location_id`) REFERENCES `supla_location` (`id`),
  CONSTRAINT `FK_793D49DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`),
  CONSTRAINT `FK_793D49DF142C1A4` FOREIGN KEY (`original_location_id`) REFERENCES `supla_location` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_iodevice`
--

LOCK TABLES `supla_iodevice` WRITE;
/*!40000 ALTER TABLE `supla_iodevice` DISABLE KEYS */;
INSERT INTO `supla_iodevice` VALUES (1,4,1,'5185940','SONOFF-DS',1,NULL,'2023-12-19 08:52:32',721663925,'2023-12-19 08:52:32',NULL,'2.27',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(2,5,1,'856603','UNI-MODULE',1,NULL,'2023-12-19 08:52:32',1950085260,'2023-12-19 08:52:32',NULL,'2.46',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(3,3,1,'611261','RGB-801',1,NULL,'2023-12-19 08:52:32',262432893,'2023-12-19 08:52:32',NULL,'2.14',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(4,4,1,'3278624','ALL-IN-ONE MEGA DEVICE',1,NULL,'2023-12-19 08:52:32',3388952006,'2023-12-19 08:52:32',NULL,'2.13',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(5,3,1,'4717386','HVAC-Monster',1,NULL,'2023-12-19 08:52:32',2484249997,'2023-12-19 08:52:32',NULL,'2.33',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\":\"OFF_WHEN_CONNECTED\",\"screenBrightness\":{\"level\":13},\"buttonVolume\":14,\"userInterface\":{\"disabled\":false},\"automaticTimeSync\":false,\"homeScreen\":{\"content\":\"TEMPERATURE\",\"offDelay\":60}}','{\"homeScreenContentAvailable\":[\"NONE\",\"TEMPERATURE\",\"HUMIDITY\",\"TIME\",\"TIME_DATE\",\"TEMPERATURE_TIME\",\"MAIN_AND_AUX_TEMPERATURE\"]}'),(6,4,1,'4726138','SECOND MEGA DEVICE',1,NULL,'2023-12-19 08:52:32',2279494256,'2023-12-19 08:52:32',NULL,'2.39',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(7,4,1,'7516562','OH-MY-GATES. This device also has ridiculously long name!',1,NULL,'2023-12-19 08:52:33',490716486,'2023-12-19 08:52:33',NULL,'2.5',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(8,5,1,'4143690','MAXIME',1,NULL,'2023-12-19 08:52:33',3966512073,'2023-12-19 08:52:33',NULL,'2.18',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(9,5,1,'4776136','NOBIS-UT',1,NULL,'2023-12-19 08:52:33',692211295,'2023-12-19 08:52:33',NULL,'2.31',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(10,5,1,'2565247','TENETUR-VEL-ALIQUAM',1,NULL,'2023-12-19 08:52:33',4088277604,'2023-12-19 08:52:33',NULL,'2.42',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(11,5,1,'3461151','VOLUPTATIBUS',1,NULL,'2023-12-19 08:52:33',3465678276,'2023-12-19 08:52:33',NULL,'2.48',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(12,5,1,'4476557','EOS-CONSECTETUR-VOLUPTATIBUS',1,NULL,'2023-12-19 08:52:33',4089750799,'2023-12-19 08:52:33',NULL,'2.49',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(13,5,1,'5971469','OMNIS-REPUDIANDAE-ASPERNATUR',1,NULL,'2023-12-19 08:52:34',1276968995,'2023-12-19 08:52:34',NULL,'2.50',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(14,5,1,'5710845','CORRUPTI-FUGIT',1,NULL,'2023-12-19 08:52:34',1826512341,'2023-12-19 08:52:34',NULL,'2.7',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(15,5,1,'206134','SUSCIPIT',1,NULL,'2023-12-19 08:52:34',1444319085,'2023-12-19 08:52:34',NULL,'2.25',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(16,5,1,'8552334','AMET-UT-DOLORIBUS',1,NULL,'2023-12-19 08:52:34',1942612780,'2023-12-19 08:52:34',NULL,'2.22',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(17,5,1,'6132129','QUO-HARUM-ESSE',1,NULL,'2023-12-19 08:52:34',1803866343,'2023-12-19 08:52:34',NULL,'2.26',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(18,5,1,'3823203','MINUS',1,NULL,'2023-12-19 08:52:34',912198517,'2023-12-19 08:52:34',NULL,'2.40',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(19,5,1,'9490438','ASPERNATUR-VITAE',1,NULL,'2023-12-19 08:52:35',574428101,'2023-12-19 08:52:35',NULL,'2.3',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(20,5,1,'5454781','QUASI-NOSTRUM-ET',1,NULL,'2023-12-19 08:52:35',2527195078,'2023-12-19 08:52:35',NULL,'2.21',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(21,5,1,'825280','ID',1,NULL,'2023-12-19 08:52:35',773094862,'2023-12-19 08:52:35',NULL,'2.29',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(22,5,1,'2493504','OMNIS-EXCEPTURI',1,NULL,'2023-12-19 08:52:35',3593891871,'2023-12-19 08:52:35',NULL,'2.41',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(23,6,2,'9832137','SUPLER MEGA DEVICE',1,NULL,'2023-12-19 08:52:35',1771974553,'2023-12-19 08:52:35',NULL,'2.12',2,NULL,NULL,48,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL),(24,4,1,'2570962','LOCKED-DEVICE',1,NULL,'2023-12-19 08:52:37',3108893356,'2023-12-19 08:52:37',NULL,'2.10',2,NULL,NULL,272,NULL,NULL,'{\"statusLed\": \"ON_WHEN_CONNECTED\"}',NULL);
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
INSERT INTO `supla_location` VALUES (1,1,'56ea','Location #2',1),(2,2,'becc','Location #2',1),(3,1,'de7b','Sypialnia',1),(4,1,'d475','Na zewnątrz',1),(5,1,'dc91','Garaż',1),(6,2,'71b4','Supler\'s location',1);
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
  `issued_with_refresh_token_id` int(11) DEFAULT NULL,
  `issuer_ip` int(10) unsigned DEFAULT NULL COMMENT '(DC2Type:ipaddress)',
  `issuer_browser_string` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2402564B5F37A13B` (`token`),
  KEY `IDX_2402564B19EB6921` (`client_id`),
  KEY `IDX_2402564BA76ED395` (`user_id`),
  KEY `IDX_2402564B4FEA67CF` (`access_id`),
  KEY `IDX_2402564BCA22CF77` (`api_client_authorization_id`),
  KEY `IDX_2402564BD2B4D7C8` (`issued_with_refresh_token_id`),
  CONSTRAINT `FK_2402564B19EB6921` FOREIGN KEY (`client_id`) REFERENCES `supla_oauth_clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564B4FEA67CF` FOREIGN KEY (`access_id`) REFERENCES `supla_accessid` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564BCA22CF77` FOREIGN KEY (`api_client_authorization_id`) REFERENCES `supla_oauth_client_authorizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2402564BD2B4D7C8` FOREIGN KEY (`issued_with_refresh_token_id`) REFERENCES `supla_oauth_refresh_tokens` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_oauth_access_tokens`
--

LOCK TABLES `supla_oauth_access_tokens` WRITE;
/*!40000 ALTER TABLE `supla_oauth_access_tokens` DISABLE KEYS */;
INSERT INTO `supla_oauth_access_tokens` VALUES (1,1,1,'0123456789012345678901234567890123456789',2051218800,'offline_access channels_ea channelgroups_ea channels_files state_webhook mqtt_broker scenes_ea accessids_r accessids_rw account_r account_rw channels_r channels_rw channelgroups_r channelgroups_rw clientapps_r clientapps_rw directlinks_r directlinks_rw iodevices_r iodevices_rw locations_r locations_rw scenes_r scenes_rw schedules_r schedules_rw',NULL,NULL,NULL,NULL,NULL,NULL);
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
INSERT INTO `supla_oauth_clients` VALUES (1,'2i03t63brboko0wsks8gs4so8o0g04k8gokogsgssk8co0c44s','a:0:{}','2fhy8utjvydco4w0okk0w8s48gsc8oc0wkg0gk0wcwocowocss','a:2:{i:0;s:8:\"password\";i:1;s:13:\"refresh_token\";}',1,NULL,NULL,NULL,NULL,NULL),(2,'2v9sx1qc6wqocows4ocoo80s4gggg4wcgogk40k4ocosk4o0ck','a:1:{i:0;s:35:\"http://suplascripts.local/authorize\";}','658cikjp0n40wows4c8sgwcwcow0wk44wcsw84ooks44cc8koo','a:2:{i:0;s:18:\"authorization_code\";i:1;s:13:\"refresh_token\";}',4,1,'SUPLA Scripts Tester',NULL,NULL,NULL),(3,'CALLERzqczpc4wgk0oo4wsoss040k88sks4goc0osow4sk8cgc','a:1:{i:0;s:31:\"http://localhost:8080/authorize\";}','CALLERgd2oowo408gws84kwwo88k8ck8kwk4w0kccog444wocc','a:2:{i:0;s:18:\"authorization_code\";i:1;s:13:\"refresh_token\";}',4,1,'SUPLA Caller Tester',NULL,NULL,NULL),(4,'ICONSpzqczpc4wgk0oo4wsoss040k88sks4goc0osow4sk8cgc','a:1:{i:0;s:31:\"http://localhost:8080/authorize\";}','ICONSpgd2oowo408gws84kwwo88k8ck8kwk4w0kccog444wocc','a:2:{i:0;s:18:\"authorization_code\";i:1;s:13:\"refresh_token\";}',4,1,'SUPLA Icons Tester',NULL,NULL,NULL);
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
-- Table structure for table `supla_push_notification`
--

DROP TABLE IF EXISTS `supla_push_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_push_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `iodevice_id` int(11) DEFAULT NULL,
  `managed_by_device` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sound` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2B227408A76ED395` (`user_id`),
  KEY `IDX_2B22740872F5A1AA` (`channel_id`),
  KEY `IDX_2B227408125F95D6` (`iodevice_id`),
  CONSTRAINT `FK_2B227408125F95D6` FOREIGN KEY (`iodevice_id`) REFERENCES `supla_iodevice` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2B22740872F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2B227408A76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_push_notification`
--

LOCK TABLES `supla_push_notification` WRITE;
/*!40000 ALTER TABLE `supla_push_notification` DISABLE KEYS */;
INSERT INTO `supla_push_notification` VALUES (1,1,1,1,1,NULL,'Commodi aut sit sit ❤️ non aut numquam quis dolores.',NULL),(2,1,2,1,1,'Officia sit quis et reprehenderit neque doloremque nisi.','Commodi at suscipit illo molestias.',NULL),(3,1,344,22,1,NULL,NULL,NULL),(4,1,345,22,1,'Maxime ut et suscipit occaecati iste temporibus ab voluptas.','Cumque dolorem aut voluptas.',NULL),(5,1,346,22,1,NULL,'Dolorum quam omnis nam in esse deserunt.',NULL),(6,1,347,22,1,NULL,NULL,NULL),(7,1,NULL,1,1,'Sequi quia illum aperiam ab est.','Accusantium et voluptate voluptatem commodi omnis.',NULL),(8,1,NULL,22,1,'Sed pariatur qui ut.','Quis consequatur ea quibusdam perspiciatis dicta quis.',NULL),(9,1,NULL,3,1,NULL,'Corporis enim esse est ab mollitia.',NULL);
/*!40000 ALTER TABLE `supla_push_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_rel_aid_pushnotification`
--

DROP TABLE IF EXISTS `supla_rel_aid_pushnotification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_rel_aid_pushnotification` (
  `push_notification_id` int(11) NOT NULL,
  `access_id` int(11) NOT NULL,
  PRIMARY KEY (`push_notification_id`,`access_id`),
  KEY `IDX_4A24B3E04E328CBE` (`push_notification_id`),
  KEY `IDX_4A24B3E04FEA67CF` (`access_id`),
  CONSTRAINT `FK_4A24B3E04E328CBE` FOREIGN KEY (`push_notification_id`) REFERENCES `supla_push_notification` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_4A24B3E04FEA67CF` FOREIGN KEY (`access_id`) REFERENCES `supla_accessid` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_rel_aid_pushnotification`
--

LOCK TABLES `supla_rel_aid_pushnotification` WRITE;
/*!40000 ALTER TABLE `supla_rel_aid_pushnotification` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_rel_aid_pushnotification` ENABLE KEYS */;
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
INSERT INTO `supla_rel_cg` VALUES (1,1),(190,8),(191,3),(191,7),(192,5),(193,2),(193,9),(201,8),(202,7),(203,5),(204,2),(204,9),(213,7),(215,9),(223,8),(225,11),(226,9),(234,8),(236,4),(236,5),(237,2),(237,9),(245,8),(245,10),(247,5),(247,11),(248,9),(256,10),(259,2),(259,9),(267,8),(267,10),(268,7),(269,5),(269,11),(270,2),(270,9),(278,8),(278,10),(279,7),(281,9),(289,10),(290,7),(291,11),(292,9),(300,8),(301,7),(302,11),(312,3),(313,5),(314,2),(314,9),(322,8),(323,3),(323,7),(324,5),(325,2),(325,9),(333,8),(334,7),(335,5),(336,2),(336,9),(344,1),(344,8),(345,6),(345,7),(346,5),(347,2),(347,9);
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
  `caption` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `user_icon_id` int(11) DEFAULT NULL,
  `alt_icon` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '(DC2Type:tinyint)',
  `estimated_execution_time` int(11) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `user_config` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
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
INSERT INTO `supla_scene` VALUES (1,1,1,'My scene',1,NULL,0,0,0,NULL),(2,1,3,'PaleGoldenRod',1,NULL,0,0,0,NULL),(3,1,5,'LavenderBlush',1,NULL,0,0,0,NULL),(4,1,5,'PaleGoldenRod',0,NULL,0,0,0,NULL),(5,1,3,'HoneyDew',1,NULL,0,0,0,NULL),(6,1,5,'LimeGreen',1,NULL,0,0,0,NULL),(7,1,3,'DimGray',0,NULL,0,0,0,NULL),(8,1,3,'MediumTurquoise',1,NULL,0,0,0,NULL),(9,1,4,'DarkViolet',1,NULL,0,0,0,NULL),(10,1,4,'LavenderBlush',1,NULL,0,0,0,NULL),(11,1,4,'Violet',1,NULL,0,0,0,NULL),(12,1,4,'Moccasin',0,NULL,0,0,0,NULL),(13,1,3,'LightSlateGray',1,NULL,0,0,0,NULL),(14,1,3,'DeepPink',1,NULL,0,0,0,NULL),(15,1,3,'Moccasin',1,NULL,0,0,0,NULL),(16,1,3,'LightSeaGreen',1,NULL,0,0,0,NULL);
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
  `user_delay_ms` int(11) NOT NULL DEFAULT '0',
  `wait_for_completion` tinyint(1) NOT NULL DEFAULT '0',
  `schedule_id` int(11) DEFAULT NULL,
  `push_notification_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_64A50CF5E019BC26` (`owning_scene_id`),
  KEY `IDX_64A50CF572F5A1AA` (`channel_id`),
  KEY `IDX_64A50CF589E4AAEE` (`channel_group_id`),
  KEY `IDX_64A50CF5166053B4` (`scene_id`),
  KEY `IDX_64A50CF5A40BC2D5` (`schedule_id`),
  KEY `IDX_64A50CF54E328CBE` (`push_notification_id`),
  CONSTRAINT `FK_64A50CF5166053B4` FOREIGN KEY (`scene_id`) REFERENCES `supla_scene` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64A50CF54E328CBE` FOREIGN KEY (`push_notification_id`) REFERENCES `supla_push_notification` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64A50CF572F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64A50CF589E4AAEE` FOREIGN KEY (`channel_group_id`) REFERENCES `supla_dev_channel_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64A50CF5A40BC2D5` FOREIGN KEY (`schedule_id`) REFERENCES `supla_schedule` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_64A50CF5E019BC26` FOREIGN KEY (`owning_scene_id`) REFERENCES `supla_scene` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_scene_operation`
--

LOCK TABLES `supla_scene_operation` WRITE;
/*!40000 ALTER TABLE `supla_scene_operation` DISABLE KEYS */;
INSERT INTO `supla_scene_operation` VALUES (1,1,1,NULL,NULL,110,NULL,0,0,0,NULL,NULL),(2,1,75,NULL,NULL,80,'{\"brightness\":55}',2000,2000,0,NULL,NULL),(3,1,40,NULL,NULL,90,NULL,0,0,0,NULL,NULL),(4,2,433,NULL,NULL,10100,NULL,1000,1000,0,NULL,NULL),(5,2,89,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(6,2,308,NULL,NULL,30,NULL,0,0,0,NULL,NULL),(7,2,213,NULL,NULL,10,NULL,30000,30000,0,NULL,NULL),(8,2,NULL,1,NULL,60,NULL,0,0,0,NULL,NULL),(9,2,42,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(10,2,182,NULL,NULL,10,NULL,30000,30000,0,NULL,NULL),(11,2,NULL,5,NULL,90,NULL,0,0,0,NULL,NULL),(12,2,171,NULL,NULL,60,NULL,0,0,0,NULL,NULL),(13,3,NULL,NULL,1,3000,NULL,30000,30000,0,NULL,NULL),(14,3,NULL,3,NULL,10,NULL,1000,1000,0,NULL,NULL),(15,3,NULL,NULL,1,3000,NULL,0,0,0,NULL,NULL),(16,3,NULL,5,NULL,10,NULL,1000,1000,0,NULL,NULL),(17,3,82,NULL,NULL,70,NULL,30000,30000,0,NULL,NULL),(18,4,NULL,NULL,3,3001,NULL,0,0,0,NULL,NULL),(19,4,NULL,9,NULL,100,NULL,0,0,0,NULL,NULL),(20,4,186,NULL,NULL,10,NULL,1000,1000,0,NULL,NULL),(21,4,NULL,6,NULL,10,NULL,1000,1000,0,NULL,NULL),(22,4,NULL,2,NULL,30,NULL,30000,30000,0,NULL,NULL),(23,4,NULL,NULL,1,3001,NULL,0,0,0,NULL,NULL),(24,5,NULL,NULL,3,3001,NULL,1000,1000,0,NULL,NULL),(25,5,NULL,11,NULL,10100,NULL,30000,30000,0,NULL,NULL),(26,5,334,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(27,5,433,NULL,NULL,110,NULL,0,0,0,NULL,NULL),(28,5,133,NULL,NULL,110,NULL,0,0,0,NULL,NULL),(29,6,NULL,NULL,5,3001,NULL,0,0,0,NULL,NULL),(30,6,NULL,NULL,5,3002,NULL,30000,30000,0,NULL,NULL),(31,6,53,NULL,NULL,10,NULL,1000,1000,0,NULL,NULL),(32,6,NULL,NULL,2,3002,NULL,0,0,0,NULL,NULL),(33,6,NULL,4,NULL,90,NULL,1000,1000,0,NULL,NULL),(34,6,NULL,5,NULL,90,NULL,0,0,0,NULL,NULL),(35,6,308,NULL,NULL,10100,NULL,0,0,0,NULL,NULL),(36,6,48,NULL,NULL,110,NULL,30000,30000,0,NULL,NULL),(37,6,NULL,9,NULL,40,NULL,0,0,0,NULL,NULL),(38,6,333,NULL,NULL,10100,NULL,30000,30000,0,NULL,NULL),(39,7,83,NULL,NULL,60,NULL,30000,30000,0,NULL,NULL),(40,7,NULL,NULL,6,3001,NULL,30000,30000,0,NULL,NULL),(41,7,NULL,10,NULL,70,NULL,0,0,0,NULL,NULL),(42,7,NULL,2,NULL,10100,NULL,1000,1000,0,NULL,NULL),(43,7,202,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(44,7,NULL,NULL,3,3000,NULL,0,0,0,NULL,NULL),(45,7,303,NULL,NULL,100,NULL,30000,30000,0,NULL,NULL),(46,7,NULL,11,NULL,10100,NULL,1000,1000,0,NULL,NULL),(47,8,213,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(48,8,53,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(49,8,NULL,NULL,4,3002,NULL,0,0,0,NULL,NULL),(50,8,NULL,11,NULL,10,NULL,0,0,0,NULL,NULL),(51,8,NULL,8,NULL,110,NULL,30000,30000,0,NULL,NULL),(52,9,NULL,10,NULL,60,NULL,30000,30000,0,NULL,NULL),(53,9,NULL,NULL,3,3000,NULL,0,0,0,NULL,NULL),(54,9,NULL,NULL,1,3001,NULL,0,0,0,NULL,NULL),(55,9,NULL,NULL,3,3001,NULL,0,0,0,NULL,NULL),(56,9,192,NULL,NULL,90,NULL,0,0,0,NULL,NULL),(57,9,NULL,NULL,2,3002,NULL,0,0,0,NULL,NULL),(58,10,139,NULL,NULL,110,NULL,1000,1000,0,NULL,NULL),(59,10,NULL,NULL,2,3002,NULL,0,0,0,NULL,NULL),(60,10,NULL,NULL,5,3002,NULL,0,0,0,NULL,NULL),(61,10,NULL,5,NULL,10100,NULL,0,0,0,NULL,NULL),(62,10,5,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(63,10,49,NULL,NULL,60,NULL,1000,1000,0,NULL,NULL),(64,10,314,NULL,NULL,40,NULL,0,0,0,NULL,NULL),(65,11,NULL,NULL,9,3001,NULL,0,0,0,NULL,NULL),(66,11,NULL,2,NULL,30,NULL,0,0,0,NULL,NULL),(67,11,NULL,NULL,6,3002,NULL,30000,30000,0,NULL,NULL),(68,11,NULL,10,NULL,70,NULL,0,0,0,NULL,NULL),(69,11,NULL,6,NULL,10,NULL,0,0,0,NULL,NULL),(70,11,NULL,6,NULL,10,NULL,30000,30000,0,NULL,NULL),(71,11,NULL,9,NULL,10100,NULL,0,0,0,NULL,NULL),(72,11,236,NULL,NULL,10100,NULL,0,0,0,NULL,NULL),(73,11,NULL,2,NULL,10100,NULL,0,0,0,NULL,NULL),(74,11,NULL,NULL,2,3002,NULL,0,0,0,NULL,NULL),(75,12,NULL,NULL,7,3001,NULL,0,0,0,NULL,NULL),(76,12,NULL,4,NULL,90,NULL,1000,1000,0,NULL,NULL),(77,12,NULL,NULL,11,3002,NULL,0,0,0,NULL,NULL),(78,12,NULL,NULL,5,3001,NULL,0,0,0,NULL,NULL),(79,12,NULL,NULL,6,3001,NULL,30000,30000,0,NULL,NULL),(80,12,NULL,NULL,3,3002,NULL,1000,1000,0,NULL,NULL),(81,12,NULL,NULL,6,3002,NULL,0,0,0,NULL,NULL),(82,13,131,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(83,13,236,NULL,NULL,10100,NULL,30000,30000,0,NULL,NULL),(84,13,51,NULL,NULL,90,NULL,0,0,0,NULL,NULL),(85,13,45,NULL,NULL,20,NULL,30000,30000,0,NULL,NULL),(86,14,258,NULL,NULL,10,NULL,1000,1000,0,NULL,NULL),(87,14,NULL,NULL,7,3002,NULL,1000,1000,0,NULL,NULL),(88,14,313,NULL,NULL,90,NULL,0,0,0,NULL,NULL),(89,14,158,NULL,NULL,10100,NULL,30000,30000,0,NULL,NULL),(90,14,NULL,2,NULL,40,NULL,0,0,0,NULL,NULL),(91,14,NULL,NULL,9,3002,NULL,30000,30000,0,NULL,NULL),(92,14,NULL,3,NULL,10,NULL,0,0,0,NULL,NULL),(93,14,NULL,7,NULL,10,NULL,0,0,0,NULL,NULL),(94,14,192,NULL,NULL,90,NULL,0,0,0,NULL,NULL),(95,15,135,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(96,15,NULL,NULL,7,3002,NULL,30000,30000,0,NULL,NULL),(97,15,NULL,7,NULL,10,NULL,0,0,0,NULL,NULL),(98,16,213,NULL,NULL,10,NULL,0,0,0,NULL,NULL),(99,16,NULL,2,NULL,30,NULL,30000,30000,0,NULL,NULL),(100,16,248,NULL,NULL,100,NULL,1000,1000,0,NULL,NULL),(101,16,NULL,4,NULL,10,NULL,1000,1000,0,NULL,NULL);
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
INSERT INTO `supla_schedule` VALUES (1,1,245,'daily','2023-12-19 16:09:09',NULL,1,'2023-12-20 07:05:00','Omnis qui aspernatur exercitationem facere qui.',1,NULL,NULL,'[{\"crontab\":\"SR-10 * * * *\",\"action\":{\"id\":60}}]'),(2,1,279,'minutely','2023-12-24 06:09:13',NULL,1,'2023-12-22 06:19:00','Error et nihil.',1,NULL,NULL,'[{\"crontab\":\"*\\/10 * * * *\",\"action\":{\"id\":10}}]'),(3,1,300,'daily','2023-12-23 05:58:45',NULL,1,'2023-12-21 15:05:00','Consequatur quia.',1,NULL,NULL,'[{\"crontab\":\"SS10 * * * *\",\"action\":{\"id\":110}}]'),(4,1,270,'daily','2023-12-24 03:13:58',NULL,1,'2023-12-22 07:16:00','Alias illum.',1,NULL,NULL,'[{\"crontab\":\"SR0 * * * *\",\"action\":{\"id\":10100}}]'),(5,1,269,'daily','2023-12-20 10:07:53',NULL,1,'2023-12-19 14:44:00','Consequatur eos omnis.',1,NULL,NULL,'[{\"crontab\":\"SS-10 * * * *\",\"action\":{\"id\":90}}]'),(6,1,204,'minutely','2023-12-20 06:02:30',NULL,1,'2023-12-19 09:03:00','Voluptatem consequuntur possimus.',1,NULL,NULL,'[{\"crontab\":\"*\\/60 * * * *\",\"action\":{\"id\":100}}]'),(7,1,234,'minutely','2023-12-22 08:26:29',NULL,1,'2023-12-20 09:26:00','Dolorem qui non aut iusto.',1,NULL,NULL,'[{\"crontab\":\"*\\/60 * * * *\",\"action\":{\"id\":70}}]'),(8,1,302,'daily','2023-12-21 23:16:08',NULL,1,'2023-12-20 15:05:00','Perspiciatis ad consequatur.',1,NULL,NULL,'[{\"crontab\":\"SS10 * * * *\",\"action\":{\"id\":10100}}]'),(9,1,325,'daily','2023-12-21 14:03:20',NULL,1,'2023-12-20 07:25:00','Laborum animi iure.',1,NULL,NULL,'[{\"crontab\":\"SR10 * * * *\",\"action\":{\"id\":51}}]'),(10,1,246,'minutely','2023-12-26 02:18:55',NULL,1,'2023-12-24 02:49:00','Rerum recusandae voluptates.',1,NULL,NULL,'[{\"crontab\":\"*\\/30 * * * *\",\"action\":{\"id\":10}}]'),(11,1,257,'minutely','2023-12-21 14:34:08',NULL,1,'2023-12-19 16:04:00','Ut eos dolorum.',1,NULL,NULL,'[{\"crontab\":\"*\\/90 * * * *\",\"action\":{\"id\":10}}]'),(12,1,202,'minutely','2023-12-23 12:27:28',NULL,1,'2023-12-21 12:37:00','Autem dolorem consectetur qui vel.',1,NULL,NULL,'[{\"crontab\":\"*\\/10 * * * *\",\"action\":{\"id\":10}}]'),(13,1,279,'daily','2023-12-23 18:11:21',NULL,1,'2023-12-22 07:26:00','Quisquam omnis.',1,NULL,NULL,'[{\"crontab\":\"SR10 * * * *\",\"action\":{\"id\":10}}]'),(14,1,235,'daily','2023-12-25 22:11:24',NULL,1,'2023-12-24 07:06:00','Illo rem ipsum earum.',1,NULL,NULL,'[{\"crontab\":\"SR-10 * * * *\",\"action\":{\"id\":10}}]'),(15,1,247,'minutely','2023-12-24 17:40:57',NULL,1,'2023-12-22 18:11:00','Et harum voluptatum ipsam.',1,NULL,NULL,'[{\"crontab\":\"*\\/30 * * * *\",\"action\":{\"id\":10100}}]');
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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_scheduled_executions`
--

LOCK TABLES `supla_scheduled_executions` WRITE;
/*!40000 ALTER TABLE `supla_scheduled_executions` DISABLE KEYS */;
INSERT INTO `supla_scheduled_executions` VALUES (1,1,'2023-12-20 07:04:00',NULL,NULL,NULL,NULL,0,NULL,60,NULL),(2,1,'2023-12-21 07:04:00',NULL,NULL,NULL,NULL,0,NULL,60,NULL),(3,1,'2023-12-22 07:05:00',NULL,NULL,NULL,NULL,0,NULL,60,NULL),(4,2,'2023-12-24 06:19:00',NULL,NULL,NULL,NULL,0,NULL,10,NULL),(5,3,'2023-12-23 15:05:00',NULL,NULL,NULL,NULL,0,NULL,110,NULL),(6,4,'2023-12-24 07:16:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL),(7,5,'2023-12-20 14:44:00',NULL,NULL,NULL,NULL,0,NULL,90,NULL),(8,5,'2023-12-21 14:44:00',NULL,NULL,NULL,NULL,0,NULL,90,NULL),(9,6,'2023-12-20 07:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(10,6,'2023-12-20 08:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(11,6,'2023-12-20 09:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(12,6,'2023-12-20 10:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(13,6,'2023-12-20 11:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(14,6,'2023-12-20 12:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(15,6,'2023-12-20 13:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(16,6,'2023-12-20 14:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(17,6,'2023-12-20 15:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(18,6,'2023-12-20 16:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(19,6,'2023-12-20 17:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(20,6,'2023-12-20 18:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(21,6,'2023-12-20 19:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(22,6,'2023-12-20 20:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(23,6,'2023-12-20 21:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(24,6,'2023-12-20 22:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(25,6,'2023-12-20 23:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(26,6,'2023-12-21 00:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(27,6,'2023-12-21 01:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(28,6,'2023-12-21 02:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(29,6,'2023-12-21 03:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(30,6,'2023-12-21 04:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(31,6,'2023-12-21 05:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(32,6,'2023-12-21 06:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(33,6,'2023-12-21 07:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(34,6,'2023-12-21 08:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(35,6,'2023-12-21 09:03:00',NULL,NULL,NULL,NULL,0,NULL,100,NULL),(36,7,'2023-12-22 09:26:00',NULL,NULL,NULL,NULL,0,NULL,70,NULL),(37,8,'2023-12-22 15:05:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL),(38,9,'2023-12-22 07:25:00',NULL,NULL,NULL,NULL,0,NULL,51,NULL),(39,10,'2023-12-26 02:49:00',NULL,NULL,NULL,NULL,0,NULL,10,NULL),(40,11,'2023-12-21 16:04:00',NULL,NULL,NULL,NULL,0,NULL,10,NULL),(41,12,'2023-12-23 12:37:00',NULL,NULL,NULL,NULL,0,NULL,10,NULL),(42,13,'2023-12-24 07:26:00',NULL,NULL,NULL,NULL,0,NULL,10,NULL),(43,14,'2023-12-26 07:06:00',NULL,NULL,NULL,NULL,0,NULL,10,NULL),(44,15,'2023-12-24 18:11:00',NULL,NULL,NULL,NULL,0,NULL,10100,NULL);
/*!40000 ALTER TABLE `supla_scheduled_executions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supla_settings_string`
--

DROP TABLE IF EXISTS `supla_settings_string`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_settings_string` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_814604C95E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_settings_string`
--

LOCK TABLES `supla_settings_string` WRITE;
/*!40000 ALTER TABLE `supla_settings_string` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_settings_string` ENABLE KEYS */;
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
INSERT INTO `supla_temperature_log` VALUES (2,'2023-12-16 09:02:37',11.1300),(2,'2023-12-16 09:12:37',10.6900),(2,'2023-12-16 09:22:37',11.2000),(2,'2023-12-16 09:32:37',11.2400),(2,'2023-12-16 09:42:37',10.5100),(2,'2023-12-16 09:52:37',9.6100),(2,'2023-12-16 10:02:37',8.8200),(2,'2023-12-16 10:12:37',8.0500),(2,'2023-12-16 10:22:37',9.0100),(2,'2023-12-16 10:32:37',9.1300),(2,'2023-12-16 10:42:37',8.1400),(2,'2023-12-16 10:52:37',7.6000),(2,'2023-12-16 11:02:37',6.7400),(2,'2023-12-16 11:12:37',7.0200),(2,'2023-12-16 11:22:37',6.1800),(2,'2023-12-16 11:32:37',5.7200),(2,'2023-12-16 11:42:37',6.4300),(2,'2023-12-16 11:52:37',5.8700),(2,'2023-12-16 12:02:37',5.2200),(2,'2023-12-16 12:12:37',4.3400),(2,'2023-12-16 12:22:37',5.0900),(2,'2023-12-16 12:32:37',5.6300),(2,'2023-12-16 12:42:37',6.6200),(2,'2023-12-16 12:52:37',5.8000),(2,'2023-12-16 13:02:37',5.6900),(2,'2023-12-16 13:12:37',5.0700),(2,'2023-12-16 13:22:37',4.3400),(2,'2023-12-16 13:32:37',3.6300),(2,'2023-12-16 13:42:37',4.6000),(2,'2023-12-16 13:52:37',4.2800),(2,'2023-12-16 14:02:37',5.1400),(2,'2023-12-16 14:12:37',4.2800),(2,'2023-12-16 14:22:37',3.4600),(2,'2023-12-16 14:32:37',4.3100),(2,'2023-12-16 14:42:37',4.1000),(2,'2023-12-16 14:52:37',4.6800),(2,'2023-12-16 15:02:37',5.5700),(2,'2023-12-16 15:12:37',4.5900),(2,'2023-12-16 15:32:37',4.4100),(2,'2023-12-16 15:42:37',5.1100),(2,'2023-12-16 15:52:37',4.1100),(2,'2023-12-16 16:02:37',4.9300),(2,'2023-12-16 16:12:37',5.8400),(2,'2023-12-16 16:22:37',6.0000),(2,'2023-12-16 16:32:37',5.3500),(2,'2023-12-16 16:42:37',6.1400),(2,'2023-12-16 16:52:37',6.8600),(2,'2023-12-16 17:02:37',7.3200),(2,'2023-12-16 17:12:37',8.2300),(2,'2023-12-16 17:22:37',7.3400),(2,'2023-12-16 17:32:37',8.2200),(2,'2023-12-16 17:42:37',9.1500),(2,'2023-12-16 17:52:37',9.5200),(2,'2023-12-16 18:02:37',9.3800),(2,'2023-12-16 18:12:37',8.8600),(2,'2023-12-16 18:22:37',9.2800),(2,'2023-12-16 18:32:37',10.1200),(2,'2023-12-16 18:42:37',11.0900),(2,'2023-12-16 18:52:37',11.6400),(2,'2023-12-16 19:02:37',12.6100),(2,'2023-12-16 19:12:37',13.1000),(2,'2023-12-16 19:22:37',13.7100),(2,'2023-12-16 19:32:37',13.1000),(2,'2023-12-16 19:42:37',12.1900),(2,'2023-12-16 19:52:37',12.4100),(2,'2023-12-16 20:02:37',11.5300),(2,'2023-12-16 20:12:37',11.9300),(2,'2023-12-16 20:22:37',10.9700),(2,'2023-12-16 20:32:37',10.6000),(2,'2023-12-16 20:42:37',9.6800),(2,'2023-12-16 20:52:37',8.9400),(2,'2023-12-16 21:02:37',8.1700),(2,'2023-12-16 21:12:37',9.1000),(2,'2023-12-16 21:22:37',8.5300),(2,'2023-12-16 21:32:37',8.6600),(2,'2023-12-16 21:42:37',9.5500),(2,'2023-12-16 21:52:37',8.5700),(2,'2023-12-16 22:02:37',8.8300),(2,'2023-12-16 22:12:37',8.0300),(2,'2023-12-16 22:22:37',7.4100),(2,'2023-12-16 22:42:37',6.0300),(2,'2023-12-16 22:52:37',6.5800),(2,'2023-12-16 23:02:37',6.3600),(2,'2023-12-16 23:12:37',7.1300),(2,'2023-12-16 23:22:37',7.3400),(2,'2023-12-16 23:32:37',7.5700),(2,'2023-12-16 23:42:37',6.7400),(2,'2023-12-16 23:52:37',7.6600),(2,'2023-12-17 00:02:37',7.0800),(2,'2023-12-17 00:12:37',6.2900),(2,'2023-12-17 00:22:37',5.4900),(2,'2023-12-17 00:32:37',5.8600),(2,'2023-12-17 00:42:37',5.1000),(2,'2023-12-17 00:52:37',5.8600),(2,'2023-12-17 01:02:37',5.0000),(2,'2023-12-17 01:12:37',5.5100),(2,'2023-12-17 01:22:37',5.9900),(2,'2023-12-17 01:32:37',6.5700),(2,'2023-12-17 01:42:37',5.7000),(2,'2023-12-17 01:52:37',5.6900),(2,'2023-12-17 02:02:37',5.5400),(2,'2023-12-17 02:12:37',6.4600),(2,'2023-12-17 02:22:37',6.5400),(2,'2023-12-17 02:32:37',5.9500),(2,'2023-12-17 02:42:37',6.8200),(2,'2023-12-17 02:52:37',6.2900),(2,'2023-12-17 03:02:37',6.9600),(2,'2023-12-17 03:12:37',6.0100),(2,'2023-12-17 03:22:37',5.6300),(2,'2023-12-17 03:32:37',6.0400),(2,'2023-12-17 03:42:37',5.2900),(2,'2023-12-17 03:52:37',5.8400),(2,'2023-12-17 04:12:37',5.9700),(2,'2023-12-17 04:22:37',6.8000),(2,'2023-12-17 04:32:37',5.8500),(2,'2023-12-17 04:42:37',5.4400),(2,'2023-12-17 04:52:37',5.0400),(2,'2023-12-17 05:02:37',4.5000),(2,'2023-12-17 05:12:37',5.4900),(2,'2023-12-17 05:32:37',4.3000),(2,'2023-12-17 05:52:37',5.3700),(2,'2023-12-17 06:02:37',5.2200),(2,'2023-12-17 06:22:37',5.5100),(2,'2023-12-17 06:32:37',6.4900),(2,'2023-12-17 06:42:37',7.0300),(2,'2023-12-17 06:52:37',6.2200),(2,'2023-12-17 07:02:37',6.7800),(2,'2023-12-17 07:12:37',5.9300),(2,'2023-12-17 07:22:37',6.2500),(2,'2023-12-17 07:32:37',6.4800),(2,'2023-12-17 07:42:37',7.3700),(2,'2023-12-17 07:52:37',7.6800),(2,'2023-12-17 08:12:37',8.4100),(2,'2023-12-17 08:22:37',7.8700),(2,'2023-12-17 08:32:37',7.7200),(2,'2023-12-17 08:42:37',8.0500),(2,'2023-12-17 08:52:37',7.2100),(2,'2023-12-17 09:02:37',6.5700),(2,'2023-12-17 09:12:37',5.6500),(2,'2023-12-17 09:22:37',4.9600),(2,'2023-12-17 09:32:37',4.2000),(2,'2023-12-17 09:42:37',4.4200),(2,'2023-12-17 09:52:37',4.5900),(2,'2023-12-17 10:02:37',5.3500),(2,'2023-12-17 10:12:37',5.8400),(2,'2023-12-17 10:32:37',4.1700),(2,'2023-12-17 10:42:37',4.2100),(2,'2023-12-17 10:52:37',4.8900),(2,'2023-12-17 11:02:37',5.3400),(2,'2023-12-17 11:12:37',4.6000),(2,'2023-12-17 11:22:37',5.1600),(2,'2023-12-17 11:32:37',6.0100),(2,'2023-12-17 11:42:37',6.5600),(2,'2023-12-17 11:52:37',7.3100),(2,'2023-12-17 12:02:37',6.4500),(2,'2023-12-17 12:12:37',7.0800),(2,'2023-12-17 12:22:37',7.2300),(2,'2023-12-17 12:32:37',7.7900),(2,'2023-12-17 12:42:37',7.0800),(2,'2023-12-17 12:52:37',6.4500),(2,'2023-12-17 13:02:37',6.6200),(2,'2023-12-17 13:12:37',7.1300),(2,'2023-12-17 13:22:37',7.8000),(2,'2023-12-17 13:32:37',6.9700),(2,'2023-12-17 13:42:37',7.8900),(2,'2023-12-17 14:02:37',9.0800),(2,'2023-12-17 14:12:37',8.7700),(2,'2023-12-17 14:22:37',8.4300),(2,'2023-12-17 14:32:37',9.1400),(2,'2023-12-17 14:52:37',9.5900),(2,'2023-12-17 15:02:37',10.1100),(2,'2023-12-17 15:12:37',10.9500),(2,'2023-12-17 15:22:37',11.4000),(2,'2023-12-17 15:32:37',10.9700),(2,'2023-12-17 15:42:37',10.0000),(2,'2023-12-17 15:52:37',9.4800),(2,'2023-12-17 16:02:37',8.9700),(2,'2023-12-17 16:12:37',8.0400),(2,'2023-12-17 16:22:37',8.0200),(2,'2023-12-17 16:32:37',8.2800),(2,'2023-12-17 16:42:37',9.0600),(2,'2023-12-17 16:52:37',8.1800),(2,'2023-12-17 17:02:37',8.3600),(2,'2023-12-17 17:12:37',9.3400),(2,'2023-12-17 17:22:37',8.7000),(2,'2023-12-17 17:32:37',7.7200),(2,'2023-12-17 17:42:37',8.6800),(2,'2023-12-17 17:52:37',9.4000),(2,'2023-12-17 18:02:37',9.9100),(2,'2023-12-17 18:12:37',9.2600),(2,'2023-12-17 18:22:37',10.1200),(2,'2023-12-17 18:32:37',11.1100),(2,'2023-12-17 18:42:37',10.5200),(2,'2023-12-17 18:52:37',9.7100),(2,'2023-12-17 19:02:37',9.2200),(2,'2023-12-17 19:12:37',8.4100),(2,'2023-12-17 19:22:37',7.5700),(2,'2023-12-17 19:32:37',8.5200),(2,'2023-12-17 19:42:37',9.0300),(2,'2023-12-17 19:52:37',8.1400),(2,'2023-12-17 20:02:37',8.7200),(2,'2023-12-17 20:12:37',7.9000),(2,'2023-12-17 20:22:37',8.5400),(2,'2023-12-17 20:32:37',7.6200),(2,'2023-12-17 20:42:37',6.8100),(2,'2023-12-17 20:52:37',7.7700),(2,'2023-12-17 21:02:37',7.4700),(2,'2023-12-17 21:12:37',7.1800),(2,'2023-12-17 21:22:37',6.3600),(2,'2023-12-17 21:32:37',5.4700),(2,'2023-12-17 21:42:37',6.3300),(2,'2023-12-17 21:52:37',5.3500),(2,'2023-12-17 22:02:37',6.2700),(2,'2023-12-17 22:12:37',5.9900),(2,'2023-12-17 22:22:37',6.2000),(2,'2023-12-17 22:32:37',6.6800),(2,'2023-12-17 22:42:37',6.2400),(2,'2023-12-17 22:52:37',7.1000),(2,'2023-12-17 23:02:37',7.2800),(2,'2023-12-17 23:12:37',6.6100),(2,'2023-12-17 23:22:37',6.8000),(2,'2023-12-17 23:32:37',7.7500),(2,'2023-12-17 23:42:37',7.2000),(2,'2023-12-17 23:52:37',8.0300),(2,'2023-12-18 00:02:37',8.4400),(2,'2023-12-18 00:12:37',7.6700),(2,'2023-12-18 00:22:37',8.6500),(2,'2023-12-18 00:32:37',8.3700),(2,'2023-12-18 00:42:37',9.3700),(2,'2023-12-18 00:52:37',9.3800),(2,'2023-12-18 01:02:37',9.0800),(2,'2023-12-18 01:12:37',9.6300),(2,'2023-12-18 01:22:37',10.6200),(2,'2023-12-18 01:32:37',11.4200),(2,'2023-12-18 01:42:37',10.5400),(2,'2023-12-18 01:52:37',9.6000),(2,'2023-12-18 02:02:37',9.1600),(2,'2023-12-18 02:12:37',8.1600),(2,'2023-12-18 02:22:37',8.8100),(2,'2023-12-18 02:42:37',7.3300),(2,'2023-12-18 02:52:37',8.2100),(2,'2023-12-18 03:02:37',7.7400),(2,'2023-12-18 03:12:37',6.9500),(2,'2023-12-18 03:22:37',7.4200),(2,'2023-12-18 03:32:37',8.4100),(2,'2023-12-18 03:42:37',9.1800),(2,'2023-12-18 03:52:37',8.3200),(2,'2023-12-18 04:02:37',8.9000),(2,'2023-12-18 04:12:37',8.1800),(2,'2023-12-18 04:22:37',7.3100),(2,'2023-12-18 04:32:37',6.7700),(2,'2023-12-18 04:42:37',6.4400),(2,'2023-12-18 04:52:37',7.1300),(2,'2023-12-18 05:02:37',6.8900),(2,'2023-12-18 05:22:37',6.5800),(2,'2023-12-18 05:32:37',5.6500),(2,'2023-12-18 05:42:37',5.9400),(2,'2023-12-18 05:52:37',5.3700),(2,'2023-12-18 06:02:37',4.5900),(2,'2023-12-18 06:12:37',4.2400),(2,'2023-12-18 06:22:37',4.8500),(2,'2023-12-18 06:32:37',5.5200),(2,'2023-12-18 06:42:37',5.1600),(2,'2023-12-18 06:52:37',5.6400),(2,'2023-12-18 07:02:37',4.8700),(2,'2023-12-18 07:12:37',5.8600),(2,'2023-12-18 07:22:37',5.0300),(2,'2023-12-18 07:32:37',4.2600),(2,'2023-12-18 07:42:37',4.3700),(2,'2023-12-18 07:52:37',5.2400),(2,'2023-12-18 08:02:37',5.4500),(2,'2023-12-18 08:12:37',5.6300),(2,'2023-12-18 08:22:37',6.4600),(2,'2023-12-18 08:32:37',6.0100),(2,'2023-12-18 08:42:37',5.4300),(2,'2023-12-18 08:52:37',5.7300),(2,'2023-12-18 09:02:37',6.5700),(2,'2023-12-18 09:12:37',5.7900),(2,'2023-12-18 09:22:37',6.5800),(2,'2023-12-18 09:32:37',6.8100),(2,'2023-12-18 09:42:37',6.8100),(2,'2023-12-18 10:02:37',7.4600),(2,'2023-12-18 10:12:37',8.4100),(2,'2023-12-18 10:22:37',8.0100),(2,'2023-12-18 10:32:37',7.3400),(2,'2023-12-18 10:42:37',7.4600),(2,'2023-12-18 10:52:37',8.3600),(2,'2023-12-18 11:02:37',8.0700),(2,'2023-12-18 11:12:37',7.5600),(2,'2023-12-18 11:22:37',7.1100),(2,'2023-12-18 11:32:37',6.1300),(2,'2023-12-18 11:42:37',6.5600),(2,'2023-12-18 11:52:37',5.7200),(2,'2023-12-18 12:02:37',5.3900),(2,'2023-12-18 12:12:37',5.7900),(2,'2023-12-18 12:22:37',6.1400),(2,'2023-12-18 12:32:37',7.0100),(2,'2023-12-18 12:42:37',7.7600),(2,'2023-12-18 12:52:37',7.4400),(2,'2023-12-18 13:02:37',6.6800),(2,'2023-12-18 13:12:37',6.2000),(2,'2023-12-18 13:22:37',5.4800),(2,'2023-12-18 13:32:37',5.8800),(2,'2023-12-18 13:42:37',5.3300),(2,'2023-12-18 13:52:37',5.3600),(2,'2023-12-18 14:02:37',4.6500),(2,'2023-12-18 14:22:37',3.5700),(2,'2023-12-18 14:32:37',3.4000),(2,'2023-12-18 14:42:37',3.1200),(2,'2023-12-18 14:52:37',3.8400),(2,'2023-12-18 15:02:37',3.1300),(2,'2023-12-18 15:12:37',2.5200),(2,'2023-12-18 15:22:37',3.1700),(2,'2023-12-18 15:32:37',2.4400),(2,'2023-12-18 15:42:37',1.5600),(2,'2023-12-18 15:52:37',1.3100),(2,'2023-12-18 16:02:37',0.9000),(2,'2023-12-18 16:12:37',0.0000),(2,'2023-12-18 16:22:37',-0.7000),(2,'2023-12-18 16:32:37',-0.5000),(2,'2023-12-18 16:42:37',-0.3800),(2,'2023-12-18 16:52:37',0.0500),(2,'2023-12-18 17:02:37',-0.2600),(2,'2023-12-18 17:12:37',-0.8800),(2,'2023-12-18 17:22:37',0.0300),(2,'2023-12-18 17:32:37',0.4500),(2,'2023-12-18 17:42:37',-0.3800),(2,'2023-12-18 18:02:37',0.0000),(2,'2023-12-18 18:12:37',-0.9400),(2,'2023-12-18 18:42:37',0.1000),(2,'2023-12-18 18:52:37',-0.3900),(2,'2023-12-18 19:02:37',-1.2600),(2,'2023-12-18 19:12:37',-0.4000),(2,'2023-12-18 19:22:37',-0.6300),(2,'2023-12-18 19:42:37',-0.2900),(2,'2023-12-18 19:52:37',-0.0500),(2,'2023-12-18 20:02:37',-1.0200),(2,'2023-12-18 20:12:37',-0.4400),(2,'2023-12-18 20:22:37',-0.9100),(2,'2023-12-18 20:32:37',-1.8100),(2,'2023-12-18 20:42:37',-1.1600),(2,'2023-12-18 20:52:37',-0.4500),(2,'2023-12-18 21:02:37',0.3500),(2,'2023-12-18 21:12:37',1.2300),(2,'2023-12-18 21:22:37',1.1500),(2,'2023-12-18 21:32:37',0.8800),(2,'2023-12-18 21:42:37',0.6800),(2,'2023-12-18 21:52:37',1.4600),(2,'2023-12-18 22:02:37',0.4800),(2,'2023-12-18 22:12:37',1.0200),(2,'2023-12-18 22:22:37',0.6700),(2,'2023-12-18 22:32:37',0.2200),(2,'2023-12-18 22:42:37',-0.6100),(2,'2023-12-18 23:02:37',-0.2200),(2,'2023-12-18 23:12:37',0.3400),(2,'2023-12-18 23:22:37',-0.1700),(2,'2023-12-18 23:32:37',-0.8400),(2,'2023-12-18 23:42:37',-0.1600),(2,'2023-12-18 23:52:37',-1.0200),(2,'2023-12-19 00:12:37',0.0800),(2,'2023-12-19 00:22:37',0.5500),(2,'2023-12-19 00:32:37',-0.3700),(2,'2023-12-19 00:42:37',0.4300),(2,'2023-12-19 00:52:37',1.3600),(2,'2023-12-19 01:02:37',1.5900),(2,'2023-12-19 01:12:37',0.8200),(2,'2023-12-19 01:22:37',1.0700),(2,'2023-12-19 01:32:37',1.9500),(2,'2023-12-19 01:42:37',2.3000),(2,'2023-12-19 01:52:37',2.2300),(2,'2023-12-19 02:02:37',1.6500),(2,'2023-12-19 02:12:37',2.3000),(2,'2023-12-19 02:22:37',2.7000),(2,'2023-12-19 02:32:37',3.5600),(2,'2023-12-19 02:42:37',4.2700),(2,'2023-12-19 02:52:37',5.2600),(2,'2023-12-19 03:02:37',4.9100),(2,'2023-12-19 03:12:37',4.0900),(2,'2023-12-19 03:22:37',4.9400),(2,'2023-12-19 03:32:37',5.4800),(2,'2023-12-19 03:42:37',6.1100),(2,'2023-12-19 03:52:37',6.3800),(2,'2023-12-19 04:02:37',5.8300),(2,'2023-12-19 04:12:37',5.1400),(2,'2023-12-19 04:22:37',6.1200),(2,'2023-12-19 04:32:37',5.6100),(2,'2023-12-19 04:42:37',6.4000),(2,'2023-12-19 04:52:37',6.7300),(2,'2023-12-19 05:02:37',5.8200),(2,'2023-12-19 05:22:37',4.9400),(2,'2023-12-19 05:32:37',5.2700),(2,'2023-12-19 05:42:37',4.2700),(2,'2023-12-19 05:52:37',3.4700),(2,'2023-12-19 06:02:37',4.1800),(2,'2023-12-19 06:12:37',3.7300),(2,'2023-12-19 06:22:37',4.3000),(2,'2023-12-19 06:32:37',4.9700),(2,'2023-12-19 06:42:37',4.6100),(2,'2023-12-19 06:52:37',3.7900),(2,'2023-12-19 07:02:37',4.5200),(2,'2023-12-19 07:12:37',5.5100),(2,'2023-12-19 07:22:37',5.6900),(2,'2023-12-19 07:32:37',6.2700),(2,'2023-12-19 07:42:37',5.3200),(2,'2023-12-19 07:52:37',4.8000),(2,'2023-12-19 08:02:37',5.0500),(2,'2023-12-19 08:12:37',4.9500),(2,'2023-12-19 08:22:37',4.6500),(2,'2023-12-19 08:32:37',5.0000),(2,'2023-12-19 08:42:37',4.0600);
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
INSERT INTO `supla_temphumidity_log` VALUES (60,'2023-12-16 08:52:37',10.8200,49.7200),(60,'2023-12-16 09:02:37',10.6300,49.6300),(60,'2023-12-16 09:12:37',11.1000,50.0600),(60,'2023-12-16 09:22:37',11.7500,50.8400),(60,'2023-12-16 09:32:37',11.3000,51.6200),(60,'2023-12-16 09:42:37',10.3400,51.3300),(60,'2023-12-16 09:52:37',10.7100,50.5000),(60,'2023-12-16 10:02:37',10.8300,49.5600),(60,'2023-12-16 10:12:37',10.5300,49.1700),(60,'2023-12-16 10:22:37',9.9900,49.8500),(60,'2023-12-16 10:32:37',9.1800,49.1500),(60,'2023-12-16 10:42:37',8.3700,49.5300),(60,'2023-12-16 10:52:37',8.8100,50.4500),(60,'2023-12-16 11:02:37',9.7400,50.3500),(60,'2023-12-16 11:12:37',9.2300,50.1900),(60,'2023-12-16 11:22:37',8.2800,50.8500),(60,'2023-12-16 11:32:37',8.1900,51.4300),(60,'2023-12-16 11:42:37',7.4800,50.8800),(60,'2023-12-16 11:52:37',8.0300,51.4300),(60,'2023-12-16 12:02:37',7.6200,52.0300),(60,'2023-12-16 12:12:37',7.0500,51.1000),(60,'2023-12-16 12:22:37',7.6400,50.6400),(60,'2023-12-16 12:32:37',7.4200,49.8200),(60,'2023-12-16 12:42:37',6.5200,49.5200),(60,'2023-12-16 12:52:37',6.7700,48.5900),(60,'2023-12-16 13:02:37',6.0000,49.5300),(60,'2023-12-16 13:22:37',4.2700,48.7000),(60,'2023-12-16 13:32:37',3.3600,49.2200),(60,'2023-12-16 13:42:37',2.5800,48.2600),(60,'2023-12-16 13:52:37',1.6100,47.8000),(60,'2023-12-16 14:02:37',2.0400,46.9900),(60,'2023-12-16 14:12:37',1.2500,46.2600),(60,'2023-12-16 14:22:37',1.9300,46.8700),(60,'2023-12-16 14:32:37',2.0800,46.5100),(60,'2023-12-16 14:42:37',1.1600,45.6700),(60,'2023-12-16 14:52:37',1.3500,45.4500),(60,'2023-12-16 15:02:37',1.7200,44.8200),(60,'2023-12-16 15:12:37',2.3000,44.0400),(60,'2023-12-16 15:22:37',1.8600,43.3400),(60,'2023-12-16 15:32:37',1.0900,43.7300),(60,'2023-12-16 15:42:37',0.2900,42.7500),(60,'2023-12-16 15:52:37',-0.6900,41.9000),(60,'2023-12-16 16:02:37',-1.3700,41.2100),(60,'2023-12-16 16:12:37',-1.0400,41.8300),(60,'2023-12-16 16:22:37',-0.1100,41.8200),(60,'2023-12-16 16:32:37',0.6600,41.4600),(60,'2023-12-16 16:52:37',1.1900,42.1000),(60,'2023-12-16 17:02:37',1.4500,42.2500),(60,'2023-12-16 17:12:37',2.2700,41.9300),(60,'2023-12-16 17:22:37',2.0500,40.9500),(60,'2023-12-16 17:32:37',1.3600,40.1000),(60,'2023-12-16 17:42:37',2.2700,40.3400),(60,'2023-12-16 17:52:37',1.3900,40.6200),(60,'2023-12-16 18:02:37',1.9900,40.5000),(60,'2023-12-16 18:12:37',1.6700,41.3100),(60,'2023-12-16 18:22:37',2.2300,41.3700),(60,'2023-12-16 18:32:37',1.9500,42.0000),(60,'2023-12-16 18:42:37',2.7800,42.2100),(60,'2023-12-16 18:52:37',3.5900,41.3500),(60,'2023-12-16 19:02:37',3.1100,40.7400),(60,'2023-12-16 19:12:37',4.0400,40.2000),(60,'2023-12-16 19:22:37',4.6400,40.9300),(60,'2023-12-16 19:32:37',3.7700,41.8600),(60,'2023-12-16 19:42:37',4.1700,40.9500),(60,'2023-12-16 19:52:37',3.2900,40.2600),(60,'2023-12-16 20:02:37',2.4300,39.4800),(60,'2023-12-16 20:12:37',3.1500,38.8400),(60,'2023-12-16 20:22:37',3.8200,38.7100),(60,'2023-12-16 20:32:37',2.9100,39.4700),(60,'2023-12-16 20:42:37',2.0900,40.3200),(60,'2023-12-16 20:52:37',2.6600,41.1300),(60,'2023-12-16 21:02:37',3.2400,40.2800),(60,'2023-12-16 21:12:37',4.1000,40.0800),(60,'2023-12-16 21:22:37',3.7200,39.8500),(60,'2023-12-16 21:32:37',4.6300,39.5700),(60,'2023-12-16 21:42:37',5.0300,38.5800),(60,'2023-12-16 21:52:37',4.5000,39.3100),(60,'2023-12-16 22:12:37',3.3700,39.9200),(60,'2023-12-16 22:22:37',3.6200,40.8900),(60,'2023-12-16 22:32:37',4.5900,40.1100),(60,'2023-12-16 22:42:37',4.4600,41.0100),(60,'2023-12-16 22:52:37',5.3900,40.5300),(60,'2023-12-16 23:02:37',6.2300,40.0500),(60,'2023-12-16 23:12:37',7.1500,40.7500),(60,'2023-12-16 23:22:37',7.5200,40.5900),(60,'2023-12-16 23:32:37',6.9600,39.8400),(60,'2023-12-16 23:42:37',6.1300,39.0200),(60,'2023-12-16 23:52:37',5.4900,38.2700),(60,'2023-12-17 00:02:37',5.6700,37.4900),(60,'2023-12-17 00:12:37',5.5400,36.7100),(60,'2023-12-17 00:22:37',5.3000,35.8800),(60,'2023-12-17 00:32:37',5.5000,35.7800),(60,'2023-12-17 00:42:37',6.1500,36.6800),(60,'2023-12-17 01:02:37',5.0500,38.0700),(60,'2023-12-17 01:12:37',5.5300,38.9400),(60,'2023-12-17 01:22:37',4.8100,39.7900),(60,'2023-12-17 01:32:37',3.8800,39.0600),(60,'2023-12-17 01:42:37',3.4500,38.2400),(60,'2023-12-17 01:52:37',2.6300,37.3100),(60,'2023-12-17 02:02:37',1.6700,38.3000),(60,'2023-12-17 02:12:37',2.3500,39.2900),(60,'2023-12-17 02:22:37',1.4300,38.7900),(60,'2023-12-17 02:32:37',2.1200,38.0300),(60,'2023-12-17 02:42:37',1.1300,38.6100),(60,'2023-12-17 02:52:37',1.7100,39.5400),(60,'2023-12-17 03:12:37',0.3000,39.1600),(60,'2023-12-17 03:22:37',0.0100,40.0600),(60,'2023-12-17 03:32:37',0.6300,39.5000),(60,'2023-12-17 03:42:37',-0.1400,38.9700),(60,'2023-12-17 03:52:37',-0.2900,38.0900),(60,'2023-12-17 04:02:37',0.4100,38.1700),(60,'2023-12-17 04:12:37',-0.4500,37.3500),(60,'2023-12-17 04:22:37',-0.3900,37.0000),(60,'2023-12-17 04:32:37',0.5900,36.7200),(60,'2023-12-17 04:42:37',0.4400,35.9400),(60,'2023-12-17 04:52:37',-0.1000,36.8800),(60,'2023-12-17 05:02:37',0.2400,37.6400),(60,'2023-12-17 05:12:37',0.3000,36.6700),(60,'2023-12-17 05:22:37',0.9500,35.7700),(60,'2023-12-17 05:32:37',1.8500,36.6300),(60,'2023-12-17 05:42:37',1.0900,36.0100),(60,'2023-12-17 05:52:37',0.2300,36.5100),(60,'2023-12-17 06:02:37',1.0400,35.6300),(60,'2023-12-17 06:12:37',0.1700,35.7300),(60,'2023-12-17 06:22:37',-0.6600,36.2400),(60,'2023-12-17 06:32:37',-1.1500,35.5200),(60,'2023-12-17 06:42:37',-1.9300,36.1900),(60,'2023-12-17 06:52:37',-1.3700,36.7800),(60,'2023-12-17 07:02:37',-2.2900,36.0500),(60,'2023-12-17 07:12:37',-2.5100,35.2700),(60,'2023-12-17 07:22:37',-2.0300,34.3400),(60,'2023-12-17 07:32:37',-1.6700,34.3200),(60,'2023-12-17 07:42:37',-1.3600,34.8000),(60,'2023-12-17 07:52:37',-0.8400,35.5800),(60,'2023-12-17 08:02:37',-0.4600,35.7800),(60,'2023-12-17 08:12:37',-1.1000,34.7800),(60,'2023-12-17 08:22:37',-1.8600,34.1300),(60,'2023-12-17 08:32:37',-2.3500,33.2700),(60,'2023-12-17 08:42:37',-2.5100,33.9800),(60,'2023-12-17 08:52:37',-2.1700,33.3500),(60,'2023-12-17 09:02:37',-1.5400,34.2800),(60,'2023-12-17 09:12:37',-0.9800,34.0900),(60,'2023-12-17 09:22:37',-0.0900,33.1700),(60,'2023-12-17 09:32:37',-0.0400,34.0200),(60,'2023-12-17 09:42:37',0.7800,34.5100),(60,'2023-12-17 09:52:37',1.3500,35.4300),(60,'2023-12-17 10:02:37',1.6700,36.1800),(60,'2023-12-17 10:12:37',0.9000,37.0500),(60,'2023-12-17 10:22:37',0.1900,37.7300),(60,'2023-12-17 10:42:37',-0.0700,36.9400),(60,'2023-12-17 10:52:37',-0.9800,37.2100),(60,'2023-12-17 11:02:37',-0.8200,37.6800),(60,'2023-12-17 11:12:37',-0.3500,38.5800),(60,'2023-12-17 11:32:37',0.2900,37.0900),(60,'2023-12-17 11:42:37',-0.2100,36.2000),(60,'2023-12-17 11:52:37',-0.8100,37.0700),(60,'2023-12-17 12:02:37',-1.1600,36.1900),(60,'2023-12-17 12:22:37',0.5600,37.2200),(60,'2023-12-17 12:32:37',-0.3800,36.5900),(60,'2023-12-17 12:42:37',-1.1000,37.1300),(60,'2023-12-17 12:52:37',-0.6900,37.9000),(60,'2023-12-17 13:02:37',-0.0700,37.4400),(60,'2023-12-17 13:12:37',0.9000,37.2200),(60,'2023-12-17 13:22:37',1.5100,37.7200),(60,'2023-12-17 13:32:37',2.0000,37.0100),(60,'2023-12-17 13:42:37',1.2800,37.4600),(60,'2023-12-17 13:52:37',1.8000,36.8800),(60,'2023-12-17 14:02:37',2.1600,36.4500),(60,'2023-12-17 14:12:37',1.2600,37.2800),(60,'2023-12-17 14:22:37',1.5200,36.9000),(60,'2023-12-17 14:32:37',0.9900,36.2400),(60,'2023-12-17 14:42:37',0.6500,36.7300),(60,'2023-12-17 14:52:37',0.7900,37.6600),(60,'2023-12-17 15:02:37',0.1500,38.0600),(60,'2023-12-17 15:12:37',0.1500,38.2600),(60,'2023-12-17 15:22:37',0.3800,39.0000),(60,'2023-12-17 15:32:37',0.7100,38.1400),(60,'2023-12-17 15:42:37',1.4500,38.6000),(60,'2023-12-17 15:52:37',0.5800,37.6900),(60,'2023-12-17 16:02:37',-0.2100,37.9900),(60,'2023-12-17 16:12:37',-0.0900,37.0900),(60,'2023-12-17 16:22:37',-1.0000,36.5000),(60,'2023-12-17 16:32:37',-0.4100,37.0700),(60,'2023-12-17 16:42:37',0.5200,37.7000),(60,'2023-12-17 16:52:37',0.4600,37.8600),(60,'2023-12-17 17:02:37',0.8700,37.0500),(60,'2023-12-17 17:12:37',0.0200,37.8600),(60,'2023-12-17 17:22:37',0.7400,37.8500),(60,'2023-12-17 17:42:37',2.5300,36.3900),(60,'2023-12-17 17:52:37',1.7400,37.3700),(60,'2023-12-17 18:02:37',1.1900,37.2100),(60,'2023-12-17 18:12:37',0.2000,37.6600),(60,'2023-12-17 18:22:37',-0.6700,37.1000),(60,'2023-12-17 18:32:37',0.2100,37.9400),(60,'2023-12-17 18:42:37',0.8600,37.0600),(60,'2023-12-17 18:52:37',1.4600,37.9200),(60,'2023-12-17 19:22:37',2.8200,38.3500),(60,'2023-12-17 19:32:37',2.0800,39.1900),(60,'2023-12-17 19:42:37',1.4600,38.6700),(60,'2023-12-17 19:52:37',1.3000,38.0200),(60,'2023-12-17 20:02:37',0.4600,38.4500),(60,'2023-12-17 20:12:37',-0.3100,37.7400),(60,'2023-12-17 20:22:37',-0.8500,37.9700),(60,'2023-12-17 20:32:37',-0.3500,37.2200),(60,'2023-12-17 20:42:37',-0.7300,36.8100),(60,'2023-12-17 21:02:37',-1.0400,37.4800),(60,'2023-12-17 21:12:37',-1.4500,36.7200),(60,'2023-12-17 21:22:37',-2.1100,37.4200),(60,'2023-12-17 21:32:37',-1.2000,36.9500),(60,'2023-12-17 21:42:37',-0.3900,37.8700),(60,'2023-12-17 21:52:37',-0.0600,38.3900),(60,'2023-12-17 22:02:37',0.0600,39.3700),(60,'2023-12-17 22:12:37',-0.5300,39.9400),(60,'2023-12-17 22:22:37',0.4700,39.5000),(60,'2023-12-17 22:32:37',-0.0100,40.4000),(60,'2023-12-17 22:42:37',-0.7600,40.0900),(60,'2023-12-17 22:52:37',-1.3600,39.2500),(60,'2023-12-17 23:02:37',-0.9500,38.7400),(60,'2023-12-17 23:12:37',-0.2400,38.1200),(60,'2023-12-17 23:22:37',-0.7200,37.8600),(60,'2023-12-17 23:32:37',0.0800,36.9500),(60,'2023-12-17 23:42:37',-0.0300,35.9700),(60,'2023-12-17 23:52:37',-0.7200,35.7900),(60,'2023-12-18 00:02:37',0.2200,36.5400),(60,'2023-12-18 00:12:37',0.5400,36.4800),(60,'2023-12-18 00:22:37',1.2900,37.3000),(60,'2023-12-18 00:32:37',0.5500,38.2500),(60,'2023-12-18 00:42:37',-0.3300,37.9100),(60,'2023-12-18 00:52:37',0.6200,37.3200),(60,'2023-12-18 01:02:37',0.2000,36.4800),(60,'2023-12-18 01:12:37',0.8200,37.2500),(60,'2023-12-18 01:22:37',1.7600,36.7900),(60,'2023-12-18 01:32:37',0.8100,36.9400),(60,'2023-12-18 01:42:37',1.1700,35.9600),(60,'2023-12-18 01:52:37',0.2700,36.2800),(60,'2023-12-18 02:02:37',0.6500,36.6500),(60,'2023-12-18 02:12:37',0.5100,37.1300),(60,'2023-12-18 02:32:37',2.4700,37.4500),(60,'2023-12-18 02:42:37',2.1200,36.6600),(60,'2023-12-18 02:52:37',3.0900,36.1100),(60,'2023-12-18 03:02:37',2.1700,36.2000),(60,'2023-12-18 03:22:37',1.9400,37.5400),(60,'2023-12-18 03:32:37',1.5200,37.4400),(60,'2023-12-18 03:42:37',0.6900,36.5900),(60,'2023-12-18 03:52:37',0.2000,36.0200),(60,'2023-12-18 04:02:37',0.8300,35.6000),(60,'2023-12-18 04:12:37',0.5300,35.4300),(60,'2023-12-18 04:22:37',0.8100,34.9900),(60,'2023-12-18 04:32:37',1.1300,34.0600),(60,'2023-12-18 04:52:37',0.2100,34.5400),(60,'2023-12-18 05:02:37',0.9600,34.2100),(60,'2023-12-18 05:12:37',0.8200,34.3900),(60,'2023-12-18 05:22:37',1.5800,35.0100),(60,'2023-12-18 05:32:37',2.5400,34.5800),(60,'2023-12-18 05:42:37',3.2400,35.1900),(60,'2023-12-18 05:52:37',2.6000,34.8100),(60,'2023-12-18 06:02:37',3.0800,35.7300),(60,'2023-12-18 06:12:37',4.0800,36.3900),(60,'2023-12-18 06:22:37',4.3300,36.5100),(60,'2023-12-18 06:32:37',3.3900,36.0800),(60,'2023-12-18 06:52:37',2.9100,36.9800),(60,'2023-12-18 07:02:37',3.3300,36.0000),(60,'2023-12-18 07:12:37',2.6500,36.6300),(60,'2023-12-18 07:22:37',3.0300,35.6600),(60,'2023-12-18 07:32:37',3.8100,36.0900),(60,'2023-12-18 07:42:37',2.8300,36.8500),(60,'2023-12-18 07:52:37',3.4800,37.5800),(60,'2023-12-18 08:02:37',4.3200,37.3900),(60,'2023-12-18 08:12:37',3.4600,38.0400),(60,'2023-12-18 08:22:37',4.1700,37.2800),(60,'2023-12-18 08:32:37',3.5800,38.1400),(60,'2023-12-18 08:42:37',3.3000,38.7400),(60,'2023-12-18 08:52:37',2.5400,38.3000),(60,'2023-12-18 09:02:37',2.1200,37.3800),(60,'2023-12-18 09:12:37',1.3700,38.3500),(60,'2023-12-18 09:22:37',0.6400,37.5200),(60,'2023-12-18 09:32:37',0.2600,36.6200),(60,'2023-12-18 09:42:37',-0.4200,37.6100),(60,'2023-12-18 09:52:37',-1.1600,38.5200),(60,'2023-12-18 10:02:37',-0.8200,38.9500),(60,'2023-12-18 10:12:37',-0.0100,38.5000),(60,'2023-12-18 10:22:37',-0.6700,37.6600),(60,'2023-12-18 10:32:37',-0.2100,37.9800),(60,'2023-12-18 10:42:37',0.7600,38.9200),(60,'2023-12-18 10:52:37',0.9000,39.8900),(60,'2023-12-18 11:02:37',1.8600,40.8500),(60,'2023-12-18 11:12:37',1.6100,40.3900),(60,'2023-12-18 11:22:37',2.5800,41.0100),(60,'2023-12-18 11:32:37',3.2700,40.9100),(60,'2023-12-18 11:42:37',4.2000,41.1100),(60,'2023-12-18 11:52:37',3.8900,41.4900),(60,'2023-12-18 12:12:37',2.8200,41.2800),(60,'2023-12-18 12:22:37',1.8200,40.8400),(60,'2023-12-18 12:32:37',1.9100,40.6800),(60,'2023-12-18 12:42:37',2.1600,41.2700),(60,'2023-12-18 12:52:37',2.8700,41.1200),(60,'2023-12-18 13:02:37',2.0000,40.5300),(60,'2023-12-18 13:12:37',2.7800,39.8900),(60,'2023-12-18 13:22:37',2.6300,40.8300),(60,'2023-12-18 13:32:37',3.1400,41.1000),(60,'2023-12-18 13:42:37',2.3500,40.3800),(60,'2023-12-18 13:52:37',3.1600,39.7600),(60,'2023-12-18 14:02:37',2.4200,38.8400),(60,'2023-12-18 14:12:37',1.7800,39.4500),(60,'2023-12-18 14:22:37',0.8800,39.5400),(60,'2023-12-18 14:32:37',1.1700,38.7200),(60,'2023-12-18 14:42:37',0.3800,39.1500),(60,'2023-12-18 14:52:37',1.3200,38.4000),(60,'2023-12-18 15:02:37',1.8600,37.6400),(60,'2023-12-18 15:22:37',3.4300,36.5900),(60,'2023-12-18 15:32:37',2.6200,35.8700),(60,'2023-12-18 15:42:37',3.4800,35.2300),(60,'2023-12-18 16:02:37',5.1500,34.9900),(60,'2023-12-18 16:12:37',4.4400,34.7800),(60,'2023-12-18 16:22:37',4.0200,33.8200),(60,'2023-12-18 16:32:37',3.0500,33.5400),(60,'2023-12-18 16:42:37',2.8200,34.5200),(60,'2023-12-18 16:52:37',1.9100,34.9200),(60,'2023-12-18 17:02:37',2.4700,34.2500),(60,'2023-12-18 17:12:37',2.9700,35.1900),(60,'2023-12-18 17:22:37',3.6700,34.9300),(60,'2023-12-18 17:32:37',4.3200,34.6800),(60,'2023-12-18 17:42:37',5.1500,33.7900),(60,'2023-12-18 17:52:37',5.2900,32.8200),(60,'2023-12-18 18:02:37',4.9400,32.1500),(60,'2023-12-18 18:12:37',4.9500,33.1500),(60,'2023-12-18 18:22:37',4.9300,33.7400),(60,'2023-12-18 18:32:37',5.5000,32.8200),(60,'2023-12-18 18:42:37',6.0400,32.1500),(60,'2023-12-18 18:52:37',5.8200,32.0100),(60,'2023-12-18 19:02:37',5.3100,32.3400),(60,'2023-12-18 19:12:37',4.6500,33.2900),(60,'2023-12-18 19:22:37',5.5100,33.9800),(60,'2023-12-18 19:32:37',5.6600,33.1400),(60,'2023-12-18 19:42:37',5.8100,32.4400),(60,'2023-12-18 19:52:37',6.2100,31.5700),(60,'2023-12-18 20:02:37',6.4300,30.6300),(60,'2023-12-18 20:12:37',5.6100,31.5000),(60,'2023-12-18 20:22:37',6.2900,31.0900),(60,'2023-12-18 20:32:37',5.8200,31.7700),(60,'2023-12-18 20:42:37',6.4500,30.9400),(60,'2023-12-18 20:52:37',5.7400,30.4200),(60,'2023-12-18 21:02:37',4.7700,29.5000),(60,'2023-12-18 21:12:37',3.8600,30.2900),(60,'2023-12-18 21:22:37',4.6100,29.9000),(60,'2023-12-18 21:32:37',5.2300,30.8000),(60,'2023-12-18 21:42:37',5.5000,31.6200),(60,'2023-12-18 21:52:37',4.9400,31.5500),(60,'2023-12-18 22:02:37',4.3200,32.1000),(60,'2023-12-18 22:12:37',4.8200,31.3200),(60,'2023-12-18 22:22:37',3.9300,30.6500),(60,'2023-12-18 22:32:37',4.7300,31.5300),(60,'2023-12-18 22:42:37',3.9000,32.3300),(60,'2023-12-18 22:52:37',3.2100,32.0000),(60,'2023-12-18 23:12:37',2.5800,30.3300),(60,'2023-12-18 23:22:37',2.9900,29.6000),(60,'2023-12-18 23:32:37',3.5200,30.2700),(60,'2023-12-18 23:42:37',4.3300,30.5900),(60,'2023-12-18 23:52:37',3.4900,30.2100),(60,'2023-12-19 00:02:37',2.6400,29.9900),(60,'2023-12-19 00:12:37',2.5400,30.5300),(60,'2023-12-19 00:22:37',3.4500,30.2700),(60,'2023-12-19 00:32:37',3.6200,29.3600),(60,'2023-12-19 00:42:37',3.2600,28.6000),(60,'2023-12-19 00:52:37',2.7800,29.5100),(60,'2023-12-19 01:02:37',3.1000,28.5600),(60,'2023-12-19 01:12:37',2.4100,27.8800),(60,'2023-12-19 01:22:37',3.0900,28.1300),(60,'2023-12-19 01:32:37',3.4600,27.4300),(60,'2023-12-19 01:42:37',4.2200,28.1900),(60,'2023-12-19 01:52:37',3.8600,27.2500),(60,'2023-12-19 02:02:37',3.3300,28.2400),(60,'2023-12-19 02:12:37',4.2700,28.0100),(60,'2023-12-19 02:22:37',4.7700,27.1700),(60,'2023-12-19 02:32:37',4.1200,26.7200),(60,'2023-12-19 02:42:37',3.3900,26.5500),(60,'2023-12-19 02:52:37',3.0500,27.0400),(60,'2023-12-19 03:02:37',3.5300,27.7800),(60,'2023-12-19 03:12:37',2.9400,28.6100),(60,'2023-12-19 03:22:37',2.0000,29.2900),(60,'2023-12-19 03:32:37',1.1400,29.6800),(60,'2023-12-19 03:42:37',1.9300,28.7500),(60,'2023-12-19 03:52:37',1.0400,29.7400),(60,'2023-12-19 04:12:37',1.8500,27.8600),(60,'2023-12-19 04:22:37',1.0700,28.5700),(60,'2023-12-19 04:32:37',1.4100,29.4100),(60,'2023-12-19 04:42:37',1.7600,29.8400),(60,'2023-12-19 04:52:37',1.0400,29.2500),(60,'2023-12-19 05:02:37',0.8100,29.5700),(60,'2023-12-19 05:12:37',0.6500,28.9900),(60,'2023-12-19 05:22:37',0.1800,27.9900),(60,'2023-12-19 05:32:37',1.1400,27.3400),(60,'2023-12-19 05:42:37',1.5000,26.6300),(60,'2023-12-19 05:52:37',1.1500,27.2600),(60,'2023-12-19 06:02:37',0.4500,26.9000),(60,'2023-12-19 06:12:37',1.2400,27.7100),(60,'2023-12-19 06:22:37',1.4700,28.5300),(60,'2023-12-19 06:32:37',2.4500,27.8000),(60,'2023-12-19 06:42:37',3.1800,27.1700),(60,'2023-12-19 07:02:37',2.4700,27.4500),(60,'2023-12-19 07:12:37',1.9500,26.5200),(60,'2023-12-19 07:22:37',2.2900,27.1200),(60,'2023-12-19 07:32:37',2.6900,26.8300),(60,'2023-12-19 07:42:37',3.4700,26.3000),(60,'2023-12-19 07:52:37',2.8800,25.3100),(60,'2023-12-19 08:02:37',1.9700,24.6800),(60,'2023-12-19 08:12:37',1.0000,25.3400),(60,'2023-12-19 08:22:37',1.9300,25.7200),(60,'2023-12-19 08:42:37',3.7700,24.8400),(66,'2023-12-16 08:52:37',0.0000,10.5700),(66,'2023-12-16 09:02:37',0.0000,11.3800),(66,'2023-12-16 09:12:37',0.0000,12.3500),(66,'2023-12-16 09:22:37',0.0000,13.3400),(66,'2023-12-16 09:32:37',0.0000,13.1500),(66,'2023-12-16 09:42:37',0.0000,13.5100),(66,'2023-12-16 09:52:37',0.0000,13.9400),(66,'2023-12-16 10:02:37',0.0000,14.3300),(66,'2023-12-16 10:12:37',0.0000,13.9100),(66,'2023-12-16 10:22:37',0.0000,14.3500),(66,'2023-12-16 10:32:37',0.0000,13.5800),(66,'2023-12-16 10:42:37',0.0000,14.3700),(66,'2023-12-16 10:52:37',0.0000,14.7500),(66,'2023-12-16 11:02:37',0.0000,14.0000),(66,'2023-12-16 11:12:37',0.0000,13.2700),(66,'2023-12-16 11:22:37',0.0000,14.1300),(66,'2023-12-16 11:32:37',0.0000,13.4200),(66,'2023-12-16 11:52:37',0.0000,13.0900),(66,'2023-12-16 12:02:37',0.0000,12.8800),(66,'2023-12-16 12:12:37',0.0000,12.3000),(66,'2023-12-16 12:22:37',0.0000,13.1300),(66,'2023-12-16 12:32:37',0.0000,13.6900),(66,'2023-12-16 12:42:37',0.0000,14.2800),(66,'2023-12-16 12:52:37',0.0000,13.6700),(66,'2023-12-16 13:02:37',0.0000,13.7900),(66,'2023-12-16 13:12:37',0.0000,13.4500),(66,'2023-12-16 13:22:37',0.0000,12.6900),(66,'2023-12-16 13:32:37',0.0000,12.0300),(66,'2023-12-16 13:42:37',0.0000,12.2900),(66,'2023-12-16 13:52:37',0.0000,11.5900),(66,'2023-12-16 14:02:37',0.0000,11.2600),(66,'2023-12-16 14:12:37',0.0000,12.2100),(66,'2023-12-16 14:22:37',0.0000,12.6700),(66,'2023-12-16 14:32:37',0.0000,11.9200),(66,'2023-12-16 14:42:37',0.0000,11.5000),(66,'2023-12-16 14:52:37',0.0000,11.4300),(66,'2023-12-16 15:02:37',0.0000,10.8700),(66,'2023-12-16 15:12:37',0.0000,11.3500),(66,'2023-12-16 15:22:37',0.0000,10.3700),(66,'2023-12-16 15:32:37',0.0000,11.0900),(66,'2023-12-16 15:42:37',0.0000,12.0000),(66,'2023-12-16 15:52:37',0.0000,11.0900),(66,'2023-12-16 16:02:37',0.0000,10.2400),(66,'2023-12-16 16:12:37',0.0000,11.0100),(66,'2023-12-16 16:22:37',0.0000,10.3300),(66,'2023-12-16 16:32:37',0.0000,11.0800),(66,'2023-12-16 16:42:37',0.0000,10.7400),(66,'2023-12-16 16:52:37',0.0000,10.2600),(66,'2023-12-16 17:02:37',0.0000,9.6500),(66,'2023-12-16 17:12:37',0.0000,9.0600),(66,'2023-12-16 17:22:37',0.0000,9.8000),(66,'2023-12-16 17:32:37',0.0000,10.7500),(66,'2023-12-16 17:42:37',0.0000,10.4300),(66,'2023-12-16 17:52:37',0.0000,11.0100),(66,'2023-12-16 18:02:37',0.0000,11.9300),(66,'2023-12-16 18:12:37',0.0000,11.5900),(66,'2023-12-16 18:22:37',0.0000,12.5200),(66,'2023-12-16 18:32:37',0.0000,13.3500),(66,'2023-12-16 18:42:37',0.0000,14.1800),(66,'2023-12-16 18:52:37',0.0000,14.8900),(66,'2023-12-16 19:02:37',0.0000,15.4400),(66,'2023-12-16 19:12:37',0.0000,14.9200),(66,'2023-12-16 19:22:37',0.0000,15.1300),(66,'2023-12-16 19:32:37',0.0000,14.1600),(66,'2023-12-16 19:42:37',0.0000,15.1400),(66,'2023-12-16 19:52:37',0.0000,14.8400),(66,'2023-12-16 20:02:37',0.0000,15.5400),(66,'2023-12-16 20:12:37',0.0000,16.0500),(66,'2023-12-16 20:22:37',0.0000,15.1100),(66,'2023-12-16 20:32:37',0.0000,14.8600),(66,'2023-12-16 20:42:37',0.0000,15.7800),(66,'2023-12-16 20:52:37',0.0000,16.6000),(66,'2023-12-16 21:02:37',0.0000,17.0900),(66,'2023-12-16 21:12:37',0.0000,17.3400),(66,'2023-12-16 21:22:37',0.0000,16.5900),(66,'2023-12-16 21:32:37',0.0000,16.2900),(66,'2023-12-16 21:42:37',0.0000,17.1200),(66,'2023-12-16 21:52:37',0.0000,16.9400),(66,'2023-12-16 22:02:37',0.0000,16.1000),(66,'2023-12-16 22:12:37',0.0000,17.0500),(66,'2023-12-16 22:22:37',0.0000,16.9200),(66,'2023-12-16 22:32:37',0.0000,17.6900),(66,'2023-12-16 22:42:37',0.0000,16.9300),(66,'2023-12-16 22:52:37',0.0000,16.0400),(66,'2023-12-16 23:02:37',0.0000,15.3000),(66,'2023-12-16 23:12:37',0.0000,14.4900),(66,'2023-12-16 23:22:37',0.0000,14.3400),(66,'2023-12-16 23:32:37',0.0000,15.3200),(66,'2023-12-16 23:42:37',0.0000,15.8500),(66,'2023-12-16 23:52:37',0.0000,15.9800),(66,'2023-12-17 00:02:37',0.0000,15.5300),(66,'2023-12-17 00:12:37',0.0000,16.4200),(66,'2023-12-17 00:22:37',0.0000,16.8600),(66,'2023-12-17 00:32:37',0.0000,17.3200),(66,'2023-12-17 00:42:37',0.0000,18.2700),(66,'2023-12-17 00:52:37',0.0000,18.4900),(66,'2023-12-17 01:02:37',0.0000,17.7300),(66,'2023-12-17 01:12:37',0.0000,17.0400),(66,'2023-12-17 01:22:37',0.0000,16.8400),(66,'2023-12-17 01:32:37',0.0000,16.6700),(66,'2023-12-17 01:42:37',0.0000,17.1000),(66,'2023-12-17 01:52:37',0.0000,16.3300),(66,'2023-12-17 02:02:37',0.0000,17.2800),(66,'2023-12-17 02:12:37',0.0000,16.3700),(66,'2023-12-17 02:22:37',0.0000,17.3300),(66,'2023-12-17 02:32:37',0.0000,16.6000),(66,'2023-12-17 02:52:37',0.0000,16.2100),(66,'2023-12-17 03:02:37',0.0000,16.7200),(66,'2023-12-17 03:12:37',0.0000,17.6700),(66,'2023-12-17 03:22:37',0.0000,17.1300),(66,'2023-12-17 03:32:37',0.0000,17.6100),(66,'2023-12-17 03:42:37',0.0000,18.3200),(66,'2023-12-17 03:52:37',0.0000,18.1400),(66,'2023-12-17 04:02:37',0.0000,18.9600),(66,'2023-12-17 04:12:37',0.0000,18.2700),(66,'2023-12-17 04:22:37',0.0000,19.0200),(66,'2023-12-17 04:32:37',0.0000,18.0800),(66,'2023-12-17 04:42:37',0.0000,17.6000),(66,'2023-12-17 04:52:37',0.0000,18.0300),(66,'2023-12-17 05:02:37',0.0000,17.2500),(66,'2023-12-17 05:12:37',0.0000,17.6000),(66,'2023-12-17 05:22:37',0.0000,18.4400),(66,'2023-12-17 05:32:37',0.0000,18.3400),(66,'2023-12-17 05:42:37',0.0000,18.0400),(66,'2023-12-17 05:52:37',0.0000,17.3800),(66,'2023-12-17 06:02:37',0.0000,16.4900),(66,'2023-12-17 06:12:37',0.0000,16.2900),(66,'2023-12-17 06:22:37',0.0000,16.5200),(66,'2023-12-17 06:32:37',0.0000,15.8200),(66,'2023-12-17 06:42:37',0.0000,15.0900),(66,'2023-12-17 06:52:37',0.0000,14.4600),(66,'2023-12-17 07:02:37',0.0000,15.0800),(66,'2023-12-17 07:12:37',0.0000,14.1100),(66,'2023-12-17 07:32:37',0.0000,13.2500),(66,'2023-12-17 07:42:37',0.0000,13.8900),(66,'2023-12-17 07:52:37',0.0000,13.3900),(66,'2023-12-17 08:02:37',0.0000,12.4400),(66,'2023-12-17 08:12:37',0.0000,12.8800),(66,'2023-12-17 08:22:37',0.0000,12.4600),(66,'2023-12-17 08:32:37',0.0000,13.4400),(66,'2023-12-17 08:42:37',0.0000,13.9200),(66,'2023-12-17 08:52:37',0.0000,14.2700),(66,'2023-12-17 09:02:37',0.0000,13.7200),(66,'2023-12-17 09:12:37',0.0000,14.1000),(66,'2023-12-17 09:22:37',0.0000,13.6300),(66,'2023-12-17 09:32:37',0.0000,14.1400),(66,'2023-12-17 09:42:37',0.0000,13.1900),(66,'2023-12-17 09:52:37',0.0000,12.6200),(66,'2023-12-17 10:02:37',0.0000,12.9400),(66,'2023-12-17 10:22:37',0.0000,11.6200),(66,'2023-12-17 10:32:37',0.0000,11.3500),(66,'2023-12-17 10:42:37',0.0000,10.3800),(66,'2023-12-17 10:52:37',0.0000,10.5100),(66,'2023-12-17 11:02:37',0.0000,9.9100),(66,'2023-12-17 11:12:37',0.0000,9.1000),(66,'2023-12-17 11:22:37',0.0000,8.4700),(66,'2023-12-17 11:32:37',0.0000,8.0400),(66,'2023-12-17 11:42:37',0.0000,7.6600),(66,'2023-12-17 11:52:37',0.0000,8.2000),(66,'2023-12-17 12:02:37',0.0000,8.8600),(66,'2023-12-17 12:12:37',0.0000,8.2800),(66,'2023-12-17 12:42:37',0.0000,9.0300),(66,'2023-12-17 12:52:37',0.0000,8.5500),(66,'2023-12-17 13:02:37',0.0000,9.5500),(66,'2023-12-17 13:12:37',0.0000,10.1200),(66,'2023-12-17 13:22:37',0.0000,9.1600),(66,'2023-12-17 13:32:37',0.0000,8.9200),(66,'2023-12-17 13:42:37',0.0000,9.1100),(66,'2023-12-17 13:52:37',0.0000,9.7800),(66,'2023-12-17 14:02:37',0.0000,9.1000),(66,'2023-12-17 14:12:37',0.0000,9.9900),(66,'2023-12-17 14:22:37',0.0000,9.6800),(66,'2023-12-17 14:32:37',0.0000,10.3000),(66,'2023-12-17 14:42:37',0.0000,10.7600),(66,'2023-12-17 14:52:37',0.0000,10.6600),(66,'2023-12-17 15:02:37',0.0000,10.1600),(66,'2023-12-17 15:12:37',0.0000,10.4000),(66,'2023-12-17 15:32:37',0.0000,9.9700),(66,'2023-12-17 15:42:37',0.0000,9.6800),(66,'2023-12-17 15:52:37',0.0000,10.2900),(66,'2023-12-17 16:02:37',0.0000,9.9400),(66,'2023-12-17 16:12:37',0.0000,10.1000),(66,'2023-12-17 16:22:37',0.0000,9.7800),(66,'2023-12-17 16:32:37',0.0000,10.7100),(66,'2023-12-17 16:42:37',0.0000,10.3600),(66,'2023-12-17 16:52:37',0.0000,9.5500),(66,'2023-12-17 17:02:37',0.0000,9.0700),(66,'2023-12-17 17:12:37',0.0000,9.2200),(66,'2023-12-17 17:32:37',0.0000,9.2100),(66,'2023-12-17 17:42:37',0.0000,10.1000),(66,'2023-12-17 18:02:37',0.0000,10.2100),(66,'2023-12-17 18:12:37',0.0000,9.9800),(66,'2023-12-17 18:22:37',0.0000,10.6700),(66,'2023-12-17 18:32:37',0.0000,10.2500),(66,'2023-12-17 18:42:37',0.0000,9.4800),(66,'2023-12-17 18:52:37',0.0000,10.3000),(66,'2023-12-17 19:02:37',0.0000,11.2600),(66,'2023-12-17 19:12:37',0.0000,11.4100),(66,'2023-12-17 19:22:37',0.0000,12.0300),(66,'2023-12-17 19:32:37',0.0000,12.4100),(66,'2023-12-17 19:42:37',0.0000,12.7100),(66,'2023-12-17 19:52:37',0.0000,12.2300),(66,'2023-12-17 20:02:37',0.0000,12.5600),(66,'2023-12-17 20:12:37',0.0000,12.1900),(66,'2023-12-17 20:22:37',0.0000,12.6200),(66,'2023-12-17 20:32:37',0.0000,11.7400),(66,'2023-12-17 20:42:37',0.0000,11.2100),(66,'2023-12-17 21:02:37',0.0000,11.8500),(66,'2023-12-17 21:12:37',0.0000,12.5100),(66,'2023-12-17 21:22:37',0.0000,13.2500),(66,'2023-12-17 21:32:37',0.0000,13.8400),(66,'2023-12-17 21:42:37',0.0000,14.2600),(66,'2023-12-17 21:52:37',0.0000,15.0700),(66,'2023-12-17 22:02:37',0.0000,15.4900),(66,'2023-12-17 22:12:37',0.0000,14.9100),(66,'2023-12-17 22:22:37',0.0000,15.3500),(66,'2023-12-17 22:32:37',0.0000,15.6000),(66,'2023-12-17 22:52:37',0.0000,16.2500),(66,'2023-12-17 23:12:37',0.0000,17.0200),(66,'2023-12-17 23:22:37',0.0000,17.4000),(66,'2023-12-17 23:32:37',0.0000,17.1100),(66,'2023-12-17 23:42:37',0.0000,16.9600),(66,'2023-12-18 00:02:37',0.0000,16.5900),(66,'2023-12-18 00:12:37',0.0000,16.1100),(66,'2023-12-18 00:22:37',0.0000,16.7100),(66,'2023-12-18 00:32:37',0.0000,17.0500),(66,'2023-12-18 00:42:37',0.0000,17.5300),(66,'2023-12-18 00:52:37',0.0000,18.3200),(66,'2023-12-18 01:02:37',0.0000,17.8100),(66,'2023-12-18 01:12:37',0.0000,18.2700),(66,'2023-12-18 01:22:37',0.0000,19.2700),(66,'2023-12-18 01:32:37',0.0000,18.6100),(66,'2023-12-18 01:42:37',0.0000,17.8500),(66,'2023-12-18 01:52:37',0.0000,17.3500),(66,'2023-12-18 02:02:37',0.0000,16.4700),(66,'2023-12-18 02:12:37',0.0000,15.5200),(66,'2023-12-18 02:22:37',0.0000,15.0300),(66,'2023-12-18 02:32:37',0.0000,15.9300),(66,'2023-12-18 02:42:37',0.0000,16.5500),(66,'2023-12-18 02:52:37',0.0000,17.3900),(66,'2023-12-18 03:02:37',0.0000,17.9800),(66,'2023-12-18 03:12:37',0.0000,17.4100),(66,'2023-12-18 03:22:37',0.0000,17.0700),(66,'2023-12-18 03:32:37',0.0000,16.5300),(66,'2023-12-18 03:42:37',0.0000,15.5400),(66,'2023-12-18 03:52:37',0.0000,16.4200),(66,'2023-12-18 04:02:37',0.0000,15.5700),(66,'2023-12-18 04:12:37',0.0000,15.8400),(66,'2023-12-18 04:22:37',0.0000,16.7500),(66,'2023-12-18 04:42:37',0.0000,18.1900),(66,'2023-12-18 04:52:37',0.0000,18.3100),(66,'2023-12-18 05:02:37',0.0000,17.3700),(66,'2023-12-18 05:12:37',0.0000,17.9900),(66,'2023-12-18 05:22:37',0.0000,18.7300),(66,'2023-12-18 05:32:37',0.0000,18.5600),(66,'2023-12-18 05:42:37',0.0000,18.3100),(66,'2023-12-18 05:52:37',0.0000,17.5500),(66,'2023-12-18 06:02:37',0.0000,18.3600),(66,'2023-12-18 06:12:37',0.0000,17.5400),(66,'2023-12-18 06:22:37',0.0000,17.2900),(66,'2023-12-18 06:32:37',0.0000,16.9700),(66,'2023-12-18 06:42:37',0.0000,17.3700),(66,'2023-12-18 06:52:37',0.0000,16.9300),(66,'2023-12-18 07:02:37',0.0000,16.1200),(66,'2023-12-18 07:12:37',0.0000,16.9500),(66,'2023-12-18 07:22:37',0.0000,16.2500),(66,'2023-12-18 07:32:37',0.0000,15.8800),(66,'2023-12-18 07:42:37',0.0000,16.7600),(66,'2023-12-18 07:52:37',0.0000,15.8900),(66,'2023-12-18 08:02:37',0.0000,16.7900),(66,'2023-12-18 08:12:37',0.0000,17.7500),(66,'2023-12-18 08:22:37',0.0000,18.6300),(66,'2023-12-18 08:32:37',0.0000,18.1800),(66,'2023-12-18 08:42:37',0.0000,18.5300),(66,'2023-12-18 08:52:37',0.0000,18.2300),(66,'2023-12-18 09:02:37',0.0000,17.6600),(66,'2023-12-18 09:12:37',0.0000,18.5400),(66,'2023-12-18 09:22:37',0.0000,17.6600),(66,'2023-12-18 09:32:37',0.0000,17.0500),(66,'2023-12-18 09:42:37',0.0000,16.3900),(66,'2023-12-18 09:52:37',0.0000,15.4300),(66,'2023-12-18 10:02:37',0.0000,14.8000),(66,'2023-12-18 10:12:37',0.0000,15.3700),(66,'2023-12-18 10:22:37',0.0000,16.1100),(66,'2023-12-18 10:32:37',0.0000,15.5900),(66,'2023-12-18 10:42:37',0.0000,14.9600),(66,'2023-12-18 10:52:37',0.0000,15.8100),(66,'2023-12-18 11:12:37',0.0000,15.2600),(66,'2023-12-18 11:32:37',0.0000,13.7900),(66,'2023-12-18 11:42:37',0.0000,13.1300),(66,'2023-12-18 11:52:37',0.0000,12.3500),(66,'2023-12-18 12:02:37',0.0000,12.7800),(66,'2023-12-18 12:12:37',0.0000,12.6100),(66,'2023-12-18 12:22:37',0.0000,13.0300),(66,'2023-12-18 12:32:37',0.0000,12.7900),(66,'2023-12-18 12:42:37',0.0000,13.5100),(66,'2023-12-18 12:52:37',0.0000,12.8600),(66,'2023-12-18 13:02:37',0.0000,11.9600),(66,'2023-12-18 13:12:37',0.0000,12.4700),(66,'2023-12-18 13:22:37',0.0000,12.0500),(66,'2023-12-18 13:32:37',0.0000,12.7800),(66,'2023-12-18 13:42:37',0.0000,12.5200),(66,'2023-12-18 13:52:37',0.0000,11.5900),(66,'2023-12-18 14:02:37',0.0000,11.0300),(66,'2023-12-18 14:12:37',0.0000,11.8500),(66,'2023-12-18 14:22:37',0.0000,10.8500),(66,'2023-12-18 14:32:37',0.0000,11.4800),(66,'2023-12-18 14:42:37',0.0000,12.1100),(66,'2023-12-18 14:52:37',0.0000,11.6300),(66,'2023-12-18 15:02:37',0.0000,12.0700),(66,'2023-12-18 15:12:37',0.0000,11.4300),(66,'2023-12-18 15:22:37',0.0000,12.3500),(66,'2023-12-18 15:32:37',0.0000,12.9800),(66,'2023-12-18 15:42:37',0.0000,12.2800),(66,'2023-12-18 15:52:37',0.0000,13.1400),(66,'2023-12-18 16:02:37',0.0000,13.9200),(66,'2023-12-18 16:12:37',0.0000,14.8200),(66,'2023-12-18 16:32:37',0.0000,14.5800),(66,'2023-12-18 16:42:37',0.0000,13.9200),(66,'2023-12-18 16:52:37',0.0000,13.0300),(66,'2023-12-18 17:02:37',0.0000,12.9300),(66,'2023-12-18 17:12:37',0.0000,12.2500),(66,'2023-12-18 17:22:37',0.0000,12.6500),(66,'2023-12-18 17:32:37',0.0000,12.1700),(66,'2023-12-18 17:42:37',0.0000,11.1700),(66,'2023-12-18 17:52:37',0.0000,10.2900),(66,'2023-12-18 18:02:37',0.0000,9.3900),(66,'2023-12-18 18:12:37',0.0000,9.0400),(66,'2023-12-18 18:22:37',0.0000,9.4500),(66,'2023-12-18 18:32:37',0.0000,10.0700),(66,'2023-12-18 18:42:37',0.0000,9.8200),(66,'2023-12-18 18:52:37',0.0000,9.0100),(66,'2023-12-18 19:02:37',0.0000,8.1700),(66,'2023-12-18 19:12:37',0.0000,7.5200),(66,'2023-12-18 19:22:37',0.0000,6.7900),(66,'2023-12-18 19:32:37',0.0000,7.7900),(66,'2023-12-18 19:42:37',0.0000,6.9000),(66,'2023-12-18 19:52:37',0.0000,6.1000),(66,'2023-12-18 20:02:37',0.0000,6.7100),(66,'2023-12-18 20:12:37',0.0000,7.0700),(66,'2023-12-18 20:22:37',0.0000,6.4800),(66,'2023-12-18 20:32:37',0.0000,5.9800),(66,'2023-12-18 20:42:37',0.0000,6.9300),(66,'2023-12-18 20:52:37',0.0000,7.4500),(66,'2023-12-18 21:02:37',0.0000,7.5600),(66,'2023-12-18 21:12:37',0.0000,6.7200),(66,'2023-12-18 21:32:37',0.0000,6.6200),(66,'2023-12-18 21:42:37',0.0000,6.0500),(66,'2023-12-18 21:52:37',0.0000,6.8000),(66,'2023-12-18 22:02:37',0.0000,7.1700),(66,'2023-12-18 22:12:37',0.0000,6.2500),(66,'2023-12-18 22:22:37',0.0000,5.3100),(66,'2023-12-18 22:32:37',0.0000,6.3000),(66,'2023-12-18 22:42:37',0.0000,5.4700),(66,'2023-12-18 22:52:37',0.0000,6.3900),(66,'2023-12-18 23:02:37',0.0000,5.4400),(66,'2023-12-18 23:12:37',0.0000,4.6100),(66,'2023-12-18 23:22:37',0.0000,4.2400),(66,'2023-12-18 23:32:37',0.0000,3.7800),(66,'2023-12-18 23:42:37',0.0000,4.5500),(66,'2023-12-18 23:52:37',0.0000,5.5000),(66,'2023-12-19 00:02:37',0.0000,4.6800),(66,'2023-12-19 00:12:37',0.0000,4.7000),(66,'2023-12-19 00:22:37',0.0000,3.8100),(66,'2023-12-19 00:32:37',0.0000,3.2500),(66,'2023-12-19 00:52:37',0.0000,3.3200),(66,'2023-12-19 01:02:37',0.0000,2.3500),(66,'2023-12-19 01:12:37',0.0000,2.5800),(66,'2023-12-19 01:22:37',0.0000,1.5900),(66,'2023-12-19 01:32:37',0.0000,0.6600),(66,'2023-12-19 01:42:37',0.0000,0.7700),(66,'2023-12-19 01:52:37',0.0000,0.0000),(66,'2023-12-19 02:02:37',0.0000,0.0000),(66,'2023-12-19 02:12:37',0.0000,0.2300),(66,'2023-12-19 02:22:37',0.0000,0.8000),(66,'2023-12-19 02:32:37',0.0000,0.0000),(66,'2023-12-19 02:42:37',0.0000,0.0000),(66,'2023-12-19 02:52:37',0.0000,0.0000),(66,'2023-12-19 03:02:37',0.0000,0.0000),(66,'2023-12-19 03:12:37',0.0000,0.6400),(66,'2023-12-19 03:22:37',0.0000,1.5300),(66,'2023-12-19 03:32:37',0.0000,1.0400),(66,'2023-12-19 03:42:37',0.0000,0.8300),(66,'2023-12-19 03:52:37',0.0000,1.2100),(66,'2023-12-19 04:02:37',0.0000,1.8600),(66,'2023-12-19 04:12:37',0.0000,1.8900),(66,'2023-12-19 04:32:37',0.0000,0.5200),(66,'2023-12-19 04:42:37',0.0000,1.1200),(66,'2023-12-19 04:52:37',0.0000,1.6600),(66,'2023-12-19 05:02:37',0.0000,2.5900),(66,'2023-12-19 05:12:37',0.0000,1.8400),(66,'2023-12-19 05:22:37',0.0000,1.4000),(66,'2023-12-19 05:32:37',0.0000,0.6200),(66,'2023-12-19 05:42:37',0.0000,1.5100),(66,'2023-12-19 05:52:37',0.0000,1.9200),(66,'2023-12-19 06:02:37',0.0000,1.8700),(66,'2023-12-19 06:12:37',0.0000,1.1600),(66,'2023-12-19 06:22:37',0.0000,0.6100),(66,'2023-12-19 06:32:37',0.0000,1.4100),(66,'2023-12-19 06:42:37',0.0000,1.0500),(66,'2023-12-19 06:52:37',0.0000,1.7500),(66,'2023-12-19 07:02:37',0.0000,1.4200),(66,'2023-12-19 07:12:37',0.0000,2.1400),(66,'2023-12-19 07:22:37',0.0000,3.0700),(66,'2023-12-19 07:32:37',0.0000,4.0400),(66,'2023-12-19 07:42:37',0.0000,4.7200),(66,'2023-12-19 07:52:37',0.0000,5.7100),(66,'2023-12-19 08:02:37',0.0000,6.2300),(66,'2023-12-19 08:12:37',0.0000,5.3200),(66,'2023-12-19 08:22:37',0.0000,4.4300),(66,'2023-12-19 08:32:37',0.0000,3.9000);
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
  `limit_push_notifications` int(11) NOT NULL DEFAULT '200',
  `limit_push_notifications_per_hour` int(11) NOT NULL DEFAULT '20',
  `limit_value_based_triggers` int(11) NOT NULL DEFAULT '50',
  `home_latitude` decimal(9,6) NOT NULL,
  `home_longitude` decimal(9,6) NOT NULL,
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
INSERT INTO `supla_user` VALUES (1,'76d67fa6905e61032a1d5715336cf5eb','dffc9a6688858c04f42fb4b2319429aa8e1da7a1ba5e93e552af61b388c3b773aaca2c6c3d49083761cd4447c0b3f21d085979bbd7dd320b5ffa215e35aa1294edfcdbe10c724a7fa23b1a5693f22b31c4092656992bc5979b42859862b84da37d1703db','koe9h1v9tpcgocoo8kkcc0ossg8ssg4','user@supla.org','$2y$13$tk3beLZ3.EwWI5rnjAngtOrtEy1sc5sGjR24j/IV3b6Ld0HddMvo6',1,'2023-12-19 08:52:30',NULL,NULL,10,10,100,200,'Europe/Berlin',20,NULL,'2023-12-26 08:52:31','2023-12-26 08:52:31',20,10,1,1,NULL,NULL,50,20,NULL,NULL,50,NULL,0,NULL,20,'{}',20,200,20,50,52.500000,13.366660),(2,'5eb4319ec53ed54d0b35b10b8f56aa57','e9bde1d3e341abb8e58adcfa54ae3767a4fa2cfd2087dad3f3dc3a717813450bef74bd6d37f0420b4a8636f171ab78193b0eb7a66b7fb64d1967bce81643340c3aaca56688b14059e0e2da69b44a8bb337c4bd79877dd3dec41f121b6d47de2a010833e6','7nlv0xxo3dcsossscgsgo8so4kcg40k','supler@supla.org','$2y$13$rlosLHoPYsbfilW7sAKPW.s1uBsa6KGpjRoUHjKdHMVJWey0QtPBS',1,'2023-12-19 08:52:31',NULL,NULL,10,10,100,200,'Europe/Berlin',20,NULL,'2023-12-26 08:52:32','2023-12-26 08:52:32',20,10,1,1,NULL,NULL,50,20,NULL,NULL,50,NULL,0,NULL,20,'{}',20,200,20,50,52.500000,13.366660);
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
  1 AS `is_now_active` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `supla_v_auto_gate_closing`
--

DROP TABLE IF EXISTS `supla_v_auto_gate_closing`;
/*!50001 DROP VIEW IF EXISTS `supla_v_auto_gate_closing`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `supla_v_auto_gate_closing` AS SELECT
 1 AS `user_id`,
  1 AS `enabled`,
  1 AS `device_id`,
  1 AS `channel_id`,
  1 AS `is_now_active`,
  1 AS `max_time_open`,
  1 AS `seconds_open`,
  1 AS `closing_attempt`,
  1 AS `last_seen_open` */;
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
  1 AS `user_id` */;
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
  1 AS `em_subc_flags`,
  1 AS `value`,
  1 AS `validity_time_sec`,
  1 AS `user_config`,
  1 AS `properties`,
  1 AS `em_subc_user_config`,
  1 AS `em_subc_properties` */;
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
  1 AS `client_id` */;
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
  1 AS `client_id` */;
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
  1 AS `limit_client` */;
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
  1 AS `password` */;
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
  1 AS `channel_hidden` */;
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
  1 AS `hidden` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `supla_value_based_trigger`
--

DROP TABLE IF EXISTS `supla_value_based_trigger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supla_value_based_trigger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `owning_channel_id` int(11) NOT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `channel_group_id` int(11) DEFAULT NULL,
  `scene_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `push_notification_id` int(11) DEFAULT NULL,
  `trigger` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` int(11) NOT NULL,
  `action_param` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `active_from` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `active_to` datetime DEFAULT NULL COMMENT '(DC2Type:utcdatetime)',
  `active_hours` varchar(768) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_conditions` varchar(1024) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1DFF99CAA76ED395` (`user_id`),
  KEY `IDX_1DFF99CA13740A2` (`owning_channel_id`),
  KEY `IDX_1DFF99CA72F5A1AA` (`channel_id`),
  KEY `IDX_1DFF99CA89E4AAEE` (`channel_group_id`),
  KEY `IDX_1DFF99CA166053B4` (`scene_id`),
  KEY `IDX_1DFF99CAA40BC2D5` (`schedule_id`),
  KEY `IDX_1DFF99CA4E328CBE` (`push_notification_id`),
  CONSTRAINT `FK_1DFF99CA13740A2` FOREIGN KEY (`owning_channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1DFF99CA166053B4` FOREIGN KEY (`scene_id`) REFERENCES `supla_scene` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1DFF99CA4E328CBE` FOREIGN KEY (`push_notification_id`) REFERENCES `supla_push_notification` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1DFF99CA72F5A1AA` FOREIGN KEY (`channel_id`) REFERENCES `supla_dev_channel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1DFF99CA89E4AAEE` FOREIGN KEY (`channel_group_id`) REFERENCES `supla_dev_channel_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1DFF99CAA40BC2D5` FOREIGN KEY (`schedule_id`) REFERENCES `supla_schedule` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1DFF99CAA76ED395` FOREIGN KEY (`user_id`) REFERENCES `supla_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_value_based_trigger`
--

LOCK TABLES `supla_value_based_trigger` WRITE;
/*!40000 ALTER TABLE `supla_value_based_trigger` DISABLE KEYS */;
/*!40000 ALTER TABLE `supla_value_based_trigger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'supla'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `supla_current_weekday_hour` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` FUNCTION `supla_current_weekday_hour`(`user_timezone` VARCHAR(50)) RETURNS varchar(3) CHARSET latin1
BEGIN
            DECLARE current_weekday INT;
            DECLARE current_hour INT;
            DECLARE current_time_in_user_timezone DATETIME;
            SELECT COALESCE(CONVERT_TZ(UTC_TIMESTAMP, 'UTC', `user_timezone`), UTC_TIMESTAMP) INTO current_time_in_user_timezone;
            SELECT (WEEKDAY(current_time_in_user_timezone) + 1) INTO current_weekday;
            SELECT HOUR(current_time_in_user_timezone) INTO current_hour;
            RETURN CONCAT(current_weekday, current_hour);
        END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `supla_is_now_active` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
CREATE DEFINER=`root`@`%` FUNCTION `supla_is_now_active`(
    `active_from` DATETIME,
    `active_to` DATETIME,
    `active_hours` VARCHAR(768),
    `user_timezone` VARCHAR(50)
) RETURNS int(11)
BEGIN
    DECLARE res INT DEFAULT 1;
    IF `active_from` IS NOT NULL THEN
        SELECT
            (active_from <= UTC_TIMESTAMP)
        INTO res;
    END IF;
    IF res = 1 AND `active_to` IS NOT NULL THEN
        SELECT (active_to >= UTC_TIMESTAMP) INTO res;
    END IF;
    IF res = 1 AND `active_hours` IS NOT NULL THEN
        SELECT
            (`active_hours` LIKE CONCAT('%,', supla_current_weekday_hour(`user_timezone`), ',%') COLLATE utf8mb4_unicode_ci)
        INTO res;
    END IF;
    RETURN res;
END;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP FUNCTION IF EXISTS `version_to_int` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_channel` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_client` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_em_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_em_voltage_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_em_voltage_log_item`(
                IN `_date` DATETIME,
                IN `_channel_id` INT(11),
                IN `_phase_no` TINYINT,
                IN `_count_total` INT(11),
                IN `_count_above` INT(11),
                IN `_count_below` INT(11),
                IN `_sec_above` INT(11),
                IN `_sec_below` INT(11),
                IN `_max_sec_above` INT(11),
                IN `_max_sec_below` INT(11),
                IN `_min_voltage` NUMERIC(7,2),
                IN `_max_voltage` NUMERIC(7,2),
                IN `_avg_voltage` NUMERIC(7,2),
                IN `_measurement_time_sec` INT(11)
            )
    NO SQL
BEGIN
            INSERT INTO `supla_em_voltage_log` (`date`,channel_id, phase_no, count_total, count_above, count_below, sec_above, sec_below, max_sec_above, max_sec_below, min_voltage, max_voltage, avg_voltage, measurement_time_sec)
                                        VALUES (_date,_channel_id,_phase_no,_count_total,_count_above,_count_below,_sec_above,_sec_below,_max_sec_above,_max_sec_below,_min_voltage,_max_voltage,_avg_voltage,_measurement_time_sec);

            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_ic_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_iodevice` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_temperature_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_temphumidity_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_add_thermostat_log_item` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_thermostat_log_item`(IN `_channel_id` INT(11), IN `_measured_temperature` DECIMAL(5,2), IN `_preset_temperature` DECIMAL(5,2), IN `_on` TINYINT)
    NO SQL
BEGIN INSERT INTO `supla_thermostat_log`(`channel_id`, `date`, `measured_temperature`, `preset_temperature`, `on`) VALUES (_channel_id,UTC_TIMESTAMP(),_measured_temperature, _preset_temperature, _on); END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_disable_schedule` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_disable_schedule`(IN `_user_id` INT, IN `_id` INT)
BEGIN UPDATE supla_schedule SET enabled = 0 WHERE id = _id AND user_id = _user_id; DELETE FROM supla_scheduled_executions WHERE schedule_id = _id AND planned_timestamp >= UTC_TIMESTAMP(); END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_enable_schedule` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_enable_schedule`(IN `_user_id` INT, IN `_id` INT)
UPDATE supla_schedule SET enabled = 1, next_calculation_date = UTC_TIMESTAMP() WHERE id = _id AND user_id = _user_id AND enabled != 1 ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_get_device_firmware_url` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_mark_gate_closed` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_mark_gate_closed`(IN `_channel_id` INT)
UPDATE
    `supla_auto_gate_closing`
SET
    seconds_open = NULL,
    closing_attempt = NULL,
    last_seen_open = NULL
WHERE
    channel_id = _channel_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_mark_gate_open` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_mark_gate_open`(IN `_channel_id` INT)
BEGIN
    -- We assume the server will mark open gates at least every minute.
    UPDATE
        `supla_auto_gate_closing`
    SET
        seconds_open = NULL,
        closing_attempt = NULL,
        last_seen_open = NULL
    WHERE
        channel_id = _channel_id AND last_seen_open IS NOT NULL AND TIMESTAMPDIFF(MINUTE, last_seen_open, UTC_TIMESTAMP()) >= 4;

    UPDATE
        `supla_auto_gate_closing`
    SET
        seconds_open = IFNULL(seconds_open, 0) + IFNULL(
            UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(last_seen_open),
            0),
            last_seen_open = UTC_TIMESTAMP()
        WHERE
            channel_id = _channel_id;

      SELECT
            max_time_open - seconds_open AS `seconds_left`
      FROM
            `supla_auto_gate_closing`
      WHERE
             channel_id = _channel_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_mqtt_broker_auth` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_oauth_add_client_for_app` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_oauth_add_token_for_app` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_oauth_add_token_for_app`(IN `_user_id` INT(11), IN `_token` VARCHAR(255) CHARSET utf8,
IN `_expires_at` INT(11), IN `_access_id` INT(11), OUT `_id` INT(11))
    NO SQL
BEGIN

SET @client_id = 0;

SELECT `id` INTO @client_id FROM `supla_oauth_clients` WHERE `type` = 2 LIMIT 1;

IF @client_id <> 0 AND EXISTS(SELECT 1 FROM `supla_accessid` WHERE `user_id` = _user_id AND `id` = _access_id) THEN

  INSERT INTO `supla_oauth_access_tokens`(`client_id`, `user_id`, `token`, `expires_at`, `scope`, `access_id`) VALUES
   (@client_id, _user_id, _token, _expires_at, 'channels_r channels_files', _access_id);
  SELECT LAST_INSERT_ID() INTO _id;

END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_on_channeladded` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_on_newclient` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_on_newdevice` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_register_device_managed_push` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_register_device_managed_push`(IN `_user_id` INT, IN `_device_id` INT, IN `_channel_id` INT, IN `_sm_title` TINYINT, IN `_sm_body` TINYINT, IN `_sm_sound` TINYINT)
INSERT INTO `supla_push_notification`(
    `user_id`,
    `iodevice_id`,
    `channel_id`,
    `managed_by_device`,
    `title`,
    `body`,
    `sound`
)
SELECT
    _user_id,
    _device_id,
    CASE _channel_id
      WHEN 0 THEN NULL ELSE _channel_id END,
    1,
    CASE _sm_title WHEN 0 THEN NULL ELSE '' END,
    CASE _sm_body WHEN 0 THEN NULL ELSE '' END,
    CASE _sm_sound WHEN 0 THEN NULL ELSE 0 END
FROM DUAL
  WHERE NOT EXISTS(
   SELECT id
    FROM `supla_push_notification`
    WHERE user_id = _user_id
      AND iodevice_id = _device_id
      AND managed_by_device = 1
      AND (( _channel_id = 0 AND channel_id IS NULL)
        OR( channel_id != 0 AND channel_id =
           _channel_id)) LIMIT 1) ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_remove_push_recipients` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_remove_push_recipients`(IN `_user_id` INT, IN `_client_id` INT)
UPDATE supla_client SET push_token = NULL WHERE id = _client_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_channel_caption` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_channel_caption`(IN `_user_id` INT, IN `_channel_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4)
    NO SQL
UPDATE supla_dev_channel SET caption = _caption WHERE id = _channel_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_channel_function` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_channel_function`(IN `_user_id` INT, IN `_channel_id` INT, IN `_func` INT)
    NO SQL
UPDATE supla_dev_channel SET func = _func WHERE id = _channel_id AND user_id = _user_id AND type = 8000 ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_channel_group_caption` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_channel_group_caption`(IN `_user_id` INT, IN `_channel_group_id` INT, IN `_caption` VARCHAR(255) CHARSET utf8mb4)
    NO SQL
UPDATE supla_dev_channel_group SET caption = _caption WHERE id = _channel_group_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_channel_json_config` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_channel_json_config`(IN `_user_id` INT, IN `_channel_id` INT, IN `_user_config` VARCHAR(4096) CHARSET utf8mb4, IN `_user_config_md5` VARCHAR(32), IN `_properties` VARCHAR(2048) CHARSET utf8mb4, IN `_properties_md5` VARCHAR(32))
BEGIN UPDATE supla_dev_channel SET user_config = _user_config, properties = _properties WHERE id = _channel_id AND user_id = _user_id AND MD5(IFNULL(user_config, '')) = _user_config_md5 AND MD5(IFNULL(properties, '')) = _properties_md5; SELECT ABS(STRCMP(user_config, _user_config))+ABS(STRCMP(properties, _properties)) FROM supla_dev_channel WHERE id = _channel_id AND user_id = _user_id; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_closing_attempt` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_closing_attempt`(IN `_channel_id` INT)
UPDATE
        `supla_auto_gate_closing`
    SET
        closing_attempt = UTC_TIMESTAMP()
    WHERE
        channel_id = _channel_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_device_json_config` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_device_json_config`(IN `_user_id` INT, IN `_device_id` INT, IN `_user_config` VARCHAR(4096) CHARSET utf8mb4, IN `_user_config_md5` VARCHAR(32), IN `_properties` VARCHAR(2048) CHARSET utf8mb4, IN `_properties_md5` VARCHAR(32))
BEGIN UPDATE supla_iodevice SET user_config = _user_config, properties = _properties WHERE id = _device_id AND user_id = _user_id AND MD5(IFNULL(user_config, '')) = _user_config_md5 AND MD5(IFNULL(properties, '')) = _properties_md5; SELECT ABS(STRCMP(user_config, _user_config))+ABS(STRCMP(properties, _properties)) FROM supla_iodevice WHERE id = _device_id AND user_id = _user_id; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_location_caption` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_location_caption`(IN `_user_id` INT, IN `_location_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4)
    NO SQL
UPDATE supla_location SET caption = _caption WHERE id = _location_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_registration_enabled` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_registration_enabled`(IN `user_id` INT, IN `iodevice_sec` INT, IN `client_sec` INT)
    NO SQL
BEGIN IF iodevice_sec >= 0 THEN SET @date = NULL; IF iodevice_sec > 0 THEN SET @date = DATE_ADD(UTC_TIMESTAMP, INTERVAL iodevice_sec SECOND); END IF; UPDATE supla_user SET iodevice_reg_enabled = @date WHERE id = user_id; END IF; IF client_sec >= 0 THEN SET @date = NULL; IF client_sec > 0 THEN SET @date = DATE_ADD(UTC_TIMESTAMP, INTERVAL client_sec SECOND); END IF; UPDATE supla_user SET client_reg_enabled = @date WHERE id = user_id; END IF; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_set_scene_caption` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_set_scene_caption`(IN `_user_id` INT, IN `_scene_id` INT, IN `_caption` VARCHAR(255) CHARSET utf8mb4)
    NO SQL
UPDATE supla_scene SET caption = _caption WHERE id = _scene_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_amazon_alexa` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_amazon_alexa`(IN `_access_token` VARCHAR(1024) CHARSET utf8, IN `_refresh_token` VARCHAR(1024) CHARSET utf8, IN `_expires_in` INT, IN `_user_id` INT)
    NO SQL
BEGIN UPDATE supla_amazon_alexa SET `access_token` = _access_token, `refresh_token` = _refresh_token, `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _expires_in second) WHERE `user_id` = _user_id; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_extended_value` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_channel_extended_value`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_type` TINYINT,
    IN `_value` VARBINARY(1024)
)
    NO SQL
BEGIN
    UPDATE `supla_dev_channel_extended_value` SET
        `update_time` = UTC_TIMESTAMP(), `type` = _type, `value` = _value
         WHERE user_id = _user_id AND channel_id = _id;

    IF ROW_COUNT() = 0 THEN
      INSERT INTO `supla_dev_channel_extended_value` (`channel_id`, `user_id`, `update_time`, `type`, `value`)
         VALUES(_id, _user_id, UTC_TIMESTAMP(), _type, _value)
      ON DUPLICATE KEY UPDATE `type` = _type, `value` = _value, `update_time` = UTC_TIMESTAMP();
     END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_flags` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_channel_flags`(IN `_channel_id` INT, IN `_user_id` INT, IN `_flags` INT)
    NO SQL
UPDATE supla_dev_channel SET flags = IFNULL(flags, 0) | IFNULL(_flags, 0) WHERE id = _channel_id AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_params` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
    SET
        param1 = _param1,
        param2 = _param2,
        param3 = _param3,
        param4 = _param4
    WHERE
        id = _id AND user_id = _user_id ;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_properties` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_channel_value` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_channel_value`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_value` VARBINARY(8),
    IN `_validity_time_sec` INT
)
    NO SQL
BEGIN
    IF _validity_time_sec > 0 THEN
        SET @valid_to = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _validity_time_sec SECOND);
    END IF;

    UPDATE `supla_dev_channel_value` SET
        `update_time` = UTC_TIMESTAMP(), `valid_to` = @valid_to,  `value` = _value
         WHERE user_id = _user_id AND channel_id = _id;

    IF ROW_COUNT() = 0 THEN
      INSERT INTO `supla_dev_channel_value` (`channel_id`, `user_id`, `update_time`, `valid_to`, `value`)
         VALUES(_id, _user_id, UTC_TIMESTAMP(), @valid_to, _value)
      ON DUPLICATE KEY UPDATE `value` = _value, update_time = UTC_TIMESTAMP(), valid_to = @valid_to;
     END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_client` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_google_home` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_google_home`(IN `_access_token` VARCHAR(255) CHARSET utf8, IN `_user_id` INT)
    NO SQL
BEGIN UPDATE supla_google_home SET `access_token` = _access_token WHERE `user_id` = _user_id; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_iodevice` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_iodevice`(IN `_name` VARCHAR(100) CHARSET utf8mb4, IN `_last_ipv4` INT(10) UNSIGNED,
  IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11), IN `_original_location_id` INT(11),
  IN `_auth_key` VARCHAR(64) CHARSET utf8, IN `_id` INT(11), IN `_flags` INT(11))
    NO SQL
BEGIN
UPDATE `supla_iodevice`
SET
`name` = _name,
`last_connected` = UTC_TIMESTAMP(),
`last_ipv4` = _last_ipv4,
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
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_push_notification_client_token` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_push_notification_client_token`(IN `_user_id` INT, IN `_client_id` INT, IN `_token` VARCHAR(255) CHARSET utf8mb4, IN `_platform` TINYINT, IN `_app_id` INT, IN `_devel_env` TINYINT)
UPDATE supla_client SET
   push_token = _token,
   push_token_update_time = UTC_TIMESTAMP(),
   platform = _platform,
   app_id = _app_id,
   devel_env = _devel_env
WHERE id = _client_id
 AND user_id = _user_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `supla_update_state_webhook` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
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
/*!50001 VIEW `supla_v_client_channel` AS select `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`,ifnull(`c`.`param1`,0) AS `param1`,ifnull(`c`.`param2`,0) AS `param2`,`c`.`caption` AS `caption`,ifnull(`c`.`param3`,0) AS `param3`,ifnull(`c`.`param4`,0) AS `param4`,`c`.`text_param1` AS `text_param1`,`c`.`text_param2` AS `text_param2`,`c`.`text_param3` AS `text_param3`,ifnull(`d`.`manufacturer_id`,0) AS `manufacturer_id`,ifnull(`d`.`product_id`,0) AS `product_id`,ifnull(`c`.`user_icon_id`,0) AS `user_icon_id`,`c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,`c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,(case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end) AS `location_id`,ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version`,ifnull(`c`.`flags`,0) AS `flags`,ifnull(`em_subc`.`flags`,0) AS `em_subc_flags`,`v`.`value` AS `value`,(case when (`v`.`valid_to` >= utc_timestamp()) then time_to_sec(timediff(`v`.`valid_to`,utc_timestamp())) else NULL end) AS `validity_time_sec`,`c`.`user_config` AS `user_config`,`c`.`properties` AS `properties`,`em_subc`.`user_config` AS `em_subc_user_config`,`em_subc`.`properties` AS `em_subc_properties` from (((((((`supla_dev_channel` `c` join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) join `supla_location` `l` on((`l`.`id` = (case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end)))) join `supla_rel_aidloc` `r` on((`r`.`location_id` = `l`.`id`))) join `supla_accessid` `a` on((`a`.`id` = `r`.`access_id`))) join `supla_client` `cl` on((`cl`.`access_id` = `r`.`access_id`))) left join `supla_dev_channel_value` `v` on((`c`.`id` = `v`.`channel_id`))) left join `supla_dev_channel` `em_subc` on(((`em_subc`.`user_id` = `c`.`user_id`) and (`em_subc`.`type` = 5000) and ((((`c`.`func` = 130) or (`c`.`func` = 140)) and (`c`.`param1` = `em_subc`.`id`)) or ((`c`.`func` = 300) and (`c`.`param2` = `em_subc`.`id`)))))) where ((((`c`.`func` is not null) and (`c`.`func` <> 0)) or (`c`.`type` = 8000)) and (ifnull(`c`.`hidden`,0) = 0) and (`d`.`enabled` = 1) and (`l`.`enabled` = 1) and (`a`.`enabled` = 1)) */;
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

-- Dump completed on 2023-12-19  9:52:54
