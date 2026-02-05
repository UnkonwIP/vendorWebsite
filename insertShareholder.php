<?php
$conn = new mysqli('localhost', 'root', '', 'vendor_information');

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB Connection Failed"]);
    exit;
}

$table = $_POST['Table'] ?? '';
$formID = $_POST['registrationFormID'] ?? '';

if (!$table || !$formID) {
    echo json_encode(["success" => false, "error" => "Missing Table or ID"]);
    exit;
}

// Logic per table
if ($table === 'Shareholders') {
    $stmt = $conn->prepare("INSERT INTO shareholders (registrationFormID, companyShareholderID, name, nationality, address, sharePercentage) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssd", $formID, $_POST['companyShareholderID'], $_POST['name'], $_POST['nationality'], $_POST['address'], $_POST['sharePercentage']);

} elseif ($table === 'DirectorAndSecretary') {
    $stmt = $conn->prepare("INSERT INTO directorandsecretary (registrationFormID, nationality, name, position, appointmentDate, dob) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $formID, $_POST['nationality'], $_POST['name'], $_POST['position'], $_POST['appointmentDate'], $_POST['dob']);

} elseif ($table === 'Management') {
    $stmt = $conn->prepare("INSERT INTO management (registrationFormID, nationality, name, position, yearsInPosition, yearsInRelatedField) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssii", $formID, $_POST['nationality'], $_POST['name'], $_POST['position'], $_POST['yearsInPosition'], $_POST['yearsInRelatedField']);

} elseif ($table === 'Bank') {
    $stmt = $conn->prepare("INSERT INTO bank (registrationFormID, bankName, bankAddress, swiftCode) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $formID, $_POST['bankName'], $_POST['bankAddress'], $_POST['swiftCode']);

} elseif ($table === 'Staff') {
    $stmt = $conn->prepare("INSERT INTO staff (registrationFormID, staffNo, name, designation, qualification, yearsOfExperience, employmentStatus, skills, relevantCertification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssisss", $formID, $_POST['staffNo'], $_POST['name'], $_POST['designation'], $_POST['qualification'], $_POST['yearsOfExperience'], $_POST['employmentStatus'], $_POST['skills'], $_POST['relevantCertification']);

} elseif ($table === 'ProjectTrackRecord') {
    $stmt = $conn->prepare("INSERT INTO projecttrackrecord (registrationFormID, projectRecordNo, projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssssss", $formID, $_POST['projectRecordNo'], $_POST['projectTitle'], $_POST['projectNature'], $_POST['location'], $_POST['clientName'], $_POST['projectValue'], $_POST['commencementDate'], $_POST['completionDate']);

} elseif ($table === 'CurrentProject') {
    $stmt = $conn->prepare("INSERT INTO currentproject (registrationFormID, currentProjectRecordNo, projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate, progressOfTheWork) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssssssd", $formID, $_POST['currentProjectRecordNo'], $_POST['projectTitle'], $_POST['projectNature'], $_POST['location'], $_POST['clientName'], $_POST['projectValue'], $_POST['commencementDate'], $_POST['completionDate'], $_POST['progressOfTheWork']);

} elseif ($table === 'CreditFacilities') {
    $stmt = $conn->prepare("INSERT INTO creditfacilities (registrationFormID, typeOfCreditFacilities, financialInstitution, totalAmount, expiryDate, unutilisedAmountCurrentlyAvailable, asAtDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdsds", $formID, $_POST['typeOfCreditFacilities'], $_POST['financialInstitution'], $_POST['totalAmount'], $_POST['expiryDate'], $_POST['unutilisedAmountCurrentlyAvailable'], $_POST['asAtDate']);

} else {
    echo json_encode(["success" => false, "error" => "Unknown Table"]);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "id" => $conn->insert_id]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
?>