<?php
// Secure AJAX handler for forgot password
header('Content-Type: application/json');
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'config.php';

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

$email = trim($_POST['email'] ?? '');
$regNo = trim($_POST['newCompanyRegistration'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo json_encode([
		'status' => 'error',
		'message' => 'If the information is correct, you will receive a reset link.'
	]);
	exit;
}

// Find user by email
$stmt = $conn->prepare('SELECT va.username, va.role, va.newCompanyRegistrationNumber, va.email FROM vendoraccount va WHERE va.email = ? LIMIT 1');
$stmt->bind_param('s', $email);
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

// If vendor, require regNo and check
if ($user['role'] === 'vendor') {
	if (empty($regNo) || $regNo !== $user['newCompanyRegistrationNumber']) {
		echo json_encode([
			'status' => 'success',
			'message' => 'If the information is correct, you will receive a reset link.'
		]);
		exit;
	}
}

// Generate reset token and expiry
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
	$mail->Host       = 'smtp.gmail.com';
	$mail->SMTPAuth   = true;
	$mail->Username   = MAIL_USER;
	$mail->Password   = MAIL_PASS;
	$mail->SMTPSecure = 'tls';
	$mail->Port       = 587;
	$mail->setFrom(MAIL_USER, 'Vendor System');
	$mail->addAddress($email);
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
