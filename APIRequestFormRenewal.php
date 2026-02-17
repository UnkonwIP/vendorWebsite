<?php
session_start();
require_once "config.php";

// Only allow admins (future proof for role-based access)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update formRenewalStatus for all vendors
    $sql = "UPDATE vendoraccount SET formRenewalStatus = 'not complete' WHERE role = 'vendor'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['form_renewal_message'] = 'All vendor accounts have been set to request a new registration form.';
    } else {
        $_SESSION['form_renewal_message'] = 'Error updating vendor accounts.';
    }
    header("Location: admin.php");
    exit();
}
