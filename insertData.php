<!DOCTYPE html>
<html>
<head></head>
<body>
    <!-- css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendorStyle.css">
<?php

    // connect to the database
    $conn = new mysqli('localhost', 'root', '', 'vendor_information');


//primarykey
$newCRN = $_POST['newCRN'] ?? '';
$currentDate = date('Y-m-d');  // this is the current date not year
//normal data
$CompanyName = $_POST['CompanyName'] ?? '';
$tax = $_POST['tax'] ?? '';
$FaxNo = $_POST['FaxNo'] ?? '';

//conpanyOrganisation radio button
$companyOrganisation = $_POST['CompanyOrganisation'] ?? '';
    
$oldCRN = $_POST['oldCRN'] ?? '';
$OtherName = $_POST['OtherName'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$EmailAddress = $_POST['EmailAddress'] ?? '';
$website = $_POST['Website'] ?? '';
$BranchAddress = $_POST['BranchAddress'] ?? '';
$AuthorisedCapital =$_POST['AuthorisedCapital'] ?? '';
$PaidUpCapital =$_POST['PaidUpCapital'] ?? '';
$CountryOfIncorporation =$_POST['CountryOfIncorporation'] ?? '';
$DateOfIncorporation =$_POST['DateOfIncorporation'] ?? '';
$NatureOfBusiness =$_POST['NatureOfBusiness'] ?? '';
$RegisteredAddress =$_POST['RegisteredAddress'] ?? '';
$CorrespondenceAddress =$_POST['CorrespondenceAddress'] ?? '';
$TypeOfOrganisation =$_POST['TypeOfOrganisation'] ?? '';
$ParentCompany =$_POST['ParentCompany'] ?? '';
$ParentCompanyCountry =$_POST['ParentCompanyCountry'] ?? '';
$UltimateParentCompany =$_POST['UltimateParentCompany'] ?? '';
$UParentCompanyCountry =$_POST['UParentCompanyCountry'] ?? '';

//bankrupt / bankrupt description
$bankruptcy =$_POST['bankruptcy'] ?? '';
$bankruptcyDescription = $_POST['bankruptcy-details'] ?? '';

$CIDB =$_POST['CIDB'] ?? '';
$CIDBValidityDate =$_POST['CIDBValidityDate'] ?? '';
$CIDBTrade =$_POST['CIDBTrade'] ?? '';
$ValueOfSimilarProject =$_POST['ValueOfSimilarProject'] ?? '';
$ValueOfCurrentProject =$_POST['ValueOfCurrentProject'] ?? '';

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

//status
$Status = "pending";
        
    if ($conn->connect_error) {
        die('Connection Failed : ' . $conn->connect_error);
    }

    echo "<!-- Form submitted to insertData.php -->\n";

$stmt = $conn->prepare("
    INSERT INTO registrationform (
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
        verifierName,
        verifierDesignation,
        dateOfVerification,
        auditorCompanyName,
        auditorCompanyAddress,
        auditorName,
        auditorEmail,
        auditorPhone,
        advocatesCompanyName,
        advocatesCompanyAddress,
        advocatesName,
        advocatesEmail,
        advocatesPhone,
        auditorYearOfService,
        advocatesYearOfService,
        status
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");


$stmt->bind_param(
    "ssssssssssssddsssssssssssssssddssssssssisssssis",
    $newCRN,                  // s: newCRN
    $currentDate,             // s: currentDate
    $CompanyName,             // s: CompanyName
    $tax,                     // s: tax
    $FaxNo,                   // s: FaxNo

    $companyOrganisation,     // s: companyOrganisation
    $oldCRN,                  // s: oldCRN
    $OtherName,               // s: OtherName
    $telephone,               // s: telephone
    $EmailAddress,            // s: EmailAddress

    $website,                 // s: website
    $BranchAddress,           // s: BranchAddress
    $AuthorisedCapital,       // d: AuthorisedCapital
    $PaidUpCapital,           // d: PaidUpCapital
    $CountryOfIncorporation,  // s: CountryOfIncorporation
    
    $DateOfIncorporation,     // s: DateOfIncorporation
    $NatureOfBusiness,        // s: NatureOfBusiness
    $RegisteredAddress,       // s: RegisteredAddress
    $CorrespondenceAddress,   // s: CorrespondenceAddress
    $TypeOfOrganisation,      // s: TypeOfOrganisation
    
    $ParentCompany,           // s: ParentCompany
    $ParentCompanyCountry,    // s: ParentCompanyCountry
    $UltimateParentCompany,   // s: UltimateParentCompany
    $UParentCompanyCountry,   // s: UParentCompanyCountry
    $bankruptcy,              // s: bankruptcy
    
    $bankruptcyDescription,   // s: bankruptcyDescription
    $CIDB,                    // s: CIDB
    $CIDBValidityDate,        // s: CIDBValidityDate
    $CIDBTrade,               // s: CIDBTrade
    $ValueOfSimilarProject,   // d: ValueOfSimilarProject
    
    $ValueOfCurrentProject,   // d: ValueOfCurrentProject
    $name,                    // s: name
    $DesignationOfWritter,    // s: DesignationOfWritter
    $DateOfWritting,          // s: DateOfWritting
    $AuditorCompanyName,      // s: AuditorCompanyName
    
    $AuditorCompanyAddress,   // s: AuditorCompanyAddress
    $AuditorPersonName,       // s: AuditorPersonName
    $AuditorPersonEmail,      // s: AuditorPersonEmail
    $AuditorPersonPhone,      // s: AuditorPersonPhone
    $AuditorYearOfService,    // i: AuditorYearOfService
    
    $AdvocatesCompanyName,    // s: AdvocatesCompanyName
    $AdvocatesCompanyAddress, // s: AdvocatesCompanyAddress
    $AdvocatesPersonName,     // s: AdvocatesPersonName
    $AdvocatesPersonEmail,    // s: AdvocatesPersonEmail
    $AdvocatesPersonPhone,    // s: AdvocatesPersonPhone

    $AdvocatesYearOfService,  // i: AdvocatesYearOfService
    $Status                   // s: Status
);


    if ($stmt->execute()) {
        ?>
        <div class="input-group mb-3">
            <span class="form-control text-success">
                Registration inserted successfully
            </span>
            <span class="input-group-text text-success">✓</span>
        </div>
        <?php
    } else {
        ?>
        <div class="input-group mb-3">
            <span class="form-control text-danger">
                Insert failed: <?= htmlspecialchars($stmt->error) ?>
            </span>
            <span class="input-group-text text-danger">✗</span>
        </div>
        <?php
    }

$stmt->close();

// Get the registrationFormID of the newly inserted registrationform row
$registrationFormID = $conn->insert_id;


//inserting into bank table
$bankNames = $_POST['NameOfBank'] ?? [];
$AddressOfBank = $_POST['AddressOfBank'] ?? [];
$SwiftCodeOfBank = $_POST['SwiftCodeOfBank'] ?? [];

$BankStmt = $conn->prepare("INSERT INTO bank (registrationFormID,
bankName,
bankAddress,
swiftCode
) VALUES (?,?,?,?)");

    for ($i = 0; $i < count($bankNames); $i++) {
        // skip empty
        if (
            empty($bankNames[$i]) &&
            empty($AddressOfBank[$i]) &&
            empty($SwiftCodeOfBank[$i])
        ) {
            continue;
        }
        $BankStmt->bind_param(
            "isss",
            $registrationFormID,
            $bankNames[$i],
            $AddressOfBank[$i],
            $SwiftCodeOfBank[$i]
        );
        if ($BankStmt->execute()) {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-success">
                    Bank Data inserted successfully
                </span>
                <span class="input-group-text text-success">✓</span>
            </div>
            <?php
        } else {
            ?>
            <div class="input-group mb-3">
                <span class="form-control text-danger">
                    Insert failed: <?= htmlspecialchars($BankStmt->error) ?>
                </span>
                <span class="input-group-text text-danger">✗</span>
            </div>
            <?php
        }
    }

$BankStmt->close();


//contacts
//primary
$PrimaryContactPerson = $_POST['PrimaryContactPerson'] ?? '';
$PrimaryDepartment = $_POST['PrimaryDepartment'] ?? '';
$PrimaryTelephone = $_POST['PrimaryTelephone'] ?? '';
$PrimaryEmail = $_POST['PrimaryEmail'] ?? '';
$PrimaryStatus = 'Primary';

//secondary
$SecondaryContactPerson = $_POST['SecondaryContactPerson'] ?? '';
$SecondaryDepartment = $_POST['SecondaryDepartment'] ?? '';
$SecondaryTelephone = $_POST['SecondaryTelephone'] ?? '';
$SecondaryEmail = $_POST['SecondaryEmail'] ?? '';
$SecondaryStatus = 'Secondary';

$ContactStmt = $conn->prepare("INSERT INTO contacts (registrationFormID,
contactPersonName,
department,
telephoneNumber,
emailAddress,
contactStatus
) VALUES (?,?,?,?,?,?)");

    // Primary contact 
    $ContactStmt->bind_param(
        "isssss",
        $registrationFormID,
        $PrimaryContactPerson,
        $PrimaryDepartment,
        $PrimaryTelephone,
        $PrimaryEmail,
        $PrimaryStatus
    );
    if ($ContactStmt->execute()) {
        ?>
        <div class="input-group mb-3">
            <span class="form-control text-success">
                Primary Contact Data inserted successfully
            </span>
            <span class="input-group-text text-success">✓</span>
        </div>
        <?php
    } else {
        ?>
        <div class="input-group mb-3">
            <span class="form-control text-danger">
                Insert failed: <?= htmlspecialchars($ContactStmt->error) ?>
            </span>
            <span class="input-group-text text-danger">✗</span>
        </div>
        <?php
    }
    // Secondary contact
    $ContactStmt->bind_param(
        "isssss",
        $registrationFormID,
        $SecondaryContactPerson,
        $SecondaryDepartment,
        $SecondaryTelephone,
        $SecondaryEmail,
        $SecondaryStatus
    );
    if ($ContactStmt->execute()) {
        ?>
        <div class="input-group mb-3">
            <span class="form-control text-success">
                Secondary Contact Data inserted successfully
            </span>
            <span class="input-group-text text-success">✓</span>
        </div>
        <?php
    } else {
        ?>
        <div class="input-group mb-3">
            <span class="form-control text-danger">
                Insert failed: <?= htmlspecialchars($ContactStmt->error) ?>
            </span>
            <span class="input-group-text text-danger">✗</span>
        </div>
        <?php
    }

$ContactStmt->close();

//Credit Facilities
$TypeOfCredit = $_POST['TypeOfCredit'] ?? [];
$FinancialInstitution = $_POST['FinancialInstitution'] ?? [];
$CreditTotalAmount = $_POST['CreditTotalAmount'] ?? [];
$CreditUnutilisedAmount = $_POST['CreditUnutilisedAmount'] ?? [];
$CreditExpiryDate = $_POST['CreditExpiryDate'] ?? [];
$CreditAsAtDate = $_POST['CreditAsAtDate'] ?? [];

$CreditStmt = $conn->prepare("INSERT INTO Creditfacilities (
registrationFormID,
typeOfCreditFacilities,
financialInstitution,
totalAmount,
expirydate,
unutilisedAmountCurrentlyAvailable,
asAtDate
) VALUES (?,?,?,?,?,?,?)");

for ($i = 0; $i < count($TypeOfCredit); $i++){
    //skip empty
    if(
        empty($TypeOfCredit[$i]) &&
        empty($FinancialInstitution[$i]) &&
        empty($CreditTotalAmount[$i]) &&
        empty($CreditUnutilisedAmount[$i]) &&
        empty($CreditExpiryDate[$i]) &&
        empty($CreditAsAtDate[$i])
    ){
        continue;
    }
    
    $CreditStmt->bind_param(
        "issdsds",
        $registrationFormID,        //i
        $TypeOfCredit[$i],          //s
        $FinancialInstitution[$i],  //s
        $CreditTotalAmount[$i],     //d
        $CreditExpiryDate[$i],      //s
        $CreditUnutilisedAmount[$i],//d
        $CreditAsAtDate[$i]         //s
    );
    
    
    if($CreditStmt->execute()) {
    ?>
    <div class="input-group mb-3">
        <span class="form-control text-success">
        Credit data inserted successfully
        </span>
        <span class="input-group-text text-success">✓</span>
    </div>
    <?php
    } else {
    ?>
    <div class="input-group mb-3">
        <span class="form-control text-danger">
        Insert failed: <?= htmlspecialchars($CreditStmt->error) ?>
        </span>
        <span class="input-group-text text-danger">✗</span>
    </div>
    <?php
    }
}

$CreditStmt->close();
    
//CurrentProject
// Current Project record (normalized schema)
$CurrentProjectRecordNo       = $_POST['CurrentProjectRecordNo'] ?? [];
$CurrentProjTitle       = $_POST['CurrentProjTitle'] ?? [];
$CurrentProjNature      = $_POST['CurrentProjNature'] ?? [];
$CurrentProjLocation    = $_POST['CurrentProjLocation'] ?? [];
$CurrentProjClientName  = $_POST['CurrentProjClientName'] ?? [];
$CurrentProjValue       = $_POST['CurrentProjValue'] ?? [];
$CurrentProjStartDate   = $_POST['CurrentProjStartDate'] ?? [];
$CurrentProjEndDate     = $_POST['CurrentProjEndDate'] ?? [];
$CurrentProjProgress    = $_POST['CurrentProjProgress'] ?? [];

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

    if($ProjectStmt->execute()) {
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

//director and secretary
$DirectorName             = $_POST['DirectorName'] ?? [];
$DirectorNationality      = $_POST['DirectorNationality'] ?? [];
$DirectorPosition         = $_POST['DirectorPosition'] ?? [];
$DirectorAppointmentDate  = $_POST['DirectorAppointmentDate'] ?? [];
$DirectorDOB              = $_POST['DirectorDOB'] ?? [];

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
        $DirectorNationality[$i],         // s
        $DirectorName[$i],                // s
        $DirectorPosition[$i],            // s
        $DirectorAppointmentDate[$i],     // s
        $DirectorDOB[$i]                  // s
    );

    
    if($DirectorStmt->execute()) {
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

//equipment
$EquipmentStmt = $conn->prepare("
    INSERT INTO equipment (
        registrationFormID,
        equipmentID,
        quantity,
        brand,
        rating,
        ownership,
        yearsOfManufacture,
        registrationNo
    ) VALUES (?,?,?,?,?,?,?,?)
");

$equipmentData = [
    [
        'equipmentID' => 1,
        'quantity' => $_POST['BobcatQuality'] ?? '',
        'brand'    => $_POST['BobcatBrandModel'] ?? '',
        'rating'   => $_POST['BobcatRating'] ?? '',
        'ownership'    => $_POST['BobcatOwnership'] ?? '',
        'yearsOfManufacture'     => $_POST['BobcatYearOfManufacture'] ?? '',
        'registrationNo'      => $_POST['BobcatRegistrationNo'] ?? ''
    ],
    [
        'equipmentID' => 2,
        'quantity' => $_POST['HDDQuality'] ?? '',
        'brand'    => $_POST['HDDBrandModel'] ?? '',
        'rating'   => $_POST['HDDRating'] ?? '',
        'ownership'    => $_POST['HDDOwnership'] ?? '',
        'yearsOfManufacture'     => $_POST['HDDYearOfManufacture'] ?? '',
        'registrationNo'      => $_POST['HDDRegistrationNo'] ?? ''
    ],
    [
        'equipmentID' => 3,
        'quantity' => $_POST['SplicingQuality'] ?? '',
        'brand'    => $_POST['SplicingBrandModel'] ?? '',
        'rating'   => $_POST['SplicingRating'] ?? '',
        'ownership'    => $_POST['SplicingOwnership'] ?? '',
        'yearsOfManufacture'     => $_POST['SplicingYearOfManufacture'] ?? '',
        'registrationNo'      => $_POST['SplicingRegistrationNo'] ?? ''
    ],
    [
        'equipmentID' => 4,
        'quantity' => $_POST['OPMQuality'] ?? '',
        'brand'    => $_POST['OPMBrandModel'] ?? '',
        'rating'   => $_POST['OPMRating'] ?? '',
        'ownership'    => $_POST['OPMOwnership'] ?? '',
        'yearsOfManufacture'     => $_POST['OPMYearOfManufacture'] ?? '',
        'registrationNo'      => $_POST['OPMRegistrationNo'] ?? ''
    ],
    [
        'equipmentID' => 5,
        'quantity' => $_POST['OTDRQuality'] ?? '',
        'brand'    => $_POST['OTDRBrandModel'] ?? '',
        'rating'   => $_POST['OTDRRating'] ?? '',
        'ownership'    => $_POST['OTDROwnership'] ?? '',
        'yearsOfManufacture'     => $_POST['OTDRYearOfManufacture'] ?? '',
        'registrationNo'      => $_POST['OTDRRegistrationNo'] ?? ''
    ],
    [
        'equipmentID' => 6,
        'quantity' => $_POST['TestGearQuality'] ?? '',
        'brand'    => $_POST['TestGearBrandModel'] ?? '',
        'rating'   => $_POST['TestGearRating'] ?? '',
        'ownership'    => $_POST['TestGearOwnership'] ?? '',
        'yearsOfManufacture'     => $_POST['TestGearYearOfManufacture'] ?? '',
        'registrationNo'      => $_POST['TestGearRegistrationNo'] ?? ''
    ]
];

foreach ($equipmentData as $eq) {

    // Skip completely empty rows
    if (
        empty($eq['quantity']) &&
        empty($eq['brand']) &&
        empty($eq['rating'])
    ) {
        continue;
    }

    $EquipmentStmt->bind_param(
        "iiisssss",
        $registrationFormID,        // i
        $eq['equipmentID'],         // i
        $eq['quantity'],            // i
        $eq['brand'],               // s
        $eq['rating'],              // s or d (decimal)
        $eq['ownership'],           // s
        $eq['yearsOfManufacture'],  // s (date)
        $eq['registrationNo']       // s
    );
    
    if($EquipmentStmt->execute()) {
    ?>
    <div class="input-group mb-3">
        <span class="form-control text-success">
        Equipment data inserted successfully
        </span>
        <span class="input-group-text text-success">✓</span>
    </div>
    <?php
    } else {
    ?>
    <div class="input-group mb-3">
        <span class="form-control text-danger">
        Insert failed: <?= htmlspecialchars($EquipmentStmt->error) ?>
        </span>
        <span class="input-group-text text-danger">✗</span>
    </div>
    <?php
    }
}

$EquipmentStmt->close();

//Management
$ManagementName              = $_POST['ManagementName'] ?? [];
$ManagementNationality       = $_POST['ManagementNationality'] ?? [];
$ManagementPosition          = $_POST['ManagementPosition'] ?? [];
$ManagementYearsInPosition   = $_POST['ManagementYearInPosition'] ?? [];
$ManagementYearsInIndustry   = $_POST['ManagementYearsInIndustry'] ?? [];

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

    
    if($ManagementStmt->execute()) {
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


//nett worth and working capital
$totalLiabilities = $_POST['totalLiabilities'] ?? [];
$totalAssets      = $_POST['totalAssets'] ?? [];
$NetWorth         = $_POST['NetWorth'] ?? [];
$WorkingCapital   = $_POST['WorkingCapital'] ?? [];

$FinanceStmt = $conn->prepare("
    INSERT INTO nettworth (
        registrationFormID,
        YearOf,
        TotalLiabilities,
        TotalAssets,
        NetWorth,
        WorkingCapital
    ) VALUES (?,?,?,?,?,?)
");

foreach ($totalLiabilities as $year => $liability) {

    // Skip empty rows
    if (
        empty($liability) &&
        empty($totalAssets[$year]) &&
        empty($NetWorth[$year]) &&
        empty($WorkingCapital[$year])
    ) {
        continue;
    }
    
    $totalAssets = $totalAssets[$year] ?? 0;
    $NetWorth = $NetWorth[$year] ?? 0;
    $WorkingCapital = $WorkingCapital[$year] ?? 0;
    
    $FinanceStmt->bind_param(
        "isiddd",
        $registrationFormID,                         // i
        $year,                           // i (YEAR)
        $liability,                      // d
        $totalAssets,        // d
        $NetWorth,           // d
        $WorkingCapital      // d
    );

    
    if($FinanceStmt->execute()) {
    ?> 
    <div class="input-group mb-3">
        <span class="form-control text-success">
        Nett worth & working capital data inserted successfully
        </span>
        <span class="input-group-text text-success">✓</span>
    </div>
    <?php
    } else {
    ?>
    <div class="input-group mb-3">
        <span class="form-control text-danger">
        Insert failed: <?= htmlspecialchars($FinanceStmt->error) ?>
        </span>
        <span class="input-group-text text-danger">✗</span>
    </div>
    <?php
    }
}

$FinanceStmt->close();


//Past project tracking
$ProjectRecordNo        = $_POST['ProjectRecordNo'] ?? [];
$ProjectTitle           = $_POST['ProjectTitle'] ?? [];
$ProjectNature          = $_POST['ProjectNature'] ?? [];
$ProjectLocation        = $_POST['ProjectLocation'] ?? [];
$ProjectClientName      = $_POST['ProjectClientName'] ?? [];
$ProjectValue           = $_POST['ProjectValue'] ?? [];
$ProjectCommencement    = $_POST['ProjectCommencementDate'] ?? [];
$ProjectCompletion      = $_POST['ProjectCompletionDate'] ?? [];

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

    
    if($ProjectStmt->execute()) {
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


//share holders
$ShareholderName        = $_POST['ShareholderName'] ?? [];
$ShareholderNationality = $_POST['ShareholderNationality'] ?? [];
$CompanyShareholderIDNo        = $_POST['CompanyShareholderID'] ?? [];
$ShareholderAddress     = $_POST['ShareholderAddress'] ?? [];
$ShareholderPercent     = $_POST['ShareholderPercent'] ?? [];

// Validate that shareholder percentages sum to 100%
$totalSharePercent = 0;
$shareholderCount = 0;
for ($i = 0; $i < count($ShareholderName); $i++) {
    if (!empty($ShareholderName[$i]) || !empty($ShareholderNationality[$i]) || !empty($ShareholderPercent[$i])) {
        $shareholderCount++;
        $totalSharePercent += (float)($ShareholderPercent[$i] ?? 0);
    }
}

$shareholderValidationFailed = false;
if ($shareholderCount > 0 && abs($totalSharePercent - 100) > 0.01) {
    ?> 
    <div class="input-group mb-3">
        <span class="form-control text-danger">
        Shareholder percentages must sum to 100%. Current total: <?= number_format($totalSharePercent, 2) ?>%
        </span>
        <span class="input-group-text text-danger">✗</span>
    </div>
    <?php
    // Don't insert shareholders if validation fails
    $shareholderValidationFailed = true;
} 

if (!$shareholderValidationFailed) {

$ShareholderStmt = $conn->prepare("
    INSERT INTO shareholders (
        registrationFormID,
        companyShareholderID,
        name,
        nationality,
        address,
        sharePercentage
    ) VALUES (?,?,?,?,?,?)
");

for ($i = 0; $i < count($ShareholderName); $i++) {

    // Skip empty rows
    if (
        empty($ShareholderName[$i]) &&
        empty($ShareholderNationality[$i]) &&
        empty($ShareholderPercent[$i])
    ) {
        continue;
    }

    $ShareholderStmt->bind_param(
        "issssd",
        $registrationFormID,            // i
        $CompanyShareholderIDNo[$i],    // s
        $ShareholderName[$i],           // s
        $ShareholderNationality[$i],    // s
        $ShareholderAddress[$i],        // s
        $ShareholderPercent[$i]         // d
    );

    if($ShareholderStmt->execute()) {
    ?>
    <div class="input-group mb-3">
        <span class="form-control text-success">
        Shareholder data inserted successfully
        </span>
        <span class="input-group-text text-success">✓</span>
    </div>
    <?php
    } else {
    ?>
    <div class="input-group mb-3">
        <span class="form-control text-danger">
        Insert failed: <?= htmlspecialchars($ShareholderStmt->error) ?>
        </span>
        <span class="input-group-text text-danger">✗</span>
    </div>
    <?php
    }
}

$ShareholderStmt->close();

} // End of shareholder validation block


//on site staff
$StaffNo              = $_POST['StaffNo'] ?? [];
$StaffName            = $_POST['StaffName'] ?? [];
$StaffDesignation     = $_POST['StaffDesignation'] ?? [];
$StaffQualification   = $_POST['StaffQualification'] ?? [];
$StaffExperience      = $_POST['StaffExperience'] ?? [];
$StaffEmployment      = $_POST['StaffEmploymentStatus'] ?? [];
$StaffSkills          = $_POST['StaffSkills'] ?? [];
$StaffCertification   = $_POST['StaffCertification'] ?? [];

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

    if($StaffStmt->execute()) {
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
<div class="pending-box text-success" id="successBox">
    Submission successful! Redirecting to vendor home page...
</div>
<script>
    setTimeout(function() {
        window.location.href = 'VendorHomepage.php'; // Change to your vendor home page if needed
    }, 5000);
</script>
</body>