<?php
require_once "session_bootstrap.php";
require_once "config.php";

// Allow admin and admin_head to trigger renewal (role-based access)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','admin_head'], true)) {
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

    // Redirect back to referrer when possible, else to the registration management view
    $redirect = 'AdminRegistrationManagement.php';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $ref = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        // Only allow local redirects
        if ($ref && basename($ref) !== '') {
            $redirect = basename($ref);
        }
    }

    header("Location: " . $redirect);
    exit();
}
