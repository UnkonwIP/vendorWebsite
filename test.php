<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'config.php';

session_start();

// Prevent browser caching (important for back/refresh)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

include "database.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

// Admin-only protection
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Message handling (GET only)
$message = $_GET['msg'] ?? '';
$messageType = $_GET['type'] ?? '';

// Generate one-time form token
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // One-time token check
    if (
        !isset($_POST['form_token']) ||
        !hash_equals($_SESSION['form_token'], $_POST['form_token'])
    ) {
        header("Location: " . $_SERVER['PHP_SELF'] .
            "?msg=" . urlencode("Invalid or duplicate form submission.") .
            "&type=error");
        exit();
    }

    // Invalidate token immediately
    unset($_SESSION['form_token']);

    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $vendorType = trim($_POST['vendor_type'] ?? '');
    $companyReg = trim($_POST['newcompanyregistration'] ?? '');

    $allowedRoles = ['admin', 'vendor'];
    $allowedVendorTypes = ['Civil Contractor', 'Supplier', 'TMP Contractor', 'General Contractor'];

    $hasError = false;

    if (empty($email)) {
        $msg = "Email is required.";
        $type = "error";
        $hasError = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email address.";
        $type = "error";
        $hasError = true;
    } elseif (!in_array($role, $allowedRoles)) {
        $msg = "Invalid role selected.";
        $type = "error";
        $hasError = true;
    } elseif ($role === 'vendor' && !in_array($vendorType, $allowedVendorTypes)) {
        $msg = "Invalid vendor type.";
        $type = "error";
        $hasError = true;
    } elseif ($role === 'vendor' && empty($companyReg)) {
        $msg = "Company Registration Number is required.";
        $type = "error";
        $hasError = true;
    }

    // Email uniqueness
    if (!$hasError) {
        $stmt = $conn->prepare("SELECT id FROM vendoraccount WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $msg = "This email is already registered.";
            $type = "error";
            $hasError = true;
        }
    }

    // Company registration uniqueness
    if (!$hasError && $role === 'vendor') {
        $stmt = $conn->prepare(
            "SELECT id FROM vendoraccount WHERE newCompanyRegistrationNumber = ?"
        );
        $stmt->bind_param("s", $companyReg);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $msg = "Company Registration Number already exists.";
            $type = "error";
            $hasError = true;
        }
    }

    if (!$hasError) {

        // Generate setup token
        $setupToken = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));

        // Generate account ID
        function generateAccountID($length = 15) {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $id = '';
            for ($i = 0; $i < $length; $i++) {
                $id .= $chars[random_int(0, strlen($chars) - 1)];
            }
            return $id;
        }

        $accountID = generateAccountID();
        $username = "PENDING_" . bin2hex(random_bytes(4));

        $storeVendorType = ($role === 'vendor') ? $vendorType : null;
        $storeCompanyReg = ($role === 'vendor') ? $companyReg : null;

        $stmt = $conn->prepare(
            "INSERT INTO vendoraccount 
            (newCompanyRegistrationNumber, accountID, username, email, role, vendorType, resetToken, resetExpiry)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssssss",
            $storeCompanyReg,
            $accountID,
            $username,
            $email,
            $role,
            $storeVendorType,
            $setupToken,
            $expiry
        );

        if ($stmt->execute()) {

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USER;
                $mail->Password = MAIL_PASS;
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom(MAIL_USER, 'Vendor System');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Complete Your Account Setup';

                $setupLink = APP_URL . "/VendorSetup.php?token=" . urlencode($setupToken);

                $mail->Body = "
                    <h2>Welcome to the Vendor System</h2>
                    <p>Please click the link below to set up your account:</p>
                    <p><a href='{$setupLink}'>Complete Account Setup</a></p>
                    <p>This link expires in 24 hours.</p>
                ";

                $mail->send();

                $msg = "Setup link sent to {$email}.";
                $type = "success";

            } catch (Exception $e) {
                $msg = "Account created but email failed to send.";
                $type = "error";
            }

        } else {
            $msg = "Failed to create account.";
            $type = "error";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] .
        "?msg=" . urlencode($msg) .
        "&type=" . urlencode($type));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Account</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Arial;background:#064e3b;color:#111;display:flex;justify-content:center;align-items:center;height:100vh}
.card{background:#fff;padding:30px;border-radius:12px;width:100%;max-width:420px}
label{font-size:13px;font-weight:bold}
input,select,button{width:100%;padding:10px;margin-top:6px;margin-bottom:14px}
button{background:#059669;color:#fff;border:none;border-radius:6px;font-weight:bold}
.success{background:#ecfdf5;color:#065f46;padding:10px;border-radius:6px}
.error{background:#fef2f2;color:#991b1b;padding:10px;border-radius:6px}
</style>

<script>
function toggleVendor() {
    const role = document.getElementById('role').value;
    document.getElementById('vendorFields').style.display =
        role === 'vendor' ? 'block' : 'none';
}
</script>
</head>
<body>

<div class="card">
<h2>Create Account</h2>

<?php if ($message): ?>
<div class="<?= htmlspecialchars($messageType) ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">

<label>Email</label>
<input type="email" name="email" required>

<label>Role</label>
<select name="role" id="role" onchange="toggleVendor()" required>
<option value="">-- Select --</option>
<option value="admin">Admin</option>
<option value="vendor">Vendor</option>
</select>

<div id="vendorFields" style="display:none;">
<label>Company Registration Number</label>
<input type="text" name="newcompanyregistration">

<label>Vendor Type</label>
<select name="vendor_type">
<option value="">-- Select --</option>
<option>Civil Contractor</option>
<option>Supplier</option>
<option>TMP Contractor</option>
<option>General Contractor</option>
</select>
</div>

<button type="submit">Send Setup Link</button>
</form>
</div>

</body>
</html>
