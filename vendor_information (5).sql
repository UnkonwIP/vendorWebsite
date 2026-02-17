-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 12:14 AM
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
  `bankID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `bankName` varchar(40) DEFAULT NULL,
  `bankAddress` varchar(100) DEFAULT NULL,
  `swiftCode` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `contactID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `contactPersonName` varchar(40) DEFAULT NULL,
  `department` varchar(20) DEFAULT NULL,
  `telephoneNumber` varchar(20) DEFAULT NULL,
  `emailAddress` varchar(100) DEFAULT NULL,
  `contactStatus` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `creditfacilities`
--

CREATE TABLE `creditfacilities` (
  `facilityID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `typeOfCreditFacilities` varchar(30) DEFAULT NULL,
  `financialInstitution` varchar(30) DEFAULT NULL,
  `totalAmount` decimal(10,2) DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `unutilisedAmountCurrentlyAvailable` decimal(10,2) DEFAULT NULL,
  `asAtDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currentproject`
--

CREATE TABLE `currentproject` (
  `currentProjectID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `projectTitle` varchar(50) DEFAULT NULL,
  `projectNature` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `clientName` varchar(100) DEFAULT NULL,
  `projectValue` decimal(12,2) DEFAULT NULL,
  `commencementDate` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `progressOfTheWork` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `directorandsecretary`
--

CREATE TABLE `directorandsecretary` (
  `directorID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `nationality` varchar(20) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `position` varchar(20) DEFAULT NULL,
  `appointmentDate` date DEFAULT NULL,
  `dob` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipmentRecordID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `equipmentID` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `brand` varchar(30) DEFAULT NULL,
  `rating` varchar(10) DEFAULT NULL,
  `ownership` varchar(20) DEFAULT NULL,
  `yearsOfManufacture` date DEFAULT NULL,
  `registrationNo` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipmentused`
--

CREATE TABLE `equipmentused` (
  `equipmentID` int(11) NOT NULL,
  `equipmentType` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `equipmentused` (`equipmentID`, `equipmentType`) VALUES
(1, 'Bobcat/JCB'),
(2, 'HDD Equipment'),
(3, 'Splicing Equipment'),
(4, 'Optical Power Meter (OPM)'),
(5, 'OTDR'),
(6, 'Equipment/Test Gear');


-- --------------------------------------------------------

--
-- Table structure for table `management`
--

CREATE TABLE `management` (
  `managementID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `nationality` varchar(20) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `position` varchar(20) DEFAULT NULL,
  `yearsInPosition` int(11) DEFAULT NULL,
  `yearsInRelatedField` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nettworth`
--

CREATE TABLE `nettworth` (
  `networthID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `yearOf` year(4) NOT NULL,
  `totalLiabilities` decimal(15,2) DEFAULT NULL,
  `totalAssets` decimal(15,2) DEFAULT NULL,
  `netWorth` decimal(15,2) DEFAULT NULL,
  `workingCapital` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------

--
-- Table structure for table `projecttrackrecord`
--

CREATE TABLE `projecttrackrecord` (
  `projectRecordID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `projectTitle` varchar(50) DEFAULT NULL,
  `projectNature` varchar(30) DEFAULT NULL,
  `location` varchar(56) DEFAULT NULL,
  `clientName` varchar(30) DEFAULT NULL,
  `projectValue` decimal(12,2) DEFAULT NULL,
  `commencementDate` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registrationform`
--

CREATE TABLE `registrationform` (
  `registrationFormID` int(11) NOT NULL,
  `newCompanyRegistrationNumber` varchar(20) NOT NULL,
  `formFirstSubmissionDate` date NOT NULL,
  `companyName` varchar(40) DEFAULT NULL,
  `taxRegistrationNumber` varchar(20) DEFAULT NULL,
  `faxNo` varchar(20) DEFAULT NULL,
  `companyOrganisation` varchar(30) DEFAULT NULL,
  `oldCompanyRegistrationNumber` varchar(20) DEFAULT NULL,
  `otherNames` varchar(40) DEFAULT NULL,
  `telephoneNumber` varchar(20) DEFAULT NULL,
  `emailAddress` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `authorisedCapital` decimal(12,2) DEFAULT NULL,
  `paidUpCapital` decimal(12,2) DEFAULT NULL,
  `countryOfIncorporation` varchar(56) DEFAULT NULL,
  `dateOfIncorporation` date DEFAULT NULL,
  `natureAndLineOfBusiness` varchar(50) DEFAULT NULL,
  `registeredAddress` varchar(100) DEFAULT NULL,
  `correspondenceAddress` varchar(100) DEFAULT NULL,
  `typeOfOrganisation` varchar(30) DEFAULT NULL,
  `parentCompany` varchar(40) DEFAULT NULL,
  `parentCompanyCountry` varchar(56) DEFAULT NULL,
  `ultimateParentCompany` varchar(40) DEFAULT NULL,
  `ultimateParentCompanyCountry` varchar(56) DEFAULT NULL,
  `bankruptHistory` varchar(5) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `cidb` varchar(20) DEFAULT NULL,
  `cidbValidationTill` date DEFAULT NULL,
  `trade` varchar(30) DEFAULT NULL,
  `otherTradeDetails` varchar(30) DEFAULT NULL,
  `valueOfSimilarProject` varchar(50) DEFAULT NULL,
  `valueOfCurrentProject` varchar(50) DEFAULT NULL,
  `yearsOfExperienceInIndustry` int(11) DEFAULT NULL,
  `creditFacilitiesStatus` varchar(10) DEFAULT NULL,
  `verifierName` varchar(20) DEFAULT NULL,
  `verifierDesignation` varchar(20) DEFAULT NULL,
  `dateOfVerification` date DEFAULT NULL,
  `auditorCompanyName` varchar(50) DEFAULT NULL,
  `auditorCompanyAddress` varchar(100) DEFAULT NULL,
  `auditorName` varchar(50) DEFAULT NULL,
  `auditorEmail` varchar(100) DEFAULT NULL,
  `auditorPhone` varchar(20) DEFAULT NULL,
  `auditorYearOfService` int(11) DEFAULT NULL,
  `advocatesCompanyName` varchar(50) DEFAULT NULL,
  `advocatesCompanyAddress` varchar(100) DEFAULT NULL,
  `advocatesName` varchar(50) DEFAULT NULL,
  `advocatesEmail` varchar(100) DEFAULT NULL,
  `advocatesPhone` varchar(20) DEFAULT NULL,
  `advocatesYearOfService` int(11) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'DRAFT',
  `rejectionReason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shareholders`
--

CREATE TABLE `shareholders` (
  `shareholderID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `companyShareholderID` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `sharePercentage` decimal(5,2) DEFAULT NULL CHECK (`sharePercentage` >= 0 and `sharePercentage` <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffID` int(11) NOT NULL,
  `registrationFormID` int(11) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `designation` varchar(30) DEFAULT NULL,
  `qualification` varchar(30) DEFAULT NULL,
  `yearsOfExperience` int(11) DEFAULT NULL,
  `employmentStatus` varchar(15) DEFAULT NULL,
  `skills` varchar(50) DEFAULT NULL,
  `relevantCertification` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendoraccount`
--

CREATE TABLE `vendoraccount` (
  `accountID` varchar(15) NOT NULL,
  `newCompanyRegistrationNumber` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `passwordHash` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(10) NOT NULL,
  `vendorType` varchar(50) DEFAULT NULL,
  `resetToken` varchar(64) DEFAULT NULL,
  `resetExpiry` datetime DEFAULT NULL,
  `formRenewalStatus` varchar(20) DEFAULT 'not complete'

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank`
--
ALTER TABLE `bank`
  ADD PRIMARY KEY (`bankID`),
  ADD KEY `registrationFormID` (`registrationFormID`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`contactID`),
  ADD KEY `registrationFormID` (`registrationFormID`);

--
-- Indexes for table `creditfacilities`
--
ALTER TABLE `creditfacilities`
  ADD PRIMARY KEY (`facilityID`),
  ADD KEY `registrationFormID` (`registrationFormID`);

--
-- Indexes for table `currentproject`
--
ALTER TABLE `currentproject`
  ADD PRIMARY KEY (`currentProjectID`),
  ADD KEY `fk_currentproject_registration` (`registrationFormID`);

--
-- Indexes for table `directorandsecretary`
--
ALTER TABLE `directorandsecretary`
  ADD PRIMARY KEY (`directorID`),
  ADD KEY `registrationFormID` (`registrationFormID`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipmentRecordID`),
  ADD KEY `fk_equipment_form` (`registrationFormID`),
  ADD KEY `fk_equipment_type` (`equipmentID`);

--
-- Indexes for table `equipmentused`
--
ALTER TABLE `equipmentused`
  ADD PRIMARY KEY (`equipmentID`);

--
-- Indexes for table `management`
--
ALTER TABLE `management`
  ADD PRIMARY KEY (`managementID`),
  ADD KEY `registrationFormID` (`registrationFormID`);

--
-- Indexes for table `nettworth`
--
ALTER TABLE `nettworth`
  ADD PRIMARY KEY (`networthID`),
  ADD KEY `fk_nettworth_registration` (`registrationFormID`);

--
-- Indexes for table `projecttrackrecord`
--
ALTER TABLE `projecttrackrecord`
  ADD PRIMARY KEY (`projectRecordID`),
  ADD KEY `registrationFormID` (`registrationFormID`);

--
-- Indexes for table `registrationform`
--
ALTER TABLE `registrationform`
  ADD PRIMARY KEY (`registrationFormID`),
  ADD KEY `fk_registration_vendor` (`newCompanyRegistrationNumber`);

--
-- Indexes for table `shareholders`
--
ALTER TABLE `shareholders`
  ADD PRIMARY KEY (`shareholderID`),
  ADD KEY `registrationFormID` (`registrationFormID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffID`),
  ADD KEY `registrationFormID` (`registrationFormID`);

--
-- Indexes for table `vendoraccount`
--
ALTER TABLE `vendoraccount`
  ADD PRIMARY KEY (`accountID`),
  ADD UNIQUE KEY `newCompanyRegistrationNumber` (`newCompanyRegistrationNumber`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank`
--
ALTER TABLE `bank`
  MODIFY `bankID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `contactID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `creditfacilities`
--
ALTER TABLE `creditfacilities`
  MODIFY `facilityID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currentproject`
--
ALTER TABLE `currentproject`
  MODIFY `currentProjectID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `directorandsecretary`
--
ALTER TABLE `directorandsecretary`
  MODIFY `directorID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipmentRecordID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipmentused`
--
ALTER TABLE `equipmentused`
  MODIFY `equipmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `management`
--
ALTER TABLE `management`
  MODIFY `managementID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nettworth`
--
ALTER TABLE `nettworth`
  MODIFY `networthID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projecttrackrecord`
--
ALTER TABLE `projecttrackrecord`
  MODIFY `projectRecordID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registrationform`
--
ALTER TABLE `registrationform`
  MODIFY `registrationFormID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shareholders`
--
ALTER TABLE `shareholders`
  MODIFY `shareholderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staffID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank`
--
ALTER TABLE `bank`
  ADD CONSTRAINT `bank_ibfk_1` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;

--
-- Constraints for table `creditfacilities`
--
ALTER TABLE `creditfacilities`
  ADD CONSTRAINT `creditfacilities_ibfk_1` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;

--
-- Constraints for table `currentproject`
--
ALTER TABLE `currentproject`
  ADD CONSTRAINT `fk_currentproject_registration` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;

--
-- Constraints for table `directorandsecretary`
--
ALTER TABLE `directorandsecretary`
  ADD CONSTRAINT `directorandsecretary_ibfk_1` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `fk_equipment_form` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_equipment_type` FOREIGN KEY (`equipmentID`) REFERENCES `equipmentused` (`equipmentID`);

--
-- Constraints for table `management`
--
ALTER TABLE `management`
  ADD CONSTRAINT `management_ibfk_1` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;

--
-- Constraints for table `nettworth`
--
ALTER TABLE `nettworth`
  ADD CONSTRAINT `fk_nettworth_registration` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;
COMMIT;

--
-- Constraints for table `projecttrackrecord`
--
ALTER TABLE `projecttrackrecord`
  ADD CONSTRAINT `projecttrackrecord_ibfk_1` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;

--
-- Constraints for table `registrationform`
--
ALTER TABLE `registrationform`
  ADD CONSTRAINT `fk_registration_vendor` FOREIGN KEY (`newCompanyRegistrationNumber`) REFERENCES `vendoraccount` (`newCompanyRegistrationNumber`) ON DELETE CASCADE;

--
-- Constraints for table `shareholders`
--
ALTER TABLE `shareholders`
  ADD CONSTRAINT `fk_shareholder_registration` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`registrationFormID`) REFERENCES `registrationform` (`registrationFormID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
