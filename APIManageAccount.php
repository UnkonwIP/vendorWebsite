<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$resp = ['success' => false, 'message' => 'Invalid request'];

// determine account id from session only — do not accept accountID from requests
$accountID = isset($_SESSION['accountID']) ? intval($_SESSION['accountID']) : 0;
if ($accountID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$action = $_REQUEST['action'] ?? $_SERVER['REQUEST_METHOD'];

// helper to send JSON and exit
function out($arr) { echo json_encode($arr); exit(); }

// Fetch current account data
if ($action === 'fetch' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare('SELECT username, email, passwordHash FROM vendoraccount WHERE accountID = ? LIMIT 1');
    $stmt->bind_param('i', $accountID);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        out(['success' => true, 'data' => ['username' => $row['username'], 'email' => $row['email']]]);
    }
    out(['success' => false, 'message' => 'Account not found']);
}

// Only allow POST for modifying actions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(['success' => false, 'message' => 'Invalid method']);
}

$action = $_POST['action'] ?? '';

if ($action === 'save_field') {
    $field = $_POST['field'] ?? '';
    $value = trim($_POST['value'] ?? '');
    if ($field === 'username') {
        if ($value === '') out(['success' => false, 'message' => 'Username cannot be empty']);
        // Ensure username is unique across accounts (allow keeping current username)
        $checkStmt = $conn->prepare('SELECT accountID FROM vendoraccount WHERE username = ? LIMIT 1');
        $checkStmt->bind_param('s', $value);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        if ($checkRes && $checkRes->num_rows > 0) {
            $existing = $checkRes->fetch_assoc();
            $existingID = isset($existing['accountID']) ? intval($existing['accountID']) : 0;
            if ($existingID !== $accountID) {
                $checkStmt->close();
                out(['success' => false, 'message' => 'Username already taken']);
            }
        }
        $checkStmt->close();

        $stmt = $conn->prepare('UPDATE vendoraccount SET username = ? WHERE accountID = ? LIMIT 1');
        $stmt->bind_param('si', $value, $accountID);
        if ($stmt->execute()) {
            $_SESSION['username'] = $value;
            out(['success' => true, 'message' => 'Username updated']);
        }
        out(['success' => false, 'message' => 'Failed to update username']);
    } elseif ($field === 'email') {
        if ($value === '' || !filter_var($value, FILTER_VALIDATE_EMAIL)) out(['success' => false, 'message' => 'Invalid email']);
        $stmt = $conn->prepare('UPDATE vendoraccount SET email = ? WHERE accountID = ? LIMIT 1');
        $stmt->bind_param('si', $value, $accountID);
        if ($stmt->execute()) {
            out(['success' => true, 'message' => 'Email updated']);
        }
        out(['success' => false, 'message' => 'Failed to update email']);
    }
    out(['success' => false, 'message' => 'Unknown field']);
}

if ($action === 'initiate_password_change') {
    // Only require current password to request OTP. New password is set after OTP verification.
    $current = $_POST['current_password'] ?? '';
    if ($current === '') out(['success' => false, 'message' => 'Current password required']);
    // fetch current hash
    $stmt = $conn->prepare('SELECT passwordHash, email, username FROM vendoraccount WHERE accountID = ? LIMIT 1');
    $stmt->bind_param('i', $accountID);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!($row = $res->fetch_assoc())) out(['success' => false, 'message' => 'Account not found']);
    $currentHash = $row['passwordHash'];
    $currentEmail = $row['email'];
    if (!password_verify($current, $currentHash)) out(['success' => false, 'message' => 'Current password incorrect']);

    $otp = strval(random_int(100000, 999999));
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $expiry = time() + 600;
    $_SESSION['otp_pending'] = [
        'code_hash' => $otpHash,
        'expires' => $expiry,
        'verified' => false,
        'newHash' => null,
        'newUsername' => null,
        'newEmail' => null,
        'attempts' => 0,
    ];
    // send email
    try {
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        } else {
            require __DIR__ . '/PHPMailer/src/Exception.php';
            require __DIR__ . '/PHPMailer/src/PHPMailer.php';
            require __DIR__ . '/PHPMailer/src/SMTP.php';
        }
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->setFrom(MAIL_USER, 'Vendor System');
        $mail->addAddress($currentEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for password change';
        $mail->Body = "<p>Your OTP for changing your password is: <strong>" . htmlspecialchars($otp) . "</strong></p><p>This code expires in 10 minutes.</p>";
        $mail->send();
        out(['success' => true, 'message' => 'OTP sent to your email']);
    } catch (Exception $e) {
        unset($_SESSION['otp_pending']);
        out(['success' => false, 'message' => 'Failed to send OTP email.']);
    }
}

if ($action === 'verify_otp') {
    $code = trim($_POST['otp_code'] ?? '');
    if (empty($_SESSION['otp_pending']) || !is_array($_SESSION['otp_pending'])) out(['success' => false, 'message' => 'No OTP pending']);
    $pending = &$_SESSION['otp_pending'];
        if (time() > ($pending['expires'] ?? 0)) { unset($_SESSION['otp_pending']); out(['success' => false, 'message' => 'OTP expired']); }
        if (!empty($pending['attempts']) && $pending['attempts'] >= 5) { unset($_SESSION['otp_pending']); out(['success' => false, 'message' => 'Too many attempts']); }
        if (!password_verify($code, $pending['code_hash'])) {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1;
            out(['success' => false, 'message' => 'Incorrect OTP']);
        }
        // mark verified; user can now set a new password
        $_SESSION['otp_pending']['verified'] = true;
        out(['success' => true, 'message' => 'OTP verified — you may now set a new password']);
    }

// Allow cancelling/clearing a pending OTP from the client (e.g., user pressed Cancel)
if ($action === 'cancel_otp') {
    if (!empty($_SESSION['otp_pending'])) unset($_SESSION['otp_pending']);
    out(['success' => true, 'message' => 'Request cancelled']);
}

    // After OTP verified, accept new password and apply it
    if ($action === 'set_new_password') {
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($_SESSION['otp_pending']) || !is_array($_SESSION['otp_pending']) || empty($_SESSION['otp_pending']['verified'])) out(['success' => false, 'message' => 'OTP not verified']);
        if ($new === '' || $new !== $confirm) out(['success' => false, 'message' => 'New passwords do not match']);
        // validate password strength minimally
        if (strlen($new) < 6) out(['success' => false, 'message' => 'Password too short']);
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE vendoraccount SET passwordHash = ? WHERE accountID = ? LIMIT 1');
        $stmt->bind_param('si', $newHash, $accountID);
        if ($stmt->execute()) {
            unset($_SESSION['otp_pending']);
            out(['success' => true, 'message' => 'Password changed successfully']);
        }
        out(['success' => false, 'message' => 'Failed to update password']);
    }
    // no additional fallback logic here

out(['success' => false, 'message' => 'Unknown action']);

?>
