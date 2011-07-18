-- phpMyAdmin SQL Dump
-- version 3.4.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 18, 2011 at 03:56 PM
-- Server version: 5.1.53
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `databaseName` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job` enum('create','drop','edit') COLLATE utf8_unicode_ci NOT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','complete','failed') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=18 ;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `userId`, `databaseName`, `job`, `time`, `status`) VALUES
(17, 1, 'timestamps', 'create', '2011-07-14 15:51:53', 'pending'),
(16, 1, 'DeathStar', 'create', '2011-07-14 15:49:05', 'pending'),
(15, 1, 'Database1', 'create', '2011-07-14 15:48:17', 'pending'),
(14, 1, 'cowDatabase', 'create', '2011-07-14 15:47:17', 'pending');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
