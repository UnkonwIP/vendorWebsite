
<?php

require_once "config.php";
// Use Asia/Kuala_Lumpur for reset expiry comparison
date_default_timezone_set('Asia/Kuala_Lumpur');

$token = $_GET['token'] ?? "";
$message = "";
$tokenValid = false;

if (!$token) {
    $message = "Invalid reset link.";
} else {
    // Validate token exists and is not expired
    $stmt = $conn->prepare(
        "SELECT accountID FROM vendoraccount
         WHERE resetToken = ? AND resetExpiry > NOW() LIMIT 1"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $tokenValid = true;
    } else {
        $message = "Invalid or expired reset link.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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

        .card button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            border: none;
            color: white;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        .card button[type="submit"]:hover {
            background: #1e3c72;
        }

        .card button[type="submit"]:disabled {
            background: #ccc;
            cursor: not-allowed;
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

        .password-field {
            position: relative;
            width: 100%;
            display: block;
            margin-bottom: 15px;
            text-align: left;
        }

        .password-field input {
            width: 100%;
            padding: 12px 45px 12px 12px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            display: block;
            margin-bottom: 0;
        }

        .password-field button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 18px;
            padding: 5px;
            width: auto;
            height: auto;
            line-height: 1;
            z-index: 10;
        }

        .form-text {
            text-align: left;
            margin-bottom: 0.5em;
            font-size: 13px;
        }

        .strength-feedback {
            font-size: 13px;
            color: #d32f2f;
            text-align: left;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>Reset Password</h2>
    <?php if ($message): ?>
        <div class="message <?php echo ($message === 'Invalid or expired reset link.') ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($tokenValid): ?>
    <form id="resetForm" autocomplete="off">
        <div class="password-field">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="button" id="togglePassword" tabindex="-1">
                👁️
            </button>
        </div>
        <div class="form-text">Must be at least 8 characters, include upper/lowercase, number, and symbol.</div>
        <div id="strengthFeedback" class="strength-feedback"></div>

        <div class="password-field">
            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
            <button type="button" id="toggleConfirmPassword" tabindex="-1">
                👁️
            </button>
        </div>

        <button type="submit" id="submitBtn">Update Password</button>
        <div id="formLoading" style="display:none; margin:10px 0; color:#2a5298;">Processing...</div>
    </form>
    <?php else: ?>
        <p>Please use the reset link sent to your email to reset your password.</p>
        <div class="links">
            <a href="index.php">← Back to Login</a>
        </div>
    <?php endif; ?>

    <?php if ($tokenValid): ?>
    <div class="links">
        <a href="index.php">← Back to Login</a>
    </div>
    <?php endif; ?>
</div>
<script>
// Password strength validation
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirmPassword');
const feedback = document.getElementById('strengthFeedback');
const submitBtn = document.getElementById('submitBtn');
const togglePassword = document.getElementById('togglePassword');
const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
const form = document.getElementById('resetForm');
const formLoading = document.getElementById('formLoading');
const token = "<?php echo htmlspecialchars($token); ?>";

function checkStrength(pw) {
    let msg = [];
    if (pw.length < 8) msg.push('at least 8 characters');
    if (!/[A-Z]/.test(pw)) msg.push('an uppercase letter');
    if (!/[a-z]/.test(pw)) msg.push('a lowercase letter');
    if (!/[0-9]/.test(pw)) msg.push('a number');
    if (!/[^A-Za-z0-9]/.test(pw)) msg.push('a symbol');
    return msg.length === 0 ? '' : 'Password must contain ' + msg.join(', ') + '.';
}

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

function updateStrength() {
    const pw = passwordInput.value;
    const confirmPw = confirmPasswordInput.value;
    const msg = checkStrength(pw);
    
    // If strength messages exist, show them immediately
    if (msg) {
        feedback.textContent = msg;
        feedback.style.color = '#d32f2f';
        submitBtn.disabled = true;
        return;
    }

    // Password meets strength requirements — show positive feedback
    // If confirm password exists and doesn't match, show mismatch error
    if (confirmPw && pw !== confirmPw) {
        feedback.textContent = 'Passwords do not match.';
        feedback.style.color = '#d32f2f';
        submitBtn.disabled = true;
        return;
    }

    // Good strength (and either no confirm entered yet, or confirm matches)
    feedback.textContent = 'Password strength: Good!';
    feedback.style.color = '#388e3c';
    // Enable submit only when confirm is present and matches; otherwise keep disabled
    submitBtn.disabled = !(confirmPw && pw === confirmPw);
}

if (passwordInput) {
    passwordInput.addEventListener('input', updateStrength);
    updateStrength();
}

if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', updateStrength);
}

// Click and hold: show password while button is pressed
if (togglePassword) {
    togglePassword.addEventListener('mousedown', function(e) {
        e.preventDefault();
        passwordInput.type = 'text';
        togglePassword.textContent = '🙈';
    });
    
    togglePassword.addEventListener('mouseup', function(e) {
        e.preventDefault();
        passwordInput.type = 'password';
        togglePassword.textContent = '👁️';
    });
    
    togglePassword.addEventListener('mouseleave', function(e) {
        e.preventDefault();
        passwordInput.type = 'password';
        togglePassword.textContent = '👁️';
    });
    
    // Touch support
    togglePassword.addEventListener('touchstart', function(e) {
        e.preventDefault();
        passwordInput.type = 'text';
        togglePassword.textContent = '🙈';
    });
    
    togglePassword.addEventListener('touchend', function(e) {
        e.preventDefault();
        passwordInput.type = 'password';
        togglePassword.textContent = '👁️';
    });
}

// Click and hold: show confirm password while button is pressed
if (toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('mousedown', function(e) {
        e.preventDefault();
        confirmPasswordInput.type = 'text';
        toggleConfirmPassword.textContent = '🙈';
    });
    
    toggleConfirmPassword.addEventListener('mouseup', function(e) {
        e.preventDefault();
        confirmPasswordInput.type = 'password';
        toggleConfirmPassword.textContent = '👁️';
    });
    
    toggleConfirmPassword.addEventListener('mouseleave', function(e) {
        e.preventDefault();
        confirmPasswordInput.type = 'password';
        toggleConfirmPassword.textContent = '👁️';
    });
    
    // Touch support
    toggleConfirmPassword.addEventListener('touchstart', function(e) {
        e.preventDefault();
        confirmPasswordInput.type = 'text';
        toggleConfirmPassword.textContent = '🙈';
    });
    
    toggleConfirmPassword.addEventListener('touchend', function(e) {
        e.preventDefault();
        confirmPasswordInput.type = 'password';
        toggleConfirmPassword.textContent = '👁️';
    });
}

// Form submission with AJAX
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearMessage();
        
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();
        
        // Validate locally
        const strengthMsg = checkStrength(password);
        if (strengthMsg) {
            showMessage(strengthMsg, 'error');
            return;
        }
        
        if (password !== confirmPassword) {
            showMessage('Passwords do not match.', 'error');
            return;
        }
        
        // Show loading and disable button
        formLoading.style.display = 'block';
        submitBtn.disabled = true;
        
        // AJAX: submit reset password
        const params = new URLSearchParams();
        params.append('token', token);
        params.append('password', password);
        
        fetch('APIResetPasswordHandler.php', {
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
                // Redirect after success
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 2000);
            }
        })
        .catch(() => {
            formLoading.style.display = 'none';
            submitBtn.disabled = false;
            showMessage('Error updating password. Please try again.', 'error');
        });
    });
}
</script>
</body>
</html>
