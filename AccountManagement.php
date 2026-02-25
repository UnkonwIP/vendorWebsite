<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/config.php';
// PHPMailer availability (used for OTP emails)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	require __DIR__ . '/PHPMailer/src/Exception.php';
	require __DIR__ . '/PHPMailer/src/PHPMailer.php';
	require __DIR__ . '/PHPMailer/src/SMTP.php';
}

$message = '';
$messageType = '';
$otpSent = false;

// Require login
$accountID = isset($_SESSION['accountID']) ? intval($_SESSION['accountID']) : 0;
if (empty($accountID)) {
	header('Location: index.php');
	exit();
}

// Fetch current user
$stmt = $conn->prepare('SELECT username, email, passwordHash FROM vendoraccount WHERE accountID = ? LIMIT 1');
$stmt->bind_param('i', $accountID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
	$currentUsername = $row['username'];
	$currentEmail = $row['email'];
	$currentHash = $row['passwordHash'];
} else {
	$message = 'Account not found.';
	$messageType = 'error';
	$currentUsername = '';
	$currentEmail = '';
	$currentHash = '';
}

// Home link depending on role
$homeLink = (isset($_SESSION['role']) && $_SESSION['role'] === 'vendor') ? 'VendorHomepage.php' : 'AdminHome.php';

