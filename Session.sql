-- =====================================================================
-- Warehouse management - MySQL / MariaDB database
--
-- Import in phpMyAdmin (Import tab) or from the command line:
--   mysql -u root < Session.sql
--
-- Creates the `warehouse` database with all tables, foreign keys and
-- sample data. Sign in with:
--   admin / admin12345            (Administrator - all permissions)
--   manager / manager123          (Warehouse manager)
--   storekeeper / storekeeper123  (Storekeeper)
--   accountant / accountant123    (Accountant - read only)
-- Change these passwords after the first login (Users > Edit).
--
-- Tables
--   usertypes      user types; every permission is a can_* column
--   users          users.usertype_id -> usertypes.id (FOREIGN KEY)
--   units          units of measurement (t, kg, pcs, L, m, ...)
--   categories     product categories
--   suppliers      suppliers
--   products       products.unit_id -> units, products.category_id -> categories
--   stock_entries  goods received: quantity, unit_price, total_value
--                  (= quantity x unit_price), entry_date, supplier, user
--   migrations     schema versions used by `php console migrate`
--
-- The schema is generated from database/migrations; to rebuild it with
-- the framework instead, run: php console migrate:fresh --seed
-- =====================================================================


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

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `warehouse` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `warehouse`;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Construction materials','2026-10-27 19:59:55',NULL),(2,'Food products','2026-10-27 19:59:55',NULL),(3,'Chemicals','2026-10-27 19:59:55',NULL),(4,'Packaging','2026-10-27 19:59:55',NULL),(5,'Electrical','2026-10-27 19:59:55',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migrations_migration_unique` (`migration`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_01_01_000001_create_usertypes_table',1),(2,'2026_01_01_000002_create_users_table',1),(3,'2026_01_01_000003_create_units_table',1),(4,'2026_01_01_000004_create_categories_table',1),(5,'2026_01_01_000005_create_suppliers_table',1),(6,'2026_01_01_000006_create_products_table',1),(7,'2026_01_01_000007_create_stock_entries_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(55) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `category_id` (`category_id`),
  KEY `unit_id` (`unit_id`),
  KEY `products_name_index` (`name`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'CEM-500','Portland cement M500',1,1,NULL,'2026-10-27 19:59:55',NULL),(2,'REB-12','Rebar A500C, 12 mm',1,1,NULL,'2026-10-27 19:59:55',NULL),(3,'SND-01','Washed sand',1,8,NULL,'2026-10-27 19:59:55',NULL),(4,'FLR-50','Wheat flour, premium',2,2,NULL,'2026-10-27 19:59:55',NULL),(5,'SUG-01','Sugar',2,2,NULL,'2026-10-27 19:59:55',NULL),(6,'OIL-5L','Sunflower oil, 5 L bottle',2,4,NULL,'2026-10-27 19:59:55',NULL),(7,'NAOH-25','Caustic soda, 25 kg bag',3,4,NULL,'2026-10-27 19:59:55',NULL),(8,'SOLV-01','Industrial solvent',3,5,NULL,'2026-10-27 19:59:55',NULL),(9,'BOX-60','Cardboard box 60×40×40',4,4,NULL,'2026-10-27 19:59:55',NULL),(10,'FILM-500','Stretch film, 500 mm',4,11,NULL,'2026-10-27 19:59:55',NULL),(11,'CBL-3X25','Copper cable 3×2.5 mm²',5,6,NULL,'2026-10-27 19:59:55',NULL),(12,'LED-18','LED panel 18 W',5,4,NULL,'2026-10-27 19:59:55',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `stock_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` decimal(15,3) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total_value` decimal(20,2) NOT NULL,
  `entry_date` date NOT NULL,
  `document_no` varchar(55) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `user_id` (`user_id`),
  KEY `stock_entries_entry_date_index` (`entry_date`),
  CONSTRAINT `stock_entries_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_entries_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_entries_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_entries` WRITE;
/*!40000 ALTER TABLE `stock_entries` DISABLE KEYS */;
INSERT INTO `stock_entries` VALUES (1,10,3,1,63.600,10.34,657.62,'2026-10-03','INV-202610-001',NULL,'2026-10-03 10:00:00',NULL),(2,10,1,4,70.200,9.36,657.07,'2026-10-17','INV-202610-002',NULL,'2026-10-17 10:00:00',NULL),(3,1,3,3,16.000,142.10,2273.60,'2026-10-23','INV-202610-003',NULL,'2026-10-23 10:00:00',NULL),(4,1,4,1,22.400,156.60,3507.84,'2026-09-28','INV-202609-004',NULL,'2026-09-28 10:00:00',NULL),(5,5,1,3,1815.000,1.34,2432.10,'2026-10-24','INV-202610-005',NULL,'2026-10-24 10:00:00',NULL),(6,8,1,2,306.000,2.50,765.00,'2026-10-23','INV-202610-006',NULL,'2026-10-23 10:00:00',NULL),(7,5,4,2,2130.000,1.32,2811.60,'2026-10-08','INV-202610-007',NULL,'2026-10-08 10:00:00',NULL),(8,4,3,3,1880.000,0.91,1710.80,'2026-10-22','INV-202610-008',NULL,'2026-10-22 10:00:00',NULL),(9,6,4,4,173.000,11.42,1975.66,'2026-10-11','INV-202610-009',NULL,'2026-10-11 10:00:00',NULL),(10,6,1,2,106.000,11.09,1175.54,'2026-09-30','INV-202609-010',NULL,'2026-09-30 10:00:00',NULL),(11,12,4,4,117.000,7.55,883.35,'2026-10-19','INV-202610-011',NULL,'2026-10-19 10:00:00',NULL),(12,7,2,2,38.400,32.32,1241.09,'2026-10-19','INV-202610-012',NULL,'2026-10-19 10:00:00',NULL),(13,9,4,4,512.000,1.18,604.16,'2026-10-24','INV-202610-013',NULL,'2026-10-24 10:00:00',NULL),(14,4,4,1,2820.000,0.89,2509.80,'2026-10-11','INV-202610-014',NULL,'2026-10-11 10:00:00',NULL),(15,5,1,1,1440.000,1.40,2016.00,'2026-10-02','INV-202610-015',NULL,'2026-10-02 10:00:00',NULL),(16,3,1,3,40.800,19.98,815.18,'2026-10-18','INV-202610-016',NULL,'2026-10-18 10:00:00',NULL),(17,7,3,1,37.200,33.28,1238.02,'2026-10-07','INV-202610-017',NULL,'2026-10-07 10:00:00',NULL),(18,9,1,4,800.000,1.07,856.00,'2026-10-01','INV-202610-018',NULL,'2026-10-01 10:00:00',NULL),(19,6,4,2,155.000,11.20,1736.00,'2026-10-10','INV-202610-019',NULL,'2026-10-10 10:00:00',NULL),(20,5,4,1,915.000,1.30,1189.50,'2026-10-16','INV-202610-020',NULL,'2026-10-16 10:00:00',NULL),(21,6,4,2,97.000,11.09,1075.73,'2026-10-26','INV-202610-021',NULL,'2026-10-26 10:00:00',NULL),(22,10,2,2,88.200,10.34,911.99,'2026-10-27','INV-202610-022',NULL,'2026-10-27 10:00:00',NULL),(23,5,2,3,1995.000,1.36,2713.20,'2026-10-02','INV-202610-023',NULL,'2026-10-02 10:00:00',NULL),(24,7,3,1,58.400,31.04,1812.74,'2026-10-15','INV-202610-024',NULL,'2026-10-15 10:00:00',NULL),(25,7,1,1,51.200,32.00,1638.40,'2026-10-01','INV-202610-025',NULL,'2026-10-01 10:00:00',NULL),(26,1,2,3,28.200,156.60,4416.12,'2026-10-27','INV-202610-026',NULL,'2026-10-27 10:00:00',NULL),(27,10,4,3,60.600,9.56,579.34,'2026-10-19','INV-202610-027',NULL,'2026-10-19 10:00:00',NULL),(28,1,4,3,17.200,152.25,2618.70,'2026-10-19','INV-202610-028',NULL,'2026-10-19 10:00:00',NULL),(29,2,4,3,3.900,1227.20,4786.08,'2026-09-30','INV-202609-029',NULL,'2026-09-30 10:00:00',NULL),(30,9,1,1,616.000,1.05,646.80,'2026-10-16','INV-202610-030',NULL,'2026-10-16 10:00:00',NULL),(31,4,2,4,2020.000,0.82,1656.40,'2026-10-10','INV-202610-031',NULL,'2026-10-10 10:00:00',NULL),(32,7,4,3,22.800,31.36,715.01,'2026-10-27','INV-202610-032',NULL,'2026-10-27 10:00:00',NULL),(33,1,1,3,14.600,153.70,2244.02,'2026-10-07','INV-202610-033',NULL,'2026-10-07 10:00:00',NULL),(34,12,2,2,162.000,7.77,1258.74,'2026-09-28','INV-202609-034',NULL,'2026-09-28 10:00:00',NULL),(35,4,4,2,2900.000,0.89,2581.00,'2026-10-15','INV-202610-035',NULL,'2026-10-15 10:00:00',NULL),(36,8,3,2,300.000,2.28,684.00,'2026-10-27','INV-202610-036',NULL,'2026-10-27 10:00:00',NULL),(37,2,2,1,3.600,1168.20,4205.52,'2026-10-09','INV-202610-037',NULL,'2026-10-09 10:00:00',NULL),(38,9,1,4,1136.000,1.13,1283.68,'2026-10-27','INV-202610-038',NULL,'2026-10-27 10:00:00',NULL),(39,2,2,1,2.900,1227.20,3558.88,'2026-10-04','INV-202610-039',NULL,'2026-10-04 10:00:00',NULL),(40,10,4,3,57.600,10.04,578.30,'2026-10-11','INV-202610-040',NULL,'2026-10-11 10:00:00',NULL);
/*!40000 ALTER TABLE `stock_entries` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Baku Steel Company','+994 12 555 10 10','sales@bakusteel.example','Baku, Sabunchu district','2026-10-27 19:59:55',NULL),(2,'Azersun Distribution','+994 12 555 20 20','orders@azersun.example','Baku, Khatai district','2026-10-27 19:59:55',NULL),(3,'Caspian Chemicals','+994 22 555 30 30','info@caspianchem.example','Sumgait, Industrial zone','2026-10-27 19:59:55',NULL),(4,'Ganja Packaging','+994 22 555 40 40',NULL,'Ganja, Nizami street 12','2026-10-27 19:59:55',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(55) NOT NULL,
  `symbol` varchar(15) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_name_unique` (`name`),
  UNIQUE KEY `units_symbol_unique` (`symbol`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'Ton','t','2026-10-27 19:59:55',NULL),(2,'Kilogram','kg','2026-10-27 19:59:55',NULL),(3,'Gram','g','2026-10-27 19:59:55',NULL),(4,'Piece','pcs','2026-10-27 19:59:55',NULL),(5,'Litre','L','2026-10-27 19:59:55',NULL),(6,'Metre','m','2026-10-27 19:59:55',NULL),(7,'Square metre','m²','2026-10-27 19:59:55',NULL),(8,'Cubic metre','m³','2026-10-27 19:59:55',NULL),(9,'Box','box','2026-10-27 19:59:55',NULL),(10,'Pack','pack','2026-10-27 19:59:55',NULL),(11,'Roll','roll','2026-10-27 19:59:55',NULL),(12,'Pallet','plt','2026-10-27 19:59:55',NULL);
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usertype_id` bigint(20) unsigned NOT NULL,
  `username` varchar(55) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `usertype_id` (`usertype_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`usertype_id`) REFERENCES `usertypes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'admin','Administrator','$2y$10$6oLsPqEVdAYEHFhuDipYIu.afmQB9w9LUgXD5iss9YlgXuZCuK.P6',1,'2026-10-27 19:59:55',NULL),(2,2,'manager','Warehouse Manager','$2y$10$VTP06uRxR3lNPt6QMKq9uOrErcQMdNwPDVtPTOfqe5bUPDUAZqqHC',1,'2026-10-27 19:59:55',NULL),(3,3,'storekeeper','Storekeeper','$2y$10$360vHYvulFkvCUQY1ZaQx.gOqTulSMDg/wX1pADKU2Hz5copTTbVm',1,'2026-10-27 19:59:55',NULL),(4,4,'accountant','Accountant','$2y$10$n1vUPS8gIb91ruBndDQWDOujDurza3PojOPjVVR4C/Uk8mBJJxqQ.',1,'2026-10-27 19:59:55',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `usertypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usertypes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(55) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `can_view_entries` tinyint(1) NOT NULL DEFAULT 0,
  `can_create_entries` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit_entries` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete_entries` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_products` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_suppliers` tinyint(1) NOT NULL DEFAULT 0,
  `can_view_reports` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_users` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_usertypes` tinyint(1) NOT NULL DEFAULT 0,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usertypes_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `usertypes` WRITE;
/*!40000 ALTER TABLE `usertypes` DISABLE KEYS */;
INSERT INTO `usertypes` VALUES (1,'Administrator','Full access to everything, including users and permissions.',1,1,1,1,1,1,1,1,1,1,'2026-10-27 19:59:55',NULL),(2,'Warehouse manager','Runs the warehouse: entries, products, suppliers and reports.',1,1,1,1,1,1,1,0,0,0,'2026-10-27 19:59:55',NULL),(3,'Storekeeper','Receives goods and records stock entries.',1,1,0,0,0,0,0,0,0,0,'2026-10-27 19:59:55',NULL),(4,'Accountant','Read-only access to entries and the stock report.',1,0,0,0,0,0,1,0,0,0,'2026-10-27 19:59:55',NULL);
/*!40000 ALTER TABLE `usertypes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

