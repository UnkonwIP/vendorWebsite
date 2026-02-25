<?php
// APIDeleteRegistrationForm.php
header('Content-Type: text/html');
require_once __DIR__ . '/session_bootstrap.php';
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<div class='alert alert-danger'>Invalid request method.</div>";
    exit();
}

$registrationFormID = $_POST['registrationFormID'] ?? '';
if (empty($registrationFormID) || !is_numeric($registrationFormID)) {
    echo "<div class='alert alert-danger'>Missing or invalid registrationFormID.</div>";
    exit();
}
// Permission: only admin/admin_head or owning vendor can delete the form
$role = $_SESSION['role'] ?? '';
$currentAccount = isset($_SESSION['accountID']) ? intval($_SESSION['accountID']) : 0;
if (!in_array($role, ['admin','admin_head'], true)) {
    $pstmt = $conn->prepare("SELECT newCompanyRegistrationNumber FROM registrationform WHERE registrationFormID = ? LIMIT 1");
    $pstmt->bind_param("i", $registrationFormID);
    $pstmt->execute();
    $prow = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();
    $crn = $prow['newCompanyRegistrationNumber'] ?? '';
    if (empty($crn)) { echo "<div class='alert alert-danger'>Forbidden.</div>"; exit(); }
    $pstmt = $conn->prepare("SELECT accountID FROM vendoraccount WHERE newCompanyRegistrationNumber = ? LIMIT 1");
    $pstmt->bind_param("s", $crn);
    $pstmt->execute();
    $owner = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();
    $ownerID = isset($owner['accountID']) ? intval($owner['accountID']) : 0;
    if ($ownerID !== $currentAccount) { echo "<div class='alert alert-danger'>Forbidden.</div>"; exit(); }
}

// Delete related rows in child tables first (if any)
$tables = [
    'bank',
    'contacts',
    'shareholders',
    'projecttrackrecord',
    'currentproject',
    'staff',
    'directorandsecretary',
    'management',
    'nettworth',
    'equipment',
    'creditfacilities'
];
foreach ($tables as $table) {
    $stmt = $conn->prepare("DELETE FROM $table WHERE registrationFormID = ?");
    $stmt->bind_param("i", $registrationFormID);
    $stmt->execute();
    $stmt->close();
}

// Delete the main registration form
$stmt = $conn->prepare("DELETE FROM registrationform WHERE registrationFormID = ?");
$stmt->bind_param("i", $registrationFormID);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo "<div class='alert alert-success'>Registration form deleted successfully!</div>";
    echo "<script>setTimeout(function(){ window.location.href = 'VendorHomepage.php'; }, 1000);</script>";
} else {
    echo "<div class='alert alert-danger'>Failed to delete registration form.</div>";
}
