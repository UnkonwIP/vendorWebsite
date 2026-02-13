<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Vendor Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendorStyle.css" rel="stylesheet">
    <style>
        .verification-container { border: 2px solid #dee2e6; background-color: #f8f9fa; padding: 20px; }
        .signature-box { border-bottom: 2px solid #333; height: 60px; margin-bottom: 5px; position: relative; }
        .legal-text { font-size: 0.9rem; color: #444; text-align: justify; margin-bottom: 15px; }
        .section-desc { font-style: italic; color: #666; font-size: 0.9rem; margin-bottom: 15px; }
        /* Read-only inputs look clean, Editable inputs pop out */
        input[readonly], textarea[readonly] { background-color: #f8f9fa; border: 1px solid #dee2e6; color: #495057; }
        input:not([readonly]), textarea:not([readonly]) { background-color: #fff; border: 1px solid #0d6efd; box-shadow: 0 0 5px rgba(13, 110, 253, 0.25); }
        /* 1. Allow the accordion to contain the scrollable area */
        .accordion-body {
            max-width: 100%;
            overflow: visible; /* Changed from hidden to allow child scrolling */
            padding: 1rem;
        }

        /* 2. The critical fix for the scrollbar */
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto; /* This forces the scrollbar to appear when needed */
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
            border: 1px solid #e0e0e0; /* Adds a boundary so you see the 'box' edge */
            border-radius: 4px;
        }

        /* 3. Prevent the table from collapsing too small */
        .table {
            min-width: 600px; /* Ensures the table maintains a readable shape while scrolling */
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php
    // --- DATABASE CONNECTION & DATA FETCHING ---
    require_once "config.php";
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    $registrationFormID = $_POST['registrationFormID'] ?? '';

    // Safety check
    if (empty($registrationFormID)) {
        die("<div class='container mt-5'><div class='alert alert-danger'>Error: No Registration ID provided. Please access this page from the Vendor Homepage.</div></div>");
    }

    // 1. Fetch Main Form
    $stmt = $conn->prepare("SELECT * FROM registrationform WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $RegistrationRow = $stmt->get_result()->fetch_assoc();
    if (!$RegistrationRow) die("Error: Record not found.");

    // Helper functions to fetch tables
    function fetchTable($conn, $sql, $id) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    $ShareHoldersTable  = fetchTable($conn, "SELECT * FROM shareholders WHERE registrationFormID = ?", $registrationFormID);
    $DirectorsTable     = fetchTable($conn, "SELECT * FROM directorandsecretary WHERE registrationFormID = ?", $registrationFormID);
    $ManagementTable    = fetchTable($conn, "SELECT * FROM management WHERE registrationFormID = ?", $registrationFormID);
    $BankTable          = fetchTable($conn, "SELECT * FROM bank WHERE registrationFormID = ?", $registrationFormID);
    $StaffTeamTable     = fetchTable($conn, "SELECT * FROM staff WHERE registrationFormID = ?", $registrationFormID);
    $ProjectRecordTable = fetchTable($conn, "SELECT * FROM projecttrackrecord WHERE registrationFormID = ?", $registrationFormID);
    $CurrentProjTable   = fetchTable($conn, "SELECT * FROM currentproject WHERE registrationFormID = ?", $registrationFormID);
    $CreditFacilities   = fetchTable($conn, "SELECT * FROM creditfacilities WHERE registrationFormID = ?", $registrationFormID);
    $ContactsResult     = fetchTable($conn, "SELECT * FROM contacts WHERE registrationFormID = ?", $registrationFormID);
    
    // Net Worth (Keyed by Year)
    $NetWorthResult = fetchTable($conn, "SELECT * FROM nettworth WHERE registrationFormID = ? ORDER BY yearOf DESC", $registrationFormID);
    $NetWorthData = [];
    while($row = $NetWorthResult->fetch_assoc()) { $NetWorthData[$row['yearOf']] = $row; }

    // Equipment (Keyed by Equipment ID/Type)
    $EquipmentResult = fetchTable($conn, "SELECT * FROM equipment WHERE registrationFormID = ?", $registrationFormID);
    $EquipmentData = [];
    while($row = $EquipmentResult->fetch_assoc()) { $EquipmentData[$row['equipmentID']] = $row; }
    ?>

    <input type="hidden" id="registrationFormID" value="<?= htmlspecialchars($registrationFormID) ?>">

    <div class="container my-5">
        <div class="text-center mb-4">
            <img src="Image/company%20logo.png" alt="Company Logo" style="width: 150px;" class="mb-3">
            <h4><b>CIVIL CONTRACTOR REGISTRATION FORM</b></h4>
            <p class="text-muted">(For all information given below, documentary evidence shall be submitted)</p>
        </div>

        <div class="accordion" id="accordionExample">
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                        Part A: Particulars of Company
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <div class="input-group">
                                    <input type="text" id="companyName" class="form-control" data-field="companyName" value="<?= htmlspecialchars($RegistrationRow['companyName']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'companyName', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telephone No</label>
                                <div class="input-group">
                                    <input type="number" id="telephoneNumber" class="form-control" data-field="telephoneNumber" value="<?= htmlspecialchars($RegistrationRow['telephoneNumber']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'telephoneNumber', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Other Name (Any previous Legal Name/Trading Names)</label>
                                <div class="input-group">
                                    <input type="text" id="otherNames" class="form-control" data-field="otherNames" value="<?= htmlspecialchars($RegistrationRow['otherNames']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'otherNames', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tax Registration Number</label>
                                <div class="input-group">
                                    <input type="number" id="taxRegistrationNumber" class="form-control" data-field="taxRegistrationNumber" value="<?= htmlspecialchars($RegistrationRow['taxRegistrationNumber']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'taxRegistrationNumber', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Company Registration No (New)</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($RegistrationRow['newCompanyRegistrationNumber']) ?>" readonly title="Cannot change unique ID">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Registration No (Old)</label>
                                <div class="input-group">
                                    <input type="text" id="oldCompanyRegistrationNumber" class="form-control" data-field="oldCompanyRegistrationNumber" value="<?= htmlspecialchars($RegistrationRow['oldCompanyRegistrationNumber']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'oldCompanyRegistrationNumber', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fax No</label>
                                <div class="input-group">
                                    <input type="number" id="faxNo" class="form-control" data-field="faxNo" value="<?= htmlspecialchars($RegistrationRow['faxNo']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'faxNo', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <input type="email" id="emailAddress" class="form-control" data-field="emailAddress" value="<?= htmlspecialchars($RegistrationRow['emailAddress']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'emailAddress', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Country of Incorporation</label>
                                <div class="input-group">
                                    <input type="text" id="countryOfIncorporation" class="form-control" data-field="countryOfIncorporation" value="<?= htmlspecialchars($RegistrationRow['countryOfIncorporation']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'countryOfIncorporation', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Incorporation</label>
                                <div class="input-group">
                                    <input type="date" id="dateOfIncorporation" class="form-control" data-field="dateOfIncorporation" value="<?= htmlspecialchars($RegistrationRow['dateOfIncorporation']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'dateOfIncorporation', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold">Company Organisation</label>
                                <div class="card p-3 bg-light">
                                    <div id="CompanyOrgGroup" data-field="companyOrganisation">
                                        <?php $org = $RegistrationRow['companyOrganisation']; ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="CompanyOrganisation" value="More than 15" <?= $org == 'More than 15' ? 'checked' : '' ?> disabled>
                                            <label class="form-check-label">More than 15</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="CompanyOrganisation" value="10 - 15" <?= $org == '10 - 15' ? 'checked' : '' ?> disabled>
                                            <label class="form-check-label">10 - 15</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="CompanyOrganisation" value="5 - 10" <?= $org == '5 - 10' ? 'checked' : '' ?> disabled>
                                            <label class="form-check-label">5 - 10</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="CompanyOrganisation" value="Less than 5" <?= $org == 'Less than 5' ? 'checked' : '' ?> disabled>
                                            <label class="form-check-label">Less than 5</label>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-secondary mt-2 w-25" onclick="editRadioGroup(this, 'CompanyOrgGroup', 'RegistrationForm')">Enable Editing</button>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Nature and Line of Business</label>
                                <div class="input-group">
                                    <input type="text" id="natureAndLineOfBusiness" class="form-control" data-field="natureAndLineOfBusiness" value="<?= htmlspecialchars($RegistrationRow['natureAndLineOfBusiness']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'natureAndLineOfBusiness', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Registered Address</label>
                                <div class="input-group">
                                    <textarea id="registeredAddress" class="form-control" data-field="registeredAddress" rows="2" readonly><?= htmlspecialchars($RegistrationRow['registeredAddress']) ?></textarea>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'registeredAddress', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Correspondence/Business Address</label>
                                <div class="input-group">
                                    <textarea id="correspondenceAddress" class="form-control" data-field="correspondenceAddress" rows="2" readonly><?= htmlspecialchars($RegistrationRow['correspondenceAddress']) ?></textarea>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'correspondenceAddress', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold">Type of Organisation</label>
                                <div class="card p-3 bg-light">
                                    <div id="TypeOrgGroup" data-field="typeOfOrganisation">
                                        <?php $typeOrg = $RegistrationRow['typeOfOrganisation']; ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="TypeOfOrganisation" value="Berhad" <?= $typeOrg == 'Berhad' ? 'checked' : '' ?> disabled>
                                            <label class="form-check-label">Berhad</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="TypeOfOrganisation" value="Sdn Bhd" <?= $typeOrg == 'Sdn Bhd' ? 'checked' : '' ?> disabled>
                                            <label class="form-check-label">Sdn Bhd</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="TypeOfOrganisation" value="sole Proprietor/Enterprise" <?= $typeOrg == 'sole Proprietor/Enterprise' ? 'checked' : '' ?> disabled>
                                            <label class="form-check-label">Sole Proprietor/Enterprise</label>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-secondary mt-2 w-25" onclick="editRadioGroup(this, 'TypeOrgGroup', 'RegistrationForm')">Enable Editing</button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <div class="input-group">
                                    <input type="text" id="website" class="form-control" data-field="website" value="<?= htmlspecialchars($RegistrationRow['website']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'website', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Branch Address (if any)</label>
                                <div class="input-group">
                                    <input type="text" id="branch" class="form-control" data-field="branch" value="<?= htmlspecialchars($RegistrationRow['branch']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'branch', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Authorised Capital</label>
                                <div class="input-group">
                                    <input type="number" id="authorisedCapital" class="form-control" data-field="authorisedCapital" value="<?= htmlspecialchars($RegistrationRow['authorisedCapital']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'authorisedCapital', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Paid-up Capital</label>
                                <div class="input-group">
                                    <input type="number" id="paidUpCapital" class="form-control" data-field="paidUpCapital" value="<?= htmlspecialchars($RegistrationRow['paidUpCapital']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'paidUpCapital', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                        Part B: Particulars of Shareholders
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label>Parent Company (Full Legal Name)</label>
                                <div class="input-group">
                                    <input type="text" id="parentCompany" class="form-control" data-field="parentCompany" value="<?= htmlspecialchars($RegistrationRow['parentCompany']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'parentCompany', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Country</label>
                                <div class="input-group">
                                    <input type="text" id="parentCompanyCountry" class="form-control" data-field="parentCompanyCountry" value="<?= htmlspecialchars($RegistrationRow['parentCompanyCountry']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'parentCompanyCountry', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Ultimate Parent Company (Full Legal Name)</label>
                                <div class="input-group">
                                    <input type="text" id="ultimateParentCompany" class="form-control" data-field="ultimateParentCompany" value="<?= htmlspecialchars($RegistrationRow['ultimateParentCompany']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'ultimateParentCompany', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Country</label>
                                <div class="input-group">
                                    <input type="text" id="ultimateParentCompanyCountry" class="form-control" data-field="ultimateParentCompanyCountry" value="<?= htmlspecialchars($RegistrationRow['ultimateParentCompanyCountry']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'ultimateParentCompanyCountry', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                        </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" id="shareholderTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID / Registration Number</th>
                                    <th>Name</th>
                                    <th>Nationality</th>
                                    <th>Address</th>
                                    <th>% of shares</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $ShareHoldersTable->fetch_assoc()): ?>
                                <tr data-id="<?= $row['shareholderID'] ?>">
                                    <td><input type="text" data-field="companyShareholderID" class="form-control" value="<?= htmlspecialchars($row['companyShareholderID']) ?>" readonly></td>
                                    <td><input type="text" data-field="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" readonly></td>
                                    <td><input type="text" data-field="nationality" class="form-control" value="<?= htmlspecialchars($row['nationality']) ?>" readonly></td>
                                    <td><input type="text" data-field="address" class="form-control" value="<?= htmlspecialchars($row['address']) ?>" readonly></td>
                                    <td><input type="number" data-field="sharePercentage" class="form-control" step="0.01" value="<?= htmlspecialchars($row['sharePercentage']) ?>" readonly></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary mb-1" onclick="editTableRow(this, 'Shareholders', 'shareholderID')">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteEditRow(this, 'Shareholders', 'shareholderID')">Delete</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <button class="btn btn-success mt-3" onclick="addEditShareholders('Shareholders', 'shareholderTable')">Add New Shareholder</button>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                        Part C: Particulars of Directors & Company Secretary
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle" id="DirectorTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Nationality</th>
                                        <th>Position</th>
                                        <th>Appointment Date</th>
                                        <th>Date of Birth</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $DirectorsTable->fetch_assoc()): ?>
                                    <tr data-id="<?= $row['directorID'] ?>">
                                        <td><input type="text" data-field="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" readonly></td>
                                        <td><input type="text" data-field="nationality" class="form-control" value="<?= htmlspecialchars($row['nationality']) ?>" readonly></td>
                                        <td><input type="text" data-field="position" class="form-control" value="<?= htmlspecialchars($row['position']) ?>" readonly></td>
                                        <td><input type="date" data-field="appointmentDate" class="form-control" value="<?= ($row['appointmentDate'] == '0000-00-00' ? '' : htmlspecialchars($row['appointmentDate'])) ?>" readonly></td>
                                        <td><input type="date" data-field="dob" class="form-control" value="<?= ($row['dob'] == '0000-00-00' ? '' : htmlspecialchars($row['dob'])) ?>" readonly></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary mb-1" onclick="editTableRow(this, 'DirectorAndSecretary', 'directorID')">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEditRow(this, 'DirectorAndSecretary', 'directorID')">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <button class="btn btn-success mt-3" onclick="addEditShareholders('DirectorAndSecretary', 'DirectorTable')">Add Director</button>
                        </div>
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
                    <div class="table-responsive">
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
                        <button type="button" class="btn btn-success mt-3" onclick='addEditShareholders("Management","ManagementTable")'>Add Management</button>
                    </div>
                </div>
            </div>
        </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFive">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                        Part E: Particulars of Finance
                    </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        
                        <div class="card p-3 mb-3 bg-light">
                            <label class="fw-bold">Does the Company have history of bankruptcy?</label>
                            <div id="BankruptcyGroup" data-field="bankruptHistory">
                                <?php $bk = $RegistrationRow['bankruptHistory']; ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="bankruptcy" value="yes" <?= $bk=='yes'?'checked':'' ?> disabled> <label>Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="bankruptcy" value="no" <?= $bk=='no'?'checked':'' ?> disabled> <label>No</label>
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="small text-muted">If yes, description:</label>
                                <div class="input-group">
                                    <input type="text" id="description" class="form-control" data-field="description" value="<?= htmlspecialchars($RegistrationRow['description']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'description', 'RegistrationForm')">Edit Description</button>
                                    <button class="btn btn-secondary ms-2" onclick="editRadioGroup(this, 'BankruptcyGroup', 'RegistrationForm')">Toggle Yes/No</button>
                                </div>
                            </div>
                        </div>

                        <p class="section-desc">Please provide <u>3 most recent</u> annual Audited Financial Statements...</p>

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
                                        <td rowspan="3" class="fw-bold bg-light">Auditors</td>
                                        <td><label>Name</label></td>
                                        <td><div class="input-group"><input type="text" id="auditorCompanyName" class="form-control" data-field="auditorCompanyName" value="<?= htmlspecialchars($RegistrationRow['auditorCompanyName']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'auditorCompanyName', 'RegistrationForm')">Edit</button></div></td>
                                        <td><label>Name</label></td>
                                        <td><div class="input-group"><input type="text" id="auditorName" class="form-control" data-field="auditorName" value="<?= htmlspecialchars($RegistrationRow['auditorName']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'auditorName', 'RegistrationForm')">Edit</button></div></td>
                                        <td rowspan="3"><div class="input-group"><input type="number" id="auditorYearOfService" class="form-control" data-field="auditorYearOfService" value="<?= htmlspecialchars($RegistrationRow['auditorYearOfService']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'auditorYearOfService', 'RegistrationForm')">Edit</button></div></td>
                                    </tr>
                                    <tr>
                                        <td rowspan="2"><label>Address</label></td>
                                        <td rowspan="2"><div class="input-group h-100"><textarea id="auditorCompanyAddress" class="form-control" data-field="auditorCompanyAddress" readonly><?= htmlspecialchars($RegistrationRow['auditorCompanyAddress']) ?></textarea><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'auditorCompanyAddress', 'RegistrationForm')">Edit</button></div></td>
                                        <td><label>Email</label></td>
                                        <td><div class="input-group"><input type="text" id="auditorEmail" class="form-control" data-field="auditorEmail" value="<?= htmlspecialchars($RegistrationRow['auditorEmail']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'auditorEmail', 'RegistrationForm')">Edit</button></div></td>
                                    </tr>
                                    <tr>
                                        <td><label>Phone</label></td>
                                        <td><div class="input-group"><input type="text" id="auditorPhone" class="form-control" data-field="auditorPhone" value="<?= htmlspecialchars($RegistrationRow['auditorPhone']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'auditorPhone', 'RegistrationForm')">Edit</button></div></td>
                                    </tr>

                                    <tr>
                                        <td rowspan="3" class="fw-bold bg-light">Advocates & Solicitors</td>
                                        <td><label>Name</label></td>
                                        <td><div class="input-group"><input type="text" id="advocatesCompanyName" class="form-control" data-field="advocatesCompanyName" value="<?= htmlspecialchars($RegistrationRow['advocatesCompanyName']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'advocatesCompanyName', 'RegistrationForm')">Edit</button></div></td>
                                        <td><label>Name</label></td>
                                        <td><div class="input-group"><input type="text" id="advocatesName" class="form-control" data-field="advocatesName" value="<?= htmlspecialchars($RegistrationRow['advocatesName']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'advocatesName', 'RegistrationForm')">Edit</button></div></td>
                                        <td rowspan="3"><div class="input-group"><input type="number" id="advocatesYearOfService" class="form-control" data-field="advocatesYearOfService" value="<?= htmlspecialchars($RegistrationRow['advocatesYearOfService']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'advocatesYearOfService', 'RegistrationForm')">Edit</button></div></td>
                                    </tr>
                                    <tr>
                                        <td rowspan="2"><label>Address</label></td>
                                        <td rowspan="2"><div class="input-group h-100"><textarea id="advocatesCompanyAddress" class="form-control" data-field="advocatesCompanyAddress" readonly><?= htmlspecialchars($RegistrationRow['advocatesCompanyAddress']) ?></textarea><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'advocatesCompanyAddress', 'RegistrationForm')">Edit</button></div></td>
                                        <td><label>Email</label></td>
                                        <td><div class="input-group"><input type="text" id="advocatesEmail" class="form-control" data-field="advocatesEmail" value="<?= htmlspecialchars($RegistrationRow['advocatesEmail']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'advocatesEmail', 'RegistrationForm')">Edit</button></div></td>
                                    </tr>
                                    <tr>
                                        <td><label>Phone</label></td>
                                        <td><div class="input-group"><input type="text" id="advocatesPhone" class="form-control" data-field="advocatesPhone" value="<?= htmlspecialchars($RegistrationRow['advocatesPhone']) ?>" readonly><button class="btn btn-sm btn-outline-primary" onclick="editField(this, 'advocatesPhone', 'RegistrationForm')">Edit</button></div></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="mt-4">Bank Information</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="bankTable">
                                <thead class="table-dark"><tr><th>Bank Name</th><th>Address</th><th>Swift Code</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php while($row = $BankTable->fetch_assoc()): ?>
                                    <tr data-id="<?= $row['bankID'] ?>">
                                        <td><input type="text" data-field="bankName" class="form-control" value="<?= htmlspecialchars($row['bankName']) ?>" readonly></td>
                                        <td><input type="text" data-field="bankAddress" class="form-control" value="<?= htmlspecialchars($row['bankAddress']) ?>" readonly></td>
                                        <td><input type="text" data-field="swiftCode" class="form-control" value="<?= htmlspecialchars($row['swiftCode']) ?>" readonly></td>
                                        <td><button class="btn btn-sm btn-outline-primary" onclick="editTableRow(this, 'Bank', 'bankID')">Edit</button><button class="btn btn-sm btn-danger ms-1" onclick="deleteEditRow(this, 'Bank', 'bankID')">Delete</button></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <button class="btn btn-success mt-3" onclick="addEditShareholders('Bank', 'bankTable')">Add Bank</button>
                        </div>

                        <p class="section-desc mt-3">Please include the last 6 months Bank Statement.</p>

                        <h6 class="mt-4">Nett Worth and Working Capital</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <?php $currentYear=date("Y"); for($i=1; $i<=3; $i++) echo "<th>".($currentYear-$i)." (RM)</th>"; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $fields = ['totalLiabilities'=>'Total Liabilities', 'totalAssets'=>'Total Assets', 'netWorth'=>'Net Worth (Assets - Liabilities)', 'workingCapital'=>'Working Capital'];
                                    foreach($fields as $dbField => $label): ?>
                                    <tr>
                                        <td class="text-start"><?= $label ?></td>
                                        <?php for($i=1; $i<=3; $i++): 
                                            $y = $currentYear - $i;
                                            $val = $NetWorthData[$y][$dbField] ?? '';
                                            // Row ID might be missing if year not inserted, handled by Upsert Logic in JS
                                            $rid = $NetWorthData[$y]['networthID'] ?? ''; 
                                        ?>
                                        <td data-id="<?= $rid ?>" data-year="<?= $y ?>">
                                            <div class="input-group input-group-sm">
                                                <input type="number" data-field="<?= $dbField ?>" class="form-control" value="<?= htmlspecialchars($val) ?>" readonly>
                                                <button class="btn btn-outline-secondary" onclick="editSpecialRow(this, 'NetWorth', 'networthID')">Edit</button>
                                            </div>
                                        </td>
                                        <?php endfor; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <h6 class="mt-4">Credit Facilities</h6>
                        <p>Does the Company have any credit facilities?</p>
                        <div class="card p-3 mb-3 bg-light">
                            <div id="CreditRadioGroup" data-field="creditFacilitiesStatus"> 
                                <?php $cf = $RegistrationRow['creditFacilitiesStatus']; ?>
                                <label class="me-3"><input type="radio" name="CreditFacilitiesStatus" value="Yes" <?= $cf === 'Yes' ? 'checked' : '' ?> disabled > Yes</label>
                            <label><input type="radio" name="CreditFacilitiesStatus" value="No" <?= $cf === 'No' ? 'checked' : '' ?> disabled><label> No</label>
                            </div>
                        </div>

                        <div id="CreditTableContainer">
                            <div class="table-responsive">
                                <table id="CreditTable" class="table table-bordered table-centered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Type of Credit</th>
                                            <th>Institution/Bank</th>
                                            <th>Total Amount (RM)</th>
                                            <th>Unutilised Amount (RM)</th>
                                            <th>Expiry Date</th>
                                            <th>As At Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $CreditFacilities->fetch_assoc()): ?>
                                        <tr data-id="<?= $row['facilityID'] ?>">
                                            <td><input type="text" data-field="typeOfCreditFacilities" class="form-control" value="<?= htmlspecialchars($row['typeOfCreditFacilities']) ?>" readonly></td>
                                            <td><input type="text" data-field="financialInstitution" class="form-control" value="<?= htmlspecialchars($row['financialInstitution']) ?>" readonly></td>
                                            <td><input type="number" data-field="totalAmount" class="form-control" value="<?= htmlspecialchars($row['totalAmount']) ?>" readonly></td>
                                            <td><input type="number" data-field="unutilisedAmountCurrentlyAvailable" class="form-control" value="<?= htmlspecialchars($row['unutilisedAmountCurrentlyAvailable']) ?>" readonly></td>
                                            <td><input type="date" data-field="expiryDate" class="form-control" value="<?= ($row['expiryDate'] == '0000-00-00' ? '' : htmlspecialchars($row['expiryDate'])) ?>" readonly></td>
                                            <td><input type="date" data-field="asAtDate" class="form-control" value="<?= ($row['asAtDate'] == '0000-00-00' ? '' : htmlspecialchars($row['asAtDate'])) ?>" readonly></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editTableRow(this, 'CreditFacilities', 'facilityID')">Edit</button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteEditRow(this, 'CreditFacilities', 'facilityID')">Delete</button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <button class="btn btn-success mt-3" onclick="addEditShareholders('CreditFacilities', 'CreditTable')">Add Credit Facility</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSix">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                        Part F: Contractor's Technical Capability
                    </button>
                </h2>
                <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>CIDB Grade</label>
                                <div class="input-group">
                                    <input type="text" id="cidb" class="form-control" data-field="cidb" value="<?= htmlspecialchars($RegistrationRow['cidb']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'cidb', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Validity Date</label>
                                <div class="input-group">
                                    <input type="date" id="cidbVal" class="form-control" data-field="cidbValidationTill" value="<?= htmlspecialchars($RegistrationRow['cidbValidationTill']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'cidbVal', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                        </div>

                        <div class="card p-3 my-3 bg-light">
                            <label class="fw-bold">Trade</label>
                            <div id="TradeGroup" data-field="trade">
                                <?php $trade = $RegistrationRow['trade']; ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="CIDBTrade" value="ISP" <?= $trade=='ISP'?'checked':'' ?> disabled> <label>ISP</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="CIDBTrade" value="OSP" <?= $trade=='OSP'?'checked':'' ?> disabled> <label>OSP</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="CIDBTrade" value="O&M" <?= $trade=='O&M'?'checked':'' ?> disabled> <label>O&M</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="CIDBTrade" value="M&E" <?= $trade=='M&E'?'checked':'' ?> disabled> <label>M&E</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="CIDBTrade" value="Others" <?= (!in_array($trade, ['ISP','OSP','O&M','M&E']) && !empty($trade))?'checked':'' ?> disabled> <label>Others</label>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-secondary mt-2 w-25" onclick="editRadioGroup(this, 'TradeGroup', 'RegistrationForm')">Edit Trade</button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card p-3 h-100 bg-light">
                                    <label class="fw-bold mb-2">Value of Similar Project (Last 5 Years)</label>
                                    <div id="SimilarProjGroup" data-field="valueOfSimilarProject">
                                        <?php $vSim = $RegistrationRow['valueOfSimilarProject']; ?>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="SimilarProject" value=">15M" <?= $vSim=='>15M'?'checked':'' ?> disabled> <label>> RM15M</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="SimilarProject" value="10M-14.9M" <?= $vSim=='10M-14.9M'?'checked':'' ?> disabled> <label>RM10M - RM14.9M</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="SimilarProject" value="5M-9.9M" <?= $vSim=='5M-9.9M'?'checked':'' ?> disabled> <label>RM5M - RM9.9M</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="SimilarProject" value="1M-4.9M" <?= $vSim=='1M-4.9M'?'checked':'' ?> disabled> <label>RM1M - RM4.9M</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="SimilarProject" value="<1M" <?= $vSim=='<1M'?'checked':'' ?> disabled> <label>< RM1M</label></div>
                                    </div>
                                    <button class="btn btn-sm btn-secondary mt-2" onclick="editRadioGroup(this, 'SimilarProjGroup', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card p-3 h-100 bg-light">
                                    <label class="fw-bold mb-2">Value of Current On-Going Project</label>
                                    <div id="CurrentProjGroup" data-field="valueOfCurrentProject">
                                        <?php $vCur = $RegistrationRow['valueOfCurrentProject']; ?>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="CurrentProjectVal" value=">5M" <?= $vCur=='>5M'?'checked':'' ?> disabled> <label>> RM5M</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="CurrentProjectVal" value="2M-4.9M" <?= $vCur=='2M-4.9M'?'checked':'' ?> disabled> <label>RM2M - RM4.9M</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="CurrentProjectVal" value="0.5M-1.9M" <?= $vCur=='0.5M-1.9M'?'checked':'' ?> disabled> <label>RM0.5M - RM1.9M</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="CurrentProjectVal" value="<0.5M" <?= $vCur=='<0.5M'?'checked':'' ?> disabled> <label>< RM0.5M</label></div>
                                    </div>
                                    <button class="btn btn-sm btn-secondary mt-2" onclick="editRadioGroup(this, 'CurrentProjGroup', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                        </div>

                        <div class="my-3">
                            <label class="form-label fw-bold">Experience in the Industry (Years)</label>
                            <div class="input-group">
                                <input type="number" id="yearsOfExperienceInIndustry" class="form-control" data-field="yearsOfExperienceInIndustry" value="<?= htmlspecialchars($RegistrationRow['yearsOfExperienceInIndustry'] ?? '') ?>" readonly>
                                <button class="btn btn-outline-primary" onclick="editField(this, 'yearsOfExperienceInIndustry', 'RegistrationForm')">Edit</button>
                            </div>
                        </div>

                        <h6 class="mt-4">List of Plant, Machinery and Equipment</h6>
                        <p class="section-desc">The Contractor is required to complete the form by listing all plant and machinery... provide valid calibration certificates...</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light text-center">
                                    <tr><th>Equipment Type</th><th>Qty</th><th>Brand/Model</th><th>Rating</th><th>Ownership</th><th>Year Mfg</th><th>Reg No</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $eqTypes = [1=>'Bobcat/JCB', 2=>'HDD Equipment', 3=>'Splicing', 4=>'OPM', 5=>'OTDR', 6=>'Test Gear'];
                                    foreach($eqTypes as $id => $name):
                                        $d = $EquipmentData[$id] ?? [];
                                        $rid = $d['equipmentRecordID'] ?? '';
                                    ?>
                                    <tr data-id="<?= $rid ?>" data-type-id="<?= $id ?>">
                                        <td class="fw-bold"><?= $name ?></td>
                                        <td><input type="number" data-field="quantity" class="form-control form-control-sm" value="<?= $d['quantity']??'' ?>" readonly></td>
                                        <td><input type="text" data-field="brand" class="form-control form-control-sm" value="<?= $d['brand']??'' ?>" readonly></td>
                                        <td><input type="text" data-field="rating" class="form-control form-control-sm" value="<?= $d['rating']??'' ?>" readonly></td>
                                        <td><input type="text" data-field="ownership" class="form-control form-control-sm" value="<?= $d['ownership']??'' ?>" readonly></td>
                                        <td><input type="date" data-field="yearsOfManufacture" class="form-control form-control-sm" value="<?= $d['yearsOfManufacture']??'' ?>" readonly></td>
                                        <td><input type="text" data-field="registrationNo" class="form-control form-control-sm" value="<?= $d['registrationNo']??'' ?>" readonly></td>
                                        <td><button class="btn btn-sm btn-outline-secondary" onclick="editSpecialRow(this, 'Equipment', 'equipmentRecordID')">Edit</button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>


                        <h6 class="mt-5">List of Site Team and Site Staff</h6>
                        <div class="table-responsive">
                            <table id="StaffTeamTable" class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Qualification</th>
                                        <th>Exp (Yrs)</th>
                                        <th>Status</th>
                                        <th>Skills</th>
                                        <th>Certification</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $StaffTeamTable->fetch_assoc()): ?>
                                    <tr data-id="<?= $row['staffID'] ?>"> 
                                        <td><input type="number" data-field="staffNo" class="form-control" value="<?= htmlspecialchars($row['staffNo']) ?>" readonly></td>
                                        <td><input type="text" data-field="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" readonly></td>
                                        <td><input type="text" data-field="designation" class="form-control" value="<?= htmlspecialchars($row['designation']) ?>" readonly></td>
                                        <td><input type="text" data-field="qualification" class="form-control" value="<?= htmlspecialchars($row['qualification']) ?>" readonly></td>
                                        <td><input type="number" data-field="yearsOfExperience" class="form-control" value="<?= htmlspecialchars($row['yearsOfExperience']) ?>" readonly></td>
                                        <td><input type="text" data-field="employmentStatus" class="form-control" value="<?= htmlspecialchars($row['employmentStatus']) ?>" readonly></td>
                                        <td><input type="text" data-field="skills" class="form-control" value="<?= htmlspecialchars($row['skills']) ?>" readonly></td>
                                        <td><input type="text" data-field="relevantCertification" class="form-control" value="<?= htmlspecialchars($row['relevantCertification']) ?>" readonly></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary mb-1" onclick="editTableRow(this, 'Staff', 'staffID')">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEditRow(this, 'Staff', 'staffID')">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <button class="btn btn-success mt-3" onclick="addEditShareholders('Staff', 'StaffTeamTable')">Add Staff</button>
                        </div>

                        <h6 class="mt-5">Project Track Record (Last 5 Years)</h6>
                        <div class="table-responsive">
                            <table id="ProjectRecordTable" class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Title</th>
                                        <th>Nature</th>
                                        <th>Location</th>
                                        <th>Client</th>
                                        <th>Value</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $ProjectRecordTable->fetch_assoc()): ?>
                                    <tr data-id="<?= $row['projectRecordID'] ?>">
                                        <td><input type="number" data-field="projectRecordNo" class="form-control" value="<?= htmlspecialchars($row['projectRecordNo']) ?>" readonly></td>
                                        <td><input type="text" data-field="projectTitle" class="form-control" value="<?= htmlspecialchars($row['projectTitle']) ?>" readonly></td>
                                        <td><input type="text" data-field="projectNature" class="form-control" value="<?= htmlspecialchars($row['projectNature']) ?>" readonly></td>
                                        <td><input type="text" data-field="location" class="form-control" value="<?= htmlspecialchars($row['location']) ?>" readonly></td>
                                        <td><input type="text" data-field="clientName" class="form-control" value="<?= htmlspecialchars($row['clientName']) ?>" readonly></td>
                                        <td><input type="number" data-field="projectValue" class="form-control" value="<?= htmlspecialchars($row['projectValue']) ?>" readonly></td>
                                        <td><input type="date" data-field="commencementDate" class="form-control" value="<?= ($row['commencementDate'] == '0000-00-00' ? '' : htmlspecialchars($row['commencementDate'])) ?>" readonly></td>
                                        <td><input type="date" data-field="completionDate" class="form-control" value="<?= ($row['completionDate'] == '0000-00-00' ? '' : htmlspecialchars($row['completionDate'])) ?>" readonly></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary mb-1" onclick="editTableRow(this, 'ProjectTrackRecord', 'projectRecordID')">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEditRow(this, 'ProjectTrackRecord', 'projectRecordID')">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <button class="btn btn-success mt-3" onclick="addEditShareholders('ProjectTrackRecord', 'ProjectRecordTable')">Add Project Record</button>
                        </div>
                        <h6 class="mt-5">Current Projects</h6>
                        <div class="table-responsive">
                            <table id="CurrentProjTable" class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Title</th>
                                        <th>Nature</th>
                                        <th>Location</th>
                                        <th>Client</th>
                                        <th>Value</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Progress (%)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $CurrentProjTable->fetch_assoc()): ?>
                                    <tr data-id="<?= $row['currentProjectID'] ?>">
                                        <td><input type="number" data-field="currentProjectRecordNo" class="form-control" value="<?= htmlspecialchars($row['currentProjectRecordNo']) ?>" readonly></td>
                                        <td><input type="text" data-field="projectTitle" class="form-control" value="<?= htmlspecialchars($row['projectTitle']) ?>" readonly></td>
                                        <td><input type="text" data-field="projectNature" class="form-control" value="<?= htmlspecialchars($row['projectNature']) ?>" readonly></td>
                                        <td><input type="text" data-field="location" class="form-control" value="<?= htmlspecialchars($row['location']) ?>" readonly></td>
                                        <td><input type="text" data-field="clientName" class="form-control" value="<?= htmlspecialchars($row['clientName']) ?>" readonly></td>
                                        <td><input type="number" data-field="projectValue" class="form-control" value="<?= htmlspecialchars($row['projectValue']) ?>" readonly></td>
                                        <td><input type="date" data-field="commencementDate" class="form-control" value="<?= ($row['commencementDate'] == '0000-00-00' ? '' : htmlspecialchars($row['commencementDate'])) ?>" readonly></td>
                                        <td><input type="date" data-field="completionDate" class="form-control" value="<?= ($row['completionDate'] == '0000-00-00' ? '' : htmlspecialchars($row['completionDate'])) ?>" readonly></td>
                                        <td><input type="number" data-field="progressOfTheWork" class="form-control" value="<?= htmlspecialchars($row['progressOfTheWork']) ?>" readonly></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary mb-1" onclick="editTableRow(this, 'CurrentProject', 'currentProjectID')">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEditRow(this, 'CurrentProject', 'currentProjectID')">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <button class="btn btn-success mt-3" onclick="addEditShareholders('CurrentProject', 'CurrentProjTable')">Add Current Project</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSeven">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                        Part G: Contact Person
                    </button>
                </h2>
                <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                <?php while($contact = $ContactsResult->fetch_assoc()): ?>
                    <div class="card p-3 mb-3 ">
                        <h6 class="text-primary fw-bold"><?= htmlspecialchars($contact['contactStatus'] ?? 'Contact') ?> Person</h6>
                        <div class="row g-3" data-id="<?= $contact['contactID'] ?>"> <div class="col-md-6">
                                <label>Name</label>
                                <div class="input-group">
                                    <input type="text" data-field="contactPersonName" class="form-control" value="<?= htmlspecialchars($contact['contactPersonName']) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Department</label>
                                <div class="input-group">
                                    <input type="text" data-field="department" class="form-control" value="<?= htmlspecialchars($contact['department']) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Telephone</label>
                                <div class="input-group">
                                    <input type="text" data-field="telephoneNumber" class="form-control" value="<?= htmlspecialchars($contact['telephoneNumber']) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <div class="input-group">
                                    <input type="text" data-field="emailAddress" class="form-control" value="<?= htmlspecialchars($contact['emailAddress']) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button class="btn btn-outline-primary btn-sm" onclick="editTableRow(this, 'Contacts', 'contactID')">Edit Contact</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
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
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTen">
                    Part J: Information Verification
                </button>
            </h2>
            <div id="collapseTen" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <p class="legal-text">By signing this document, the undersigned... certify the following...</p>
                    
                    <div class="verification-container">
                        <p><strong>The above information had been verified by:</strong></p>
                        <div class="row">
                            <div class="col-md-6 mt-4">
                                <div class="signature-box"></div>
                                <p class="small text-muted">(Authorised Signature and Company Stamp)<br>Chairman/Director/Company Secretary</p>
                            </div>
                            <div class="col-md-6">
                                <label>Name:</label>
                                <div class="input-group mb-2">
                                    <input type="text" id="verifierName" class="form-control" data-field="verifierName" value="<?= htmlspecialchars($RegistrationRow['verifierName']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'verifierName', 'RegistrationForm')">Edit</button>
                                </div>
                                <label>Designation:</label>
                                <div class="input-group mb-2">
                                    <input type="text" id="verifierDesignation" class="form-control" data-field="verifierDesignation" value="<?= htmlspecialchars($RegistrationRow['verifierDesignation']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'verifierDesignation', 'RegistrationForm')">Edit</button>
                                </div>
                                <label>Date:</label>
                                <div class="input-group mb-2">
                                    <input type="date" id="dateOfVerification" class="form-control" data-field="dateOfVerification" value="<?= htmlspecialchars($RegistrationRow['dateOfVerification']) ?>" readonly>
                                    <button class="btn btn-outline-primary" onclick="editField(this, 'dateOfVerification', 'RegistrationForm')">Edit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div> <div class="d-grid gap-2 mt-5">
            <a href="VendorHomepage.php" class="btn btn-lg btn-primary">Return to Homepage</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="VendorUpdateScript.js"></script> </body>
</html>