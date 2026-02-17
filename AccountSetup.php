<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'config.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

$message = "";
$messageType = ""; // success | error
$tokenValid = false;
$setupToken = "";

// Check if token exists in URL
if (isset($_GET['token'])) {
    $setupToken = trim($_GET['token']);
    
    // Validate token against database (new schema: resetToken, resetExpiry)
    $stmt = $conn->prepare(
        "SELECT accountID, email FROM vendoraccount 
        WHERE resetToken = ? AND resetExpiry > NOW() AND username LIKE 'PENDING_%'"
    );
    $stmt->bind_param("s", $setupToken);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $tokenValid = true;
    } else {
        $message = "Invalid or expired setup link. Please request a new account invitation.";
        $messageType = "error";
    }
}

// Get role and vendor type from token
$userRole = '';
$userVendorType = '';
if ($tokenValid) {
    $stmt = $conn->prepare(
        "SELECT role, vendorType FROM vendoraccount 
        WHERE resetToken = ? AND resetExpiry > NOW()"
    );
    $stmt->bind_param("s", $setupToken);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $userRole = $row['role'];
    $userVendorType = $row['vendorType'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && $tokenValid) {
    $accountID = trim($_POST['accountID']);
    $username = trim($_POST['username']);
    $password  = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Get the email from the valid token
    $stmt = $conn->prepare(
        "SELECT email FROM vendoraccount 
        WHERE resetToken = ? AND resetExpiry > NOW() AND username LIKE 'PENDING_%'"
    );
    $stmt->bind_param("s", $setupToken);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $email = $row['email'];

    // Validation
    if (empty($accountID) || empty($username) || empty($password) || empty($confirmPassword)) {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "error";
    } else {
        // Check if account ID already exists (exclude PENDING_ usernames)
        $checkStmt = $conn->prepare("SELECT accountID FROM vendoraccount WHERE accountID = ? AND username NOT LIKE 'PENDING_%'");
        $checkStmt->bind_param("s", $accountID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "Account ID already exists. Please choose a different one.";
            $messageType = "error";
        } else {
            // Hash password and update account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $updateStmt = $conn->prepare(
                "UPDATE vendoraccount 
                SET accountID = ?, username = ?, passwordHash = ?, resetToken = NULL, resetExpiry = NULL
                WHERE email = ? AND resetToken = ? AND username LIKE 'PENDING_%'"
            );
            $updateStmt->bind_param("sssss", $accountID, $username, $hashedPassword, $email, $setupToken);

            if ($updateStmt->execute()) {
                $message = "Account setup completed successfully! You can now login with your account ID and password.";
                $messageType = "success";
                $tokenValid = false; // Hide form after successful setup
            } else {
                $message = "Error setting up account. Please try again.";
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Account Setup</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: #fff;
            width: 450px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
        }

        .card h2 {
            margin-bottom: 10px;
            color: #333;
        }

        .card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .card input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .card button {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            border: none;
            color: white;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        .card button:hover {
            background: #1e3c72;
        }

        .message {
            margin-bottom: 15px;
            font-size: 14px;
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            color: green;
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .error {
            color: red;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .links {
            margin-top: 20px;
            font-size: 13px;
        }

        .links a {
            color: #2a5298;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="card">
    <h2>Vendor Account Setup</h2>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($tokenValid): ?>
        <p>Complete your account setup below.</p>

        <form method="post" autocomplete="off" id="setupForm">
            <input type="text" name="accountID" placeholder="Account ID" required>

            <input type="text" name="username" placeholder="Username" required>

            <div style="position: relative; width: 100%; display: block; margin-bottom: 15px; text-align: left;">
                <input type="password" name="password" id="password" placeholder="Password" required 
                    style="width: 100%; padding: 12px 45px 12px 12px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; display: block;">
                
                <button type="button" id="togglePassword" tabindex="-1" 
                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; font-size: 18px; padding: 5px; width: auto; height: auto; line-height: 1; z-index: 10;">
                    👁️
                </button>
            </div>
            <div id="passwordHelp" class="form-text" style="text-align:left;margin-bottom:0.5em;font-size:13px;">Must be at least 8 characters, include upper/lowercase, number, and symbol.</div>
            <div id="strengthFeedback" class="strength-feedback" style="font-size:13px;color:#d32f2f;text-align:left;margin-bottom:10px;"></div>

            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>

            <button type="submit" id="submitBtn">Complete Setup</button>
        </form>
        <script>
        // Password strength validation (same as reset_password.php)
        const passwordInput = document.getElementById('password');
        const feedback = document.getElementById('strengthFeedback');
        const submitBtn = document.getElementById('submitBtn');
        const togglePassword = document.getElementById('togglePassword');
        function checkStrength(pw) {
            let msg = [];
            if (pw.length < 8) msg.push('at least 8 characters');
            if (!/[A-Z]/.test(pw)) msg.push('an uppercase letter');
            if (!/[a-z]/.test(pw)) msg.push('a lowercase letter');
            if (!/[0-9]/.test(pw)) msg.push('a number');
            if (!/[^A-Za-z0-9]/.test(pw)) msg.push('a symbol');
            return msg.length === 0 ? '' : 'Password must contain ' + msg.join(', ') + '.';
        }
        function updateStrength() {
            const pw = passwordInput.value;
            const msg = checkStrength(pw);
            feedback.textContent = msg;
            if (msg) {
                feedback.style.color = '#d32f2f';
                submitBtn.disabled = true;
            } else {
                feedback.textContent = 'Password strength: Good!';
                feedback.style.color = '#388e3c';
                submitBtn.disabled = false;
            }
        }
        if (passwordInput) {
            passwordInput.addEventListener('input', updateStrength);
            updateStrength();
        }
        if (togglePassword) {
            togglePassword.addEventListener('click', function(e) {
                e.preventDefault();
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    togglePassword.textContent = '🙈';
                } else {
                    passwordInput.type = 'password';
                    togglePassword.textContent = '👁️';
                }
            });
        }
        </script>
    <?php else: ?>
        <p>Please use the setup link sent to your email to create your account.</p>
        
        <div class="links">
            <a href="index.php">← Back to Login</a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
