-- phpMyAdmin SQL Dump
-- version 4.2.13.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 17, 2014 at 04:01 PM
-- Server version: 5.5.40
-- PHP Version: 5.6.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `communicator`
--

-- --------------------------------------------------------

--
-- Table structure for table `audience`
--

CREATE TABLE IF NOT EXISTS `audience` (
`id` int(11) NOT NULL,
  `audience_phone` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `audience_email` varchar(30) COLLATE utf8_bin NOT NULL,
  `audience_first_name` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `audience_last_name` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `profile_image` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT 'profiles/you.png',
  `roles` varchar(150) COLLATE utf8_bin DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `password` varchar(50) COLLATE utf8_bin DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_attributes`
--

CREATE TABLE IF NOT EXISTS `inventory_attributes` (
`id` int(11) NOT NULL,
  `audience_id` int(30) NOT NULL,
  `inventory_name` varchar(150) COLLATE utf8_bin NOT NULL,
  `type` varchar(20) COLLATE utf8_bin NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `question_count` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=238 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE IF NOT EXISTS `inventory_items` (
`id` int(11) NOT NULL,
  `audience_id` int(20) NOT NULL,
  `inventory_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `item_name` varchar(150) COLLATE utf8_bin NOT NULL,
  `body` text COLLATE utf8_bin NOT NULL,
  `already_read` tinyint(1) NOT NULL DEFAULT '0',
  `question` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(20) COLLATE utf8_bin NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `unlocked` tinyint(1) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=512 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `PROJECT_ID` int(11) NOT NULL,
  `CONSUMER_KEY` varchar(50) COLLATE utf8_bin NOT NULL,
  `CONSUMER_SECRET` varchar(50) COLLATE utf8_bin NOT NULL,
  `ACCESS_TOKEN` varchar(50) COLLATE utf8_bin NOT NULL,
  `ACCESS_TOKEN_SECRET` varchar(50) COLLATE utf8_bin NOT NULL,
  `BADGES_GROUP_ID` int(11) NOT NULL,
  `ROLES_GROUP_ID` int(11) NOT NULL,
  `PROJECT_NAME` varchar(20) COLLATE utf8_bin NOT NULL,
  `REGISTRATION_REQUIRED` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audience`
--
ALTER TABLE `audience`
 ADD PRIMARY KEY (`audience_email`,`project_id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `inventory_attributes`
--
ALTER TABLE `inventory_attributes`
 ADD PRIMARY KEY (`audience_id`,`inventory_name`), ADD KEY `id` (`id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
 ADD PRIMARY KEY (`audience_id`,`inventory_name`,`item_name`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
 ADD PRIMARY KEY (`PROJECT_ID`), ADD UNIQUE KEY `PROJECT_NAME` (`PROJECT_NAME`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audience`
--
ALTER TABLE `audience`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=55;
--
-- AUTO_INCREMENT for table `inventory_attributes`
--
ALTER TABLE `inventory_attributes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=238;
--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=512;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

