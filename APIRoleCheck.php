<?php
// Secure AJAX endpoint for role check
header('Content-Type: application/json');
require_once 'config.php';

$identifier = trim($_POST['identifier'] ?? '');
if ($identifier === '') {
	echo json_encode(['role' => 'other']);
	exit;
}

$stmt = $conn->prepare('SELECT role FROM vendoraccount WHERE username = ? OR newCompanyRegistrationNumber = ? LIMIT 1');
$stmt->bind_param('ss', $identifier, $identifier);
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
