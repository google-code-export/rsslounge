
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `rsslounge`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `b8wordlist`
--

CREATE TABLE IF NOT EXISTS `b8wordlist` (
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `count` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

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
  `rating` float NOT NULL,
  `rated` enum('up','down') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feed` (`feed`),
  KEY `uid` (`uid`),
  KEY `unread` (`unread`),
  KEY `starred` (`starred`),
  KEY `datetime` (`datetime`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=636 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `name` varchar(200) NOT NULL,
  `value` text NOT NULL,
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `version`
--

CREATE TABLE IF NOT EXISTS `version` (
  `version` varchar(100) NOT NULL,
  KEY `version` (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;










--
-- Daten für Tabelle `b8wordlist`
--

INSERT INTO `b8wordlist` (`token`, `count`) VALUES
('bayes*dbversion', '2'),
('bayes*texts.ham', '1'),
('another', '1 0 100208'),
('bayes*texts.spam', '1'),
('bayes', '0 1 100208'),
('learn', '1 1 100208');


--
-- Daten für Tabelle `version`
--

INSERT INTO `version` (`version`) VALUES
('2');
