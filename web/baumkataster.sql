-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 20. Jun 2020 um 21:49
-- Server-Version: 10.3.22-MariaDB-0+deb10u1-log
-- PHP-Version: 7.3.14-1~deb10u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `baumkataster`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `baumkataster`
--

CREATE TABLE `baumkataster` (
  `FID` varchar(100) NOT NULL,
  `OBJECTID` int(11) NOT NULL,
  `SHAPE` varchar(100) NOT NULL,
  `BAUM_ID` int(11) NOT NULL,
  `DATENFUEHRUNG` varchar(100) NOT NULL,
  `BEZIRK` varchar(100) NOT NULL,
  `OBJEKT_STRASSE` varchar(100) NOT NULL,
  `GEBIETSGRUPPE` varchar(100) NOT NULL,
  `GATTUNG_ART` varchar(100) NOT NULL,
  `PFLANZJAHR` varchar(100) NOT NULL,
  `PFLANZJAHR_TXT` varchar(100) NOT NULL,
  `STAMMUMFANG` varchar(100) NOT NULL,
  `STAMMUMFANG_TXT` varchar(100) NOT NULL,
  `BAUMHOEHE` varchar(100) NOT NULL,
  `BAUMHOEHE_TXT` varchar(100) NOT NULL,
  `KRONENDURCHMESSER` varchar(100) NOT NULL,
  `KRONENDURCHMESSER_TXT` varchar(100) NOT NULL,
  `BAUMNUMMER` varchar(100) NOT NULL,
  `SE_ANNO_CAD_DATA` varchar(100) NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `source` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `baumkataster`
--
ALTER TABLE `baumkataster`
  ADD PRIMARY KEY (`BAUM_ID`),
  ADD KEY `lat` (`lat`),
  ADD KEY `lon` (`lon`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
