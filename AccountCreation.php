<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Prefer Composer autoload when PHPMailer is installed into `vendor/`
// If you installed PHPMailer via Composer: run `composer require phpmailer/phpmailer`
// and use the autoloader below. Otherwise adjust the path to where you moved PHPMailer.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback to the old direct includes if vendor/autoload.php is not present
    require  __DIR__ . '/PHPMailer/src/Exception.php';
    require  __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require  __DIR__ . '/PHPMailer/src/SMTP.php';
}

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/config.php';

// Prevent browser caching (important for back/refresh)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

date_default_timezone_set('Asia/Kuala_Lumpur');

// Protect page (admin only or head-of-department)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'admin_head'], true)) {
    header("Location: index.php");
    exit();
}

$message = "";
$messageType = ""; // success | error

// Show message from GET if redirected
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? '';
}

// Generate token ONLY on GET requests (displaying the form)
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (empty($_SESSION['form_token'])) {
        $_SESSION['form_token'] = bin2hex(random_bytes(16));
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $vendorType = isset($_POST['vendor_type']) ? trim($_POST['vendor_type']) : '';
    $adminDepartment = isset($_POST['admin_department']) ? trim($_POST['admin_department']) : '';

    // --- FIX STARTS HERE ---
    // One-time token check: strictly check if session token exists before comparing
    if (
        !isset($_POST['form_token']) || 
        !isset($_SESSION['form_token']) || 
        !hash_equals($_SESSION['form_token'], $_POST['form_token'])
    ) {
        // If token invalid or missing, redirect with error
        header("Location: " . $_SERVER['PHP_SELF'] .
            "?msg=" . urlencode("Invalid or duplicate form submission (Session expired). Please try again.") .
            "&type=error");
        exit();
    }
    // --- FIX ENDS HERE ---

    // Invalidate token immediately after use to prevent double submission
    unset($_SESSION['form_token']);

    $newCompanyRegistrationNumber = isset($_POST['newcompanyregistration']) ? trim($_POST['newcompanyregistration']) : '';

    // Allowed roles and vendor types
    $allowedRoles = ['admin', 'vendor', 'admin_head'];
    $allowedVendorTypes = ['Civil Contractor', 'Supplier', 'TMP Contractor', 'General Contractor'];
    // Store simple department keys for vendorType: General, Finance, Legal, Project, Plan
    $allowedAdminDepartments = ['General', 'Finance', 'Legal', 'Project', 'Plan'];

    $hasError = false;
    if (empty($email)) {
        $message = "Email is required.";
        $messageType = "error";
        $hasError = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
        $hasError = true;
    } elseif (empty($role) || !in_array($role, $allowedRoles)) {
        $message = "Please select a valid role.";
        $messageType = "error";
        $hasError = true;
    } elseif ($role === 'vendor' && (empty($vendorType) || !in_array($vendorType, $allowedVendorTypes))) {
        $message = "Please select a valid vendor type.";
        $messageType = "error";
        $hasError = true;
    } elseif (($role === 'admin' || $role === 'admin_head') && (empty($adminDepartment) || !in_array($adminDepartment, $allowedAdminDepartments))) {
        $message = "Please select a valid admin department.";
        $messageType = "error";
        $hasError = true;
    } elseif ($role === 'vendor' && empty($newCompanyRegistrationNumber)) {
        $message = "Company Registration Number is required for vendor accounts.";
        $messageType = "error";
        $hasError = true;
    }

    // Check if email already exists (enforce uniqueness for admin accounts including heads)
    if (!$hasError && ($role === 'admin' || $role === 'admin_head')) {
        $checkStmt = $conn->prepare("SELECT username FROM vendoraccount WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $message = "This email is already registered for an admin account.";
            $messageType = "error";
            $hasError = true;
        }
    }

    // If creating an admin_head, ensure only one head exists per department
    if (!$hasError && $role === 'admin_head') {
        $deptCheck = $conn->prepare("SELECT username FROM vendoraccount WHERE vendorType = ? AND role = 'admin_head' LIMIT 1");
        $deptCheck->bind_param('s', $adminDepartment);
        $deptCheck->execute();
        $deptRes = $deptCheck->get_result();
        if ($deptRes && $deptRes->num_rows > 0) {
            $message = "The selected head role is already assigned to another admin.";
            $messageType = "error";
            $hasError = true;
        }
    }

    // For vendors, check company registration number
    if (!$hasError && $role === 'vendor') {
        $checkCRStmt = $conn->prepare("SELECT username FROM vendoraccount WHERE newCompanyRegistrationNumber = ?");
        $checkCRStmt->bind_param("s", $newCompanyRegistrationNumber);
        $checkCRStmt->execute();
        $checkCRResult = $checkCRStmt->get_result();
        if ($checkCRResult->num_rows > 0) {
            $message = "This Company Registration Number is already registered.";
            $messageType = "error";
            $hasError = true;
        }
    }

    // If no errors, create account and send email
    if (!$hasError) {
        // Generate setup token
        $setupToken = bin2hex(random_bytes(32));
        $tokenExpiry = date("Y-m-d H:i:s", strtotime("+24 hours"));

        // accountID will be generated by the database (AUTO_INCREMENT)

        // For admin accounts, save the chosen admin department into `vendorType`.
        $storeVendorType = null;
        if ($role === 'vendor') {
            $storeVendorType = $vendorType;
        } elseif ($role === 'admin' || $role === 'admin_head') {
            $storeVendorType = $adminDepartment ?: 'General';
        }
        $storeNewCompanyRegistrationNumber = ($role === 'vendor') ? $newCompanyRegistrationNumber : null;
        $tempUsername = "PENDING_" . bin2hex(random_bytes(4));

        $stmt = $conn->prepare(
            "INSERT INTO vendoraccount (newCompanyRegistrationNumber, username, email, role, vendorType, resetToken, resetExpiry) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssss", $storeNewCompanyRegistrationNumber, $tempUsername, $email, $role, $storeVendorType, $setupToken, $tokenExpiry);

        if ($stmt->execute()) {
            // Send setup email
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = MAIL_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username = MAIL_USER;
                $mail->Password = MAIL_PASS;
                $mail->SMTPSecure = MAIL_ENCRYPTION;
                $mail->Port       = MAIL_PORT;

                $mail->setFrom(MAIL_USER, 'Vendor System');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Complete Your Account Setup';

                // Ensure localhost setup link is correct for your environment
                $baseUrl = APP_URL; // define in config.php
                $setupLink = $baseUrl . "AccountSetup.php?token=" . urlencode($setupToken);

                $mail->Body = "
                    <h2>Welcome to the Vendor System</h2><br><br>
                    You have been invited to create an account.<br><br>
                    Please click the link below to set up your account:<br>
                    <a href='" . htmlspecialchars($setupLink) . "'>Complete Your Account Setup</a><br><br>
                    This link will expire in 24 hours.<br><br>
                    If you did not request this, please ignore this email.<br><br>
                    Regards,<br>
                    Vendor System Team
                ";

                $mail->send();

                $message = "Setup link has been sent to " . htmlspecialchars($email) . ".";
                $messageType = "success";
            } catch (Exception $e) {
                // Account was created but email failed
                $message = "Account invitation created but failed to send email. Error: " . $mail->ErrorInfo;
                $messageType = "error";
            }
        } else {
            $message = "Error creating account invitation. Please try again.";
            $messageType = "error";
        }
    }
    
    // Redirect after POST to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($message) . "&type=" . urlencode($messageType));
    exit();
}

