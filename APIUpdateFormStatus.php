<?php
session_start();
require_once "config.php";

date_default_timezone_set('Asia/Kuala_Lumpur'); // Fixed

// Only admin can update status
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registrationFormID = $_POST['registrationFormID'] ?? '';
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

    if ($status === 'approved') {
        $stmt = $conn->prepare("UPDATE registrationform SET status = ?, rejectionReason = NULL WHERE registrationFormID = ?");
        $stmt->bind_param("si", $status, $registrationFormID);
    } else if ($status === 'rejected') {
        $stmt = $conn->prepare("UPDATE registrationform SET status = ?, rejectionReason = ? WHERE registrationFormID = ?");
        $stmt->bind_param("ssi", $status, $rejectionReason, $registrationFormID);
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
