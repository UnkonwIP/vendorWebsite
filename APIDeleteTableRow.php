<?php
// APIDeleteTableRow.php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/config.php';
if ($conn->connect_error) exit("Connection failed");

$id = $_POST['ID'] ?? '';
$idName = $_POST['idName'] ?? '';
$formID = $_POST['registrationFormID'] ?? '';

if (empty($formID) || !is_numeric($formID)) exit("Missing registrationFormID");

// Permission: allow admins/admin_head, otherwise verify vendor owns the form
$role = $_SESSION['role'] ?? '';
$currentAccount = isset($_SESSION['accountID']) ? intval($_SESSION['accountID']) : 0;
if (!in_array($role, ['admin', 'admin_head'], true)) {
    $stmt = $conn->prepare("SELECT newCompanyRegistrationNumber FROM registrationform WHERE registrationFormID = ? LIMIT 1");
    $stmt->bind_param("i", $formID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $crn = $row['newCompanyRegistrationNumber'] ?? '';
    if (empty($crn)) exit("Forbidden");
    $stmt = $conn->prepare("SELECT accountID FROM vendoraccount WHERE newCompanyRegistrationNumber = ? LIMIT 1");
    $stmt->bind_param("s", $crn);
    $stmt->execute();
    $owner = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $ownerID = isset($owner['accountID']) ? intval($owner['accountID']) : 0;
    if ($ownerID !== $currentAccount) exit("Forbidden");
}
$tableAlias = $_POST['Table'] ?? '';

// Map Frontend Alias -> SQL Table
$TableMap = [
    'Shareholders' => 'shareholders',
    'DirectorAndSecretary' => 'directorandsecretary',
    'Management' => 'management',
    'Bank' => 'bank',
    'Staff' => 'staff',
    'ProjectTrackRecord' => 'projecttrackrecord',
    'CurrentProject' => 'currentproject',
    'Contacts' => 'contacts',
    'CreditFacilities' => 'creditfacilities'
];

if (!isset($TableMap[$tableAlias])) exit("Invalid Table");
$dbTable = $TableMap[$tableAlias];

// Security: ID name must be alphanumeric
if (!preg_match('/^[a-zA-Z0-9_]+$/', $idName)) exit("Invalid ID Column");

$stmt = $conn->prepare("DELETE FROM `$dbTable` WHERE `$idName` = ? AND `registrationFormID` = ?");
$stmt->bind_param("ii", $id, $formID);

if ($stmt->execute()) echo "Deleted";
else echo "Error: " . $stmt->error;
?>