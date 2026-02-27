<?php
require_once "session_bootstrap.php";
require_once "config.php";
require_once __DIR__ . '/status_helpers.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

// Check user is logged in and has appropriate role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'admin_head'])) {
    http_response_code(403);
    echo "Unauthorized.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit();
}

$registrationFormID = $_POST['registrationFormID'] ?? '';
$action = $_POST['action'] ?? ''; // 'approve' or 'reject'
$rejectionReason = $_POST['rejectionReason'] ?? null;

if (empty($registrationFormID) || empty($action)) {
    echo "Missing required fields.";
    exit();
}

// Get current user's role and department
$accountID = $_SESSION['accountID'] ?? '';
$role = $_SESSION['role'];
$vendorType = '';

if (!empty($accountID)) {
    $vtStmt = $conn->prepare("SELECT vendorType FROM vendoraccount WHERE accountID = ? LIMIT 1");
    if ($vtStmt) {
        $vtStmt->bind_param('s', $accountID);
        $vtStmt->execute();
        $vtRes = $vtStmt->get_result();
        if ($vtRes && ($vtRow = $vtRes->fetch_assoc())) {
            $vendorType = $vtRow['vendorType'] ?? '';
        }
        $vtStmt->close();
    }
}

$
// Map vendorType to department column
$deptColumn = null;
$vtLower = strtolower($vendorType);

if (strpos($vtLower, 'finance') !== false) {
    $deptColumn = 'financeDepartmentStatus';
} elseif (strpos($vtLower, 'project') !== false) {
    $deptColumn = 'projectDepartmentStatus';
} elseif (strpos($vtLower, 'legal') !== false) {
    $deptColumn = 'legalDepartmentStatus';
} elseif (strpos($vtLower, 'plan') !== false) {
    $deptColumn = 'planDepartmentStatus';
}

if ($deptColumn === null) {
    echo "Unable to determine department.";
    exit();
}

// Fetch current main status and this department's current status
$checkStmt = $conn->prepare("SELECT status, $deptColumn FROM registrationform WHERE registrationFormID = ? LIMIT 1");
$checkStmt->bind_param("i", $registrationFormID);
$checkStmt->execute();
$checkRes = $checkStmt->get_result();
if (!$checkRes || !($checkRow = $checkRes->fetch_assoc())) {
    echo "Record not found.";
    exit();
}

$mainStatus = normalize_status($checkRow['status'] ?? '');
$currentDeptStatus = normalize_status($checkRow[$deptColumn] ?? '');

// Departments may act only after general admin marked form 'pending approval'
if ($mainStatus !== 'pending approval') {
    http_response_code(409);
    echo "Invalid workflow: main form must be in 'pending approval' before department actions.";
    exit();
}

// Determine the new status based on role and action
// Determine allowed transition (idempotent)
$allowedNew = allowed_department_transition($currentDeptStatus, $action, $role);
if ($allowedNew === false) {
    http_response_code(409);
    echo "Invalid or duplicate transition for department (current: {$currentDeptStatus}, action: {$action}).";
    exit();
}
$newStatus = $allowedNew;

// Update the department status
$updateStmt = $conn->prepare("UPDATE registrationform SET $deptColumn = ? WHERE registrationFormID = ?");
$updateStmt->bind_param("si", $newStatus, $registrationFormID);

if (!$updateStmt->execute()) {
    echo "Failed to update department status.";
    exit();
}

// If head approved, check if all departments are approved
if ($role === 'admin_head' && $newStatus === 'approved') {
    $checkStmt = $conn->prepare("SELECT financeDepartmentStatus, projectDepartmentStatus, legalDepartmentStatus, planDepartmentStatus, status FROM registrationform WHERE registrationFormID = ?");
    $checkStmt->bind_param("i", $registrationFormID);
    $checkStmt->execute();
    $checkRes = $checkStmt->get_result();
    
    if ($checkRes && ($checkRow = $checkRes->fetch_assoc())) {
        $finance = strtolower($checkRow['financeDepartmentStatus'] ?? '');
        $project = strtolower($checkRow['projectDepartmentStatus'] ?? '');
        $legal = strtolower($checkRow['legalDepartmentStatus'] ?? '');
        $plan = strtolower($checkRow['planDepartmentStatus'] ?? '');
        
        // If all departments are approved, update the main form status to approved
        if ($finance === 'approved' && $project === 'approved' && $legal === 'approved' && $plan === 'approved') {
            $finalStmt = $conn->prepare("UPDATE registrationform SET status = 'approved' WHERE registrationFormID = ?");
            $finalStmt->bind_param("i", $registrationFormID);
            $finalStmt->execute();
        }
    }
}

// Redirect back
$redirectUrl = $_POST['redirectUrl'] ?? '';
if (!empty($redirectUrl)) {
    header("Location: " . $redirectUrl);
} else {
    header("Location: AdminRegistrationManagement.php");
}
exit();
