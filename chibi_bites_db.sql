CREATE DATABASE  IF NOT EXISTS `chibi_bites` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `chibi_bites`;
-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: chibi_bites
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,3,'Choco Berry',3,35.00),(2,3,'Berry Cream',3,35.00),(3,4,'Choco Berry',3,35.00),(4,5,'Choco Berry',3,35.00),(5,5,'Berry Cream',3,35.00),(6,6,'Matcha Zen',3,35.00),(7,7,'Choco Lava',3,35.00),(8,8,'Choco Lava',3,35.00),(9,9,'Biscoff Bliss',1,35.00),(10,10,'Matcha Zen',3,35.00),(11,10,'Mochi Bites',1,80.00),(12,10,'Oreo Cream',3,35.00),(13,11,'Berry Cream',6,35.00),(14,12,'Choco Berry',3,35.00),(15,18,'Choco Berry',3,35.00),(16,18,'Sunny Mango',3,35.00),(17,19,'Choco Berry',3,35.00),(18,20,'Berry Cream',3,35.00),(19,21,'Choco Berry',3,35.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cod',
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,450.00,'506 Jamaica St., Vermont Park, Quezon City','cod','cancelled','2026-01-19 13:27:28'),(2,1,450.00,'506 Jamaica St., Vermont Park, Quezon City','cod','delivered','2026-01-19 13:27:54'),(3,1,0.00,', ','GCash','out_for_delivery','2026-01-22 05:38:25'),(4,1,0.00,', ','GCash','confirmed','2026-01-22 05:39:35'),(5,1,250.00,'506 Jamaica St., Vermont Park, Quezon City','cod','processing','2026-01-22 06:45:30'),(6,1,150.00,'506 Jamaica St., Vermont Park, Quezon City','cod','ready','2026-01-22 06:47:03'),(7,1,150.00,'506 Jamaica St., Vermont Park, Quezon City','cod','pending','2026-01-22 07:03:59'),(8,1,150.00,'506 Jamaica St., Vermont Park, Quezon City','cod','pending','2026-01-22 07:23:33'),(9,1,85.00,'506 Jamaica St., Vermont Park, Quezon City','cod','pending','2026-01-22 08:04:56'),(10,1,330.00,'506 Jamaica St., Vermont Park, Quezon City','cod','pending','2026-01-22 08:19:45'),(11,1,250.00,'506 Jamaica St., Vermont Park, Quezon City','cod','pending','2026-01-22 08:40:18'),(12,1,150.00,'506 Jamaica St., Vermont Park Village, Quezon City','cod','pending','2026-01-22 08:45:34'),(18,1,250.00,'506 Jamaica St., Vermont Park Village, Antipolo','cod','pending','2026-01-22 09:20:19'),(19,1,150.00,'506 Jamaica St., Vermont Park Village, Antipolo','gcash','pending','2026-01-22 09:23:13'),(20,1,150.00,'506 Jamaica St., Vermont Park Village, Antipolo','gcash','pending','2026-01-22 09:23:28'),(21,1,150.00,'506 Jamaica St., Vermont Park Village, Antipolo','gcash','pending','2026-01-22 09:59:56');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text,
  `image_path` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'mochi',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Berry Cream',35.00,'Delicious matcha-flavored mochi with a smooth, chewy texture. Made with premium green tea powder for an authentic taste.','images/berry_cream.jpg','mochi',1,'2026-01-18 06:53:00'),(2,'Biscoff Bliss',35.00,'Sweet strawberry mochi with a delightful fruity flavor. Soft and chewy with real strawberry filling.','images/biscoff_bliss.jpg','mochi',1,'2026-01-18 06:53:00'),(3,'Choco Berry',35.00,'Classic vanilla mochi with a creamy, sweet center. A timeless favorite for all mochi lovers.','images/choco_berry.jpg','mochi',1,'2026-01-18 06:53:00'),(4,'Choco Lava',35.00,'Traditional red bean mochi with authentic sweet bean paste. A classic Japanese treat.','images/choco_lava.jpg','mochi',1,'2026-01-18 06:53:00'),(5,'Matcha Zen',35.00,'Delicious matcha-flavored mochi with a smooth, chewy texture. Made with premium green tea powder for an authentic taste.','images/matcha_zen.jpg','mochi',1,'2026-01-18 06:53:00'),(6,'Oreo Cream',35.00,'Sweet strawberry mochi with a delightful fruity flavor. Soft and chewy with real strawberry filling.','images/oreo_cream.jpg','mochi',1,'2026-01-18 06:53:00'),(7,'Sunny Mango',35.00,'Classic vanilla mochi with a creamy, sweet center. A timeless favorite for all mochi lovers.','images/sunny_mango.jpg','mochi',1,'2026-01-18 06:53:00'),(8,'Mochi Bites',80.00,'Classic vanilla mochi with a creamy, sweet center. A timeless favorite for all mochi lovers.','images/mochi_bites.jpg','mochi',1,'2026-01-18 06:53:00');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `zipcode` varchar(4) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Chester Alcanzarin','chestersaan25@gmail.com','09705730898','506 Jamaica St., Vermont Park','Antipolo','1870','$2y$10$IfZjQqCp1rhXPjGGBxMVOeXRwzbIBm5wXIqNricE6fGHnonwlafFO','2026-01-17 22:40:09','2026-01-22 10:00:11'),(2,'sample','sample@email.com','09705730898','123 street','Quezon City','1234','$2y$10$PaBnOQf0bRNZPiDEqk/PVO44/O1SFFBVJHIGePeMmH55mHtwFq63O','2026-01-18 04:50:44','2026-01-18 04:50:44');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'chibi_bites'
