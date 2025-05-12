-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2024 at 01:41 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `book`
--

-- --------------------------------------------------------

--
-- Table structure for table `passwords`
--

CREATE TABLE `passwords` (
  `ID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `PassTitle` varchar(255) NOT NULL,
  `Pass` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passwords`
--

INSERT INTO `passwords` (`ID`, `UserID`, `PassTitle`, `Pass`) VALUES
(2, 5, 'asd', 'Qk+EQDAciHoBclXNyDGckA=='),
(3, 5, 'asd123', 'eCF6hvxUy0vs4jwm0jVMTA==');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(60) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Username`, `Password`) VALUES
(4, 'Batuhan', '$2y$10$P8QSc35iHrclkG/t4BNpWuciSHl8IHUn3mSlhPeqoVTTzdBjIASPK'),
(5, 'User123', '$2y$10$N8dd8UBHILPgVxmkUSSA7O4epTTix7a4mAuYiCsxK9ex5GUGRI9rm');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `passwords`
--
ALTER TABLE `passwords`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `UserID` (`UserID`,`PassTitle`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`,`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `passwords`
--
ALTER TABLE `passwords`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(60) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `passwords`
--
ALTER TABLE `passwords`
  ADD CONSTRAINT `passwords_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
