-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 13, 2020 at 07:59 PM
-- Server version: 10.3.22-MariaDB-0+deb10u1
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `PhotoShare`
--

-- --------------------------------------------------------

--
-- Table structure for table `hash`
--

CREATE TABLE `hash` (
  `Id` int(11) NOT NULL,
  `FatherId` int(11) NOT NULL,
  `Hash` varchar(50) NOT NULL,
  `Type` int(11) NOT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `Descr` varchar(1024) DEFAULT NULL,
  `Disabled` int(11) NOT NULL,
  `OwnerId` int(11) NOT NULL,
  `CreatedOn` datetime NOT NULL,
  `ModDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hash`
--

INSERT INTO `hash` (`Id`, `FatherId`, `Hash`, `Type`, `Name`, `Descr`, `Disabled`, `OwnerId`, `CreatedOn`, `ModDate`) VALUES
(12, 490, 'c727a3ecf3768e36f94fa930f73bea', 0, '', NULL, 0, 2, '2020-03-01 13:44:37', '2020-03-01 13:44:37'),
(22, 490, '9383568b9b2bb85fbfe28f00947f4d', 0, '', NULL, 0, 2, '2020-03-01 13:46:44', '2020-03-01 13:46:44'),
(88, 510, 'C6DEFC9E', 0, '', NULL, 0, 2, '2020-03-02 17:47:54', '2020-03-02 17:47:54'),
(490, 0, '16F290BF', 1, 'testi 12', '321312312312312', 0, 2, '2020-05-13 17:06:11', '2020-05-13 17:06:11'),
(510, 0, '1BC48D97', 1, 'Testi 2 3 4 5 6', 'dasda sdasddasa Tässä pitäisi olla pitkä teksti s dasdasdasd', 0, 2, '2020-05-13 17:10:01', '2020-05-13 17:10:01'),
(511, 0, '53383C24', 1, 'This is event', 'And this is description', 0, 2, '2020-05-13 17:10:01', '2020-05-13 17:10:01'),
(530, 0, '29D709AF', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(531, 0, '28B418B1', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(532, 0, '13D44198', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(533, 0, 'BA1FFF2B', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(534, 0, '70641466', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(535, 0, '962FE82A', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(536, 0, '25696632', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(537, 0, '8B002E19', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(538, 0, '1A197C1D', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49'),
(539, 0, 'DDB839A8', 0, NULL, NULL, 0, 2, '2020-05-13 19:58:49', '2020-05-13 19:58:49');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `Id` int(11) NOT NULL,
  `HashId` int(11) NOT NULL,
  `NameOnDisk` varchar(80) NOT NULL,
  `Deleted` int(11) NOT NULL,
  `CreatedOn` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`Id`, `HashId`, `NameOnDisk`, `Deleted`, `CreatedOn`) VALUES
(53, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0877.JPG', 0, '2020-03-02 18:30:54'),
(54, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0861.JPG', 0, '2020-03-02 18:30:59'),
(55, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0862.JPG', 0, '2020-03-02 18:31:05'),
(56, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0860.JPG', 0, '2020-03-02 18:31:10'),
(57, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0859.JPG', 0, '2020-03-02 18:31:15'),
(58, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0867.JPG', 0, '2020-03-02 18:31:20'),
(59, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0866.JPG', 0, '2020-03-02 18:31:26'),
(60, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0865.JPG', 0, '2020-03-02 18:31:31'),
(61, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0864.JPG', 0, '2020-03-02 18:31:36'),
(62, 12, 'c727a3ecf3768e36f94fa930f73bea_IMG_0863.JPG', 0, '2020-03-02 18:31:41'),
(63, 22, '9383568b9b2bb85fbfe28f00947f4d_IMG_20200301_162338.jpg', 0, '2020-03-02 18:31:46'),
(64, 22, '9383568b9b2bb85fbfe28f00947f4d_IMG_06666.jpg', 0, '2020-03-02 18:31:51'),
(65, 88, 'C6DEFC9E_IMG_20200302_175509.jpg', 0, '2020-03-02 18:31:55'),
(66, 88, 'C6DEFC9E_IMG_20200302_175519.jpg', 0, '2020-03-02 18:31:59');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `Id` int(11) NOT NULL,
  `PermType` int(11) NOT NULL,
  `Order` int(11) NOT NULL,
  `Descr` varchar(20) NOT NULL,
  `Label` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`Id`, `PermType`, `Order`, `Descr`, `Label`) VALUES
(1, 3, 2, 'add', 'add users'),
(2, 3, 3, 'edit', 'edit users'),
(5, 3, 4, 'delete', 'delete users'),
(9, 4, 6, 'generate', 'generate codes'),
(13, 4, 8, 'clear', 'clear unused codes'),
(14, 4, 7, 'process', 'process images'),
(15, 5, 10, 'create', 'create new event'),
(16, 5, 11, 'edit', 'edit events'),
(17, 5, 12, 'delete', 'delete events'),
(18, 3, 1, 'label', 'user permissions'),
(19, 4, 5, 'label', 'code permissions'),
(22, 5, 9, 'label', 'event permissions'),
(24, 6, 13, 'label', 'misc permissions'),
(25, 6, 14, 'permission', 'misc test permissions');

-- --------------------------------------------------------

--
-- Table structure for table `permission_type`
--

CREATE TABLE `permission_type` (
  `Id` int(11) NOT NULL,
  `Type` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `permission_type`
--

INSERT INTO `permission_type` (`Id`, `Type`) VALUES
(3, 'user'),
(4, 'code'),
(5, 'event'),
(6, 'misc');

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `TokenType` int(11) NOT NULL,
  `Token` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tokens`
--

INSERT INTO `tokens` (`Id`, `UserId`, `TokenType`, `Token`) VALUES
(68, 2, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1ODkzODY0OTYsImlzcyI6InBhbGlra2FsYWF0aWtrb1wvMTkyLjE2OC4xLjQiLCJleHAiOjE1ODkzOTAwOTYsInVzZXJJZCI6IjIifQ.hQOqJoBxOGY9ZM_RLnSTPbIFB9rIxdrkBa-JVJkbysY'),
(69, 2, 2, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1ODkzODY0OTYsImlzcyI6InBhbGlra2FsYWF0aWtrb1wvMTkyLjE2OC4xLjQiLCJleHAiOjE1ODk0NjUxNDUsInVzZXJJZCI6IjIifQ.4sPQ9nk0x9yweAM1QwH7SWzhNHBQoBwevKdV_6TfMfU');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `Email` varchar(140) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `FirstName` varchar(140) NOT NULL,
  `LastName` varchar(140) NOT NULL,
  `Disabled` int(11) NOT NULL,
  `LastLogin` datetime NOT NULL,
  `CreatedOn` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `Email`, `Password`, `FirstName`, `LastName`, `Disabled`, `LastLogin`, `CreatedOn`) VALUES
(1, 'ville.kouhia@gmail.com', 'ville123', 'Ville', 'Kouhia', 0, '2020-02-15 15:41:31', '2020-02-15 15:41:31'),
(2, 'testi@domaini.fi', '$argon2id$v=19$m=65536,t=4,p=1$QXBOVldiaUZBZGhvdUo4bQ$lWLYgyo96JPMipClSITvry9bsAQVsG1qoI4a/GwLguw', 'Moi', 'TestatAN', 0, '2020-02-15 15:41:31', '2020-02-15 15:41:31'),
(11, 'testi@aa.si', '$argon2id$v=19$m=65536,t=4,p=1$dHlkbDl0M2lFYU9OUmZtUQ$PTJ4u3wI/XjPzkA8iGagmCqr0D4le89fWi4wYsgueyE', 'Testiaasi', 'AasiTestaa', 0, '2020-04-14 18:01:29', '2020-04-14 18:01:29'),
(12, 'ville.kouhi2a@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$NTBRVVY3RUJqZGNQaTJGSA$ubQH/7cuPWcEBQ3JoU8cVEMt7rJa8xzQnP5V72CwSb0', 'Ville2', 'Kouhia2', 1, '2020-04-14 18:59:13', '2020-04-14 18:59:13');

-- --------------------------------------------------------

--
-- Table structure for table `user_perm`
--

CREATE TABLE `user_perm` (
  `Id` int(11) NOT NULL,
  `PermId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Authorized` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_perm`
--

INSERT INTO `user_perm` (`Id`, `PermId`, `UserId`, `Authorized`) VALUES
(2, 2, 2, 1),
(3, 1, 2, 1),
(5, 5, 2, 1),
(6, 9, 2, 1),
(7, 11, 2, 0),
(8, 12, 2, 0),
(9, 13, 2, 1),
(10, 14, 2, 0),
(11, 15, 2, 1),
(12, 16, 2, 1),
(13, 17, 2, 0),
(16, 18, 2, 0),
(17, 19, 2, 1),
(18, 22, 2, 0),
(20, 1, 1, 1),
(21, 2, 1, 1),
(22, 5, 1, 1),
(23, 9, 1, 0),
(24, 13, 1, 0),
(25, 14, 1, 0),
(26, 15, 1, 1),
(27, 16, 1, 1),
(28, 17, 1, 1),
(29, 18, 1, 0),
(30, 19, 1, 0),
(31, 22, 1, 0),
(32, 1, 11, 1),
(33, 2, 11, 1),
(34, 5, 11, 1),
(35, 9, 11, 1),
(36, 13, 11, 1),
(37, 14, 11, 1),
(38, 15, 11, 1),
(39, 16, 11, 1),
(40, 17, 11, 1),
(41, 18, 11, 0),
(42, 19, 11, 0),
(43, 22, 11, 0),
(44, 24, 11, 0),
(45, 25, 11, 1),
(46, 1, 12, 0),
(47, 2, 12, 0),
(48, 5, 12, 0),
(49, 9, 12, 0),
(50, 13, 12, 0),
(51, 14, 12, 1),
(52, 15, 12, 0),
(53, 16, 12, 0),
(54, 17, 12, 0),
(55, 18, 12, 0),
(56, 19, 12, 0),
(57, 22, 12, 0),
(58, 24, 12, 0),
(59, 25, 12, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hash`
--
ALTER TABLE `hash`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `permission_type`
--
ALTER TABLE `permission_type`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id` (`Id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Id` (`Id`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `user_perm`
--
ALTER TABLE `user_perm`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hash`
--
ALTER TABLE `hash`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=540;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `permission_type`
--
ALTER TABLE `permission_type`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tokens`
--
ALTER TABLE `tokens`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_perm`
--
ALTER TABLE `user_perm`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
