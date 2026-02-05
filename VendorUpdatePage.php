<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Vendor Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendorStyle.css" rel="stylesheet">
    <style>
        .verification-container { border: 2px solid #dee2e6; background-color: #f8f9fa; }
        .signature-box { border-bottom: 2px solid #333; height: 60px; margin-bottom: 5px; position: relative; }
        .legal-text { font-size: 0.85rem; color: #666; text-align: justify; }
    </style>
</head>
<body>
    <?php
    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'vendor_information');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Search data
    $registrationFormID = $_POST['registrationFormID'] ?? '';

    // 1. Main Registration Form
    $stmt = $conn->prepare("SELECT * FROM registrationform WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $RegistrationRow = $stmt->get_result()->fetch_assoc();
    
    if (!$RegistrationRow) {
        echo "<div class='alert alert-danger m-5'>No data found for ID: " . htmlspecialchars($registrationFormID) . "</div>";
        exit;
    }

    // 2. Shareholders
    $stmt = $conn->prepare("SELECT * FROM shareholders WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $ShareHoldersTable = $stmt->get_result();

    // 3. Directors
    $stmt = $conn->prepare("SELECT * FROM directorandsecretary WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $DirectorsTable = $stmt->get_result();

    // 4. Management
    $stmt = $conn->prepare("SELECT * FROM management WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $ManagementTable = $stmt->get_result();

    // 5. Bank
    $stmt = $conn->prepare("SELECT * FROM bank WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $BankTable = $stmt->get_result();

    // 6. Staff
    $stmt = $conn->prepare("SELECT * FROM staff WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $StaffTeamTable = $stmt->get_result();

    // 7. Project Track Record
    $stmt = $conn->prepare("SELECT * FROM projecttrackrecord WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $ProjectRecordTable = $stmt->get_result();

    // 8. Current Project
    $stmt = $conn->prepare("SELECT * FROM currentproject WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $CurrentProjTable = $stmt->get_result();

    // 9. Credit Facilities
    $stmt = $conn->prepare("SELECT * FROM creditfacilities WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $CreditFacilitiesTable = $stmt->get_result();

    // 10. Net Worth (Fetch all years)
    $stmt = $conn->prepare("SELECT * FROM nettworth WHERE registrationFormID = ? ORDER BY yearOf DESC");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $NetWorthResult = $stmt->get_result();
    $NetWorthData = [];
    while($row = $NetWorthResult->fetch_assoc()) {
        $NetWorthData[$row['yearOf']] = $row;
    }

    // 11. Equipment (Map by equipmentID/Type)
    $stmt = $conn->prepare("SELECT * FROM equipment WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $EquipmentResult = $stmt->get_result();
    $EquipmentData = [];
    while($row = $EquipmentResult->fetch_assoc()) {
        $EquipmentData[$row['equipmentID']] = $row;
    }

    // 12. Contacts
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $ContactsResult = $stmt->get_result();
    $Contacts = [];
    while($row = $ContactsResult->fetch_assoc()) {
        $Contacts[] = $row;
    }
    ?>

    <input type="hidden" id="registrationFormID" value="<?= htmlspecialchars($registrationFormID) ?>">
    <input type="hidden" id="time" value="<?= htmlspecialchars($RegistrationRow['formFirstSubmissionDate']) ?>">

    <img src="Image/company%20logo.png" class="rounded mx-auto d-block" alt="Company Logo" style="width: 150px;">
    <div class="text-center mb-4">
        <b>CIVIL CONTRACTOR REGISTRATION FORM</b><br>
        (For all information given below, documentary evidence shall be submitted)
    </div>
    
    <div class="container">
    <div class="accordion" id="accordionExample">
    
    <form action="insertData.php" method="post">

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Part A: Particulars of Company
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="CompanyName">Company Name</label>
                            <div class="input-group">
                                <input type="text" name="companyName" class="form-control" id="companyName"
                                    data-field="companyName"
                                    value="<?php echo htmlspecialchars($RegistrationRow['companyName']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'companyName','RegistrationForm')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="telephone">Telephone No</label>
                            <div class="input-group">
                                <input type="text" name="telephoneNumber" id="telephoneNumber" class="form-control"
                                    data-field="telephoneNumber"
                                    value="<?php echo htmlspecialchars($RegistrationRow['telephoneNumber']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'telephoneNumber','RegistrationForm')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="OtherName">Other Name (Previous Legal/Trading Names)</label>
                            <div class="input-group">
                                <input type="text" name="otherNames" id="otherNames" class="form-control"
                                    data-field="otherNames"
                                    value="<?php echo htmlspecialchars($RegistrationRow['otherNames']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'otherNames','RegistrationForm')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="tax">Tax Registration Number</label>
                            <div class="input-group">
                                <input type="text" name="taxRegistrationNumber" id="taxRegistrationNumber" class="form-control"
                                    data-field="taxRegistrationNumber"
                                    value="<?php echo htmlspecialchars($RegistrationRow['taxRegistrationNumber']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'taxRegistrationNumber','RegistrationForm')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="NewCompanyRegistration">Company Registration No (new)</label>
                            <input type="text" name="newCompanyRegistrationNumber" id="newCompanyRegistrationNumber" class="form-control"
                                data-field="newCompanyRegistrationNumber"
                                value="<?php echo htmlspecialchars($RegistrationRow['newCompanyRegistrationNumber']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="FaxNo">Fax No</label>
                            <div class="input-group">
                                <input type="text" name="faxNo" id="faxNo" class="form-control"
                                    data-field="faxNo"
                                    value="<?php echo htmlspecialchars($RegistrationRow['faxNo']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'faxNo','RegistrationForm')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="oldCRN">Company Registration No (old)</label>
                            <div class="input-group">
                                <input type="text" name="oldCompanyRegistrationNumber" id="oldCompanyRegistrationNumber" class="form-control"
                                    data-field="oldCompanyRegistrationNumber"
                                    value="<?php echo htmlspecialchars($RegistrationRow['oldCompanyRegistrationNumber']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'oldCompanyRegistrationNumber','RegistrationForm')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="Email">Email</label>
                            <div class="input-group">
                                <input type="email" name="emailAddress" id="emailAddress" class="form-control"
                                    data-field="emailAddress"
                                    value="<?php echo htmlspecialchars($RegistrationRow['emailAddress']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'emailAddress','RegistrationForm')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="CountryOfIncorporation">Country of Incorporation</label>
                            <div class="input-group">
                                <input type="text" name="CountryOfIncorporation" id="CountryOfIncorporation" class="form-control"
                                    data-field="countryOfIncorporation"
                                    value="<?php echo htmlspecialchars($RegistrationRow['countryOfIncorporation']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'CountryOfIncorporation','RegistrationForm','text')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="DateOfIncorporation">Date of incorporation</label>
                            <div class="input-group">
                                <input type="date" name="DateOfIncorporation" id="DateOfIncorporation" class="form-control"
                                    data-field="dateOfIncorporation"
                                    value="<?php echo htmlspecialchars($RegistrationRow['dateOfIncorporation']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'DateOfIncorporation','RegistrationForm','date')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label>Company Organisation (Staff Count)</label>
                            <div class="btn-group w-100" id="CompanyOrganisationGroup" data-field="companyOrganisation">
                                <input type="radio" class="btn-check" name="CompanyOrganisation" id="opt1" value="More than 15" autocomplete="off" disabled <?= $RegistrationRow['companyOrganisation'] === 'More than 15' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="opt1">More than 15</label>

                                <input type="radio" class="btn-check" name="CompanyOrganisation" id="opt2" value="10 - 15" autocomplete="off" disabled <?= $RegistrationRow['companyOrganisation'] === '10 - 15' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="opt2">10 - 15</label>

                                <input type="radio" class="btn-check" name="CompanyOrganisation" id="opt3" value="5 - 10" autocomplete="off" disabled <?= $RegistrationRow['companyOrganisation'] === '5 - 10' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="opt3">5 - 10</label>

                                <input type="radio" class="btn-check" name="CompanyOrganisation" id="opt4" value="Less than 5" autocomplete="off" disabled <?= $RegistrationRow['companyOrganisation'] === 'Less than 5' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="opt4">Less than 5</label>
                            </div>
                            <button type="button" class="btn btn-outline-primary mt-2" onclick="editRadioGroup(this, 'CompanyOrganisationGroup', 'RegistrationForm')">Edit</button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="NatureOfBusiness">Nature and Line of Business</label>
                            <div class="input-group">
                                <input type="text" 
                                    name="NatureOfBusiness" 
                                    id="NatureOfBusiness" 
                                    class="form-control" 
                                    data-field="natureAndLineOfBusiness" 
                                    value="<?= htmlspecialchars($RegistrationRow['natureAndLineOfBusiness'] ?? ''); ?>" 
                                    readonly>
                                
                                <button type="button" 
                                        class="btn btn-outline-primary" 
                                        onclick="editField(this, 'NatureOfBusiness', 'RegistrationForm', 'text')">
                                    Edit
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="RegisteredAddress">Registered Address</label>
                            <div class="input-group">
                                <input type="text" name="RegisteredAddress" id="RegisteredAddress" class="form-control"
                                    data-field="registeredAddress"
                                    value="<?= htmlspecialchars($RegistrationRow['registeredAddress'] ?? ''); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'RegisteredAddress','RegistrationForm','text')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="CorrespondenceAddress">Correspondence Address</label>
                            <div class="input-group">
                                <input type="text" name="CorrespondenceAddress" id="CorrespondenceAddress" class="form-control"
                                    data-field="correspondenceAddress"
                                    value="<?php echo htmlspecialchars($RegistrationRow['correspondenceAddress']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'CorrespondenceAddress','RegistrationForm','text')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label>Type of Organisation</label>
                            <div class="btn-group w-100" id="TypeOfOrganisationGroup" data-field="typeOfOrganisation">
                                <input type="radio" class="btn-check" name="TypeOfOrganisation" id="Berhad" value="Berhad" disabled <?= $RegistrationRow['typeOfOrganisation'] === 'Berhad' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="Berhad">Berhad</label>

                                <input type="radio" class="btn-check" name="TypeOfOrganisation" id="Sdn_Bhd" value="Sdn Bhd" disabled <?= $RegistrationRow['typeOfOrganisation'] === 'Sdn Bhd' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="Sdn_Bhd">Sdn Bhd</label>

                                <input type="radio" class="btn-check" name="TypeOfOrganisation" id="SoleProp" value="Sole Proprietor/Enterprise" disabled <?= $RegistrationRow['typeOfOrganisation'] === 'Sole Proprietor/Enterprise' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="SoleProp">Sole Proprietor/Enterprise</label>
                            </div>
                            <button type="button" class="btn btn-outline-primary mt-2" onclick="editRadioGroup(this, 'TypeOfOrganisationGroup', 'RegistrationForm')">Edit</button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="Website">Website</label>
                            <div class="input-group">
                                <input type="text" name="Website" id="Website" class="form-control"
                                    data-field="website"
                                    value="<?php echo htmlspecialchars($RegistrationRow['website']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'Website','RegistrationForm','text')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="BranchAddress">Branch Address</label>
                            <div class="input-group">
                                <input type="text" name="BranchAddress" id="BranchAddress" class="form-control"
                                    data-field="branch"
                                    value="<?php echo htmlspecialchars($RegistrationRow['branch']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'BranchAddress','RegistrationForm','text')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="AuthorisedCapital">Authorised Capital</label>
                            <div class="input-group">
                                <input type="number" name="AuthorisedCapital" id="AuthorisedCapital" class="form-control"
                                    data-field="authorisedCapital"
                                    value="<?php echo htmlspecialchars($RegistrationRow['authorisedCapital']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'AuthorisedCapital','RegistrationForm','double')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="PaidUpCapital">Paid-up Capital</label>
                            <div class="input-group">
                                <input type="number" name="PaidUpCapital" id="PaidUpCapital" class="form-control"
                                    data-field="paidUpCapital"
                                    value="<?php echo htmlspecialchars($RegistrationRow['paidUpCapital']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'PaidUpCapital','RegistrationForm','double')">Edit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Part B: Particulars of Shareholders
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ParentCompany">Parent Company</label>
                            <div class="input-group">
                                <input type="text" name="ParentCompany" id="ParentCompany" class="form-control"
                                    data-field="parentCompany"
                                    value="<?php echo htmlspecialchars($RegistrationRow['parentCompany']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'ParentCompany','RegistrationForm','text')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="ParentCompanyCountry">Country</label>
                            <div class="input-group">
                                <input type="text" name="ParentCompanyCountry" id="ParentCompanyCountry" class="form-control"
                                    data-field="parentCompanyCountry"
                                    value="<?php echo htmlspecialchars($RegistrationRow['parentCompanyCountry']); ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'ParentCompanyCountry','RegistrationForm','text')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="ultimateParentCompany">Ultimate Parent Company Name</label>
                            <div class="input-group">
                                <input type="text" 
                                    id="ultimateParentCompany" 
                                    class="form-control" 
                                    data-field="ultimateParentCompany" 
                                    value="<?= htmlspecialchars($RegistrationRow['ultimateParentCompany'] ?? '') ?>" 
                                    readonly>
                                <button class="btn btn-outline-primary" type="button" 
                                        onclick="editField(this, 'ultimateParentCompany', 'RegistrationForm', 'text')">
                                    Edit
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="ultimateParentCompanyCountry">Country of Incorporation (Ultimate Parent)</label>
                            <div class="input-group">
                                <input type="text" 
                                    id="ultimateParentCompanyCountry" 
                                    class="form-control" 
                                    data-field="ultimateParentCompanyCountry" 
                                    value="<?= htmlspecialchars($RegistrationRow['ultimateParentCompanyCountry'] ?? '') ?>" 
                                    readonly>
                                <button class="btn btn-outline-primary" type="button" 
                                        onclick="editField(this, 'ultimateParentCompanyCountry', 'RegistrationForm', 'text')">
                                    Edit
                                </button>
                            </div>
                        </div>
                    </div>

                    <table id="shareholderTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID / Reg No</th>
                                <th>Name</th>
                                <th>Nationality</th>
                                <th>Address</th>
                                <th>% of shares</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $ShareHoldersTable->fetch_assoc()): ?>
                            <tr data-id="<?= $row['shareholderID'] ?>">
                                <td><input type="text" data-field="companyShareholderID" class="form-control" value="<?= htmlspecialchars($row['companyShareholderID']) ?>" readonly></td>
                                <td><input type="text" data-field="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" readonly></td>
                                <td><input type="text" data-field="nationality" class="form-control" value="<?= htmlspecialchars($row['nationality']) ?>" readonly></td>
                                <td><input type="text" data-field="address" class="form-control" value="<?= htmlspecialchars($row['address']) ?>" readonly></td>
                                <td><input type="number" data-field="sharePercentage" class="form-control" value="<?= htmlspecialchars($row['sharePercentage']) ?>" step="0.01" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this,'Shareholders','shareholderID')">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEditRow(this,'Shareholders','shareholderID')">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick='addEditShareholders("Shareholders","shareholderTable")'>Add Shareholder</button>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Part C: Directors & Company Secretary
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <table id="DirectorTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Nationality</th>
                                <th>Position</th>
                                <th>Appointment Date</th>
                                <th>DOB</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $DirectorsTable->fetch_assoc()): ?>
                            <tr data-id="<?= $row['directorID'] ?>">
                                <td><input type="text" data-field="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" readonly></td>
                                <td><input type="text" data-field="nationality" class="form-control" value="<?= htmlspecialchars($row['nationality']) ?>" readonly></td>
                                <td><input type="text" data-field="position" class="form-control" value="<?= htmlspecialchars($row['position']) ?>" readonly></td>
                                <td><input type="date" data-field="appointmentDate" class="form-control" value="<?= htmlspecialchars($row['appointmentDate']) ?>" readonly></td>
                                <td><input type="date" data-field="dob" class="form-control" value="<?= htmlspecialchars($row['dob']) ?>" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this,'DirectorAndSecretary','directorID')">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEditRow(this,'DirectorAndSecretary','directorID')">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick='addEditShareholders("DirectorAndSecretary","DirectorTable")'>Add Director</button>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    Part D: Management
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <table id="ManagementTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Nationality</th>
                                <th>Position</th>
                                <th>Years in Position</th>
                                <th>Years in Field</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $ManagementTable->fetch_assoc()): ?>
                            <tr data-id="<?= $row['managementID'] ?>">
                                <td><input type="text" data-field="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" readonly></td>
                                <td><input type="text" data-field="nationality" class="form-control" value="<?= htmlspecialchars($row['nationality']) ?>" readonly></td>
                                <td><input type="text" data-field="position" class="form-control" value="<?= htmlspecialchars($row['position']) ?>" readonly></td>
                                <td><input type="number" data-field="yearsInPosition" class="form-control" value="<?= htmlspecialchars($row['yearsInPosition']) ?>" readonly></td>
                                <td><input type="number" data-field="yearsInRelatedField" class="form-control" value="<?= htmlspecialchars($row['yearsInRelatedField']) ?>" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this,'Management','managementID')">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEditRow(this,'Management','managementID')">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick='addEditShareholders("Management","ManagementTable")'>Add Management</button>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFive">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    Part E: Particulars of Finance
                </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <div class="mb-3">
                        <label>Does the Company have history of bankruptcy?</label>
                        <div id="BankruptcyGroup" data-field="bankruptHistory">
                            <input type="radio" name="bankruptcy" value="yes" disabled <?= $RegistrationRow['bankruptHistory'] === 'yes' ? 'checked' : '' ?>> Yes
                            <input type="radio" name="bankruptcy" value="no" disabled <?= $RegistrationRow['bankruptHistory'] === 'no' ? 'checked' : '' ?>> No
                            <button type="button" class="btn btn-outline-primary btn-sm ms-3" onclick="editRadioGroup(this, 'BankruptcyGroup', 'RegistrationForm')">Edit</button>
                        </div>
                    </div>

                    <h5 class="mt-4">Auditors & Advocates</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width: 15%;">Category</th>
                                    <th colspan="2">Company Details</th>
                                    <th colspan="2">Contact Person Details</th>
                                    <th style="width: 10%;">Year of Service</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td rowspan="3" class="fw-bold text-center bg-light">Auditors</td>
                                    <td style="width: 10%;"><label class="small fw-bold">Name</label></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="auditorCompanyName" class="form-control" data-field="auditorCompanyName" value="<?= htmlspecialchars($RegistrationRow['auditorCompanyName'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'auditorCompanyName', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                    <td style="width: 10%;"><label class="small fw-bold">Name</label></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="auditorName" class="form-control" data-field="auditorName" value="<?= htmlspecialchars($RegistrationRow['auditorName'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'auditorName', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                    <td rowspan="3">
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="auditorYearOfService" class="form-control text-center" data-field="auditorYearOfService" value="<?= htmlspecialchars($RegistrationRow['auditorYearOfService'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'auditorYearOfService', 'RegistrationForm', 'number')">Edit</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td rowspan="2"><label class="small fw-bold">Address</label></td>
                                    <td rowspan="2">
                                        <div class="input-group input-group-sm h-100">
                                            <textarea id="auditorCompanyAddress" class="form-control" data-field="auditorCompanyAddress" rows="3" readonly><?= htmlspecialchars($RegistrationRow['auditorCompanyAddress'] ?? '') ?></textarea>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'auditorCompanyAddress', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                    <td><label class="small fw-bold">Email</label></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="email" id="auditorEmail" class="form-control" data-field="auditorEmail" value="<?= htmlspecialchars($RegistrationRow['auditorEmail'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'auditorEmail', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="small fw-bold">Phone</label></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="auditorPhone" class="form-control" data-field="auditorPhone" value="<?= htmlspecialchars($RegistrationRow['auditorPhone'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'auditorPhone', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="table-divider"><td colspan="6" style="height: 20px; border: none;"></td></tr>
                                <tr>
                                    <td rowspan="3" class="fw-bold text-center bg-light">Advocates & Solicitors</td>
                                    <td><label class="small fw-bold">Name</label></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="advocatesCompanyName" class="form-control" data-field="advocatesCompanyName" value="<?= htmlspecialchars($RegistrationRow['advocatesCompanyName'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'advocatesCompanyName', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                    <td><label class="small fw-bold">Name</label></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="advocatesName" class="form-control" data-field="advocatesName" value="<?= htmlspecialchars($RegistrationRow['advocatesName'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'advocatesName', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                    <td rowspan="3">
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="advocatesYearOfService" class="form-control text-center" data-field="advocatesYearOfService" value="<?= htmlspecialchars($RegistrationRow['advocatesYearOfService'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'advocatesYearOfService', 'RegistrationForm', 'number')">Edit</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td rowspan="2"><label class="small fw-bold">Address</label></td>
                                    <td rowspan="2">
                                        <div class="input-group input-group-sm h-100">
                                            <textarea id="advocatesCompanyAddress" class="form-control" data-field="advocatesCompanyAddress" rows="3" readonly><?= htmlspecialchars($RegistrationRow['advocatesCompanyAddress'] ?? '') ?></textarea>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'advocatesCompanyAddress', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                    <td><label class="small fw-bold">Email</label></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="email" id="advocatesEmail" class="form-control" data-field="advocatesEmail" value="<?= htmlspecialchars($RegistrationRow['advocatesEmail'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'advocatesEmail', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label class="small fw-bold">Phone</label></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="advocatesPhone" class="form-control" data-field="advocatesPhone" value="<?= htmlspecialchars($RegistrationRow['advocatesPhone'] ?? '') ?>" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'advocatesPhone', 'RegistrationForm', 'text')">Edit</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-4">Bank Information</h5>
                    <table id="bankTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name of Bank</th>
                                <th>Address</th>
                                <th>Swift Code</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $BankTable->fetch_assoc()): ?>
                            <tr data-id="<?= $row['bankID'] ?>">
                                <td><input type="text" data-field="bankName" class="form-control" value="<?= htmlspecialchars($row['bankName']) ?>" readonly></td>
                                <td><input type="text" data-field="bankAddress" class="form-control" value="<?= htmlspecialchars($row['bankAddress']) ?>" readonly></td>
                                <td><input type="text" data-field="swiftCode" class="form-control" value="<?= htmlspecialchars($row['swiftCode']) ?>" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this,'Bank','bankID')">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEditRow(this,'Bank','bankID')">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick='addEditShareholders("Bank","bankTable")'>Add Bank</button>

                    <h5 class="mt-4">Net Worth (Last 3 Years)</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Item</th>
                            <?php 
                                $currentYear = date("Y");
                                for($i=1; $i<=3; $i++) { echo "<th>".($currentYear-$i)." (RM)</th>"; }
                            ?>
                        </tr>
                        <?php
                            $fields = [
                                'totalLiabilities' => 'Total Liabilities', 
                                'totalAssets' => 'Total Assets', 
                                'netWorth' => 'Net Worth', 
                                'workingCapital' => 'Working Capital'
                            ];
                            foreach($fields as $dbField => $label) {
                                echo "<tr><td>$label</td>";
                                for($i=1; $i<=3; $i++) {
                                    $y = $currentYear-$i;
                                    $val = isset($NetWorthData[$y]) ? $NetWorthData[$y][$dbField] : '';
                                    echo "<td><input type='text' class='form-control' value='".htmlspecialchars($val)."' readonly></td>";
                                }
                                echo "</tr>";
                            }
                        ?>
                    </table>
                    <h5 class="mt-4">Credit Facilities</h5>
                    <table id="CreditTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Institution</th>
                                <th>Total (RM)</th>
                                <th>Expiry</th>
                                <th>Unutilised</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $CreditFacilitiesTable->fetch_assoc()): ?>
                            <tr data-id="<?= $row['facilityID'] ?>">
                                <td><input type="text" data-field="typeOfCreditFacilities" class="form-control" value="<?= htmlspecialchars($row['typeOfCreditFacilities']) ?>" readonly></td>
                                <td><input type="text" data-field="financialInstitution" class="form-control" value="<?= htmlspecialchars($row['financialInstitution']) ?>" readonly></td>
                                <td><input type="number" data-field="totalAmount" class="form-control" value="<?= htmlspecialchars($row['totalAmount']) ?>" readonly></td>
                                <td><input type="date" data-field="expiryDate" class="form-control" value="<?= htmlspecialchars($row['expiryDate']) ?>" readonly></td>
                                <td><input type="number" data-field="unutilisedAmountCurrentlyAvailable" class="form-control" value="<?= htmlspecialchars($row['unutilisedAmountCurrentlyAvailable']) ?>" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this,'CreditFacilities','facilityID')">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEditRow(this,'CreditFacilities','facilityID')">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSix">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                    Part F: Technical Capability
                </button>
            </h2>
            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>CIDB Grade</label>
                            <div class="input-group">
                                <input type="text" id="cidb" class="form-control" data-field="cidb" value="<?= htmlspecialchars($RegistrationRow['cidb']) ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'cidb','RegistrationForm','text')">Edit</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>CIDB Validity</label>
                            <div class="input-group">
                                <input type="date" id="cidbVal" class="form-control" data-field="cidbValidationTill" value="<?= htmlspecialchars($RegistrationRow['cidbValidationTill']) ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="editField(this, 'cidbVal','RegistrationForm','date')">Edit</button>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4">Plant & Machinery</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Brand</th>
                                <th>Rating</th>
                                <th>Ownership</th>
                                <th>Year Mfg</th>
                                <th>Reg No</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Map ID 1-6 from equipmentused table
                            $eqTypes = [
                                1 => 'Bobcat/JCB', 
                                2 => 'HDD Equipment', 
                                3 => 'Splicing Equipment',
                                4 => 'Optical Power Meter',
                                5 => 'OTDR',
                                6 => 'Test Gear'
                            ];
                            foreach($eqTypes as $id => $name): 
                                $eData = $EquipmentData[$id] ?? ['quantity'=>'','brand'=>'','rating'=>'','ownership'=>'','yearsOfManufacture'=>'','registrationNo'=>''];
                            ?>
                            <tr data-equipment-type-id="<?= $id ?>">
                                <td><?= $name ?></td>
                                <td><input type="number" class="form-control" value="<?= htmlspecialchars($eData['quantity']) ?>" readonly></td>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($eData['brand']) ?>" readonly></td>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($eData['rating']) ?>" readonly></td>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($eData['ownership']) ?>" readonly></td>
                                <td><input type="date" class="form-control" value="<?= htmlspecialchars($eData['yearsOfManufacture']) ?>" readonly></td>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($eData['registrationNo']) ?>" readonly></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h5 class="mt-4">Staff & Site Team</h5>
                    <table id="StaffTeamTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Qualification</th>
                                <th>Status</th>
                                <th>Skills</th>
                                <th>Exp (Yrs)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $StaffTeamTable->fetch_assoc()): ?>
                            <tr data-id="<?= $row['staffNo'] ?>">
                                <td><input type="number" data-field="staffNo" class="form-control" value="<?= htmlspecialchars($row['staffNo']) ?>" readonly></td>
                                <td><input type="text" data-field="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" readonly></td>
                                <td><input type="text" data-field="designation" class="form-control" value="<?= htmlspecialchars($row['designation']) ?>" readonly></td>
                                <td><input type="text" data-field="qualification" class="form-control" value="<?= htmlspecialchars($row['qualification']) ?>" readonly></td>
                                <td><input type="text" data-field="employmentStatus" class="form-control" value="<?= htmlspecialchars($row['employmentStatus']) ?>" readonly></td>
                                <td><input type="text" data-field="skills" class="form-control" value="<?= htmlspecialchars($row['skills']) ?>" readonly></td>
                                <td><input type="number" data-field="yearsOfExperience" class="form-control" value="<?= htmlspecialchars($row['yearsOfExperience']) ?>" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this,'Staff','staffNo')">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEditRow(this,'Staff','staffNo')">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick='addEditShareholders("Staff","StaffTeamTable")'>Add Staff</button>

                    <h5 class="mt-4">Project Track Record</h5>
                    <table id="ProjectRecordTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>Client</th>
                                <th>Value</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $ProjectRecordTable->fetch_assoc()): ?>
                            <tr data-id="<?= $row['projectRecordNo'] ?>">
                                <td><input type="number" data-field="projectRecordNo" class="form-control" value="<?= htmlspecialchars($row['projectRecordNo']) ?>" readonly></td>
                                <td><input type="text" data-field="projectTitle" class="form-control" value="<?= htmlspecialchars($row['projectTitle']) ?>" readonly></td>
                                <td><input type="text" data-field="clientName" class="form-control" value="<?= htmlspecialchars($row['clientName']) ?>" readonly></td>
                                <td><input type="text" data-field="projectValue" class="form-control" value="<?= htmlspecialchars($row['projectValue']) ?>" readonly></td>
                                <td><input type="date" data-field="commencementDate" class="form-control" value="<?= htmlspecialchars($row['commencementDate']) ?>" readonly></td>
                                <td><input type="date" data-field="completionDate" class="form-control" value="<?= htmlspecialchars($row['completionDate']) ?>" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this,'ProjectTrackRecord','projectRecordNo')">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEditRow(this,'ProjectTrackRecord','projectRecordNo')">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick='addEditShareholders("ProjectTrackRecord","ProjectRecordTable")'>Add Project Record</button>
                    
                    <h5 class="mt-4">Current Projects</h5>
                     <table id="CurrentProjTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>Client</th>
                                <th>Value</th>
                                <th>Progress (%)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $CurrentProjTable->fetch_assoc()): ?>
                            <tr data-id="<?= $row['currentProjectRecordNo'] ?>">
                                <td><input type="number" data-field="currentProjectRecordNo" class="form-control" value="<?= htmlspecialchars($row['currentProjectRecordNo']) ?>" readonly></td>
                                <td><input type="text" data-field="projectTitle" class="form-control" value="<?= htmlspecialchars($row['projectTitle']) ?>" readonly></td>
                                <td><input type="text" data-field="clientName" class="form-control" value="<?= htmlspecialchars($row['clientName']) ?>" readonly></td>
                                <td><input type="text" data-field="projectValue" class="form-control" value="<?= htmlspecialchars($row['projectValue']) ?>" readonly></td>
                                <td><input type="number" data-field="progressOfTheWork" class="form-control" value="<?= htmlspecialchars($row['progressOfTheWork']) ?>" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editTableRow(this,'CurrentProject','currentProjectRecordNo')">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteEditRow(this,'CurrentProject','currentProjectRecordNo')">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick='addEditShareholders("CurrentProject","CurrentProjTable")'>Add Current Project</button>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSeven">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                    Part G: Contact Details
                </button>
            </h2>
            <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <?php 
                    // Display existing contacts
                    foreach($Contacts as $idx => $contact): 
                        $label = ($idx == 0) ? "Primary" : "Secondary";
                    ?>
                    <h6 class="text-primary"><?= $label ?> Contact Person</h6>
                    <div class="row mb-3 border-bottom pb-2">
                         <div class="col-md-6">
                            <label>Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($contact['contactPersonName']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Phone</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($contact['telephoneNumber']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($contact['emailAddress']) ?>" readonly>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingH">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseH">
                    Part H: Self Declaration
                </button>
            </h2>
            <div id="collapseH" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    <div class="legal-text mb-3">
                        <p>We understand and acknowledge that MSA RESOURCES SDN. BHD. <b>(Company No. 199801006982 (463109-M)) (“MSAR”)</b> observes good business conduct and is committed to adhere to all laws and regulations wherever it operates including but not limited to economic sanctions, export control, competition or anti-trust, personal data protection laws, guided by MSAR’s policies.</p>
                        <p>Accordingly, we confirm and declare that, to the best of our knowledge, the Company and/or any of its affiliates, including its and their directors, officers, employees–</p>
                        <ol type="a">
                            <li>Are not the target or subjects of any sanctions;</li>
                            <li>Are not owned or controlled by any person who is the target or subject of any sanctions;</li>
                            <li>Are not acting for the benefit of or on behalf of any person that is the target or subject of any sanctions;</li>
                            <li>Have not been engaging in any conduct/activity that would result in us being in breach of any sanctions;</li>
                            <li>Have not been the subject of any convictions or prosecutions in relation to export control regulations within the last 5 years.</li>
                        </ol>
                    </div>
                    <div class="alert alert-info py-2">
                        <i class="fa fa-info-circle"></i> This section serves as a legal declaration. Any changes to the verification details below (Part J) apply to this declaration.
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingNine">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNine">
                    Part I: Notice of Disclosure
                </button>
            </h2>
            <div id="collapseNine" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    <div class="legal-text" style="text-align: justify; font-size: 0.95rem;">
                        <p><strong>Applicable Laws relating to regulation of the Processing of Personal Data (“Data Protection Law”)</strong></p>
                        <p>Pursuant to the requirement of Data Protection Law, we hereby wish to give this notice and seek your consent on the processing of your personal data. The Personal Data will be processed by MSAR or such other third parties (as the case may be) for the following purposes:</p>
                        <ol type="a">
                            <li>to evaluate and process your application for registration as a vendor;</li>
                            <li>to enable MSAR to discharge its contractual obligations, including but not limited to the preparation of various contract-related documents, payment-related purposes and to communicate with you;</li>
                            <li>to respond to your inquiries and/or complaints and to resolve disputes;</li>
                            <li>to enable MSAR to comply with its legal and regulatory obligations, including but not limited to any anti-money laundering and/or anti-terrorism financing laws and regulations and for any other purpose required by law;</li>
                            <li>for MSAR’s internal record keeping, analysis and/or for any other purpose that is incidental or ancillary to the above-mentioned purposes.</li>
                        </ol>
                        <p>Please note that it is necessary for MSAR to process your Personal Data for the purposes stated above. If you do not provide MSAR with your Personal Data or do not consent to this notice, MSAR will not be able to evaluate and process your application for registration as a vendor and/or to communicate with you.</p>
                        <p>You may, at any time, make a request in writing for access to, or to request for correction or rectification of your Personal Data, or to limit the processing of your Personal Data by contacting:</p>
                        <div class="card bg-light mt-3">
                            <div class="card-body">
                                <strong>Name: En. Razlan Radzi</strong><br>
                                <strong>Phone No: 019 - 258 8888</strong><br>
                                <strong>Email: razlan@msar.tech</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTen">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTen">
                    Part J: Information Verification
                </button>
            </h2>
            <div id="collapseTen" class="accordion-collapse collapse show" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    <div class="legal-text mb-4" style="text-align: justify; font-size: 0.9rem; color: #555;">
                        <p>By providing the information in this form, the vendor and/or person(s) who provided the information:</p>
                        <ul class="mb-4">
                            <li>declares that he/she has the proper mandate to disclose such information;</li>
                            <li>consents to the processing of such information for the purposes stated in this form;</li>
                            <li>represents that such information is accurate, current, and complete;</li>
                            <li>consents to MSA Resources Sdn. Bhd. to conduct credit checks with CTOS or other agencies.</li>
                        </ul>
                    </div>

                    <div class="verification-container p-4 border rounded bg-light">
                        <p class="mb-1"><strong>The above information had been verified by: -</strong></p>
                        <p class="mb-4"><strong>For and on behalf of the Company.</strong></p>

                        <div class="row align-items-end">
                            <div class="col-md-6 mb-4">
                                <div class="signature-box border-bottom border-dark mb-2" style="height: 80px; width: 100%; position: relative;">
                                    <span class="text-muted small" style="position: absolute; bottom: 5px; left: 0;">
                                        (Authorised Signature and Company Stamp)
                                    </span>
                                </div>
                                <p class="text-uppercase fw-bold small">Chairman / Director / Company Secretary</p>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Name:</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="verifierName" 
                                            class="form-control" 
                                            data-field="verifierName" 
                                            value="<?= htmlspecialchars($RegistrationRow['verifierName'] ?? '') ?>" 
                                            readonly>
                                        <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'verifierName', 'RegistrationForm', 'text')">Edit</button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Designation:</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="verifierDesignation" 
                                            class="form-control" 
                                            data-field="verifierDesignation" 
                                            value="<?= htmlspecialchars($RegistrationRow['verifierDesignation'] ?? '') ?>" 
                                            readonly>
                                        <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'verifierDesignation', 'RegistrationForm', 'text')">Edit</button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Date:</label>
                                    <div class="input-group input-group-sm">
                                        <input type="date" id="dateOfVerification" 
                                            class="form-control" 
                                            data-field="dateOfVerification" 
                                            value="<?= htmlspecialchars($RegistrationRow['dateOfVerification'] ?? '') ?>" 
                                            readonly>
                                        <button class="btn btn-outline-primary" type="button" onclick="editField(this, 'dateOfVerification', 'RegistrationForm', 'date')">Edit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Submit Final Changes</button>
            <a href="VendorUpdateDate.php" class="btn btn-secondary">Back to List</a>
        </div>

    </form>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="VendorUpdateScript.js"></script>
</body>
</html>