// OTP pending state
$otpPending = isset($_SESSION['otp_pending']) && is_array($_SESSION['otp_pending']);

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// If verifying OTP
	if (!empty($_POST['otp_code'])) {
		$submittedOtp = trim($_POST['otp_code']);
		if (empty($_SESSION['otp_pending']) || !is_array($_SESSION['otp_pending'])) {
			$message = 'No OTP request pending. Please submit changes again.';
			$messageType = 'error';
		} else {
			$pending = &$_SESSION['otp_pending'];
			// check expiry
			if (time() > ($pending['expires'] ?? 0)) {
				unset($_SESSION['otp_pending']);
				$message = 'OTP expired. Please submit the password change again.';
				$messageType = 'error';
			} elseif (!empty($pending['attempts']) && $pending['attempts'] >= 5) {
				unset($_SESSION['otp_pending']);
				$message = 'Too many incorrect attempts. Please submit the password change again.';
				$messageType = 'error';
			} elseif (password_verify($submittedOtp, $pending['code_hash'])) {
				// OTP correct — apply pending updates
				$fields = [];
				$params = [];
				$types = '';
				if (!empty($pending['newUsername']) && $pending['newUsername'] !== $currentUsername) {
					$fields[] = 'username = ?'; $params[] = $pending['newUsername']; $types .= 's';
				}
				if (!empty($pending['newEmail']) && $pending['newEmail'] !== $currentEmail) {
					$fields[] = 'email = ?'; $params[] = $pending['newEmail']; $types .= 's';
				}
				if (!empty($pending['newHash'])) {
					$fields[] = 'passwordHash = ?'; $params[] = $pending['newHash']; $types .= 's';
				}
				if (!empty($fields)) {
					$types .= 'i'; $params[] = $accountID;
					$sql = 'UPDATE vendoraccount SET ' . implode(', ', $fields) . ' WHERE accountID = ? LIMIT 1';
					$upd = $conn->prepare($sql);
					$bindParams = [];
					$bindParams[] = $types;
					foreach ($params as $k => $v) $bindParams[] = &$params[$k];
					call_user_func_array([$upd, 'bind_param'], $bindParams);
					if ($upd->execute()) {
						$message = 'Account updated successfully.';
						$messageType = 'success';
						if (!empty($pending['newUsername'])) { $_SESSION['username'] = $pending['newUsername']; $currentUsername = $pending['newUsername']; }
						if (!empty($pending['newEmail'])) { $currentEmail = $pending['newEmail']; }
						if (!empty($pending['newHash'])) { $currentHash = $pending['newHash']; }
					} else {
						// Detect duplicate key (e.g., email already exists)
						$errno = $upd->errno ?: $conn->errno;
						if (intval($errno) === 1062) {
							$message = 'Email already in use by another account.';
						} else {
							$message = 'Failed to update account. Please try again.';
						}
						$messageType = 'error';
					}
					$upd->close();
				} else {
					$message = 'No pending changes to apply.';
					$messageType = 'info';
				}
				unset($_SESSION['otp_pending']);
			} else {
				// incorrect
				$pending['attempts'] = ($pending['attempts'] ?? 0) + 1;
				$remaining = 5 - $pending['attempts'];
				if ($remaining <= 0) {
					unset($_SESSION['otp_pending']);
					$message = 'Too many incorrect attempts. Please submit the password change again.';
					$messageType = 'error';
				} else {
					$message = 'Incorrect OTP. Attempts remaining: ' . $remaining;
					$messageType = 'error';
					$otpSent = true; // keep OTP input visible
				}
			}
		}
	} else {
	$newUsername = array_key_exists('username', $_POST) ? trim($_POST['username']) : null;
	$newEmail = array_key_exists('email', $_POST) ? trim($_POST['email']) : null;
	$currentPassword = $_POST['current_password'] ?? '';
	$newPassword = array_key_exists('new_password', $_POST) ? $_POST['new_password'] : '';
	$confirmPassword = array_key_exists('confirm_password', $_POST) ? $_POST['confirm_password'] : '';

	// Validate only fields that were submitted
	if ($newUsername !== null && $newUsername === '') {
		$message = 'Username cannot be empty.';
		$messageType = 'error';
	} elseif ($newEmail !== null && ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL))) {
		$message = 'Please provide a valid email address.';
		$messageType = 'error';
	} else {
		// If user wants to change password, verify current password and match new passwords
		$changingPassword = false;
		if ($newPassword !== '' || $confirmPassword !== '') {
			if ($newPassword !== $confirmPassword) {
				$message = 'New passwords do not match.';
				$messageType = 'error';
			} elseif (empty($currentPassword)) {
				$message = 'Please provide your current password to change to a new password.';
				$messageType = 'error';
			} elseif (!password_verify($currentPassword, $currentHash)) {
				$message = 'Current password is incorrect.';
				$messageType = 'error';
			} else {
				$changingPassword = true;
			}
		}

		if ($messageType !== 'error') {
			// Check email uniqueness (exclude current account) only if email submitted
			if ($newEmail !== null) {
				$check = $conn->prepare('SELECT accountID FROM vendoraccount WHERE email = ? AND accountID <> ? LIMIT 1');
				$check->bind_param('si', $newEmail, $accountID);
				$check->execute();
				$check->store_result();
				if ($check->num_rows > 0) {
					$message = 'Email already in use by another account.';
					$messageType = 'error';
					$check->close();
				} else {
					$check->close();
				}
			}
			if ($messageType === 'error') {
				// stop further processing when email check failed
			} else {

				// Build update query
				$fields = [];
				$params = [];
				$types = '';

				if ($newUsername !== null && $newUsername !== $currentUsername) {
					$fields[] = 'username = ?';
					$params[] = $newUsername;
					$types .= 's';
				}
				if ($newEmail !== null && $newEmail !== $currentEmail) {
					$fields[] = 'email = ?';
					$params[] = $newEmail;
					$types .= 's';
				}
				if ($changingPassword) {
					// Prepare new hash but do NOT persist yet — require OTP verification
					$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
					// Generate OTP and email it to the user's current email
					$otp = strval(random_int(100000, 999999));
					$otpHash = password_hash($otp, PASSWORD_DEFAULT);
					$expiry = time() + 600; // 10 minutes
					// store pending changes in session
					$_SESSION['otp_pending'] = [
						'code_hash' => $otpHash,
						'expires' => $expiry,
						'newHash' => $newHash,
						'newUsername' => $newUsername,
						'newEmail' => $newEmail,
						'attempts' => 0,
					];
					// send email
					try {
						$mail = new PHPMailer\PHPMailer\PHPMailer(true);
						$mail->isSMTP();
						$mail->Host = MAIL_HOST;
						$mail->SMTPAuth = true;
						$mail->Username = MAIL_USER;
						$mail->Password = MAIL_PASS;
						$mail->SMTPSecure = MAIL_ENCRYPTION;
						$mail->Port = MAIL_PORT;
						$mail->setFrom(MAIL_USER, 'Vendor System');
						$mail->addAddress($currentEmail);
						$mail->isHTML(true);
						$mail->Subject = 'Your OTP for password change';
						$mail->Body = "<p>Your OTP for changing your password is: <strong>" . htmlspecialchars($otp) . "</strong></p><p>This code expires in 10 minutes.</p>";
						$mail->send();
						$message = 'An OTP has been sent to your registered email. Enter it below to confirm the password change.';
						$messageType = 'info';
						$otpSent = true;
					} catch (\Exception $e) {
						$message = 'Failed to send OTP email. Please try again later.';
						$messageType = 'error';
						unset($_SESSION['otp_pending']);
					}
					// do not proceed with DB update now
				}

				if (!empty($fields)) {
					$types .= 'i'; // accountID
					$params[] = $accountID;
					$sql = 'UPDATE vendoraccount SET ' . implode(', ', $fields) . ' WHERE accountID = ? LIMIT 1';
					$upd = $conn->prepare($sql);
					// bind parameters dynamically
					$bindParams = [];
					$bindParams[] = $types;
					foreach ($params as $k => $v) $bindParams[] = &$params[$k];
					call_user_func_array([$upd, 'bind_param'], $bindParams);
					if ($upd->execute()) {
						$message = 'Account updated successfully.';
						$messageType = 'success';
						// Refresh current values and session username
						if (in_array('username = ?', $fields, true) || in_array('username = ?', $fields)) {
							$_SESSION['username'] = $newUsername;
							$currentUsername = $newUsername;
						}
						if (in_array('email = ?', $fields, true) || in_array('email = ?', $fields)) {
							$currentEmail = $newEmail;
						}
						if ($changingPassword) {
							$currentHash = $newHash;
						}
					} else {
						$errno = $upd->errno ?: $conn->errno;
						if (intval($errno) === 1062) {
							$message = 'Email already in use by another account.';
						} else {
							$message = 'Failed to update account. Please try again.';
						}
						$messageType = 'error';
					}
					$upd->close();
				} else {
					$message = 'No changes detected.';
					$messageType = 'info';
				}
			}

		}


	}


}

}

	?>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Account Settings</title>
	<style>
		body { font-family: Arial, Helvetica, sans-serif; background:#f3f4f6; margin:0; padding:40px; }
		.card { background:#fff; max-width:560px; margin:0 auto; padding:24px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,.06); }
		.card h2 { margin:0 0 16px 0; }
		.form-group { margin-bottom:12px; }
		input[type=text], input[type=email], input[type=password] { width:100%; padding:10px; box-sizing:border-box; border:1px solid #d1d5db; border-radius:6px; }
		button { background:#2563eb; color:#fff; padding:10px 14px; border:none; border-radius:6px; cursor:pointer; }
		.message { padding:10px; border-radius:6px; margin-bottom:12px; }
		.success { background:#ecfdf5; color:#065f46; border:1px solid #bbf7d0; }
		.error { background:#fff1f2; color:#991b1b; border:1px solid #fecaca; }
		.info { background:#f0f9ff; color:#075985; border:1px solid #a5f3fc; }
	</style>
</head>
<body>
	<div class="card">
		<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
			<h2 style="margin:0;">Account Settings</h2>
			<div>
				<a href="<?php echo htmlspecialchars($homeLink); ?>" style="display:inline-block;margin-right:8px;text-decoration:none;background:#6b7280;color:#fff;padding:8px 10px;border-radius:6px;">Back</a>
			</div>
		</div>
		<?php if ($message): ?>
			<div class="message <?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></div>
		<?php endif; ?>

		<form method="post" autocomplete="off" id="accountForm">
			<div class="form-group" style="display:flex;align-items:center;gap:8px;">
				<div style="flex:1;">
					<label>Username</label>
					<input type="text" name="username" value="<?php echo htmlspecialchars($currentUsername); ?>" required disabled>
				</div>
				<div style="white-space:nowrap;">
					<button type="button" class="field-edit" data-field="username" style="background:#f59e0b;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;">Edit</button>
					<button type="button" class="field-save" data-field="username" style="display:none;background:#16a34a;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;margin-left:6px;">Save</button>
					<button type="button" class="field-cancel" data-field="username" style="display:none;background:#ef4444;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;margin-left:6px;">Cancel</button>
				</div>
			</div>
			<div class="form-group" style="display:flex;align-items:center;gap:8px;">
				<div style="flex:1;">
					<label>Email</label>
					<input type="email" name="email" value="<?php echo htmlspecialchars($currentEmail); ?>" required disabled>
				</div>
				<div style="white-space:nowrap;">
					<button type="button" class="field-edit" data-field="email" style="background:#f59e0b;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;">Edit</button>
					<button type="button" class="field-save" data-field="email" style="display:none;background:#16a34a;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;margin-left:6px;">Save</button>
					<button type="button" class="field-cancel" data-field="email" style="display:none;background:#ef4444;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;margin-left:6px;">Cancel</button>
				</div>
			</div>

			<div id="passwordSection" style="display:flex;flex-direction:column;">
				<hr style="margin:16px 0;">
				<p style="margin:0 0 8px 0;font-size:90%;color:#374151">Change password (optional)</p>
				<div style="display:flex;align-items:center;gap:8px;">
					<div style="flex:1;">
						<div class="form-group">
							<label>Current Password</label>
							<input type="password" name="current_password" placeholder="Enter current password" disabled>
						</div>
					</div>
					<div style="white-space:nowrap;">
						<button type="button" id="editPassword" style="background:#f59e0b;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;">Edit Password</button>
						<button type="button" id="savePassword" style="display:none;background:#16a34a;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;margin-left:6px;">Save</button>
						<button type="button" id="cancelPassword" style="display:none;background:#ef4444;color:#fff;border:none;padding:8px 10px;border-radius:6px;cursor:pointer;margin-left:6px;">Cancel</button>
					</div>
				</div>

				<?php if ($otpPending || $otpSent): ?>
					<div id="otpSection" style="margin-top:12px;">
						<hr style="margin:16px 0;">
						<p style="margin:0 0 8px 0;font-size:90%;color:#374151">Enter OTP sent to your email</p>
						<div class="form-group">
							<label>OTP Code</label>
							<input type="text" name="otp_code" placeholder="6-digit code" required>
						</div>
					</div>
				<?php endif; ?>
				<div class="form-group">
					<label>New Password</label>
					<input type="password" name="new_password" placeholder="New password" disabled>
				</div>
				<div class="form-group">
					<label>Confirm New Password</label>
					<input type="password" name="confirm_password" placeholder="Confirm new password" disabled>
				</div>
			</div>
			<div style="text-align:right;margin-top:8px;">
				<!-- save/cancel buttons are at top; show note when form is disabled -->
				<small id="noteText" style="color:#475569;">Click "Edit" to modify your account information.</small>
			</div>
		</form>
		<script>
			(function(){
				// global edit buttons removed; per-field controls used instead
				const form = document.getElementById('accountForm');
				const inputs = form.querySelectorAll('input[name=username], input[name=email]');
				const passwordSection = document.getElementById('passwordSection');
				const note = document.getElementById('noteText');

				// store originals
				const original = {
					username: form.querySelector('input[name=username]').value,
					email: form.querySelector('input[name=email]').value
				};

				function enterEdit() {
					inputs.forEach(i=>{ i.disabled = false; });
					passwordSection.style.display = 'block';
					if (note) note.style.display = 'none';
				}

				function exitEdit(restore=true) {
					if (restore) {
						form.querySelector('input[name=username]').value = original.username;
						form.querySelector('input[name=email]').value = original.email;
					}
					inputs.forEach(i=>{ i.disabled = true; });
					// clear password fields
					passwordSection.querySelectorAll('input[type=password]').forEach(p=>p.value='');
					passwordSection.style.display = 'none';
					// hide all per-field save/cancel and show edit buttons
					document.querySelectorAll('.field-save').forEach(function(b){ b.style.display = 'none'; });
					document.querySelectorAll('.field-cancel').forEach(function(b){ b.style.display = 'none'; });
					document.querySelectorAll('.field-edit').forEach(function(b){ b.style.display = ''; });
					// reset password buttons
					var editPassword = document.getElementById('editPassword');
					var savePassword = document.getElementById('savePassword');
					var cancelPassword = document.getElementById('cancelPassword');
					if (editPassword) editPassword.style.display = '';
					if (savePassword) savePassword.style.display = 'none';
					if (cancelPassword) cancelPassword.style.display = 'none';
					if (note) note.style.display = '';
				}

				// remove global edit handlers (we use per-field controls)
                
				// Per-field controls
				document.querySelectorAll('.field-edit').forEach(function(btn){
					btn.addEventListener('click', function(e){
						var field = btn.getAttribute('data-field');
						var input = form.querySelector('input[name="' + field + '"]');
						if (!input) return;
						// show save/cancel for this field
						form.querySelectorAll('.field-save[data-field="' + field + '"]')[0].style.display = '';
						form.querySelectorAll('.field-cancel[data-field="' + field + '"]')[0].style.display = '';
						btn.style.display = 'none';
						input.disabled = false;
						input.focus();
					});
				});

				document.querySelectorAll('.field-cancel').forEach(function(btn){
					btn.addEventListener('click', function(e){
						var field = btn.getAttribute('data-field');
						var input = form.querySelector('input[name="' + field + '"]');
						if (!input) return;
						// restore original
						input.value = original[field];
						input.disabled = true;
						btn.style.display = 'none';
						form.querySelectorAll('.field-save[data-field="' + field + '"]')[0].style.display = 'none';
						form.querySelectorAll('.field-edit[data-field="' + field + '"]')[0].style.display = '';
					});
				});

				document.querySelectorAll('.field-save').forEach(function(btn){
					btn.addEventListener('click', function(e){
						var field = btn.getAttribute('data-field');
						// submit form — server will update changed fields
						form.submit();
					});
				});

				// Password-specific controls
				var editPassword = document.getElementById('editPassword');
				var savePassword = document.getElementById('savePassword');
				var cancelPassword = document.getElementById('cancelPassword');
				var curPwd = form.querySelector('input[name=current_password]');
				var newPwd = form.querySelector('input[name=new_password]');
				var confPwd = form.querySelector('input[name=confirm_password]');
				if (editPassword) {
					editPassword.addEventListener('click', function(e){
						e.preventDefault();
						// reveal password section
						passwordSection.style.display = 'block';
						editPassword.style.display = 'none';
						savePassword.style.display = '';
						cancelPassword.style.display = '';
						curPwd.disabled = false; newPwd.disabled = false; confPwd.disabled = false;
						curPwd.focus();
					});
				}
				if (cancelPassword) {
					cancelPassword.addEventListener('click', function(e){
						e.preventDefault();
						editPassword.style.display = '';
						savePassword.style.display = 'none';
						cancelPassword.style.display = 'none';
						curPwd.value = ''; newPwd.value = ''; confPwd.value = '';
						curPwd.disabled = true; newPwd.disabled = true; confPwd.disabled = true;
						// hide password section again when cancelling
						passwordSection.style.display = 'none';
					});
				}
				if (savePassword) {
					savePassword.addEventListener('click', function(e){
						e.preventDefault();
						// submit form; server will handle OTP
						form.submit();
					});
				}

				// Initialize per-field as view-only
				exitEdit(false);
			})();
		</script>
			<?php if ($otpPending || $otpSent): ?>
			<script>
				// Open password edit mode and focus OTP when server sent OTP
				(function(){
					var pwdBtn = document.getElementById('editPassword');
					if (pwdBtn) pwdBtn.click();
					var otp = document.querySelector('input[name=otp_code]');
					if (otp) otp.focus();
				})();
			</script>
			<?php endif; ?>
	</div>
</body>
</html>