<?php
// Secure AJAX handler for password reset
header('Content-Type: application/json');
require_once 'config.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($token) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request. Please try again.'
    ]);
    exit;
}

// Server-side password strength validation
$valid = strlen($password) >= 8 &&
    preg_match('/[A-Z]/', $password) &&
    preg_match('/[a-z]/', $password) &&
    preg_match('/[0-9]/', $password) &&
    preg_match('/[^A-Za-z0-9]/', $password);

if (!$valid) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Password does not meet strength requirements.'
    ]);
    exit;
}

// Find account by token
$stmt = $conn->prepare(
    "SELECT accountID, resetExpiry FROM vendoraccount
     WHERE resetToken = ? AND resetExpiry > NOW()"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows !== 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or expired reset link.'
    ]);
    exit;
}

$row = $result->fetch_assoc();
$newPassword = password_hash($password, PASSWORD_DEFAULT);

// Update password and clear reset token
$update = $conn->prepare(
    "UPDATE vendoraccount
     SET passwordHash=?, resetToken=NULL, resetExpiry=NULL
     WHERE accountID=?"
);
$update->bind_param("ss", $newPassword, $row['accountID']);

if ($update->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Password updated successfully. Redirecting to login...'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error updating password. Please try again.'
    ]);
}
exit;
?>
