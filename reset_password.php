
<?php

require_once "config.php";
// Use Asia/Kuala_Lumpur for reset expiry comparison
date_default_timezone_set('Asia/Kuala_Lumpur');

$token = $_POST['token'] ?? $_GET['token'] ?? "";
$message = "";
$success = false;
// DEBUG: Show token and DB row for troubleshooting
// echo '<div style="background:#fffbe6;border:1px solid #ffe58f;padding:10px;margin-bottom:15px;font-size:0.95em;">';
// echo '<b>DEBUG:</b><br>Token from URL/POST: <code>' . htmlspecialchars($token ?? '') . '</code><br>';
// if ($token) {
//     $debug = $conn->query("SELECT accountID, resetToken, resetExpiry, NOW() as now FROM vendoraccount WHERE resetToken = '" . $conn->real_escape_string($token) . "'");
//     if ($debug && $debug->num_rows > 0) {
//         $row = $debug->fetch_assoc();
//         echo 'DB resetToken: <code>' . htmlspecialchars($row['resetToken']) . '</code><br>';
//         echo 'DB resetExpiry: <code>' . htmlspecialchars($row['resetExpiry']) . '</code><br>';
//         echo 'DB NOW(): <code>' . htmlspecialchars($row['now']) . '</code><br>';
//         echo 'DB accountID: <code>' . htmlspecialchars($row['accountID']) . '</code><br>';
//     } else {
//         echo 'No matching token found in DB.';
//     }
// }
// echo '</div>';

if (!$token) {
    $message = "Invalid reset link.";
} else if (isset($_POST['update'])) {
    $password = $_POST['password'] ?? '';
    // Server-side password strength validation
    $valid = strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[^A-Za-z0-9]/', $password);
    if (!$valid) {
        $message = "Password does not meet strength requirements.";
    } else {
        $stmt = $conn->prepare(
            "SELECT accountID, resetExpiry FROM vendoraccount
             WHERE resetToken = ? AND resetExpiry > NOW()"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $newPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare(
                "UPDATE vendoraccount
                 SET passwordHash=?, resetToken=NULL, resetExpiry=NULL
                 WHERE accountID=?"
            );
            $update->bind_param("ss", $newPassword, $row['accountID']);
            $update->execute();
            $success = true;
            $message = "Password updated successfully. Redirecting to <a href='index.php'>login</a>...";
        } else {
            $message = "Invalid or expired reset link.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-container { background: #fff; padding: 2.5rem 2rem; border-radius: 1rem; box-shadow: 0 2px 16px rgba(0,0,0,0.08); max-width: 400px; width: 100%; }
        .form-label { font-weight: 500; }
        .strength-feedback { font-size: 0.95em; margin-top: 0.25rem; }
    </style>
</head>
<body>
<div class="reset-container">
    <h2 class="mb-4 text-center">Reset Password</h2>
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>" role="alert">
            <?php echo $message; ?>
        </div>
        <?php if ($success): ?>
        <script>
            setTimeout(function(){ window.location.href = 'index.php'; }, 5000);
        </script>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!$success): ?>
    <form method="post" autocomplete="off" id="resetForm">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" required aria-describedby="passwordHelp">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1"><span id="eyeIcon">👁️</span></button>
            </div>
            <div id="passwordHelp" class="form-text">Must be at least 8 characters, include upper/lowercase, number, and symbol.</div>
            <div id="strengthFeedback" class="strength-feedback text-danger"></div>
        </div>
        <button type="submit" name="update" class="btn btn-primary w-100" id="submitBtn">Update Password</button>
    </form>
    <?php endif; ?>
</div>
<script>
// Password strength validation
const passwordInput = document.getElementById('password');
const feedback = document.getElementById('strengthFeedback');
const submitBtn = document.getElementById('submitBtn');
const togglePassword = document.getElementById('togglePassword');
const eyeIcon = document.getElementById('eyeIcon');

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
        feedback.classList.remove('text-success');
        feedback.classList.add('text-danger');
        submitBtn.disabled = true;
    } else {
        feedback.textContent = 'Password strength: Good!';
        feedback.classList.remove('text-danger');
        feedback.classList.add('text-success');
        submitBtn.disabled = false;
    }
}
if (passwordInput) {
    passwordInput.addEventListener('input', updateStrength);
    updateStrength();
}
if (togglePassword) {
    togglePassword.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.textContent = '🙈';
        } else {
            passwordInput.type = 'password';
            eyeIcon.textContent = '👁️';
        }
    });
}
</script>
</body>
</html>
