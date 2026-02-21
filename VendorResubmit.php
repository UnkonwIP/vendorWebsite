<?php
session_start();
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$registrationFormID = $_POST['registrationFormID'] ?? null;
if (empty($registrationFormID) || !is_numeric($registrationFormID)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing registrationFormID']);
    exit();
}

// Verify ownership: the registrationform.newCompanyRegistrationNumber must match vendoraccount.newCompanyRegistrationNumber for this session
$stmt = $conn->prepare('SELECT newCompanyRegistrationNumber, status FROM registrationform WHERE registrationFormID = ? LIMIT 1');
$stmt->bind_param('i', $registrationFormID);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
if (!$r) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Record not found']);
    exit();
}

$newCRN = $r['newCompanyRegistrationNumber'] ?? null;
$currentStatus = strtolower($r['status'] ?? '');

$vstmt = $conn->prepare('SELECT accountID, newCompanyRegistrationNumber FROM vendoraccount WHERE accountID = ? LIMIT 1');
$vstmt->bind_param('s', $_SESSION['accountID']);
$vstmt->execute();
$vendor = $vstmt->get_result()->fetch_assoc();

if (!$vendor || ($vendor['newCompanyRegistrationNumber'] ?? '') !== ($newCRN ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authorized for this record']);
    exit();
}

if ($currentStatus !== 'rejected') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Form not in rejected state']);
    exit();
}

// Update status to pending and clear rejectionReason
$ustmt = $conn->prepare('UPDATE registrationform SET status = ?, rejectionReason = NULL WHERE registrationFormID = ?');
$newStatus = 'pending';
$ustmt->bind_param('si', $newStatus, $registrationFormID);
if ($ustmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error']);
}

?>
