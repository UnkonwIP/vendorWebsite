<?php

// APIAddTableRow.php

// 1. Disable error displaying to screen (prevents warnings from breaking JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 2. Set Header to JSON
header('Content-Type: application/json');

require_once __DIR__ . '/session_bootstrap.php';
require_once "config.php";

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB Connection Failed: " . $conn->connect_error]);
    exit;
}

$table = $_POST['Table'] ?? '';
$formID = $_POST['registrationFormID'] ?? '';

if (!$table || !$formID) {
    echo json_encode(["success" => false, "error" => "Missing Table or ID"]);
    exit;
}

$formID = is_numeric($formID) ? intval($formID) : 0;
if (empty($formID)) { echo json_encode(["success" => false, "error" => "Missing registrationFormID"]); exit; }

// Permission: only admin/admin_head or owning vendor may add rows for this form
$role = $_SESSION['role'] ?? '';
$currentAccount = isset($_SESSION['accountID']) ? intval($_SESSION['accountID']) : 0;
if (!in_array($role, ['admin','admin_head'], true)) {
    $pstmt = $conn->prepare("SELECT newCompanyRegistrationNumber FROM registrationform WHERE registrationFormID = ? LIMIT 1");
    $pstmt->bind_param("i", $formID);
    $pstmt->execute();
    $prow = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();
    $crn = $prow['newCompanyRegistrationNumber'] ?? '';
    if (empty($crn)) { echo json_encode(["success" => false, "error" => "Forbidden"]); exit; }
    $pstmt = $conn->prepare("SELECT accountID FROM vendoraccount WHERE newCompanyRegistrationNumber = ? LIMIT 1");
    $pstmt->bind_param("s", $crn);
    $pstmt->execute();
    $owner = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();
    $ownerID = isset($owner['accountID']) ? intval($owner['accountID']) : 0;
    if ($ownerID !== $currentAccount) { echo json_encode(["success" => false, "error" => "Forbidden"]); exit; }
}

$stmt = false;

// --- Logic per table ---
try {
    if ($table === 'Shareholders') {
        $sql = "INSERT INTO shareholders (registrationFormID, name, nationality, address, sharePercentage) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Check if POST keys exist, default to empty string/0 if not
            $name = $_POST['name'] ?? '';
            $nat = $_POST['nationality'] ?? '';
            $addr = $_POST['address'] ?? '';
            $perc = $_POST['sharePercentage'] ?? 0;
            $stmt->bind_param("isssd", $formID, $name, $nat, $addr, $perc);
        }

    } elseif ($table === 'DirectorAndSecretary') {
        $sql = "INSERT INTO directorandsecretary (registrationFormID, nationality, name, position, appointmentDate, dob) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $nat = $_POST['nationality'] ?? '';
            $name = $_POST['name'] ?? '';
            $pos = $_POST['position'] ?? '';
            $appt = $_POST['appointmentDate'] ?? date('Y-m-d');
            $dob = $_POST['dob'] ?? date('Y-m-d');
            $stmt->bind_param("isssss", $formID, $nat, $name, $pos, $appt, $dob);
        }

    } elseif ($table === 'Management') {
        $sql = "INSERT INTO management (registrationFormID, nationality, name, position, yearsInPosition, yearsInRelatedField) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $nat = $_POST['nationality'] ?? '';
            $name = $_POST['name'] ?? '';
            $pos = $_POST['position'] ?? '';
            $yPos = $_POST['yearsInPosition'] ?? 0;
            $yField = $_POST['yearsInRelatedField'] ?? 0;
            $stmt->bind_param("isssii", $formID, $nat, $name, $pos, $yPos, $yField);
        }

    } elseif ($table === 'Bank') {
        $sql = "INSERT INTO bank (registrationFormID, bankName, bankAddress, swiftCode) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $name = $_POST['bankName'] ?? '';
            $addr = $_POST['bankAddress'] ?? '';
            $swift = $_POST['swiftCode'] ?? '';
            $stmt->bind_param("isss", $formID, $name, $addr, $swift);
        }

    } elseif ($table === 'Staff') {
        $sql = "INSERT INTO staff (registrationFormID, name, designation, qualification, yearsOfExperience, employmentStatus, skills, relevantCertification) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $name = $_POST['name'] ?? '';
            $desig = $_POST['designation'] ?? '';
            $qual = $_POST['qualification'] ?? '';
            $exp = $_POST['yearsOfExperience'] ?? 0;
            $emp = $_POST['employmentStatus'] ?? '';
            $skill = $_POST['skills'] ?? '';
            $cert = $_POST['relevantCertification'] ?? '';
            $stmt->bind_param("isssisss", $formID, $name, $desig, $qual, $exp, $emp, $skill, $cert);
        }

    } elseif ($table === 'ProjectTrackRecord') {
        $sql = "INSERT INTO projecttrackrecord (registrationFormID, projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $title = $_POST['projectTitle'] ?? '';
            $nat = $_POST['projectNature'] ?? '';
            $loc = $_POST['location'] ?? '';
            $client = $_POST['clientName'] ?? '';
            $val = $_POST['projectValue'] ?? 0;
            $start = $_POST['commencementDate'] ?? date('Y-m-d');
            $end = $_POST['completionDate'] ?? date('Y-m-d');
            $stmt->bind_param("isssssss", $formID, $title, $nat, $loc, $client, $val, $start, $end);
        }

    } elseif ($table === 'CurrentProject') {
        $sql = "INSERT INTO currentproject (registrationFormID, projectTitle, projectNature, location, clientName, projectValue, commencementDate, completionDate, progressOfTheWork) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $title = $_POST['projectTitle'] ?? '';
            $nat = $_POST['projectNature'] ?? '';
            $loc = $_POST['location'] ?? '';
            $client = $_POST['clientName'] ?? '';
            $val = $_POST['projectValue'] ?? 0;
            $start = $_POST['commencementDate'] ?? date('Y-m-d');
            $end = $_POST['completionDate'] ?? date('Y-m-d');
            $prog = $_POST['progressOfTheWork'] ?? 0;
            $stmt->bind_param("isssssssd", $formID, $title, $nat, $loc, $client, $val, $start, $end, $prog);
        }

    } elseif ($table === 'CreditFacilities') {
        $sql = "INSERT INTO creditfacilities (registrationFormID, typeOfCreditFacilities, financialInstitution, totalAmount, expiryDate, unutilisedAmountCurrentlyAvailable, asAtDate) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $type = $_POST['typeOfCreditFacilities'] ?? '';
            $inst = $_POST['financialInstitution'] ?? '';
            $amt = $_POST['totalAmount'] ?? 0;
            $exp = $_POST['expiryDate'] ?? date('Y-m-d');
            $unused = $_POST['unutilisedAmountCurrentlyAvailable'] ?? 0;
            $asAt = $_POST['asAtDate'] ?? date('Y-m-d');
            $stmt->bind_param("issdsds", $formID, $type, $inst, $amt, $exp, $unused, $asAt);
        }

    } else {
        echo json_encode(["success" => false, "error" => "Unknown Table: " . $table]);
        exit;
    }

    // --- Execution ---
    if (!$stmt) {
        // If prepare failed (usually SQL syntax error or column mismatch)
        echo json_encode(["success" => false, "error" => "SQL Prepare Error: " . $conn->error]);
    } elseif ($stmt->execute()) {
        echo json_encode(["success" => true, "id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "error" => "Execute Error: " . $stmt->error]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Exception: " . $e->getMessage()]);
}

$conn->close();
?>