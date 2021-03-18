-- --------------------------------------------------------
-- Host:                         mysql.cc.puv.fi
-- Server version:               5.5.62-0+deb8u1 - (Debian)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table e1601124_media_chest.media
CREATE TABLE IF NOT EXISTS `media` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `has_file` tinyint(4) NOT NULL,
  `text_message` longtext NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '0',
  `longitude` double DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `ip_address` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `point-to-user` (`username`),
  KEY `longitude` (`longitude`),
  KEY `latitude` (`latitude`),
  CONSTRAINT `point-to-user` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table e1601124_media_chest.user
CREATE TABLE IF NOT EXISTS `user` (
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
