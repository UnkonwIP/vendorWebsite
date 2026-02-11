<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
<?php
    require_once "config.php";
    if ($conn->connect_error) { die('Connection Failed : ' . $conn->connect_error); }

    // --- Variables ---
    $newCRN = $_POST['newCRN'] ?? '';
    $currentDate = date('Y-m-d');
    $CompanyName = $_POST['CompanyName'] ?? '';
    $tax = $_POST['tax'] ?? '';
    $FaxNo = $_POST['FaxNo'] ?? '';
    $companyOrganisation = $_POST['CompanyOrganisation'] ?? '';
    $oldCRN = $_POST['oldCRN'] ?? '';
    $OtherName = $_POST['OtherName'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $EmailAddress = $_POST['EmailAddress'] ?? '';
    $website = $_POST['Website'] ?? '';
    $BranchAddress = $_POST['BranchAddress'] ?? '';
    $AuthorisedCapital = $_POST['AuthorisedCapital'] ?? 0;
    $PaidUpCapital = $_POST['PaidUpCapital'] ?? 0;
    $CountryOfIncorporation = $_POST['CountryOfIncorporation'] ?? ''; // Fixed variable check
    $DateOfIncorporation = $_POST['DateOfIncorporation'] ?? '';
    $NatureOfBusiness = $_POST['NatureOfBusiness'] ?? '';
    $RegisteredAddress = $_POST['RegisteredAddress'] ?? '';
    $CorrespondenceAddress = $_POST['CorrespondenceAddress'] ?? '';
    $TypeOfOrganisation = $_POST['TypeOfOrganisation'] ?? '';
    $ParentCompany = $_POST['ParentCompany'] ?? '';
    $ParentCompanyCountry = $_POST['ParentCompanyCountry'] ?? '';
    $UltimateParentCompany = $_POST['UltimateParentCompany'] ?? '';
    $UParentCompanyCountry = $_POST['UParentCompanyCountry'] ?? '';
    $bankruptcy = $_POST['bankruptcy'] ?? '';
    $bankruptcyDescription = $_POST['bankruptcy-details'] ?? '';
    $CIDB = $_POST['CIDB'] ?? '';
    $CIDBValidityDate = $_POST['CIDBValidityDate'] ?? '';
    $CIDBTrade = $_POST['CIDBTrade'] ?? '';
    $ValueOfSimilarProject = $_POST['ValueOfSimilarProject'] ?? '';
    $ValueOfCurrentProject = $_POST['ValueOfCurrentProject'] ?? '';
    $ExperienceInIndustry = $_POST['ExperienceInIndustry'] ?? 0;
    $creditFacilitiesStatus = $_POST['CreditFacilitiesStatus'] ?? '';
    $name = $_POST['NameOfWritter'] ?? '';
    $DesignationOfWritter = $_POST['DesignationOfWritter'] ?? '';
    $DateOfWritting = $_POST['DateOfWritting'] ?? '';
    $AuditorCompanyName = $_POST['AuditorCompanyName'] ?? '';
    $AuditorCompanyAddress = $_POST['AuditorCompanyAddress'] ?? '';
    $AuditorPersonName = $_POST['AuditorPersonName'] ?? '';
    $AuditorPersonEmail = $_POST['AuditorPersonEmail'] ?? '';
    $AuditorPersonPhone = $_POST['AuditorPersonPhone'] ?? '';
    $AuditorYearOfService = $_POST['AuditorYearOfService'] ?? 0;
    $AdvocatesCompanyName = $_POST['AdvocatesCompanyName'] ?? '';
    $AdvocatesCompanyAddress = $_POST['AdvocatesCompanyAddress'] ?? '';
    $AdvocatesPersonName = $_POST['AdvocatesPersonName'] ?? '';
    $AdvocatesPersonEmail = $_POST['AdvocatesPersonEmail'] ?? '';
    $AdvocatesPersonPhone = $_POST['AdvocatesPersonPhone'] ?? '';
    $AdvocatesYearOfService = $_POST['AdvocatesYearOfService'] ?? 0;
    $Status = "pending";

    // --- Main Insert ---
    // FIX: Corrected the bind param string types. 
    // Mapped 'AuthorisedCapital' and 'PaidUpCapital' as 'd' (decimal).
    // Mapped 'CountryOfIncorporation' correctly as 's' (string).
    
    $stmt = $conn->prepare("INSERT INTO registrationform (
    newCompanyRegistrationNumber, formFirstSubmissionDate, companyName, taxRegistrationNumber, faxNo, 
    companyOrganisation, oldCompanyRegistrationNumber, otherNames, telephoneNumber, emailAddress, 
    website, branch, authorisedCapital, paidUpCapital, countryOfIncorporation, 
    dateOfIncorporation, natureAndLineOfBusiness, registeredAddress, correspondenceAddress, typeOfOrganisation, 
    parentCompany, parentCompanyCountry, ultimateParentCompany, ultimateParentCompanyCountry, bankruptHistory, 
    description, cidb, cidbValidationTill, trade, valueOfSimilarProject, 
    valueOfCurrentProject, yearsOfExperienceInIndustry, creditFacilitiesStatus, verifierName, verifierDesignation, 
    dateOfVerification, auditorCompanyName, auditorCompanyAddress, auditorName, auditorEmail, 
    auditorPhone, auditorYearOfService, advocatesCompanyName, advocatesCompanyAddress, advocatesName, 
    advocatesEmail, advocatesPhone, advocatesYearOfService, status) 
    VALUES (?,?,?,?,?, 
            ?,?,?,?,?, 
            ?,?,?,?,?, 
            ?,?,?,?,?, 
            ?,?,?,?,?, 
            ?,?,?,?,?, 
            ?,?,?,?,?, 
            ?,?,?,?,?, 
            ?,?,?,?,?, 
            ?,?,?,?)");

    // Correct Bind String: 12 strings, 2 doubles, then strings...
    $bindString = "ssssssssssssddsssssssssssssssssssssssssssisssssis"; 
    
    $stmt->bind_param($bindString, 
    $newCRN, $currentDate, $CompanyName, $tax, $FaxNo, 
    $companyOrganisation, $oldCRN, $OtherName, $telephone, $EmailAddress, 
    $website, $BranchAddress, $AuthorisedCapital, $PaidUpCapital, $CountryOfIncorporation, 
    $DateOfIncorporation, $NatureOfBusiness, $RegisteredAddress, $CorrespondenceAddress, $TypeOfOrganisation, 
    $ParentCompany, $ParentCompanyCountry, $UltimateParentCompany, $UParentCompanyCountry, $bankruptcy, 
    $bankruptcyDescription, $CIDB, $CIDBValidityDate, $CIDBTrade, $ValueOfSimilarProject, 
    $ValueOfCurrentProject, $ExperienceInIndustry, $creditFacilitiesStatus, $name, $DesignationOfWritter, 
    $DateOfWritting, $AuditorCompanyName, $AuditorCompanyAddress, $AuditorPersonName, $AuditorPersonEmail, 
    $AuditorPersonPhone, $AuditorYearOfService, $AdvocatesCompanyName, $AdvocatesCompanyAddress, $AdvocatesPersonName, 
    $AdvocatesPersonEmail, $AdvocatesPersonPhone, $AdvocatesYearOfService, $Status
    );

    if ($stmt->execute()) {
        $registrationFormID = $conn->insert_id;
        echo "<div class='alert alert-success'>Basic Registration Saved ✓</div>";
    } else {
        die("<div class='alert alert-danger'>Error Saving Registration: " . $stmt->error . "</div>");
    }
    $stmt->close();

    // --- Helper for Multiple Rows ---
    function insertRows($conn, $sql, $types, $dataArrays) {
        $stmt = $conn->prepare($sql);
        $count = count($dataArrays[0]);
        for($i=0; $i < $count; $i++) {
            // Check if first field of row is empty to skip blank rows
            if(empty($dataArrays[0][$i])) continue;
            
            $params = [];
            foreach($dataArrays as $arr) {
                $params[] = $arr[$i] ?? null;
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
        }
        $stmt->close();
    }

    // 3. Banks
    insertRows($conn, 
        "INSERT INTO bank (registrationFormID, bankName, bankAddress, swiftCode) VALUES (?,?,?,?)", 
        "isss", 
        [array_fill(0, count($_POST['NameOfBank']??[]), $registrationFormID), $_POST['NameOfBank']??[], $_POST['AddressOfBank']??[], $_POST['SwiftCodeOfBank']??[]]
    );

    // 4. Contacts (Manual handle for Primary/Secondary)
    $cStmt = $conn->prepare("INSERT INTO contacts (registrationFormID, contactPersonName, department, telephoneNumber, emailAddress, contactStatus) VALUES (?,?,?,?,?,?)");
    if(!empty($_POST['PrimaryContactPerson'])) {
        $s="Primary"; $cStmt->bind_param("isssss", $registrationFormID, $_POST['PrimaryContactPerson'], $_POST['PrimaryDepartment'], $_POST['PrimaryTelephone'], $_POST['PrimaryEmail'], $s); $cStmt->execute();
    }
    if(!empty($_POST['SecondaryContactPerson'])) {
        $s="Secondary"; $cStmt->bind_param("isssss", $registrationFormID, $_POST['SecondaryContactPerson'], $_POST['SecondaryDepartment'], $_POST['SecondaryTelephone'], $_POST['SecondaryEmail'], $s); $cStmt->execute();
    }
    $cStmt->close();

    // 5. Shareholders (FIXED: Uses correctly capitalized keys from JS now)
    insertRows($conn,
        "INSERT INTO shareholders (registrationFormID, companyShareholderID, name, nationality, address, sharePercentage) VALUES (?,?,?,?,?,?)",
        "issssd",
        [
            array_fill(0, count($_POST['ShareholderName']??[]), $registrationFormID),
            $_POST['CompanyShareholderID']??[],
            $_POST['ShareholderName']??[],
            $_POST['ShareholderNationality']??[],
            $_POST['ShareholderAddress']??[],
            $_POST['ShareholderPercent']??[]
        ]
    );

    // 6. Project Track Record
    insertRows($conn,
        "INSERT INTO projecttrackrecord (registrationFormID, projectRecordNo, projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate) VALUES (?,?,?,?,?,?,?,?,?)",
        "iisssssss",
        [
            array_fill(0, count($_POST['ProjectTitle']??[]), $registrationFormID),
            $_POST['ProjectRecordNo']??[], $_POST['ProjectTitle']??[], $_POST['ProjectNature']??[], $_POST['ProjectLocation']??[],
            $_POST['ProjectClientName']??[], $_POST['ProjectValue']??[], $_POST['ProjectCommencementDate']??[], $_POST['ProjectCompletionDate']??[]
        ]
    );

    // 7. Current Project
    insertRows($conn,
        "INSERT INTO currentproject (registrationFormID, currentProjectRecordNo, projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate, progressOfTheWork) VALUES (?,?,?,?,?,?,?,?,?,?)",
        "iisssssssd",
        [
            array_fill(0, count($_POST['CurrentProjTitle']??[]), $registrationFormID),
            $_POST['CurrentProjectRecordNo']??[], $_POST['CurrentProjTitle']??[], $_POST['CurrentProjNature']??[], $_POST['CurrentProjLocation']??[],
            $_POST['CurrentProjClientName']??[], $_POST['CurrentProjValue']??[], $_POST['CurrentProjStartDate']??[], $_POST['CurrentProjEndDate']??[], $_POST['CurrentProjProgress']??[]
        ]
    );

    // 8. Staff
    insertRows($conn,
        "INSERT INTO staff (registrationFormID, staffNo, name, designation, qualification, yearsOfExperience, employmentStatus, skills, relevantCertification) VALUES (?,?,?,?,?,?,?,?,?)",
        "iisssisss",
        [
            array_fill(0, count($_POST['StaffName']??[]), $registrationFormID),
            $_POST['StaffNo']??[], $_POST['StaffName']??[], $_POST['StaffDesignation']??[], $_POST['StaffQualification']??[],
            $_POST['StaffExperience']??[], $_POST['StaffEmploymentStatus']??[], $_POST['StaffSkills']??[], $_POST['StaffCertification']??[]
        ]
    );

    // 9. Directors
    insertRows($conn,
        "INSERT INTO directorandsecretary (registrationFormID, nationality, name, position, appointmentDate, dob) VALUES (?,?,?,?,?,?)",
        "isssss",
        [
            array_fill(0, count($_POST['DirectorName']??[]), $registrationFormID),
            $_POST['DirectorNationality']??[], $_POST['DirectorName']??[], $_POST['DirectorPosition']??[],
            $_POST['DirectorAppointmentDate']??[], $_POST['DirectorDOB']??[]
        ]
    );

    // 10. Management
    insertRows($conn,
        "INSERT INTO management (registrationFormID, nationality, name, position, yearsInPosition, yearsInRelatedField) VALUES (?,?,?,?,?,?)",
        "isssii",
        [
            array_fill(0, count($_POST['ManagementName']??[]), $registrationFormID),
            $_POST['ManagementNationality']??[], $_POST['ManagementName']??[], $_POST['ManagementPosition']??[],
            $_POST['ManagementYearInPosition']??[], $_POST['ManagementYearsInIndustry']??[]
        ]
    );

    // 11. Net Worth (Special handling for keys)
    $FinanceStmt = $conn->prepare("INSERT INTO nettworth (registrationFormID, yearOf, totalLiabilities, totalAssets, netWorth, workingCapital) VALUES (?,?,?,?,?,?)");
    $totalLiabilities = $_POST['totalLiabilities'] ?? [];
    foreach ($totalLiabilities as $year => $val) {
        if(empty($val)) continue;
        $v1 = $_POST['totalAssets'][$year] ?? 0;
        $v2 = $_POST['NetWorth'][$year] ?? 0;
        $v3 = $_POST['WorkingCapital'][$year] ?? 0;
        $FinanceStmt->bind_param("isiddd", $registrationFormID, $year, $val, $v1, $v2, $v3);
        $FinanceStmt->execute();
    }
    $FinanceStmt->close();

    // 12. Equipment (Special handling)
    $EquipmentStmt = $conn->prepare("INSERT INTO equipment (registrationFormID, equipmentID, quantity, brand, rating, ownership, yearsOfManufacture, registrationNo) VALUES (?,?,?,?,?,?,?,?)");
    $eqList = [
        1 => ['BobcatQuantity','BobcatBrandModel','BobcatRating','BobcatOwnership','BobcatYearOfManufacture','BobcatRegistrationNo'],
        2 => ['HDDQuantity','HDDBrandModel','HDDRating','HDDOwnership','HDDYearOfManufacture','HDDRegistrationNo'],
        3 => ['SplicingQuantity','SplicingBrandModel','SplicingRating','SplicingOwnership','SplicingYearOfManufacture','SplicingRegistrationNo'],
        4 => ['OPMQuantity','OPMBrandModel','OPMRating','OPMOwnership','OPMYearOfManufacture','OPMRegistrationNo'],
        5 => ['OTDRQuantity','OTDRBrandModel','OTDRRating','OTDROwnership','OTDRYearOfManufacture','OTDRRegistrationNo'],
        6 => ['TestGearQuantity','TestGearBrandModel','TestGearRating','TestGearOwnership','TestGearYearOfManufacture','TestGearRegistrationNo']
    ];
    foreach($eqList as $id => $fields) {
        $q = $_POST[$fields[0]] ?? '';
        if($q === '') continue; 
        $EquipmentStmt->bind_param("iiisssss", $registrationFormID, $id, 
            $q, $_POST[$fields[1]], $_POST[$fields[2]], $_POST[$fields[3]], $_POST[$fields[4]], $_POST[$fields[5]]
        );
        $EquipmentStmt->execute();
    }
    $EquipmentStmt->close();

    // 13. Credit Facilities
    insertRows($conn,
        "INSERT INTO creditfacilities (registrationFormID, typeOfCreditFacilities, financialInstitution, totalAmount, expiryDate, unutilisedAmountCurrentlyAvailable, asAtDate) VALUES (?,?,?,?,?,?,?)",
        "issdsds",
        [
            array_fill(0, count($_POST['TypeOfCredit']??[]), $registrationFormID),
            $_POST['TypeOfCredit']??[], $_POST['FinancialInstitution']??[], $_POST['CreditTotalAmount']??[],
            $_POST['CreditExpiryDate']??[], $_POST['CreditUnutilisedAmount']??[], $_POST['CreditAsAtDate']??[]
        ]
    );

    $conn->close();
?>
<div class="alert alert-success mt-4">All Data Inserted! Redirecting...</div>
<script>
    setTimeout(function() { window.location.href = 'VendorHomepage.php'; }, 2000);
</script>
</div>
</body>
</html>