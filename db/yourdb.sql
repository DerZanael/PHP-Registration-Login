SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database :  `yourdb`
--
CREATE DATABASE IF NOT EXISTS `yourdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `yourdb`;

-- --------------------------------------------------------

--
-- Table structure `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verified` tinyint(2) NOT NULL DEFAULT '0',
  `token` varchar(70) DEFAULT NULL,
  `registration_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
