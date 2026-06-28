-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2026 at 11:45 AM
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
-- Database: `internship_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application` (
  `ApplicationID` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL,
  `CompanyID` int(11) NOT NULL,
  `Status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `SubmissionDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application`
--

INSERT INTO `application` (`ApplicationID`, `StudentID`, `CompanyID`, `Status`, `SubmissionDate`) VALUES
(1, 12, 1, 'Pending', '2026-06-15 09:00:00'),
(2, 12, 3, 'Pending', '2026-06-15 10:15:00'),
(4, 12, 5, 'Pending', '2026-06-16 14:00:00'),
(5, 13, 2, 'Pending', '2026-06-17 08:45:00'),
(6, 13, 4, 'Pending', '2026-06-17 13:20:00'),
(7, 13, 6, 'Pending', '2026-06-18 09:10:00'),
(8, 14, 1, 'Pending', '2026-06-14 10:00:00'),
(9, 14, 5, 'Pending', '2026-06-15 11:15:00'),
(10, 14, 6, 'Pending', '2026-06-16 12:30:00'),
(11, 14, 9, 'Pending', '2026-06-16 16:45:00'),
(12, 15, 3, 'Pending', '2026-06-16 09:30:00'),
(13, 15, 5, 'Pending', '2026-06-17 10:45:00'),
(14, 15, 2, 'Pending', '2026-06-18 14:15:00'),
(15, 16, 7, 'Pending', '2026-06-15 13:00:00'),
(16, 16, 8, 'Pending', '2026-06-16 15:30:00'),
(17, 16, 4, 'Pending', '2026-06-18 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `CompanyID` int(11) NOT NULL,
  `CompanyName` varchar(255) NOT NULL,
  `Industry` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`CompanyID`, `CompanyName`, `Industry`) VALUES
(1, 'Microsoft Corporation', 'Technology'),
(2, 'Apple Inc.', 'Consumer Electronics'),
(3, 'NVIDIA Corporation', 'Semiconductors'),
(4, 'Amazon.com, Inc.', 'E-commerce & Cloud'),
(5, 'Alphabet Inc. (Google)', 'Internet Services'),
(6, 'Meta Platforms, Inc.', 'Social Media'),
(7, 'Berkshire Hathaway', 'Financial Services'),
(8, 'Tesla, Inc.', 'Automotive'),
(9, 'Eli Lilly and Company', 'Pharmaceuticals');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Roles` enum('Admin','Supervisor','Student') NOT NULL,
  `MatricNumber` varchar(50) DEFAULT NULL,
  `CGPA` decimal(3,2) DEFAULT NULL,
  `Major` varchar(100) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `CompanyID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Name`, `Email`, `Password`, `Roles`, `MatricNumber`, `CGPA`, `Major`, `ContactNumber`, `CompanyID`) VALUES
(1, 'System Admin', 'admin@utm.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Admin', NULL, NULL, NULL, NULL, NULL),
(2, 'Satya Nadella', 'satya@microsoft.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111222', 1),
(3, 'Tim Cook', 'tim@apple.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111333', 2),
(4, 'Jensen Huang', 'jensen@nvidia.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111444', 3),
(5, 'Andy Jassy', 'andy@amazon.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111555', 4),
(6, 'Sundar Pichai', 'sundar@google.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111666', 5),
(7, 'Mark Zuckerberg', 'mark@meta.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111777', 6),
(8, 'Warren Buffett', 'warren@berkshire.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111888', 7),
(9, 'Elon Musk', 'elon@tesla.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111999', 8),
(10, 'David Ricks', 'david@elililly.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111000', 9),
(11, 'Hock Tan', 'hock@broadcom.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Supervisor', NULL, NULL, NULL, '+1800111111', NULL),
(12, 'Looi Yu Xiang', 'yuxiang@gmail.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Student', 'A24CS0107', 3.85, 'Network and Security', '+60111222333', NULL),
(13, 'Tan Jia Yie', 'jiayie@gmail.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Student', 'A24CS0108', 3.70, 'Software Engineering', '+60111222444', NULL),
(14, 'Ng She Ling', 'sheling@gmail.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Student', 'A24CS0109', 3.90, 'Data Engineering', '+60111222555', NULL),
(15, 'Mohammad Adrian Syahirin', 'adrian@gmail.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Student', 'A24CS0110', 3.65, 'Bioinformatics', '+60111222666', NULL),
(16, 'Ezralyn', 'ezralyn@gmail.com', '$2y$10$t0T1VgCidpDV6UIE.7LpcO3KAWOybbU5L2fBCzSgqGLfK83ZBQFnq', 'Student', 'A24CS0111', 3.80, 'Graphics and Multimedia', '+60111222777', NULL),
(17, 'Yu Xiang Looi', 'lyx878lvyuxiang@gmail.com', '$2y$10$GYZiEo.ontGlh78UIb1n4ej9grpzj9tl9LpsH5HYsAkAf2VuRjwWu', 'Supervisor', NULL, NULL, NULL, '+0115161', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`ApplicationID`),
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `CompanyID` (`CompanyID`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`CompanyID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `CompanyID` (`CompanyID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `ApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `CompanyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application`
--
ALTER TABLE `application`
  ADD CONSTRAINT `application_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_ibfk_2` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
