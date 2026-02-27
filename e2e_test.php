<?php
// e2e_test.php — simulate admin -> pending approval -> dept admin -> head approval
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/status_helpers.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

echo "Starting E2E approval flow test\n";

// 1) Insert a vendoraccount required by registrationform FK
$regNum = 'TESTREG' . substr(md5((string)microtime(true)), 0, 8);
$uStmt = $conn->prepare("INSERT INTO vendoraccount (newCompanyRegistrationNumber, username, role, vendorType, email) VALUES (?, ?, 'vendor', 'General', ?)");
$username = 'testuser_' . substr(md5((string)microtime(true)),0,6);
$email = 'test+' . substr(md5((string)microtime(true)),0,6) . '@example.local';
$uStmt->bind_param('sss', $regNum, $username, $email);
if (!$uStmt->execute()) {
    die("Failed to insert vendoraccount: " . $conn->error . "\n");
}
$vendorAccountID = $conn->insert_id;
echo "Inserted vendoraccount with newCompanyRegistrationNumber=$regNum\n";

// 2) Insert test registration form
$ins = $conn->prepare("INSERT INTO registrationform (newCompanyRegistrationNumber, status, financeDepartmentStatus, projectDepartmentStatus, legalDepartmentStatus, planDepartmentStatus, formFirstSubmissionDate) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
$init = 'not review';
$ins->bind_param('ssssss', $regNum, $init, $init, $init, $init, $init);
if (!$ins->execute()) {
    die("Failed to insert test registrationform: " . $conn->error . "\n");
}
$formID = $conn->insert_id;
echo "Inserted test form ID: $formID (all statuses 'not review')\n";

// Helper to print current statuses
function print_status($id) {
    global $conn;
    $q = $conn->prepare("SELECT registrationFormID, status, financeDepartmentStatus, projectDepartmentStatus, legalDepartmentStatus, planDepartmentStatus, rejectionReason FROM registrationform WHERE registrationFormID = ? LIMIT 1");
    $q->bind_param('i', $id);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    echo "Current row: " . json_encode($r) . "\n";
}

print_status($formID);

// 2) General admin: move main -> pending approval
$cur = 'not review';
list($newMain, $initDeps) = allowed_main_transition($cur, 'pending approval');
if ($newMain === false) {
    die("Main transition not allowed\n");
}
$u = $conn->prepare("UPDATE registrationform SET status = 'pending approval', rejectionReason = NULL, financeDepartmentStatus = 'not review', projectDepartmentStatus = 'not review', legalDepartmentStatus = 'not review', planDepartmentStatus = 'not review' WHERE registrationFormID = ?");
$u->bind_param('i', $formID);
$u->execute();
echo "General admin set main -> pending approval\n";
print_status($formID);

// 3) Department admin (finance) marks their dept -> pending approval
$deptCol = 'financeDepartmentStatus';
$curDept = 'not review';
$deptPending = allowed_department_transition($curDept, 'approve', 'admin');
if ($deptPending === false) die("Dept admin cannot move to pending approval\n");
$q = $conn->prepare("UPDATE registrationform SET $deptCol = ? WHERE registrationFormID = ?");
$q->bind_param('si', $deptPending, $formID);
$q->execute();
echo "Finance dept admin set financeDepartmentStatus -> $deptPending\n";
print_status($formID);

// 4) Department head finalizes finance -> approved
$curDept = $deptPending;
$deptFinal = allowed_department_transition($curDept, 'approve', 'admin_head');
if ($deptFinal === false) die("Dept head cannot finalize approval\n");
$q = $conn->prepare("UPDATE registrationform SET $deptCol = ? WHERE registrationFormID = ?");
$q->bind_param('si', $deptFinal, $formID);
$q->execute();
echo "Finance dept head set financeDepartmentStatus -> $deptFinal\n";
print_status($formID);

// 5) Fast-forward: mark other departments approved to trigger main approval
$conn->query("UPDATE registrationform SET projectDepartmentStatus='approved', legalDepartmentStatus='approved', planDepartmentStatus='approved' WHERE registrationFormID = $formID");
echo "Other departments set to 'approved'\n";
print_status($formID);

// 6) Check if all depts approved and finalize main
$row = $conn->query("SELECT financeDepartmentStatus, projectDepartmentStatus, legalDepartmentStatus, planDepartmentStatus FROM registrationform WHERE registrationFormID = $formID")->fetch_assoc();
$all = array_map('strtolower', $row);
if ($all['financeDepartmentStatus'] === 'approved' && $all['projectDepartmentStatus'] === 'approved' && $all['legalDepartmentStatus'] === 'approved' && $all['planDepartmentStatus'] === 'approved') {
    $conn->query("UPDATE registrationform SET status='approved' WHERE registrationFormID = $formID");
    echo "All departments approved — main status set to 'approved'\n";
}
print_status($formID);

// Cleanup: optional — leave the test row for inspection
echo "E2E test completed. Test form ID: $formID\n";
?>