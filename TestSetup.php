<?php
// TestSetup.php

// Email connection test using PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback to the old direct includes if vendor/autoload.php is not present
    require  __DIR__ . '/PHPMailer/src/Exception.php';
    require  __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require  __DIR__ . '/PHPMailer/src/SMTP.php';
}
require_once __DIR__ . '/config.php';

function testEmailConnection() {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST; // Change as needed
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER; // Change as needed
        $mail->Password   = MAIL_PASS; // Change as needed
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom(MAIL_USER, 'Test System');
        $mail->addAddress(DEFAULT_ADMIN_EMAIL);
        $mail->isHTML(true);
        $mail->Subject = 'Test Email Connection';
        $mail->Body    = 'This is a test email to verify SMTP connection.';

        $mail->send();
        echo "Email sent successfully to " . DEFAULT_ADMIN_EMAIL . "\n";
    } catch (Exception $e) {
        echo "Failed to send email to " . DEFAULT_ADMIN_EMAIL . ". Error: {$mail->ErrorInfo}\n";
    }
}

// Create default admin account
function createDefaultAdmin($conn) {
    $username = 'Admin';
    $password = password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT);
    $email = DEFAULT_ADMIN_EMAIL;
    $role = 'admin';
    $accountID = "admin";

    // Check if admin already exists in vendoraccount
    $stmt = $conn->prepare("SELECT accountID FROM vendoraccount WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "Admin account already exists.\n";
        $stmt->close();
        return;
    }
    $stmt->close();

    // Insert admin account into vendoraccount
    $stmt = $conn->prepare("INSERT INTO vendoraccount (accountID, username, passwordHash, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $accountID, $username, $password, $email, $role);
    if ($stmt->execute()) {
        echo "Default admin account created.\n";
    } else {
        echo "Failed to create admin account.\n";
    }
    $stmt->close();
}

// Example usage:

// 1. Test email connection
testEmailConnection();

// 2. Create default admin account
createDefaultAdmin($conn);
?>