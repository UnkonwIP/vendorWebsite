<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
    $conn = new mysqli('localhost', 'root', '', 'vendor_information');
    if ($conn->connect_error) {
        die('Connection Failed : ' . $conn->connect_error);
    }

    // --- 1. Basic Variables ---
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
    $AuthorisedCapital = $_POST['AuthorisedCapital'] ?? '';
    $PaidUpCapital = $_POST['PaidUpCapital'] ?? '';
    $CountryOfIncorporation = $_POST['CountryOfIncorporation'] ?? '';
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
    $ExperienceInIndustry = $_POST['ExperienceInIndustry'] ?? '';
    $creditFacilitiesStatus = $_POST['CreditFacilitiesStatus'] ?? '';
    $name = $_POST['NameOfWritter'] ?? '';
    $DesignationOfWritter = $_POST['DesignationOfWritter'] ?? '';
    $DateOfWritting = $_POST['DateOfWritting'] ?? '';
    $AuditorCompanyName = $_POST['AuditorCompanyName'] ?? '';
    $AuditorCompanyAddress = $_POST['AuditorCompanyAddress'] ?? '';
    $AuditorPersonName = $_POST['AuditorPersonName'] ?? '';
    $AuditorPersonEmail = $_POST['AuditorPersonEmail'] ?? '';
    $AuditorPersonPhone = $_POST['AuditorPersonPhone'] ?? '';
    $AdvocatesCompanyName = $_POST['AdvocatesCompanyName'] ?? '';
    $AdvocatesCompanyAddress = $_POST['AdvocatesCompanyAddress'] ?? '';
    $AdvocatesPersonName = $_POST['AdvocatesPersonName'] ?? '';
    $AdvocatesPersonEmail = $_POST['AdvocatesPersonEmail'] ?? '';
    $AdvocatesPersonPhone = $_POST['AdvocatesPersonPhone'] ?? '';
    $AuditorYearOfService = $_POST['AuditorYearOfService'] ?? '';
    $AdvocatesYearOfService = $_POST['AdvocatesYearOfService'] ?? '';
    $Status = "pending";

    // --- 2. Insert Registration Form ---
    $stmt = $conn->prepare("INSERT INTO registrationform (
    newCompanyRegistrationNumber,   
    formFirstSubmissionDate, 
    companyName, 
    taxRegistrationNumber, 
    faxNo, 
    companyOrganisation, 
    oldCompanyRegistrationNumber, 
    otherNames, 
    telephoneNumber, 
    emailAddress, 
    website, 
    branch, 
    authorisedCapital, 
    paidUpCapital, 
    countryOfIncorporation, 
    dateOfIncorporation, 
    natureAndLineOfBusiness, 
    registeredAddress, 
    correspondenceAddress, 
    typeOfOrganisation, 
    parentCompany, 
    parentCompanyCountry, 
    ultimateParentCompany, 
    ultimateParentCompanyCountry, 
    bankruptHistory, 
    description, 
    cidb, 
    cidbValidationTill, 
    trade, 
    valueOfSimilarProject, 
    valueOfCurrentProject,
    yearsOfExperienceInIndustry, 
    creditFacilitiesStatus,
    verifierName, 
    verifierDesignation, 
    dateOfVerification,
    auditorCompanyName,
    auditorCompanyAddress, 
    auditorName, 
    auditorEmail, 
    auditorPhone,
    auditorYearOfService, 
    advocatesCompanyName, 
    advocatesCompanyAddress, 
    advocatesName, 
    advocatesEmail, 
    advocatesPhone,  
    advocatesYearOfService, 
    status) 
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

    $stmt->bind_param("sssssssssssssddssssssssssssssssisssssssssisssssis", 
    $newCRN,                    //s
    $currentDate,               //s
    $CompanyName,               //s
    $tax,                       //s   
    $FaxNo,                     //s
    $companyOrganisation,       //s

    $oldCRN,                    //s
    $OtherName,                 //s    
    $telephone,                 //s
    $EmailAddress,              //s
    $website,                   //s
    
    $BranchAddress,             //s
    $AuthorisedCapital,         //d
    $PaidUpCapital,             //d
    $CountryOfIncorporation,    //s
    $DateOfIncorporation,       //s

    $NatureOfBusiness,          //s
    $RegisteredAddress,         //s
    $CorrespondenceAddress,     //s
    $TypeOfOrganisation,        //s
    $ParentCompany,             //s

    $ParentCompanyCountry,      //s
    $UltimateParentCompany,     //s
    $UParentCompanyCountry,     //s
    $bankruptcy,                //s
    $bankruptcyDescription,     //s

    $CIDB,                      //s
    $CIDBValidityDate,          //s
    $CIDBTrade,                 //s
    $ValueOfSimilarProject,     //s
    $ValueOfCurrentProject,     //s

    $ExperienceInIndustry,      //i
    $creditFacilitiesStatus,    //s
    $name,                      //s
    $DesignationOfWritter,      //s
    $DateOfWritting,            //s

    $AuditorCompanyName,        //s
    $AuditorCompanyAddress,     //s
    $AuditorPersonName,         //s
    $AuditorPersonEmail,        //s
    $AuditorPersonPhone,        //s

    $AuditorYearOfService,      //i
    $AdvocatesCompanyName,      //s
    $AdvocatesCompanyAddress,   //s
    $AdvocatesPersonName,       //s
    $AdvocatesPersonEmail,      //s

    $AdvocatesPersonPhone,      //s
    $AdvocatesYearOfService,    //i
    $Status                     //s    
    );

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Registration inserted successfully ✓</div>";
    } else {
        echo "<div class='alert alert-danger'>Insert failed: " . htmlspecialchars($stmt->error) . " ✗</div>";
    }
    $stmt->close();

    // Get the registrationFormID of the newly inserted registrationform row
    $registrationFormID = $conn->insert_id;

    // --- 3. Banks ---
    $bankNames = $_POST['NameOfBank'] ?? [];
    $AddressOfBank = $_POST['AddressOfBank'] ?? [];
    $SwiftCodeOfBank = $_POST['SwiftCodeOfBank'] ?? [];
    $BankStmt = $conn->prepare("INSERT INTO bank (registrationFormID, bankName, bankAddress, swiftCode) VALUES (?,?,?,?)");
    for ($i = 0; $i < count($bankNames); $i++) {
        if (empty($bankNames[$i])) continue;
        $BankStmt->bind_param("isss", $registrationFormID, $bankNames[$i], $AddressOfBank[$i], $SwiftCodeOfBank[$i]);
        $BankStmt->execute();
    }
    $BankStmt->close();

    // --- 4. Contacts ---
    $PrimaryContactPerson = $_POST['PrimaryContactPerson'] ?? '';
    if (!empty($PrimaryContactPerson)) {
        $cStmt = $conn->prepare("INSERT INTO contacts (registrationFormID, contactPersonName, department, telephoneNumber, emailAddress, contactStatus) VALUES (?,?,?,?,?,?)");
        $status = 'Primary';
        $cStmt->bind_param("isssss", $registrationFormID, $PrimaryContactPerson, $_POST['PrimaryDepartment'], $_POST['PrimaryTelephone'], $_POST['PrimaryEmail'], $status);
        $cStmt->execute();

        $status = 'Secondary';
        $cStmt->bind_param("isssss", $registrationFormID, $_POST['SecondaryContactPerson'], $_POST['SecondaryDepartment'], $_POST['SecondaryTelephone'], $_POST['SecondaryEmail'], $status);
        $cStmt->execute();
        $cStmt->close();
    }

    // --- 5. Credit Facilities ---
    $TypeOfCredit = $_POST['TypeOfCredit'] ?? [];
    $FinancialInstitution = $_POST['FinancialInstitution'] ?? [];
    $CreditTotalAmount = $_POST['CreditTotalAmount'] ?? [];
    $CreditExpiryDate = $_POST['CreditExpiryDate'] ?? [];
    $CreditUnutilisedAmount = $_POST['CreditUnutilisedAmount'] ?? [];
    $CreditAsAtDate = $_POST['CreditAsAtDate'] ?? [];
    $CreditStmt = $conn->prepare("INSERT INTO creditfacilities (registrationFormID, typeOfCreditFacilities, financialInstitution, totalAmount, expiryDate, unutilisedAmountCurrentlyAvailable, asAtDate) VALUES (?,?,?,?,?,?,?)");
    for ($i = 0; $i < count($TypeOfCredit); $i++) {
        if (empty($TypeOfCredit[$i])) continue;
        $CreditStmt->bind_param("issdsds", $registrationFormID, $TypeOfCredit[$i], $FinancialInstitution[$i], $CreditTotalAmount[$i], $CreditExpiryDate[$i], $CreditUnutilisedAmount[$i], $CreditAsAtDate[$i]);
        $CreditStmt->execute();
    }
    $CreditStmt->close();

    // --- 6. Equipment (FIXED TYPOS HERE) ---
    $EquipmentStmt = $conn->prepare("INSERT INTO equipment (registrationFormID, equipmentID, quantity, brand, rating, ownership, yearsOfManufacture, registrationNo) VALUES (?,?,?,?,?,?,?,?)");
    $equipmentData = [
        ['equipmentID' => 1, 'quantity' => $_POST['BobcatQuantity'] ?? '', 'brand' => $_POST['BobcatBrandModel'] ?? '', 'rating' => $_POST['BobcatRating'] ?? '', 'ownership' => $_POST['BobcatOwnership'] ?? '', 'yearsOfManufacture' => $_POST['BobcatYearOfManufacture'] ?? '', 'registrationNo' => $_POST['BobcatRegistrationNo'] ?? ''],
        ['equipmentID' => 2, 'quantity' => $_POST['HDDQuantity'] ?? '', 'brand' => $_POST['HDDBrandModel'] ?? '', 'rating' => $_POST['HDDRating'] ?? '', 'ownership' => $_POST['HDDOwnership'] ?? '', 'yearsOfManufacture' => $_POST['HDDYearOfManufacture'] ?? '', 'registrationNo' => $_POST['HDDRegistrationNo'] ?? ''],
        ['equipmentID' => 3, 'quantity' => $_POST['SplicingQuantity'] ?? '', 'brand' => $_POST['SplicingBrandModel'] ?? '', 'rating' => $_POST['SplicingRating'] ?? '', 'ownership' => $_POST['SplicingOwnership'] ?? '', 'yearsOfManufacture' => $_POST['SplicingYearOfManufacture'] ?? '', 'registrationNo' => $_POST['SplicingRegistrationNo'] ?? ''],
        ['equipmentID' => 4, 'quantity' => $_POST['OPMQuantity'] ?? '', 'brand' => $_POST['OPMBrandModel'] ?? '', 'rating' => $_POST['OPMRating'] ?? '', 'ownership' => $_POST['OPMOwnership'] ?? '', 'yearsOfManufacture' => $_POST['OPMYearOfManufacture'] ?? '', 'registrationNo' => $_POST['OPMRegistrationNo'] ?? ''],
        ['equipmentID' => 5, 'quantity' => $_POST['OTDRQuantity'] ?? '', 'brand' => $_POST['OTDRBrandModel'] ?? '', 'rating' => $_POST['OTDRRating'] ?? '', 'ownership' => $_POST['OTDROwnership'] ?? '', 'yearsOfManufacture' => $_POST['OTDRYearOfManufacture'] ?? '', 'registrationNo' => $_POST['OTDRRegistrationNo'] ?? ''],
        ['equipmentID' => 6, 'quantity' => $_POST['TestGearQuantity'] ?? '', 'brand' => $_POST['TestGearBrandModel'] ?? '', 'rating' => $_POST['TestGearRating'] ?? '', 'ownership' => $_POST['TestGearOwnership'] ?? '', 'yearsOfManufacture' => $_POST['TestGearYearOfManufacture'] ?? '', 'registrationNo' => $_POST['TestGearRegistrationNo'] ?? '']
    ];
    foreach ($equipmentData as $eq) {
        if (empty($eq['quantity']) && empty($eq['brand'])) continue;
        $EquipmentStmt->bind_param("iiisssss", $registrationFormID, $eq['equipmentID'], $eq['quantity'], $eq['brand'], $eq['rating'], $eq['ownership'], $eq['yearsOfManufacture'], $eq['registrationNo']);
        $EquipmentStmt->execute();
    }
    $EquipmentStmt->close();

    // --- 7. Net Worth ---
    $totalLiabilities = $_POST['totalLiabilities'] ?? [];
    $totalAssets = $_POST['totalAssets'] ?? [];
    $NetWorth = $_POST['NetWorth'] ?? [];
    $WorkingCapital = $_POST['WorkingCapital'] ?? [];
    $FinanceStmt = $conn->prepare("INSERT INTO nettworth (registrationFormID, yearOf, totalLiabilities, totalAssets, netWorth, workingCapital) VALUES (?,?,?,?,?,?)");
    foreach ($totalLiabilities as $year => $liability) {
        if (empty($liability)) continue;
        $v1 = $totalAssets[$year] ?? 0;
        $v2 = $NetWorth[$year] ?? 0;
        $v3 = $WorkingCapital[$year] ?? 0;
        $FinanceStmt->bind_param("isiddd", $registrationFormID, $year, $liability, $v1, $v2, $v3);
        $FinanceStmt->execute();
    }
    $FinanceStmt->close();

    // --- 8. Shareholders ---
    $ShareholderName = $_POST['ShareholderName'] ?? [];
    $ShareholderPercent = $_POST['ShareholderPercent'] ?? [];
    $ShareholderStmt = $conn->prepare("INSERT INTO shareholders (registrationFormID, companyShareholderID, name, nationality, address, sharePercentage) VALUES (?,?,?,?,?,?)");
    for ($i = 0; $i < count($ShareholderName); $i++) {
        if (empty($ShareholderName[$i])) continue;
        $ShareholderStmt->bind_param("issssd", $registrationFormID, $_POST['CompanyShareholderID'][$i], $ShareholderName[$i], $_POST['ShareholderNationality'][$i], $_POST['ShareholderAddress'][$i], $ShareholderPercent[$i]);
        $ShareholderStmt->execute();
    }
    $ShareholderStmt->close();

    // Current Project record (normalized schema)
    $CurrentProjectRecordNo = $_POST['CurrentProjectRecordNo'] ?? [];
    $CurrentProjTitle = $_POST['CurrentProjTitle'] ?? [];
    $CurrentProjNature = $_POST['CurrentProjNature'] ?? [];
    $CurrentProjLocation = $_POST['CurrentProjLocation'] ?? [];
    $CurrentProjClientName = $_POST['CurrentProjClientName'] ?? [];
    $CurrentProjValue = $_POST['CurrentProjValue'] ?? [];
    $CurrentProjStartDate = $_POST['CurrentProjStartDate'] ?? [];
    $CurrentProjEndDate = $_POST['CurrentProjEndDate'] ?? [];
    $CurrentProjProgress = $_POST['CurrentProjProgress'] ?? [];

    $ProjectStmt = $conn->prepare("
        INSERT INTO currentproject (
            registrationFormID,
            currentProjectRecordNo,
            projectTitle,
            projectNature,
            location,
            clientName,
            projectValue,
            commencementDate,
            completionDate,
            progressOfTheWork
        ) VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    for ($i = 0; $i < count($CurrentProjectRecordNo); $i++) {
        // Skip empty rows
        if (
            empty($CurrentProjectRecordNo[$i]) &&
            empty($CurrentProjTitle[$i]) &&
            empty($CurrentProjNature[$i])
        ) {
            continue;
        }

        $ProjectStmt->bind_param(
            "iissssddds",
            $registrationFormID,            // i
            $CurrentProjectRecordNo[$i],    // i
            $CurrentProjTitle[$i],          // s
            $CurrentProjNature[$i],         // s
            $CurrentProjLocation[$i],       // s
            $CurrentProjClientName[$i],     // s
            $CurrentProjValue[$i],          // d
            $CurrentProjStartDate[$i],      // s (date)
            $CurrentProjEndDate[$i],        // s (date)
            $CurrentProjProgress[$i]        // d (decimal)
        );

        if ($ProjectStmt->execute()) {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-success">
                    Current Project statement inserted successfully
                </span>
                <span class="input-group-text text-success">✓</span>
            </div>
            <?php
        } else {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-danger">
                    Insert failed: <?= htmlspecialchars($ProjectStmt->error) ?>
                </span>
                <span class="input-group-text text-danger">✗</span>
            </div>
            <?php
        }
    }
    $ProjectStmt->close();

    // director and secretary
    $DirectorName = $_POST['DirectorName'] ?? [];
    $DirectorNationality = $_POST['DirectorNationality'] ?? [];
    $DirectorPosition = $_POST['DirectorPosition'] ?? [];
    $DirectorAppointmentDate = $_POST['DirectorAppointmentDate'] ?? [];
    $DirectorDOB = $_POST['DirectorDOB'] ?? [];

    $DirectorStmt = $conn->prepare("
        INSERT INTO directorandsecretary (
            registrationFormID,
            nationality,
            name,
            position,
            appointmentDate,
            dob
        ) VALUES (?,?,?,?,?,?)
    ");

    for ($i = 0; $i < count($DirectorName); $i++) {
        // Skip empty rows
        if (
            empty($DirectorName[$i]) &&
            empty($DirectorNationality[$i]) &&
            empty($DirectorPosition[$i])
        ) {
            continue;
        }

        $DirectorStmt->bind_param(
            "isssss",
            $registrationFormID,             // i
            $DirectorNationality[$i],        // s
            $DirectorName[$i],               // s
            $DirectorPosition[$i],           // s
            $DirectorAppointmentDate[$i],    // s
            $DirectorDOB[$i]                 // s
        );

        if ($DirectorStmt->execute()) {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-success">
                    Director data inserted successfully
                </span>
                <span class="input-group-text text-success">✓</span>
            </div>
            <?php
        } else {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-danger">
                    Insert failed: <?= htmlspecialchars($DirectorStmt->error) ?>
                </span>
                <span class="input-group-text text-danger">✗</span>
            </div>
            <?php
        }
    }
    $DirectorStmt->close();

    // Management
    $ManagementName = $_POST['ManagementName'] ?? [];
    $ManagementNationality = $_POST['ManagementNationality'] ?? [];
    $ManagementPosition = $_POST['ManagementPosition'] ?? [];
    $ManagementYearsInPosition = $_POST['ManagementYearInPosition'] ?? [];
    $ManagementYearsInIndustry = $_POST['ManagementYearsInIndustry'] ?? [];

    $ManagementStmt = $conn->prepare("
        INSERT INTO management (
            registrationFormID,
            nationality,
            name,
            position,
            yearsInPosition,
            yearsInRelatedField
        ) VALUES (?,?,?,?,?,?)
    ");

    for ($i = 0; $i < count($ManagementName); $i++) {
        // Skip empty rows
        if (
            empty($ManagementName[$i]) &&
            empty($ManagementNationality[$i]) &&
            empty($ManagementPosition[$i])
        ) {
            continue;
        }

        $ManagementStmt->bind_param(
            "isssii",
            $registrationFormID,               // i
            $ManagementNationality[$i],        // s
            $ManagementName[$i],               // s
            $ManagementPosition[$i],           // s
            $ManagementYearsInPosition[$i],    // i
            $ManagementYearsInIndustry[$i]     // i
        );

        if ($ManagementStmt->execute()) {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-success">
                    Management data inserted successfully
                </span>
                <span class="input-group-text text-success">✓</span>
            </div>
            <?php
        } else {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-danger">
                    Insert failed: <?= htmlspecialchars($ManagementStmt->error) ?>
                </span>
                <span class="input-group-text text-danger">✗</span>
            </div>
            <?php
        }
    }
    $ManagementStmt->close();

    // Past project tracking
    $ProjectRecordNo = $_POST['ProjectRecordNo'] ?? [];
    $ProjectTitle = $_POST['ProjectTitle'] ?? [];
    $ProjectNature = $_POST['ProjectNature'] ?? [];
    $ProjectLocation = $_POST['ProjectLocation'] ?? [];
    $ProjectClientName = $_POST['ProjectClientName'] ?? [];
    $ProjectValue = $_POST['ProjectValue'] ?? [];
    $ProjectCommencement = $_POST['ProjectCommencementDate'] ?? [];
    $ProjectCompletion = $_POST['ProjectCompletionDate'] ?? [];

    $ProjectStmt = $conn->prepare("
        INSERT INTO projecttrackrecord (
            registrationFormID,
            projectRecordNo,
            projectTitle,
            projectNature,
            location,
            clientName,
            projectValue,
            commencementDate,
            completionDate
        ) VALUES (?,?,?,?,?,?,?,?,?)
    ");

    for ($i = 0; $i < count($ProjectRecordNo); $i++) {
        // Skip empty rows
        if (
            empty($ProjectTitle[$i]) &&
            empty($ProjectNature[$i]) &&
            empty($ProjectLocation[$i])
        ) {
            continue;
        }

        $ProjectStmt->bind_param(
            "iisssssdd",
            $registrationFormID,        // i
            $ProjectRecordNo[$i],       // i
            $ProjectTitle[$i],          // s
            $ProjectNature[$i],         // s
            $ProjectLocation[$i],       // s
            $ProjectClientName[$i],     // s
            $ProjectValue[$i],          // d
            $ProjectCommencement[$i],   // s (DATE)
            $ProjectCompletion[$i]      // s (DATE)
        );

        if ($ProjectStmt->execute()) {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-success">
                    Project record data inserted successfully
                </span>
                <span class="input-group-text text-success">✓</span>
            </div>
            <?php
        } else {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-danger">
                    Insert failed: <?= htmlspecialchars($ProjectStmt->error) ?>
                </span>
                <span class="input-group-text text-danger">✗</span>
            </div>
            <?php
        }
    }
    $ProjectStmt->close();

    // on site staff
    $StaffNo = $_POST['StaffNo'] ?? [];
    $StaffName = $_POST['StaffName'] ?? [];
    $StaffDesignation = $_POST['StaffDesignation'] ?? [];
    $StaffQualification = $_POST['StaffQualification'] ?? [];
    $StaffExperience = $_POST['StaffExperience'] ?? [];
    $StaffEmployment = $_POST['StaffEmploymentStatus'] ?? [];
    $StaffSkills = $_POST['StaffSkills'] ?? [];
    $StaffCertification = $_POST['StaffCertification'] ?? [];

    $StaffStmt = $conn->prepare("
        INSERT INTO staff (
            registrationFormID,
            staffNo,
            name,
            designation,
            qualification,
            yearsOfExperience,
            employmentStatus,
            skills,
            relevantCertification
        ) VALUES (?,?,?,?,?,?,?,?,?)
    ");

    for ($i = 0; $i < count($StaffNo); $i++) {
        // Skip empty rows
        if (
            empty($StaffName[$i]) &&
            empty($StaffDesignation[$i]) &&
            empty($StaffQualification[$i])
        ) {
            continue;
        }

        $StaffStmt->bind_param(
            "iisssisss",
            $registrationFormID,        // i
            $StaffNo[$i],               // i
            $StaffName[$i],             // s
            $StaffDesignation[$i],      // s
            $StaffQualification[$i],    // s
            $StaffExperience[$i],       // i
            $StaffEmployment[$i],       // s
            $StaffSkills[$i],           // s
            $StaffCertification[$i]     // s
        );

        if ($StaffStmt->execute()) {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-success">
                    Staff team data inserted successfully
                </span>
                <span class="input-group-text text-success">✓</span>
            </div>
            <?php
        } else {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-danger">
                    Insert failed: <?= htmlspecialchars($StaffStmt->error) ?>
                </span>
                <span class="input-group-text text-danger">✗</span>
            </div>
            <?php
        }
    }
    $StaffStmt->close();

    $conn->close();
?>
<div class="alert alert-success mt-4">All Data Inserted! Redirecting...</div>
<script>
    setTimeout(function() {
        window.location.href = 'VendorHomepage.php';
    }, 3000);
</script>
</body>
</html>