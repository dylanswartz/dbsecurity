-- phpMyAdmin SQL Dump
-- version 3.4.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 09, 2011 at 11:09 AM
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
-- Table structure for table `config_data`
--

CREATE TABLE IF NOT EXISTS `config_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `databaseId` int(11) NOT NULL,
  `username` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `path` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `config_data`
--

INSERT INTO `config_data` (`id`, `databaseId`, `username`, `password`, `path`, `lastUpdated`) VALUES
(1, 1, 'db2', '6pvf9gehf0', '/home/dylan/public_html/db2_config.php', '2011-08-09 14:34:20'),
(2, 2, 'db3', 'h7eo2t09zm', '/home/dylan/public_html/db3_config.php', '2011-08-09 14:37:52'),
(3, 3, 'db5', 'b_5|vrybxi', '/home/dylan/public_html/db5_config.php', '2011-08-09 14:56:52'),
(4, 4, 'db4', '4k_i5gex#|', '/home/dylan/public_html/db4_config.php', '2011-08-09 15:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `database_list`
--

CREATE TABLE IF NOT EXISTS `database_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `creator` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `timeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ownerId` (`creator`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `database_list`
--

INSERT INTO `database_list` (`id`, `name`, `creator`, `timeCreated`) VALUES
(1, 'db2', '', '2011-08-09 14:34:20'),
(2, 'db3', 'dylan', '2011-08-09 14:37:52'),
(3, 'db5', 'dylan', '2011-08-09 14:56:52'),
(4, 'db4', 'dylan', '2011-08-09 15:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `databaseName` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job` enum('create','drop','edit') COLLATE utf8_unicode_ci NOT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','complete','failed') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `user`, `databaseName`, `job`, `time`, `status`) VALUES
(1, '', 'db1', 'create', '2011-08-09 14:33:06', 'failed'),
(2, '', 'db2', 'create', '2011-08-09 14:34:18', 'complete'),
(3, 'dylan', 'db3', 'create', '2011-08-09 14:37:45', 'complete'),
(4, 'dylan', 'db5', 'create', '2011-08-09 14:56:49', 'complete'),
(5, 'dylan', 'db4', 'create', '2011-08-09 15:05:06', 'complete');

-- --------------------------------------------------------

--
-- Table structure for table `jobs_completed`
--

CREATE TABLE IF NOT EXISTS `jobs_completed` (
  `id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `jobs_completed`
--

INSERT INTO `jobs_completed` (`id`, `time`) VALUES
(2, '2011-08-09 14:34:20'),
(3, '2011-08-09 14:37:52'),
(4, '2011-08-09 14:56:52'),
(5, '2011-08-09 15:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `jobs_failed`
--

CREATE TABLE IF NOT EXISTS `jobs_failed` (
  `id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `jobs_failed`
--

INSERT INTO `jobs_failed` (`id`, `time`) VALUES
(1, '2011-08-09 14:34:12');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
