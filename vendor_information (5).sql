-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 26, 2026 at 01:49 PM
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
-- Database: `vendor_information`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank`
--

CREATE TABLE `bank` (
  `NewCompanyRegistration` int(20) DEFAULT NULL,
  `time` date DEFAULT NULL,
  `BankID` int(7) NOT NULL,
  `BankName` varchar(40) DEFAULT NULL,
  `BankAddress` varchar(100) DEFAULT NULL,
  `SWIFTCode` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank`
--

INSERT INTO `bank` (`NewCompanyRegistration`, `time`, `BankID`, `BankName`, `BankAddress`, `SWIFTCode`) VALUES
(91, '2026-01-31', 1, 'Test', 'Test', 'Test'),
(95, '2026-01-31', 2, 'Test', 'Test', 'Test'),
(65, '2026-01-31', 3, 'Test', 'Test', 'Test'),
(53, '2026-02-01', 4, 'Test', 'Test', 'Test'),
(81, '2026-02-01', 5, 'Test', 'Test', 'Test'),
(91, '2026-02-03', 6, 'Test', 'Test', 'Test'),
(91, '2026-02-04', 7, 'Test', 'Test', 'Test');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contactID` int(7) NOT NULL,
  `NewCompanyRegistration` int(20) DEFAULT NULL,
  `time` date DEFAULT NULL,
  `ContactPersonName` varchar(40) DEFAULT NULL,
  `department` varchar(20) DEFAULT NULL,
  `telephone` int(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contactStatus` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`contactID`, `NewCompanyRegistration`, `time`, `ContactPersonName`, `department`, `telephone`, `email`, `contactStatus`) VALUES
(1, 91, '2026-01-31', 'Test', 'Test', 0, 'test@gmail.com', 'Primary'),
(2, 91, '2026-01-31', 'Test', 'Test', 0, 'test@gmail.com', 'Secondary'),
(3, 95, '2026-01-31', 'Test', 'Test', 0, 'test@gmail.com', 'Primary'),
(4, 95, '2026-01-31', 'Test', 'Test', 0, 'test@gmail.com', 'Secondary'),
(5, 65, '2026-01-31', 'Test', 'Test', 0, 'test@gmail.com', 'Primary'),
(6, 65, '2026-01-31', 'Test', 'Test', 0, 'test@gmail.com', 'Secondary'),
(7, 53, '2026-02-01', 'Test', 'Test', 0, 'test@gmail.com', 'Primary'),
(8, 53, '2026-02-01', 'Test', 'Test', 0, 'test@gmail.com', 'Secondary'),
(9, 81, '2026-02-01', 'Test', 'Test', 0, 'test@gmail.com', 'Primary'),
(10, 81, '2026-02-01', 'Test', 'Test', 0, 'test@gmail.com', 'Secondary'),
(11, 91, '2026-02-03', 'Test', 'Test', 0, 'test@gmail.com', 'Primary'),
(12, 91, '2026-02-03', 'Test', 'Test', 0, 'test@gmail.com', 'Secondary'),
(13, 91, '2026-02-04', 'Test', 'Test', 0, 'test@gmail.com', 'Primary'),
(14, 91, '2026-02-04', 'Test', 'Test', 0, 'test@gmail.com', 'Secondary');

-- --------------------------------------------------------

--
-- Table structure for table `creditfacilities`
--

