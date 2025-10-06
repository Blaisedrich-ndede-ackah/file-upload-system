-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 03:37 PM
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
-- Database: `file_upload_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'kofi', '$2y$10$eXTB30RzvL3fofAN7aasOeRJiDhBCeV.bd1jzE4t6WP/VubPAV12m', '2025-05-10 13:11:32'),
(2, 'admin', '$2y$10$ocVG4iffxUfB7nX7DJloR.bmdAb9KRw7Vw4K34ytWz3C.zObKA7c.', '2025-05-10 13:16:21'),
(6, 'Nana', '$2y$10$w6FaRyeM96PP2T84S7iYsuB14EqZDrLBRkYHGnfeatrdQu9Bpe2AK', '2025-05-10 13:29:03'),
(8, 'Maame', '$2y$10$yfQW8hI7MYNHcnhXVkpm0.Yy5XTggCQySiN/ZNUK14KHhcZgZppCu', '2025-05-10 13:30:19');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `download_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `filename`, `original_filename`, `file_path`, `file_size`, `file_type`, `upload_date`, `status`, `download_count`) VALUES
(1, '681f50a3c0799.jpg', 'about.jpg', 'uploads/681f50a3c0799.jpg', 5349405, 'image/jpeg', '2025-05-10 13:12:03', 'rejected', 1),
(2, '681f559d26fd4.jpg', 'about.jpg', 'uploads/681f559d26fd4.jpg', 5349405, 'image/jpeg', '2025-05-10 13:33:17', 'approved', 0),
(3, '681f565922608.jpg', 'profile.jpg', 'uploads/681f565922608.jpg', 2950275, 'image/jpeg', '2025-05-10 13:36:25', 'approved', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
