CREATE DATABASE IF NOT EXISTS TEST;
USE TEST;

DROP TABLE IF EXISTS `customer`;
CREATE TABLE IF NOT EXISTS `customer` (
  `id` int(11) NOT NULL,
  `cust_first_name` varchar(255) NOT NULL,
  `cust_last_name` varchar(255) NOT NULL,
  `cust_email` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
)

INSERT INTO `customer` VALUES (1, 'John', 'Doe', 'john@doe.com');
INSERT INTO `customer` VALUES (2, 'Jean', 'Doe', 'jean@doe.com');

DROP TABLE IF EXISTS `order`;
CREATE TABLE IF NOT EXISTS `order` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `item` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
)