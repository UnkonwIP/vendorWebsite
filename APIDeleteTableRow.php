<?php
// APIDeleteTableRow.php
require_once "config.php";
if ($conn->connect_error) exit("Connection failed");

$id = $_POST['ID'] ?? '';
$idName = $_POST['idName'] ?? '';
$formID = $_POST['registrationFormID'] ?? '';
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