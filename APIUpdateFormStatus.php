<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/status_helpers.php';

date_default_timezone_set('Asia/Kuala_Lumpur'); // Fixed

// Only admin can update status
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    http_response_code(403);
    echo "Unauthorized.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registrationFormID = isset($_POST['registrationFormID']) ? intval($_POST['registrationFormID']) : 0;
    $status = $_POST['status'] ?? '';
    $rejectionReason = $_POST['rejectionReason'] ?? null;

    if (empty($registrationFormID) || empty($status)) {
        echo "Missing required fields.";
        exit();
    }

    if ($status === 'rejected' && (is_null($rejectionReason) || trim($rejectionReason) === '')) {
        echo "Rejection reason required.";
        exit();
    }

    // General admin data completeness check
    // Validate requested status and enforce idempotent main transitions
    // Normalize incoming status if it came as 'approved' (meaning general admin's approve action)
    $requested = normalize_status($status);

    // Fetch current main status
    $curStmt = $conn->prepare("SELECT status FROM registrationform WHERE registrationFormID = ? LIMIT 1");
    $curStmt->bind_param('i', $registrationFormID);
    $curStmt->execute();
    $curRes = $curStmt->get_result();
    $currentRow = $curRes->fetch_assoc();
    $currentStatus = normalize_status($currentRow['status'] ?? '');

    if ($requested === 'approved' || $requested === 'pending approval') {
        // Treat general admin 'approved' request as the action to set main -> 'pending approval'
        list($newMain, $initDeps) = allowed_main_transition($currentStatus, 'pending approval');
        if ($newMain === false) {
            http_response_code(409);
            echo "Invalid or duplicate main status transition.";
            exit();
        }

        // Build update to set main status to 'pending approval' and reset department statuses
        $stmt = $conn->prepare("UPDATE registrationform SET 
            status = 'pending approval', 
            rejectionReason = NULL,
            financeDepartmentStatus = 'not review',
            projectDepartmentStatus = 'not review',
            legalDepartmentStatus = 'not review',
            planDepartmentStatus = 'not review'
            WHERE registrationFormID = ?");
        $stmt->bind_param("i", $registrationFormID);
    } else if ($requested === 'rejected') {
        // Rejection by general admin for data incompleteness: enforce idempotency
        list($newMain, $initDeps) = allowed_main_transition($currentStatus, 'rejected');
        if ($newMain === false) {
            http_response_code(409);
            echo "Invalid or duplicate main status transition (already rejected).";
            exit();
        }

        $stmt = $conn->prepare("UPDATE registrationform SET status = ?, rejectionReason = ? WHERE registrationFormID = ?");
        $stmt->bind_param("ssi", $newMain, $rejectionReason, $registrationFormID);
    } else {
        echo "Invalid status.";
        exit();
    }

    if ($stmt->execute()) {
        $redirectUrl = $_POST['redirectUrl'] ?? '';
        if (!empty($redirectUrl)) {
            header("Location: " . $redirectUrl);
        } else {
            header("Location: AdminVendorFormList.php");
        }
        exit();
    } else {
        echo "Failed to update status.";
    }
} else {
    echo "Invalid request method.";
}
