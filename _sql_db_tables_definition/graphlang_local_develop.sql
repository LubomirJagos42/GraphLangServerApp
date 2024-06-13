-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2024 at 08:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `graphlang_local_develop`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_users`
--

CREATE TABLE `active_users` (
  `internal_id` int(11) NOT NULL,
  `name` varchar(1000) NOT NULL,
  `email` varchar(1000) NOT NULL,
  `password` varchar(1000) NOT NULL,
  `last_logged` datetime DEFAULT NULL,
  `token` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `active_users`
--

INSERT INTO `active_users` (`internal_id`, `name`, `email`, `password`, `last_logged`, `token`) VALUES
(1, 'LubomirJagos', 'lubomir.jagos@hidden-mail.com', '6a284155906c26cbca20c53376bc63ac', '2024-05-28 00:00:00', ''),
(2, 'GraphLang_Core', 'lubomir.jagos@hidden-mail.com', '6a284155906c26cbca20c53376bc63ac', NULL, ''),
(4, 'John Doe', 'john.doe.nonexisting.guy@gmail.com', '482c811da5d5b4bc6d497ffa98491e38', NULL, ''),
(5, 'Lucy Skyler', 'lucy.skyler.nonexsiting@gmail.com', '482c811da5d5b4bc6d497ffa98491e38', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `media_to_project_assignment`
--

CREATE TABLE `media_to_project_assignment` (
  `media_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nodes_to_category_assignment`
--

CREATE TABLE `nodes_to_category_assignment` (
  `category_id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_categories`
--

CREATE TABLE `project_categories` (
  `internal_id` int(11) NOT NULL,
  `category_name` varchar(1000) NOT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storage_media`
--

CREATE TABLE `storage_media` (
  `internal_id` int(11) NOT NULL,
  `media_owner` int(11) DEFAULT NULL,
  `media_format` varchar(100) DEFAULT NULL,
  `media_language` varchar(1000) DEFAULT NULL,
  `media_version` varchar(1000) DEFAULT NULL,
  `media_content` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storage_schematic_blocks`
--

CREATE TABLE `storage_schematic_blocks` (
  `internal_id` int(11) NOT NULL,
  `node_display_name` varchar(1000) NOT NULL,
  `node_class_name` varchar(1000) NOT NULL,
  `node_class_parent` varchar(1000) DEFAULT NULL,
  `node_content_code` blob NOT NULL,
  `node_language` varchar(2000) DEFAULT NULL,
  `node_directory` varchar(2000) DEFAULT NULL,
  `node_owner` int(11) NOT NULL,
  `node_project` int(11) DEFAULT NULL,
  `last_change` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_projects`
--

CREATE TABLE `user_projects` (
  `internal_id` int(11) NOT NULL,
  `project_owner` int(11) NOT NULL,
  `project_graphlang_version` varchar(1000) DEFAULT NULL,
  `project_name` varchar(1000) NOT NULL,
  `project_visibility` varchar(1000) NOT NULL,
  `project_image` blob DEFAULT NULL,
  `project_description` text DEFAULT NULL,
  `project_code_template` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_users`
--
ALTER TABLE `active_users`
  ADD PRIMARY KEY (`internal_id`);

--
-- Indexes for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`internal_id`);

--
-- Indexes for table `storage_media`
--
ALTER TABLE `storage_media`
  ADD PRIMARY KEY (`internal_id`);

--
-- Indexes for table `storage_schematic_blocks`
--
ALTER TABLE `storage_schematic_blocks`
  ADD PRIMARY KEY (`internal_id`);

--
-- Indexes for table `user_projects`
--
ALTER TABLE `user_projects`
  ADD PRIMARY KEY (`internal_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_users`
--
ALTER TABLE `active_users`
  MODIFY `internal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `internal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `storage_media`
--
ALTER TABLE `storage_media`
  MODIFY `internal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `storage_schematic_blocks`
--
ALTER TABLE `storage_schematic_blocks`
  MODIFY `internal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_projects`
--
ALTER TABLE `user_projects`
  MODIFY `internal_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
