<?php
// UpdateTableRow.php
require_once "config.php";
if ($conn->connect_error) { http_response_code(500); exit("DB connection failed"); }

$required = ['field', 'value', 'registrationFormID', 'Table', 'idName'];
foreach ($required as $key) { if (!isset($_POST[$key])) exit("Missing parameter: $key"); }

$field  = $_POST['field'];
$value  = trim($_POST['value']);
$formID = $_POST['registrationFormID'];
$table  = $_POST['Table'];
$rowId  = $_POST['rowId']; 
$idName = $_POST['idName']; 

// Configuration: Whitelist
$AllowedConfig = [
    'Shareholders' => ['table' => 'shareholders', 'fields' => ['companyShareholderID', 'name', 'nationality', 'address', 'sharePercentage']],
    'Equipment' => ['table' => 'equipment', 'fields' => ['quantity', 'brand', 'rating', 'ownership', 'yearsOfManufacture', 'registrationNo']],
    'NetWorth' => ['table' => 'nettworth', 'fields' => ['totalLiabilities', 'totalAssets', 'netWorth', 'workingCapital']],
    'Contacts' => ['table' => 'contacts', 'fields' => ['contactPersonName', 'department', 'telephoneNumber', 'emailAddress']],
    'DirectorAndSecretary' => ['table' => 'directorandsecretary','fields' => ['nationality', 'name', 'position', 'appointmentDate', 'dob']],
    'Management' => ['table' => 'management','fields' => ['nationality', 'name', 'position', 'yearsInPosition', 'yearsInRelatedField']],
    'Bank' => ['table' => 'bank','fields' => ['bankName', 'bankAddress', 'swiftCode']],
    'CreditFacilities' => ['table' => 'creditfacilities','fields' => ['typeOfCreditFacilities', 'financialInstitution', 'totalAmount', 'expiryDate', 'unutilisedAmountCurrentlyAvailable', 'asAtDate']],
    'Staff' => ['table' => 'staff','fields' => ['staffNo', 'name', 'designation', 'qualification', 'yearsOfExperience', 'employmentStatus', 'skills', 'relevantCertification']],
    'ProjectTrackRecord' => ['table' => 'projecttrackrecord','fields' => ['projectRecordNo', 'projectTitle', 'projectNature', 'location', 'clientName', 'projectValue', 'commencementDate', 'completionDate']],
    'CurrentProject' => ['table' => 'currentproject','fields' => ['currentProjectRecordNo', 'projectTitle', 'projectNature', 'location', 'clientName', 'projectValue', 'commencementDate', 'completionDate', 'progressOfTheWork']],
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