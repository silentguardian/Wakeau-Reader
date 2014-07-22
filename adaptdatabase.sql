SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `rbook` (
  `id_book` mediumint(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `level` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_book`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `rquestion` (
  `id_question` mediumint(8) NOT NULL AUTO_INCREMENT,
  `id_book` mediumint(8) NOT NULL DEFAULT '0',
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `option_a` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `option_b` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `option_c` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `option_d` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `answer` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_question`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
