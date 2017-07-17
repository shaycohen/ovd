-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: May 10, 2017 at 07:41 AM
-- Server version: 5.7.18
-- PHP Version: 7.0.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ovd`
--

-- --------------------------------------------------------

--
-- Table structure for table `container`
--

CREATE TABLE `container` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `container` (`id`, `description`, `warehouse_id`) VALUES
(1, 'cont1', 1);
INSERT INTO `container` (`id`, `description`, `warehouse_id`) VALUES
(2, 'cont2', 2);
INSERT INTO `container` (`id`, `description`, `warehouse_id`) VALUES
(3, 'cont3', 1);
INSERT INTO `container` (`id`, `description`, `warehouse_id`) VALUES
(4, 'cont4', 2);
-- --------------------------------------------------------

--
-- Table structure for table `manifest`
--

CREATE TABLE `manifest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `container_id` int(11) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `manifest` (`id`, `container_id`, `description`) VALUES
(1, 1, 'manifest1_cont1');
INSERT INTO `manifest` (`id`, `container_id`, `description`) VALUES
(2, 1, 'manifest2_cont1');
INSERT INTO `manifest` (`id`, `container_id`, `description`) VALUES
(3, 2, 'manifest3_cont2');
INSERT INTO `manifest` (`id`, `container_id`, `description`) VALUES
(4, 3, 'manifest4_cont3');
INSERT INTO `manifest` (`id`, `container_id`, `description`) VALUES
(5, 4, 'manifest5_cont4');

-- --------------------------------------------------------

--
-- Table structure for table `damage`
--

CREATE TABLE `damage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manifest_id` int(11),
  `container_id` int(11),
  `type` int(11) NOT NULL,
  `enabled` int(1), 
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `damage` (`id`, `manifest_id`, `type`,  `enabled`, `description`) VALUES
(1, 1, 1, 1, 'damage1');
INSERT INTO `damage` (`id`, `manifest_id`, `type`,  `enabled`, `description`) VALUES
(2, 1, 2, 1, 'damage2');
INSERT INTO `damage` (`id`, `manifest_id`, `type`,  `enabled`, `description`) VALUES
(3, 1, 3, 1, 'damage3');
INSERT INTO `damage` (`id`, `manifest_id`, `type`,  `enabled`, `description`) VALUES
(4, 1, 3, 1, 'damage4');
INSERT INTO `damage` (`id`, `manifest_id`, `type`,  `enabled`, `description`) VALUES
(5, 2, 1, 1, 'damage5');
INSERT INTO `damage` (`id`, `manifest_id`, `type`,  `enabled`, `description`) VALUES
(6, 2, 2, 1, 'damage6');
INSERT INTO `damage` (`id`, `container_id`, `type`,  `enabled`, `description`) VALUES
(7, 1, 1, 1, 'damage_container_1');
INSERT INTO `damage` (`id`, `container_id`, `type`,  `enabled`, `description`) VALUES
(8, 2, 1, 1, 'damage_container_2');
INSERT INTO `damage` (`id`, `container_id`, `type`,  `enabled`, `description`) VALUES
(9, 3, 1, 1, 'damage_container_3');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lname` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `pw` varchar(2048) NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `user` (`id`, `fname`, `lname`, `username`, `warehouse_id`, `pw`) VALUES
(1, 'user', 'one', 'user1', 1, 'pass1');
INSERT INTO `user` (`id`, `fname`, `lname`, `username`, `warehouse_id`, `pw`) VALUES
(2, 'user', 'two', 'user2', 2, 'pass2');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse`
--

CREATE TABLE `warehouse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `warehouse`
--

INSERT INTO `warehouse` (`id`, `description`) VALUES
(1, 'warehouse1');
INSERT INTO `warehouse` (`id`, `description`) VALUES
(2, 'warehouse2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `container`
--
ALTER TABLE `container`
  ADD UNIQUE KEY `container_uniq` (`id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `manifest`
--
ALTER TABLE `manifest`
  ADD UNIQUE KEY `manifest_uniq` (`id`),
  ADD KEY `manifest_container_id` (`container_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD UNIQUE KEY `user_uniq` (`id`),
  ADD UNIQUE KEY `username_uniq` (`username`),
  ADD KEY `user_warehouse_id` (`warehouse_id`);

--
-- Indexes for table `warehouse`
--
ALTER TABLE `warehouse`
  ADD UNIQUE KEY `warehouse_uniq` (`id`);

--
-- Indexes for table `damage`
--
ALTER TABLE `damage`
  ADD UNIQUE KEY `damage_uniq` (`id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `container`
--
ALTER TABLE `container`
  ADD CONSTRAINT `warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`);

--
-- Constraints for table `manifest`
--
ALTER TABLE `manifest`
  ADD CONSTRAINT `manifest_container_id` FOREIGN KEY (`container_id`) REFERENCES `container` (`id`);
--
-- Constraints for table `damage`
--
ALTER TABLE `damage`
  ADD CONSTRAINT `damage_manifest_id` FOREIGN KEY (`manifest_id`) REFERENCES `manifest` (`id`);
ALTER TABLE `damage`
  ADD CONSTRAINT `damage_container_id` FOREIGN KEY (`container_id`) REFERENCES `manifest` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
