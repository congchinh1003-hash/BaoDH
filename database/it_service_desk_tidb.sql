-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: it_service_desk
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Current Database: `it_service_desk`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `it_service_desk` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `it_service_desk`;

--
-- Table structure for table `asset_categories`
--

DROP TABLE IF EXISTS `asset_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'bi-laptop',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_categories`
--

LOCK TABLES `asset_categories` WRITE;
/*!40000 ALTER TABLE `asset_categories` DISABLE KEYS */;
INSERT INTO `asset_categories` VALUES (1,'LAPTOP','Máy tính xách tay (Laptop)','Laptop cấp cho nhân viên làm việc văn phòng và di động','bi-laptop',1,'2026-09-20 12:56:11'),(2,'DESKTOP','Máy tính để bàn (PC)','Máy trạm, PC văn phòng cố định','bi-pc-display',1,'2026-09-20 12:56:11'),(3,'MONITOR','Màn hình hiển thị','Màn hình rời 24-27 inch','bi-display',1,'2026-09-20 12:56:11'),(4,'PRINTER','Máy in / Máy quét','Máy in mạng, photocopy văn phòng','bi-printer',1,'2026-09-20 12:56:11'),(5,'NETWORK','Thiết bị Mạng','Switch, Router, Access Point WiFi','bi-router',1,'2026-09-20 12:56:11'),(6,'SERVER','Máy chủ hệ thống','Máy chủ vật lý, Rack Server phòng Data Center','bi-hdd-rack',1,'2026-09-20 12:56:11'),(7,'SOFTWARE','Bản quyền Phần mềm','License Windows, Microsoft 365, AutoCAD...','bi-key',1,'2026-09-20 12:56:11'),(8,'APPLICATION','Ứng dụng & Dịch vụ số (Systems/Apps)','Hệ thống ERP, CRM, HRM, Website nội bộ, Mail Server','bi-laptop',1,'2026-09-21 20:18:49'),(9,'NETWORK_DEVICE','Thiết bị mạng & An ninh (Network/Firewall)','Switch Core, Router, Firewall Fortinet/Cisco, VPN Gateway','bi-laptop',1,'2026-09-21 20:18:49');
/*!40000 ALTER TABLE `asset_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_handovers`
--

DROP TABLE IF EXISTS `asset_handovers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_handovers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `action_type` enum('HANDOVER','RECALL','TRANSFER','DISPOSAL','STATUS_CHANGE') NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) DEFAULT NULL,
  `from_location` varchar(150) DEFAULT NULL,
  `to_location` varchar(150) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `handover_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_hnd_asset` (`asset_id`),
  KEY `fk_hnd_from_user` (`from_user_id`),
  KEY `fk_hnd_to_user` (`to_user_id`),
  KEY `fk_hnd_performer` (`performed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_handovers`
--

LOCK TABLES `asset_handovers` WRITE;
/*!40000 ALTER TABLE `asset_handovers` DISABLE KEYS */;
INSERT INTO `asset_handovers` VALUES (1,1,'HANDOVER',NULL,6,'Kho IT','Tầng 3 - Phòng Kinh doanh','Bàn giao laptop mới cho nhân viên Đỗ Mai Phương khi thử việc',1,'2025-01-16 09:00:00'),(2,4,'HANDOVER',NULL,6,'Kho IT','Tầng 3 - Bàn 12','Cấp bổ sung màn hình rời hỗ trợ nhập liệu',1,'2025-01-16 09:30:00'),(3,2,'HANDOVER',NULL,7,'Kho IT','Tầng 4 - Phòng Marketing','Bàn giao máy đồ họa cho nhân viên Vũ Đình Trọng',1,'2024-06-11 10:00:00'),(4,8,'RECALL',7,NULL,'Tầng 4','Kho IT','Thu hồi laptop cũ của Trọng trước khi nâng cấp máy mới',1,'2024-06-10 16:30:00'),(5,6,'STATUS_CHANGE',NULL,NULL,'Kho IT - Kệ A2','Kho IT - Kệ A2','Đổi trạng thái sang MAINTENANCE: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-20 18:49:45'),(6,6,'STATUS_CHANGE',NULL,NULL,'Kho IT - Kệ A2','Kho IT - Kệ A2','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-20 18:50:34'),(7,1,'STATUS_CHANGE',6,6,'Tầng 3 - Phòng Kinh doanh','Tầng 3 - Phòng Kinh doanh','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-20 19:14:49'),(8,2,'STATUS_CHANGE',7,7,'Tầng 4 - Phòng Marketing','Tầng 4 - Phòng Marketing','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-21 20:29:28'),(9,3,'STATUS_CHANGE',NULL,NULL,'Tầng 2 - Phòng Kế toán','Tầng 2 - Phòng Kế toán','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-21 20:31:06'),(10,4,'STATUS_CHANGE',6,6,'Tầng 3 - Bàn 12','Tầng 3 - Bàn 12','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-21 21:10:55'),(11,5,'STATUS_CHANGE',NULL,NULL,'Tầng 2 - Sảnh chung','Tầng 2 - Sảnh chung','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-21 21:37:22'),(12,7,'STATUS_CHANGE',NULL,NULL,'Server Room Tầng 1 - Rack 1','Server Room Tầng 1 - Rack 1','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-22 22:45:22'),(13,1,'STATUS_CHANGE',6,6,'Tầng 3 - Phòng Kinh doanh','Tầng 3 - Phòng Kinh doanh','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-26 10:45:56'),(14,1,'STATUS_CHANGE',6,6,'Tầng 3 - Phòng Kinh doanh','Tầng 3 - Phòng Kinh doanh','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-09-26 10:54:41'),(15,4,'STATUS_CHANGE',6,6,'Tầng 3 - Bàn 12','Tầng 3 - Bàn 12','Đổi trạng thái sang REPAIR: Đưa vào phòng bảo trì sửa bàn phím',3,'2026-10-26 17:15:58');
/*!40000 ALTER TABLE `asset_handovers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_repairs`
--

DROP TABLE IF EXISTS `asset_repairs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_repairs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `repair_date` date NOT NULL,
  `issue_description` text NOT NULL,
  `root_cause` text DEFAULT NULL,
  `solution` text DEFAULT NULL,
  `replaced_parts` text DEFAULT NULL,
  `cost` decimal(15,2) DEFAULT 0.00,
  `vendor` varchar(150) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_rep_asset` (`asset_id`),
  KEY `fk_rep_performer` (`performed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_repairs`
--

LOCK TABLES `asset_repairs` WRITE;
/*!40000 ALTER TABLE `asset_repairs` DISABLE KEYS */;
INSERT INTO `asset_repairs` VALUES (1,8,'2026-09-15','Máy kêu to, quạt phát tiếng rít, liệt phím cách (Spacebar)','Bụi bẩn bám kẹt quạt, bàn phím bị đổ nước ngọt nhẹ','Gửi hãng bảo hành thay quạt và cụm phím mới','Quạt tản nhiệt Asus B1, Bàn phím LED',850000.00,'Trung tâm Bảo hành Asus Việt Nam',3,'Chờ linh kiện hãng báo về','2026-09-20 12:56:11');
/*!40000 ALTER TABLE `asset_repairs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_expire` date DEFAULT NULL,
  `status` enum('IN_STOCK','IN_USE','REPAIR','LOST','DAMAGED','LIQUIDATED','DISPOSED') DEFAULT 'IN_STOCK',
  `department` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `current_user_id` int(11) DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_code` (`asset_code`),
  KEY `idx_asset_status` (`status`),
  KEY `idx_asset_user` (`current_user_id`),
  KEY `fk_asset_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (1,'AST-LAP-001','Dell Latitude 5420 i5',1,'Dell','Latitude 5420','SN-DL5420-9981','2025-01-15','2028-01-15','IN_USE','Kinh doanh','Tầng 3 - Phòng Kinh doanh',6,'CPU: Core i5 1145G7 | RAM: 16GB DDR4 | SSD: 512GB NVMe | Màn hình: 14 inch FHD','Cấp cho Đỗ Mai Phương khi nhận việc','2026-09-20 12:56:11','2026-11-17 22:11:43'),(2,'AST-LAP-002','MacBook Pro 14 M2 Pro',1,'Apple','MacBook Pro 14 2023','SN-APMBP-7721','2024-06-10','2026-06-10','REPAIR','Marketing','Tầng 4 - Phòng Marketing',7,'CPU: Apple M2 Pro 10-core | RAM: 16GB | SSD: 512GB | Retina XDR','Cấp cho Vũ Đình Trọng làm đồ họa','2026-09-20 12:56:11','2026-09-21 20:29:28'),(3,'AST-PC-001','HP ProDesk 400 G7 MT',2,'HP','ProDesk 400 G7','SN-HPPD-1029','2023-11-20','2026-11-20','REPAIR','Kế toán','Tầng 2 - Phòng Kế toán',NULL,'CPU: Core i7 10700 | RAM: 16GB | SSD: 256GB + HDD 1TB','Máy để bàn phòng Kế toán dùng chung','2026-09-20 12:56:11','2026-09-21 20:31:06'),(4,'AST-MON-001','Dell UltraSharp 24 U2422H',3,'Dell','U2422H','SN-DU2422-5532','2025-01-15','2028-01-15','IN_USE','Kinh doanh','Tầng 3 - Bàn 12',6,'24 inch IPS, FHD 1920x1080, 100% sRGB, Type-C, DP, HDMI','Màn hình phụ cấp kèm laptop của Mai Phương','2026-09-20 12:56:11','2026-11-17 22:11:43'),(5,'AST-PRN-001','Canon LBP 2900 Laser',4,'Canon','LBP 2900','SN-CN2900-4411','2023-05-10','2025-05-10','REPAIR','Hành chính','Tầng 2 - Sảnh chung',NULL,'In laser đen trắng A4, cổng USB + Print Server LAN','Máy in dùng chung toàn tầng 2','2026-09-20 12:56:11','2026-09-21 21:37:22'),(6,'AST-LAP-003','ThinkPad T14 Gen 3 i5',1,'Lenovo','ThinkPad T14 Gen 3','SN-LNT14-3321','2024-08-15','2027-08-15','IN_USE','Kế toán','Phòng Kế toán P.302',17,'CPU: Core i5 1240P | RAM: 16GB | SSD: 512GB','Máy dự phòng trong kho IT','2026-09-20 12:56:11','2026-11-17 22:15:49'),(7,'AST-NET-001','Cisco Catalyst 2960X 48P',5,'Cisco','WS-C2960X-48TD-L','SN-CS2960-0091','2022-03-01','2027-03-01','REPAIR','Hạ tầng IT','Server Room Tầng 1 - Rack 1',NULL,'Switch Layer 2, 48 port Gigabit PoE+, 2 x 10G SFP+','Switch mạng chính tòa nhà','2026-09-20 12:56:11','2026-09-22 22:45:22'),(8,'AST-LAP-004','Asus ExpertBook B1',1,'Asus','B1400CEAE','SN-ASB1-8842','2023-02-10','2025-02-10','REPAIR','Kho IT','Trung tâm bảo hành Asus',NULL,'CPU: Core i3 1115G4 | RAM: 8GB | SSD: 256GB','Đang gửi hãng thay quạt tản nhiệt và bàn phím','2026-09-20 12:56:11','2026-09-20 12:56:11'),(9,'AST-MON-002','Màn hình LG 27 inch 4K UltraFine',2,'LG','27UP850','LG4K889911','2025-06-15','2027-06-15','IN_USE','Kế toán','Phòng Kế toán P.302',17,'27 inch 4K, USB-C 96W, IPS Panel',NULL,'2026-11-17 22:11:43','2026-11-17 22:15:49');
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `object_type` varchar(50) NOT NULL,
  `object_id` int(11) DEFAULT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_obj` (`object_type`,`object_id`),
  KEY `fk_audit_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'TICKET_SLA_OVERDUE','ticket',1,'WITHIN_SLA','OVERDUE','Hạn xử lý SLA đã hết',NULL,NULL,'2026-09-20 18:49:12'),(2,2,'ADJUST_PRIORITY','TICKET',6,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-20 18:49:45'),(3,3,'MARK_DUPLICATE','TICKET',6,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-20 18:49:45'),(4,2,'ADJUST_PRIORITY','TICKET',7,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-20 18:50:34'),(5,3,'MARK_DUPLICATE','TICKET',7,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-20 18:50:34'),(6,6,'CANCEL_TICKET','TICKET',8,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-20 18:50:34'),(7,2,'ADJUST_PRIORITY','TICKET',10,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-20 19:14:49'),(8,3,'MARK_DUPLICATE','TICKET',10,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-20 19:14:49'),(9,6,'CANCEL_TICKET','TICKET',11,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-20 19:14:49'),(10,2,'ADJUST_PRIORITY','TICKET',23,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-21 20:29:28'),(11,3,'MARK_DUPLICATE','TICKET',23,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-21 20:29:28'),(12,6,'CANCEL_TICKET','TICKET',24,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-21 20:29:28'),(13,2,'ADJUST_PRIORITY','TICKET',29,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-21 20:31:06'),(14,3,'MARK_DUPLICATE','TICKET',29,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-21 20:31:06'),(15,6,'CANCEL_TICKET','TICKET',30,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-21 20:31:06'),(16,2,'ADJUST_PRIORITY','TICKET',35,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-21 21:10:55'),(17,3,'MARK_DUPLICATE','TICKET',35,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-21 21:10:55'),(18,6,'CANCEL_TICKET','TICKET',36,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-21 21:10:55'),(19,1,'CREATE_USER','USER',8,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-21 21:35:36'),(20,1,'UPDATE_USER','USER',8,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:35:50'),(21,1,'TOGGLE_USER_STATUS','USER',8,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:35:50'),(22,1,'DELETE_USER','USER',8,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:35:50'),(23,1,'CREATE_CATEGORY','CATEGORY',5,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-21 21:35:50'),(24,1,'UPDATE_CATEGORY','CATEGORY',5,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:35:50'),(25,1,'CREATE_SERVICE','SERVICE',12,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-21 21:35:50'),(26,1,'UPDATE_SERVICE','SERVICE',12,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:35:50'),(27,1,'DELETE_SERVICE','SERVICE',12,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:35:50'),(28,1,'DELETE_CATEGORY','CATEGORY',5,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:35:50'),(29,1,'CREATE_IT_GROUP','IT_GROUP',6,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-21 21:35:50'),(30,1,'UPDATE_IT_GROUP','IT_GROUP',6,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 21:35:50'),(31,1,'CREATE_USER','USER',9,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-21 21:36:03'),(32,1,'UPDATE_USER','USER',9,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:36:03'),(33,1,'TOGGLE_USER_STATUS','USER',9,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:36:03'),(34,1,'DELETE_USER','USER',9,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:36:03'),(35,1,'CREATE_CATEGORY','CATEGORY',6,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-21 21:36:03'),(36,1,'UPDATE_CATEGORY','CATEGORY',6,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:36:03'),(37,1,'CREATE_SERVICE','SERVICE',13,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-21 21:36:03'),(38,1,'UPDATE_SERVICE','SERVICE',13,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:36:03'),(39,1,'DELETE_SERVICE','SERVICE',13,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:36:03'),(40,1,'DELETE_CATEGORY','CATEGORY',6,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:36:03'),(41,1,'CREATE_IT_GROUP','IT_GROUP',7,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-21 21:36:03'),(42,1,'DELETE_IT_GROUP','IT_GROUP',7,'Nhóm Test CRUD IT',NULL,'Xóa nhóm IT Nhóm Test CRUD IT',NULL,NULL,'2026-09-21 21:36:03'),(43,1,'CREATE_SLA_CONFIG','SLA_CONFIG',4,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-21 21:36:03'),(44,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',4,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-21 21:36:03'),(45,1,'DELETE_SLA_CONFIG','SLA_CONFIG',4,NULL,NULL,'Xóa cấu hình SLA #4',NULL,NULL,'2026-09-21 21:36:03'),(46,1,'CREATE_USER','USER',10,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-21 21:36:48'),(47,1,'UPDATE_USER','USER',10,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:36:48'),(48,1,'TOGGLE_USER_STATUS','USER',10,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:36:48'),(49,1,'DELETE_USER','USER',10,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:36:48'),(50,1,'CREATE_CATEGORY','CATEGORY',7,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-21 21:36:48'),(51,1,'UPDATE_CATEGORY','CATEGORY',7,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:36:48'),(52,1,'CREATE_SERVICE','SERVICE',14,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-21 21:36:48'),(53,1,'UPDATE_SERVICE','SERVICE',14,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:36:48'),(54,1,'DELETE_SERVICE','SERVICE',14,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:36:48'),(55,1,'DELETE_CATEGORY','CATEGORY',7,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:36:48'),(56,1,'CREATE_IT_GROUP','IT_GROUP',8,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-21 21:36:48'),(57,1,'DELETE_IT_GROUP','IT_GROUP',8,'Nhóm Test CRUD IT',NULL,'Xóa nhóm IT Nhóm Test CRUD IT',NULL,NULL,'2026-09-21 21:36:48'),(58,1,'CREATE_SLA_CONFIG','SLA_CONFIG',5,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-21 21:36:48'),(59,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',5,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-21 21:36:48'),(60,1,'DELETE_SLA_CONFIG','SLA_CONFIG',5,NULL,NULL,'Xóa cấu hình SLA #5',NULL,NULL,'2026-09-21 21:36:48'),(61,1,'CREATE_USER','USER',11,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-21 21:37:12'),(62,1,'UPDATE_USER','USER',11,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:37:12'),(63,1,'TOGGLE_USER_STATUS','USER',11,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:37:12'),(64,1,'DELETE_USER','USER',11,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:37:12'),(65,1,'CREATE_CATEGORY','CATEGORY',8,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-21 21:37:12'),(66,1,'UPDATE_CATEGORY','CATEGORY',8,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:37:12'),(67,1,'CREATE_SERVICE','SERVICE',15,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-21 21:37:12'),(68,1,'UPDATE_SERVICE','SERVICE',15,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:37:12'),(69,1,'DELETE_SERVICE','SERVICE',15,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:37:12'),(70,1,'DELETE_CATEGORY','CATEGORY',8,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:37:12'),(71,1,'CREATE_IT_GROUP','IT_GROUP',9,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-21 21:37:12'),(72,1,'UPDATE_IT_GROUP','IT_GROUP',9,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 21:37:12'),(73,1,'DELETE_IT_GROUP','IT_GROUP',9,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 21:37:12'),(74,1,'CREATE_SLA_CONFIG','SLA_CONFIG',6,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-21 21:37:12'),(75,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',6,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-21 21:37:12'),(76,1,'DELETE_SLA_CONFIG','SLA_CONFIG',6,NULL,NULL,'Xóa cấu hình SLA #6',NULL,NULL,'2026-09-21 21:37:12'),(77,2,'ADJUST_PRIORITY','TICKET',39,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-21 21:37:22'),(78,3,'MARK_DUPLICATE','TICKET',39,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-21 21:37:22'),(79,6,'CANCEL_TICKET','TICKET',40,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-21 21:37:22'),(80,1,'CREATE_USER','USER',12,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-21 21:47:53'),(81,1,'UPDATE_USER','USER',12,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:47:53'),(82,1,'TOGGLE_USER_STATUS','USER',12,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:47:53'),(83,1,'DELETE_USER','USER',12,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:47:53'),(84,1,'CREATE_CATEGORY','CATEGORY',9,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-21 21:47:53'),(85,1,'UPDATE_CATEGORY','CATEGORY',9,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:47:53'),(86,1,'CREATE_SERVICE','SERVICE',16,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-21 21:47:53'),(87,1,'UPDATE_SERVICE','SERVICE',16,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:47:53'),(88,1,'DELETE_SERVICE','SERVICE',16,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:47:53'),(89,1,'DELETE_CATEGORY','CATEGORY',9,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:47:53'),(90,1,'CREATE_IT_GROUP','IT_GROUP',10,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-21 21:47:53'),(91,1,'UPDATE_IT_GROUP','IT_GROUP',10,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 21:47:53'),(92,1,'DELETE_IT_GROUP','IT_GROUP',10,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 21:47:53'),(93,1,'CREATE_SLA_CONFIG','SLA_CONFIG',7,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-21 21:47:53'),(94,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',7,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-21 21:47:53'),(95,1,'DELETE_SLA_CONFIG','SLA_CONFIG',7,NULL,NULL,'Xóa cấu hình SLA #7',NULL,NULL,'2026-09-21 21:47:53'),(96,1,'CREATE_USER','USER',13,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-21 21:58:34'),(97,1,'UPDATE_USER','USER',13,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:58:34'),(98,1,'TOGGLE_USER_STATUS','USER',13,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:58:34'),(99,1,'DELETE_USER','USER',13,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 21:58:34'),(100,1,'CREATE_CATEGORY','CATEGORY',10,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-21 21:58:34'),(101,1,'UPDATE_CATEGORY','CATEGORY',10,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:58:34'),(102,1,'CREATE_SERVICE','SERVICE',17,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-21 21:58:34'),(103,1,'UPDATE_SERVICE','SERVICE',17,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:58:34'),(104,1,'DELETE_SERVICE','SERVICE',17,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 21:58:34'),(105,1,'DELETE_CATEGORY','CATEGORY',10,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 21:58:34'),(106,1,'CREATE_IT_GROUP','IT_GROUP',11,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-21 21:58:34'),(107,1,'UPDATE_IT_GROUP','IT_GROUP',11,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 21:58:34'),(108,1,'DELETE_IT_GROUP','IT_GROUP',11,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 21:58:34'),(109,1,'CREATE_SLA_CONFIG','SLA_CONFIG',8,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-21 21:58:34'),(110,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',8,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-21 21:58:34'),(111,1,'DELETE_SLA_CONFIG','SLA_CONFIG',8,NULL,NULL,'Xóa cấu hình SLA #8',NULL,NULL,'2026-09-21 21:58:34'),(112,1,'CREATE_USER','USER',14,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-21 22:06:13'),(113,1,'UPDATE_USER','USER',14,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-21 22:06:13'),(114,1,'TOGGLE_USER_STATUS','USER',14,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 22:06:13'),(115,1,'DELETE_USER','USER',14,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 22:06:13'),(116,1,'CREATE_CATEGORY','CATEGORY',11,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-21 22:06:13'),(117,1,'UPDATE_CATEGORY','CATEGORY',11,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 22:06:13'),(118,1,'CREATE_SERVICE','SERVICE',18,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-21 22:06:13'),(119,1,'UPDATE_SERVICE','SERVICE',18,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 22:06:13'),(120,1,'DELETE_SERVICE','SERVICE',18,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 22:06:13'),(121,1,'DELETE_CATEGORY','CATEGORY',11,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 22:06:13'),(122,1,'CREATE_IT_GROUP','IT_GROUP',12,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-21 22:06:13'),(123,1,'UPDATE_IT_GROUP','IT_GROUP',12,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 22:06:13'),(124,1,'DELETE_IT_GROUP','IT_GROUP',12,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 22:06:13'),(125,1,'CREATE_SLA_CONFIG','SLA_CONFIG',9,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-21 22:06:13'),(126,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',9,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-21 22:06:13'),(127,1,'DELETE_SLA_CONFIG','SLA_CONFIG',9,NULL,NULL,'Xóa cấu hình SLA #9',NULL,NULL,'2026-09-21 22:06:13'),(128,1,'CREATE_USER','USER',15,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-21 22:33:38'),(129,1,'UPDATE_USER','USER',15,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-21 22:33:38'),(130,1,'TOGGLE_USER_STATUS','USER',15,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 22:33:38'),(131,1,'DELETE_USER','USER',15,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-21 22:33:38'),(132,1,'CREATE_CATEGORY','CATEGORY',12,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-21 22:33:38'),(133,1,'UPDATE_CATEGORY','CATEGORY',12,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 22:33:38'),(134,1,'CREATE_SERVICE','SERVICE',19,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-21 22:33:38'),(135,1,'UPDATE_SERVICE','SERVICE',19,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 22:33:38'),(136,1,'DELETE_SERVICE','SERVICE',19,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-21 22:33:38'),(137,1,'DELETE_CATEGORY','CATEGORY',12,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-21 22:33:38'),(138,1,'CREATE_IT_GROUP','IT_GROUP',13,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-21 22:33:38'),(139,1,'UPDATE_IT_GROUP','IT_GROUP',13,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 22:33:38'),(140,1,'DELETE_IT_GROUP','IT_GROUP',13,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-21 22:33:39'),(141,1,'CREATE_SLA_CONFIG','SLA_CONFIG',10,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-21 22:33:39'),(142,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',10,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-21 22:33:39'),(143,1,'DELETE_SLA_CONFIG','SLA_CONFIG',10,NULL,NULL,'Xóa cấu hình SLA #10',NULL,NULL,'2026-09-21 22:33:39'),(144,1,'CREATE_USER','USER',16,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-22 21:58:52'),(145,1,'UPDATE_USER','USER',16,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-22 21:58:52'),(146,1,'TOGGLE_USER_STATUS','USER',16,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-22 21:58:52'),(147,1,'DELETE_USER','USER',16,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-22 21:58:52'),(148,1,'CREATE_CATEGORY','CATEGORY',13,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-22 21:58:52'),(149,1,'UPDATE_CATEGORY','CATEGORY',13,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-22 21:58:52'),(150,1,'CREATE_SERVICE','SERVICE',20,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-22 21:58:52'),(151,1,'UPDATE_SERVICE','SERVICE',20,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-22 21:58:52'),(152,1,'DELETE_SERVICE','SERVICE',20,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-22 21:58:52'),(153,1,'DELETE_CATEGORY','CATEGORY',13,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-22 21:58:52'),(154,1,'CREATE_IT_GROUP','IT_GROUP',14,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-22 21:58:52'),(155,1,'UPDATE_IT_GROUP','IT_GROUP',14,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-22 21:58:52'),(156,1,'DELETE_IT_GROUP','IT_GROUP',14,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-22 21:58:52'),(157,1,'CREATE_SLA_CONFIG','SLA_CONFIG',11,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-22 21:58:52'),(158,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',11,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-22 21:58:52'),(159,1,'DELETE_SLA_CONFIG','SLA_CONFIG',11,NULL,NULL,'Xóa cấu hình SLA #11',NULL,NULL,'2026-09-22 21:58:52'),(160,1,'CREATE_USER','USER',47,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-22 22:29:51'),(161,1,'UPDATE_USER','USER',47,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:29:51'),(162,1,'TOGGLE_USER_STATUS','USER',47,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:29:51'),(163,1,'DELETE_USER','USER',47,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:29:51'),(164,1,'CREATE_CATEGORY','CATEGORY',14,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-22 22:29:51'),(165,1,'UPDATE_CATEGORY','CATEGORY',14,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-22 22:29:51'),(166,1,'CREATE_SERVICE','SERVICE',21,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-22 22:29:51'),(167,1,'UPDATE_SERVICE','SERVICE',21,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-22 22:29:51'),(168,1,'DELETE_SERVICE','SERVICE',21,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-22 22:29:51'),(169,1,'DELETE_CATEGORY','CATEGORY',14,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-22 22:29:51'),(170,1,'CREATE_IT_GROUP','IT_GROUP',15,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-22 22:29:51'),(171,1,'UPDATE_IT_GROUP','IT_GROUP',15,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-22 22:29:51'),(172,1,'DELETE_IT_GROUP','IT_GROUP',15,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-22 22:29:51'),(173,1,'CREATE_SLA_CONFIG','SLA_CONFIG',12,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-22 22:29:51'),(174,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',12,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-22 22:29:51'),(175,1,'DELETE_SLA_CONFIG','SLA_CONFIG',12,NULL,NULL,'Xóa cấu hình SLA #12',NULL,NULL,'2026-09-22 22:29:51'),(176,1,'CREATE_USER','USER',48,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-22 22:29:56'),(177,1,'UPDATE_USER','USER',48,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:29:56'),(178,1,'TOGGLE_USER_STATUS','USER',48,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:29:56'),(179,1,'DELETE_USER','USER',48,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:29:56'),(180,1,'CREATE_CATEGORY','CATEGORY',15,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-22 22:29:56'),(181,1,'UPDATE_CATEGORY','CATEGORY',15,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-22 22:29:56'),(182,1,'CREATE_SERVICE','SERVICE',22,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-22 22:29:56'),(183,1,'UPDATE_SERVICE','SERVICE',22,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-22 22:29:56'),(184,1,'DELETE_SERVICE','SERVICE',22,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-22 22:29:56'),(185,1,'DELETE_CATEGORY','CATEGORY',15,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-22 22:29:56'),(186,1,'CREATE_IT_GROUP','IT_GROUP',16,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-22 22:29:56'),(187,1,'UPDATE_IT_GROUP','IT_GROUP',16,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-22 22:29:56'),(188,1,'DELETE_IT_GROUP','IT_GROUP',16,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-22 22:29:56'),(189,1,'CREATE_SLA_CONFIG','SLA_CONFIG',13,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-22 22:29:56'),(190,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',13,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-22 22:29:56'),(191,1,'DELETE_SLA_CONFIG','SLA_CONFIG',13,NULL,NULL,'Xóa cấu hình SLA #13',NULL,NULL,'2026-09-22 22:29:56'),(192,1,'CREATE_USER','USER',49,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-09-22 22:45:16'),(193,1,'UPDATE_USER','USER',49,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:45:16'),(194,1,'TOGGLE_USER_STATUS','USER',49,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:45:16'),(195,1,'DELETE_USER','USER',49,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-09-22 22:45:16'),(196,1,'CREATE_CATEGORY','CATEGORY',16,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-09-22 22:45:16'),(197,1,'UPDATE_CATEGORY','CATEGORY',16,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-22 22:45:16'),(198,1,'CREATE_SERVICE','SERVICE',23,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-09-22 22:45:16'),(199,1,'UPDATE_SERVICE','SERVICE',23,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-22 22:45:16'),(200,1,'DELETE_SERVICE','SERVICE',23,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-09-22 22:45:16'),(201,1,'DELETE_CATEGORY','CATEGORY',16,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-09-22 22:45:16'),(202,1,'CREATE_IT_GROUP','IT_GROUP',17,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-09-22 22:45:16'),(203,1,'UPDATE_IT_GROUP','IT_GROUP',17,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-22 22:45:16'),(204,1,'DELETE_IT_GROUP','IT_GROUP',17,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-09-22 22:45:16'),(205,1,'CREATE_SLA_CONFIG','SLA_CONFIG',14,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-09-22 22:45:16'),(206,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',14,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-09-22 22:45:16'),(207,1,'DELETE_SLA_CONFIG','SLA_CONFIG',14,NULL,NULL,'Xóa cấu hình SLA #14',NULL,NULL,'2026-09-22 22:45:16'),(208,2,'ADJUST_PRIORITY','TICKET',51,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-22 22:45:22'),(209,3,'MARK_DUPLICATE','TICKET',51,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-22 22:45:22'),(210,6,'CANCEL_TICKET','TICKET',52,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-22 22:45:22'),(211,1,'CREATE_BACKUP','BACKUP',NULL,NULL,'backup_20260922_231204.sql','Tạo bản sao lưu CSDL (46 bảng, 255822 bytes)',NULL,NULL,'2026-09-22 23:12:05'),(212,2,'ADJUST_PRIORITY','TICKET',55,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-26 10:45:56'),(213,3,'MARK_DUPLICATE','TICKET',55,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-26 10:45:56'),(214,6,'CANCEL_TICKET','TICKET',56,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-26 10:45:56'),(215,2,'ADJUST_PRIORITY','TICKET',57,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-09-26 10:54:41'),(216,3,'MARK_DUPLICATE','TICKET',57,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-09-26 10:54:41'),(217,6,'CANCEL_TICKET','TICKET',58,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-09-26 10:54:41'),(218,1,'UPDATE_PERMISSION_MATRIX','PERMISSIONS',NULL,NULL,'59 permissions','Cập nhật ma trận phân quyền RBAC',NULL,NULL,'2026-09-26 11:56:43'),(219,2,'ADJUST_PRIORITY','TICKET',59,'Khẩn cấp','Thường','Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc',NULL,NULL,'2026-10-26 17:15:58'),(220,3,'MARK_DUPLICATE','TICKET',59,'IN_PROGRESS','DUPLICATE OF #IT-000001','Trùng với sự cố mạng LAN IT-000001',NULL,NULL,'2026-10-26 17:15:58'),(221,6,'CANCEL_TICKET','TICKET',60,'NEW','CANCELLED','Người dùng tự khắc phục được lỗi',NULL,NULL,'2026-10-26 17:15:58'),(222,1,'CREATE_USER','USER',50,NULL,'test_crud_user','Tạo tài khoản test_crud_user (Test CRUD User)',NULL,NULL,'2026-10-26 17:19:59'),(223,1,'UPDATE_USER','USER',50,'Test CRUD User','Test CRUD User (Updated)','Cập nhật tài khoản test_crud_user',NULL,NULL,'2026-10-26 17:19:59'),(224,1,'TOGGLE_USER_STATUS','USER',50,'1','0','Đã khóa tài khoản test_crud_user',NULL,NULL,'2026-10-26 17:19:59'),(225,1,'DELETE_USER','USER',50,'test_crud_user',NULL,'Xóa tài khoản test_crud_user',NULL,NULL,'2026-10-26 17:19:59'),(226,1,'CREATE_CATEGORY','CATEGORY',17,NULL,'Test Danh Mục CRUD','Tạo danh mục mới Test Danh Mục CRUD (CAT_TEST_CRUD)',NULL,NULL,'2026-10-26 17:19:59'),(227,1,'UPDATE_CATEGORY','CATEGORY',17,NULL,'Test Danh Mục CRUD (Sửa)','Cập nhật danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-10-26 17:19:59'),(228,1,'CREATE_SERVICE','SERVICE',24,NULL,'Test Dịch Vụ Con','Tạo dịch vụ con Test Dịch Vụ Con',NULL,NULL,'2026-10-26 17:19:59'),(229,1,'UPDATE_SERVICE','SERVICE',24,NULL,'Test Dịch Vụ Con (Sửa)','Cập nhật dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-10-26 17:19:59'),(230,1,'DELETE_SERVICE','SERVICE',24,'Test Dịch Vụ Con (Sửa)',NULL,'Xóa dịch vụ Test Dịch Vụ Con (Sửa)',NULL,NULL,'2026-10-26 17:19:59'),(231,1,'DELETE_CATEGORY','CATEGORY',17,'Test Danh Mục CRUD (Sửa)',NULL,'Xóa danh mục Test Danh Mục CRUD (Sửa)',NULL,NULL,'2026-10-26 17:19:59'),(232,1,'CREATE_IT_GROUP','IT_GROUP',18,NULL,'Nhóm Test CRUD IT','Tạo nhóm IT mới Nhóm Test CRUD IT (GRP_TEST_CRUD)',NULL,NULL,'2026-10-26 17:19:59'),(233,1,'UPDATE_IT_GROUP','IT_GROUP',18,NULL,'Nhóm Test CRUD IT (Sửa)','Cập nhật nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-10-26 17:19:59'),(234,1,'DELETE_IT_GROUP','IT_GROUP',18,'Nhóm Test CRUD IT (Sửa)',NULL,'Xóa nhóm IT Nhóm Test CRUD IT (Sửa)',NULL,NULL,'2026-10-26 17:19:59'),(235,1,'CREATE_SLA_CONFIG','SLA_CONFIG',15,NULL,'SLA Test CRUD 12 Giờ','Thêm cấu hình SLA SLA Test CRUD 12 Giờ',NULL,NULL,'2026-10-26 17:19:59'),(236,1,'UPDATE_SLA_CONFIG','SLA_CONFIG',15,NULL,'SLA Test CRUD 16 Giờ','Cập nhật cấu hình SLA SLA Test CRUD 16 Giờ',NULL,NULL,'2026-10-26 17:19:59'),(237,1,'DELETE_SLA_CONFIG','SLA_CONFIG',15,NULL,NULL,'Xóa cấu hình SLA #15',NULL,NULL,'2026-10-26 17:19:59');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `change_requests`
--

DROP TABLE IF EXISTS `change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `change_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `change_code` varchar(30) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `reason` text NOT NULL,
  `change_type` enum('STANDARD','NORMAL','EMERGENCY') NOT NULL DEFAULT 'NORMAL',
  `risk_level` enum('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL DEFAULT 'MEDIUM',
  `environment_stage` enum('DEV','TEST','MIG_UAT','RELEASE_PROD') NOT NULL DEFAULT 'DEV',
  `status` enum('DRAFT','SUBMITTED','CAB_APPROVAL','SCHEDULED','IMPLEMENTING','TESTING_UAT','RELEASE_PROD','SUCCESSFUL','FAILED','ROLLED_BACK','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  `implementation_plan` text DEFAULT NULL,
  `rollback_plan` text DEFAULT NULL,
  `test_plan` text DEFAULT NULL,
  `scheduled_start` datetime DEFAULT NULL,
  `scheduled_end` datetime DEFAULT NULL,
  `actual_start` datetime DEFAULT NULL,
  `actual_end` datetime DEFAULT NULL,
  `requested_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `cab_approved_by` int(11) DEFAULT NULL,
  `cab_approved_at` datetime DEFAULT NULL,
  `cab_note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `change_code` (`change_code`),
  KEY `idx_cr_status` (`status`),
  KEY `idx_cr_env` (`environment_stage`),
  KEY `idx_cr_req` (`requested_by`),
  KEY `idx_cr_ass` (`assigned_to`),
  KEY `fk_cr_cab` (`cab_approved_by`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `change_requests`
--

LOCK TABLES `change_requests` WRITE;
/*!40000 ALTER TABLE `change_requests` DISABLE KEYS */;
INSERT INTO `change_requests` VALUES (1,'CHG-000001','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','DEV','DRAFT','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,NULL,3,3,NULL,NULL,NULL,'2026-09-21 20:28:59','2026-09-21 20:28:59'),(2,'CHG-000002','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 20:29:17',3,3,1,'2026-09-21 20:29:17','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 20:29:17','2026-09-21 20:29:17'),(3,'CHG-000003','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 20:29:52',3,3,1,'2026-09-21 20:29:52','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 20:29:52','2026-09-21 20:29:52'),(4,'CHG-000004','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 20:31:06',3,3,1,'2026-09-21 20:31:06','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 20:31:06','2026-09-21 20:31:06'),(5,'CHG-000005','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 20:38:17',3,3,1,'2026-09-21 20:38:17','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 20:38:17','2026-09-21 20:38:17'),(6,'CHG-000006','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 21:10:46',3,3,1,'2026-09-21 21:10:46','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 21:10:46','2026-09-21 21:10:46'),(7,'CHG-000007','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 21:37:18',3,3,1,'2026-09-21 21:37:18','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 21:37:18','2026-09-21 21:37:18'),(8,'CHG-000008','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 21:47:53',3,3,1,'2026-09-21 21:47:53','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 21:47:53','2026-09-21 21:47:53'),(9,'CHG-000009','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 21:58:34',3,3,1,'2026-09-21 21:58:34','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 21:58:34','2026-09-21 21:58:34'),(10,'CHG-000010','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 22:06:14',3,3,1,'2026-09-21 22:06:14','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 22:06:14','2026-09-21 22:06:14'),(11,'CHG-000011','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-21 22:33:39',3,3,1,'2026-09-21 22:33:39','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-21 22:33:39','2026-09-21 22:33:39'),(12,'CHG-000012','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-09-22 21:58:52',3,3,1,'2026-09-22 21:58:52','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-09-22 21:58:52','2026-09-22 21:58:52'),(13,'CHG-000013','Nâng cấp cụm Database MySQL Cluster lên 8.0.36','Cập nhật phiên bản MySQL trên 3 node Master-Slave.','Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.','NORMAL','HIGH','RELEASE_PROD','SUCCESSFUL','1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.','Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.','Chạy test suite tự động 250 API tests trên Staging.',NULL,NULL,NULL,'2026-10-26 17:16:07',3,3,1,'2026-10-26 17:16:07','Hội đồng CAB đồng ý lịch bảo trì cuối tuần','2026-10-26 17:16:07','2026-10-26 17:16:07');
/*!40000 ALTER TABLE `change_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `change_tickets`
--

DROP TABLE IF EXISTS `change_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `change_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `change_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_change_ticket` (`change_id`,`ticket_id`),
  KEY `idx_ct_ticket` (`ticket_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `change_tickets`
--

LOCK TABLES `change_tickets` WRITE;
/*!40000 ALTER TABLE `change_tickets` DISABLE KEYS */;
INSERT INTO `change_tickets` VALUES (1,1,20,'2026-09-21 20:28:59'),(2,2,22,'2026-09-21 20:29:17'),(3,3,26,'2026-09-21 20:29:52'),(4,4,28,'2026-09-21 20:31:06'),(5,5,32,'2026-09-21 20:38:17'),(6,6,34,'2026-09-21 21:10:46'),(7,7,38,'2026-09-21 21:37:18'),(8,8,42,'2026-09-21 21:47:53'),(9,9,44,'2026-09-21 21:58:34'),(10,10,46,'2026-09-21 22:06:14'),(11,11,48,'2026-09-21 22:33:39'),(12,12,50,'2026-09-22 21:58:52'),(13,13,62,'2026-10-26 17:16:07');
/*!40000 ALTER TABLE `change_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `it_group_members`
--

DROP TABLE IF EXISTS `it_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `it_group_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_user` (`group_id`,`user_id`),
  KEY `fk_igm_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `it_group_members`
--

LOCK TABLES `it_group_members` WRITE;
/*!40000 ALTER TABLE `it_group_members` DISABLE KEYS */;
INSERT INTO `it_group_members` VALUES (1,1,2,0,'2026-09-20 08:53:24'),(2,1,3,1,'2026-09-20 08:53:24'),(3,2,4,1,'2026-09-20 08:53:24'),(4,4,5,1,'2026-09-20 08:53:24'),(6,1,27,1,'2026-09-22 22:21:58'),(7,3,28,1,'2026-09-22 22:21:58'),(8,1,29,1,'2026-09-22 22:21:58'),(9,2,30,1,'2026-09-22 22:21:58'),(10,3,31,1,'2026-09-22 22:21:58'),(11,4,32,1,'2026-09-22 22:21:58'),(12,4,33,1,'2026-09-22 22:21:58'),(13,1,34,1,'2026-09-22 22:21:58'),(14,1,35,1,'2026-09-22 22:21:58'),(15,5,36,1,'2026-09-22 22:21:58'),(16,2,37,1,'2026-09-22 22:21:58'),(17,2,38,1,'2026-09-22 22:21:58'),(18,3,39,1,'2026-09-22 22:21:58'),(19,3,40,1,'2026-09-22 22:21:58'),(20,4,41,1,'2026-09-22 22:21:58'),(21,4,42,1,'2026-09-22 22:21:58'),(22,4,43,1,'2026-09-22 22:21:58'),(23,4,44,1,'2026-09-22 22:21:58'),(24,2,45,1,'2026-09-22 22:21:58'),(25,3,46,1,'2026-09-22 22:21:58');
/*!40000 ALTER TABLE `it_group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `it_groups`
--

DROP TABLE IF EXISTS `it_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `it_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `leader_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_group_leader` (`leader_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `it_groups`
--

LOCK TABLES `it_groups` WRITE;
/*!40000 ALTER TABLE `it_groups` DISABLE KEYS */;
INSERT INTO `it_groups` VALUES (1,'HELPDESK','IT Helpdesk','Hỗ trợ kỹ thuật người dùng cuối, sự cố văn phòng và tài khoản',2,1,'2026-09-20 08:53:24'),(2,'NETWORK','IT Network & Hạ tầng','Quản lý mạng LAN, WiFi, VPN, Firewall, Router',2,1,'2026-09-20 08:53:24'),(3,'SYSTEM','IT Hệ thống máy chủ','Quản trị Servers, Cloud, Virtualization, Backup',2,1,'2026-09-20 08:53:24'),(4,'APPLICATION','IT Phần mềm & Ứng dụng','Bảo trì ERP, CRM, HRM, Website nội bộ',2,1,'2026-09-20 08:53:24'),(5,'HARDWARE','IT Phần cứng & Thiết bị','Bảo dưỡng, sửa chữa máy tính, máy in, scan',2,1,'2026-09-20 08:53:24');
/*!40000 ALTER TABLE `it_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jump_command_logs`
--

DROP TABLE IF EXISTS `jump_command_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jump_command_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `command` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_jcl_session` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jump_command_logs`
--

LOCK TABLES `jump_command_logs` WRITE;
/*!40000 ALTER TABLE `jump_command_logs` DISABLE KEYS */;
INSERT INTO `jump_command_logs` VALUES (1,5,1,3,'docker ps','2026-09-21 21:47:44'),(2,5,1,3,'df -h','2026-09-21 21:47:44'),(3,6,2,1,'systemctl status nginx','2026-09-21 21:52:34'),(4,6,2,1,'docker ps','2026-09-21 21:52:34'),(5,8,3,1,'slect','2026-09-21 21:52:47'),(6,8,3,1,'help','2026-09-21 21:52:52'),(7,16,1,3,'docker ps','2026-09-21 21:57:37'),(8,16,1,3,'df -h','2026-09-21 21:57:37'),(9,18,1,3,'docker ps','2026-09-21 21:58:06'),(10,18,1,3,'df -h','2026-09-21 21:58:06'),(11,20,1,3,'docker ps','2026-09-21 21:58:12'),(12,20,1,3,'df -h','2026-09-21 21:58:12'),(13,22,1,3,'docker ps','2026-09-21 21:58:26'),(14,22,1,3,'df -h','2026-09-21 21:58:26'),(15,23,3,1,'SELECT id, code, title, status, priority FROM tickets LIMIT 5;','2026-09-21 21:59:54'),(16,23,3,1,'SELECT id, code, title, status, priority FROM tickets LIMIT 5;','2026-09-21 21:59:55'),(17,23,3,1,'SELECT id, code, title, status, priority FROM tickets LIMIT 5;','2026-09-21 21:59:56'),(18,23,3,1,'select * From user','2026-09-21 22:00:04'),(19,23,3,1,'select  * from users','2026-09-21 22:00:15'),(20,23,3,1,'select top 1 * from useres','2026-09-21 22:00:34'),(21,24,4,1,'free -m','2026-09-21 22:00:46'),(22,24,4,1,'mysql -u root -p','2026-09-21 22:00:48'),(23,24,4,1,'SELECT id, username, full_name, email FROM users LIMIT 5;','2026-09-21 22:00:52'),(24,28,1,3,'docker ps','2026-09-21 22:03:37'),(25,28,1,3,'df -h','2026-09-21 22:03:37'),(26,30,1,3,'docker ps','2026-09-21 22:05:03'),(27,30,1,3,'df -h','2026-09-21 22:05:03'),(28,32,1,3,'docker ps','2026-09-21 22:06:06'),(29,32,1,3,'df -h','2026-09-21 22:06:06'),(30,33,3,1,'SELECT id, code, title, status, priority FROM tickets LIMIT 5;','2026-09-21 22:08:09'),(31,33,3,1,'select','2026-09-21 22:08:16'),(32,33,3,1,'SELECT * FROM users LIMIT 5;','2026-09-21 22:08:21'),(33,33,3,1,'select * From','2026-09-21 22:08:28'),(34,34,3,1,'SELECT * FROM users LIMIT 5;','2026-09-21 22:28:43'),(35,34,3,1,'SELECT * FROM users LIMIT 5;','2026-09-21 22:28:46'),(36,34,3,1,'SELECT id, code, title, status, priority FROM tickets LIMIT 5;','2026-09-21 22:28:50'),(37,36,1,3,'docker ps','2026-09-21 22:30:53'),(38,36,1,3,'df -h','2026-09-21 22:30:53'),(39,38,1,3,'docker ps','2026-09-21 22:33:33'),(40,38,1,3,'df -h','2026-09-21 22:33:33'),(41,39,3,1,'select * FROM users','2026-09-21 22:49:35'),(42,39,3,1,'update from users  set username  = admin1 where id = 1','2026-09-21 22:50:04'),(43,39,3,1,'select * FROM users','2026-09-21 22:50:09'),(44,41,1,3,'docker ps','2026-09-22 21:58:52'),(45,41,1,3,'df -h','2026-09-22 21:58:52'),(46,43,1,3,'docker ps','2026-09-22 22:13:05'),(47,43,1,3,'df -h','2026-09-22 22:13:05'),(48,45,1,3,'docker ps','2026-09-22 22:29:41'),(49,45,1,3,'df -h','2026-09-22 22:29:41'),(50,47,1,3,'docker ps','2026-09-22 22:29:56'),(51,47,1,3,'df -h','2026-09-22 22:29:56'),(52,49,1,3,'docker ps','2026-09-22 22:45:09'),(53,49,1,3,'df -h','2026-09-22 22:45:09'),(54,51,1,3,'docker ps','2026-09-22 23:05:38'),(55,51,1,3,'df -h','2026-09-22 23:05:38'),(56,53,1,3,'docker ps','2026-10-26 17:16:11'),(57,53,1,3,'df -h','2026-10-26 17:16:11'),(58,56,3,1,'select * from users  LIMIT  100','2026-10-27 22:04:41'),(59,57,2,1,'SELECT id, username, full_name, email FROM users LIMIT 5;','2026-10-27 22:05:32'),(60,58,4,1,'git status','2026-10-27 22:05:56'),(61,58,4,1,'ping 8.8.8.8','2026-10-27 22:06:01');
/*!40000 ALTER TABLE `jump_command_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jump_server_permissions`
--

DROP TABLE IF EXISTS `jump_server_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jump_server_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `granted_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_jsp_server` (`server_id`),
  KEY `fk_jsp_user` (`user_id`),
  KEY `fk_jsp_group` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jump_server_permissions`
--

LOCK TABLES `jump_server_permissions` WRITE;
/*!40000 ALTER TABLE `jump_server_permissions` DISABLE KEYS */;
INSERT INTO `jump_server_permissions` VALUES (3,2,2,NULL,1,'2026-09-21 21:42:50'),(4,3,3,NULL,1,'2026-09-21 21:42:50'),(5,3,2,NULL,1,'2026-09-21 21:42:50'),(6,4,2,NULL,1,'2026-09-21 21:42:50'),(24,1,2,NULL,1,'2026-10-26 17:16:11');
/*!40000 ALTER TABLE `jump_server_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jump_server_sessions`
--

DROP TABLE IF EXISTS `jump_server_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jump_server_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_token` varchar(64) NOT NULL,
  `server_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `client_ip` varchar(50) DEFAULT NULL,
  `status` enum('ACTIVE','KICKED','TERMINATED') NOT NULL DEFAULT 'ACTIVE',
  `started_at` datetime DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL,
  `last_heartbeat_at` datetime DEFAULT current_timestamp(),
  `kicked_by_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `fk_jss_server` (`server_id`),
  KEY `fk_jss_user` (`user_id`),
  KEY `fk_jss_kicked_by` (`kicked_by_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jump_server_sessions`
--

LOCK TABLES `jump_server_sessions` WRITE;
/*!40000 ALTER TABLE `jump_server_sessions` DISABLE KEYS */;
INSERT INTO `jump_server_sessions` VALUES (1,'009a18abb5544a97f747e2a69846df1566586aa0a7621c0c51539e53413ed47b',1,1,'::1','KICKED','2026-09-21 21:44:52','2026-09-21 21:47:08','2026-09-21 21:44:52',2),(2,'cc3bc468a01f3bb1b0ac6d2afda6eb92daab5e4f24769c502ab2345857133eb4',1,2,'192.168.1.50','KICKED','2026-09-21 21:47:08','2026-09-21 21:47:08','2026-09-21 21:47:08',3),(3,'5c54a82a02b07342dcea41f4f1b45c371db4a2542650de3ac61e682f685ac3eb',1,3,'192.168.1.88','KICKED','2026-09-21 21:47:08','2026-09-21 21:47:44','2026-09-21 21:47:08',2),(4,'d8fa23a89e5c311585b612237c14ca601fc5b72884b4e8f88486a71f82e4358b',1,2,'192.168.1.50','KICKED','2026-09-21 21:47:44','2026-09-21 21:47:44','2026-09-21 21:47:44',3),(5,'977e16994b34c1ce0d9cb7e8c782fb4a0d84a201ba156bfdf116a7158c8e63fe',1,3,'192.168.1.88','KICKED','2026-09-21 21:47:44','2026-09-21 21:47:44','2026-09-21 21:47:44',1),(6,'53ced02bf3d63bbfe1b71b20d0e65703e58f25d4ebd1081bbfb9039a7f150363',2,1,'::1','TERMINATED','2026-09-21 21:52:32','2026-09-21 21:52:37','2026-09-21 21:52:37',NULL),(7,'a3e94bc580cf9074562e679fb79975e71b3090a45c8493ce2fab8b61ab9cca59',1,1,'::1','TERMINATED','2026-09-21 21:52:38','2026-09-21 21:52:40','2026-09-21 21:52:38',NULL),(8,'a76406d2e050338682b1523adf60938658f535c5d01d1534eb13e3c1d53dea94',3,1,'::1','KICKED','2026-09-21 21:52:42','2026-09-21 21:53:03','2026-09-21 21:53:02',1),(9,'e21a0b1703f14b88ccff5133c05545e53465a94f71070dce166439cabdcde2e6',3,1,'::1','KICKED','2026-09-21 21:53:03','2026-09-21 21:53:04','2026-09-21 21:53:03',1),(10,'ddbb40faaa1b0925c26d4ad96808c98b53f3a1331cf33b29ab027bf38e8b178b',3,1,'::1','KICKED','2026-09-21 21:53:04','2026-09-21 21:53:04','2026-09-21 21:53:04',1),(11,'c7a7526a80905a4e3b8a49fed6ca0234721caba2c7357df8ba23c7dfce6f1c00',3,1,'::1','KICKED','2026-09-21 21:53:04','2026-09-21 21:56:26','2026-09-21 21:53:04',3),(12,'636bf021df6bf743c9fd971ccb1c21dd38e0dfcca0ce62df7eb7b449573240c9',1,1,'::1','TERMINATED','2026-09-21 21:55:57','2026-09-21 21:55:59','2026-09-21 21:55:57',NULL),(13,'ac738fd66cdb4ab0fdc6992bffb716375300da6c66934537db7e84111f7fa5d0',3,3,'::1','KICKED','2026-09-21 21:56:26','2026-09-21 21:56:35','2026-09-21 21:56:34',1),(14,'1f6cd4547de12d32ea375266354e3a976046e6ae332c18866e371c81f484b62b',3,1,'::1','TERMINATED','2026-09-21 21:56:35','2026-09-21 21:56:51','2026-09-21 21:56:48',NULL),(15,'432d02427c6f8df95cd49e9fa0977e0253d066970fccc776250d35c996a8ba53',1,2,'192.168.1.50','KICKED','2026-09-21 21:57:37','2026-09-21 21:57:37','2026-09-21 21:57:37',3),(16,'8575d1dcb85468c9f8618cd2e7b0abb1af21e357a2fa0ccbd9a3f68c6a9c3498',1,3,'192.168.1.88','KICKED','2026-09-21 21:57:37','2026-09-21 21:57:37','2026-09-21 21:57:37',1),(17,'4b35ec25477d3a9bf7ce54dbc633267913b66adbea4c3b2fe9f4b4016c7958c8',1,2,'192.168.1.50','KICKED','2026-09-21 21:58:06','2026-09-21 21:58:06','2026-09-21 21:58:06',3),(18,'12bd901e4677f49a105d8e3115569b4a66b866713d186b799483bce4172acc18',1,3,'192.168.1.88','KICKED','2026-09-21 21:58:06','2026-09-21 21:58:06','2026-09-21 21:58:06',1),(19,'68b5126f8c683dbf210407ba0181c7e59b6268e5e928f24ddca1a55f380ae3f1',1,2,'192.168.1.50','KICKED','2026-09-21 21:58:12','2026-09-21 21:58:12','2026-09-21 21:58:12',3),(20,'ba47c47d972e6e2cdd013f3fdce8c17e9d8988f9f8c8659fc5103d2e4034ca2d',1,3,'192.168.1.88','KICKED','2026-09-21 21:58:12','2026-09-21 21:58:12','2026-09-21 21:58:12',1),(21,'f9bc4eb30f322c9ebaa3c7ca2dfeb89030f97e27a596fecd33c227090937ffbf',1,2,'192.168.1.50','KICKED','2026-09-21 21:58:26','2026-09-21 21:58:26','2026-09-21 21:58:26',3),(22,'7368797603631344dcf1aa2104c46c54fe8c71ef911f6773e34bb5435d8573de',1,3,'192.168.1.88','KICKED','2026-09-21 21:58:26','2026-09-21 21:58:26','2026-09-21 21:58:26',1),(23,'09c04817620d2b40642ba75c77d8aaa5667d8e63087b57786225778b4e2084b1',3,1,'::1','KICKED','2026-09-21 21:59:44','2026-09-21 22:04:02','2026-09-21 22:00:42',1),(24,'e152596f9d1ba9285c146af31034b18fb91a7a832ea0ba81f736bc56f3680795',4,1,'::1','TERMINATED','2026-09-21 22:00:45','2026-09-21 22:00:56','2026-09-21 22:00:52',NULL),(25,'d4f941d9ac2a06d00ee12ef15bb8eac5775e72aa6a43cfc5369e23e827236a0f',1,1,'::1','TERMINATED','2026-09-21 22:03:30','2026-09-21 22:03:32','2026-09-21 22:03:30',NULL),(26,'d9e436218c302cdc9c13097097f28dd5a445aee712db383439a30d918e3b4b62',1,1,'::1','KICKED','2026-09-21 22:03:34','2026-09-21 22:03:37','2026-09-21 22:03:37',2),(27,'3fe8ea0819a003a2764eac3dbb4fdf3c9afec572351cbebaf418a0c23cc64ab5',1,2,'192.168.1.50','KICKED','2026-09-21 22:03:37','2026-09-21 22:03:37','2026-09-21 22:03:37',3),(28,'c35e3621636830ca71f2f4954125792d344155352c3bd8a25b430d9a4fc83656',1,3,'192.168.1.88','KICKED','2026-09-21 22:03:37','2026-09-21 22:03:37','2026-09-21 22:03:37',1),(29,'c78f427c3967b9adac6097967b49d06de87dafacf4752a0e037d4530910fce86',1,2,'192.168.1.50','KICKED','2026-09-21 22:05:03','2026-09-21 22:05:03','2026-09-21 22:05:03',3),(30,'a3c0471d91c57214e2e97935c3326518544acf3cd056119f4216e0a857b274aa',1,3,'192.168.1.88','KICKED','2026-09-21 22:05:03','2026-09-21 22:05:03','2026-09-21 22:05:03',1),(31,'ffbe5054ad21c4cf787a84fbb8fefa8c9e2082792170565d034ec027a9dedb12',1,2,'192.168.1.50','KICKED','2026-09-21 22:06:06','2026-09-21 22:06:06','2026-09-21 22:06:06',3),(32,'2e981fb443207a8b0bca68b507a1df38d55bf7430b37c9664b8cc1371b0ce3c8',1,3,'192.168.1.88','KICKED','2026-09-21 22:06:06','2026-09-21 22:06:06','2026-09-21 22:06:06',1),(33,'123b0288344b5d2b3a4e00ea5643ad5d5fd2595d93ad19c5c9f02a5e0af0bcbd',3,1,'::1','KICKED','2026-09-21 22:08:06','2026-09-21 22:28:39','2026-09-21 22:28:37',1),(34,'8a9bcae97773994271148a04dd12df0221922f9dcd79384df526b8d06e273948',3,1,'::1','KICKED','2026-09-21 22:28:39','2026-09-21 22:49:16','2026-09-21 22:49:15',1),(35,'f4a72eb5e3910075abb3e0ccfeba39144db356e1e5fa8e7acf06f67706b38071',1,2,'192.168.1.50','KICKED','2026-09-21 22:30:53','2026-09-21 22:30:53','2026-09-21 22:30:53',3),(36,'c304a092a10a6eac1620d6f9f21c3d16d7d2afbaa0beeeccdccdacf0aedde0c0',1,3,'192.168.1.88','KICKED','2026-09-21 22:30:53','2026-09-21 22:30:53','2026-09-21 22:30:53',1),(37,'0abd183ffe1fea4c5f99964f6513ab302773d57b6276c0398ae0a1d19bfd0834',1,2,'192.168.1.50','KICKED','2026-09-21 22:33:33','2026-09-21 22:33:33','2026-09-21 22:33:33',3),(38,'c21f268332105aa6b2d38414ad2edde51245945f7cbf0a906d0ece545f668841',1,3,'192.168.1.88','KICKED','2026-09-21 22:33:33','2026-09-21 22:33:33','2026-09-21 22:33:33',1),(39,'9bcd0462cf9d6bc07a348e1bb550c0eb54ec0618771f709ee81fe336e74e967e',3,1,'::1','KICKED','2026-09-21 22:49:16','2026-10-27 22:03:59','2026-09-22 16:05:56',1),(40,'fda5f04b191c292744e8d9a9ac506e704202678099c4c7913f22e2d435dbf361',1,2,'192.168.1.50','KICKED','2026-09-22 21:58:52','2026-09-22 21:58:52','2026-09-22 21:58:52',3),(41,'a241bc148218636950364c16f879a7d72982fbf6afcb535d33e0f3610f9b59e0',1,3,'192.168.1.88','KICKED','2026-09-22 21:58:52','2026-09-22 21:58:52','2026-09-22 21:58:52',1),(42,'becea7b2d08d7e1f6049d029fc3a964542b34358502b663fceaa7dcc16b80d3c',1,2,'192.168.1.50','KICKED','2026-09-22 22:13:05','2026-09-22 22:13:05','2026-09-22 22:13:05',3),(43,'caef540e23d4bc11d3c50f41de9aecfaa125170566fff40cc5c79a41e8ba3f05',1,3,'192.168.1.88','KICKED','2026-09-22 22:13:05','2026-09-22 22:13:05','2026-09-22 22:13:05',1),(44,'b3772d94d300618c7dce7309c8a275cf9ced9f049b337097b7c2f9c1eb94392f',1,2,'192.168.1.50','KICKED','2026-09-22 22:29:41','2026-09-22 22:29:41','2026-09-22 22:29:41',3),(45,'948011769d7fd563b7d4af7fbbcf9d4b8fda7f091ffee67a549a18264c2af687',1,3,'192.168.1.88','KICKED','2026-09-22 22:29:41','2026-09-22 22:29:41','2026-09-22 22:29:41',1),(46,'cc33a9f9711e0c5061ea118004f2ca5b5cb60e1014cc6d92fce4cb7c32dd9f16',1,2,'192.168.1.50','KICKED','2026-09-22 22:29:56','2026-09-22 22:29:56','2026-09-22 22:29:56',3),(47,'9fe4d0fd0d8fee19f88ba2d39feeaed20cca9d553aae5267b9de20a7381f1c06',1,3,'192.168.1.88','KICKED','2026-09-22 22:29:56','2026-09-22 22:29:56','2026-09-22 22:29:56',1),(48,'affb06042d59549b845996cbc16ac650d8989f8ba3350919c07430066c521737',1,2,'192.168.1.50','KICKED','2026-09-22 22:45:09','2026-09-22 22:45:09','2026-09-22 22:45:09',3),(49,'241b53656d0027e77b7d44643333cd90189a02911c348a4b0c7462ae644494f1',1,3,'192.168.1.88','KICKED','2026-09-22 22:45:09','2026-09-22 22:45:09','2026-09-22 22:45:09',1),(50,'8b5fbb942fdb88f6683aabcd6cca75d1121cdf7081a8b177003a6c8f07460c81',1,2,'192.168.1.50','KICKED','2026-09-22 23:05:38','2026-09-22 23:05:38','2026-09-22 23:05:38',3),(51,'4bcdf21fc31ce8e268fd4662dd713046ab99048cf02f3a3a04726e5579fc3880',1,3,'192.168.1.88','KICKED','2026-09-22 23:05:38','2026-09-22 23:05:38','2026-09-22 23:05:38',1),(52,'3599690c8a174e79cf0d29a0dc050f0f826348c0f7699da6bb1bcea9c3ee76a4',1,2,'192.168.1.50','KICKED','2026-10-26 17:16:11','2026-10-26 17:16:11','2026-10-26 17:16:11',3),(53,'1b6f0370f85689fb18ee37a5cc5b9edb606f404b8d2027dc5a03136d740f98f4',1,3,'192.168.1.88','KICKED','2026-10-26 17:16:11','2026-10-26 17:16:11','2026-10-26 17:16:11',1),(54,'a2dcc13854358ce0757657d24a5005fc0484f457eb88653b02de4a4b5060ed27',1,1,'::1','TERMINATED','2026-10-27 22:03:49','2026-10-27 22:03:53','2026-10-27 22:03:52',NULL),(55,'a6118bcc822ec2d5c8c8612de7ee623a5a24d86297cc472907154fe0607c9bbd',3,1,'::1','KICKED','2026-10-27 22:03:59','2026-10-27 22:04:21','2026-10-27 22:04:20',1),(56,'4267a616222712845176e868b79f60372a7b1f22c338c80e95c2fd3f2e24b662',3,1,'::1','ACTIVE','2026-10-27 22:04:21',NULL,'2026-10-27 22:05:17',NULL),(57,'ddbcd3c33807d064669e8b6b26acf342790fda9a7727a02cbd43a4e674d0dca2',2,1,'::1','TERMINATED','2026-10-27 22:05:26','2026-10-27 22:05:49','2026-10-27 22:05:49',NULL),(58,'597475c7f8526040846e7ed2fdfb1a550e7d1dc2296af3fc96db6a82009024cd',4,1,'::1','TERMINATED','2026-10-27 22:05:50','2026-10-27 22:06:05','2026-10-27 22:06:03',NULL);
/*!40000 ALTER TABLE `jump_server_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jump_servers`
--

DROP TABLE IF EXISTS `jump_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jump_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `port` int(11) NOT NULL DEFAULT 22,
  `protocol` enum('SSH','RDP','VNC','TELNET') NOT NULL DEFAULT 'SSH',
  `os_type` enum('LINUX','UBUNTU','CENTOS','WINDOWS','DEBIAN') NOT NULL DEFAULT 'LINUX',
  `default_username` varchar(50) NOT NULL DEFAULT 'root',
  `description` varchar(255) DEFAULT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `status` enum('ONLINE','OFFLINE','MAINTENANCE') NOT NULL DEFAULT 'ONLINE',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_js_asset` (`asset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jump_servers`
--

LOCK TABLES `jump_servers` WRITE;
/*!40000 ALTER TABLE `jump_servers` DISABLE KEYS */;
INSERT INTO `jump_servers` VALUES (1,'Production Web App Server (Nginx/PHP)','192.168.1.100',22,'SSH','UBUNTU','ubuntu','Máy chủ ứng dụng Web chính môi trường Production',NULL,'ONLINE',1,'2026-09-21 21:42:50'),(2,'Database Master Cluster (MySQL 8.0)','192.168.1.105',22,'SSH','CENTOS','root','Cụm máy chủ cơ sở dữ liệu giao dịch cốt lõi',NULL,'ONLINE',1,'2026-09-21 21:42:50'),(3,'Windows Server 2022 AD & File Services','192.168.1.10',3389,'RDP','WINDOWS','Administrator','Máy chủ miền Active Directory và lưu trữ tài liệu nội bộ',NULL,'ONLINE',1,'2026-09-21 21:42:50'),(4,'Core Gateway & Security Firewall','192.168.1.1',22,'SSH','DEBIAN','admin','Hệ thống tường lửa phân vùng DMZ và cân bằng tải',NULL,'ONLINE',1,'2026-09-21 21:42:50');
/*!40000 ALTER TABLE `jump_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kb_articles`
--

DROP TABLE IF EXISTS `kb_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kb_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('DRAFT','PUBLISHED','ARCHIVED') DEFAULT 'PUBLISHED',
  `view_count` int(11) DEFAULT 0,
  `helpful_count` int(11) DEFAULT 0,
  `unhelpful_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_kb_category` (`category_id`),
  KEY `fk_kb_author` (`author_id`),
  FULLTEXT KEY `ft_kb_search` (`title`,`content`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kb_articles`
--

LOCK TABLES `kb_articles` WRITE;
/*!40000 ALTER TABLE `kb_articles` DISABLE KEYS */;
INSERT INTO `kb_articles` VALUES (1,1,'Hướng dẫn tự Đổi Mật Khẩu máy tính Windows và Domain công ty','### Khi nào cần đổi mật khẩu?\n- Theo chính sách an toàn thông tin, mật khẩu cần thay đổi định kỳ 90 ngày.\n- Mật khẩu phải gồm ít nhất 8 ký tự, có chữ hoa, chữ thường, chữ số và ký tự đặc biệt.\n\n### Các bước thực hiện:\n1. Nhấn tổ hợp phím **Ctrl + Alt + Delete** trên bàn phím.\n2. Chọn dòng **\"Change a password\" (Đổi mật khẩu)**.\n3. Nhập mật khẩu cũ tại dòng *Old password*.\n4. Nhập mật khẩu mới tại dòng *New password* và xác nhận lại ở dòng *Confirm password*.\n5. Nhấn phím Enter hoặc nút mũi tên để hoàn tất.\n\n*Lưu ý: Nếu bạn quên hoàn toàn mật khẩu và không đăng nhập được vào màn hình chính, vui lòng tạo ticket yêu cầu IT Reset mật khẩu.*',1,'PUBLISHED',46,12,0,'2026-09-20 14:08:57','2026-10-26 17:19:03'),(2,2,'Hướng dẫn Cài đặt và Kết nối VPN làm việc từ xa (FortiClient / OpenVPN)','### Chuẩn bị:\n- Đường truyền Internet ổn định tại nhà.\n- Tài khoản và mật khẩu Domain công ty được cấp.\n\n### Các bước kết nối:\n1. Mở phần mềm **FortiClient VPN** (hoặc OpenVPN) trên thanh tác vụ Taskbar.\n2. Tại mục *Remote Gateway*, nhập địa chỉ: `vpn.company.local` (cổng 10443).\n3. Nhập *Username* và *Password* tài khoản công ty của bạn.\n4. Tích chọn **Save password** nếu dùng máy tính cá nhân bảo mật.\n5. Bấm nút **Connect**. Khi trạng thái hiển thị **Connected 100%**, bạn đã có thể truy cập mạng nội bộ công ty để xem ERP, ổ đĩa dùng chung NAS và máy chủ.',1,'PUBLISHED',101,31,2,'2026-09-20 14:08:57','2026-10-29 20:44:17'),(3,3,'Cách kết nối và cài đặt Máy In Mạng Tầng 2 & Tầng 3','### Thông tin máy in văn phòng:\n- **Tầng 2:** Canon LBP 2900 / HP LaserJet Pro (IP: `192.168.1.200`)\n- **Tầng 3:** Ricoh Aficio Photocopy (IP: `192.168.1.201`)\n\n### Các bước cài đặt nhanh:\n1. Mở hộp thoại Run bằng cách bấm phím **Windows + R**.\n2. Nhập đường dẫn chia sẻ: `\\\\192.168.1.200` và nhấn **Enter**.\n3. Danh sách máy in chia sẻ sẽ hiện ra, nhấp chuột phải vào tên máy in (ví dụ *Canon_LBP2900*) và chọn **Connect**.\n4. Chờ 10 giây để Windows tự động nạp Driver.\n5. Thử in trang kiểm tra (Print Test Page) để đảm bảo kết nối thành công.',1,'PUBLISHED',67,19,0,'2026-09-20 14:08:57','2026-09-20 14:08:57'),(4,4,'Khắc phục lỗi Outlook báo \"Disconnected\" hoặc Không gửi được thư','### Hiện tượng:\nGóc dưới bên phải của Outlook hiển thị chữ *Disconnected* hoặc *Trying to connect...*, thư gửi đi nằm trong hộp thư Outbox không gửi được.\n\n### Cách xử lý:\n1. **Kiểm tra mạng:** Đảm bảo máy tính đang có kết nối mạng Internet hoặc đã bật VPN nếu làm việc tại nhà.\n2. **Tắt chế độ làm việc offline:**\n   - Vào tab **Send / Receive** trên thanh Ribbon của Outlook.\n   - Kiểm tra nút **Work Offline**. Nếu nút này đang sáng màu xanh/xám, hãy bấm vào nó một lần để chuyển về chế độ Online.\n3. **Khởi động lại Outlook:** Tắt hoàn toàn Outlook, chờ 10 giây và mở lại.\n4. **Kiểm tra dung lượng hòm thư:** Nếu hòm thư đầy quá 95%, Outlook sẽ từ chối gửi thư mới. Hãy xóa bớt thư rác trong mục *Deleted Items*.',1,'PUBLISHED',52,14,0,'2026-09-20 14:08:57','2026-09-20 14:08:57'),(5,5,'Hướng dẫn Dọn dẹp ổ đĩa C khi bị báo Đỏ (Dung lượng đầy)','### Các bước giải phóng dung lượng ổ cứng an toàn:\n1. Mở hộp thoại Run (**Windows + R**), nhập `%temp%` rồi nhấn Enter.\n2. Bấm **Ctrl + A** để chọn toàn bộ file rác tạm thời, bấm **Shift + Delete** để xóa vĩnh viễn (nếu file nào đang chạy thì chọn *Skip*).\n3. Sử dụng công cụ **Disk Cleanup** có sẵn của Windows:\n   - Nhấp chuột phải vào ổ đĩa **C:** trong File Explorer $\\rightarrow$ Chọn **Properties**.\n   - Bấm nút **Disk Cleanup** $\\rightarrow$ Tích chọn các mục *Temporary files*, *Thumbnails*, *Recycle Bin*.\n   - Bấm **OK** để dọn dẹp.\n4. Di chuyển các tài liệu nặng (video, file cài đặt) từ thư mục *Downloads* sang ổ đĩa dữ liệu D: hoặc lưu trữ trên OneDrive công ty.',1,'PUBLISHED',41,11,0,'2026-09-20 14:08:57','2026-09-21 21:31:59'),(6,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 20:28:59','2026-09-21 20:28:59'),(7,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 20:29:17','2026-09-21 20:29:17'),(8,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 20:29:52','2026-09-21 20:29:52'),(9,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 20:31:06','2026-09-21 20:31:06'),(10,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 20:38:17','2026-09-21 20:38:17'),(11,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 21:10:46','2026-09-21 21:10:46'),(12,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 21:37:18','2026-09-21 21:37:18'),(13,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 21:47:53','2026-09-21 21:47:53'),(14,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 21:58:34','2026-09-21 21:58:34'),(15,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 22:06:14','2026-09-21 22:06:14'),(16,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-21 22:33:39','2026-09-21 22:33:39'),(17,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',0,0,0,'2026-09-22 21:58:52','2026-09-22 21:58:52'),(18,1,'[Known Error] Sự cố deadlock định kỳ trên module hóa đơn ERP','### 1. Mô tả sự cố (Issue Description)\nHệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.\n\n### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\nLock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.\n\n### 3. Giải pháp tạm thời (Workaround)\nChạy lệnh kill query chậm và restart worker queue batch job.\n\n### 4. Giải pháp triệt để (Permanent Solution)\nThêm compound index (customer_id, created_at) và tách batch 500 rows.',3,'PUBLISHED',4,0,0,'2026-10-26 17:16:07','2026-10-27 22:21:20');
/*!40000 ALTER TABLE `kb_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kb_categories`
--

DROP TABLE IF EXISTS `kb_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kb_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'bi-book',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kb_categories`
--

LOCK TABLES `kb_categories` WRITE;
/*!40000 ALTER TABLE `kb_categories` DISABLE KEYS */;
INSERT INTO `kb_categories` VALUES (1,'ACCOUNT','Tài Khoản & Đăng Nhập','Mật khẩu Windows, Domain AD, Mail và Tài khoản phần mềm','bi-key-fill',1,'2026-09-20 14:08:57'),(2,'NETWORK','Mạng, WiFi & VPN','Kết nối mạng nội bộ LAN, WiFi công ty và truy cập VPN từ xa','bi-wifi',1,'2026-09-20 14:08:57'),(3,'PRINTER','Máy In & Máy Quét (Scan)','Cài đặt driver máy in mạng, kết nối máy in và xử lý kẹt giấy','bi-printer-fill',1,'2026-09-20 14:08:57'),(4,'SOFTWARE','Phần Mềm Văn Phòng & Email','Cấu hình Outlook, Microsoft 365, Word, Excel và ERP','bi-envelope-paper-fill',1,'2026-09-20 14:08:57'),(5,'HARDWARE','Phần Cứng & Máy Tính','Bàn phím, chuột, màn hình phụ, dọn dẹp ổ đĩa và pin laptop','bi-laptop',1,'2026-09-20 14:08:57');
/*!40000 ALTER TABLE `kb_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_read` (`user_id`,`is_read`),
  KEY `fk_notif_ticket` (`ticket_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,5,1,'Bạn được nhắc đến','Đỗ Mai Phương đã nhắc đến bạn (@it_app) trong ticket #IT-000001','mention',NULL,1,'2026-09-20 10:10:47'),(2,6,1,'Bạn được nhắc đến','Phạm Hồng Phúc (Application) đã nhắc đến bạn (@user1) trong ticket #IT-000001','mention',NULL,0,'2026-09-20 10:11:14'),(3,2,1,'CẢNH BÁO LEO THANG (ESCALATION): Ticket #IT-000001 quá hạn SLA','Ticket \"Mất kết nối máy chủ dữ liệu kế toán\" tại nhóm IT đã quá hạn cam kết xử lý SLA. Yêu cầu lãnh đạo can thiệp/điều phối.','danger',NULL,0,'2026-09-20 18:49:12'),(4,1,1,'CẢNH BÁO LEO THANG (ESCALATION): Ticket #IT-000001 quá hạn SLA','Ticket \"Mất kết nối máy chủ dữ liệu kế toán\" tại nhóm IT đã quá hạn cam kết xử lý SLA. Yêu cầu lãnh đạo can thiệp/điều phối.','danger',NULL,0,'2026-09-20 18:49:12'),(5,6,1,'Bạn được nhắc đến','Phạm Hồng Phúc (Application) đã nhắc đến bạn (@user1) trong ticket #IT-000001','mention',NULL,0,'2026-09-20 19:16:21'),(6,6,1,'Phản hồi mới trên ticket #IT-000001','Phạm Hồng Phúc (Application) đã phản hồi: \"@user1 coi lại nhé em\"','comment',NULL,0,'2026-09-20 19:16:22'),(7,6,13,'Phản hồi mới trên ticket #IT-000013','System Administrator đã phản hồi: \"Chào Đỗ Mai Phương,\r\n\r\nBộ phận IT Support đã tiếp nhận yêu cầu #IT-000...\"','comment',NULL,0,'2026-09-22 22:41:00'),(8,3,NULL,'Test Notification','This is a test notification','test',NULL,0,'2026-09-22 22:45:33'),(9,2,53,'Cần phê duyệt yêu cầu dịch vụ','user1 vừa gửi yêu cầu cần bạn phê duyệt.','ticket_approval',NULL,0,'2026-09-22 22:45:33'),(10,3,NULL,'Test Notification','This is a test notification','test',NULL,0,'2026-09-22 23:05:38'),(11,2,54,'Cần phê duyệt yêu cầu dịch vụ','user1 vừa gửi yêu cầu cần bạn phê duyệt.','ticket_approval',NULL,0,'2026-09-22 23:05:38'),(12,6,1,'Phản hồi mới trên ticket #IT-000001','System Administrator đã phản hồi: \"Chào Đỗ Mai Phương,\r\n\r\nBộ phận IT Support đã tiếp nhận yêu cầu #IT-000...\"','comment',NULL,0,'2026-09-23 08:16:21'),(13,6,54,'Ticket đã được xử lý xong #IT-000054','Kỹ thuật viên đã xử lý xong yêu cầu của bạn. Vui lòng kiểm tra và xác nhận nghiệm thu.','resolve',NULL,0,'2026-09-26 11:44:15'),(14,3,NULL,'Test Notification','This is a test notification','test',NULL,0,'2026-10-26 17:38:00'),(15,2,63,'Cần phê duyệt yêu cầu dịch vụ','user1 vừa gửi yêu cầu cần bạn phê duyệt.','ticket_approval',NULL,0,'2026-10-26 17:38:00'),(16,6,1,'Ticket đã được xử lý xong #IT-000001','Kỹ thuật viên đã xử lý xong yêu cầu của bạn. Vui lòng kiểm tra và xác nhận nghiệm thu.','resolve',NULL,0,'2026-10-26 17:40:13'),(29,2,NULL,'Giao việc mới: DNB-1','Administrator Updated đã giao cho bạn: \"Trao đổi yêu cầu khách hàng\" trong dự án .','issue_assigned','/projects/10/issues/69',0,'2026-10-27 21:49:15'),(30,2,NULL,'Giao việc mới: DNB-2','Administrator đã giao cho bạn: \"Test Trao đổi yêu cầu khách hàng [VERIFY]\" trong dự án Dự án bank demo.','issue_assigned','/projects/10/issues/70',0,'2026-10-27 21:53:08'),(31,2,NULL,'Tiến độ công việc: DNB-1 [IN_PROGRESS]','Administrator đã chuyển trạng thái công việc \"Trao đổi yêu cầu khách hàng (Đã cập nhật)\" sang [IN_PROGRESS].','issue_status','/projects/10/issues/69',0,'2026-10-27 22:00:55'),(32,2,NULL,'Giao việc mới: DNB-3','Administrator Updated đã giao cho bạn: \"Auto Test Task 1793114246\" trong dự án Dự án bank demo.','issue_assigned','/projects/10/issues/71',0,'2026-10-27 22:17:26'),(33,2,NULL,'Giao việc mới: DNB-5','Administrator Updated đã giao cho bạn: \"Auto Test Task 1793114314\" trong dự án Dự án bank demo.','issue_assigned','/projects/10/issues/73',0,'2026-10-27 22:18:34'),(34,2,NULL,'Bình luận mới: DNB-5','Administrator Updated: \"Automated verification test comment.\"','issue_comment','/projects/10/issues/73',0,'2026-10-27 22:18:34'),(35,2,NULL,'Giao việc mới: DNB-6','Administrator Updated đã giao cho bạn: \"Auto Test Task 1793114473\" trong dự án Dự án bank demo.','issue_assigned','/projects/10/issues/74',0,'2026-10-27 22:21:13'),(36,2,NULL,'Bảng Kanban: DNB-6 [IN PROGRESS]','Administrator Updated đã chuyển công việc \"Auto Test Task 1793114473\" sang cột [IN PROGRESS].','issue_status','/projects/10/issues/74',0,'2026-10-27 22:21:13'),(37,2,NULL,'Bình luận mới: DNB-6','Administrator Updated: \"Automated verification test comment.\"','issue_comment','/projects/10/issues/74',0,'2026-10-27 22:21:13');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_menu` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'PROJECTS','menu.projects','Menu: Dự Án Agile (TaskFlow)','Cho phép xem và vào phân hệ Quản lý Dự án Agile',1,10,'2026-09-22 22:52:14'),(2,'TICKETS','menu.tickets','Menu: Vé Hỗ Trợ (ITSM)','Cho phép xem và vào phân hệ Quản lý Vé Hỗ Trợ IT',1,20,'2026-09-22 22:52:14'),(3,'CATALOG','menu.catalog','Menu: Cổng Dịch Vụ (Catalog)','Cho phép xem Cổng Dịch Vụ Công Nghệ',1,30,'2026-09-22 22:52:14'),(4,'APPROVALS','menu.approvals','Menu: Xét Duyệt (Approvals)','Cho phép vào Trung tâm phê duyệt yêu cầu dịch vụ',1,40,'2026-09-22 22:52:14'),(5,'ASSETS','menu.assets','Menu: Quản Lý Tài Sản (ITAM)','Cho phép xem và quản lý thiết bị/tài sản IT',1,50,'2026-09-22 22:52:14'),(6,'KB','menu.kb','Menu: Cơ Sở Tri Thức (KB)','Cho phép đọc và tra cứu bài viết tri thức',1,60,'2026-09-22 22:52:14'),(7,'JUMPSERVER','menu.jumpserver','Menu: JumpServer Bastion PAM','Cho phép truy cập máy chủ đặc quyền JumpServer',1,70,'2026-09-22 22:52:14'),(8,'REPORTS','menu.reports','Menu: Báo Cáo & Thống Kê SLA','Cho phép xem bảng phân tích SLA và hiệu suất',1,80,'2026-09-22 22:52:14'),(9,'ADMIN','menu.admin','Menu: Quản Trị Hệ Thống','Cho phép vào khu vực Quản trị Người dùng, Nhóm, Cấu hình',1,90,'2026-09-22 22:52:14'),(10,'SYSTEM','menu.backup','Menu: Sao Lưu & Phục Hồi CSDL','Cho phép quản lý bản sao lưu và khôi phục dữ liệu',1,100,'2026-09-22 22:52:14'),(11,'TICKETS','tickets.create','Tạo Phiếu Hỗ Trợ Mới','Gửi báo cáo sự cố hoặc yêu cầu dịch vụ IT',0,110,'2026-09-22 22:52:14'),(12,'TICKETS','tickets.assign','Phân Công Kỹ Thuật Viên','Gán hoặc chuyển giao ticket cho nhân sự xử lý',0,120,'2026-09-22 22:52:14'),(13,'TICKETS','tickets.resolve','Giải Quyết & Hoàn Thành Ticket','Cập nhật giải pháp kỹ thuật và kết thúc ticket',0,130,'2026-09-22 22:52:14'),(14,'TICKETS','tickets.priority','Điều Chỉnh Mức Độ Ưu Tiên','Thay đổi priority SLA của ticket',0,140,'2026-09-22 22:52:14'),(15,'TICKETS','tickets.delete','Hủy / Đánh Dấu Trùng Lặp','Hủy ticket hoặc đánh dấu trùng lặp (Duplicate)',0,150,'2026-09-22 22:52:14'),(16,'PROJECTS','projects.create','Tạo Dự Án Mới','Khởi tạo không gian dự án phần mềm mới',0,160,'2026-09-22 22:52:14'),(17,'PROJECTS','projects.manage','Quản Trị Thiết Lập Dự Án','Thay đổi thành viên, workflow và cài đặt dự án',0,170,'2026-09-22 22:52:14'),(18,'EXPORT','export.tickets','Xuất Excel: Danh Sách Ticket','Tải file Excel danh sách các phiếu hỗ trợ',0,180,'2026-09-22 22:52:14'),(19,'EXPORT','export.issues','Xuất Excel: Công Việc Dự Án','Tải file Excel danh sách công việc/bugs/testcases',0,190,'2026-09-22 22:52:14'),(20,'EXPORT','export.assets','Xuất Excel: Quản Lý Tài Sản','Tải file Excel danh mục thiết bị và tài sản CNTT',0,200,'2026-09-22 22:52:14'),(21,'EXPORT','export.audit_logs','Xuất Excel: Nhật Ký Kiểm Toán','Tải file Excel nhật ký truy vết hệ thống',0,210,'2026-09-22 22:52:14'),(22,'SYSTEM','system.backup','Sao Lưu & Khôi Phục Dữ Liệu','Thực hiện tạo bản snapshot và restore CSDL',0,220,'2026-09-22 22:52:14');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problem_incidents`
--

DROP TABLE IF EXISTS `problem_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problem_incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_problem_ticket` (`problem_id`,`ticket_id`),
  KEY `idx_pi_ticket` (`ticket_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problem_incidents`
--

LOCK TABLES `problem_incidents` WRITE;
/*!40000 ALTER TABLE `problem_incidents` DISABLE KEYS */;
INSERT INTO `problem_incidents` VALUES (1,1,15,'2026-09-21 20:27:28'),(2,2,17,'2026-09-21 20:28:00'),(3,3,19,'2026-09-21 20:28:59'),(4,4,21,'2026-09-21 20:29:17'),(5,5,25,'2026-09-21 20:29:52'),(6,6,27,'2026-09-21 20:31:06'),(7,7,31,'2026-09-21 20:38:17'),(8,8,33,'2026-09-21 21:10:46'),(9,9,37,'2026-09-21 21:37:18'),(10,10,41,'2026-09-21 21:47:53'),(11,11,43,'2026-09-21 21:58:34'),(12,12,45,'2026-09-21 22:06:14'),(13,13,47,'2026-09-21 22:33:39'),(14,14,49,'2026-09-22 21:58:52'),(15,15,61,'2026-10-26 17:16:07');
/*!40000 ALTER TABLE `problem_incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problems`
--

DROP TABLE IF EXISTS `problems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_code` varchar(30) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `priority_id` int(11) NOT NULL,
  `status` enum('LOGGED','INVESTIGATING','WORKAROUND_FOUND','KNOWN_ERROR','RESOLVED','CLOSED') NOT NULL DEFAULT 'LOGGED',
  `root_cause` text DEFAULT NULL,
  `workaround` text DEFAULT NULL,
  `permanent_solution` text DEFAULT NULL,
  `is_known_error` tinyint(1) DEFAULT 0,
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `problem_code` (`problem_code`),
  KEY `idx_prb_status` (`status`),
  KEY `idx_prb_assignee` (`assigned_to`),
  KEY `fk_prb_cat` (`category_id`),
  KEY `fk_prb_pri` (`priority_id`),
  KEY `fk_prb_creator` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problems`
--

LOCK TABLES `problems` WRITE;
/*!40000 ALTER TABLE `problems` DISABLE KEYS */;
INSERT INTO `problems` VALUES (1,'PRB-000001','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'LOGGED',NULL,NULL,NULL,0,3,3,NULL,'2026-09-21 20:27:28','2026-09-21 20:27:28'),(2,'PRB-000002','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'KNOWN_ERROR','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Thêm compound index (customer_id, created_at) và tách batch 500 rows.',1,3,3,NULL,'2026-09-21 20:28:00','2026-09-21 20:28:00'),(3,'PRB-000003','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 20:28:59','2026-09-21 20:28:59','2026-09-21 20:28:59'),(4,'PRB-000004','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 20:29:17','2026-09-21 20:29:17','2026-09-21 20:29:17'),(5,'PRB-000005','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 20:29:52','2026-09-21 20:29:52','2026-09-21 20:29:52'),(6,'PRB-000006','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 20:31:06','2026-09-21 20:31:06','2026-09-21 20:31:06'),(7,'PRB-000007','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 20:38:17','2026-09-21 20:38:17','2026-09-21 20:38:17'),(8,'PRB-000008','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 21:10:46','2026-09-21 21:10:46','2026-09-21 21:10:46'),(9,'PRB-000009','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 21:37:18','2026-09-21 21:37:18','2026-09-21 21:37:18'),(10,'PRB-000010','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 21:47:53','2026-09-21 21:47:53','2026-09-21 21:47:53'),(11,'PRB-000011','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 21:58:34','2026-09-21 21:58:34','2026-09-21 21:58:34'),(12,'PRB-000012','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 22:06:14','2026-09-21 22:06:14','2026-09-21 22:06:14'),(13,'PRB-000013','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-21 22:33:39','2026-09-21 22:33:39','2026-09-21 22:33:39'),(14,'PRB-000014','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-09-22 21:58:52','2026-09-22 21:58:52','2026-09-22 21:58:52'),(15,'PRB-000015','Sự cố deadlock định kỳ trên module hóa đơn ERP','Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',4,3,'RESOLVED','Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.','Chạy lệnh kill query chậm và restart worker queue batch job.','Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',1,3,3,'2026-10-26 17:16:07','2026-10-26 17:16:07','2026-10-26 17:16:07');
/*!40000 ALTER TABLE `problems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_issue_comments`
--

DROP TABLE IF EXISTS `project_issue_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_issue_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pic_issue` (`issue_id`),
  KEY `idx_pic_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_issue_comments`
--

LOCK TABLES `project_issue_comments` WRITE;
/*!40000 ALTER TABLE `project_issue_comments` DISABLE KEYS */;
INSERT INTO `project_issue_comments` VALUES (1,41,1,'Vui lòng hoàn thành trước buổi demo ngày mai!','2026-09-22 22:12:57'),(2,46,1,'Vui lòng hoàn thành trước buổi demo ngày mai!','2026-09-22 22:29:40'),(3,51,1,'Vui lòng hoàn thành trước buổi demo ngày mai!','2026-09-22 22:29:55'),(4,56,1,'Vui lòng hoàn thành trước buổi demo ngày mai!','2026-09-22 22:44:56'),(5,61,1,'Vui lòng hoàn thành trước buổi demo ngày mai!','2026-09-22 23:05:38'),(6,65,1,'Vui lòng hoàn thành trước buổi demo ngày mai!','2026-09-26 10:47:00');
/*!40000 ALTER TABLE `project_issue_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_issues`
--

DROP TABLE IF EXISTS `project_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `issue_key` varchar(30) NOT NULL,
  `issue_number` int(11) NOT NULL,
  `issue_type` enum('BACKLOG','TASK','BUG','TESTCASE','STORY','EPIC') NOT NULL DEFAULT 'TASK',
  `summary` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `status` enum('TO_DO','IN_PROGRESS','IN_REVIEW','DONE') NOT NULL DEFAULT 'TO_DO',
  `priority` enum('HIGHEST','HIGH','MEDIUM','LOW','LOWEST') NOT NULL DEFAULT 'MEDIUM',
  `reporter_id` int(11) NOT NULL,
  `assignee_id` int(11) DEFAULT NULL,
  `sprint_status` enum('BACKLOG','ACTIVE_SPRINT','COMPLETED') NOT NULL DEFAULT 'ACTIVE_SPRINT',
  `component` varchar(50) DEFAULT NULL,
  `story_points` int(11) DEFAULT 1,
  `epic_id` int(11) DEFAULT NULL,
  `test_type` enum('MANUAL','AUTOMATED','REGRESSION','SMOKE') DEFAULT 'MANUAL',
  `test_steps` text DEFAULT NULL,
  `test_expected_result` text DEFAULT NULL,
  `test_execution_status` enum('UNTESTED','PASSED','FAILED','BLOCKED') DEFAULT 'UNTESTED',
  `bug_severity` enum('BLOCKER','CRITICAL','MAJOR','MINOR','TRIVIAL') DEFAULT 'MAJOR',
  `steps_to_reproduce` text DEFAULT NULL,
  `actual_result` text DEFAULT NULL,
  `expected_result` text DEFAULT NULL,
  `environment` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `original_estimate_hours` decimal(6,2) DEFAULT 0.00,
  `remaining_hours` decimal(6,2) DEFAULT 0.00,
  `completed_hours` decimal(6,2) DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `risks` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `area` varchar(100) DEFAULT 'Main/Core',
  `iteration` varchar(100) DEFAULT 'Sprint 1 (2026)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `issue_key` (`issue_key`),
  KEY `idx_pi_prj` (`project_id`),
  KEY `idx_pi_status` (`status`),
  KEY `idx_pi_type` (`issue_type`),
  KEY `idx_pi_assignee` (`assignee_id`),
  KEY `idx_pi_sprint` (`sprint_status`),
  KEY `fk_pi_reporter` (`reporter_id`),
  KEY `fk_pi_parent` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_issues`
--

LOCK TABLES `project_issues` WRITE;
/*!40000 ALTER TABLE `project_issues` DISABLE KEYS */;
INSERT INTO `project_issues` VALUES (16,1,'NUC-344',344,'STORY','Optimize experience for mobile web','Tối ưu hóa luồng thanh toán và trải nghiệm người dùng trên thiết bị di động màn hình nhỏ.','TO_DO','HIGH',1,2,'ACTIVE_SPRINT','BILLING',3,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(17,1,'NUC-360',360,'TASK','Onboard workout options (OWO)','Tích hợp mô-đun hướng dẫn đăng ký tài khoản và thiết lập cấu hình onboarding ban đầu.','TO_DO','MEDIUM',1,3,'ACTIVE_SPRINT','ACCOUNTS',2,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(18,1,'NUC-337',337,'TASK','Multi-dest search UI mobileweb','Giao diện tìm kiếm đa điểm đến trên mobile responsive với bộ lọc nâng cao.','TO_DO','MEDIUM',1,1,'ACTIVE_SPRINT','ACCOUNTS',5,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(19,1,'NUC-339',339,'TASK','Billing system integration - frontend','Ghép nối API cổng thanh toán VNPay và thẻ tín dụng quốc tế vào form đặt chỗ.','TO_DO','HIGH',1,2,'ACTIVE_SPRINT','FORMS',3,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(20,1,'NUC-340',340,'TASK','Account settings defaults','Thiết lập các tham số mặc định về ngôn ngữ, múi giờ và tiền tệ cho tài khoản mới.','TO_DO','LOW',1,3,'ACTIVE_SPRINT','ACCOUNTS',1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(21,1,'NUC-345',345,'TESTCASE','Kiểm thử xác thực OTP thanh toán thẻ tín dụng 3D-Secure','Đảm bảo giao dịch thẻ visa/mastercard gửi mã OTP chính xác và xác thực thành công.','DONE','HIGH',1,4,'ACTIVE_SPRINT','BILLING',2,NULL,'REGRESSION','1. Chọn tour du lịch và vào thanh toán\n2. Nhập thông tin thẻ test Visa 4000...\n3. Nhập mã OTP \'123456\' trên màn hình xác thực\n4. Kiểm tra trang kết quả xác nhận','Hệ thống báo trừ tiền thành công, mã giao dịch xuất hiện và gửi email vé điện tử.','PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:12:22',24,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(22,1,'NUC-342',342,'STORY','Fast trip search','Cơ chế tìm kiếm siêu tốc chuyến bay và tour du lịch với Elasticsearch và bộ nhớ đệm Redis.','IN_PROGRESS','HIGHEST',1,2,'ACTIVE_SPRINT','ACCOUNTS',8,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(23,1,'NUC-336',336,'TASK','Affelite links integration - frontend','Gắn mã theo dõi hoa hồng tiếp thị liên kết (affiliate) vào các liên kết giới thiệu tour.','IN_PROGRESS','MEDIUM',1,1,'ACTIVE_SPRINT','BILLING',3,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-26 11:34:57',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(24,1,'NUC-341',341,'BUG','Shopping cart purchasing error - quick fix required','Nút giỏ hàng bị đứng khi người dùng bấm thanh toán 2 lần liên tiếp.','IN_PROGRESS','HIGHEST',1,36,'ACTIVE_SPRINT','FORMS',2,NULL,'MANUAL',NULL,NULL,'UNTESTED','CRITICAL','1. Thêm 1 sản phẩm vào giỏ\r\n2. Bấm nút \'Đặt tour ngay\' 2 lần liên tục nhanh\r\n3. Trình duyệt hiển thị vòng xoay vô tận',NULL,NULL,'Chrome 122 Windows 11 & Safari iOS 17',0,'2026-09-22 21:54:43','2026-09-26 11:16:20',33,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(25,1,'NUC-367',367,'STORY','Revise and streamline booking flow','Rút ngắn quy trình đặt vé từ 5 bước xuống còn 3 bước để tăng tỷ lệ chuyển đổi đơn hàng.','IN_REVIEW','HIGH',1,3,'ACTIVE_SPRINT','ACCOUNTS',5,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(26,1,'NUC-358',358,'TASK','Travel suggestion experiments','Thử nghiệm thuật toán gợi ý hành trình du lịch phù hợp dựa trên sở thích cá nhân.','IN_REVIEW','MEDIUM',1,2,'ACTIVE_SPRINT','ACCOUNTS',3,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(27,1,'NUC-354',354,'TASK','Ongoing customer satisfaction','Khảo sát CSAT tự động gửi sau khi hoàn thành chuyến đi để thu thập ý kiến khách hàng.','IN_REVIEW','LOW',1,1,'ACTIVE_SPRINT','ACCOUNTS',2,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(28,1,'NUC-351',351,'TASK','Planet Taxi Device exploration & research','Nghiên cứu tích hợp API của các đối tác vận tải đưa đón khách từ sân bay về khách sạn.','IN_REVIEW','MEDIUM',1,3,'ACTIVE_SPRINT','FEEDBACK',3,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',33,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(29,1,'NUC-352',352,'TESTCASE','Kiểm tra độ tương thích responsive trên Safari iOS và Chrome Android','Kiểm thử toàn diện các nút bấm, menu điều hướng và thanh tìm kiếm trên di động.','IN_REVIEW','MEDIUM',1,4,'ACTIVE_SPRINT','ACCOUNTS',2,NULL,'MANUAL','1. Mở trang chủ trên iPhone Safari\n2. Mở thanh menu và chọn \'Dự án\'\n3. Kiểm tra khung hiển thị không bị tràn viền ngang','Giao diện co giãn hoàn hảo, chữ và icon hiển thị sắc nét không lỗi layout.','PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',24,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(30,1,'NUC-348',348,'BUG','High outage: Software bug fix - BG Web-store app crashing','Sửa lỗi sập ứng dụng web khi tải danh sách voucher khuyến mãi vào giờ cao điểm.','DONE','HIGHEST',1,1,'ACTIVE_SPRINT','BILLING',5,NULL,'MANUAL',NULL,NULL,'UNTESTED','BLOCKER','Truy cập tab Voucher lúc có hơn 500 người dùng trực tuyến.','Database connection pool cạn kiệt, trả về lỗi 500','Đã cấu hình connection pool và lưu cache voucher 5 phút.','Production Server Ubuntu 22.04',0,'2026-09-22 21:54:43','2026-09-22 21:54:43',NULL,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(31,1,'NUC-349',349,'BUG','Web-store purchasing performance issue fix','Tối ưu truy vấn SQL tính tổng giá tiền và thuế cho giỏ hàng giảm thời gian tải từ 2.4s xuống 150ms.','DONE','HIGH',1,2,'ACTIVE_SPRINT','FORMS',3,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR','Giỏ hàng có nhiều hơn 5 dịch vụ đi kèm.','Thời gian phản hồi chậm hơn 2.5s','Đã thêm index và viết lại câu lệnh JOIN, tốc độ dưới 200ms.','Staging & Production',0,'2026-09-22 21:54:43','2026-09-22 21:54:43',NULL,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(32,1,'NUC-343',343,'TESTCASE','Kiểm thử đăng nhập một chạm Single Sign-On (SSO)','Xác thực luồng đăng nhập bằng tài khoản nội bộ Google Workspace và Microsoft 365.','DONE','MEDIUM',1,4,'ACTIVE_SPRINT','ACCOUNTS',2,NULL,'SMOKE','1. Bấm \'Đăng nhập với Google\'\n2. Chọn email công ty @corp.internal\n3. Chấp nhận quyền cơ bản','Hệ thống tự động đăng nhập và chuyển hướng thẳng vào Dashboard người dùng.','PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',24,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(33,1,'NUC-370',370,'STORY','Hỗ trợ thanh toán qua Apple Pay và Google Wallet','Cung cấp phương thức thanh toán không chạm cho ứng dụng web di động.','TO_DO','HIGH',1,2,'BACKLOG','BILLING',5,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 21:54:43',NULL,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(34,1,'NUC-371',371,'TASK','Nâng cấp bảo mật SSL/TLS và cấu hình HSTS Preload','Cập nhật chứng chỉ số TLS 1.3 và bật chính sách bảo mật HTTP Strict Transport Security.','TO_DO','HIGHEST',1,1,'BACKLOG','SECURITY',3,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 21:54:43',NULL,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(35,1,'NUC-372',372,'BUG','Avatar người dùng hiển thị méo hình trên màn hình Retina','Ảnh đại diện người dùng khi upload tỉ lệ 16:9 bị bóp méo thành hình tròn không đều.','TO_DO','LOW',1,3,'BACKLOG','ACCOUNTS',1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MINOR','Tải lên ảnh 1200x800 tại trang hồ sơ cá nhân.','Ảnh bị dẹt theo chiều dọc','Áp dụng CSS object-fit: cover để giữ nguyên tỷ lệ ảnh.','All browsers',0,'2026-09-22 21:54:43','2026-09-22 21:54:43',NULL,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(36,1,'NUC-373',373,'TESTCASE','Kiểm thử tải trọng Form đặt chỗ với 1.000 yêu cầu/giây (Stress Test)','Sử dụng k6 hoặc Apache JMeter mô phỏng tải truy cập đồng thời.','TO_DO','HIGH',1,4,'BACKLOG','FORMS',5,NULL,'AUTOMATED','1. Thiết lập k6 test script với 200 Virtual Users (VU)\n2. Chạy kịch bản gửi đơn hàng liên tục trong 5 phút\n3. Giám sát CPU/RAM máy chủ qua JumpServer','Tỷ lệ lỗi dưới 0.1%, độ trễ phản hồi p95 dưới 500ms.','UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 22:04:33',24,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(37,1,'NUC-374',374,'TASK','Tích hợp chatbot tự động giải đáp thắc mắc dịch vụ 24/7','Kết nối API trợ lý AI trả lời câu hỏi thường gặp về thủ tục visa và lịch bay.','TO_DO','MEDIUM',1,2,'BACKLOG','FEEDBACK',8,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 21:54:43','2026-09-22 21:54:43',NULL,NULL,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(40,1,'NUC-375',375,'BACKLOG','Backlog Master Epic: Nâng cấp Core API 2026','Hạng mục Backlog cấp cao quản lý toàn bộ việc nâng cấp API','TO_DO','HIGH',1,1,'BACKLOG',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:12:57','2026-10-27 21:59:30',NULL,'Ghi chú kiến trúc hệ thống: Dùng JWT và Redis',120.00,120.00,0.00,'2026-10-01','2026-12-31',NULL,NULL,'Beyond Gravity','Beyond Gravity\\Sprint 1 (2026)'),(41,1,'NUC-376',376,'TASK','Task: Viết unit tests cho Authentication API','Chi tiết các ca test PHPUnit cho AuthController','IN_PROGRESS','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:12:57','2026-09-22 22:12:57',40,'Lưu ý mock database connection',16.00,8.00,8.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(42,1,'NUC-377',377,'BUG','Bug: Lỗi timeout khi gọi API Gateway','Khi tải cao, gateway timeout sau 30 giây','TO_DO','HIGHEST',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','CRITICAL',NULL,NULL,NULL,NULL,0,'2026-09-22 22:12:57','2026-09-22 22:12:57',40,'',6.00,6.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(43,1,'NUC-378',378,'TESTCASE','TestCase: Kiểm thử gateway timeout dưới tải 500 RPS','Dùng k6 load test','DONE','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'AUTOMATED',NULL,NULL,'PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:12:57','2026-09-22 22:12:59',42,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(45,1,'NUC-379',379,'BACKLOG','Backlog Master Epic: Nâng cấp Core API 2026','Hạng mục Backlog cấp cao quản lý toàn bộ việc nâng cấp API','TO_DO','HIGH',1,1,'BACKLOG',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:29:40','2026-10-27 21:59:30',NULL,'Ghi chú kiến trúc hệ thống: Dùng JWT và Redis',120.00,120.00,0.00,'2026-10-01','2026-12-31',NULL,NULL,'Beyond Gravity','Beyond Gravity\\Sprint 1 (2026)'),(46,1,'NUC-380',380,'TASK','Task: Viết unit tests cho Authentication API','Chi tiết các ca test PHPUnit cho AuthController','IN_PROGRESS','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:29:40','2026-09-22 22:29:40',45,'Lưu ý mock database connection',16.00,8.00,8.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(47,1,'NUC-381',381,'BUG','Bug: Lỗi timeout khi gọi API Gateway','Khi tải cao, gateway timeout sau 30 giây','TO_DO','HIGHEST',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','CRITICAL',NULL,NULL,NULL,NULL,0,'2026-09-22 22:29:40','2026-09-22 22:29:40',45,'',6.00,6.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(48,1,'NUC-382',382,'TESTCASE','TestCase: Kiểm thử gateway timeout dưới tải 500 RPS','Dùng k6 load test','DONE','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'AUTOMATED',NULL,NULL,'PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:29:40','2026-09-22 22:29:40',47,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(50,1,'NUC-383',383,'BACKLOG','Backlog Master Epic: Nâng cấp Core API 2026','Hạng mục Backlog cấp cao quản lý toàn bộ việc nâng cấp API','TO_DO','HIGH',1,1,'BACKLOG',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:29:55','2026-10-27 21:59:30',NULL,'Ghi chú kiến trúc hệ thống: Dùng JWT và Redis',120.00,120.00,0.00,'2026-10-01','2026-12-31',NULL,NULL,'Beyond Gravity','Beyond Gravity\\Sprint 1 (2026)'),(51,1,'NUC-384',384,'TASK','Task: Viết unit tests cho Authentication API','Chi tiết các ca test PHPUnit cho AuthController','IN_PROGRESS','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:29:55','2026-09-22 22:29:55',50,'Lưu ý mock database connection',16.00,8.00,8.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(52,1,'NUC-385',385,'BUG','Bug: Lỗi timeout khi gọi API Gateway','Khi tải cao, gateway timeout sau 30 giây','TO_DO','HIGHEST',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','CRITICAL',NULL,NULL,NULL,NULL,0,'2026-09-22 22:29:55','2026-09-22 22:29:55',50,'',6.00,6.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(53,1,'NUC-386',386,'TESTCASE','TestCase: Kiểm thử gateway timeout dưới tải 500 RPS','Dùng k6 load test','DONE','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'AUTOMATED',NULL,NULL,'PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:29:55','2026-09-22 22:29:55',52,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(55,1,'NUC-387',387,'BACKLOG','Backlog Master Epic: Nâng cấp Core API 2026','Hạng mục Backlog cấp cao quản lý toàn bộ việc nâng cấp API','TO_DO','HIGH',1,1,'BACKLOG',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:44:56','2026-10-27 21:59:30',NULL,'Ghi chú kiến trúc hệ thống: Dùng JWT và Redis',120.00,120.00,0.00,'2026-10-01','2026-12-31',NULL,NULL,'Beyond Gravity','Beyond Gravity\\Sprint 1 (2026)'),(56,1,'NUC-388',388,'TASK','Task: Viết unit tests cho Authentication API','Chi tiết các ca test PHPUnit cho AuthController','IN_PROGRESS','MEDIUM',1,1,'BACKLOG',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:44:56','2026-10-29 20:35:23',55,'Lưu ý mock database connection',16.00,8.00,8.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(57,1,'NUC-389',389,'BUG','Bug: Lỗi timeout khi gọi API Gateway','Khi tải cao, gateway timeout sau 30 giây','TO_DO','HIGHEST',1,1,'BACKLOG',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','CRITICAL',NULL,NULL,NULL,NULL,0,'2026-09-22 22:44:56','2026-10-29 20:35:24',55,'',6.00,6.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(58,1,'NUC-390',390,'TESTCASE','TestCase: Kiểm thử gateway timeout dưới tải 500 RPS','Dùng k6 load test','DONE','MEDIUM',1,1,'BACKLOG',NULL,1,NULL,'AUTOMATED',NULL,NULL,'PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 22:44:56','2026-10-29 20:35:26',57,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(60,1,'NUC-391',391,'BACKLOG','Backlog Master Epic: Nâng cấp Core API 2026','Hạng mục Backlog cấp cao quản lý toàn bộ việc nâng cấp API','TO_DO','HIGH',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 23:05:38','2026-10-29 20:35:29',NULL,'Ghi chú kiến trúc hệ thống: Dùng JWT và Redis',120.00,120.00,0.00,'2026-10-01','2026-12-31',NULL,NULL,'Beyond Gravity','Beyond Gravity\\Sprint 1 (2026)'),(61,1,'NUC-392',392,'TASK','Task: Viết unit tests cho Authentication API','Chi tiết các ca test PHPUnit cho AuthController','IN_PROGRESS','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 23:05:38','2026-09-22 23:05:38',60,'Lưu ý mock database connection',16.00,8.00,8.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(62,1,'NUC-393',393,'BUG','Bug: Lỗi timeout khi gọi API Gateway','Khi tải cao, gateway timeout sau 30 giây','TO_DO','HIGHEST',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','CRITICAL',NULL,NULL,NULL,NULL,0,'2026-09-22 23:05:38','2026-09-22 23:05:38',60,'',6.00,6.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(63,1,'NUC-394',394,'TESTCASE','TestCase: Kiểm thử gateway timeout dưới tải 500 RPS','Dùng k6 load test','TO_DO','MEDIUM',1,1,'BACKLOG',NULL,1,NULL,'AUTOMATED',NULL,NULL,'PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-22 23:05:38','2026-10-29 20:35:22',62,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(64,1,'NUC-395',395,'BACKLOG','Backlog Master Epic: Nâng cấp Core API 2026','Hạng mục Backlog cấp cao quản lý toàn bộ việc nâng cấp API','TO_DO','HIGH',1,1,'BACKLOG',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-26 10:47:00','2026-10-27 21:59:30',NULL,'Ghi chú kiến trúc hệ thống: Dùng JWT và Redis',120.00,120.00,0.00,'2026-10-01','2026-12-31',NULL,NULL,'Beyond Gravity','Beyond Gravity\\Sprint 1 (2026)'),(65,1,'NUC-396',396,'TASK','Task: Viết unit tests cho Authentication API','Chi tiết các ca test PHPUnit cho AuthController','IN_PROGRESS','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-26 10:47:00','2026-09-26 10:47:00',64,'Lưu ý mock database connection',16.00,8.00,8.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(66,1,'NUC-397',397,'BUG','Bug: Lỗi timeout khi gọi API Gateway','Khi tải cao, gateway timeout sau 30 giây','TO_DO','HIGHEST',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','CRITICAL',NULL,NULL,NULL,NULL,0,'2026-09-26 10:47:00','2026-09-26 10:47:00',64,'',6.00,6.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(67,1,'NUC-398',398,'TESTCASE','TestCase: Kiểm thử gateway timeout dưới tải 500 RPS','Dùng k6 load test','DONE','MEDIUM',1,1,'ACTIVE_SPRINT',NULL,1,NULL,'AUTOMATED',NULL,NULL,'PASSED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-09-26 10:47:00','2026-10-26 17:16:03',66,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Main/Core','Sprint 1 (2026)'),(69,10,'DNB-1',1,'TASK','Trao đổi yêu cầu khách hàng (Đã cập nhật)','','IN_PROGRESS','MEDIUM',1,2,'ACTIVE_SPRINT',NULL,5,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-10-27 21:49:15','2026-10-27 22:00:55',NULL,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Dự án bank demo\\Core Banking & Integration','Dự án bank demo\\Sprint 1 (2026)'),(70,10,'DNB-2',2,'STORY','Test Trao đổi yêu cầu khách hàng [VERIFY]','Chi tiết yêu cầu kỹ thuật','TO_DO','HIGH',1,2,'ACTIVE_SPRINT',NULL,3,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-10-27 21:53:08','2026-10-27 21:59:30',NULL,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Dự án bank demo','Dự án bank demo\\Sprint 1 (2026)'),(72,10,'DNB-4',4,'TASK','Test issue direct','Test description','TO_DO','HIGH',1,NULL,'ACTIVE_SPRINT',NULL,1,NULL,'MANUAL',NULL,NULL,'UNTESTED','MAJOR',NULL,NULL,NULL,NULL,0,'2026-10-27 22:17:58','2026-10-27 22:21:08',NULL,'',0.00,0.00,0.00,NULL,NULL,NULL,NULL,'Dự án bank demo','Dự án bank demo\\Sprint 1 (2026)');
/*!40000 ALTER TABLE `project_issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_members`
--

DROP TABLE IF EXISTS `project_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('ADMIN','MEMBER','VIEWER') NOT NULL DEFAULT 'MEMBER',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_prj_user` (`project_id`,`user_id`),
  KEY `fk_pm_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_members`
--

LOCK TABLES `project_members` WRITE;
/*!40000 ALTER TABLE `project_members` DISABLE KEYS */;
INSERT INTO `project_members` VALUES (1,1,1,'ADMIN','2026-09-22 21:53:56'),(2,1,2,'MEMBER','2026-09-22 21:53:56'),(3,1,3,'MEMBER','2026-09-22 21:53:56'),(4,1,4,'MEMBER','2026-09-22 21:53:56'),(5,1,5,'MEMBER','2026-09-22 21:53:56'),(6,1,6,'MEMBER','2026-09-22 21:53:56'),(7,1,7,'MEMBER','2026-09-22 21:53:56'),(25,1,17,'MEMBER','2026-09-22 22:21:58'),(26,1,18,'MEMBER','2026-09-22 22:21:58'),(27,1,19,'MEMBER','2026-09-22 22:21:58'),(28,1,20,'MEMBER','2026-09-22 22:21:58'),(29,1,21,'MEMBER','2026-09-22 22:21:58'),(30,1,22,'MEMBER','2026-09-22 22:21:58'),(31,1,23,'MEMBER','2026-09-22 22:21:58'),(32,1,24,'MEMBER','2026-09-22 22:21:58'),(33,1,25,'MEMBER','2026-09-22 22:21:58'),(34,1,26,'MEMBER','2026-09-22 22:21:58'),(35,1,27,'ADMIN','2026-09-22 22:21:58'),(36,1,28,'ADMIN','2026-09-22 22:21:58'),(37,1,29,'ADMIN','2026-09-22 22:21:58'),(38,1,30,'ADMIN','2026-09-22 22:21:58'),(39,1,31,'ADMIN','2026-09-22 22:21:58'),(40,1,32,'ADMIN','2026-09-22 22:21:58'),(41,1,33,'ADMIN','2026-09-22 22:21:58'),(42,1,34,'MEMBER','2026-09-22 22:21:58'),(43,1,35,'MEMBER','2026-09-22 22:21:58'),(44,1,36,'MEMBER','2026-09-22 22:21:58'),(45,1,37,'MEMBER','2026-09-22 22:21:58'),(46,1,38,'MEMBER','2026-09-22 22:21:58'),(47,1,39,'MEMBER','2026-09-22 22:21:58'),(48,1,40,'MEMBER','2026-09-22 22:21:58'),(49,1,41,'MEMBER','2026-09-22 22:21:58'),(50,1,42,'MEMBER','2026-09-22 22:21:58'),(51,1,43,'MEMBER','2026-09-22 22:21:58'),(52,1,44,'MEMBER','2026-09-22 22:21:58'),(53,1,45,'MEMBER','2026-09-22 22:21:58'),(54,1,46,'MEMBER','2026-09-22 22:21:58'),(58,8,2,'ADMIN','2026-09-26 11:34:38'),(59,8,1,'MEMBER','2026-09-26 11:34:38'),(60,8,3,'MEMBER','2026-09-26 11:34:38'),(61,8,39,'MEMBER','2026-09-26 11:34:38'),(63,10,2,'ADMIN','2026-10-27 21:48:39'),(64,10,39,'MEMBER','2026-10-27 21:48:39'),(65,10,41,'MEMBER','2026-10-27 21:48:39'),(66,10,42,'MEMBER','2026-10-27 21:48:39'),(67,10,43,'MEMBER','2026-10-27 21:48:39');
/*!40000 ALTER TABLE `project_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_key` varchar(10) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `project_type` enum('SOFTWARE','BUSINESS','SERVICE_DESK') NOT NULL DEFAULT 'SOFTWARE',
  `lead_id` int(11) NOT NULL,
  `avatar_icon` varchar(50) NOT NULL DEFAULT 'bi-rocket-takeoff-fill',
  `avatar_bg` varchar(20) NOT NULL DEFAULT '#6554C0',
  `status` enum('ACTIVE','ARCHIVED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_key` (`project_key`),
  KEY `idx_prj_key` (`project_key`),
  KEY `idx_prj_lead` (`lead_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'NUC','Beyond Gravity','Nền tảng thương mại điện tử du lịch & đặt chuyến bay không gian','SOFTWARE',1,'bi-rocket-takeoff-fill','#6554C0','ACTIVE','2026-09-22 21:53:56','2026-09-22 21:53:56'),(8,'DNA','Dự án AI','','SOFTWARE',2,'bi-rocket-takeoff-fill','#6554C0','ACTIVE','2026-09-26 11:34:37','2026-09-26 11:34:37'),(10,'DNB','Dự án bank demo','','SOFTWARE',2,'bi-rocket-takeoff-fill','#0052CC','ACTIVE','2026-10-27 21:48:39','2026-10-27 21:48:39');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_rp_perm` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),(1,21),(1,22),(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,11),(2,12),(2,13),(2,14),(2,15),(2,16),(2,17),(2,18),(2,19),(2,20),(3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,11),(3,12),(3,13),(3,18),(3,19),(3,20),(4,2),(4,3),(4,4),(4,6),(4,11);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'ADMIN','Quản trị hệ thống','Toàn quyền cấu hình, tài khoản, SLA và danh mục','2026-09-20 08:53:23'),(2,'IT_MANAGER','IT Manager / Leader','Quản lý phân công, điều phối nhóm, giám sát SLA và báo cáo','2026-09-20 08:53:23'),(3,'IT_STAFF','Nhân viên IT','Tiếp nhận, xử lý, comment nội bộ và hoàn thành ticket','2026-09-20 08:53:23'),(4,'USER','Người dùng nội bộ','Tạo yêu cầu hỗ trợ, theo dõi tiến độ và nghiệm thu','2026-09-20 08:53:23');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sla_configs`
--

DROP TABLE IF EXISTS `sla_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sla_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `resolution_time` int(11) NOT NULL,
  `resolution_unit` enum('HOUR','DAY') NOT NULL DEFAULT 'HOUR',
  `warning_threshold_percent` int(11) NOT NULL DEFAULT 70,
  `critical_threshold_percent` int(11) NOT NULL DEFAULT 90,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_sla_priority` (`priority_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sla_configs`
--

LOCK TABLES `sla_configs` WRITE;
/*!40000 ALTER TABLE `sla_configs` DISABLE KEYS */;
INSERT INTO `sla_configs` VALUES (1,1,'SLA Mức Thường',2,'DAY',70,90,1,'2026-09-20 08:53:24'),(2,2,'SLA Mức Ưu tiên',1,'DAY',70,90,1,'2026-09-20 08:53:24'),(3,3,'SLA Mức Khẩn cấp',3,'HOUR',70,90,1,'2026-09-20 08:53:24');
/*!40000 ALTER TABLE `sla_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_approvals`
--

DROP TABLE IF EXISTS `ticket_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `change_id` int(11) DEFAULT NULL,
  `approver_id` int(11) NOT NULL,
  `step_name` varchar(100) NOT NULL DEFAULT 'Quản lý phê duyệt',
  `status` enum('PENDING','APPROVED','REJECTED','RETURNED') NOT NULL DEFAULT 'PENDING',
  `comment` text DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_appr_ticket` (`ticket_id`),
  KEY `idx_appr_change` (`change_id`),
  KEY `idx_appr_user` (`approver_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_approvals`
--

LOCK TABLES `ticket_approvals` WRITE;
/*!40000 ALTER TABLE `ticket_approvals` DISABLE KEYS */;
INSERT INTO `ticket_approvals` VALUES (1,14,NULL,1,'Phê duyệt trang thiết bị','PENDING',NULL,NULL,'2026-09-21 15:27:05'),(2,16,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:27:28','2026-09-21 15:27:28'),(3,18,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:28:00','2026-09-21 15:28:00'),(4,20,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:28:59','2026-09-21 15:28:59'),(5,22,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:29:17','2026-09-21 15:29:17'),(6,26,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:29:52','2026-09-21 15:29:52'),(7,28,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:31:06','2026-09-21 15:31:06'),(8,32,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:38:17','2026-09-21 15:38:17'),(9,34,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 21:10:46','2026-09-21 16:10:46'),(10,38,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 21:37:18','2026-09-21 16:37:18'),(11,42,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 21:47:53','2026-09-21 16:47:53'),(12,44,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 21:58:34','2026-09-21 16:58:34'),(13,46,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 22:06:14','2026-09-21 17:06:14'),(14,48,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-21 22:33:39','2026-09-21 17:33:39'),(15,50,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-09-22 21:58:52','2026-09-22 16:58:52'),(16,53,NULL,2,'Phê duyệt của IT Manager','PENDING',NULL,NULL,'2026-09-22 17:45:33'),(17,54,NULL,2,'Phê duyệt của IT Manager','APPROVED','','2026-09-26 11:44:01','2026-09-22 18:05:38'),(18,62,NULL,1,'Phê duyệt trang thiết bị','APPROVED','Đã đồng ý cấp theo ngân sách quý','2026-10-26 17:16:07','2026-10-26 11:16:07'),(19,63,NULL,2,'Phê duyệt của IT Manager','PENDING',NULL,NULL,'2026-10-26 11:38:00');
/*!40000 ALTER TABLE `ticket_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_assets`
--

DROP TABLE IF EXISTS `ticket_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ticket_asset` (`ticket_id`,`asset_id`),
  KEY `fk_ta_asset_rel` (`asset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_assets`
--

LOCK TABLES `ticket_assets` WRITE;
/*!40000 ALTER TABLE `ticket_assets` DISABLE KEYS */;
INSERT INTO `ticket_assets` VALUES (1,1,1,'2026-09-20 12:56:11'),(2,5,1,'2026-09-20 12:56:11');
/*!40000 ALTER TABLE `ticket_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_assignments`
--

DROP TABLE IF EXISTS `ticket_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) NOT NULL,
  `assigned_by_user_id` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ta_ticket` (`ticket_id`),
  KEY `fk_ta_from_user` (`from_user_id`),
  KEY `fk_ta_to_user` (`to_user_id`),
  KEY `fk_ta_by_user` (`assigned_by_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_assignments`
--

LOCK TABLES `ticket_assignments` WRITE;
/*!40000 ALTER TABLE `ticket_assignments` DISABLE KEYS */;
INSERT INTO `ticket_assignments` VALUES (1,1,NULL,3,2,'Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 08:57:40'),(2,2,NULL,3,2,'Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 08:57:59'),(3,3,NULL,3,2,'Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 09:03:01'),(4,4,NULL,3,2,'Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 09:06:59'),(5,5,NULL,5,2,'','2026-09-20 09:55:33'),(6,9,NULL,3,2,'Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 18:50:54'),(7,12,NULL,3,2,'Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 19:14:49');
/*!40000 ALTER TABLE `ticket_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_attachments`
--

DROP TABLE IF EXISTS `ticket_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_att_ticket` (`ticket_id`),
  KEY `fk_att_comment` (`comment_id`),
  KEY `fk_att_user` (`uploaded_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_attachments`
--

LOCK TABLES `ticket_attachments` WRITE;
/*!40000 ALTER TABLE `ticket_attachments` DISABLE KEYS */;
INSERT INTO `ticket_attachments` VALUES (1,1,8,'Giải pháp gói MIG 11.09.2026.docx','uploads/1_1789906581_d966b6c28ba1.docx',33726,'application/vnd.openxmlformats-officedocument.wordprocessingml.document',5,'2026-09-20 19:16:22');
/*!40000 ALTER TABLE `ticket_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_categories`
--

DROP TABLE IF EXISTS `ticket_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `default_group_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_cat_default_group` (`default_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_categories`
--

LOCK TABLES `ticket_categories` WRITE;
/*!40000 ALTER TABLE `ticket_categories` DISABLE KEYS */;
INSERT INTO `ticket_categories` VALUES (1,'NETWORK','Mạng & Kết nối','Các sự cố WiFi, VPN, mạng Internet, chập chờn đường truyền',2,1,'2026-09-20 08:53:24'),(2,'HARDWARE','Thiết bị & Phần cứng','Hỏng chuột, bàn phím, màn hình, máy in, PC/Laptop không khởi động',5,1,'2026-09-20 08:53:24'),(3,'SOFTWARE','Phần mềm ứng dụng','Lỗi phần mềm kế toán, ERP, HRM, Office, cài đặt ứng dụng',4,1,'2026-09-20 08:53:24'),(4,'ACCOUNT','Tài khoản & Quyền hạn','Cấp mới tài khoản, reset mật khẩu, mở khóa email, cấp quyền truy cập',1,1,'2026-09-20 08:53:24');
/*!40000 ALTER TABLE `ticket_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_checklists`
--

DROP TABLE IF EXISTS `ticket_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `item_text` varchar(255) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tcl_ticket` (`ticket_id`),
  KEY `fk_tcl_user` (`completed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_checklists`
--

LOCK TABLES `ticket_checklists` WRITE;
/*!40000 ALTER TABLE `ticket_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_comments`
--

DROP TABLE IF EXISTS `ticket_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_internal` tinyint(1) DEFAULT 0,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_tc_ticket` (`ticket_id`),
  KEY `fk_tc_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_comments`
--

LOCK TABLES `ticket_comments` WRITE;
/*!40000 ALTER TABLE `ticket_comments` DISABLE KEYS */;
INSERT INTO `ticket_comments` VALUES (1,5,5,0,'Chị xem lại giúp em có được chưa ạ','2026-09-20 09:56:24'),(2,5,6,0,'Ok đã được','2026-09-20 09:56:34'),(3,1,6,0,'@it_app Xem này nhé','2026-09-20 10:01:34'),(4,1,6,0,'@it_app  xin chào','2026-09-20 10:03:10'),(5,1,6,0,'@it_app  xin chào','2026-09-20 10:08:12'),(6,1,6,0,'@it_app  xin chào','2026-09-20 10:10:47'),(7,1,5,0,'@user1  xin chào bạn','2026-09-20 10:11:14'),(8,1,5,0,'@user1 coi lại nhé em','2026-09-20 19:16:21'),(9,13,1,0,'Chào Đỗ Mai Phương,\r\n\r\nBộ phận IT Support đã tiếp nhận yêu cầu #IT-000013 của bạn về sự cố: \"Test ITIL Incident: Server Outage in Production\".\r\n\r\nKỹ thuật viên đang tiến hành kiểm tra hệ thống và sẽ hỗ trợ xử lý dứt điểm cho bạn trong thời gian sớm nhất theo cam kết SLA. Nếu bạn đang ở tại bàn làm việc, xin vui lòng chuẩn bị máy hoặc cho phép chúng tôi kết nối hỗ trợ từ xa khi cần thiết.\r\n\r\nTrân trọng cảm ơn bạn!\r\n---\r\nSystem Administrator - IT Service Desk','2026-09-22 22:41:00'),(10,1,1,0,'Chào Đỗ Mai Phương,\r\n\r\nBộ phận IT Support đã tiếp nhận yêu cầu #IT-000001 của bạn về sự cố: \"Mất kết nối máy chủ dữ liệu kế toán\".\r\n\r\nKỹ thuật viên đang tiến hành kiểm tra hệ thống và sẽ hỗ trợ xử lý dứt điểm cho bạn trong thời gian sớm nhất theo cam kết SLA. Nếu bạn đang ở tại bàn làm việc, xin vui lòng chuẩn bị máy hoặc cho phép chúng tôi kết nối hỗ trợ từ xa khi cần thiết.\r\n\r\nTrân trọng cảm ơn bạn!\r\n---\r\nSystem Administrator - IT Service Desk','2026-09-23 08:16:21');
/*!40000 ALTER TABLE `ticket_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_history`
--

DROP TABLE IF EXISTS `ticket_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `field_changed` varchar(50) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_history_ticket` (`ticket_id`),
  KEY `fk_th_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=283 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_history`
--

LOCK TABLES `ticket_history` WRITE;
/*!40000 ALTER TABLE `ticket_history` DISABLE KEYS */;
INSERT INTO `ticket_history` VALUES (1,1,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo ticket mới','2026-09-20 08:57:40'),(2,1,2,'ASSIGN','assignee_id','Chưa phân công','Lê Quốc Bảo (Helpdesk)','Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 08:57:40'),(3,1,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Bắt đầu kiểm tra switch mạng','2026-09-20 08:57:40'),(4,1,3,'TRANSFER_GROUP','group_id','IT Network & Hạ tầng','IT Network & Hạ tầng','Chuyển nhóm: Lỗi từ switch core của nhóm Network','2026-09-20 08:57:40'),(5,2,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo ticket mới','2026-09-20 08:57:59'),(6,2,2,'ASSIGN','assignee_id','Chưa phân công','Lê Quốc Bảo (Helpdesk)','Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 08:57:59'),(7,2,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Bắt đầu kiểm tra switch mạng','2026-09-20 08:57:59'),(8,2,3,'TRANSFER_GROUP','group_id','IT Network & Hạ tầng','IT Network & Hạ tầng','Chuyển nhóm: Lỗi từ switch core của nhóm Network','2026-09-20 08:57:59'),(9,2,4,'CHANGE_STATUS','status','ASSIGNED','WAITING_USER','Chờ kế toán cung cấp địa chỉ IP máy trạm','2026-09-20 08:57:59'),(10,2,4,'CHANGE_STATUS','status','WAITING_USER','IN_PROGRESS','User đã cung cấp IP, tiếp tục xử lý','2026-09-20 08:58:01'),(11,2,4,'RESOLVE','status','IN_PROGRESS','RESOLVED','Giải pháp: Đã cấu hình lại VLAN 20 trên Switch Core','2026-09-20 08:58:01'),(12,2,6,'CLOSE','status','RESOLVED','CLOSED','Đã vào lại được phần mềm kế toán bình thường, cảm ơn IT!','2026-09-20 08:58:01'),(13,3,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo ticket mới','2026-09-20 09:03:01'),(14,3,2,'ASSIGN','assignee_id','Chưa phân công','Lê Quốc Bảo (Helpdesk)','Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 09:03:01'),(15,3,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Bắt đầu kiểm tra switch mạng','2026-09-20 09:03:01'),(16,3,3,'TRANSFER_GROUP','group_id','IT Network & Hạ tầng','IT Network & Hạ tầng','Chuyển nhóm: Lỗi từ switch core của nhóm Network','2026-09-20 09:03:02'),(17,3,4,'CHANGE_STATUS','status','ASSIGNED','WAITING_USER','Chờ kế toán cung cấp địa chỉ IP máy trạm','2026-09-20 09:03:02'),(18,3,4,'CHANGE_STATUS','status','WAITING_USER','IN_PROGRESS','User đã cung cấp IP, tiếp tục xử lý','2026-09-20 09:03:04'),(19,3,4,'RESOLVE','status','IN_PROGRESS','RESOLVED','Giải pháp: Đã cấu hình lại VLAN 20 trên Switch Core','2026-09-20 09:03:04'),(20,3,6,'CLOSE','status','RESOLVED','CLOSED','Đã vào lại được phần mềm kế toán bình thường, cảm ơn IT!','2026-09-20 09:03:04'),(21,4,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo ticket mới','2026-09-20 09:06:59'),(22,4,2,'ASSIGN','assignee_id','Chưa phân công','Lê Quốc Bảo (Helpdesk)','Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 09:06:59'),(23,4,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Bắt đầu kiểm tra switch mạng','2026-09-20 09:06:59'),(24,4,3,'TRANSFER_GROUP','group_id','IT Network & Hạ tầng','IT Network & Hạ tầng','Chuyển nhóm: Lỗi từ switch core của nhóm Network','2026-09-20 09:06:59'),(25,4,4,'CHANGE_STATUS','status','ASSIGNED','WAITING_USER','Chờ kế toán cung cấp địa chỉ IP máy trạm','2026-09-20 09:06:59'),(26,4,4,'CHANGE_STATUS','status','WAITING_USER','IN_PROGRESS','User đã cung cấp IP, tiếp tục xử lý','2026-09-20 09:07:01'),(27,4,4,'RESOLVE','status','IN_PROGRESS','RESOLVED','Giải pháp: Đã cấu hình lại VLAN 20 trên Switch Core','2026-09-20 09:07:01'),(28,4,6,'CLOSE','status','RESOLVED','CLOSED','Đã vào lại được phần mềm kế toán bình thường, cảm ơn IT!','2026-09-20 09:07:01'),(29,5,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo ticket mới','2026-09-20 09:54:31'),(30,5,2,'TRANSFER_GROUP','group_id','IT Phần cứng & Thiết bị','IT Phần mềm & Ứng dụng','Chuyển nhóm: Giao việc','2026-09-20 09:55:25'),(31,5,2,'ASSIGN','assignee_id','Chưa phân công','Phạm Hồng Phúc (Application)','Phân công nhân viên phụ trách','2026-09-20 09:55:33'),(32,5,5,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-20 09:56:24'),(33,5,6,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-20 09:56:34'),(34,5,5,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Kỹ thuật viên bắt đầu xử lý','2026-09-20 09:56:38'),(35,5,5,'RESOLVE','status','IN_PROGRESS','RESOLVED','Giải pháp: test','2026-09-20 09:56:52'),(36,5,6,'CLOSE','status','RESOLVED','CLOSED','Người dùng xác nhận hoàn thành','2026-09-20 09:56:57'),(37,1,6,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-20 10:01:34'),(38,1,6,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-20 10:03:10'),(39,1,6,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-20 10:08:12'),(40,1,6,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-20 10:10:47'),(41,1,5,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-20 10:11:14'),(42,1,1,'SLA_OVERDUE','sla_status','WITHIN_SLA','OVERDUE','Hệ thống tự động phát hiện vi phạm SLA cam kết (Rule 16)','2026-09-20 18:49:12'),(43,6,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo ticket mới','2026-09-20 18:49:45'),(44,6,2,'ADJUST_PRIORITY','priority_id','Khẩn cấp','Thường','Điều chỉnh mức độ ưu tiên: Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc','2026-09-20 18:49:45'),(45,6,3,'CHANGE_STATUS','status','NEW','WAITING_VENDOR','Chờ đối tác VNPT kiểm tra đường truyền cáp quang','2026-09-20 18:49:45'),(46,6,3,'CHANGE_STATUS','status','WAITING_VENDOR','IN_PROGRESS','Đối tác đã xử lý xong','2026-09-20 18:49:45'),(47,6,3,'LINK_ASSET','asset_id',NULL,'Asset #6','Cập nhật thiết bị liên quan','2026-09-20 18:49:45'),(48,6,3,'DUPLICATE','status','IN_PROGRESS','DUPLICATE','Đánh dấu trùng với ticket #IT-000001. Lý do: Trùng với sự cố mạng LAN IT-000001','2026-09-20 18:49:45'),(57,9,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo ticket mới','2026-09-20 18:50:54'),(58,9,2,'ASSIGN','assignee_id','Chưa phân công','Lê Quốc Bảo (Helpdesk)','Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 18:50:54'),(59,9,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Bắt đầu kiểm tra switch mạng','2026-09-20 18:50:54'),(60,9,3,'TRANSFER_GROUP','group_id','IT Network & Hạ tầng','IT Network & Hạ tầng','Chuyển nhóm: Lỗi từ switch core của nhóm Network','2026-09-20 18:50:54'),(61,9,4,'CHANGE_STATUS','status','ASSIGNED','WAITING_USER','Chờ kế toán cung cấp địa chỉ IP máy trạm','2026-09-20 18:50:54'),(62,9,4,'CHANGE_STATUS','status','WAITING_USER','IN_PROGRESS','User đã cung cấp IP, tiếp tục xử lý','2026-09-20 18:50:57'),(63,9,4,'RESOLVE','status','IN_PROGRESS','RESOLVED','Giải pháp: Đã cấu hình lại VLAN 20 trên Switch Core','2026-09-20 18:50:57'),(64,9,6,'CLOSE','status','RESOLVED','CLOSED','Đã vào lại được phần mềm kế toán bình thường, cảm ơn IT!','2026-09-20 18:50:57'),(65,1,3,'LOG_WORK','worklog',NULL,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-20 19:14:44'),(66,1,3,'LINK_ISSUE','ticket_links',NULL,'Liên kết với ticket #2','Loại liên kết: blocks','2026-09-20 19:14:44'),(67,1,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Di chuyển sang In Progress trên Board','2026-09-20 19:14:44'),(76,12,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo ticket mới','2026-09-20 19:14:49'),(77,12,2,'ASSIGN','assignee_id','Chưa phân công','Lê Quốc Bảo (Helpdesk)','Giao cho Bảo Helpdesk tiếp nhận','2026-09-20 19:14:49'),(78,12,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Bắt đầu kiểm tra switch mạng','2026-09-20 19:14:49'),(79,12,3,'TRANSFER_GROUP','group_id','IT Network & Hạ tầng','IT Network & Hạ tầng','Chuyển nhóm: Lỗi từ switch core của nhóm Network','2026-09-20 19:14:49'),(80,12,4,'CHANGE_STATUS','status','ASSIGNED','WAITING_USER','Chờ kế toán cung cấp địa chỉ IP máy trạm','2026-09-20 19:14:49'),(81,12,4,'CHANGE_STATUS','status','WAITING_USER','IN_PROGRESS','User đã cung cấp IP, tiếp tục xử lý','2026-09-20 19:14:52'),(82,12,4,'RESOLVE','status','IN_PROGRESS','RESOLVED','Giải pháp: Đã cấu hình lại VLAN 20 trên Switch Core','2026-09-20 19:14:52'),(83,12,6,'CLOSE','status','RESOLVED','CLOSED','Đã vào lại được phần mềm kế toán bình thường, cảm ơn IT!','2026-09-20 19:14:52'),(84,1,5,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-20 19:16:21'),(85,13,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 20:27:05'),(86,13,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 20:27:05'),(87,13,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 20:27:05'),(88,13,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 20:27:05'),(89,14,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 20:27:05'),(90,15,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 20:27:28'),(91,15,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 20:27:28'),(92,15,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 20:27:28'),(93,15,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 20:27:28'),(94,16,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 20:27:28'),(95,16,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:27:28'),(96,17,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 20:28:00'),(97,17,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 20:28:00'),(98,17,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 20:28:00'),(99,17,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 20:28:00'),(100,18,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 20:28:00'),(101,18,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:28:00'),(102,19,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 20:28:59'),(103,19,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 20:28:59'),(104,19,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 20:28:59'),(105,19,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 20:28:59'),(106,20,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 20:28:59'),(107,20,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:28:59'),(108,19,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000003): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 20:28:59'),(109,21,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 20:29:17'),(110,21,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 20:29:17'),(111,21,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 20:29:17'),(112,21,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 20:29:17'),(113,22,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 20:29:17'),(114,22,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:29:17'),(115,21,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000004): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 20:29:17'),(116,16,3,'LOG_WORK','worklog',NULL,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-21 20:29:23'),(117,16,3,'LINK_ISSUE','ticket_links',NULL,'Liên kết với ticket #2','Loại liên kết: blocks','2026-09-21 20:29:23'),(118,16,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Di chuyển sang In Progress trên Board','2026-09-21 20:29:23'),(127,25,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 20:29:52'),(128,25,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 20:29:52'),(129,25,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 20:29:52'),(130,25,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 20:29:52'),(131,26,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 20:29:52'),(132,26,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:29:52'),(133,25,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000005): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 20:29:52'),(134,27,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 20:31:06'),(135,27,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 20:31:06'),(136,27,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 20:31:06'),(137,27,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 20:31:06'),(138,28,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 20:31:06'),(139,28,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:31:06'),(140,27,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000006): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 20:31:06'),(149,18,3,'LOG_WORK','worklog',NULL,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-21 20:31:06'),(150,18,3,'LINK_ISSUE','ticket_links',NULL,'Liên kết với ticket #2','Loại liên kết: blocks','2026-09-21 20:31:06'),(151,18,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Di chuyển sang In Progress trên Board','2026-09-21 20:31:06'),(152,31,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 20:38:17'),(153,31,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 20:38:17'),(154,31,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 20:38:17'),(155,31,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 20:38:17'),(156,32,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 20:38:17'),(157,32,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 20:38:17'),(158,31,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000007): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 20:38:17'),(159,33,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 21:10:46'),(160,33,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 21:10:46'),(161,33,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 21:10:46'),(162,33,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 21:10:46'),(163,34,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 21:10:46'),(164,34,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 21:10:46'),(165,33,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000008): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 21:10:46'),(166,20,3,'LOG_WORK','worklog',NULL,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-21 21:10:54'),(167,20,3,'LINK_ISSUE','ticket_links',NULL,'Liên kết với ticket #2','Loại liên kết: blocks','2026-09-21 21:10:54'),(168,20,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Di chuyển sang In Progress trên Board','2026-09-21 21:10:54'),(177,37,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 21:37:18'),(178,37,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 21:37:18'),(179,37,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 21:37:18'),(180,37,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 21:37:18'),(181,38,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 21:37:18'),(182,38,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 21:37:18'),(183,37,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000009): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 21:37:18'),(184,22,3,'LOG_WORK','worklog',NULL,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-21 21:37:22'),(185,22,3,'LINK_ISSUE','ticket_links',NULL,'Liên kết với ticket #2','Loại liên kết: blocks','2026-09-21 21:37:22'),(186,22,3,'CHANGE_STATUS','status','ASSIGNED','IN_PROGRESS','Di chuyển sang In Progress trên Board','2026-09-21 21:37:22'),(195,41,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 21:47:53'),(196,41,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 21:47:53'),(197,41,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 21:47:53'),(198,41,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 21:47:53'),(199,42,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 21:47:53'),(200,42,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 21:47:53'),(201,41,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000010): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 21:47:53'),(202,43,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 21:58:34'),(203,43,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 21:58:34'),(204,43,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 21:58:34'),(205,43,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 21:58:34'),(206,44,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 21:58:34'),(207,44,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 21:58:34'),(208,43,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000011): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 21:58:34'),(209,45,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 22:06:14'),(210,45,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 22:06:14'),(211,45,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 22:06:14'),(212,45,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 22:06:14'),(213,46,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 22:06:14'),(214,46,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 22:06:14'),(215,45,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000012): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 22:06:14'),(216,47,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-21 22:33:39'),(217,47,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-21 22:33:39'),(218,47,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-21 22:33:39'),(219,47,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-21 22:33:39'),(220,48,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-21 22:33:39'),(221,48,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-21 22:33:39'),(222,47,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000013): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-21 22:33:39'),(223,49,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-09-22 21:58:52'),(224,49,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-09-22 21:58:52'),(225,49,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-09-22 21:58:52'),(226,49,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-09-22 21:58:52'),(227,50,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-09-22 21:58:52'),(228,50,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-09-22 21:58:52'),(229,49,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000014): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-09-22 21:58:52'),(230,13,1,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-22 22:41:00'),(239,53,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 3)','2026-09-22 22:45:33'),(240,54,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 3)','2026-09-22 23:05:38'),(241,1,1,'COMMENT',NULL,NULL,NULL,'Thêm bình luận trao đổi','2026-09-23 08:16:21'),(258,54,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: ','2026-09-26 11:44:01'),(259,54,1,'RESOLVE','status','ASSIGNED','RESOLVED','Giải pháp: đã xử lý','2026-09-26 11:44:15'),(260,54,1,'LOG_WORK','worklog',NULL,'2h','','2026-09-26 11:44:32'),(261,1,1,'CHANGE_STATUS','status','IN_PROGRESS','NEW','Chuyển cột trên Kanban Board','2026-09-26 12:02:04'),(262,14,1,'CHANGE_STATUS','status','NEW','IN_PROGRESS','Kỹ thuật viên bắt đầu xử lý','2026-09-26 12:02:13'),(271,61,6,'CREATE_TICKET',NULL,NULL,'NEW','Khởi tạo INCIDENT (Tier: L1, Ưu tiên: 3)','2026-10-26 17:16:07'),(272,61,3,'TIER_ESCALATED','L1','L2','NEW','Chuyển cấp hỗ trợ từ L1 lên L2. Lý do: Cần kỹ thuật chuyên sâu kiểm tra query slow log','2026-10-26 17:16:07'),(273,61,3,'TIER_ESCALATED','L2','L3','NEW','Chuyển cấp hỗ trợ từ L2 lên L3. Lý do: Cần can thiệp kernel Linux & DBA expert','2026-10-26 17:16:07'),(274,61,3,'TIER_ESCALATED','L3','VENDOR','WAITING_VENDOR','Chuyển cấp hỗ trợ từ L3 lên VENDOR. Lý do: Chuyển nhà thầu phần cứng Dell','2026-10-26 17:16:07'),(275,62,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 1)','2026-10-26 17:16:07'),(276,62,1,'APPROVAL','approval_status','PENDING','APPROVED','Phê duyệt: APPROVED - Lý do: Đã đồng ý cấp theo ngân sách quý','2026-10-26 17:16:07'),(277,61,3,'RESOLVE','status','WAITING_VENDOR','RESOLVED','Giải pháp: Đã giải quyết theo sự cố gốc (Problem #PRB-000015): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.','2026-10-26 17:16:07'),(278,63,6,'CREATE_SERVICE_REQUEST',NULL,NULL,'NEW','Khởi tạo SERVICE_REQUEST (Tier: L1, Ưu tiên: 3)','2026-10-26 17:38:00'),(279,1,1,'CHANGE_STATUS','status','NEW','IN_PROGRESS','Kỹ thuật viên bắt đầu xử lý','2026-10-26 17:40:07'),(280,1,1,'RESOLVE','status','IN_PROGRESS','RESOLVED','Giải pháp: 123','2026-10-26 17:40:13'),(281,62,1,'CHANGE_STATUS','status','ASSIGNED','NEW','Chuyển cột trên Kanban Board','2026-11-17 21:54:04'),(282,62,1,'CHANGE_STATUS','status','NEW','IN_PROGRESS','Chuyển cột trên Kanban Board','2026-11-17 21:54:05');
/*!40000 ALTER TABLE `ticket_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_links`
--

DROP TABLE IF EXISTS `ticket_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_ticket_id` int(11) NOT NULL,
  `target_ticket_id` int(11) NOT NULL,
  `link_type` enum('relates_to','blocks','is_blocked_by','causes') NOT NULL DEFAULT 'relates_to',
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ticket_link` (`source_ticket_id`,`target_ticket_id`,`link_type`),
  KEY `idx_tl_src` (`source_ticket_id`),
  KEY `idx_tl_tgt` (`target_ticket_id`),
  KEY `fk_tl_user` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_links`
--

LOCK TABLES `ticket_links` WRITE;
/*!40000 ALTER TABLE `ticket_links` DISABLE KEYS */;
INSERT INTO `ticket_links` VALUES (1,1,2,'blocks',3,'2026-09-20 19:14:44'),(2,16,2,'blocks',3,'2026-09-21 20:29:23'),(3,18,2,'blocks',3,'2026-09-21 20:31:06'),(4,20,2,'blocks',3,'2026-09-21 21:10:54'),(5,22,2,'blocks',3,'2026-09-21 21:37:22');
/*!40000 ALTER TABLE `ticket_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_priorities`
--

DROP TABLE IF EXISTS `ticket_priorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_priorities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#6c757d',
  `level` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_priorities`
--

LOCK TABLES `ticket_priorities` WRITE;
/*!40000 ALTER TABLE `ticket_priorities` DISABLE KEYS */;
INSERT INTO `ticket_priorities` VALUES (1,'NORMAL','Thường','#0ea5e9',1,1),(2,'HIGH','Ưu tiên','#f59e0b',2,1),(3,'URGENT','Khẩn cấp','#ef4444',3,1);
/*!40000 ALTER TABLE `ticket_priorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_ratings`
--

DROP TABLE IF EXISTS `ticket_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL CHECK (`score` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_id` (`ticket_id`),
  KEY `fk_tr_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_ratings`
--

LOCK TABLES `ticket_ratings` WRITE;
/*!40000 ALTER TABLE `ticket_ratings` DISABLE KEYS */;
INSERT INTO `ticket_ratings` VALUES (1,2,6,5,'Kỹ thuật viên hỗ trợ rất nhanh và nhiệt tình, máy in đã hoạt động tốt.','2026-09-20 13:33:04'),(2,3,7,4,'Xử lý ổn thỏa, thời gian phản hồi nhanh.','2026-09-20 13:33:04');
/*!40000 ALTER TABLE `ticket_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_services`
--

DROP TABLE IF EXISTS `ticket_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_service_cat_code` (`category_id`,`code`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_services`
--

LOCK TABLES `ticket_services` WRITE;
/*!40000 ALTER TABLE `ticket_services` DISABLE KEYS */;
INSERT INTO `ticket_services` VALUES (1,1,'VPN','Kết nối VPN từ xa','Cấp quyền hoặc xử lý lỗi không truy cập được VPN',1,'2026-09-20 08:53:24'),(2,1,'WIFI','Sự cố WiFi văn phòng','Mất sóng WiFi, không xác thực được tài khoản WiFi',1,'2026-09-20 08:53:24'),(3,1,'LAN','Mạng có dây LAN','Dây mạng đứt, không nhận IP mạng nội bộ',1,'2026-09-20 08:53:24'),(4,2,'LAPTOP','Sửa chữa Laptop / PC','Máy tính chậm, đơ màn hình xanh, không lên nguồn',1,'2026-09-20 08:53:24'),(5,2,'PRINTER','Máy in / Máy scan','Kẹt giấy, không in được tài liệu, chia sẻ máy in',1,'2026-09-20 08:53:24'),(6,2,'MONITOR','Màn hình hiển thị','Màn hình nhấp nháy, sọc màu, không lên tín hiệu',1,'2026-09-20 08:53:24'),(7,3,'ERP','Hệ thống ERP','Lỗi phân hệ kế toán, kho vận, bán hàng trên ERP',1,'2026-09-20 08:53:24'),(8,3,'EMAIL','Email doanh nghiệp','Hộp thư đầy, không gửi nhận được thư',1,'2026-09-20 08:53:24'),(9,3,'OFFICE','Microsoft Office / OS','Lỗi Windows, Excel, Word, bản quyền',1,'2026-09-20 08:53:24'),(10,4,'PASS_RESET','Khôi phục mật khẩu','Quên mật khẩu máy tính, email hoặc ứng dụng',1,'2026-09-20 08:53:24'),(11,4,'NEW_USER','Cấp tài khoản nhân sự mới','Tạo email, tài khoản AD, cấp phân quyền theo phòng ban',1,'2026-09-20 08:53:24');
/*!40000 ALTER TABLE `ticket_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_sla_pauses`
--

DROP TABLE IF EXISTS `ticket_sla_pauses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_sla_pauses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `status_trigger` varchar(50) NOT NULL,
  `paused_at` datetime NOT NULL,
  `resumed_at` datetime DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `paused_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_tsp_ticket` (`ticket_id`),
  KEY `fk_tsp_user` (`paused_by_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_sla_pauses`
--

LOCK TABLES `ticket_sla_pauses` WRITE;
/*!40000 ALTER TABLE `ticket_sla_pauses` DISABLE KEYS */;
INSERT INTO `ticket_sla_pauses` VALUES (1,2,'WAITING_USER','2026-09-20 03:57:59','2026-09-20 03:58:01',2,'Chờ kế toán cung cấp địa chỉ IP máy trạm',4,'2026-09-20 08:57:59'),(2,3,'WAITING_USER','2026-09-20 04:03:02','2026-09-20 04:03:04',2,'Chờ kế toán cung cấp địa chỉ IP máy trạm',4,'2026-09-20 09:03:02'),(3,4,'WAITING_USER','2026-09-20 04:06:59','2026-09-20 04:07:01',2,'Chờ kế toán cung cấp địa chỉ IP máy trạm',4,'2026-09-20 09:06:59'),(4,6,'WAITING_VENDOR','2026-09-20 13:49:45','2026-09-20 13:49:45',0,'Chờ đối tác VNPT kiểm tra đường truyền cáp quang',3,'2026-09-20 18:49:45'),(6,9,'WAITING_USER','2026-09-20 13:50:54','2026-09-20 13:50:56',2,'Chờ kế toán cung cấp địa chỉ IP máy trạm',4,'2026-09-20 18:50:54'),(8,12,'WAITING_USER','2026-09-20 14:14:49','2026-09-20 14:14:52',2,'Chờ kế toán cung cấp địa chỉ IP máy trạm',4,'2026-09-20 19:14:49');
/*!40000 ALTER TABLE `ticket_sla_pauses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_transfers`
--

DROP TABLE IF EXISTS `ticket_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `from_group_id` int(11) DEFAULT NULL,
  `to_group_id` int(11) NOT NULL,
  `transferred_by_user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_tt_ticket` (`ticket_id`),
  KEY `fk_tt_from_group` (`from_group_id`),
  KEY `fk_tt_to_group` (`to_group_id`),
  KEY `fk_tt_by_user` (`transferred_by_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_transfers`
--

LOCK TABLES `ticket_transfers` WRITE;
/*!40000 ALTER TABLE `ticket_transfers` DISABLE KEYS */;
INSERT INTO `ticket_transfers` VALUES (1,1,2,2,3,'Lỗi từ switch core của nhóm Network','2026-09-20 08:57:40'),(2,2,2,2,3,'Lỗi từ switch core của nhóm Network','2026-09-20 08:57:59'),(3,3,2,2,3,'Lỗi từ switch core của nhóm Network','2026-09-20 09:03:02'),(4,4,2,2,3,'Lỗi từ switch core của nhóm Network','2026-09-20 09:06:59'),(5,5,5,4,2,'Giao việc','2026-09-20 09:55:25'),(6,9,2,2,3,'Lỗi từ switch core của nhóm Network','2026-09-20 18:50:54'),(7,12,2,2,3,'Lỗi từ switch core của nhóm Network','2026-09-20 19:14:49');
/*!40000 ALTER TABLE `ticket_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_watchers`
--

DROP TABLE IF EXISTS `ticket_watchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_watchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_watcher` (`ticket_id`,`user_id`),
  KEY `fk_tw_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_watchers`
--

LOCK TABLES `ticket_watchers` WRITE;
/*!40000 ALTER TABLE `ticket_watchers` DISABLE KEYS */;
INSERT INTO `ticket_watchers` VALUES (11,14,1,'2026-09-26 12:02:40');
/*!40000 ALTER TABLE `ticket_watchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_worklogs`
--

DROP TABLE IF EXISTS `ticket_worklogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_worklogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time_spent_minutes` int(11) NOT NULL,
  `time_spent_formatted` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `logged_date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_twl_ticket` (`ticket_id`),
  KEY `idx_twl_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_worklogs`
--

LOCK TABLES `ticket_worklogs` WRITE;
/*!40000 ALTER TABLE `ticket_worklogs` DISABLE KEYS */;
INSERT INTO `ticket_worklogs` VALUES (1,1,3,135,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-20','2026-09-20 19:14:44'),(2,16,3,135,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-21','2026-09-21 20:29:23'),(3,18,3,135,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-21','2026-09-21 20:31:06'),(4,20,3,135,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-21','2026-09-21 21:10:54'),(5,22,3,135,'2h 15m','Kiểm tra cáp mạng và cấu hình Switch','2026-09-21','2026-09-21 21:37:22'),(6,54,1,120,'2h','','2026-09-26','2026-09-26 11:44:32');
/*!40000 ALTER TABLE `ticket_worklogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_code` varchar(20) NOT NULL,
  `ticket_type` enum('INCIDENT','SERVICE_REQUEST','PROBLEM','CHANGE') NOT NULL DEFAULT 'INCIDENT',
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `requester_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `priority_id` int(11) NOT NULL,
  `impact` enum('HIGH','MEDIUM','LOW') NOT NULL DEFAULT 'MEDIUM',
  `urgency` enum('HIGH','MEDIUM','LOW') NOT NULL DEFAULT 'MEDIUM',
  `support_tier` enum('L1','L2','L3','VENDOR') NOT NULL DEFAULT 'L1',
  `approval_status` enum('NONE','PENDING','APPROVED','REJECTED','RETURNED') NOT NULL DEFAULT 'NONE',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approval_note` text DEFAULT NULL,
  `problem_id` int(11) DEFAULT NULL,
  `change_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `assignee_id` int(11) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'NEW',
  `sla_id` int(11) DEFAULT NULL,
  `sla_value` int(11) NOT NULL DEFAULT 0,
  `sla_unit` varchar(10) NOT NULL DEFAULT 'HOUR',
  `sla_started_at` datetime DEFAULT NULL,
  `sla_due_at` datetime DEFAULT NULL,
  `sla_status` enum('WITHIN_SLA','WARNING','CRITICAL','OVERDUE') DEFAULT 'WITHIN_SLA',
  `sla_paused_seconds` int(11) DEFAULT 0,
  `pause_started_at` datetime DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `root_cause` text DEFAULT NULL,
  `solution` text DEFAULT NULL,
  `reopen_count` int(11) DEFAULT 0,
  `reopen_reason` text DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL,
  `duplicate_of_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_code` (`ticket_code`),
  KEY `idx_tickets_status` (`status`),
  KEY `idx_tickets_requester` (`requester_id`),
  KEY `idx_tickets_group` (`group_id`),
  KEY `idx_tickets_assignee` (`assignee_id`),
  KEY `idx_tickets_sla_due` (`sla_due_at`),
  KEY `idx_tickets_sla_status` (`sla_status`),
  KEY `fk_tk_category` (`category_id`),
  KEY `fk_tk_service` (`service_id`),
  KEY `fk_tk_priority` (`priority_id`),
  KEY `fk_tk_sla` (`sla_id`),
  KEY `fk_tk_duplicate` (`duplicate_of_id`),
  KEY `fk_tk_asset` (`asset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,'IT-000001','INCIDENT','Mất kết nối máy chủ dữ liệu kế toán','Không thể mở phần mềm kế toán từ 8h sáng, toàn bộ phòng kế toán bị ảnh hưởng',6,1,1,1,3,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,2,NULL,'RESOLVED',3,3,'HOUR','2026-09-20 03:57:40','2026-09-20 06:57:40','OVERDUE',0,NULL,'123','','',0,NULL,NULL,NULL,'Tầng 2, Phòng Kế toán','Ext 102','2026-09-20 03:57:40','2026-10-26 17:40:13','2026-10-26 17:40:13',NULL),(2,'IT-000002','INCIDENT','Mất kết nối máy chủ dữ liệu kế toán','Không thể mở phần mềm kế toán từ 8h sáng, toàn bộ phòng kế toán bị ảnh hưởng',6,1,1,NULL,3,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,2,NULL,'CLOSED',3,3,'HOUR','2026-09-20 03:57:59','2026-09-20 06:58:01','WITHIN_SLA',2,NULL,'Đã cấu hình lại VLAN 20 trên Switch Core','Xung đột dải IP','Cập nhật lại DHCP range',0,NULL,NULL,NULL,'Tầng 2, Phòng Kế toán','Ext 102','2026-09-20 03:57:59','2026-09-20 08:58:01','2026-09-20 08:58:01','2026-09-20 08:58:01'),(3,'IT-000003','INCIDENT','Mất kết nối máy chủ dữ liệu kế toán','Không thể mở phần mềm kế toán từ 8h sáng, toàn bộ phòng kế toán bị ảnh hưởng',6,1,1,NULL,3,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,2,NULL,'CLOSED',3,3,'HOUR','2026-09-20 04:03:01','2026-09-20 07:03:03','WITHIN_SLA',2,NULL,'Đã cấu hình lại VLAN 20 trên Switch Core','Xung đột dải IP','Cập nhật lại DHCP range',0,NULL,NULL,NULL,'Tầng 2, Phòng Kế toán','Ext 102','2026-09-20 04:03:01','2026-09-20 09:03:04','2026-09-20 09:03:04','2026-09-20 09:03:04'),(4,'IT-000004','INCIDENT','Mất kết nối máy chủ dữ liệu kế toán','Không thể mở phần mềm kế toán từ 8h sáng, toàn bộ phòng kế toán bị ảnh hưởng',6,1,1,NULL,3,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,2,NULL,'CLOSED',3,3,'HOUR','2026-09-20 04:06:59','2026-09-20 07:07:01','WITHIN_SLA',2,NULL,'Đã cấu hình lại VLAN 20 trên Switch Core','Xung đột dải IP','Cập nhật lại DHCP range',0,NULL,NULL,NULL,'Tầng 2, Phòng Kế toán','Ext 102','2026-09-20 04:06:59','2026-09-20 09:07:01','2026-09-20 09:07:01','2026-09-20 09:07:01'),(5,'IT-000005','INCIDENT','Máy in không hoạt động được, cần hỗ trợ','Máy in không hoạt động được, cần hỗ trợ',6,2,5,1,3,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,4,5,'CLOSED',3,3,'HOUR','2026-09-20 09:54:31','2026-09-20 12:54:31','WITHIN_SLA',0,NULL,'test','','',0,NULL,NULL,NULL,'Lầu 3','0329587081','2026-09-20 09:54:31','2026-09-20 12:56:11','2026-09-20 09:56:52','2026-09-20 09:56:57'),(6,'IT-000006','INCIDENT','Test Ticket for FSD Rules Verification','Test description',6,1,NULL,6,1,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,2,NULL,'DUPLICATE',3,3,'HOUR','2026-09-20 13:49:45','2026-09-20 16:49:45','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,1,NULL,NULL,'2026-09-20 13:49:45','2026-09-20 18:49:45',NULL,NULL),(9,'IT-000007','INCIDENT','Mất kết nối máy chủ dữ liệu kế toán','Không thể mở phần mềm kế toán từ 8h sáng, toàn bộ phòng kế toán bị ảnh hưởng',6,1,1,NULL,3,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,2,NULL,'CLOSED',3,3,'HOUR','2026-09-20 13:50:54','2026-09-20 16:50:56','WITHIN_SLA',2,NULL,'Đã cấu hình lại VLAN 20 trên Switch Core','Xung đột dải IP','Cập nhật lại DHCP range',0,NULL,NULL,NULL,'Tầng 2, Phòng Kế toán','Ext 102','2026-09-20 13:50:54','2026-09-20 18:50:57','2026-09-20 18:50:57','2026-09-20 18:50:57'),(12,'IT-000010','INCIDENT','Mất kết nối máy chủ dữ liệu kế toán','Không thể mở phần mềm kế toán từ 8h sáng, toàn bộ phòng kế toán bị ảnh hưởng',6,1,1,NULL,3,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,2,NULL,'CLOSED',3,3,'HOUR','2026-09-20 14:14:49','2026-09-20 17:14:51','WITHIN_SLA',2,NULL,'Đã cấu hình lại VLAN 20 trên Switch Core','Xung đột dải IP','Cập nhật lại DHCP range',0,NULL,NULL,NULL,'Tầng 2, Phòng Kế toán','Ext 102','2026-09-20 14:14:49','2026-09-20 19:14:52','2026-09-20 19:14:52','2026-09-20 19:14:52'),(13,'IT-000013','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,NULL,NULL,1,NULL,'WAITING_VENDOR',3,3,'HOUR','2026-09-21 15:27:05','2026-09-21 18:27:05','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:27:05','2026-09-21 15:27:05',NULL,NULL),(14,'IT-000014','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','PENDING',NULL,NULL,NULL,NULL,NULL,1,NULL,'IN_PROGRESS',1,2,'DAY','2026-09-21 15:27:05','2026-09-23 15:27:05','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:27:05','2026-09-26 12:02:13',NULL,NULL),(15,'IT-000015','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,1,NULL,1,NULL,'WAITING_VENDOR',3,3,'HOUR','2026-09-21 15:27:28','2026-09-21 18:27:28','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:27:28','2026-09-21 20:27:28',NULL,NULL),(16,'IT-000016','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 20:27:28','Đã đồng ý cấp theo ngân sách quý',NULL,NULL,1,NULL,'IN_PROGRESS',1,2,'DAY','2026-09-21 15:27:28','2026-09-23 15:27:28','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:27:28','2026-09-21 20:29:23',NULL,NULL),(17,'IT-000017','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,2,NULL,1,NULL,'WAITING_VENDOR',3,3,'HOUR','2026-09-21 15:28:00','2026-09-21 18:28:00','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:28:00','2026-09-21 20:28:00',NULL,NULL),(18,'IT-000018','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 20:28:00','Đã đồng ý cấp theo ngân sách quý',NULL,NULL,1,NULL,'IN_PROGRESS',1,2,'DAY','2026-09-21 15:28:00','2026-09-23 15:28:00','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:28:00','2026-09-21 20:31:06',NULL,NULL),(19,'IT-000019','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,3,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 15:28:59','2026-09-21 18:28:59','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000003): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:28:59','2026-09-21 20:28:59','2026-09-21 20:28:59',NULL),(20,'IT-000020','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 20:28:59','Đã đồng ý cấp theo ngân sách quý',NULL,1,1,NULL,'IN_PROGRESS',1,2,'DAY','2026-09-21 15:28:59','2026-09-23 15:28:59','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:28:59','2026-09-21 21:10:54',NULL,NULL),(21,'IT-000021','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,4,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 15:29:17','2026-09-21 18:29:17','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000004): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:29:17','2026-09-21 20:29:17','2026-09-21 20:29:17',NULL),(22,'IT-000022','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 20:29:17','Đã đồng ý cấp theo ngân sách quý',NULL,2,1,NULL,'IN_PROGRESS',1,2,'DAY','2026-09-21 15:29:17','2026-09-23 15:29:17','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:29:17','2026-09-21 21:37:22',NULL,NULL),(25,'IT-000023','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,5,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 15:29:52','2026-09-21 18:29:52','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000005): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:29:52','2026-09-21 20:29:52','2026-09-21 20:29:52',NULL),(26,'IT-000026','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 20:29:52','Đã đồng ý cấp theo ngân sách quý',NULL,3,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 15:29:52','2026-09-23 15:29:52','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:29:52','2026-09-21 20:29:52',NULL,NULL),(27,'IT-000027','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,6,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 15:31:06','2026-09-21 18:31:06','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000006): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:31:06','2026-09-21 20:31:06','2026-09-21 20:31:06',NULL),(28,'IT-000028','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 20:31:06','Đã đồng ý cấp theo ngân sách quý',NULL,4,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 15:31:06','2026-09-23 15:31:06','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:31:06','2026-09-21 20:31:06',NULL,NULL),(31,'IT-000029','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,7,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 15:38:17','2026-09-21 18:38:17','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000007): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:38:17','2026-09-21 20:38:17','2026-09-21 20:38:17',NULL),(32,'IT-000032','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 20:38:17','Đã đồng ý cấp theo ngân sách quý',NULL,5,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 15:38:17','2026-09-23 15:38:17','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 15:38:17','2026-09-21 20:38:17',NULL,NULL),(33,'IT-000033','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,8,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 16:10:46','2026-09-21 19:10:46','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000008): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 16:10:46','2026-09-21 21:10:46','2026-09-21 21:10:46',NULL),(34,'IT-000034','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 21:10:46','Đã đồng ý cấp theo ngân sách quý',NULL,6,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 16:10:46','2026-09-23 16:10:46','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 16:10:46','2026-09-21 21:10:46',NULL,NULL),(37,'IT-000035','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,9,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 16:37:18','2026-09-21 19:37:18','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000009): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 16:37:18','2026-09-21 21:37:18','2026-09-21 21:37:18',NULL),(38,'IT-000038','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 21:37:18','Đã đồng ý cấp theo ngân sách quý',NULL,7,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 16:37:18','2026-09-23 16:37:18','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 16:37:18','2026-09-21 21:37:18',NULL,NULL),(41,'IT-000039','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,10,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 16:47:53','2026-09-21 19:47:53','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000010): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 16:47:53','2026-09-21 21:47:53','2026-09-21 21:47:53',NULL),(42,'IT-000042','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 21:47:53','Đã đồng ý cấp theo ngân sách quý',NULL,8,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 16:47:53','2026-09-23 16:47:53','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 16:47:53','2026-09-21 21:47:53',NULL,NULL),(43,'IT-000043','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,11,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 16:58:34','2026-09-21 19:58:34','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000011): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 16:58:34','2026-09-21 21:58:34','2026-09-21 21:58:34',NULL),(44,'IT-000044','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 21:58:34','Đã đồng ý cấp theo ngân sách quý',NULL,9,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 16:58:34','2026-09-23 16:58:34','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 16:58:34','2026-09-21 21:58:34',NULL,NULL),(45,'IT-000045','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,12,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 17:06:14','2026-09-21 20:06:14','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000012): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 17:06:14','2026-09-21 22:06:14','2026-09-21 22:06:14',NULL),(46,'IT-000046','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 22:06:14','Đã đồng ý cấp theo ngân sách quý',NULL,10,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 17:06:14','2026-09-23 17:06:14','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 17:06:14','2026-09-21 22:06:14',NULL,NULL),(47,'IT-000047','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,13,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-21 17:33:39','2026-09-21 20:33:39','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000013): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 17:33:39','2026-09-21 22:33:39','2026-09-21 22:33:39',NULL),(48,'IT-000048','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-21 22:33:39','Đã đồng ý cấp theo ngân sách quý',NULL,11,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-21 17:33:39','2026-09-23 17:33:39','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-21 17:33:39','2026-09-21 22:33:39',NULL,NULL),(49,'IT-000049','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,14,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-09-22 16:58:52','2026-09-22 19:58:52','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000014): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-22 16:58:52','2026-09-22 21:58:52','2026-09-22 21:58:52',NULL),(50,'IT-000050','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-09-22 21:58:52','Đã đồng ý cấp theo ngân sách quý',NULL,12,1,NULL,'ASSIGNED',1,2,'DAY','2026-09-22 16:58:52','2026-09-24 16:58:52','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-09-22 16:58:52','2026-09-22 21:58:52',NULL,NULL),(53,'IT-000051','SERVICE_REQUEST','Cấp quyền truy cập hệ thống ERP SAP','Nhân viên kế toán cần quyền nhập chứng từ trên phân hệ FI.',6,1,NULL,NULL,3,'MEDIUM','MEDIUM','L1','PENDING',NULL,NULL,NULL,NULL,NULL,2,NULL,'NEW',3,3,'HOUR','2026-09-22 17:45:33','2026-09-22 20:45:33','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'Tầng 2 - Phòng Kế Toán','0901234567','2026-09-22 17:45:33','2026-09-22 17:45:33',NULL,NULL),(54,'IT-000054','SERVICE_REQUEST','Cấp quyền truy cập hệ thống ERP SAP','Nhân viên kế toán cần quyền nhập chứng từ trên phân hệ FI.',6,1,NULL,NULL,3,'MEDIUM','MEDIUM','L1','APPROVED',1,'2026-09-26 11:44:01','',NULL,NULL,2,NULL,'RESOLVED',3,3,'HOUR','2026-09-22 18:05:38','2026-09-22 21:05:38','WITHIN_SLA',0,NULL,'đã xử lý','','',0,NULL,NULL,NULL,'Tầng 2 - Phòng Kế Toán','0901234567','2026-09-22 18:05:38','2026-09-26 11:44:15','2026-09-26 11:44:15',NULL),(61,'IT-000055','INCIDENT','Test ITIL Incident: Server Outage in Production','Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',6,4,NULL,NULL,3,'HIGH','HIGH','VENDOR','NONE',NULL,NULL,NULL,15,NULL,1,NULL,'RESOLVED',3,3,'HOUR','2026-10-26 11:16:07','2026-10-26 14:16:07','WITHIN_SLA',0,NULL,'Đã giải quyết theo sự cố gốc (Problem #PRB-000015): Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-10-26 11:16:07','2026-10-26 17:16:07','2026-10-26 17:16:07',NULL),(62,'IT-000062','SERVICE_REQUEST','Test Service Request: Cấp quyền VPN & Laptop mới','Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',6,4,NULL,NULL,1,'LOW','MEDIUM','L1','APPROVED',1,'2026-10-26 17:16:07','Đã đồng ý cấp theo ngân sách quý',NULL,13,1,NULL,'IN_PROGRESS',1,2,'DAY','2026-10-26 11:16:07','2026-10-28 11:16:07','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-10-26 11:16:07','2026-11-17 21:54:05',NULL,NULL),(63,'IT-000063','SERVICE_REQUEST','Cấp quyền truy cập hệ thống ERP SAP','Nhân viên kế toán cần quyền nhập chứng từ trên phân hệ FI.',6,1,NULL,NULL,3,'MEDIUM','MEDIUM','L1','PENDING',NULL,NULL,NULL,NULL,NULL,2,NULL,'NEW',3,3,'HOUR','2026-10-26 11:38:00','2026-10-26 14:38:00','WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,'Tầng 2 - Phòng Kế Toán','0901234567','2026-10-26 11:38:00','2026-10-26 11:38:00',NULL,NULL),(64,'TICK-2026-081','INCIDENT','Yêu cầu kiểm tra kết nối mạng máy in kế toán','Máy in hóa đơn Canon trong phòng kế toán không nhận lệnh in từ mạng LAN.',17,1,NULL,NULL,3,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,1,3,'IN_PROGRESS',NULL,0,'HOUR',NULL,NULL,'WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-11-17 20:11:51','2026-11-17 22:15:49',NULL,NULL),(65,'TICK-2026-082','SERVICE_REQUEST','Cài đặt phần mềm đọc hóa đơn XML và ký số','Cần IT cài đặt phần mềm iTaxViewer và cập nhật chứng thư số mới.',17,2,NULL,NULL,2,'MEDIUM','MEDIUM','L1','NONE',NULL,NULL,NULL,NULL,NULL,1,NULL,'NEW',NULL,0,'HOUR',NULL,NULL,'WITHIN_SLA',0,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'2026-11-17 21:41:51','2026-11-17 22:15:49',NULL,NULL);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_ur_role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1),(2,2),(3,3),(4,3),(5,3),(6,4),(7,4),(17,4),(18,4),(19,4),(20,4),(21,4),(22,4),(23,4),(24,4),(25,4),(26,4),(27,1),(28,1),(29,2),(30,2),(31,2),(32,2),(33,2),(34,3),(35,3),(36,3),(37,3),(38,3),(39,3),(40,3),(41,3),(42,3),(43,3),(44,3),(45,3),(46,3);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `seat_location` varchar(150) DEFAULT NULL,
  `job_title` varchar(150) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@itdesk.local','$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76','Administrator Updated','0912345678','IT Department','Tầng 5 - P.501','Chief Information Officer',NULL,1,'2026-09-20 08:53:24','2026-09-22 22:40:03'),(2,'manager','manager@itdesk.local','$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76','Trần Văn Hoàng (IT Leader)','0901000002','IT Management','Tầng 4 - P.402 (Phòng IT Leader)','Trưởng nhóm IT Service Desk',NULL,1,'2026-09-20 08:53:24','2026-09-22 22:21:39'),(3,'it_helpdesk','helpdesk@itdesk.local','$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76','Lê Quốc Bảo (Helpdesk)','0901000003','IT Helpdesk','Tầng 4 - Bàn HD-01 (Quầy Hỗ trợ)','Chuyên viên Hỗ trợ Kỹ thuật L1',NULL,1,'2026-09-20 08:53:24','2026-09-22 22:21:39'),(4,'it_network','network@itdesk.local','$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76','Nguyễn Minh Tuấn (Network)','0901000004','IT Network','Tầng 4 - Bàn NET-01 (Khu Hạ tầng)','Kỹ sư Mạng & Viễn thông',NULL,1,'2026-09-20 08:53:24','2026-09-22 22:21:39'),(5,'it_app','app@itdesk.local','$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76','Phạm Hồng Phúc (Application)','0901000005','IT Application','Tầng 4 - Bàn APP-01 (Khu Phần mềm)','Kỹ sư Phần mềm Ứng dụng',NULL,1,'2026-09-20 08:53:24','2026-09-22 22:21:39'),(6,'user1','user1@company.local','$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76','Đỗ Mai Phương','0902000001','Phòng Kế Toán','Tầng 2 - P.KT-01 (Phòng Kế Toán)','Chuyên viên Kế toán Chi tiết',NULL,1,'2026-09-20 08:53:24','2026-09-22 22:21:39'),(7,'user2','user2@company.local','$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76','Vũ Đình Trọng','0902000002','Phòng Nhân Sự','Tầng 2 - P.NS-02 (Phòng Nhân Sự)','Chuyên viên Nhân sự & C&B',NULL,1,'2026-09-20 08:53:24','2026-09-22 22:21:39'),(17,'ketoan_lan','lan.nguyen@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Nguyễn Thị Hương Lan','0903001001','Phòng Kế Toán','Tầng 2 - Phòng KT-01 (Kế Toán Tổng Hợp)','Chuyên viên Kế toán Tổng hợp',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(18,'ketoan_minh','minh.tran@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Trần Đức Minh','0903001002','Phòng Kế Toán','Tầng 2 - Phòng KT-02 (Kế Toán Thuế)','Kế toán Thuế & Ngân sách',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(19,'ketoan_thao','thao.pham@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Phạm Thu Thảo','0903001003','Phòng Kế Toán','Tầng 2 - Phòng KT-03 (Công Nợ)','Kế toán Thanh toán & Công nợ',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(20,'ketoan_truong','truong.hoang@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Hoàng Văn Trường','0903001004','Phòng Kế Toán','Tầng 2 - Phòng KT-Trưởng (Kế Toán Trưởng)','Kế toán Trưởng',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(21,'ketoan_huyen','huyen.vu@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Vũ Khánh Huyền','0903001005','Phòng Kế Toán','Tầng 2 - Phòng KT-04 (Thủ Quỹ)','Thủ quỹ & Kế toán Kho',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(22,'kinhdoanh_nam','nam.dang@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Đặng Hải Nam','0904001001','Phòng Kinh Doanh','Tầng 3 - P.KD-01 (Trưởng Phòng KD)','Trưởng phòng Kinh doanh',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(23,'kinhdoanh_nga','nga.bui@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Bùi Thanh Nga','0904001002','Phòng Kinh Doanh','Tầng 3 - Bàn KD-02 (Sales B2B)','Chuyên viên Kinh doanh B2B',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(24,'kinhdoanh_quang','quang.le@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Lê Nhật Quang','0904001003','Phòng Kinh Doanh','Tầng 3 - Bàn KD-03 (Thị Trường)','Chuyên viên Phát triển Thị trường',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(25,'kinhdoanh_yen','yen.ngo@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Ngô Hải Yến','0904001004','Phòng Kinh Doanh','Tầng 3 - Bàn KD-04 (CSKH Doanh Nghiệp)','Quản lý Khách hàng Doanh nghiệp',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(26,'kinhdoanh_dat','dat.do@company.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Đỗ Thành Đạt','0904001005','Phòng Kinh Doanh','Tầng 3 - Bàn KD-05 (Dự Án)','Nhân viên Kinh doanh Dự án',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(27,'it_cio','tri.phan@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','PGS. TS. Phan Đình Trí','0901999001','Ban Giám Đốc CNTT','Tầng 5 - Phòng CIO-01 (Giám Đốc)','Giám đốc Công nghệ Thông tin (CIO)',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(28,'it_director','bach.dao@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Đào Xuân Bách','0901999002','Ban Giám Đốc CNTT','Tầng 5 - Phòng IT-02 (Phó Giám Đốc)','Phó Giám đốc Khối Vận hành IT',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(29,'it_mgr_helpdesk','dung.nguyen@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Nguyễn Anh Dũng','0901999003','IT Helpdesk & Hỗ Trợ','Tầng 4 - P.HD-Lead (Trưởng Nhóm Helpdesk)','Trưởng nhóm IT Helpdesk & Support',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(30,'it_mgr_network','long.trinh@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Trịnh Bá Long','0901999004','IT Network & Hạ Tầng','Tầng 4 - P.NET-01 (Trưởng Phòng Mạng)','Trưởng phòng Mạng & Hạ tầng',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(31,'it_mgr_system','dang.vu@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Vũ Hải Đăng','0901999005','IT Hệ Thống Máy Chủ','Tầng 4 - P.SYS-01 (Trưởng Nhóm Hệ Thống)','Trưởng nhóm Quản trị Hệ thống & Cloud',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(32,'it_mgr_dev','hung.mai@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Mai Thế Hùng','0901999006','IT Phần Mềm & Ứng Dụng','Tầng 4 - P.DEV-01 (Trưởng Phòng Phần Mềm)','Trưởng phòng Phát triển Phần mềm',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(33,'it_mgr_qa','trang.ho@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Hồ Quỳnh Trang','0901999007','IT Đảm Bảo Chất Lượng QA/QC','Tầng 4 - P.QA-01 (Trưởng Nhóm QA)','Trưởng nhóm Đảm bảo Chất lượng (QA Lead)',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(34,'it_hd_tuan','tuan.luu@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Lưu Minh Tuấn','0901999008','IT Helpdesk & Hỗ Trợ','Tầng 4 - Bàn HD-01 (Quầy Helpdesk)','Kỹ thuật viên Helpdesk L1',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(35,'it_hd_linh','linh.dinh@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Đinh Mỹ Linh','0901999009','IT Helpdesk & Hỗ Trợ','Tầng 4 - Bàn HD-02 (Quầy Helpdesk)','Kỹ thuật viên Helpdesk L1',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(36,'it_hd_viet','viet.chu@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Chu Quốc Việt','0901999010','IT Phần Cứng & Thiết Bị','Tầng 4 - Bàn HD-03 (Hỗ Trợ On-site)','Kỹ thuật viên Hỗ trợ On-site L2',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(37,'it_net_kien','kien.duong@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Dương Trung Kiên','0901999011','IT Network & Hạ Tầng','Tầng 4 - Bàn NET-02 (Kỹ Sư Mạng)','Kỹ sư Mạng & Firewall Cisco/Fortinet',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(38,'it_net_son','son.doan@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Đoàn Ngọc Sơn','0901999012','IT Network & Hạ Tầng','Tầng 4 - Bàn NET-03 (Hạ Tầng)','Kỹ sư Hạ tầng Mạng & Viễn thông',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(39,'it_sys_duc','duc.nguyen@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Nguyễn Văn Đức','0901999013','IT Hệ Thống Máy Chủ','Tầng 4 - Bàn SYS-02 (Quản Trị Linux)','Kỹ sư Hệ thống Linux & Kubernetes',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(40,'it_sys_thinh','thinh.tran@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Trần Quang Thịnh','0901999014','IT Hệ Thống Máy Chủ','Tầng 4 - Bàn SYS-03 (Windows Server)','Chuyên viên Windows Server & AD',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(41,'it_dev_phong','phong.hoang@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Hoàng Nam Phong','0901999015','IT Phần Mềm & Ứng Dụng','Tầng 4 - Bàn DEV-02 (Lập Trình Backend)','Lập trình viên Backend PHP/MySQL',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(42,'it_dev_mai','mai.ta@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Tạ Phương Mai','0901999016','IT Phần Mềm & Ứng Dụng','Tầng 4 - Bàn DEV-03 (Lập Trình UI/UX)','Lập trình viên Frontend UI/UX',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(43,'it_qa_vy','vy.luong@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Lương Thảo Vy','0901999017','IT Đảm Bảo Chất Lượng QA/QC','Tầng 4 - Bàn QA-02 (Kiểm Thử Manual)','Kỹ sư Kiểm thử Phần mềm (QA Tester)',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(44,'it_qa_hieu','hieu.bui@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Bùi Trung Hiếu','0901999018','IT Đảm Bảo Chất Lượng QA/QC','Tầng 4 - Bàn QA-03 (Kiểm Thử Tự Động)','Kỹ sư Kiểm thử Tự động (Automation QA)',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(45,'it_sec_quan','quan.pham@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Phạm Anh Quân','0901999019','IT An Toàn Thông Tin SOC','Tầng 4 - Bàn SEC-01 (Bảo Mật SOC)','Chuyên viên An toàn Thông tin & SOC',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58'),(46,'it_db_khang','khang.vo@itdesk.local','$2y$10$KtGeCFTgWY5x.oKiWPwOweUWN/.c7o4woS34kRDq3Mqd9Fk0S9n1W','Võ Duy Khang','0901999020','IT Hệ Thống Máy Chủ','Tầng 4 - Bàn DBA-01 (Quản Trị CSDL)','Chuyên viên Quản trị Cơ sở Dữ liệu (DBA)',NULL,1,'2026-09-22 22:21:58','2026-09-22 22:21:58');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_statuses`
--

DROP TABLE IF EXISTS `workflow_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `badge_color` varchar(20) NOT NULL DEFAULT '#6c757d',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_closed_state` tinyint(1) DEFAULT 0,
  `pauses_sla` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_statuses`
--

LOCK TABLES `workflow_statuses` WRITE;
/*!40000 ALTER TABLE `workflow_statuses` DISABLE KEYS */;
INSERT INTO `workflow_statuses` VALUES (1,'NEW','Mới tạo','#64748b',1,0,0),(2,'ASSIGNED','Đã phân công','#3b82f6',2,0,0),(3,'IN_PROGRESS','Đang xử lý','#0284c7',3,0,0),(4,'WAITING_USER','Chờ phản hồi','#f59e0b',4,0,1),(5,'WAITING_VENDOR','Chờ đối tác','#8b5cf6',5,0,0),(6,'RESOLVED','Đã xử lý xong','#10b981',6,0,0),(7,'CLOSED','Đã đóng (Nghiệm thu)','#059669',7,1,0),(8,'REOPENED','Mở lại yêu cầu','#ec4899',8,0,0),(9,'CANCELLED','Đã hủy','#94a3b8',9,1,0),(10,'DUPLICATE','Trùng lặp','#6b7280',10,1,0);
/*!40000 ALTER TABLE `workflow_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'it_service_desk'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-17 22:16:32


-- ==========================================================
-- FOREIGN KEYS ADDED AFTER ALL TABLES AND DATA ARE CREATED
-- ==========================================================

ALTER TABLE `asset_handovers` ADD CONSTRAINT `fk_hnd_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;
ALTER TABLE `asset_handovers` ADD CONSTRAINT `fk_hnd_from_user` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `asset_handovers` ADD CONSTRAINT `fk_hnd_performer` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`);
ALTER TABLE `asset_handovers` ADD CONSTRAINT `fk_hnd_to_user` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `asset_repairs` ADD CONSTRAINT `fk_rep_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;
ALTER TABLE `asset_repairs` ADD CONSTRAINT `fk_rep_performer` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`);
ALTER TABLE `assets` ADD CONSTRAINT `fk_asset_category` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`);
ALTER TABLE `assets` ADD CONSTRAINT `fk_asset_user` FOREIGN KEY (`current_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `audit_logs` ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `change_requests` ADD CONSTRAINT `fk_cr_ass` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `change_requests` ADD CONSTRAINT `fk_cr_cab` FOREIGN KEY (`cab_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `change_requests` ADD CONSTRAINT `fk_cr_req` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`);
ALTER TABLE `change_tickets` ADD CONSTRAINT `fk_ct_change` FOREIGN KEY (`change_id`) REFERENCES `change_requests` (`id`) ON DELETE CASCADE;
ALTER TABLE `change_tickets` ADD CONSTRAINT `fk_ct_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `it_group_members` ADD CONSTRAINT `fk_igm_group` FOREIGN KEY (`group_id`) REFERENCES `it_groups` (`id`) ON DELETE CASCADE;
ALTER TABLE `it_group_members` ADD CONSTRAINT `fk_igm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `it_groups` ADD CONSTRAINT `fk_group_leader` FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `jump_command_logs` ADD CONSTRAINT `fk_jcl_session` FOREIGN KEY (`session_id`) REFERENCES `jump_server_sessions` (`id`) ON DELETE CASCADE;
ALTER TABLE `jump_server_permissions` ADD CONSTRAINT `fk_jsp_group` FOREIGN KEY (`group_id`) REFERENCES `it_groups` (`id`) ON DELETE CASCADE;
ALTER TABLE `jump_server_permissions` ADD CONSTRAINT `fk_jsp_server` FOREIGN KEY (`server_id`) REFERENCES `jump_servers` (`id`) ON DELETE CASCADE;
ALTER TABLE `jump_server_permissions` ADD CONSTRAINT `fk_jsp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `jump_server_sessions` ADD CONSTRAINT `fk_jss_kicked_by` FOREIGN KEY (`kicked_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `jump_server_sessions` ADD CONSTRAINT `fk_jss_server` FOREIGN KEY (`server_id`) REFERENCES `jump_servers` (`id`) ON DELETE CASCADE;
ALTER TABLE `jump_server_sessions` ADD CONSTRAINT `fk_jss_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `jump_servers` ADD CONSTRAINT `fk_js_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL;
ALTER TABLE `kb_articles` ADD CONSTRAINT `fk_kb_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);
ALTER TABLE `kb_articles` ADD CONSTRAINT `fk_kb_category` FOREIGN KEY (`category_id`) REFERENCES `kb_categories` (`id`);
ALTER TABLE `notifications` ADD CONSTRAINT `fk_notif_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `notifications` ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `problem_incidents` ADD CONSTRAINT `fk_pi_problem` FOREIGN KEY (`problem_id`) REFERENCES `problems` (`id`) ON DELETE CASCADE;
ALTER TABLE `problem_incidents` ADD CONSTRAINT `fk_pi_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `problems` ADD CONSTRAINT `fk_prb_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `problems` ADD CONSTRAINT `fk_prb_cat` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`);
ALTER TABLE `problems` ADD CONSTRAINT `fk_prb_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
ALTER TABLE `problems` ADD CONSTRAINT `fk_prb_pri` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`);
ALTER TABLE `project_issue_comments` ADD CONSTRAINT `fk_pic_issue` FOREIGN KEY (`issue_id`) REFERENCES `project_issues` (`id`) ON DELETE CASCADE;
ALTER TABLE `project_issue_comments` ADD CONSTRAINT `fk_pic_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `project_issues` ADD CONSTRAINT `fk_pi_assignee` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `project_issues` ADD CONSTRAINT `fk_pi_parent` FOREIGN KEY (`parent_id`) REFERENCES `project_issues` (`id`) ON DELETE SET NULL;
ALTER TABLE `project_issues` ADD CONSTRAINT `fk_pi_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
ALTER TABLE `project_issues` ADD CONSTRAINT `fk_pi_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`);
ALTER TABLE `project_members` ADD CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
ALTER TABLE `project_members` ADD CONSTRAINT `fk_pm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `projects` ADD CONSTRAINT `fk_prj_lead` FOREIGN KEY (`lead_id`) REFERENCES `users` (`id`);
ALTER TABLE `role_permissions` ADD CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
ALTER TABLE `role_permissions` ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
ALTER TABLE `sla_configs` ADD CONSTRAINT `fk_sla_priority` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_approvals` ADD CONSTRAINT `fk_appr_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_approvals` ADD CONSTRAINT `fk_appr_user` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_assets` ADD CONSTRAINT `fk_ta_asset_rel` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_assets` ADD CONSTRAINT `fk_ta_ticket_rel` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_assignments` ADD CONSTRAINT `fk_ta_by_user` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_assignments` ADD CONSTRAINT `fk_ta_from_user` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `ticket_assignments` ADD CONSTRAINT `fk_ta_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_assignments` ADD CONSTRAINT `fk_ta_to_user` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_attachments` ADD CONSTRAINT `fk_att_comment` FOREIGN KEY (`comment_id`) REFERENCES `ticket_comments` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_attachments` ADD CONSTRAINT `fk_att_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_attachments` ADD CONSTRAINT `fk_att_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_categories` ADD CONSTRAINT `fk_cat_default_group` FOREIGN KEY (`default_group_id`) REFERENCES `it_groups` (`id`) ON DELETE SET NULL;
ALTER TABLE `ticket_checklists` ADD CONSTRAINT `fk_tcl_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_checklists` ADD CONSTRAINT `fk_tcl_user` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `ticket_comments` ADD CONSTRAINT `fk_tc_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_comments` ADD CONSTRAINT `fk_tc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_history` ADD CONSTRAINT `fk_th_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_history` ADD CONSTRAINT `fk_th_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_links` ADD CONSTRAINT `fk_tl_src` FOREIGN KEY (`source_ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_links` ADD CONSTRAINT `fk_tl_tgt` FOREIGN KEY (`target_ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_links` ADD CONSTRAINT `fk_tl_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_ratings` ADD CONSTRAINT `fk_tr_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_ratings` ADD CONSTRAINT `fk_tr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_services` ADD CONSTRAINT `fk_service_category` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_sla_pauses` ADD CONSTRAINT `fk_tsp_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_sla_pauses` ADD CONSTRAINT `fk_tsp_user` FOREIGN KEY (`paused_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `ticket_transfers` ADD CONSTRAINT `fk_tt_by_user` FOREIGN KEY (`transferred_by_user_id`) REFERENCES `users` (`id`);
ALTER TABLE `ticket_transfers` ADD CONSTRAINT `fk_tt_from_group` FOREIGN KEY (`from_group_id`) REFERENCES `it_groups` (`id`) ON DELETE SET NULL;
ALTER TABLE `ticket_transfers` ADD CONSTRAINT `fk_tt_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_transfers` ADD CONSTRAINT `fk_tt_to_group` FOREIGN KEY (`to_group_id`) REFERENCES `it_groups` (`id`);
ALTER TABLE `ticket_watchers` ADD CONSTRAINT `fk_tw_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_watchers` ADD CONSTRAINT `fk_tw_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_worklogs` ADD CONSTRAINT `fk_twl_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;
ALTER TABLE `ticket_worklogs` ADD CONSTRAINT `fk_twl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL;
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_assignee` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_category` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`);
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_duplicate` FOREIGN KEY (`duplicate_of_id`) REFERENCES `tickets` (`id`) ON DELETE SET NULL;
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_group` FOREIGN KEY (`group_id`) REFERENCES `it_groups` (`id`) ON DELETE SET NULL;
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_priority` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`);
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`);
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_service` FOREIGN KEY (`service_id`) REFERENCES `ticket_services` (`id`) ON DELETE SET NULL;
ALTER TABLE `tickets` ADD CONSTRAINT `fk_tk_sla` FOREIGN KEY (`sla_id`) REFERENCES `sla_configs` (`id`) ON DELETE SET NULL;
ALTER TABLE `user_roles` ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_roles` ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- ==========================================================
-- REMOTE DESKTOP SUPPORT & ASSISTANCE TABLES
-- ==========================================================

CREATE TABLE IF NOT EXISTS `remote_support_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_code` VARCHAR(20) NOT NULL UNIQUE,
  `ticket_id` INT NULL,
  `staff_id` INT NOT NULL,
  `client_id` INT NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'WAITING',
  `notes` TEXT NULL,
  `ultraviewer_id` VARCHAR(50) NULL,
  `ultraviewer_pass` VARCHAR(50) NULL,
  `started_at` DATETIME NULL,
  `ended_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_code` (`session_code`),
  INDEX `idx_ticket` (`ticket_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `remote_support_signals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_id` INT NOT NULL,
  `sender_type` VARCHAR(20) NOT NULL,
  `signal_type` VARCHAR(30) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_session_sig` (`session_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
