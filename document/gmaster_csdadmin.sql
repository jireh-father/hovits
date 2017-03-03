CREATE DATABASE  IF NOT EXISTS `gmaster` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `gmaster`;
-- MySQL dump 10.13  Distrib 5.6.13, for Win32 (x86)
--
-- Host: 192.168.228.139    Database: gmaster
-- ------------------------------------------------------
-- Server version	5.5.34

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
-- Table structure for table `gearman_server`
--

DROP TABLE IF EXISTS `gearman_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gearman_server` (
  `server_id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `port` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `is_master` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `gs_is_disabled` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `php_path` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `cli_ini_path` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gearman_dir` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gearman_command` text COLLATE utf8_unicode_ci,
  `gs_memo` text COLLATE utf8_unicode_ci,
  `gs_insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`server_id`),
  UNIQUE KEY `uq_gearman_server_host_port` (`host`,`port`),
  KEY `idx_gearman_server_is_disabled` (`gs_is_disabled`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gearman_server`
--

LOCK TABLES `gearman_server` WRITE;
/*!40000 ALTER TABLE `gearman_server` DISABLE KEYS */;
INSERT INTO `gearman_server` VALUES (9,'192.168.228.139','4730','1','0','/opt/lampstack-5.3.28-0/php/bin/php','/opt/lampstack-5.3.28-0/php/etc/php.ini','/home/apps/gearmand','/home/apps/gearmand/sbin/gearmand -d -l /home/apps/gearmand/logs/gearmand.log','','2015-02-25 07:14:35'),(10,'192.168.228.137','4730','0','1','/opt/lampstack-5.3.29-0/php/bin/php','/opt/lampstack-5.3.29-0/php/etc/php.ini','/home/apps/gearmand','/home/apps/gearmand/sbin/gearmand -d -l /home/apps/gearmand/logs/gearmand.log','','2015-02-25 07:15:11'),(11,'test12341','4730','0','0','/home/apps/php/bin/php','/home/apps/php/lib/cli.ini','/home/apps/gearmand','/home/apps/gearmand/sbin/gearmand -d -l /home/apps/gearmand/logs/gearmand.log','','2015-04-16 09:22:50'),(12,'ㅗ몰ㅇㅁㅎㅎ','4730','0','0','/home/apps/php/bin/php','/home/apps/php/lib/cli.ini','/home/apps/gearmand','/home/apps/gearmand/sbin/gearmand -d -l /home/apps/gearmand/logs/gearmand.log','','2015-04-16 09:29:14');
/*!40000 ALTER TABLE `gearman_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_schedule`
--

DROP TABLE IF EXISTS `job_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_schedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `job_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `job_type` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_command` text COLLATE utf8_unicode_ci,
  `option_data` text COLLATE utf8_unicode_ci,
  `minute` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `minute_type` enum('all','one','list','range','interval','rangeinterval') COLLATE utf8_unicode_ci NOT NULL,
  `hour` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hour_type` enum('all','one','list','range','interval','rangeinterval') COLLATE utf8_unicode_ci NOT NULL,
  `day_of_month` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `day_of_month_type` enum('all','one','list','range','interval','rangeinterval') COLLATE utf8_unicode_ci NOT NULL,
  `month` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `month_type` enum('all','one','list','range','interval','rangeinterval') COLLATE utf8_unicode_ci NOT NULL,
  `day_of_week` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `day_of_week_type` enum('all','one','list','range','interval','rangeinterval') COLLATE utf8_unicode_ci NOT NULL,
  `js_is_disabled` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `js_memo` text COLLATE utf8_unicode_ci,
  `js_insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `job_bootstrap_path` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`schedule_id`),
  UNIQUE KEY `uq_scheduler_server_id_worker_id` (`worker_id`,`server_id`),
  KEY `fk_scheduler_server_id_idx` (`server_id`),
  KEY `fk_scheduler_worker_id_idx` (`worker_id`),
  KEY `idx_scheduler_is_disabled` (`js_is_disabled`),
  CONSTRAINT `fk_job_schedule_server_id` FOREIGN KEY (`server_id`) REFERENCES `gearman_server` (`server_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_job_schedule_worker_id` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`worker_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_schedule`
--

LOCK TABLES `job_schedule` WRITE;
/*!40000 ALTER TABLE `job_schedule` DISABLE KEYS */;
INSERT INTO `job_schedule` VALUES (53,9,28,'test','','',NULL,'4-40/11','rangeinterval','*/2','interval','*','all','*','all','*','all','1','','2015-02-25 07:50:16',''),(54,10,28,'test','','',NULL,'5,11,15,30,45,51','list','*/3','interval','*','all','*','all','*','all','1','','2015-02-25 07:50:16',''),(55,10,NULL,'해외마켓 주문수집','static_method','workerCrawlerOrderForeign::runCrawlers','{\"market_list\":\"tmall\"}','*','all','*','all','*','all','*','all','*','all','1','','2015-03-02 09:29:06','');
/*!40000 ALTER TABLE `job_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `worker`
--

DROP TABLE IF EXISTS `worker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worker` (
  `worker_id` int(11) NOT NULL AUTO_INCREMENT,
  `worker_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `worker_type` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `worker_path` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `worker_class` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `worker_function` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `w_memo` text COLLATE utf8_unicode_ci,
  `w_is_disabled` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `default_option_data` text COLLATE utf8_unicode_ci,
  `w_insert_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`worker_id`),
  UNIQUE KEY `uq_worker_worker_name` (`worker_name`),
  KEY `idx_worker_is_disabled` (`w_is_disabled`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `worker`
--

LOCK TABLES `worker` WRITE;
/*!40000 ALTER TABLE `worker` DISABLE KEYS */;
INSERT INTO `worker` VALUES (24,'cli_runner:192.168.228.136:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-02-25 07:14:35'),(25,'cli_runner_sync:192.168.228.136:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-02-25 07:14:35'),(26,'cli_runner:192.168.228.137:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-02-25 07:15:11'),(27,'cli_runner_sync:192.168.228.137:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-02-25 07:15:11'),(28,'testWorker','static_method','','libWorker','testWorker','','0',NULL,'2015-02-25 07:27:46'),(29,'foreign_order_crawling_by_market','static_method','','workerCrawlerOrderForeign','crawlingByMarket','마켓별 해외마켓 주문수집 잡','0','{\"asdf\":\"f\",\"fe\":\"221\",\"f\":\"fdasf\"}','2015-03-02 09:24:30'),(30,'tmall_order_crawling_by_mall','object_method','','workerCrawlerOrderForeignTmallMall','crawlingByMall','티몰 몰별 주문수집 잡','0',NULL,'2015-03-02 09:25:34'),(31,'tmall_order_crawling_by_page','object_method','','workerCrawlerOrderForeignTmallPage','crawlingByPage','티몰 페이지별 주문수집 잡','0',NULL,'2015-03-02 09:26:28'),(32,'cli_runner:localhost:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-03-03 05:53:33'),(33,'cli_runner_sync:localhost:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-03-03 05:53:33'),(34,'cli_runner:test12341:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-04-16 09:22:50'),(35,'cli_runner_sync:test12341:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-04-16 09:22:50'),(36,'cli_runner:ㅗ몰ㅇㅁㅎㅎ:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-04-16 09:29:14'),(37,'cli_runner_sync:ㅗ몰ㅇㅁㅎㅎ:4730','static_method',NULL,'GearmanSystem','executeCli',NULL,'0',NULL,'2015-04-16 09:29:14');
/*!40000 ALTER TABLE `worker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `worker_link`
--

DROP TABLE IF EXISTS `worker_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worker_link` (
  `worker_link_id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `worker_count` int(11) NOT NULL DEFAULT '1',
  `bootstrap_path` text COLLATE utf8_unicode_ci,
  `wl_is_disabled` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `option_data` text COLLATE utf8_unicode_ci,
  `wl_memo` text COLLATE utf8_unicode_ci,
  `wl_insert_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`worker_link_id`),
  UNIQUE KEY `uq_worker_link_server_id_worker_id` (`server_id`,`worker_id`),
  KEY `fk_worker_link_server_id_idx` (`server_id`),
  KEY `fk_worker_link_worker_id_idx` (`worker_id`),
  KEY `idx_worker_link_is_disabled` (`wl_is_disabled`),
  CONSTRAINT `fk_server_worker_link_server_id` FOREIGN KEY (`server_id`) REFERENCES `gearman_server` (`server_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_server_worker_link_worker_id` FOREIGN KEY (`worker_id`) REFERENCES `worker` (`worker_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `worker_link`
--

LOCK TABLES `worker_link` WRITE;
/*!40000 ALTER TABLE `worker_link` DISABLE KEYS */;
INSERT INTO `worker_link` VALUES (44,9,24,1,NULL,'0',NULL,NULL,'2015-02-25 07:14:35'),(45,9,25,3,NULL,'0',NULL,NULL,'2015-02-25 07:14:35'),(48,10,26,1,NULL,'0',NULL,NULL,'2015-02-25 07:15:11'),(49,10,27,3,NULL,'0',NULL,NULL,'2015-02-25 07:15:11'),(72,9,28,1,'','0',NULL,'','2015-03-02 05:10:39'),(73,10,29,1,'','0','{\"market_code\":\"tmall\"}','','2015-03-02 09:26:39'),(74,10,30,1,'','0',NULL,'','2015-03-02 09:26:50'),(75,10,31,5,'','0',NULL,'','2015-03-02 09:26:55'),(76,11,34,1,NULL,'0',NULL,NULL,'2015-04-16 09:22:50'),(77,11,35,3,NULL,'0',NULL,NULL,'2015-04-16 09:22:50'),(78,12,36,1,NULL,'0',NULL,NULL,'2015-04-16 09:29:14'),(79,12,37,3,NULL,'0',NULL,NULL,'2015-04-16 09:29:14');
/*!40000 ALTER TABLE `worker_link` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-04-18 12:07:39
CREATE DATABASE  IF NOT EXISTS `csd_admin` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `csd_admin`;
-- MySQL dump 10.13  Distrib 5.6.13, for Win32 (x86)
--
-- Host: 192.168.228.139    Database: csd_admin
-- ------------------------------------------------------
-- Server version	5.5.34

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
-- Table structure for table `common_log`
--

DROP TABLE IF EXISTS `common_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `common_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `trace_id` int(11) DEFAULT NULL,
  `host` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `project_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `log_level` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `log_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `log_msg` text COLLATE utf8_unicode_ci NOT NULL,
  `log_data` longtext COLLATE utf8_unicode_ci,
  `index_key` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `index_value` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pid` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_cl_project_name` (`project_name`),
  KEY `idx_cl_log_level` (`log_level`),
  KEY `idx_cl_log_type` (`log_type`),
  KEY `idx_cl_index_key` (`index_key`),
  KEY `idx_cl_index_value` (`index_value`),
  KEY `fk_cl_trace_id_idx` (`trace_id`),
  KEY `idx_cl_host` (`host`),
  CONSTRAINT `fk_cl_trace_id` FOREIGN KEY (`trace_id`) REFERENCES `common_log` (`log_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `common_log`
--

LOCK TABLES `common_log` WRITE;
/*!40000 ALTER TABLE `common_log` DISABLE KEYS */;
INSERT INTO `common_log` VALUES (27,NULL,'서일근PC','Gmaster','debug','contApi/afterInit','Gmaster Api 시작','{\"project_name\":\"test\",\"log_level\":\"error\",\"log_type\":\"haha\",\"log_msg\":\"testmsg\",\"d\":\"\"}',NULL,NULL,'2015-03-18 03:16:21','3068'),(28,NULL,'127.0.0.1','test','error','haha','testmsg',NULL,NULL,NULL,'2015-03-18 03:16:21',NULL),(29,NULL,'서일근PC','Gmaster','debug','contApi/afterInit','Gmaster Api 시작','{\"project_name\":\"test\",\"log_level\":\"error\",\"log_type\":\"haha\",\"log_msg\":\"testmsg\",\"d\":\"\"}',NULL,NULL,'2015-03-18 03:16:53','3068'),(30,NULL,'127.0.0.1','test','error','haha','testmsg',NULL,NULL,NULL,'2015-03-18 03:16:53',NULL),(31,NULL,'서일근PC','Gmaster','debug','contApi/afterInit','Gmaster Api 시작','{\"project_name\":\"test\",\"log_level\":\"error\",\"log_type\":\"haha\",\"log_msg\":\"testmsg\",\"d\":\"\"}',NULL,NULL,'2015-03-18 03:17:31','3068'),(32,NULL,'127.0.0.1','test','error','haha','testmsg',NULL,NULL,NULL,'2015-03-18 03:17:31',NULL),(33,NULL,'서일근PC','Gmaster','debug','contApi/afterInit','Gmaster Api 시작','{\"project_name\":\"test\",\"log_level\":\"error\",\"log_type\":\"haha\",\"log_msg\":\"testmsg\"}',NULL,NULL,'2015-03-18 03:17:50','3068'),(34,NULL,'127.0.0.1','test','error','haha','testmsg',NULL,NULL,NULL,'2015-03-18 03:17:50',NULL),(35,NULL,'127.0.0.1','test','error','haha','testmsg',NULL,NULL,NULL,'2015-03-18 03:18:57',NULL),(36,NULL,'127.0.0.1','test','error','haha','testmsg',NULL,NULL,NULL,'2015-03-18 03:19:00',NULL),(37,NULL,'127.0.0.1','test','error','haha','testmsg',NULL,NULL,NULL,'2015-03-18 03:19:00',NULL),(38,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"\"],\"option_value\":[\"\"]}',NULL,NULL,'2015-04-01 08:14:36','2872'),(39,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"asdf\"],\"option_value\":[\"ff\"]}',NULL,NULL,'2015-04-01 08:14:45','2872'),(40,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\"}',NULL,NULL,'2015-04-01 08:15:01','2872'),(41,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdfasdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"\"],\"option_value\":[\"\"]}',NULL,NULL,'2015-04-01 08:15:06','2872'),(42,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test3\",\"groupware_id_list\":\"igseo\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"haha\",\"option_key\":[\"weaef\",\"asdg\",\"greag\"],\"option_value\":[\"aff\",\"gdfgfa\",\"grgrg\"]}',NULL,NULL,'2015-04-01 08:21:14','2872'),(43,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdfasdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"asdfsadf\",\"option_key\":[\"asdf\",\"efef\"],\"option_value\":[\"asdf\",\"efe\"]}',NULL,NULL,'2015-04-01 08:21:39','2872'),(44,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdfasdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"asdfsadf\",\"option_key\":[\"asdf\",\"efef\"],\"option_value\":[\"asdf\",\"efe\"]}',NULL,NULL,'2015-04-01 08:23:14','2872'),(45,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdfasdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"asdfsadf\",\"option_key\":[\"asdf\",\"efef\"],\"option_value\":[\"asdf\",\"efe\"]}',NULL,NULL,'2015-04-01 08:23:27','2872'),(46,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdfasdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"asdfsadf\",\"option_key\":[\"asdf\",\"efef\"],\"option_value\":[\"asdf\",\"efe\"]}',NULL,NULL,'2015-04-01 08:23:39','2872'),(47,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"7\",\"action_name\":\"setNotifier\",\"notifier_name\":\"asdfasdf\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"asdfsadf\",\"option_key\":[\"asdf\"],\"option_value\":[\"asdf\"]}',NULL,NULL,'2015-04-01 08:23:50','2872'),(48,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test333\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"project_name\",\"log_type\",\"index_key\"],\"option_value\":[\"test\",\"fefe\",\"fe\"]}',NULL,NULL,'2015-04-01 08:28:32','2872'),(49,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: deleteNotifier','{\"action_name\":\"deleteNotifier\",\"notifier_id\":\"7\"}',NULL,NULL,'2015-04-01 08:29:11','2872'),(50,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"8\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test333\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"log_type\",\"log_type\",\"index_key\"],\"option_value\":[\"test\",\"fefe\",\"fe\"]}',NULL,NULL,'2015-04-01 08:29:19','2872'),(51,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"8\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test333\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"log_level\",\"index_key\"],\"option_value\":[\"fefe\",\"fe\"]}',NULL,NULL,'2015-04-01 08:31:22','2872'),(52,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test4444\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"33\",\"option_key\":[\"host\",\"host\",\"host\",\"log_level\"],\"option_value\":[\"fsdfs\",\"ggg\",\"hhha\",\"rga3\"]}',NULL,NULL,'2015-04-01 08:43:27','2872'),(53,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"9\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test4444\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"33\",\"option_key\":[\"host\",\"log_level\",\"host\",\"log_level\"],\"option_value\":[\"fsdfs\",\"ggg\",\"hhha\",\"rga3\"]}',NULL,NULL,'2015-04-01 08:44:11','2872'),(54,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"9\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test4444\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"33\",\"option_key\":[\"host\",\"host\",\"host\",\"log_level\"],\"option_value\":[\"fsdfs\",\"hhha\",\"ggg\",\"rga3\"]}',NULL,NULL,'2015-04-01 08:44:19','2872'),(55,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"9\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test4444\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"33\",\"option_key\":[\"host\",\"host\",\"log_level\"],\"option_value\":[\"fsdfs\",\"ggg\",\"rga3\"]}',NULL,NULL,'2015-04-01 08:44:29','2872'),(56,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"9\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test4444\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"host\",\"host\",\"log_level\"],\"option_value\":[\"fsdfs\",\"ggg\",\"rga3\"]}',NULL,NULL,'2015-04-01 08:44:39','2872'),(57,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"9\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test4444\",\"groupware_id_list\":\"\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"33\",\"option_key\":[\"host\",\"host\",\"log_level\"],\"option_value\":[\"fsdfs\",\"ggg\",\"\"]}',NULL,NULL,'2015-04-01 08:44:43','2872'),(58,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setWorker','{\"worker_id\":\"29\",\"action_name\":\"setWorker\",\"worker_name\":\"foreign_order_crawling_by_market\",\"worker_type\":\"static_method\",\"worker_path\":\"\",\"worker_class\":\"workerCrawlerOrderForeign\",\"worker_function\":\"crawlingByMarket\",\"w_memo\":\"\\ub9c8\\ucf13\\ubcc4 \\ud574\\uc678\\ub9c8\\ucf13 \\uc8fc\\ubb38\\uc218\\uc9d1 \\uc7a1\",\"option_key\":[\"asdf\",\"fe\",\"f\"],\"option_value\":[\"f\",\"221\",\"fdasf\"]}',NULL,NULL,'2015-04-01 08:45:33','2872'),(59,58,'서일근PC','Gmaster','debug','contGmasterAction/restartWorkers','워커 재시작 시작','[[\"29\"],[\"10\"],[{\"worker_id\":\"29\",\"worker_name\":\"foreign_order_crawling_by_market\",\"worker_type\":\"static_method\",\"worker_path\":\"\",\"worker_class\":\"workerCrawlerOrderForeign\",\"worker_function\":\"crawlingByMarket\",\"w_memo\":\"\\ub9c8\\ucf13\\ubcc4 \\ud574\\uc678\\ub9c8\\ucf13 \\uc8fc\\ubb38\\uc218\\uc9d1 \\uc7a1\",\"w_is_disabled\":\"0\",\"default_option_data\":null,\"w_insert_time\":\"2015-03-02 18:24:30\"}]]',NULL,NULL,'2015-04-01 08:45:33','2872'),(60,58,'서일근PC','Gmaster','debug','GearmanProxy/callMethod','원격 메소드 호출 시작(proxy)','{\"host\":\"192.168.228.137\",\"port\":\"4730\",\"timeout\":10000,\"is_async\":true,\"command\":\"GearmanSystem::restartWorkersSeparately\",\"command_type\":\"static_method\",\"trace_id\":\"58\",\"params\":{\"original\":{\"php_path\":\"\\/opt\\/lampstack-5.3.29-0\\/php\\/bin\\/php\",\"cli_ini_path\":\"\\/opt\\/lampstack-5.3.29-0\\/php\\/etc\\/php.ini\",\"workers\":[{\"worker_id\":\"29\",\"worker_name\":\"foreign_order_crawling_by_market\",\"worker_type\":\"static_method\",\"worker_path\":\"\",\"worker_class\":\"workerCrawlerOrderForeign\",\"worker_function\":\"crawlingByMarket\",\"w_memo\":\"\\ub9c8\\ucf13\\ubcc4 \\ud574\\uc678\\ub9c8\\ucf13 \\uc8fc\\ubb38\\uc218\\uc9d1 \\uc7a1\",\"w_is_disabled\":\"0\",\"default_option_data\":null,\"w_insert_time\":\"2015-03-02 18:24:30\"}]},\"new\":{\"php_path\":\"\\/opt\\/lampstack-5.3.29-0\\/php\\/bin\\/php\",\"cli_ini_path\":\"\\/opt\\/lampstack-5.3.29-0\\/php\\/etc\\/php.ini\",\"workers\":[{\"worker_id\":\"29\",\"worker_name\":\"foreign_order_crawling_by_market\",\"worker_type\":\"static_method\",\"worker_path\":\"\",\"worker_class\":\"workerCrawlerOrderForeign\",\"worker_function\":\"crawlingByMarket\",\"w_memo\":\"\\ub9c8\\ucf13\\ubcc4 \\ud574\\uc678\\ub9c8\\ucf13 \\uc8fc\\ubb38\\uc218\\uc9d1 \\uc7a1\",\"w_is_disabled\":\"0\",\"default_option_data\":\"{\\\"asdf\\\":\\\"f\\\",\\\"fe\\\":\\\"221\\\",\\\"f\\\":\\\"fdasf\\\"}\",\"w_insert_time\":\"2015-03-02 18:24:30\",\"worker_link_id\":\"73\",\"server_id\":\"10\",\"worker_count\":\"1\",\"bootstrap_path\":\"\",\"wl_is_disabled\":\"0\",\"option_data\":\"{\\\"market_code\\\":\\\"tmall\\\"}\",\"wl_memo\":\"\",\"wl_insert_time\":\"2015-03-02 18:26:39\"}]}}}',NULL,NULL,'2015-04-01 08:45:33','2872'),(61,58,'서일근PC','Gmaster','fatal','CurlUtil/post','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\\/method\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-01 08:46:12','2872'),(62,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setSchedule','{\"schedule_id\":\"55\",\"action_name\":\"setSchedule\",\"minute_type\":\"all\",\"hour_type\":\"all\",\"day_of_month_type\":\"all\",\"month_type\":\"all\",\"day_of_week_type\":\"all\",\"job_name\":\"\\ud574\\uc678\\ub9c8\\ucf13 \\uc8fc\\ubb38\\uc218\\uc9d1\",\"job_type\":\"static_method\",\"job_command\":\"workerCrawlerOrderForeign::runCrawlers\",\"job_bootstrap_path\":\"\",\"minute\":\"*\",\"hour\":\"*\",\"day_of_month\":\"*\",\"month\":\"*\",\"day_of_week\":\"*\",\"js_memo\":\"\",\"option_key\":[\"market_list\",\"safd\"],\"option_value\":[\"tmall\",\"ff\"]}',NULL,NULL,'2015-04-01 08:46:13','2872'),(63,62,'서일근PC','Gmaster','debug','contGmasterActionSchedule/_setSchedule','스케쥴 수정','{\"aParams\":{\"schedule_id\":\"55\",\"minute_type\":\"all\",\"hour_type\":\"all\",\"day_of_month_type\":\"all\",\"month_type\":\"all\",\"day_of_week_type\":\"all\",\"job_name\":\"\\ud574\\uc678\\ub9c8\\ucf13 \\uc8fc\\ubb38\\uc218\\uc9d1\",\"job_type\":\"static_method\",\"job_command\":\"workerCrawlerOrderForeign::runCrawlers\",\"job_bootstrap_path\":\"\",\"minute\":\"*\",\"hour\":\"*\",\"day_of_month\":\"*\",\"month\":\"*\",\"day_of_week\":\"*\",\"js_memo\":\"\",\"option_data\":\"{\\\"market_list\\\":\\\"tmall\\\",\\\"safd\\\":\\\"ff\\\"}\"},\"aWhere\":{\"schedule_id\":\"55\"}}',NULL,NULL,'2015-04-01 08:46:13','2872'),(64,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setSchedule','{\"schedule_id\":\"55\",\"action_name\":\"setSchedule\",\"minute_type\":\"all\",\"hour_type\":\"all\",\"day_of_month_type\":\"all\",\"month_type\":\"all\",\"day_of_week_type\":\"all\",\"job_name\":\"\\ud574\\uc678\\ub9c8\\ucf13 \\uc8fc\\ubb38\\uc218\\uc9d1\",\"job_type\":\"static_method\",\"job_command\":\"workerCrawlerOrderForeign::runCrawlers\",\"job_bootstrap_path\":\"\",\"minute\":\"*\",\"hour\":\"*\",\"day_of_month\":\"*\",\"month\":\"*\",\"day_of_week\":\"*\",\"js_memo\":\"\",\"option_key\":[\"market_list\"],\"option_value\":[\"tmall\"]}',NULL,NULL,'2015-04-01 08:46:20','2872'),(65,64,'서일근PC','Gmaster','debug','contGmasterActionSchedule/_setSchedule','스케쥴 수정','{\"aParams\":{\"schedule_id\":\"55\",\"minute_type\":\"all\",\"hour_type\":\"all\",\"day_of_month_type\":\"all\",\"month_type\":\"all\",\"day_of_week_type\":\"all\",\"job_name\":\"\\ud574\\uc678\\ub9c8\\ucf13 \\uc8fc\\ubb38\\uc218\\uc9d1\",\"job_type\":\"static_method\",\"job_command\":\"workerCrawlerOrderForeign::runCrawlers\",\"job_bootstrap_path\":\"\",\"minute\":\"*\",\"hour\":\"*\",\"day_of_month\":\"*\",\"month\":\"*\",\"day_of_week\":\"*\",\"js_memo\":\"\",\"option_data\":\"{\\\"market_list\\\":\\\"tmall\\\"}\"},\"aWhere\":{\"schedule_id\":\"55\"}}',NULL,NULL,'2015-04-01 08:46:20','2872'),(66,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: disableNotifier','{\"action_name\":\"disableNotifier\",\"notifier_id\":\"9\"}',NULL,NULL,'2015-04-01 08:50:08','2872'),(67,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: enableNotifier','{\"action_name\":\"enableNotifier\",\"notifier_id\":\"9\"}',NULL,NULL,'2015-04-01 08:50:11','2872'),(68,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: deleteNotifier','{\"action_name\":\"deleteNotifier\",\"notifier_id\":\"7\"}',NULL,NULL,'2015-04-01 08:50:16','2872'),(69,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: deleteNotifier','{\"action_name\":\"deleteNotifier\",\"notifier_id\":\"7\"}',NULL,NULL,'2015-04-01 08:50:19','2872'),(70,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: deleteNotifier','{\"action_name\":\"deleteNotifier\",\"notifier_id\":\"7\"}',NULL,NULL,'2015-04-01 08:50:35','2872'),(71,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: deleteNotifier','{\"action_name\":\"deleteNotifier\",\"notifier_id\":\"7\"}',NULL,NULL,'2015-04-01 08:50:49','2872'),(72,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"1\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test\",\"groupware_id_list\":\"igseo\",\"email_list\":\"igseo@gmail.com\",\"mobile_list\":\"\",\"memo\":\"test\",\"option_key\":[\"project_name\"],\"option_value\":[\"test\"]}',NULL,NULL,'2015-04-01 09:33:46','2872'),(73,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"1\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test\",\"groupware_id_list\":\"igseo\",\"email_list\":\"igseo@gmail.com\",\"mobile_list\":\"\",\"memo\":\"test\",\"option_key\":[\"project_name\"],\"option_value\":[\"test\"]}',NULL,NULL,'2015-04-01 09:34:00','2872'),(74,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"1\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test\",\"groupware_id_list\":\"igseo\",\"email_list\":\"igseo@gmail.com\",\"mobile_list\":\"\",\"memo\":\"test\",\"option_key\":[\"project_name\"],\"option_value\":[\"test\"]}',NULL,NULL,'2015-04-01 09:34:53','2872'),(75,NULL,'서일근PC','test','debug','console/test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:35:45','1456'),(76,NULL,'서일근PC','test','debug','console/test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:40:02','8436'),(77,NULL,'서일근PC','test','debug','console/test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:41:06','9644'),(78,NULL,'서일근PC','test','debug','console/test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:42:11','11800'),(79,NULL,'서일근PC','test','debug','console/test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:42:28','10180'),(80,NULL,'서일근PC','test','debug','console/test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:43:23','6652'),(81,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"9\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test4444\",\"groupware_id_list\":\"igseo\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"33\",\"option_key\":[\"log_level\",\"project_name\"],\"option_value\":[\"fatal\",\"test\"]}',NULL,NULL,'2015-04-01 09:44:10','2872'),(82,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"8\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test333\",\"groupware_id_list\":\"igseo\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"log_level\",\"log_type\",\"log_level\"],\"option_value\":[\"fatal\",\"test\",\"error\"]}',NULL,NULL,'2015-04-01 09:44:35','2872'),(83,NULL,'서일근PC','test','error','test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:44:59','8272'),(84,NULL,'서일근PC','test','error','test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:45:32','9400'),(85,NULL,'서일근PC','test2','error','test','test','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:45:54','9724'),(86,NULL,'서일근PC','test2','warning','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:46:03','5332'),(87,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:46:10','3156'),(88,NULL,'서일근PC','test2','fatal','test2','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:46:26','3200'),(89,NULL,'서일근PC','test2','fatal','LogUtil/fatal','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:46:50','9004'),(90,NULL,'서일근PC','test2','fatal','console/test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!\"}',NULL,NULL,'2015-04-01 09:47:23','8232'),(91,NULL,'서일근PC','test2','fatal','console/test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 09:47:29','8924'),(92,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 09:51:34','9324'),(93,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 09:54:13','11796'),(94,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 09:55:34','2608'),(95,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 09:59:03','10912'),(96,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: disableNotifier','{\"action_name\":\"disableNotifier\",\"notifier_id\":\"8\"}',NULL,NULL,'2015-04-01 09:59:18','2872'),(97,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 09:59:22','9380'),(98,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:03:06','1224'),(99,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: enableNotifier','{\"action_name\":\"enableNotifier\",\"notifier_id\":\"8\"}',NULL,NULL,'2015-04-01 10:03:13','2872'),(100,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:03:15','8876'),(101,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:03:33','10316'),(102,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"8\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test333\",\"groupware_id_list\":\"igseo\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\",\"option_key\":[\"log_level\",\"log_level\",\"project_name\"],\"option_value\":[\"fatal\",\"error\",\"test2\"]}',NULL,NULL,'2015-04-01 10:03:54','2872'),(103,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:03:57','4616'),(104,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:05:00','9852'),(105,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:05:14','8652'),(106,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:05:33','3656'),(107,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:06:40','9340'),(108,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: disableNotifier','{\"action_name\":\"disableNotifier\",\"notifier_id\":\"8\"}',NULL,NULL,'2015-04-01 10:06:48','2872'),(109,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:06:50','9408'),(110,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: enableNotifier','{\"action_name\":\"enableNotifier\",\"notifier_id\":\"8\"}',NULL,NULL,'2015-04-01 10:06:54','2872'),(111,NULL,'서일근PC','test2','fatal','test','tes2t','{\"key\":\"\\ub0a0\\ub77c\\uac00\\ub77c!!333\"}',NULL,NULL,'2015-04-01 10:06:56','9116'),(112,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"tmall_order_crawling_sessionkey_expired\",\"groupware_id_list\":\"igseo\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\\ud2f0\\ubab0 \\uc8fc\\ubb38\\uc218\\uc9d1 \\uc138\\uc158\\ud0a4\\ub9cc\\ub8cc \\uc54c\\ub9bc\",\"option_key\":[\"log_level\",\"log_level\",\"project_name\"],\"option_value\":[\"error\",\"fatal\",\"tmall_order_crawling\"]}',NULL,NULL,'2015-04-02 00:34:37','2872'),(113,NULL,'서일근PC','tmall_order_crawling','error','LogUtil/error','티몰 주문수집 세션키 expired!','{\"session_key\":\"test_session_key_12345\"}',NULL,NULL,'2015-04-02 00:35:22','10224'),(114,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"10\",\"action_name\":\"setNotifier\",\"notifier_name\":\"tmall_order_crawling_sessionkey_expired\",\"groupware_id_list\":\"igseo,jajang\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\\ud2f0\\ubab0 \\uc8fc\\ubb38\\uc218\\uc9d1 \\uc138\\uc158\\ud0a4\\ub9cc\\ub8cc \\uc54c\\ub9bc\",\"option_key\":[\"log_level\",\"log_level\",\"project_name\"],\"option_value\":[\"error\",\"fatal\",\"tmall_order_crawling\"]}',NULL,NULL,'2015-04-02 00:36:57','2872'),(115,NULL,'서일근PC','tmall_order_crawling','error','LogUtil/error','티몰 주문수집 세션키 expired!','{\"session_key\":\"test_session_key_12345\"}',NULL,NULL,'2015-04-02 00:37:07','8900'),(116,NULL,'서일근PC','tmall_order_crawling','error','LogUtil/error','test file',NULL,NULL,NULL,'2015-04-02 02:41:59','6888'),(117,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"10\",\"action_name\":\"setNotifier\",\"notifier_name\":\"tmall_order_crawling_sessionkey_expired\",\"groupware_id_list\":\"igseo\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\\ud2f0\\ubab0 \\uc8fc\\ubb38\\uc218\\uc9d1 \\uc138\\uc158\\ud0a4\\ub9cc\\ub8cc \\uc54c\\ub9bc\",\"option_key\":[\"log_level\",\"log_level\",\"project_name\"],\"option_value\":[\"error\",\"fatal\",\"tmall_order_crawling\"]}',NULL,NULL,'2015-04-02 02:42:23','2872'),(118,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setNotifier','{\"notifier_id\":\"\",\"action_name\":\"setNotifier\",\"notifier_name\":\"test\",\"groupware_id_list\":\"igseo\",\"email_list\":\"\",\"mobile_list\":\"\",\"memo\":\"\"}',NULL,NULL,'2015-04-02 02:46:08','2872'),(119,NULL,'서일근PC','tmall_order_crawling','info','LogUtil/info','test file',NULL,NULL,NULL,'2015-04-02 02:47:00','11284'),(120,119,'서일근PC','tmall_order_crawling','debug','LogUtil/debug','in function',NULL,NULL,NULL,'2015-04-02 02:47:00','11284'),(121,119,'서일근PC','tmall_order_crawling','debug','LogUtil/debug','in static method',NULL,NULL,NULL,'2015-04-02 02:47:00','11284'),(122,119,'서일근PC','tmall_order_crawling','debug','LogUtil/debug','in method',NULL,NULL,NULL,'2015-04-02 02:47:01','11284'),(123,NULL,'서일근PC','tmall_order_crawling','info','LogUtil/info','test file',NULL,NULL,NULL,'2015-04-02 02:47:22','3440'),(124,123,'서일근PC','tmall_order_crawling','debug','bbb','in function',NULL,NULL,NULL,'2015-04-02 02:47:22','3440'),(125,123,'서일근PC','tmall_order_crawling','debug','LogUtil/debug','in static method',NULL,NULL,NULL,'2015-04-02 02:47:22','3440'),(126,123,'서일근PC','tmall_order_crawling','debug','LogUtil/debug','in method',NULL,NULL,NULL,'2015-04-02 02:47:22','3440'),(127,NULL,'서일근PC','tmall_order_crawling','info','LogUtil/info','test file',NULL,NULL,NULL,'2015-04-02 02:47:38','6904'),(128,127,'서일근PC','tmall_order_crawling','debug','bbb','in function',NULL,NULL,NULL,'2015-04-02 02:47:38','6904'),(129,127,'서일근PC','tmall_order_crawling','debug','ABC/a','in static method',NULL,NULL,NULL,'2015-04-02 02:47:38','6904'),(130,127,'서일근PC','tmall_order_crawling','debug','ABC/c','in method',NULL,NULL,NULL,'2015-04-02 02:47:38','6904'),(131,NULL,'서일근PC','tmall_order_crawling','info','LogUtil/info','test file',NULL,NULL,NULL,'2015-04-02 02:48:25','7564'),(132,NULL,'서일근PC','tmall_order_crawling','debug','bbb','in function',NULL,NULL,NULL,'2015-04-02 02:48:25','7564'),(133,NULL,'서일근PC','tmall_order_crawling','debug','ABC/a','in static method',NULL,NULL,NULL,'2015-04-02 02:48:25','7564'),(134,NULL,'서일근PC','tmall_order_crawling','debug','ABC/c','in method',NULL,NULL,NULL,'2015-04-02 02:48:25','7564'),(135,NULL,'서일근PC','tmall_order_crawling','debug','ABC/a','in static method',NULL,NULL,NULL,'2015-04-02 02:48:38','11608'),(136,NULL,'서일근PC','tmall_order_crawling','debug','ABC/a','in static method',NULL,NULL,NULL,'2015-04-02 02:49:05','9888'),(137,NULL,'서일근PC','tmall_order_crawling','debug','ABC/a','test',NULL,NULL,NULL,'2015-04-02 02:49:05','9888'),(138,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: setServer','{\"server_id\":\"9\",\"action_name\":\"setServer\",\"host\":\"192.168.228.139\",\"port\":\"4730\",\"php_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/bin\\/php\",\"cli_ini_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/etc\\/php.ini\",\"gearman_dir\":\"\\/home\\/apps\\/gearmand\",\"gearman_command\":\"\\/home\\/apps\\/gearmand\\/sbin\\/gearmand -d -l \\/home\\/apps\\/gearmand\\/logs\\/gearmand.log\",\"gs_memo\":\"\",\"is_master\":\"1\"}',NULL,NULL,'2015-04-02 07:48:13','2128'),(139,138,'서일근PC','Gmaster','debug','contGmasterActionServer/_doServerAfterJob','서버세팅 후처리 시작','{\"server_id\":\"9\",\"host\":\"192.168.228.136\",\"port\":\"4730\",\"is_master\":\"1\",\"gs_is_disabled\":\"0\",\"php_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/bin\\/php\",\"cli_ini_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/etc\\/php.ini\",\"gearman_dir\":\"\\/home\\/apps\\/gearmand\",\"gearman_command\":\"\\/home\\/apps\\/gearmand\\/sbin\\/gearmand -d -l \\/home\\/apps\\/gearmand\\/logs\\/gearmand.log\",\"gs_memo\":\"\",\"gs_insert_time\":\"2015-02-25 16:14:35\"}',NULL,NULL,'2015-04-02 07:48:13','2128'),(140,138,'서일근PC','Gmaster','debug','contGmasterAction/restartWorkers','워커 재시작 시작','[[\"24\",\"25\",\"28\"],[\"9\"],null,{\"server_id\":\"9\",\"host\":\"192.168.228.136\",\"port\":\"4730\",\"is_master\":\"1\",\"gs_is_disabled\":\"0\",\"php_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/bin\\/php\",\"cli_ini_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/etc\\/php.ini\",\"gearman_dir\":\"\\/home\\/apps\\/gearmand\",\"gearman_command\":\"\\/home\\/apps\\/gearmand\\/sbin\\/gearmand -d -l \\/home\\/apps\\/gearmand\\/logs\\/gearmand.log\",\"gs_memo\":\"\",\"gs_insert_time\":\"2015-02-25 16:14:35\"}]',NULL,NULL,'2015-04-02 07:48:13','2128'),(141,138,'서일근PC','Gmaster','debug','GearmanProxy/callMethod','원격 메소드 호출 시작(proxy)','{\"host\":\"192.168.228.139\",\"port\":\"4730\",\"timeout\":10000,\"is_async\":true,\"command\":\"GearmanSystem::restartWorkersSeparately\",\"command_type\":\"static_method\",\"trace_id\":\"138\",\"params\":{\"original\":{\"php_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/bin\\/php\",\"cli_ini_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/etc\\/php.ini\",\"workers\":[{\"worker_id\":\"24\",\"worker_name\":\"cli_runner:192.168.228.136:4730\",\"worker_type\":\"static_method\",\"worker_path\":null,\"worker_class\":\"GearmanSystem\",\"worker_function\":\"executeCli\",\"w_memo\":null,\"w_is_disabled\":\"0\",\"default_option_data\":null,\"w_insert_time\":\"2015-02-25 16:14:35\",\"worker_link_id\":\"44\",\"server_id\":\"9\",\"worker_count\":\"1\",\"bootstrap_path\":null,\"wl_is_disabled\":\"0\",\"option_data\":null,\"wl_memo\":null,\"wl_insert_time\":\"2015-02-25 16:14:35\"},{\"worker_id\":\"25\",\"worker_name\":\"cli_runner_sync:192.168.228.136:4730\",\"worker_type\":\"static_method\",\"worker_path\":null,\"worker_class\":\"GearmanSystem\",\"worker_function\":\"executeCli\",\"w_memo\":null,\"w_is_disabled\":\"0\",\"default_option_data\":null,\"w_insert_time\":\"2015-02-25 16:14:35\",\"worker_link_id\":\"45\",\"server_id\":\"9\",\"worker_count\":\"3\",\"bootstrap_path\":null,\"wl_is_disabled\":\"0\",\"option_data\":null,\"wl_memo\":null,\"wl_insert_time\":\"2015-02-25 16:14:35\"},{\"worker_id\":\"28\",\"worker_name\":\"testWorker\",\"worker_type\":\"static_method\",\"worker_path\":\"\",\"worker_class\":\"libWorker\",\"worker_function\":\"testWorker\",\"w_memo\":\"\",\"w_is_disabled\":\"0\",\"default_option_data\":null,\"w_insert_time\":\"2015-02-25 16:27:46\",\"worker_link_id\":\"72\",\"server_id\":\"9\",\"worker_count\":\"1\",\"bootstrap_path\":\"\",\"wl_is_disabled\":\"0\",\"option_data\":null,\"wl_memo\":\"\",\"wl_insert_time\":\"2015-03-02 14:10:39\"}]},\"new\":{\"php_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/bin\\/php\",\"cli_ini_path\":\"\\/opt\\/lampstack-5.3.28-0\\/php\\/etc\\/php.ini\",\"workers\":[{\"worker_id\":\"24\",\"worker_name\":\"cli_runner:192.168.228.136:4730\",\"worker_type\":\"static_method\",\"worker_path\":null,\"worker_class\":\"GearmanSystem\",\"worker_function\":\"executeCli\",\"w_memo\":null,\"w_is_disabled\":\"0\",\"default_option_data\":null,\"w_insert_time\":\"2015-02-25 16:14:35\",\"worker_link_id\":\"44\",\"server_id\":\"9\",\"worker_count\":\"1\",\"bootstrap_path\":null,\"wl_is_disabled\":\"0\",\"option_data\":null,\"wl_memo\":null,\"wl_insert_time\":\"2015-02-25 16:14:35\"},{\"worker_id\":\"25\",\"worker_name\":\"cli_runner_sync:192.168.228.136:4730\",\"worker_type\":\"static_method\",\"worker_path\":null,\"worker_class\":\"GearmanSystem\",\"worker_function\":\"executeCli\",\"w_memo\":null,\"w_is_disabled\":\"0\",\"default_option_data\":null,\"w_insert_time\":\"2015-02-25 16:14:35\",\"worker_link_id\":\"45\",\"server_id\":\"9\",\"worker_count\":\"3\",\"bootstrap_path\":null,\"wl_is_disabled\":\"0\",\"option_data\":null,\"wl_memo\":null,\"wl_insert_time\":\"2015-02-25 16:14:35\"},{\"worker_id\":\"28\",\"worker_name\":\"testWorker\",\"worker_type\":\"static_method\",\"worker_path\":\"\",\"worker_class\":\"libWorker\",\"worker_function\":\"testWorker\",\"w_memo\":\"\",\"w_is_disabled\":\"0\",\"default_option_data\":null,\"w_insert_time\":\"2015-02-25 16:27:46\",\"worker_link_id\":\"72\",\"server_id\":\"9\",\"worker_count\":\"1\",\"bootstrap_path\":\"\",\"wl_is_disabled\":\"0\",\"option_data\":null,\"wl_memo\":\"\",\"wl_insert_time\":\"2015-03-02 14:10:39\"}]}}}',NULL,NULL,'2015-04-02 07:48:13','2128'),(142,138,'서일근PC','Gmaster','fatal','GearmanProxy/callMethod','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\\/method\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 07:48:37','2128'),(143,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"hostname\",\"host\":\"192.168.228.139\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 07:49:59','2128'),(144,143,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 07:50:22','2128'),(145,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"hostname\",\"host\":\"192.168.228.139\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 07:50:22','2128'),(146,145,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 07:50:46','2128'),(147,NULL,'서일근PC','Gmaster','fatal','GearmanProxy/getWorkerStatus','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/status\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 07:54:34','2128'),(148,NULL,'서일근PC','Gmaster','fatal','GearmanProxy/getWorkerStatus','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/status\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 07:54:58','2128'),(149,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"f\",\"host\":\"192.168.228.137\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 08:35:28','2128'),(150,149,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 08:35:51','2128'),(151,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"asdfsdf\",\"host\":\"192.168.228.137\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 08:39:12','2128'),(152,151,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 08:39:36','2128'),(153,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"fqq3afqaf\",\"host\":\"192.168.228.137\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 08:39:37','2128'),(154,153,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 08:40:02','2128'),(155,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"asdfasdf\",\"host\":\"192.168.228.137\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 08:40:02','2128'),(156,155,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 08:40:25','2128'),(157,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"asdfasdffefef\",\"host\":\"192.168.228.137\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 08:40:25','2128'),(158,157,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 08:40:48','2128'),(159,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"asdfasdffefeffesf\",\"host\":\"192.168.228.137\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 08:40:48','2128'),(160,159,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 08:41:12','2128'),(161,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"asdfasdf\",\"host\":\"192.168.228.137\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 08:41:12','2128'),(162,161,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 08:41:35','2128'),(163,NULL,'서일근PC','Gmaster','info','contGmasterAction/index','csd_admin 액션 시작: shell','{\"command\":\"asdfasdfasdfsdf\",\"host\":\"192.168.228.137\",\"action_name\":\"shell\"}',NULL,NULL,'2015-04-02 08:41:35','2128'),(164,163,'서일근PC','Gmaster','fatal','GearmanProxy/executeCli','curl post 호출 실패','{\"data\":\"http:\\/\\/vm.gmaster.com\\/api\\/cli\",\"code\":0,\"previous\":null}',NULL,NULL,'2015-04-02 08:42:00','2128'),(165,NULL,'서일근PC','Gmaster','debug','Dispatcher/exec','Gmaster Api 시작',NULL,NULL,NULL,'2015-04-03 07:34:37','3160'),(166,165,'서일근PC','Gmaster','debug','Dispatcher/exec','Gmaster Api 끝',NULL,NULL,NULL,'2015-04-03 07:34:37','3160'),(167,NULL,'서일근PC','Gmaster','debug','Dispatcher/exec','Gmaster Api 시작',NULL,NULL,NULL,'2015-04-03 07:34:53','3160'),(168,167,'서일근PC','Gmaster','debug','contApiServer/index','API 요청 파라미터',NULL,NULL,NULL,'2015-04-03 07:34:54','3160'),(169,167,'서일근PC','Gmaster','debug','call_user_func_array','Gmaster Api 끝','{\"result\":false,\"msg\":\"\\ud30c\\ub77c\\ubbf8\\ud130 \\uc5d0\\ub7ec\",\"data\":null}',NULL,NULL,'2015-04-03 07:34:54','3160');
/*!40000 ALTER TABLE `common_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifier`
--

DROP TABLE IF EXISTS `notifier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifier` (
  `notifier_id` int(11) NOT NULL AUTO_INCREMENT,
  `notifier_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `groupware_id_list` text COLLATE utf8_unicode_ci,
  `email_list` text COLLATE utf8_unicode_ci,
  `mobile_list` text COLLATE utf8_unicode_ci,
  `is_disabled` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `memo` text COLLATE utf8_unicode_ci,
  `insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notifier_id`),
  UNIQUE KEY `notifier_name_UNIQUE` (`notifier_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifier`
--

LOCK TABLES `notifier` WRITE;
/*!40000 ALTER TABLE `notifier` DISABLE KEYS */;
INSERT INTO `notifier` VALUES (1,'test','igseo','igseo@gmail.com','','0','test','2015-04-01 07:32:07'),(2,'test2','igseo,test12,test3',NULL,'01000003232,01093832321','0','test2','2015-04-01 07:32:07'),(8,'test333','igseo','','','0','','2015-04-01 08:28:32'),(9,'test4444','igseo','','','0','33','2015-04-01 08:43:27'),(10,'tmall_order_crawling_sessionkey_expired','igseo','','','0','티몰 주문수집 세션키만료 알림','2015-04-02 00:34:37');
/*!40000 ALTER TABLE `notifier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifier_filter`
--

DROP TABLE IF EXISTS `notifier_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifier_filter` (
  `notifier_filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `notifier_id` int(11) NOT NULL,
  `filter_key` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `filter_value` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notifier_filter_id`),
  KEY `fk_nf_notifier_id_idx` (`notifier_id`),
  KEY `idx_nf_filter_key` (`filter_key`),
  KEY `idx_nf_filter_value` (`filter_value`),
  CONSTRAINT `fk_nf_notifier_id` FOREIGN KEY (`notifier_id`) REFERENCES `notifier` (`notifier_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifier_filter`
--

LOCK TABLES `notifier_filter` WRITE;
/*!40000 ALTER TABLE `notifier_filter` DISABLE KEYS */;
INSERT INTO `notifier_filter` VALUES (3,2,'project_name','test2','2015-04-01 07:32:56'),(4,2,'log_type','test/test','2015-04-01 07:32:56'),(5,2,'log_msg','hahaha','2015-04-01 07:32:56'),(45,1,'project_name','test','2015-04-01 09:34:53'),(46,9,'log_level','fatal','2015-04-01 09:44:10'),(47,9,'project_name','test','2015-04-01 09:44:10'),(51,8,'log_level','fatal','2015-04-01 10:03:54'),(52,8,'log_level','error','2015-04-01 10:03:54'),(53,8,'project_name','test2','2015-04-01 10:03:54'),(60,10,'log_level','error','2015-04-02 02:42:23'),(61,10,'log_level','fatal','2015-04-02 02:42:23'),(62,10,'project_name','tmall_order_crawling','2015-04-02 02:42:23');
/*!40000 ALTER TABLE `notifier_filter` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-04-18 12:07:39
