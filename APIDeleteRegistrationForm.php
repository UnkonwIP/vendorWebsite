<?php
// APIDeleteRegistrationForm.php
header('Content-Type: text/html');
session_start();
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

// Optional: Check user permissions here (e.g., vendor owns this form)

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
