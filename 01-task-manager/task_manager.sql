-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2026 at 05:00 PM
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
-- Database: `task_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','done') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `due_date`, `priority`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Fix critical login bug', '2026-03-28', 'high', 'in_progress', '2026-03-28 18:53:43', '2026-03-28 18:53:43'),
(2, 'Deploy to staging server', '2026-03-29', 'high', 'pending', '2026-03-28 18:53:43', '2026-03-28 18:53:43'),
(3, 'Write unit tests for auth', '2026-03-29', 'medium', 'pending', '2026-03-28 18:53:43', '2026-03-28 18:53:43'),
(4, 'Update API documentation', '2026-03-31', 'medium', 'pending', '2026-03-28 18:53:43', '2026-03-28 18:53:43'),
(5, 'Refactor database queries', '2026-04-02', 'low', 'pending', '2026-03-28 18:53:43', '2026-03-28 18:53:43'),
(6, 'Code review PR #42', '2026-03-28', 'medium', 'done', '2026-03-28 18:53:43', '2026-03-28 18:53:43'),
(7, 'Setup CI/CD pipeline', '2026-04-04', 'high', 'pending', '2026-03-28 18:53:43', '2026-03-28 18:53:43'),
(8, 'Clean up unused dependencies', '2026-03-30', 'low', 'pending', '2026-03-28 18:53:43', '2026-03-28 18:53:43'),
(9, 'fix login bug', '2026-04-01', 'high', 'done', '2026-03-28 18:56:33', '2026-03-28 18:56:58'),
(11, 'Test task 1774779374150', '2026-03-29', 'high', 'pending', '2026-03-29 10:16:14', '2026-03-29 10:16:14'),
(12, 'Test task 1774779375714', '2026-03-29', 'high', 'pending', '2026-03-29 10:16:15', '2026-03-29 10:16:15'),
(13, 'planning on the new system development', '2026-03-31', 'high', 'in_progress', '2026-03-29 12:30:53', '2026-03-29 12:31:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_title_due_date` (`title`,`due_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
