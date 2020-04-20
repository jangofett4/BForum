-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 19 Nis 2020, 20:34:06
-- Sunucu sürümü: 10.4.8-MariaDB
-- PHP Sürümü: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `bforum`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tbladmins`
--

CREATE TABLE `tbladmins` (
  `ID` int(10) UNSIGNED NOT NULL,
  `AUserID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tblcomments`
--

CREATE TABLE `tblcomments` (
  `ID` int(10) UNSIGNED NOT NULL,
  `CContent` varchar(2048) NOT NULL,
  `COp` int(10) UNSIGNED NOT NULL,
  `CPost` int(10) UNSIGNED NOT NULL,
  `CTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tblpostflags`
--

CREATE TABLE `tblpostflags` (
  `ID` int(10) UNSIGNED NOT NULL,
  `FPost` int(10) UNSIGNED NOT NULL,
  `FPinned` tinyint(1) NOT NULL,
  `FOrder` int(10) UNSIGNED NOT NULL,
  `FLocked` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tblposts`
--

CREATE TABLE `tblposts` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PTitle` varchar(64) NOT NULL,
  `PContent` varchar(4096) NOT NULL,
  `POp` int(10) UNSIGNED NOT NULL,
  `PTopic` int(10) UNSIGNED NOT NULL,
  `PTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `PUpdated` timestamp NOT NULL DEFAULT current_timestamp(),
  `PEdits` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tbltopicperms`
--

CREATE TABLE `tbltopicperms` (
  `ID` int(11) UNSIGNED NOT NULL,
  `PUser` int(11) UNSIGNED NOT NULL,
  `PTopic` int(11) UNSIGNED NOT NULL,
  `PPerms` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tbltopics`
--

CREATE TABLE `tbltopics` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TName` varchar(32) NOT NULL,
  `TDesc` varchar(128) NOT NULL,
  `TCreator` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tblusers`
--

CREATE TABLE `tblusers` (
  `ID` int(10) UNSIGNED NOT NULL,
  `UPassword` varchar(32) NOT NULL,
  `UName` varchar(32) NOT NULL,
  `USurname` varchar(32) NOT NULL,
  `UEmail` varchar(48) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `tbladmins`
--
ALTER TABLE `tbladmins`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `AUserID` (`AUserID`);

--
-- Tablo için indeksler `tblcomments`
--
ALTER TABLE `tblcomments`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `tblcomments_ibfk_1` (`COp`),
  ADD KEY `tblcomments_ibfk_2` (`CPost`);

--
-- Tablo için indeksler `tblpostflags`
--
ALTER TABLE `tblpostflags`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `tblpostflags_ibfk_1` (`FPost`);

--
-- Tablo için indeksler `tblposts`
--
ALTER TABLE `tblposts`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `POp` (`POp`),
  ADD KEY `PTopic` (`PTopic`);

--
-- Tablo için indeksler `tbltopicperms`
--
ALTER TABLE `tbltopicperms`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `tbltopicperms_ibfk_1` (`PTopic`),
  ADD KEY `tbltopicperms_ibfk_2` (`PUser`);

--
-- Tablo için indeksler `tbltopics`
--
ALTER TABLE `tbltopics`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TCreator` (`TCreator`);

--
-- Tablo için indeksler `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`UEmail`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `tbladmins`
--
ALTER TABLE `tbladmins`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tblcomments`
--
ALTER TABLE `tblcomments`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tblpostflags`
--
ALTER TABLE `tblpostflags`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tblposts`
--
ALTER TABLE `tblposts`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tbltopicperms`
--
ALTER TABLE `tbltopicperms`
  MODIFY `ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tbltopics`
--
ALTER TABLE `tbltopics`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `tbladmins`
--
ALTER TABLE `tbladmins`
  ADD CONSTRAINT `tbladmins_ibfk_1` FOREIGN KEY (`AUserID`) REFERENCES `tblusers` (`ID`);

--
-- Tablo kısıtlamaları `tblcomments`
--
ALTER TABLE `tblcomments`
  ADD CONSTRAINT `tblcomments_ibfk_1` FOREIGN KEY (`COp`) REFERENCES `tblusers` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblcomments_ibfk_2` FOREIGN KEY (`CPost`) REFERENCES `tblposts` (`ID`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `tblpostflags`
--
ALTER TABLE `tblpostflags`
  ADD CONSTRAINT `tblpostflags_ibfk_1` FOREIGN KEY (`FPost`) REFERENCES `tblposts` (`ID`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `tblposts`
--
ALTER TABLE `tblposts`
  ADD CONSTRAINT `tblposts_ibfk_1` FOREIGN KEY (`POp`) REFERENCES `tblusers` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblposts_ibfk_2` FOREIGN KEY (`PTopic`) REFERENCES `tbltopics` (`ID`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `tbltopicperms`
--
ALTER TABLE `tbltopicperms`
  ADD CONSTRAINT `tbltopicperms_ibfk_1` FOREIGN KEY (`PTopic`) REFERENCES `tbltopics` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbltopicperms_ibfk_2` FOREIGN KEY (`PUser`) REFERENCES `tblusers` (`ID`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `tbltopics`
--
ALTER TABLE `tbltopics`
  ADD CONSTRAINT `tbltopics_ibfk_1` FOREIGN KEY (`TCreator`) REFERENCES `tblusers` (`ID`) ON DELETE CASCADE;
COMMIT;

-- Add users
INSERT INTO `tblusers` 
	(`ID`, `UEmail`, `UName`, `USurname`, `UPassword`) VALUES
	(0, 'system@admin.com', 'Admin', 'Admin', 'sysadmin');
INSERT INTO `tbladmins` (`AUserID`) VALUES (0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