--
/*!50003 DROP PROCEDURE IF EXISTS `AddOrderItem` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddOrderItem`(
    IN p_order_id INT,
    IN p_product_name VARCHAR(255),
    IN p_quantity INT,
    IN p_price DECIMAL(10,2)
)
BEGIN
    INSERT INTO order_items (
        order_id, 
        product_name, 
        quantity, 
        price
    ) 
    VALUES (
        p_order_id, 
        p_product_name, 
        p_quantity, 
        p_price
    );
    
    SELECT ROW_COUNT() as affected_rows;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CreateOrder` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateOrder`(
    IN p_user_id INT,
    IN p_total_amount DECIMAL(10,2),
    IN p_delivery_address TEXT,
    IN p_payment_method VARCHAR(50),
    OUT p_order_id INT
)
BEGIN
    INSERT INTO orders (
        user_id, 
        total_amount, 
        delivery_address, 
        payment_method,
        status, 
        created_at
    ) 
    VALUES (
        p_user_id, 
        p_total_amount, 
        p_delivery_address, 
        p_payment_method,
        'pending', 
        NOW()
    );
    
    SET p_order_id = LAST_INSERT_ID();
    
    SELECT p_order_id as order_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GetUserDashboardData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserDashboardData`(IN p_user_id INT)
BEGIN
    -- Result Set 1: User Info
    SELECT fullname, email, phone, street, city 
    FROM users 
    WHERE id = p_user_id;
    -- Result Set 2: Statistics
    SELECT 
        COUNT(id) as order_count, 
        IFNULL(SUM(total_amount), 0) as total_spent 
    FROM orders 
    WHERE user_id = p_user_id;
    -- Result Set 3: Order History (Increased to 50)
    SELECT id, total_amount, status, created_at 
    FROM orders 
    WHERE user_id = p_user_id 
    ORDER BY created_at DESC 
    LIMIT 50;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `UpdateOrderStatus` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateOrderStatus`(
    IN p_order_id INT,
    IN p_status VARCHAR(50),
    OUT p_message VARCHAR(100)
)
BEGIN
    DECLARE order_exists INT DEFAULT 0;
    DECLARE old_status VARCHAR(50);
    DECLARE affected_rows INT DEFAULT 0;
    
    -- Check if order exists and get current status
    SELECT COUNT(*), status INTO order_exists, old_status 
    FROM orders WHERE id = p_order_id;
    
    IF order_exists = 0 THEN
        SET p_message = 'Order not found';
        SELECT 0 as success, p_message as message, 0 as affected_rows;
    ELSEIF old_status = 'cancelled' OR old_status = 'delivered' THEN
        SET p_message = CONCAT('Cannot change status from ', old_status);
        SELECT 0 as success, p_message as message, 0 as affected_rows;
    ELSE
        -- Update order status
        UPDATE orders 
        SET 
            status = p_status,
            updated_at = NOW()
        WHERE id = p_order_id;
        
        SET affected_rows = ROW_COUNT();
        
        -- Add to order history log (if you have a log table)
        -- INSERT INTO order_status_history (order_id, old_status, new_status, changed_at) 
        -- VALUES (p_order_id, old_status, p_status, NOW());
        
        SET p_message = CONCAT('Order status updated from ', old_status, ' to ', p_status);
        SELECT 1 as success, p_message as message, affected_rows as affected_rows;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `UpdateUserProfile` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserProfile`(
    IN p_user_id INT,
    IN p_fullname VARCHAR(255),
    IN p_phone VARCHAR(20),
    IN p_street VARCHAR(255),
    IN p_city VARCHAR(100),
    IN p_zipcode VARCHAR(4)
)
BEGIN
    UPDATE users 
    SET 
        fullname = p_fullname,
        phone = p_phone,
        street = p_street,
        city = p_city,
        zipcode = p_zipcode,
        updated_at = NOW()
    WHERE id = p_user_id;
    
    SELECT ROW_COUNT() as affected_rows;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-22 19:07:36
