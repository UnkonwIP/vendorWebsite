<?php
require_once "session_bootstrap.php";
require_once "config.php";
require_once __DIR__ . '/status_helpers.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

// Check user is logged in and is a department admin or head.
// Department admins (`admin`) perform initial review -> set their department to 'pending approval'.
// Department heads (`admin_head`) finalize from 'pending approval' -> 'approved' or can reject.
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'admin_head'], true)) {
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

// Get current user's role and determine department column via helper
$accountID = $_SESSION['accountID'] ?? '';
$role = $_SESSION['role'];

$deptColumn = get_dept_column_for_account($conn, $accountID);
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

// If a department rejected the form, mark the main status as 'rejected'
// so the vendor can edit and resubmit. Store the rejection reason if provided.
if (strtolower($action) === 'reject') {
    $reason = is_null($rejectionReason) ? null : trim((string)$rejectionReason);
    $mainUpdate = $conn->prepare("UPDATE registrationform SET status = 'rejected', rejectionReason = ? WHERE registrationFormID = ?");
    if ($mainUpdate) {
        // bind nullable string
        mysqli_stmt_bind_param($mainUpdate, 'si', $reason, $registrationFormID);
        $mainUpdate->execute();
        mysqli_stmt_close($mainUpdate);
    }
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

// Redirect back (respect provided redirectUrl; otherwise send based on role)
$redirectUrl = $_POST['redirectUrl'] ?? '';
if (!empty($redirectUrl)) {
    header("Location: " . $redirectUrl);
} else {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin_head') {
        header("Location: AdminHeadRegisrationManagement.php");
    } else {
        header("Location: AdminRegistrationManagement.php");
    }
}
exit();
