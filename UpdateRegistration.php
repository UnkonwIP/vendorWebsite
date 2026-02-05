<?php
$conn = new mysqli('localhost', 'root', '', 'vendor_information');
if (!isset($_POST['field'], $_POST['value'], $_POST['registrationFormID'])) exit("Missing data");
$field = $_POST['field'];
$value = $_POST['value'];
$formID = $_POST['registrationFormID'];

// Whitelist fields
$allowed = ['companyName', 'telephoneNumber', 'otherNames', 'taxRegistrationNumber', 'faxNo', 'oldCompanyRegistrationNumber', 'emailAddress', 'newCompanyRegistrationNumber', 'countryOfIncorporation', 'dateOfIncorporation', 'natureAndLineOfBusiness', 'registeredAddress', 'correspondenceAddress', 'companyOrganisation', 'typeOfOrganisation', 'website', 'branch', 'authorisedCapital', 'paidUpCapital', 'parentCompany', 'parentCompanyCountry', 'ultimateParentCompany', 'ultimateParentCompanyCountry', 'bankruptHistory', 'description', 'cidb', 'cidbValidationTill', 'verifierName', 'verifierDesignation', 'dateOfVerification'];

if (!in_array($field, $allowed)) exit("Field not allowed");
$stmt = $conn->prepare("UPDATE registrationform SET `$field` = ? WHERE registrationFormID = ?");
$stmt->bind_param("si", $value, $formID);
$stmt->execute();
echo "Updated";
?>