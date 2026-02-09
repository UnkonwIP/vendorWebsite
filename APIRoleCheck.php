<?php
// Secure AJAX endpoint for role check
header('Content-Type: application/json');
require_once 'config.php';

$email = trim($_POST['email'] ?? '');
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo json_encode(['role' => 'other']);
	exit;
}

$stmt = $conn->prepare('SELECT role FROM vendoraccount WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
	$row = $result->fetch_assoc();
	if ($row['role'] === 'vendor') {
		echo json_encode(['role' => 'vendor']);
		exit;
	}
}
echo json_encode(['role' => 'other']);
exit;
?>
