SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `sabredav`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur fÃ¼r Tabelle `autoban`
--

CREATE TABLE IF NOT EXISTS `autoban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `username` varchar(128) NOT NULL,
  `ts` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `addressbooks`
--

CREATE TABLE IF NOT EXISTS `addressbooks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `principaluri` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `displayname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uri` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `ctag` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `principaluri` (`principaluri`,`uri`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `calendarobjects`
--

CREATE TABLE IF NOT EXISTS `calendarobjects` (
  `etag` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` int(11) NOT NULL,
  `componenttype` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstoccurence` int(11) DEFAULT NULL,
  `lastoccurence` int(11) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `calendardata` mediumblob,
  `uri` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `calendarid` int(10) unsigned NOT NULL,
  `lastmodified` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `calendarid` (`calendarid`,`uri`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `calendars`
--

CREATE TABLE IF NOT EXISTS `calendars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `principaluri` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `displayname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uri` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ctag` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci,
  `calendarorder` int(10) unsigned NOT NULL DEFAULT '0',
  `calendarcolor` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `timezone` text COLLATE utf8_unicode_ci,
  `components` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transparent` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `principaluri` (`principaluri`,`uri`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cards`
--

CREATE TABLE IF NOT EXISTS `cards` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `addressbookid` int(11) unsigned NOT NULL,
  `carddata` mediumblob,
  `uri` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastmodified` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groupmembers`
--

CREATE TABLE IF NOT EXISTS `groupmembers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `principal_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `principal_id` (`principal_id`,`member_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `locks`
--

CREATE TABLE IF NOT EXISTS `locks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner` varchar(100) DEFAULT NULL,
  `timeout` int(10) unsigned DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `scope` tinyint(4) DEFAULT NULL,
  `depth` tinyint(4) DEFAULT NULL,
  `uri` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `principals`
--

CREATE TABLE IF NOT EXISTS `principals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varchar(200) CHARACTER SET latin1 NOT NULL,
  `email` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `displayname` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `vcardurl` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rcube_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `digesta1` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users_cal_rw`
--

CREATE TABLE IF NOT EXISTS `users_cal_rw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rcube_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `digesta1` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users_cal_r`
--

CREATE TABLE IF NOT EXISTS `users_cal_r` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rcube_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `digesta1` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users_abook_rw`
--

CREATE TABLE IF NOT EXISTS `users_abook_rw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rcube_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `digesta1` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users_abook_r`
--

CREATE TABLE IF NOT EXISTS `users_abook_r` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rcube_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `digesta1` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Constraints der exportierten Tabellen
--

--
--
-- Constraints der Tabelle `calendarobjects`
--
ALTER TABLE `calendarobjects`
  ADD CONSTRAINT `calendarobjects_ibfk_1` FOREIGN KEY (`calendarid`) REFERENCES `calendars` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
