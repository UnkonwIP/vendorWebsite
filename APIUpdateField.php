<?php
include "config.php";

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
    'cidb', 'cidbValidationTill', 'trade', 'valueOfSimilarProject', 
    'valueOfCurrentProject', 'yearsOfExperienceInIndustry',
    
    // Part J
    'verifierName', 'verifierDesignation', 'dateOfVerification'
];

if (!in_array($field, $allowed)) {
    http_response_code(403);
    exit("Error: Field '$field' is not allowed to be edited via this API.");
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