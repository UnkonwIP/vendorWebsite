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

if (class_exists('Dotenv\\Dotenv')) {
  Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
}
echo 'DB_HOST='.htmlspecialchars(getenv('DB_HOST')).'<br>';
echo 'MAIL_USER='.htmlspecialchars(getenv('MAIL_USER')).'<br>';

function testEmailConnection() {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST; // Change as needed
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER; // Change as needed
        $mail->Password   = MAIL_PASS; // Change as needed
        $mail->SMTPSecure = MAIL_ENCRYPTION; // Change as needed
        $mail->Port       = MAIL_PORT; // Change as needed

        // Enable verbose debug output for SMTP (useful for diagnosing auth/connect problems)
        // $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
        // $mail->Debugoutput = 'echo';
        // Allow self-signed certs in test environments; remove/lock down in production
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

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

    // Insert admin account into vendoraccount, set as Head for General
    // Save admin role into vendorType; department column is no longer used
    $vendorType = 'General';
    $stmt = $conn->prepare("INSERT INTO vendoraccount (username, passwordHash, email, role, vendorType) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $username, $password, $email, $role, $vendorType);
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