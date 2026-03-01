<?php
// Secure AJAX handler for forgot password
header('Content-Type: application/json');

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


// Rate limiting (simple session-based, for demo)
session_start();
if (!isset($_SESSION['fp_attempts'])) $_SESSION['fp_attempts'] = 0;
if ($_SESSION['fp_attempts'] > 10) {
	echo json_encode([
		'status' => 'error',
		'message' => 'Too many attempts. Please try again later.'
	]);
	exit;
}
$_SESSION['fp_attempts']++;

$identifier = trim($_POST['identifier'] ?? '');

if ($identifier === '') {
	echo json_encode([
		'status' => 'error',
		'message' => 'If the information is correct, you will receive a reset link.'
	]);
	exit;
}

// Find user by username OR company registration number
$stmt = $conn->prepare('SELECT va.username, va.role, va.newCompanyRegistrationNumber, va.email FROM vendoraccount va WHERE va.username = ? OR va.newCompanyRegistrationNumber = ? LIMIT 1');
$stmt->bind_param('ss', $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
	// Always generic message
	echo json_encode([
		'status' => 'success',
		'message' => 'If the information is correct, you will receive a reset link.'
	]);
	exit;
}
$user = $result->fetch_assoc();


// Generate reset token and expiry (Asia/Kuala_Lumpur)
date_default_timezone_set('Asia/Kuala_Lumpur');
$token  = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Update vendoraccount with token and expiry
$update = $conn->prepare('UPDATE vendoraccount SET resetToken = ?, resetExpiry = ? WHERE username = ?');
$update->bind_param('sss', $token, $expiry, $user['username']);
$update->execute();

// Send email (always act as if successful)
$resetLink = APP_URL . 'reset_password.php?token=' . urlencode($token);
$mail = new PHPMailer(true);
try {
	$mail->isSMTP();
	$mail->Host       = MAIL_HOST;
	$mail->SMTPAuth   = true;
	$mail->Username   = MAIL_USER;
	$mail->Password   = MAIL_PASS;
	$mail->SMTPSecure = MAIL_ENCRYPTION;
	$mail->Port       = MAIL_PORT;
	$mail->setFrom(MAIL_USER, 'Vendor System');
	$mail->addAddress($user['email']);
	$mail->isHTML(true);
	$mail->Subject = 'Password Reset Request';
	$mail->Body =
		'Hello,<br><br>' .
		'We received a request to reset your password.<br><br>' .
		'<a href="' . htmlspecialchars($resetLink) . '">Click here to reset your password</a><br><br>' .
		'This link will expire in 1 hour.<br><br>' .
		'If you did not request this, please ignore this email.';
	$mail->send();
} catch (Exception $e) {
	// Do not reveal error
}

echo json_encode([
	'status' => 'success',
	'message' => 'If the information is correct, you will receive a reset link.'
]);
exit;
?>
