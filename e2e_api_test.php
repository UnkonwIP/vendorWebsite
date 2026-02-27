<?php
// e2e_api_test.php — simulate API endpoint flows for approval (admin -> dept admin -> head)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/status_helpers.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

echo "Starting API-simulated E2E approval flow test\n";

// Create vendoraccount for FK
$regNum = 'API' . substr(md5((string)microtime(true)), 0, 8);
$username = 'api_tu_' . substr(md5((string)microtime(true)),0,6);
$email = 'api+' . substr(md5((string)microtime(true)),0,6) . '@example.local';
$uStmt = $conn->prepare("INSERT INTO vendoraccount (newCompanyRegistrationNumber, username, role, vendorType, email) VALUES (?, ?, 'vendor', 'Finance', ?)");
$uStmt->bind_param('sss', $regNum, $username, $email);
if (!$uStmt->execute()) die("Failed to insert vendoraccount: " . $conn->error . "\n");
echo "Inserted vendoraccount with newCompanyRegistrationNumber=$regNum\n";

// Insert registrationform
$ins = $conn->prepare("INSERT INTO registrationform (newCompanyRegistrationNumber, status, financeDepartmentStatus, projectDepartmentStatus, legalDepartmentStatus, planDepartmentStatus, formFirstSubmissionDate) VALUES (?, 'not review', 'not review', 'not review', 'not review', 'not review', CURDATE())");
$ins->bind_param('s', $regNum);
if (!$ins->execute()) die("Failed to insert registrationform: " . $conn->error . "\n");
$formID = $conn->insert_id;
echo "Inserted registrationform ID: $formID\n";

function show($id) {
    global $conn;
    $r = $conn->query("SELECT registrationFormID, status, financeDepartmentStatus, projectDepartmentStatus, legalDepartmentStatus, planDepartmentStatus, rejectionReason FROM registrationform WHERE registrationFormID = $id")->fetch_assoc();
    echo "ROW: " . json_encode($r) . "\n";
}

show($formID);

// --- Simulate APIUpdateFormStatus as admin: set main -> pending approval ---
$sessionRole = 'admin';
echo "Simulate general admin action (role=$sessionRole) to set main -> pending approval\n";
$curRow = $conn->query("SELECT status FROM registrationform WHERE registrationFormID = $formID")->fetch_assoc();
$curStatus = normalize_status($curRow['status'] ?? '');
list($newMain, $initDeps) = allowed_main_transition($curStatus, 'pending approval');
if ($newMain === false) { echo "Main transition not allowed\n"; exit(1); }
$stmt = $conn->prepare("UPDATE registrationform SET status='pending approval', rejectionReason = NULL, financeDepartmentStatus='not review', projectDepartmentStatus='not review', legalDepartmentStatus='not review', planDepartmentStatus='not review' WHERE registrationFormID = ?");
$stmt->bind_param('i', $formID);
$stmt->execute();
show($formID);

// --- Simulate APIDepartmentApproval as department admin (role=admin) -> set finance to pending approval ---
$userRole = 'admin';
$deptCol = 'financeDepartmentStatus';
echo "Simulate department admin (role=$userRole) setting $deptCol -> pending approval\n";
$curDept = $conn->query("SELECT $deptCol FROM registrationform WHERE registrationFormID = $formID")->fetch_row()[0];
$allowed = allowed_department_transition($curDept, 'approve', $userRole);
if ($allowed === false) { echo "Dept admin cannot transition\n"; exit(1); }
$u = $conn->prepare("UPDATE registrationform SET $deptCol = ? WHERE registrationFormID = ?");
$u->bind_param('si', $allowed, $formID);
$u->execute();
show($formID);

// --- Simulate APIDepartmentApproval as department head (role=admin_head) finalizing to approved ---
$userRole = 'admin_head';
echo "Simulate department head (role=$userRole) finalizing $deptCol -> approved\n";
$curDept = $conn->query("SELECT $deptCol FROM registrationform WHERE registrationFormID = $formID")->fetch_row()[0];
$allowed = allowed_department_transition($curDept, 'approve', $userRole);
if ($allowed === false) { echo "Dept head cannot finalize\n"; exit(1); }
$u = $conn->prepare("UPDATE registrationform SET $deptCol = ? WHERE registrationFormID = ?");
$u->bind_param('si', $allowed, $formID);
$u->execute();
show($formID);

// Fast-forward remaining departments to approved to trigger main approval
$conn->query("UPDATE registrationform SET projectDepartmentStatus='approved', legalDepartmentStatus='approved', planDepartmentStatus='approved' WHERE registrationFormID = $formID");
echo "Set other departments -> approved\n";
show($formID);

// Check and finalize main
$row = $conn->query("SELECT financeDepartmentStatus, projectDepartmentStatus, legalDepartmentStatus, planDepartmentStatus FROM registrationform WHERE registrationFormID = $formID")->fetch_assoc();
$all = array_map('strtolower', $row);
if ($all['financeDepartmentStatus'] === 'approved' && $all['projectDepartmentStatus'] === 'approved' && $all['legalDepartmentStatus'] === 'approved' && $all['planDepartmentStatus'] === 'approved') {
    $conn->query("UPDATE registrationform SET status='approved' WHERE registrationFormID = $formID");
    echo "All departments approved — main set to approved\n";
}
show($formID);

echo "API-simulated E2E test completed for form ID: $formID\n";
?>