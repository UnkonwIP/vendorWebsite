<?php
// UpdateTableRow.php
require_once __DIR__ . '/session_bootstrap.php';
require_once "config.php";
if ($conn->connect_error) { http_response_code(500); exit("DB connection failed"); }

// Require expected params
$required = ['field', 'value', 'registrationFormID', 'Table', 'idName'];
foreach ($required as $key) { if (!isset($_POST[$key])) exit("Missing parameter: $key"); }

$field  = $_POST['field'];
$value  = trim($_POST['value']);
$formID = $_POST['registrationFormID'];
$formID = is_numeric($formID) ? intval($formID) : 0;

if (empty($formID)) exit("Missing registrationFormID");

// Permission: only admin/admin_head or owning vendor may update rows for this form
$role = $_SESSION['role'] ?? '';
$currentAccount = isset($_SESSION['accountID']) ? intval($_SESSION['accountID']) : 0;
if (!in_array($role, ['admin','admin_head'], true)) {
    $pstmt = $conn->prepare("SELECT newCompanyRegistrationNumber FROM registrationform WHERE registrationFormID = ? LIMIT 1");
    $pstmt->bind_param("i", $formID);
    $pstmt->execute();
    $prow = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();
    $crn = $prow['newCompanyRegistrationNumber'] ?? '';
    if (empty($crn)) exit("Forbidden");
    $pstmt = $conn->prepare("SELECT accountID FROM vendoraccount WHERE newCompanyRegistrationNumber = ? LIMIT 1");
    $pstmt->bind_param("s", $crn);
    $pstmt->execute();
    $owner = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();
    $ownerID = isset($owner['accountID']) ? intval($owner['accountID']) : 0;
    if ($ownerID !== $currentAccount) exit("Forbidden");
}
$table  = $_POST['Table'];
$rowId  = $_POST['rowId']; 
$idName = $_POST['idName']; 

// Configuration: Whitelist
$AllowedConfig = [
    'Shareholders' => ['table' => 'shareholders', 'fields' => ['name', 'nationality', 'address', 'sharePercentage']],
    'Equipment' => ['table' => 'equipment', 'fields' => ['quantity', 'brand', 'rating', 'ownership', 'yearsOfManufacture', 'registrationNo']],
    'NetWorth' => ['table' => 'nettworth', 'fields' => ['totalLiabilities', 'totalAssets', 'netWorth', 'workingCapital']],
    'Contacts' => ['table' => 'contacts', 'fields' => ['contactPersonName', 'department', 'telephoneNumber', 'emailAddress']],
    'DirectorAndSecretary' => ['table' => 'directorandsecretary','fields' => ['nationality', 'name', 'position', 'appointmentDate', 'dob']],
    'Management' => ['table' => 'management','fields' => ['nationality', 'name', 'position', 'yearsInPosition', 'yearsInRelatedField']],
    'Bank' => ['table' => 'bank','fields' => ['bankName', 'bankAddress', 'swiftCode']],
    'CreditFacilities' => ['table' => 'creditfacilities','fields' => ['typeOfCreditFacilities', 'financialInstitution', 'totalAmount', 'expiryDate', 'unutilisedAmountCurrentlyAvailable', 'asAtDate']],
    'Staff' => ['table' => 'staff','fields' => ['name', 'designation', 'qualification', 'yearsOfExperience', 'employmentStatus', 'skills', 'relevantCertification']],
    'ProjectTrackRecord' => ['table' => 'projecttrackrecord','fields' => ['projectTitle', 'projectNature', 'location', 'clientName', 'projectValue', 'commencementDate', 'completionDate']],
    'CurrentProject' => ['table' => 'currentproject','fields' => ['projectTitle', 'projectNature', 'location', 'clientName', 'projectValue', 'commencementDate', 'completionDate', 'progressOfTheWork']],
];

if (!isset($AllowedConfig[$table])) exit("Invalid table");
$dbTable = $AllowedConfig[$table]['table'];
if (!in_array($field, $AllowedConfig[$table]['fields'])) exit("Invalid field");

// === UPSERT LOGIC (INSERT IF EMPTY ROW ID) ===
if (empty($rowId)) {
    // Equipment Insert (needs type)
    if ($table === 'Equipment' && !empty($_POST['extraTypeId'])) {
        $typeID = $_POST['extraTypeId'];
        $stmt = $conn->prepare("INSERT INTO equipment (registrationFormID, equipmentID, $field) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $formID, $typeID, $value);
        if ($stmt->execute()) echo "INSERTED:" . $conn->insert_id;
        else echo "Insert Error: " . $stmt->error;
        exit;
    }
    // NetWorth Insert (needs year)
    if ($table === 'NetWorth' && !empty($_POST['extraYear'])) {
        $year = $_POST['extraYear'];
        $stmt = $conn->prepare("INSERT INTO nettworth (registrationFormID, yearOf, $field) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $formID, $year, $value);
        if ($stmt->execute()) echo "INSERTED:" . $conn->insert_id;
        else echo "Insert Error: " . $stmt->error;
        exit;
    }
    exit("Error: No Row ID provided and not a valid Upsert context.");
}

// === STANDARD UPDATE ===
// We perform a safe UPDATE using the PK ($idName) and FK ($formID)
$sql = "UPDATE `$dbTable` SET `$field` = ? WHERE `$idName` = ? AND `registrationFormID` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $value, $rowId, $formID);

if ($stmt->execute()) {
    echo ($stmt->affected_rows > 0) ? "Saved" : "No changes";
} else {
    echo "SQL Error: " . $stmt->error;
}
?>