-- phpMyAdmin SQL Dump
-- version 4.1.14.8
-- http://www.phpmyadmin.net
--
-- Servidor: db539681916.db.1and1.com
-- Tiempo de generación: 17-02-2015 a las 10:56:02
-- Versión del servidor: 5.1.73-log
-- Versión de PHP: 5.4.36-0+deb7u3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `db539681916`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audience`
--

CREATE TABLE IF NOT EXISTS `audience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_phone` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `audience_email` varchar(30) COLLATE utf8_bin NOT NULL,
  `audience_first_name` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `audience_last_name` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `profile_image` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT 'profiles/you.png',
  `roles` varchar(150) COLLATE utf8_bin DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `password` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`audience_email`,`project_id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_attributes`
--

CREATE TABLE IF NOT EXISTS `inventory_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_id` int(30) NOT NULL,
  `inventory_name` varchar(150) COLLATE utf8_bin NOT NULL,
  `type` varchar(20) COLLATE utf8_bin NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `question_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`audience_id`,`inventory_name`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_items`
--

CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_id` int(20) NOT NULL,
  `inventory_name` varchar(50) COLLATE utf8_bin NOT NULL,
  `item_name` varchar(150) COLLATE utf8_bin NOT NULL,
  `body` text COLLATE utf8_bin NOT NULL,
  `already_read` tinyint(1) NOT NULL DEFAULT '0',
  `question` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(20) COLLATE utf8_bin NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `unlocked` tinyint(1) NOT NULL,
  PRIMARY KEY (`audience_id`,`inventory_name`,`item_name`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` varchar(38) COLLATE utf8_bin NOT NULL,
  `audience_id` int(20) NOT NULL,
  `name` varchar(150) COLLATE utf8_bin NOT NULL,
  `body` text COLLATE utf8_bin NOT NULL,
  `already_read` tinyint(1) NOT NULL DEFAULT '0',
  `question` tinyint(1) NOT NULL DEFAULT '0',
  `type` varchar(20) COLLATE utf8_bin NOT NULL,
  `message_feed_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `order` int(11) NOT NULL AUTO_INCREMENT,
  `character_name` varchar(150) COLLATE utf8_bin NOT NULL,
  `unlocked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `order` (`order`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `projects`
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
  `REGISTRATION_REQUIRED` tinyint(1) NOT NULL DEFAULT '0',
  `DELAY` int(11) NOT NULL DEFAULT '3000',
  `INVENTORY` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`PROJECT_ID`),
  UNIQUE KEY `PROJECT_NAME` (`PROJECT_NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
