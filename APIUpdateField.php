<?php
// APIUpdateField.php
require_once "config.php";
session_start();

if (!isset($_POST['field'], $_POST['value'], $_POST['registrationFormID'])) {
    http_response_code(400);
    exit("Missing data");
}

$field = $_POST['field'];
$value = $_POST['value'];
$formID = $_POST['registrationFormID'];

// --- FIX: Extended Whitelist to include ALL form fields ---
$allowed = [
    // Part A
    'companyName', 'telephoneNumber', 'otherNames', 'taxRegistrationNumber', 
    'faxNo', 'oldCompanyRegistrationNumber', 'emailAddress', 'newCompanyRegistrationNumber', 
    'countryOfIncorporation', 'dateOfIncorporation', 'natureAndLineOfBusiness', 
    'registeredAddress', 'correspondenceAddress', 'companyOrganisation', 
    'typeOfOrganisation', 'website', 'branch', 'authorisedCapital', 'paidUpCapital', 
    
    // Part B
    'parentCompany', 'parentCompanyCountry', 'ultimateParentCompany', 'ultimateParentCompanyCountry', 
    
    // Part E (Finance & Legal)
    'bankruptHistory', 'description', 
    'auditorCompanyName', 'auditorCompanyAddress', 'auditorName', 'auditorEmail', 'auditorPhone', 'auditorYearOfService',
    'advocatesCompanyName', 'advocatesCompanyAddress', 'advocatesName', 'advocatesEmail', 'advocatesPhone', 'advocatesYearOfService',
    
    // Part F (Technical)
    'cidbGrade', 'cidbSpecialization', 'cidbValidationTill', 'trade', 'otherTradeDetails', 'valueOfSimilarProject', 
    'valueOfCurrentProject', 'yearsOfExperienceInIndustry',
    
    // Part J
    'verifierName', 'verifierDesignation', 'dateOfVerification'
];

if (!in_array($field, $allowed)) {
    http_response_code(403);
    exit("Error: Field '$field' is not allowed to be edited via this API.");
}

// Server-side guard: only allow vendors to edit when form status is 'rejected'. Admins may always edit.
$role = $_SESSION['role'] ?? '';
// fetch current status
$sstmt = $conn->prepare("SELECT newCompanyRegistrationNumber, status FROM registrationform WHERE registrationFormID = ? LIMIT 1");
$sstmt->bind_param('i', $formID);
$sstmt->execute();
$sres = $sstmt->get_result();
$srow = $sres ? $sres->fetch_assoc() : null;
$currentStatus = strtolower($srow['status'] ?? '');
// If user is vendor, only allow update when status is 'rejected'
if ($role === 'vendor' && $currentStatus !== 'rejected') {
    http_response_code(403);
    exit("Editing is locked. Only editable after admin rejection.");
}

$stmt = $conn->prepare("UPDATE registrationform SET `$field` = ? WHERE registrationFormID = ?");
$stmt->bind_param("si", $value, $formID);

if ($stmt->execute()) {
    echo "Updated";
} else {
    http_response_code(500);
    echo "DB Error: " . $stmt->error;
}
?>