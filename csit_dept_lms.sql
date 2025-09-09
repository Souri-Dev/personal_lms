-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Aug 13, 2025 at 12:34 AM
-- Server version: 8.0.43
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `csit_dept_lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `class_section_id` int NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attendance_session_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `class_section_id`, `date`, `status`, `attendance_session_id`) VALUES
(28, 4, 1, '2025-08-07 00:16:46', 'present', 9),
(29, 7, 9, '2025-08-10 18:51:21', 'present', 10),
(30, 4, 6, '2025-08-10 22:38:08', 'present', 14);

-- --------------------------------------------------------

--
-- Table structure for table `attendance_session`
--

CREATE TABLE `attendance_session` (
  `id` int NOT NULL,
  `class_section_id` int NOT NULL,
  `date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_session`
--

INSERT INTO `attendance_session` (`id`, `class_section_id`, `date`, `is_active`, `started_at`, `ended_at`) VALUES
(9, 1, '2025-08-07', 0, '2025-08-07 00:16:25', '2025-08-07 00:17:15'),
(10, 9, '2025-08-10', 0, NULL, '2025-08-10 18:51:28'),
(11, 8, '2025-08-10', 1, '2025-08-10 20:21:50', NULL),
(12, 10, '2025-08-10', 0, '2025-08-10 20:24:04', '2025-08-10 20:24:21'),
(13, 7, '2025-08-10', 0, '2025-08-10 21:24:37', '2025-08-10 22:37:49'),
(14, 6, '2025-08-10', 0, NULL, '2025-08-10 22:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `class_section`
--

CREATE TABLE `class_section` (
  `id` int NOT NULL,
  `class_id` int DEFAULT NULL,
  `section_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_in` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_section`
--

INSERT INTO `class_section` (`id`, `class_id`, `section_name`, `time_in`) VALUES
(1, 1, 'A', '08:00:00'),
(2, 1, 'B', '13:00:00'),
(3, 1, 'C', '15:00:00'),
(5, 1, 'D', '09:00:00'),
(6, 4, 'A', '00:00:00'),
(7, 4, 'B', '00:00:00'),
(8, 5, 'A', '19:00:00'),
(9, 6, 'A', '22:17:00'),
(10, 6, 'B', '13:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250707030547', '2025-07-26 12:53:46', 290),
('DoctrineMigrations\\Version20250726134422', '2025-07-26 15:44:36', 181),
('DoctrineMigrations\\Version20250801160625', '2025-08-01 18:06:33', 137),
('DoctrineMigrations\\Version20250802134134', '2025-08-02 15:42:38', 53),
('DoctrineMigrations\\Version20250802191627', '2025-08-02 21:16:44', 79),
('DoctrineMigrations\\Version20250802194137', '2025-08-02 21:41:46', 42),
('DoctrineMigrations\\Version20250803151616', '2025-08-03 17:16:30', 97),
('DoctrineMigrations\\Version20250804141931', '2025-08-04 22:19:41', 91),
('DoctrineMigrations\\Version20250806145932', '2025-08-06 22:59:44', 162),
('DoctrineMigrations\\Version20250806150118', '2025-08-06 23:05:01', 183),
('DoctrineMigrations\\Version20250806155305', '2025-08-06 23:53:13', 36);

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_class`
--

CREATE TABLE `school_class` (
  `id` int NOT NULL,
  `subject_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_class`
--

INSERT INTO `school_class` (`id`, `subject_name`, `subject_code`, `description`) VALUES
(1, 'Management Information Technology', 'ITS 310', 'This is a test subject'),
(4, 'Internship 1', 'ITS 401', 'This is a test'),
(5, 'OOP', 'ITS 209', 'Another testing'),
(6, 'GRAPHICS', 'ITS 208', 'TEST');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int NOT NULL,
  `student_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `student_number`, `name`, `course`, `section`, `qr`) VALUES
(1, '201904262', 'Ariel Bensing', 'BSINT', 'A', '6bcef74d-24de-450e-aa46-e66ddbdaa0f1'),
(4, '20123123', 'Juan Dela Cruz', 'BSCS', 'B', 'ded6bcde-e078-4801-b031-32807c04176d'),
(6, '20134991', 'John Doe', 'BSINT', 'A', '87d739ee-b4a2-45c2-86cd-e479795e2b87'),
(7, '20112345', 'Jane Doe', 'BSIT', 'A', '217de1ad-91dd-4d2f-97fd-178af1324614');

-- --------------------------------------------------------

--
-- Table structure for table `students_class_sections`
--

CREATE TABLE `students_class_sections` (
  `student_id` int NOT NULL,
  `class_section_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students_class_sections`
--

INSERT INTO `students_class_sections` (`student_id`, `class_section_id`) VALUES
(1, 2),
(1, 6),
(4, 1),
(4, 6),
(6, 1),
(6, 8),
(6, 9),
(7, 8),
(7, 9);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `roles`, `password`) VALUES
(1, 'Abensing', '[]', '$2y$13$0yZcw/CDeM0ixUmob6LYh.7T1i6xJQ.9x/BLWDNGUES8D4PpFkL6u');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6DE30D91CB944F1A` (`student_id`),
  ADD KEY `IDX_6DE30D916E2E11D8` (`class_section_id`),
  ADD KEY `IDX_6DE30D91A746B1C7` (`attendance_session_id`);

--
-- Indexes for table `attendance_session`
--
ALTER TABLE `attendance_session`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_date` (`class_section_id`,`date`),
  ADD KEY `IDX_D7833BD66E2E11D8` (`class_section_id`);

--
-- Indexes for table `class_section`
--
ALTER TABLE `class_section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_E8061D13EA000B10` (`class_id`);

--
-- Indexes for table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Indexes for table `school_class`
--
ALTER TABLE `school_class`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_33B1AF85E5DFB443` (`subject_code`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_B723AF33C9F64A58` (`qr`),
  ADD UNIQUE KEY `UNIQ_B723AF335E237E06` (`name`);

--
-- Indexes for table `students_class_sections`
--
ALTER TABLE `students_class_sections`
  ADD PRIMARY KEY (`student_id`,`class_section_id`),
  ADD KEY `IDX_AF98F5D4CB944F1A` (`student_id`),
  ADD KEY `IDX_AF98F5D46E2E11D8` (`class_section_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_USERNAME` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `attendance_session`
--
ALTER TABLE `attendance_session`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `class_section`
--
ALTER TABLE `class_section`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_class`
--
ALTER TABLE `school_class`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `FK_6DE30D916E2E11D8` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`),
  ADD CONSTRAINT `FK_6DE30D91A746B1C7` FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_session` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_6DE30D91CB944F1A` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`);

--
-- Constraints for table `attendance_session`
--
ALTER TABLE `attendance_session`
  ADD CONSTRAINT `FK_D7833BD66E2E11D8` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_section`
--
ALTER TABLE `class_section`
  ADD CONSTRAINT `FK_E8061D13EA000B10` FOREIGN KEY (`class_id`) REFERENCES `school_class` (`id`);

--
-- Constraints for table `students_class_sections`
--
ALTER TABLE `students_class_sections`
  ADD CONSTRAINT `FK_AF98F5D46E2E11D8` FOREIGN KEY (`class_section_id`) REFERENCES `class_section` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_AF98F5D4CB944F1A` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
