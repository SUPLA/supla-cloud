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
INSERT INTO `supla_accessid` VALUES (1,1,'eabba362','Access Identifier #1',1),(2,2,'d96f8703','Access Identifier #1',1),(3,1,'233f3f8c','Wspólny',1),(4,1,'8b47fd07','Dzieci',1),(5,2,'b67dfde3','Supler Access ID',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_audit`
--

LOCK TABLES `supla_audit` WRITE;
/*!40000 ALTER TABLE `supla_audit` DISABLE KEYS */;
INSERT INTO `supla_audit` VALUES (1,NULL,2,'2019-12-27 10:32:47',2130706433,'',0),(2,1,1,'2019-12-27 10:32:52',2130706433,'user@supla.org',NULL);
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
INSERT INTO `supla_client` VALUES (1,4,_binary '2792391','HTC One M8',0,1287684161,'2019-12-19 09:24:26',2074964767,'2019-12-24 23:33:59','1.15',78,1,NULL,NULL,NULL),(2,3,_binary '6364841','iPhone 6s',1,597004240,'2019-11-30 19:11:40',794570769,'2019-12-22 13:23:30','1.49',67,1,NULL,NULL,NULL),(3,4,_binary '5599719','Nokia 3310',0,546941552,'2019-11-17 00:22:03',1421573028,'2019-12-26 09:00:50','1.82',64,1,NULL,NULL,NULL),(4,NULL,_binary '2244836','Samsung Galaxy Tab S2',1,1480800457,'2019-12-06 10:46:10',946664042,'2019-12-21 17:43:13','1.51',91,1,NULL,NULL,NULL),(5,3,_binary '2634950','Apple iPad',1,1796029905,'2019-11-16 04:44:25',989133781,'2019-12-22 15:18:17','1.12',48,1,NULL,NULL,NULL),(6,5,_binary '3486330','SUPLER PHONE',1,2138495090,'2019-11-17 22:01:45',1189984198,'2019-12-22 18:36:19','1.67',1,2,NULL,NULL,NULL);
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
INSERT INTO `supla_dev_channel` VALUES (1,1,1,0,'Quibusdam aliquam odio a.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(2,1,1,1,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(3,2,1,0,NULL,2900,140,96,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(4,2,1,1,NULL,2900,90,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(5,2,1,2,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(6,2,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(7,2,1,4,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(8,2,1,5,'Distinctio omnis dolorem.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(9,2,1,6,'Sint vel.',3000,40,NULL,0,-40,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(10,3,1,0,'Veniam recusandae debitis.',4010,200,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(11,3,1,1,NULL,4010,190,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(12,4,1,0,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(13,4,1,1,NULL,1000,60,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(14,4,1,2,'Error vel sunt.',1000,70,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(15,4,1,3,NULL,1000,100,NULL,150,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(16,4,1,4,NULL,1000,80,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(17,4,1,5,NULL,1000,120,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(18,4,1,6,NULL,1000,230,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(19,4,1,7,NULL,1000,240,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(20,4,1,8,'Facere officiis deleniti occaecati aut.',1010,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(21,4,1,9,'Exercitationem maxime.',1010,60,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(22,4,1,10,NULL,1010,70,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(23,4,1,11,'Rerum quis iusto.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(24,4,1,12,'Autem sed fugit.',1010,80,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(25,4,1,13,'Tenetur ad repellat repellat.',1010,120,NULL,215,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(26,4,1,14,NULL,1010,230,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(27,4,1,15,'Quia fuga sint molestias.',1010,240,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(28,4,1,16,'Possimus assumenda neque libero unde.',1020,210,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(29,4,1,17,NULL,1020,220,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(30,4,1,18,NULL,2000,10,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(31,4,1,19,NULL,2000,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(32,4,1,20,'Perspiciatis a maxime et.',2000,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(33,4,1,21,NULL,2000,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(34,4,1,22,'Quis voluptatum.',2010,10,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(35,4,1,23,'Non maiores facilis repellendus.',2010,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(36,4,1,24,NULL,2010,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(37,4,1,25,'Fuga facilis facilis.',2010,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(38,4,1,26,NULL,2010,130,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(39,4,1,27,'Vitae nostrum aliquid rerum.',2010,140,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(40,4,1,28,NULL,2010,300,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(41,4,1,29,'Vel in quasi quisquam.',2020,10,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(42,4,1,30,NULL,2020,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(43,4,1,31,'Sit necessitatibus provident labore.',2020,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(44,4,1,32,'Et iure expedita.',2020,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(45,4,1,33,'Voluptas quo officia.',2020,130,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(46,4,1,34,'Tenetur dolorem explicabo aliquam perferendis.',2020,140,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(47,4,1,35,'Doloremque omnis et.',2020,110,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(48,4,1,36,NULL,2020,300,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(49,4,1,37,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(50,4,1,38,'Itaque dolor et.',3010,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(51,4,1,39,NULL,3022,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(52,4,1,40,NULL,3020,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(53,4,1,41,'Velit et.',3032,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(54,4,1,42,NULL,3030,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(55,4,1,43,'Expedita quia vel libero impedit.',3034,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(56,4,1,44,'Vel praesentium impedit.',3036,42,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(57,4,1,45,NULL,3038,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(58,4,1,46,NULL,3042,250,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(59,4,1,47,NULL,3044,260,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(60,4,1,48,NULL,3048,270,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(61,4,1,49,NULL,3050,280,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(62,4,1,50,NULL,3100,290,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(63,4,1,51,NULL,4000,180,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(64,4,1,52,NULL,4010,190,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(65,4,1,53,NULL,4020,200,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(66,4,1,54,'Dolores placeat est.',5000,310,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(67,4,1,55,NULL,5010,310,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(68,4,1,56,'In odio autem perferendis atque.',5010,320,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(69,4,1,57,NULL,5010,330,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(70,4,1,58,NULL,6000,400,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(71,4,1,59,'Omnis saepe voluptas cupiditate.',6010,410,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(72,5,1,0,'Eum ipsam nemo.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(73,5,1,1,NULL,1000,60,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(74,5,1,2,NULL,1000,70,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(75,5,1,3,'Iusto eum libero sunt quis.',1000,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(76,5,1,4,NULL,1000,80,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(77,5,1,5,NULL,1000,120,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(78,5,1,6,'Debitis nihil atque.',1000,230,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(79,5,1,7,'Rerum et non.',1000,240,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(80,5,1,8,'Facilis nam illo.',1010,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(81,5,1,9,NULL,1010,60,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(82,5,1,10,NULL,1010,70,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(83,5,1,11,'Doloremque quia ipsam qui.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(84,5,1,12,'Ut id asperiores ad.',1010,80,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(85,5,1,13,'Sunt est.',1010,120,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(86,5,1,14,'Et dolores consectetur.',1010,230,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(87,5,1,15,'Perferendis id vero ut neque.',1010,240,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(88,5,1,16,'Sit in voluptas ut doloribus.',1020,210,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(89,5,1,17,'Tenetur ea dolores possimus.',1020,220,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(90,5,1,18,'Impedit nesciunt et.',2000,10,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(91,5,1,19,'Commodi consequatur at.',2000,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(92,5,1,20,'Aut commodi asperiores.',2000,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(93,5,1,21,'Reiciendis non consectetur.',2000,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(94,5,1,22,'Eos ullam.',2010,0,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(95,5,1,23,'Ea vel delectus quidem.',2010,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(96,5,1,24,'Quidem recusandae occaecati.',2010,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(97,5,1,25,NULL,2010,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(98,5,1,26,'Culpa ipsa libero.',2010,130,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(99,5,1,27,NULL,2010,140,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(100,5,1,28,'Sit deserunt assumenda laudantium iusto.',2010,300,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(101,5,1,29,NULL,2020,10,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(102,5,1,30,'Error soluta aut inventore.',2020,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(103,5,1,31,'Inventore aut praesentium deserunt.',2020,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(104,5,1,32,NULL,2020,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(105,5,1,33,NULL,2020,0,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(106,5,1,34,NULL,2020,140,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(107,5,1,35,NULL,2020,110,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(108,5,1,36,'Nesciunt cum.',2020,300,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(109,5,1,37,'Est qui in sequi.',3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(110,5,1,38,'Assumenda eum.',3010,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(111,5,1,39,NULL,3022,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(112,5,1,40,'Et pariatur.',3020,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(113,5,1,41,'Eos quo vel voluptate.',3032,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(114,5,1,42,'Eius minima.',3030,0,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(115,5,1,43,'Facilis at animi.',3034,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(116,5,1,44,'Facere accusamus.',3036,42,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(117,5,1,45,'Rerum sed illum.',3038,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(118,5,1,46,'Voluptas necessitatibus consequatur voluptas.',3042,250,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(119,5,1,47,'Quibusdam magni id quae.',3044,260,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(120,5,1,48,'Voluptas voluptatem sit.',3048,270,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(121,5,1,49,'Provident voluptas vitae.',3050,280,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(122,5,1,50,NULL,3100,290,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(123,5,1,51,'Dolores eligendi velit esse.',4000,180,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(124,5,1,52,'Atque eos facere et.',4010,190,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(125,5,1,53,NULL,4020,200,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(126,5,1,54,NULL,5000,310,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(127,5,1,55,NULL,5010,310,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(128,5,1,56,NULL,5010,320,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(129,5,1,57,NULL,5010,330,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(130,5,1,58,'Dolorum modi.',6000,400,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(131,5,1,59,NULL,6010,410,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(132,6,1,0,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(133,6,1,1,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(134,6,1,2,'Soluta magnam laboriosam quibusdam.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(135,6,1,3,'Nulla recusandae in et.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(136,6,1,4,'Qui rerum odio.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(137,6,1,5,'Repellat error occaecati.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(138,6,1,6,'Omnis praesentium provident.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(139,6,1,7,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(140,6,1,8,'Facere totam cumque.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(141,6,1,9,'Ut iusto alias fuga.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(142,7,1,0,'Vitae et.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(143,7,1,1,NULL,2900,90,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(144,7,1,2,'Fuga nobis magni doloremque.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(145,7,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(146,7,1,4,'Voluptatem dolores.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(147,7,1,5,'Voluptas atque molestiae.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(148,7,1,6,'Doloribus qui.',3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(149,8,1,0,NULL,2900,140,96,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(150,8,1,1,'Et qui maiores et.',2900,90,15,4000,15,0,NULL,NULL,NULL,0,0,3,0,NULL),(151,8,1,2,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(152,8,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(153,8,1,4,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(154,8,1,5,NULL,1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(155,8,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(156,9,1,0,'Tenetur excepturi aspernatur consequatur.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(157,9,1,1,NULL,2900,90,15,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(158,9,1,2,'Repudiandae molestiae et veritatis dolorem.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(159,9,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(160,9,1,4,'Reiciendis eligendi.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(161,9,1,5,NULL,1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(162,9,1,6,'Aut eos autem ea.',3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(163,10,1,0,NULL,2900,140,96,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(164,10,1,1,NULL,2900,90,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(165,10,1,2,'Ullam deleniti nesciunt.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(166,10,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(167,10,1,4,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(168,10,1,5,NULL,1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(169,10,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(170,11,1,0,'Sunt sunt beatae.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(171,11,1,1,'Illo a voluptas quidem.',2900,90,15,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(172,11,1,2,'Accusantium aut sit harum.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(173,11,1,3,'Et vel unde explicabo.',2900,110,16,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(174,11,1,4,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(175,11,1,5,'Eum quae voluptatem ut.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(176,11,1,6,'Vero est sit deserunt.',3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(177,12,1,0,'Nisi quam et.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(178,12,1,1,NULL,2900,90,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(179,12,1,2,'Ut sed autem consequuntur.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(180,12,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(181,12,1,4,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(182,12,1,5,NULL,1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(183,12,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(184,13,1,0,'Vel et dolores.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(185,13,1,1,'Accusantium tempora sed.',2900,90,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(186,13,1,2,'Unde cupiditate occaecati occaecati.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(187,13,1,3,'Incidunt ab eligendi.',2900,110,16,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(188,13,1,4,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(189,13,1,5,'Perspiciatis reprehenderit incidunt.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(190,13,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(191,14,1,0,'Enim commodi sed voluptatem.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(192,14,1,1,'Sint atque sunt.',2900,90,15,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(193,14,1,2,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(194,14,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(195,14,1,4,'Repellat nemo sint.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(196,14,1,5,NULL,1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(197,14,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(198,15,1,0,NULL,2900,140,96,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(199,15,1,1,NULL,2900,90,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(200,15,1,2,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(201,15,1,3,'Sunt non ut.',2900,110,16,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(202,15,1,4,'Sit neque ut voluptate.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(203,15,1,5,'Perferendis facere et.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(204,15,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(205,16,1,0,'Voluptas praesentium voluptas.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(206,16,1,1,NULL,2900,90,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(207,16,1,2,'Reprehenderit minima expedita.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(208,16,1,3,'Minus aut eligendi.',2900,110,16,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(209,16,1,4,'Rerum possimus autem.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(210,16,1,5,'Repudiandae quasi nemo.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(211,16,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(212,17,1,0,'Rerum tempora ex.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(213,17,1,1,'Sapiente dolorum autem vel.',2900,90,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(214,17,1,2,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(215,17,1,3,'Expedita facere laborum facere voluptatum.',2900,110,16,385,25,445,NULL,NULL,NULL,0,0,NULL,0,NULL),(216,17,1,4,'Ut deleniti.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(217,17,1,5,'Dolorem harum ut.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(218,17,1,6,'Odit cum iste.',3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(219,18,1,0,NULL,2900,140,96,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(220,18,1,1,NULL,2900,90,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(221,18,1,2,'Laboriosam quod aut.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(222,18,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(223,18,1,4,'Quo saepe ducimus.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(224,18,1,5,'Facilis est omnis.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(225,18,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(226,19,1,0,'Qui ut deserunt.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(227,19,1,1,'Assumenda nisi ex.',2900,90,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(228,19,1,2,NULL,2900,20,15,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(229,19,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(230,19,1,4,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(231,19,1,5,'Enim provident culpa est.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(232,19,1,6,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(233,20,1,0,'Pariatur facere enim.',2900,140,96,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(234,20,1,1,'Ad amet quod.',2900,90,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(235,20,1,2,'Molestias recusandae excepturi dolorem.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(236,20,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(237,20,1,4,NULL,1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(238,20,1,5,'Sunt quis ea est.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(239,20,1,6,'Explicabo quod facere perferendis.',3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(240,21,1,0,NULL,2900,140,96,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(241,21,1,1,'Repellendus deleniti eum.',2900,90,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(242,21,1,2,'Minima dolores.',2900,20,15,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(243,21,1,3,NULL,2900,110,16,0,0,0,NULL,NULL,NULL,0,0,4,0,NULL),(244,21,1,4,'Harum temporibus qui.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,3,0,NULL),(245,21,1,5,'Voluptatem sequi quibusdam.',1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(246,21,1,6,'Molestiae accusantium nam eum sapiente.',3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(247,22,2,0,'Aliquid aut non.',1000,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(248,22,2,1,'Ratione ut blanditiis qui.',1000,60,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(249,22,2,2,NULL,1000,70,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(250,22,2,3,'Qui quasi vel.',1000,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(251,22,2,4,NULL,1000,80,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(252,22,2,5,NULL,1000,120,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(253,22,2,6,NULL,1000,230,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(254,22,2,7,NULL,1000,240,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(255,22,2,8,'Rem sit vitae sunt.',1010,50,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(256,22,2,9,NULL,1010,60,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(257,22,2,10,'Dolorem at.',1010,70,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(258,22,2,11,NULL,1010,100,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(259,22,2,12,NULL,1010,80,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(260,22,2,13,NULL,1010,120,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(261,22,2,14,'Vero sed et.',1010,230,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(262,22,2,15,'Explicabo labore cumque ipsam.',1010,240,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(263,22,2,16,'Itaque voluptatem sit.',1020,210,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(264,22,2,17,'Quis rerum quia architecto.',1020,220,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(265,22,2,18,'Fugit similique nisi et fugit.',2000,10,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(266,22,2,19,NULL,2000,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(267,22,2,20,NULL,2000,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(268,22,2,21,NULL,2000,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(269,22,2,22,'Est qui non quia.',2010,10,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(270,22,2,23,NULL,2010,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(271,22,2,24,NULL,2010,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(272,22,2,25,NULL,2010,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(273,22,2,26,'Quos sunt voluptatum repellendus est.',2010,130,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(274,22,2,27,'Molestiae blanditiis et doloribus.',2010,140,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(275,22,2,28,'Autem est sed.',2010,300,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(276,22,2,29,'Voluptatem a inventore voluptatibus quis.',2020,10,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(277,22,2,30,'Corrupti sit quas aut.',2020,20,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(278,22,2,31,NULL,2020,30,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(279,22,2,32,NULL,2020,90,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(280,22,2,33,'Aut autem labore.',2020,130,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(281,22,2,34,NULL,2020,140,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(282,22,2,35,NULL,2020,110,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(283,22,2,36,'Enim eos suscipit inventore.',2020,300,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(284,22,2,37,NULL,3000,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(285,22,2,38,'Laborum nesciunt dolorum.',3010,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(286,22,2,39,NULL,3022,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(287,22,2,40,NULL,3020,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(288,22,2,41,NULL,3032,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(289,22,2,42,NULL,3030,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(290,22,2,43,NULL,3034,40,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(291,22,2,44,'Ipsa velit ut.',3036,42,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(292,22,2,45,NULL,3038,45,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(293,22,2,46,'Vero qui alias.',3042,250,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(294,22,2,47,NULL,3044,260,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(295,22,2,48,'Et culpa quos et.',3048,270,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(296,22,2,49,NULL,3050,280,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(297,22,2,50,NULL,3100,290,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(298,22,2,51,NULL,4000,180,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(299,22,2,52,'Soluta labore consequatur aut.',4010,190,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(300,22,2,53,NULL,4020,200,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(301,22,2,54,'Et quasi unde.',5000,310,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(302,22,2,55,'Natus facilis recusandae.',5010,310,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(303,22,2,56,NULL,5010,320,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(304,22,2,57,NULL,5010,330,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(305,22,2,58,NULL,6000,400,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL),(306,22,2,59,'Sed in.',6010,410,NULL,0,0,0,NULL,NULL,NULL,0,0,NULL,0,NULL);
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
INSERT INTO `supla_dev_channel_group` VALUES (1,1,'Światła na parterze',140,0,4,0,NULL),(2,1,'Dolorem laborum.',90,0,4,0,NULL),(3,1,'Molestiae in ipsam.',20,0,5,0,NULL),(4,1,'Molestias neque quod.',140,0,3,0,NULL),(5,1,'Dolor minus ea.',140,0,3,0,NULL),(6,1,'Assumenda quia tempore.',140,0,5,0,NULL),(7,1,'Ex nesciunt laborum optio.',90,0,5,0,NULL),(8,1,'Ut nihil explicabo voluptatem.',90,0,4,0,NULL),(9,1,'Quia sunt.',20,0,4,0,NULL),(10,1,'Quam veritatis quia quia.',140,0,5,0,NULL),(11,1,'Et iusto sint quia.',110,0,4,0,NULL);
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
INSERT INTO `supla_direct_link` VALUES (1,1,154,NULL,'TZxZMWjFZzE','White','[1000]',NULL,NULL,NULL,NULL,3647206537,1,0),(2,1,213,NULL,'3YzGNDZNGi','PeachPuff','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(3,1,235,NULL,'5TEQNZWENW4x','MediumVioletRed','[90]',NULL,NULL,NULL,NULL,NULL,1,0),(4,1,152,NULL,'mDimN4YyM2NNG','LightGreen','[40]',NULL,NULL,NULL,NULL,NULL,1,0),(5,1,194,NULL,'ZTzDcRGFNZjmNh','MintCream','[30]',NULL,NULL,NULL,NULL,NULL,1,0),(6,1,238,NULL,'zZZD3QEGDMY54','MistyRose','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(7,1,175,NULL,'UDYNATDM2ZTZ','MediumTurquoise','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(8,1,211,NULL,'NmQxM2ZGGZ','DarkViolet','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(9,1,201,NULL,'jM2UDi3NMNZzV','LightYellow','[40]',NULL,NULL,NULL,NULL,NULL,1,0),(10,1,150,NULL,'TWWM2MyMRQQ','LightGoldenRodYellow','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(11,1,238,NULL,'ZZTRiTFMjGNi','BurlyWood','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(12,1,240,NULL,'mFTWZQUNW54','DarkOliveGreen','[60]',NULL,NULL,NULL,NULL,NULL,1,0),(13,1,232,NULL,'mNmw2YNUzYM5','DeepSkyBlue','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(14,1,171,NULL,'WmZhNmkMZDNhZ','CadetBlue','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(15,1,186,NULL,'DQ2jMjGkyYVMYG2M','DarkGreen','[10]',NULL,NULL,NULL,NULL,NULL,1,0),(16,1,246,NULL,'imiFMmYNYYATxZT','DimGrey','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(17,1,243,NULL,'zzY5whjGNjZM','Darkorange','[40]',NULL,NULL,NULL,NULL,NULL,1,0),(18,1,192,NULL,'jAMDyYT5jmMYMd','HoneyDew','[10]',NULL,NULL,NULL,NULL,NULL,1,0),(19,1,236,NULL,'R2zNEMdNTiT','CornflowerBlue','[30]',NULL,NULL,NULL,NULL,NULL,1,0),(20,1,210,NULL,'zkjx2NTYTgQND','Cornsilk','[1000]',NULL,NULL,NULL,NULL,NULL,1,0),(21,2,247,NULL,'YDkEFDEc4zWEDMj','SUPLAER Direct Link','[1000]',NULL,NULL,NULL,NULL,NULL,1,0);
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
INSERT INTO `supla_iodevice` VALUES (1,4,1,_binary '1368556','SONOFF-DS',1,NULL,'2019-12-27 10:30:30',5257721,'2019-12-27 10:30:30',NULL,'2.34',2,NULL,NULL,0,NULL,NULL),(2,5,1,_binary '4357143','UNI-MODULE',1,NULL,'2019-12-27 10:30:30',6667419,'2019-12-27 10:30:30',NULL,'2.24',2,NULL,NULL,0,NULL,NULL),(3,3,1,_binary '1108342','RGB-801',1,NULL,'2019-12-27 10:30:30',9444517,'2019-12-27 10:30:30',NULL,'2.21',2,NULL,NULL,0,NULL,NULL),(4,4,1,_binary '7298474','ALL-IN-ONE MEGA DEVICE',1,NULL,'2019-12-27 10:30:30',7355389,'2019-12-27 10:30:30',NULL,'2.24',2,NULL,NULL,0,NULL,NULL),(5,4,1,_binary '4959507','SECOND MEGA DEVICE',1,NULL,'2019-12-27 10:30:30',2762932,'2019-12-27 10:30:30',NULL,'2.41',2,NULL,NULL,0,NULL,NULL),(6,4,1,_binary '8804139','OH-MY-GATES. This device also has ridiculously long name!',1,NULL,'2019-12-27 10:30:31',9182217,'2019-12-27 10:30:31',NULL,'2.45',2,NULL,NULL,0,NULL,NULL),(7,5,1,_binary '6486554','VOLUPTAS-OPTIO',1,NULL,'2019-12-27 10:30:31',2187753,'2019-12-27 10:30:31',NULL,'2.6',2,NULL,NULL,0,NULL,NULL),(8,5,1,_binary '1297955','UT-REPUDIANDAE',1,NULL,'2019-12-27 10:30:31',7890045,'2019-12-27 10:30:31',NULL,'2.40',2,NULL,NULL,0,NULL,NULL),(9,5,1,_binary '9625148','OFFICIA-MAXIME',1,NULL,'2019-12-27 10:30:31',6497447,'2019-12-27 10:30:31',NULL,'2.28',2,NULL,NULL,0,NULL,NULL),(10,5,1,_binary '4266034','MODI-ET-CONSEQUATUR',1,NULL,'2019-12-27 10:30:31',8615481,'2019-12-27 10:30:31',NULL,'2.30',2,NULL,NULL,0,NULL,NULL),(11,5,1,_binary '7643724','FACILIS',1,NULL,'2019-12-27 10:30:31',4954697,'2019-12-27 10:30:31',NULL,'2.41',2,NULL,NULL,0,NULL,NULL),(12,5,1,_binary '1026279','LABORUM-TEMPORA-ESSE',1,NULL,'2019-12-27 10:30:31',4259120,'2019-12-27 10:30:31',NULL,'2.9',2,NULL,NULL,0,NULL,NULL),(13,5,1,_binary '2072463','IPSUM-ODIT',1,NULL,'2019-12-27 10:30:32',4484559,'2019-12-27 10:30:32',NULL,'2.23',2,NULL,NULL,0,NULL,NULL),(14,5,1,_binary '9723489','COMMODI',1,NULL,'2019-12-27 10:30:32',9320001,'2019-12-27 10:30:32',NULL,'2.35',2,NULL,NULL,0,NULL,NULL),(15,5,1,_binary '608065','PROVIDENT-DISTINCTIO',1,NULL,'2019-12-27 10:30:32',3787838,'2019-12-27 10:30:32',NULL,'2.38',2,NULL,NULL,0,NULL,NULL),(16,5,1,_binary '4258630','NON-ASSUMENDA-DOLORES',1,NULL,'2019-12-27 10:30:32',4707413,'2019-12-27 10:30:32',NULL,'2.10',2,NULL,NULL,0,NULL,NULL),(17,5,1,_binary '3607703','IN-EA',1,NULL,'2019-12-27 10:30:32',5451184,'2019-12-27 10:30:32',NULL,'2.6',2,NULL,NULL,0,NULL,NULL),(18,5,1,_binary '2732063','NON-EXERCITATIONEM',1,NULL,'2019-12-27 10:30:32',9388168,'2019-12-27 10:30:32',NULL,'2.8',2,NULL,NULL,0,NULL,NULL),(19,5,1,_binary '107124','IPSUM-ANIMI-RATIONE',1,NULL,'2019-12-27 10:30:32',3199368,'2019-12-27 10:30:32',NULL,'2.44',2,NULL,NULL,0,NULL,NULL),(20,5,1,_binary '4121081','IUSTO-EX',1,NULL,'2019-12-27 10:30:32',9267192,'2019-12-27 10:30:32',NULL,'2.15',2,NULL,NULL,0,NULL,NULL),(21,5,1,_binary '3392332','QUI-VOLUPTATIBUS',1,NULL,'2019-12-27 10:30:33',3071772,'2019-12-27 10:30:33',NULL,'2.42',2,NULL,NULL,0,NULL,NULL),(22,6,2,_binary '5147151','SUPLER MEGA DEVICE',1,NULL,'2019-12-27 10:30:33',6666297,'2019-12-27 10:30:33',NULL,'2.49',2,NULL,NULL,0,NULL,NULL);
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
INSERT INTO `supla_location` VALUES (1,1,'f648','Location #1',1),(2,2,'69fc','Location #1',1),(3,1,'d966','Sypialnia',1),(4,1,'2a4d','Na zewnątrz',1),(5,1,'97c8','Garaż',1),(6,2,'937e','Supler\'s location',1);
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
INSERT INTO `supla_oauth_access_tokens` VALUES (1,1,1,'0123456789012345678901234567890123456789',2051218800,'offline_access channels_ea channelgroups_ea channels_files accessids_r accessids_rw account_r account_rw channels_r channels_rw channelgroups_r channelgroups_rw clientapps_r clientapps_rw directlinks_r directlinks_rw iodevices_r iodevices_rw locations_r locations_rw schedules_r schedules_rw',NULL,NULL),(2,1,1,'YTFhNDI1M2FiNDZiYmUxYjlkMzZlNDZjNTRhYjc1NGM5OTM4YTY3OTcxOTcwNjk0NTgzM2E4MjFjNTMyNWZjNw.aHR0cDovL3N1cGxhLmxvY2Fs',1577444023,'channels_ea channelgroups_ea channels_files accessids_r accessids_rw account_r account_rw channels_r channels_rw channelgroups_r channelgroups_rw clientapps_r clientapps_rw directlinks_r directlinks_rw iodevices_r iodevices_rw locations_r locations_rw schedules_r schedules_rw',NULL,NULL),(3,1,1,'ZDE1ZjQ0ZjU3ZDE5NDY3ZWZjMTUxMDdiOTQ1ZTRmODZjZGMwMWI0OTUwZjNkMzRmZjlkNzVhNjA0NTQ2NzU2Mg.aHR0cDovL3N1cGxhLmxvY2Fs',1577444023,'channels_files',NULL,NULL);
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
INSERT INTO `supla_oauth_clients` VALUES (1,'5focjtd5adgk8s0o0owwso4cg8o0sssco8k0g0cs4480g8occk','a:0:{}','zrwq58ohe80w4g4kogc08ocw40ww8s0gkcog48s0sc0k0ww4w','a:2:{i:0;s:8:\"password\";i:1;s:13:\"refresh_token\";}',1,NULL,NULL,NULL,NULL,NULL);
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
INSERT INTO `supla_rel_cg` VALUES (1,1),(142,4),(142,6),(143,8),(144,3),(144,9),(149,4),(149,5),(149,6),(151,3),(151,9),(156,4),(156,6),(156,10),(157,8),(158,3),(158,9),(159,11),(163,4),(163,5),(164,7),(164,8),(165,3),(165,9),(170,4),(170,5),(170,6),(170,10),(171,8),(172,3),(172,9),(177,4),(177,6),(177,10),(178,8),(179,3),(184,6),(185,2),(185,8),(186,3),(187,11),(191,4),(191,6),(192,8),(193,3),(193,9),(198,4),(198,5),(198,10),(199,8),(200,3),(200,9),(205,4),(205,5),(205,6),(205,10),(206,7),(206,8),(207,3),(207,9),(212,4),(212,6),(214,3),(219,4),(219,6),(221,3),(221,9),(226,5),(226,6),(226,10),(228,3),(233,4),(233,5),(233,6),(235,3),(235,9),(236,11),(240,1),(240,4),(240,10),(241,8),(242,3),(242,9),(243,11);
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
INSERT INTO `supla_schedule` VALUES (1,1,192,'SS0 * * * *',10,NULL,'daily','2020-01-01 18:58:33',NULL,1,'2019-12-31 15:05:00','Fugiat voluptatem aut.',1,NULL),(2,1,177,'*/90 * * * *',70,NULL,'minutely','2019-12-30 23:50:35',NULL,1,'2019-12-29 01:20:00','Et ipsum.',1,NULL),(3,1,159,'*/15 * * * *',50,NULL,'minutely','2019-12-30 10:05:32',NULL,1,'2019-12-28 10:20:00','Autem eum ipsum natus explicabo.',1,NULL),(4,1,143,'SR0 * * * *',10,NULL,'daily','2019-12-30 02:01:04',NULL,1,'2019-12-28 07:15:00','Officiis adipisci.',1,NULL),(5,1,221,'*/15 * * * *',90,NULL,'minutely','2020-01-01 23:02:04',NULL,1,'2019-12-30 23:15:00','Sed aut animi nemo.',1,NULL),(6,1,179,'*/90 * * * *',20,NULL,'minutely','2019-12-29 21:19:14',NULL,1,'2019-12-27 22:50:00','Iusto placeat eaque laudantium accusamus et.',1,NULL),(7,1,166,'SS-10 * * * *',50,NULL,'daily','2019-12-27 20:44:01',NULL,1,'2019-12-27 14:50:00','Illo tenetur facilis.',1,NULL),(8,1,240,'*/15 * * * *',110,NULL,'minutely','2020-01-01 02:57:18',NULL,1,'2019-12-30 03:10:00','Optio totam ea qui id.',1,NULL),(9,1,243,'SR-10 * * * *',40,NULL,'daily','2019-12-30 13:43:00',NULL,1,'2019-12-29 07:05:00','Rerum aliquid.',1,NULL),(10,1,194,'SS0 * * * *',30,NULL,'daily','2020-01-01 10:25:05',NULL,1,'2019-12-30 15:05:00','Et perspiciatis.',1,NULL),(11,1,163,'*/60 * * * *',60,NULL,'minutely','2019-12-29 15:16:03',NULL,1,'2019-12-27 16:15:00','Ipsa suscipit et.',1,NULL),(12,1,214,'SR0 * * * *',10,NULL,'daily','2020-01-03 00:03:18',NULL,1,'2020-01-01 07:15:00','Rerum rem eos.',1,NULL),(13,1,206,'SS10 * * * *',10,NULL,'daily','2020-01-02 12:51:13',NULL,1,'2019-12-31 15:15:00','Aliquam nam consequuntur maxime nostrum.',1,NULL),(14,1,241,'SS10 * * * *',10,NULL,'daily','2019-12-29 02:47:42',NULL,1,'2019-12-27 15:10:00','Cum omnis.',1,NULL),(15,1,242,'*/30 * * * *',90,NULL,'minutely','2020-01-03 00:06:36',NULL,1,'2020-01-01 00:35:00','Ullam id praesentium dolor.',1,NULL),(16,1,213,'*/60 * * * *',10,NULL,'minutely','2019-12-31 11:58:16',NULL,1,'2019-12-29 13:00:00','Dicta eos nam deleniti.',1,NULL),(17,1,198,'SS10 * * * *',110,NULL,'daily','2019-12-29 22:57:16',NULL,1,'2019-12-28 15:10:00','A qui eos.',1,NULL),(18,1,171,'*/30 * * * *',10,NULL,'minutely','2019-12-31 01:36:12',NULL,1,'2019-12-29 02:05:00','Quia ducimus laboriosam.',1,NULL),(19,1,235,'SR-10 * * * *',10,NULL,'daily','2019-12-28 22:49:11',NULL,1,'2019-12-28 07:05:00','Pariatur officiis.',1,NULL),(20,1,151,'*/60 * * * *',90,NULL,'minutely','2019-12-30 10:24:37',NULL,1,'2019-12-28 11:25:00','Molestias porro reprehenderit.',1,NULL);
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
  PRIMARY KEY (`id`),
  KEY `IDX_FB21DBDCA40BC2D5` (`schedule_id`),
  KEY `result_idx` (`result`),
  KEY `result_timestamp_idx` (`result_timestamp`),
  KEY `planned_timestamp_idx` (`planned_timestamp`),
  KEY `retry_timestamp_idx` (`retry_timestamp`),
  KEY `fetched_timestamp_idx` (`fetched_timestamp`),
  KEY `consumed_idx` (`consumed`),
  CONSTRAINT `FK_FB21DBDCA40BC2D5` FOREIGN KEY (`schedule_id`) REFERENCES `supla_schedule` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supla_scheduled_executions`
--

LOCK TABLES `supla_scheduled_executions` WRITE;
/*!40000 ALTER TABLE `supla_scheduled_executions` DISABLE KEYS */;
INSERT INTO `supla_scheduled_executions` VALUES (1,1,'2020-01-02 15:05:00',NULL,NULL,NULL,NULL,0,NULL),(2,2,'2019-12-31 01:20:00',NULL,NULL,NULL,NULL,0,NULL),(3,3,'2019-12-30 10:20:00',NULL,NULL,NULL,NULL,0,NULL),(4,4,'2019-12-30 07:15:00',NULL,NULL,NULL,NULL,0,NULL),(5,5,'2020-01-01 23:15:00',NULL,NULL,NULL,NULL,0,NULL),(6,6,'2019-12-29 22:50:00',NULL,NULL,NULL,NULL,0,NULL),(7,7,'2019-12-28 14:50:00',NULL,NULL,NULL,NULL,0,NULL),(8,7,'2019-12-29 14:50:00',NULL,NULL,NULL,NULL,0,NULL),(9,8,'2020-01-01 03:10:00',NULL,NULL,NULL,NULL,0,NULL),(10,9,'2019-12-31 07:05:00',NULL,NULL,NULL,NULL,0,NULL),(11,10,'2020-01-01 15:05:00',NULL,NULL,NULL,NULL,0,NULL),(12,11,'2019-12-29 16:15:00',NULL,NULL,NULL,NULL,0,NULL),(13,12,'2020-01-03 07:15:00',NULL,NULL,NULL,NULL,0,NULL),(14,13,'2020-01-02 15:15:00',NULL,NULL,NULL,NULL,0,NULL),(15,14,'2019-12-29 15:10:00',NULL,NULL,NULL,NULL,0,NULL),(16,15,'2020-01-03 00:35:00',NULL,NULL,NULL,NULL,0,NULL),(17,16,'2019-12-31 13:00:00',NULL,NULL,NULL,NULL,0,NULL),(18,17,'2019-12-30 15:10:00',NULL,NULL,NULL,NULL,0,NULL),(19,18,'2019-12-31 02:05:00',NULL,NULL,NULL,NULL,0,NULL),(20,19,'2019-12-29 07:05:00',NULL,NULL,NULL,NULL,0,NULL),(21,19,'2019-12-30 07:05:00',NULL,NULL,NULL,NULL,0,NULL),(22,20,'2019-12-30 11:25:00',NULL,NULL,NULL,NULL,0,NULL);
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
INSERT INTO `supla_user` VALUES (1,'abeb20d86ed19df9de20ff32a39733a4','e149fd72653dfa91b44058838ffb208a63c43919c0b72e9a0c963ae94f53e1f18e3e4fcd3799ee3536af1348870fc100f7dd1bfb6bd67361f7f002b1316e3cd658359b5ec5bc7d926bec6df92fb7961ab545ab00e53385d57fbd6d22964467480578f8c3','7o5caysf0jk04g0skgwcg4swwkos4s4','user@supla.org','$2y$13$nZqdKyfNebDNL2ZTo0oAhOTrdumNgryJVFxRKqztVbqVJ1BAKQv7W',1,'2019-12-27 10:30:28',NULL,NULL,10,10,100,200,'Europe/Berlin',20,NULL,'2020-01-03 10:30:29','2020-01-03 10:30:29',20,10,1,1,NULL,NULL,50,20,'en',NULL),(2,'361fe549d3f11f2274997c2419417e08','5458bb95a69785c89df3d1c85f319c35b60103c7e030ddefc84f945068c0ad009fc104f0409d0a4cd67c79b6964aa6c2896f91f4bf6045811e1542240195f0e494acbeeb6204e14f5afec97acaf30ab5e18e6ddbe0d7fa413d679c57d1d9c9d132da74b5','c7c9wqbame8gg40c8wkwswwgo4csgsw','supler@supla.org','$2y$13$ztf.Juo2Fa6j9TIUXo9qZuxvLPfNKLm7NZsNmhIW3aI.AaQTw1Sjy',1,'2019-12-27 10:30:29',NULL,NULL,10,10,100,200,'Europe/Berlin',20,NULL,'2020-01-03 10:30:30','2020-01-03 10:30:30',20,10,1,1,NULL,NULL,50,20,'',NULL);
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
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_client`(IN `_access_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8,
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
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_em_log_item`(IN `_channel_id` INT(11), IN `_phase1_fae` BIGINT, IN `_phase1_rae` BIGINT,
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
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_iodevice`(IN `_location_id` INT(11), IN `_user_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8, IN `_reg_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(10), IN `_protocol_version` INT(11), IN `_product_id` SMALLINT, IN `_manufacturer_id` SMALLINT, IN `_original_location_id` INT(11), IN `_auth_key` VARCHAR(64), IN `_flags` INT(11), OUT `_id` INT(11))
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
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
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
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_add_thermostat_log_item`(IN `_channel_id` INT(11), IN `_measured_temperature` DECIMAL(5,2), IN `_preset_temperature` DECIMAL(5,2))
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
CREATE DEFINER=`root`@`%` PROCEDURE `supla_get_device_firmware_url`(IN `device_id` INT, IN `platform` TINYINT, IN `fparam1` INT, IN `fparam2` INT, IN `fparam3` INT, IN `fparam4` INT, OUT `protocols` TINYINT, OUT `host` VARCHAR(100), OUT `port` INT, OUT `path` VARCHAR(100))
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
CREATE DEFINER=`root`@`%` PROCEDURE `supla_oauth_add_token_for_app`(IN `_user_id` INT(11), IN `_token` VARCHAR(255) CHARSET utf8,
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
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
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
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
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
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_amazon_alexa`(IN `_access_token` VARCHAR(1024) CHARSET utf8, IN `_refresh_token` VARCHAR(1024) CHARSET utf8, IN `_expires_in` INT, IN `_user_id` INT)
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
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_client`(IN `_access_id` INT(11), IN `_name` VARCHAR(100) CHARSET utf8,
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
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `supla_update_iodevice`(IN `_name` VARCHAR(100) CHARSET utf8, IN `_last_ipv4` INT(10) UNSIGNED,
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
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

-- Dump completed on 2019-12-27 11:43:15
