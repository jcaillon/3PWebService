-- phpMyAdmin SQL Dump
-- version 4.4.13.1
-- http://www.phpmyadmin.net
--
-- Client :  noyacfrvludbuser.mysql.db
-- Généré le :  Mer 15 Juin 2016 à 22:20
-- Version du serveur :  5.5.46-0+deb7u1-log
-- Version de PHP :  5.4.45-0+deb7u3

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
-- Structure de la table `3pusers`
--

DROP TABLE IF EXISTS `3pusers`;
CREATE TABLE IF NOT EXISTS `3pusers` (
  `id` bigint(20) NOT NULL,
  `createTime` datetime NOT NULL,
  `lastUpdateTime` datetime NOT NULL,
  `computerId` varchar(50) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `nbPing` int(11) NOT NULL DEFAULT '0',
  `3pVersion` varchar(10) DEFAULT '',
  `NppVersion` varchar(10) DEFAULT '',
  `timeZone` varchar(20) DEFAULT NULL,
  `lang` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `3pusers`
--
ALTER TABLE `3pusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id` (`id`),
  ADD UNIQUE KEY `unique_computerId` (`computerId`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `3pusers`
--
ALTER TABLE `3pusers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
