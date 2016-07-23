-- phpMyAdmin SQL Dump
-- version 4.4.15.7
-- http://www.phpmyadmin.net
--
-- Client :  noyacfrvludbuser.mysql.db
-- Généré le :  Sam 23 Juillet 2016 à 16:53
-- Version du serveur :  5.5.46-0+deb7u1-log
-- Version de PHP :  5.4.45-0+deb7u4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `noyacfrvludbuser`
--

-- --------------------------------------------------------

--
-- Structure de la table `MySoft_bugs`
--

CREATE TABLE IF NOT EXISTS `MySoft_bugs` (
  `softName` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `originVersion` varchar(21) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `originClass` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `originLine` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `nbReceived` int(11) NOT NULL,
  `UUID` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `message` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `MySoft_ping`
--

CREATE TABLE IF NOT EXISTS `MySoft_ping` (
  `softName` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `UUID` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `userName` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `firstPing` datetime NOT NULL,
  `lastPing` datetime NOT NULL,
  `lang` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `version` varchar(21) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `nbPing` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `MySoft_bugs`
--
ALTER TABLE `MySoft_bugs`
  ADD PRIMARY KEY (`originVersion`,`originClass`,`originLine`,`softName`) USING BTREE;

--
-- Index pour la table `MySoft_ping`
--
ALTER TABLE `MySoft_ping`
  ADD PRIMARY KEY (`softName`,`UUID`) USING BTREE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
