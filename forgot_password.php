<?php
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
date_default_timezone_set('Asia/Kuala_Lumpur');

$message = "";
$messageType = ""; // success | error
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>

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
        width: 380px;
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
    }

    .success {
        color: green;
    }

    .error {
        color: red;
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
    <h2>Forgot Password</h2>
    <p>Enter your username (admins) or username/company registration number (vendors) to receive a password reset link.</p>

    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form id="forgotPasswordForm" autocomplete="off" aria-label="Forgot Password Form">
        <label for="identifier" class="visually-hidden">Username or Company Registration Number</label>
        <input type="text" id="identifier" name="identifier" placeholder="Username or Company Registration Number" required aria-required="true" aria-label="Username or Company Registration Number" autocomplete="off">
        <button type="submit" id="submitBtn">Send Reset Link</button>
        <div id="formLoading" style="display:none; margin:10px 0; color:#2a5298;">Processing...</div>
    </form>

    <div class="links">
        <a href="index.php">← Back to Login</a>
    </div>
</div>

</body>
<script>
// Accessibility: visually hidden class
const style = document.createElement('style');
style.innerHTML = `.visually-hidden { position: absolute !important; height: 1px; width: 1px; overflow: hidden; clip: rect(1px, 1px, 1px, 1px); white-space: nowrap; }`;
document.head.appendChild(style);

const identifierInput = document.getElementById('identifier');
const form = document.getElementById('forgotPasswordForm');
const submitBtn = document.getElementById('submitBtn');
const formLoading = document.getElementById('formLoading');

function showMessage(msg, type) {
    let msgDiv = document.querySelector('.message');
    if (!msgDiv) {
        msgDiv = document.createElement('div');
        msgDiv.className = 'message';
        form.insertBefore(msgDiv, form.firstChild);
    }
    msgDiv.textContent = msg;
    msgDiv.className = 'message ' + (type || '');
    msgDiv.setAttribute('role', 'alert');
}

function clearMessage() {
    let msgDiv = document.querySelector('.message');
    if (msgDiv) msgDiv.remove();
}

// No email validation: using identifier (username or company registration number)

form.addEventListener('submit', function(e) {
    e.preventDefault();
    clearMessage();
    const identifier = identifierInput.value.trim();
    if (!identifier) {
        showMessage('Please enter your username or company registration number.', 'error');
        return;
    }
    formLoading.style.display = 'block';
    submitBtn.disabled = true;
    // AJAX: submit forgot password with identifier
    const params = new URLSearchParams();
    params.append('identifier', identifier);
    params.append('reset', '1');
    fetch('APIForgotPasswordHandler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        formLoading.style.display = 'none';
        submitBtn.disabled = false;
        showMessage(data.message, data.status);
        if (data.status === 'success') {
            form.reset();
        }
    })
    .catch(() => {
        formLoading.style.display = 'none';
        submitBtn.disabled = false;
        showMessage('If the information is correct, you will receive a reset link.', 'success');
    });
});
</script>
</html>
