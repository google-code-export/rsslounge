-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 19. September 2009 um 19:01
-- Server Version: 5.1.37
-- PHP-Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `rsslounge`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `position` (`position`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `categories`
--

INSERT INTO `categories` (`id`, `name`, `position`) VALUES
(-1, 'unkategorisiert', 0),
(1, 'Blogs', 1),
(2, 'Images', 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `feeds`
--

CREATE TABLE IF NOT EXISTS `feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(200) NOT NULL,
  `url` text,
  `category` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL,
  `favicon` text,
  `filter` text,
  `name` text NOT NULL,
  `position` int(11) NOT NULL,
  `icon` text NOT NULL,
  `multimedia` tinyint(1) NOT NULL,
  `dirtyicon` tinyint(1) NOT NULL DEFAULT '1',
  `htmlurl` text NOT NULL,
  `lastrefresh` int(11) DEFAULT NULL,
  `error` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `priority` (`priority`),
  KEY `position` (`position`),
  KEY `dirtyicon` (`dirtyicon`),
  KEY `lastrefresh` (`lastrefresh`),
  KEY `error` (`error`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Daten für Tabelle `feeds`
--

INSERT INTO `feeds` (`id`, `source`, `url`, `category`, `priority`, `favicon`, `filter`, `name`, `position`, `icon`, `multimedia`, `dirtyicon`, `htmlurl`, `lastrefresh`, `error`) VALUES
(1, 'plugins_rss_feed', 'http://blog.aditu.de/feed', 1, 1, '', '', 'Tobis Blog', 0, '0b832c11c1127f5bcf9011ebe7d98807.ico', 0, 1, 'http://blog.aditu.de/', 1253379409, 0),
(2, 'plugins_images_deviantart', 'SSilence', 2, 3, '', '', 'SSilence', 0, 'a54d65cc8768f644533b8853e3dff821.png', 1, 1, 'http://browse.deviantart.com/?order=5&amp;q=by:SSilence', 1253379423, 0),
(3, 'plugins_images_visualizeus', '', 2, 2, '', '', 'vi.sualize.us', 2, 'd1e9f2ee7385eed7f85bae612ad9f8a7.ico', 1, 1, 'http://vi.sualize.us/popular', 1253379449, 0),
(7, 'plugins_rss_feed', 'http://www.ftd.de/rss2/', -1, 2, '', '', 'FTD', 1, '78ae7a488905806a06facd735c3d73b9.ico', 0, 1, 'http://www.ftd.de/rss2/', 1253379658, 0),
(5, 'plugins_images_deviantartfavs', 'SSilence', 2, 3, '', '', 'SSilence Favorites', 1, 'a54d65cc8768f644533b8853e3dff821.png', 1, 1, 'http://browse.deviantart.com/?order=5&amp;q=favby:SSilence', 1253379569, 0),
(6, 'plugins_rss_feed', 'http://rss.cnn.com/rss/cnn_topstories.rss', -1, 1, '', '', 'CNN', 0, '9b0df61a6b6bde574e83b3b99f2da13c.ico', 0, 1, 'http://www.cnn.com/?eref=rss_topstories', 1253379611, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `feed` int(11) NOT NULL,
  `unread` int(11) NOT NULL,
  `starred` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `uid` varchar(255) NOT NULL,
  `link` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feed` (`feed`),
  KEY `uid` (`uid`),
  KEY `unread` (`unread`),
  KEY `starred` (`starred`),
  KEY `datetime` (`datetime`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=153 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feed` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feed` (`feed`),
  KEY `datetime` (`datetime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `messages`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `name` varchar(200) NOT NULL,
  `value` text NOT NULL,
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `settings`
--

INSERT INTO `settings` (`name`, `value`) VALUES
('language', 'en'),
('priorityStart', '1'),
('priorityEnd', '3'),
('deleteItems', '90'),
('saveOpenCategories', '1'),
('openCategories', '-1,1,2'),
('firstUnread', '1'),
('refresh', '65'),
('lastrefresh', '1253379389'),
('timeout', '0'),
('view', 'both'),
('itemsperpage', '50'),
('imagesPosition', 'top'),
('imagesHeight', '1'),
('selected', ''),
('dateFilter', '0'),
('dateStart', ''),
('dateEnd', ''),
('unread', '1'),
('starred', '0'),
('currentPriorityStart', '1'),
('currentPriorityEnd', '3');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `version`
--

CREATE TABLE IF NOT EXISTS `version` (
  `version` varchar(100) NOT NULL,
  KEY `version` (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `version`
--

INSERT INTO `version` (`version`) VALUES
('1');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