// Generate a fresh token for the next form render if one doesn't exist
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(16));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <style>
        :root {
            --primary-color: #059669;
            --primary-hover: #047857;
            --bg-gradient: linear-gradient(135deg, #064e3b, #065f46);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-gradient);
            font-family: 'Inter', -apple-system, sans-serif;
        }

        .create-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .create-card h2 {
            margin: 0 0 10px;
            font-size: 24px;
            color: var(--text-main);
        }

        .create-card p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-main);
        }

        input, select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            border: none;
            color: white;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }

        button:hover {
            background: var(--primary-hover);
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid;
        }

        .success {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .error {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .links {
            margin-top: 25px;
            font-size: 13px;
        }

        .links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .links a:hover {
            text-decoration: underline;
        }

        #vendorTypeDiv {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<script>
function toggleVendorType() {
    const roleSelect = document.getElementById('roleSelect');
    const vendorTypeDiv = document.getElementById('vendorTypeDiv');
    const vendorTypeSelect = document.getElementById('vendorTypeSelect');
    const companyRegDiv = document.getElementById('companyRegDiv');
    const companyRegInput = document.getElementById('newcompanyregistration');
    
    const adminDeptDiv = document.getElementById('adminDeptDiv');
    const adminDeptSelect = document.getElementById('adminDepartmentSelect');

    if (roleSelect.value === 'vendor') {
        vendorTypeDiv.style.display = 'block';
        vendorTypeSelect.required = true;
        companyRegDiv.style.display = 'block';
        companyRegInput.required = true;

        // Hide admin fields
        adminDeptDiv.style.display = 'none';
        adminDeptSelect.required = false;
        adminDeptSelect.value = '';
    } else if (roleSelect.value === 'admin' || roleSelect.value === 'admin_head') {
        // Show admin department selector and hide vendor-only fields
        adminDeptDiv.style.display = 'block';
        adminDeptSelect.required = true;

        vendorTypeDiv.style.display = 'none';
        vendorTypeSelect.required = false;
        vendorTypeSelect.value = '';

        companyRegDiv.style.display = 'none';
        companyRegInput.required = false;
        companyRegInput.value = '';
    } else {
        // No role selected: hide both vendor and admin specific fields
        vendorTypeDiv.style.display = 'none';
        vendorTypeSelect.required = false;
        vendorTypeSelect.value = '';

        companyRegDiv.style.display = 'none';
        companyRegInput.required = false;
        companyRegInput.value = '';

        adminDeptDiv.style.display = 'none';
        adminDeptSelect.required = false;
        adminDeptSelect.value = '';
    }
}

// Run toggle function when page loads to check for cached form values
window.addEventListener('load', function() {
    toggleVendorType();
});
</script>

<div class="create-card">
    <h2>Create Account</h2>
    <p>Set up a new vendor or admin account</p>

    <?php if ($message): ?>
        <div class="message <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="post" id="createAccountForm">

        <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($_SESSION['form_token']); ?>">

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="Enter email address" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="roleSelect" required onchange="toggleVendorType()">
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="admin_head">Admin - Head of Department</option>
                <option value="vendor">Vendor</option>
            </select>
        </div>

            <div id="adminDeptDiv" style="display:none;">
                <div class="form-group">
                    <label for="adminDepartmentSelect">Admin Department</label>
                    <select name="admin_department" id="adminDepartmentSelect">
                        <option value="General" selected>General</option>
                        <option value="Finance">Finance</option>
                        <option value="Legal">Legal</option>
                        <option value="Project">Project</option>
                        <option value="Plan">Plan</option>
                    </select>
                </div>
            </div>

        <div id="companyRegDiv" style="display:none;">
            <div class="form-group">
                <label for="newcompanyregistration">Company Registration Number</label>
                <input type="text" name="newcompanyregistration" id="newcompanyregistration" placeholder="Enter Company Registration Number">
            </div>
        </div>

        <div id="vendorTypeDiv" style="display:none;">
            <div class="form-group">
                <label for="vendorTypeSelect">Vendor Type</label>
                <select name="vendor_type" id="vendorTypeSelect">
                    <option value="">-- Select Vendor Type --</option>
                    <option value="Civil Contractor">Civil Contractor</option>
                    <option value="Supplier">Supplier</option>
                    <option value="TMP Contractor">TMP Contractor</option>
                    <option value="General Contractor">General Contractor</option>
                </select>
            </div>
        </div>

        <button type="submit">Send Setup Link</button>
    </form>

    <div class="links">
        <a href="AdminVendorManagement.php">← Back to Admin Panel</a>
    </div>
</div>

</body>
</html>