-- phpMyAdmin SQL Dump
-- version 4.4.15.7
-- http://www.phpmyadmin.net
--
-- Client :  noyacfrvludbuser.mysql.db
-- Généré le :  Dim 24 Juillet 2016 à 19:52
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
CREATE DATABASE IF NOT EXISTS `noyacfrvludbuser` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `noyacfrvludbuser`;

-- --------------------------------------------------------

--
-- Structure de la table `MySoft_bugs`
--

DROP TABLE IF EXISTS `MySoft_bugs`;
CREATE TABLE IF NOT EXISTS `MySoft_bugs` (
  `softName` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `originVersion` varchar(21) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `originMethod` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `originLine` int(11) NOT NULL,
  `receptionTime` datetime NOT NULL,
  `nbReceived` int(11) NOT NULL DEFAULT '0',
  `UUID` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `message` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fullException` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `MySoft_ping`
--

DROP TABLE IF EXISTS `MySoft_ping`;
CREATE TABLE IF NOT EXISTS `MySoft_ping` (
  `softName` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `UUID` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `userName` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `firstPing` datetime NOT NULL,
  `lastPing` datetime NOT NULL,
  `location` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `version` varchar(21) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `nbPing` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `MySoft_ping`
--

INSERT INTO `MySoft_ping` (`softName`, `UUID`, `userName`, `firstPing`, `lastPing`, `location`, `version`, `nbPing`) VALUES
  ('3p', 'C62A95AD-D80C-40DB-A548-A07C7FCCEEC7', 'Julien (JULIEN-PCFIXE)', '2016-07-24 18:59:10', '2016-07-24 18:59:10', 'FR, RhÃ´ne, Villeurbanne', 'v1.6.3', 1);

--
-- Index pour les tables exportées
--

--
-- Index pour la table `MySoft_bugs`
--
ALTER TABLE `MySoft_bugs`
ADD PRIMARY KEY (`originVersion`,`originMethod`,`originLine`,`softName`) USING BTREE,
ADD KEY `time` (`receptionTime`);

--
-- Index pour la table `MySoft_ping`
--
ALTER TABLE `MySoft_ping`
ADD PRIMARY KEY (`softName`,`UUID`) USING BTREE,
ADD KEY `time` (`lastPing`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