CREATE TABLE `creditfacilities` (
  `facilityID` int(10) NOT NULL,
  `NewCompanyRegistration` int(20) DEFAULT NULL,
  `time` date DEFAULT NULL,
  `typeOfCreditFacilities` varchar(20) DEFAULT NULL,
  `financialInstitution` varchar(30) DEFAULT NULL,
  `totalAmount` double(10,2) DEFAULT NULL,
  `expirydate` date DEFAULT NULL,
  `unutilisedAmountCurrentlyAvailable` double(10,2) DEFAULT NULL,
  `asAtDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `creditfacilities`
--

INSERT INTO `creditfacilities` (`facilityID`, `NewCompanyRegistration`, `time`, `typeOfCreditFaciliites`, `financialInstitution`, `totalAmount`, `expirydate`, `unutilesedAmountCurrentlyAvailable`, `asAtDate`) VALUES
(1, 91, '2026-01-31', 'Test', 'Test', 0.00, '2024-01-01', 0.00, '2024-01-01'),
(2, 95, '2026-01-31', 'Test', 'Test', 0.00, '2024-01-01', 0.00, '2024-01-01'),
(3, 65, '2026-01-31', 'Test', 'Test', 0.00, '2024-01-01', 0.00, '2024-01-01'),
(4, 53, '2026-02-01', 'Test', 'Test', 0.00, '2024-01-01', 0.00, '2024-01-01'),
(5, 81, '2026-02-01', 'Test', 'Test', 0.00, '2024-01-01', 0.00, '2024-01-01'),
(6, 91, '2026-02-03', 'Test', 'Test', 0.00, '2024-01-01', 0.00, '2024-01-01'),
(7, 91, '2026-02-04', 'Test', 'Test', 0.00, '2024-01-01', 0.00, '2024-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `currentproject`
--

CREATE TABLE `currentproject` (
  `CurrentprojectNo` int(5) NOT NULL,
  `NewCompanyRegistration` int(20) NOT NULL,
  `time` date NOT NULL,
  `projectTitle` varchar(20) DEFAULT NULL,
  `projectNature` varchar(20) DEFAULT NULL,
  `location` varchar(56) DEFAULT NULL,
  `clientName` varchar(20) DEFAULT NULL,
  `projectValue` double(12,2) DEFAULT NULL,
  `commencement` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `progressOfTheWork` double(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `currentproject`
--

INSERT INTO `currentproject` (`CurrentprojectNo`, `NewCompanyRegistration`, `time`, `projectTitle`, `projectNature`, `location`, `clientName`, `projectValue`, `commencement`, `completionDate`, `progressOfTheWork`) VALUES
(48, 65, '2026-01-31', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01', 75.00),
(63, 81, '2026-02-01', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01', 76.00),
(70, 53, '2026-02-01', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01', 92.00),
(73, 95, '2026-01-31', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01', 58.00),
(80, 91, '2026-02-03', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01', 18.00),
(84, 91, '2026-01-31', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01', 53.00),
(84, 91, '2026-02-04', 'Test', NULL, 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01', 82.00);

-- --------------------------------------------------------

--
-- Table structure for table `directorandsecretary`
--

CREATE TABLE `directorandsecretary` (
  `DirectorID` int(10) NOT NULL,
  `NewCompanyRegistration` int(20) DEFAULT NULL,
  `time` date DEFAULT NULL,
  `nationality` varchar(20) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `position` varchar(15) DEFAULT NULL,
  `appointmentDate` date DEFAULT NULL,
  `DOB` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `directorandsecretary`
--

INSERT INTO `directorandsecretary` (`DirectorID`, `NewCompanyRegistration`, `time`, `nationality`, `name`, `position`, `appoitmentDate`, `DOB`) VALUES
(1, 91, '2026-01-31', 'Test', 'Test', 'Test', '2024-01-01', '2024-01-01'),
(2, 95, '2026-01-31', 'Test', 'Test', 'Test', '2024-01-01', '2024-01-01'),
(3, 65, '2026-01-31', 'Test', 'Test', 'Test', '2024-01-01', '2024-01-01'),
(4, 53, '2026-02-01', 'Test', 'Test', 'Test', '2024-01-01', '2024-01-01'),
(5, 81, '2026-02-01', 'Test', 'Test', 'Test', '2024-01-01', '2024-01-01'),
(6, 91, '2026-02-03', 'Test', 'Test', 'Test', '2024-01-01', '2024-01-01'),
(7, 91, '2026-02-04', 'Test', 'Test', 'Test', '2024-01-01', '2024-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipmentID` int(3) NOT NULL,
  `NewCompanyRegistration` int(20) NOT NULL,
  `time` date NOT NULL,
  `quantity` int(5) DEFAULT NULL,
  `brand` varchar(30) DEFAULT NULL,
  `rating` double(3,1) DEFAULT NULL,
  `ownership` varchar(20) DEFAULT NULL,
  `yearsOfManufacture` date DEFAULT NULL,
  `registrationNo` int(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipmentID`, `NewCompanyRegistration`, `time`, `quantity`, `brand`, `rating`, `ownership`, `yearsOfManufacture`, `registrationNo`) VALUES
(1, 1, '2026-01-10', 0, 'Test', 1.0, 'Test', '2024-01-01', 2024),
(1, 53, '2026-02-01', 0, 'Test', 5.0, 'Test', '2024-01-01', 2024),
(1, 65, '2026-01-31', 0, 'Test', 3.0, 'Test', '2024-01-01', 2024),
(1, 81, '2026-02-01', 0, 'Test', 1.0, 'Test', '2024-01-01', 2024),
(1, 91, '2026-01-31', 0, 'Test', 1.0, 'Test', '2024-01-01', 2024),
(1, 91, '2026-02-03', 0, 'Test', 9.9, 'Test', '2024-01-01', 2024),
(1, 91, '2026-02-04', 0, 'Test', 9.0, 'Test', '2024-01-01', 2024),
(1, 95, '2026-01-31', 0, 'Test', 1.0, 'Test', '2024-01-01', 2024),
(2, 1, '2026-01-10', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(2, 53, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(2, 65, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(2, 81, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(2, 91, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(2, 91, '2026-02-03', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(2, 91, '2026-02-04', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(2, 95, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(3, 1, '2026-01-10', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(3, 53, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(3, 65, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(3, 81, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(3, 91, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(3, 91, '2026-02-03', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(3, 91, '2026-02-04', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(3, 95, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(4, 1, '2026-01-10', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(4, 53, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(4, 65, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(4, 81, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(4, 91, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(4, 91, '2026-02-03', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(4, 91, '2026-02-04', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(4, 95, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(5, 1, '2026-01-10', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(5, 53, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(5, 65, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(5, 81, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(5, 91, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(5, 91, '2026-02-03', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(5, 91, '2026-02-04', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(5, 95, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(6, 1, '2026-01-10', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(6, 53, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(6, 65, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(6, 81, '2026-02-01', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(6, 91, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(6, 91, '2026-02-03', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(6, 91, '2026-02-04', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024),
(6, 95, '2026-01-31', 0, 'Test', 0.0, 'Test', '2024-01-01', 2024);

-- --------------------------------------------------------

--
-- Table structure for table `equipmentused`
--

CREATE TABLE `equipmentused` (
  `equipmentID` int(3) NOT NULL,
  `equipmentType` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipmentused`
--

INSERT INTO `equipmentused` (`equipmentID`, `equipmentType`) VALUES
(1, 'Bobcat/JCB'),
(2, 'HDD Equipment'),
(3, 'Splicing Equipment'),
(4, 'Optical Power Meter (OPM)'),
(5, 'Optical Time Domain Reflectometer (OTDR)'),
(6, 'Equipment/Test Gear');

-- --------------------------------------------------------

--
-- Table structure for table `management`
--

CREATE TABLE `management` (
  `ManagementID` int(10) NOT NULL,
  `NewCompanyRegistration` int(20) DEFAULT NULL,
  `time` date DEFAULT NULL,
  `nationality` varchar(20) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `position` varchar(15) DEFAULT NULL,
  `yearsInPosition` int(2) DEFAULT NULL,
  `yearsInRelatedField` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `management`
--

INSERT INTO `management` (`ManagementID`, `NewCompanyRegistration`, `time`, `nationality`, `name`, `position`, `yearsInPosition`, `yearsInRelatedField`) VALUES
(1, 91, '2026-01-31', 'Test', 'Test', 'Test', 16, 98),
(2, 95, '2026-01-31', 'Test', 'Test', 'Test', 64, 8),
(3, 65, '2026-01-31', 'Test', 'Test', 'Test', 55, 48),
(4, 53, '2026-02-01', 'Test', 'Test', 'Test', 37, 25),
(5, 81, '2026-02-01', 'Test', 'Test', 'Test', 34, 17),
(6, 91, '2026-02-03', 'Test', 'Test', 'Test', 89, 22);

-- --------------------------------------------------------

--
-- Table structure for table `nettworth`
--

CREATE TABLE `nettworth` (
  `NewCompanyRegistration` int(20) NOT NULL,
  `time` date NOT NULL,
  `YearOf` int(4) NOT NULL,
  `TotalLiabilities` double(8,2) DEFAULT NULL,
  `TotalAssets` double(8,2) DEFAULT NULL,
  `NetWorth` double(8,2) DEFAULT NULL,
  `WorkingCapital` double(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nettworth`
--

INSERT INTO `nettworth` (`NewCompanyRegistration`, `time`, `YearOf`, `TotalLiabilities`, `TotalAssets`, `NetWorth`, `WorkingCapital`) VALUES
(53, '2026-02-01', 2023, 0.00, 0.00, 0.00, 0.00),
(53, '2026-02-01', 2024, 0.00, 0.00, 0.00, 0.00),
(53, '2026-02-01', 2025, 0.00, 0.00, 0.00, 0.00),
(65, '2026-01-31', 2023, 0.00, 0.00, 0.00, 0.00),
(65, '2026-01-31', 2024, 0.00, 0.00, 0.00, 0.00),
(65, '2026-01-31', 2025, 0.00, 0.00, 0.00, 0.00),
(81, '2026-02-01', 2023, 0.00, 0.00, 0.00, 0.00),
(81, '2026-02-01', 2024, 0.00, 0.00, 0.00, 0.00),
(81, '2026-02-01', 2025, 0.00, 0.00, 0.00, 0.00),
(91, '2026-01-31', 2023, 0.00, 0.00, 0.00, 0.00),
(91, '2026-01-31', 2024, 0.00, 0.00, 0.00, 0.00),
(91, '2026-01-31', 2025, 0.00, 0.00, 0.00, 0.00),
(91, '2026-02-03', 2023, 0.00, 0.00, 0.00, 0.00),
(91, '2026-02-03', 2024, 0.00, 0.00, 0.00, 0.00),
(91, '2026-02-03', 2025, 0.00, 0.00, 0.00, 0.00),
(91, '2026-02-04', 2023, 0.00, 0.00, 0.00, 0.00),
(91, '2026-02-04', 2024, 0.00, 0.00, 0.00, 0.00),
(91, '2026-02-04', 2025, 0.00, 0.00, 0.00, 0.00),
(95, '2026-01-31', 2023, 0.00, 0.00, 0.00, 0.00),
(95, '2026-01-31', 2024, 0.00, 0.00, 0.00, 0.00),
(95, '2026-01-31', 2025, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `projecttrackrecord`
--

CREATE TABLE `projecttrackrecord` (
  `projectRecordNo` int(5) NOT NULL,
  `NewCompanyRegistration` int(20) NOT NULL,
  `time` date NOT NULL,
  `projectTitle` varchar(20) DEFAULT NULL,
  `projectNature` varchar(20) DEFAULT NULL,
  `location` varchar(56) DEFAULT NULL,
  `clientName` varchar(20) DEFAULT NULL,
  `projectValue` double(12,2) DEFAULT NULL,
  `commencement` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projecttrackrecord`
--

INSERT INTO `projecttrackrecord` (`projectRecordNo`, `NewCompanyRegistration`, `time`, `projectTitle`, `projectNature`, `location`, `clientName`, `projectValue`, `commencement`, `completionDate`) VALUES
(14, 95, '2026-01-31', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01'),
(29, 81, '2026-02-01', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01'),
(40, 53, '2026-02-01', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01'),
(60, 91, '2026-01-31', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01'),
(76, 65, '2026-01-31', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01'),
(83, 91, '2026-02-04', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01'),
(98, 91, '2026-02-03', 'Test', 'Test', 'Test', 'Test', 0.00, '2024-01-01', '2024-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `registrationform`
--

CREATE TABLE `registrationform` (
  `NewCompanyRegistration` int(20) NOT NULL,
  `time` date NOT NULL,
  `companyName` varchar(40) DEFAULT NULL,
  `taxRegistrationNumber` int(15) DEFAULT NULL,
  `faxNo` int(12) DEFAULT NULL,
  `companyOrganisation` varchar(30) DEFAULT NULL,
  `OldCompanyRegistration` int(20) DEFAULT NULL,
  `otherNames` varchar(40) DEFAULT NULL,
  `telephoneNumber` int(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `EmailAddress` varchar(100) NOT NULL,
  `website` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `authorisedCapital` double(12,2) DEFAULT NULL,
  `paidUpCapital` double(12,2) DEFAULT NULL,
  `CountryOfIncorporation` varchar(56) DEFAULT NULL,
  `DateOfIncorporation` date DEFAULT NULL,
  `NatureAndLineOfBusiness` varchar(20) DEFAULT NULL,
  `registeredAddress` varchar(56) DEFAULT NULL,
  `correspondenceAddress` varchar(50) DEFAULT NULL,
  `TypeOfOrganisation` varchar(30) DEFAULT NULL,
  `parentCompany` varchar(40) DEFAULT NULL,
  `parentCompanyCountry` varchar(56) DEFAULT NULL,
  `ultimateParentCompany` varchar(40) DEFAULT NULL,
  `ultimateParentCompanyCountry` varchar(56) DEFAULT NULL,
  `bankruptHistory` varchar(4) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `CIDB` varchar(20) DEFAULT NULL,
  `CIDBValidationTill` date DEFAULT NULL,
  `trade` varchar(15) DEFAULT NULL,
  `ValueOfSimilarProject` double(12,2) DEFAULT NULL,
  `ValueOfCurrentProject` double(12,2) DEFAULT NULL,
  `name` varchar(20) DEFAULT NULL,
  `designation` varchar(20) DEFAULT NULL,
  `DateOfVerification` date DEFAULT NULL,
  `AuditorCompanyName` varchar(20) DEFAULT NULL,
  `AuditorCompanyAddress` varchar(20) DEFAULT NULL,
  `AuditorName` varchar(20) DEFAULT NULL,
  `AuditorEmail` varchar(100) DEFAULT NULL,
  `AuditorPhone` int(12) DEFAULT NULL,
  `AdvocatesCompanyName` varchar(20) DEFAULT NULL,
  `AdvocatesCompanyAddress` varchar(20) DEFAULT NULL,
  `AdvocatesName` varchar(20) DEFAULT NULL,
  `AdvocatesEmail` varchar(100) DEFAULT NULL,
  `AdvocatesPhone` int(12) DEFAULT NULL,
  `AuditorYearOfService` int(2) DEFAULT NULL,
  `AdvocatesYearOfService` int(2) DEFAULT NULL,
  `Status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrationform`
--

INSERT INTO `registrationform` (`NewCompanyRegistration`, `time`, `companyName`, `taxRegistrationNumber`, `faxNo`, `companyOrganisation`, `OldCompanyRegistration`, `otherNames`, `telephoneNumber`, `email`, `EmailAddress`, `website`, `branch`, `authorisedCapital`, `paidUpCapital`, `CountryOfIncorporation`, `DateOfIncorporation`, `NatureAndLineOfBusiness`, `registeredAddress`, `correspondenceAddress`, `TypeOfOrganisation`, `parentCompany`, `parentCompanyCountry`, `ultimateParentCompany`, `ultimateParentCompanyCountry`, `bankruptHistory`, `discription`, `CIDB`, `CIDBValidationTill`, `trade`, `ValueOfSimilarProject`, `ValueOfCurrentProject`, `name`, `designation`, `DateOfVerification`, `AuditorCompanyName`, `AuditorCompanyAddress`, `AuditorName`, `AuditorEmail`, `AuditorPhone`, `AdvocatesCompanyName`, `AdvocatesCompanyAddress`, `AdvocatesName`, `AdvocatesEmail`, `AdvocatesPhone`, `AuditorYearOfService`, `AdvocatesYearOfService`, `Status`) VALUES
(53, '2026-02-01', 'Test', 79, 74, 'More than 15', 81, 'Test', 31, 'test@gmail.com', 'test@gmail.com', 'Test', 'Test', 60.00, 95.00, 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'Berhad', 'Test', 'Test', 'Test', 'Test', 'yes', 'Test', 'Test', '2024-01-01', 'ISP', 0.00, 0.00, 'Test', 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'test@gmail.com', 0, 'Test', 'Test', 'Test', 'test@gmail.com', 0, 40, 30, 'pending'),
(65, '2026-01-31', 'Test', 1, 14, 'More than 15', 20, 'Test', 25, 'test@gmail.com', 'test@gmail.com', 'Test', 'Test', 50.00, 86.00, 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'Berhad', 'Test', 'Test', 'Test', 'Test', 'yes', 'Test', 'Test', '2024-01-01', 'ISP', 0.00, 0.00, 'Test', 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'test@gmail.com', 0, 'Test', 'Test', 'Test', 'test@gmail.com', 0, 38, 98, 'pending'),
(81, '2026-02-01', 'Test', 80, 6, 'More than 15', 72, 'Test', 27, 'test@gmail.com', 'test@gmail.com', 'Test', 'Test', 50.00, 13.00, 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'Berhad', 'Test', 'Test', 'Test', 'Test', 'yes', 'Test', 'Test', '2024-01-01', 'ISP', 0.00, 0.00, 'Test', 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'test@gmail.com', 0, 'Test', 'Test', 'Test', 'test@gmail.com', 0, 97, 83, 'pending'),
(91, '2026-01-31', 'Test', 35, 86, 'More than 15', 12, 'Test', 47, 'test@gmail.com', 'danielhozhixuan@gmail.com', 'Test', 'Test', 51.00, 24.00, 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'Berhad', 'Test', 'Test', 'Test', 'Test', 'yes', 'Test', 'Test', '2024-01-01', 'ISP', 0.00, 0.00, 'Test', 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'test@gmail.com', 0, 'Test', 'Test', 'Test', 'test@gmail.com', 0, 57, 47, 'pending'),
(91, '2026-02-03', 'Asia Pacific University of Technology & ', 36, 44, 'More than 15', 71, 'Test', 143682865, 'danielhozhixuan@gmail.com', 'test@gmail.com', 'Test', 'Test', 95.00, 18.00, 'Malaysia', '2024-01-01', 'Test', 'Test', 'Test', 'Berhad', 'Test', 'Test', 'Test', 'Test', 'yes', 'Test', 'Test', '2024-01-01', 'ISP', 0.00, 0.00, 'Test', 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'test@gmail.com', 0, 'Test', 'Test', 'Test', 'test@gmail.com', 0, 8, 6, 'pending'),
(91, '2026-02-04', 'Test', 4, 2147483647, 'More than 15', 53, 'Test', 2147483647, 'test@gmail.com', 'test@gmail.com', 'Test', 'Test', 78.00, 59.00, 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'Berhad', 'Test', 'Test', 'Test', 'Test', 'yes', 'Test', 'Test', '2024-01-01', 'ISP', 0.00, 0.00, 'Test', 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'test@gmail.com', 0, 'Test', 'Test', 'Test', 'test@gmail.com', 0, 71, 54, 'pending'),
(95, '2026-01-31', 'Test', 35, 95, 'More than 15', 25, 'Test', 27, 'test@gmail.com', 'test@gmail.com', 'Test', 'Test', 6.00, 63.00, 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'Berhad', 'Test', 'Test', 'Test', 'Test', 'yes', 'Test', 'Test', '2024-01-01', 'ISP', 0.00, 0.00, 'Test', 'Test', '2024-01-01', 'Test', 'Test', 'Test', 'test@gmail.com', 0, 'Test', 'Test', 'Test', 'test@gmail.com', 0, 46, 68, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `shareholders`
--

CREATE TABLE `shareholders` (
  `ShareHolderID` int(10) NOT NULL,
  `NewCompanyRegistration` int(20) NOT NULL,
  `time` date NOT NULL,
  `nationality` varchar(20) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `address` varchar(30) DEFAULT NULL,
  `share` double(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shareholders`
--

INSERT INTO `shareholders` (`ShareHolderID`, `NewCompanyRegistration`, `time`, `nationality`, `name`, `address`, `share`) VALUES
(16, 95, '2026-01-31', 'Test', 'Test', 'Test', 100.00),
(29, 81, '2026-02-01', 'Test', 'Test', 'Test', 100.00),
(50, 91, '2026-02-03', 'Test', 'Test', 'Test', 100.00),
(54, 65, '2026-01-31', 'Test', 'Test', 'Test', 100.00),
(73, 53, '2026-02-01', 'Test', 'Test', 'Test', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffNO` int(5) NOT NULL,
  `NewCompanyRegistration` int(20) NOT NULL,
  `time` date NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `designation` varchar(30) DEFAULT NULL,
  `qualification` varchar(30) DEFAULT NULL,
  `yearsOfExperience` int(2) DEFAULT NULL,
  `employmentStatus` varchar(10) DEFAULT NULL,
  `skills` varchar(20) DEFAULT NULL,
  `ReleventCertification` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staffNO`, `NewCompanyRegistration`, `time`, `name`, `designation`, `qualification`, `yearsOfExperience`, `employmentStatus`, `skills`, `ReleventCertification`) VALUES
(4, 81, '2026-02-01', 'Test', 'Test', 'Test', 49, 'Test', 'Test', 'Test'),
(8, 91, '2026-02-04', 'Test', 'Test', 'Test', 59, 'Test', 'Test', 'Test'),
(10, 95, '2026-01-31', 'Test', 'Test', 'Test', 94, 'Test', 'Test', 'Test'),
(26, 91, '2026-02-03', 'Test', 'Test', 'Test', 36, 'Test', 'Test', 'Test'),
(34, 65, '2026-01-31', 'Test', 'Test', 'Test', 87, 'Test', 'Test', 'Test'),
(83, 53, '2026-02-01', 'Test', 'Test', 'Test', 17, 'Test', 'Test', 'Test'),
(93, 91, '2026-01-31', 'Test', 'Test', 'Test', 11, 'Test', 'Test', 'Test');

-- --------------------------------------------------------

--
-- Table structure for table `vendoraccount`
--

CREATE TABLE `vendoraccount` (
  `NewCompanyRegistration` int(20)  NULL,
  `accountID` varchar(15) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(10) NOT NULL,
  `vendor_type` varchar(50) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendoraccount`
--

INSERT INTO `vendoraccount` (`NewCompanyRegistration`, `accountID`, `password`, `role`) VALUES
(NULL, 'admin1', '$2y$10$TjVy4x.c0bKmmBHwSIzuW.0A2bLopqW5vhEbBhSnuJH4LclSt76Ye', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank`
--
ALTER TABLE `bank`
  ADD PRIMARY KEY (`BankID`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contactID`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `creditfacilities`
--
ALTER TABLE `creditfacilities`
  ADD PRIMARY KEY (`facilityID`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `currentproject`
--
ALTER TABLE `currentproject`
  ADD PRIMARY KEY (`CurrentprojectNo`,`NewCompanyRegistration`,`time`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `directorandsecretary`
--
ALTER TABLE `directorandsecretary`
  ADD PRIMARY KEY (`DirectorID`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipmentID`,`NewCompanyRegistration`,`time`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `equipmentused`
--
ALTER TABLE `equipmentused`
  ADD PRIMARY KEY (`equipmentID`);

--
-- Indexes for table `management`
--
ALTER TABLE `management`
  ADD PRIMARY KEY (`ManagementID`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `nettworth`
--
ALTER TABLE `nettworth`
  ADD PRIMARY KEY (`NewCompanyRegistration`,`time`,`YearOf`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `projecttrackrecord`
--
ALTER TABLE `projecttrackrecord`
  ADD PRIMARY KEY (`projectRecordNo`,`NewCompanyRegistration`,`time`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `registrationform`
--
ALTER TABLE `registrationform`
  ADD PRIMARY KEY (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `shareholders`
--
ALTER TABLE `shareholders`
  ADD PRIMARY KEY (`ShareHolderID`,`NewCompanyRegistration`,`time`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffNO`,`NewCompanyRegistration`,`time`),
  ADD KEY `NewCompanyRegistration` (`NewCompanyRegistration`,`time`);

--
-- Indexes for table `vendoraccount`
--
ALTER TABLE `vendoraccount`
  ADD PRIMARY KEY (`NewCompanyRegistration`, `accountID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank`
--
ALTER TABLE `bank`
  MODIFY `BankID` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `currentproject`
--
ALTER TABLE `currentproject`
  MODIFY `CurrentprojectNo` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contactID` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `creditfacilities`
--
ALTER TABLE `creditfacilities`
  MODIFY `facilityID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `directorandsecretary`
--
ALTER TABLE `directorandsecretary`
  MODIFY `DirectorID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipmentID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `management`
--
ALTER TABLE `management`
  MODIFY `ManagementID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projecttrackrecord`
--
ALTER TABLE `projecttrackrecord`
  MODIFY `projectRecordNo` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staffNO` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for table `vendoraccount`
--
ALTER TABLE `vendoraccount`
  ADD CONSTRAINT `vendoraccount_ibfk_1` FOREIGN KEY (`NewCompanyRegistration`) REFERENCES `registrationform` (`NewCompanyRegistration`);

--
-- Constraints for table `shareholders`
--
ALTER TABLE `shareholders`
  ADD CONSTRAINT `shareholders_ibfk_1` FOREIGN KEY (`NewCompanyRegistration`,`time`) REFERENCES `registrationform` (`NewCompanyRegistration`, `time`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
