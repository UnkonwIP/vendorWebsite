<?php
session_start();
include "config.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

// Protect page (admin only)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header("Location: index.php");
	exit();
}

$accountID = $_GET['accountID'] ?? '';
$vendorNewCompanyRegistration = '';
$vendorUsername = '';

if (empty($accountID)) {
	echo "<div style='color:red;text-align:center;margin-top:40px;'>Error: Vendor account ID not specified.</div>";
	exit();
}

// Get vendor's company registration number and username
$stmtAcc = $conn->prepare("SELECT newCompanyRegistrationNumber, username FROM vendoraccount WHERE accountID = ?");
$stmtAcc->bind_param("s", $accountID);
$stmtAcc->execute();
$accResult = $stmtAcc->get_result();
if ($accRow = $accResult->fetch_assoc()) {
	$vendorNewCompanyRegistration = $accRow['newCompanyRegistrationNumber'];
	$vendorUsername = $accRow['username'];
} else {
	echo "<div style='color:red;text-align:center;margin-top:40px;'>Error: Vendor not found.</div>";
	exit();
}

$forms = [];
if (!empty($vendorNewCompanyRegistration)) {
	$stmt = $conn->prepare(
		"SELECT registrationFormID, newCompanyRegistrationNumber, companyName AS CompanyName, formFirstSubmissionDate, status, rejectionReason
		FROM registrationform
		WHERE newCompanyRegistrationNumber = ?
		ORDER BY registrationFormID DESC"
	);
	$stmt->bind_param("s", $vendorNewCompanyRegistration);
	$stmt->execute();
	$formsResult = $stmt->get_result();
	while ($row = $formsResult->fetch_assoc()) {
		$forms[] = $row;
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin - Vendor Form List</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		:root {
			--primary-color: #059669;
			--primary-hover: #047857;
			--bg-gradient: linear-gradient(135deg, #064e3b, #065f46);
			--text-main: #1e293b;
			--text-muted: #64748b;
		}
		body {
			background: var(--bg-gradient);
			font-family: 'Inter', -apple-system, sans-serif;
			min-height: 100vh;
			padding: 20px;
		}
		.navbar {
			background: rgba(255, 255, 255, 0.95);
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			padding: 15px 20px;
			margin-bottom: 30px;
			border-radius: 12px;
		}
		.navbar-brand {
			font-weight: 700;
			color: var(--primary-color) !important;
			font-size: 20px;
		}
		.navbar-nav .nav-link {
			color: var(--text-main) !important;
			margin-left: 20px;
			transition: color 0.2s;
		}
		.navbar-nav .nav-link:hover {
			color: var(--primary-color) !important;
		}
		.navbar-nav .logout-link {
			color: #dc2626 !important;
		}
		.container-main {
			max-width: 1000px;
			margin: 0 auto;
		}
		.page-header {
			background: white;
			padding: 30px;
			border-radius: 12px;
			margin-bottom: 30px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		}
		.page-header h1 {
			color: var(--text-main);
			font-size: 28px;
			margin-bottom: 10px;
		}
		.page-header p {
			color: var(--text-muted);
			font-size: 14px;
		}
		.forms-container {
			background: white;
			padding: 30px;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		}
		.forms-empty {
			text-align: center;
			padding: 50px 20px;
			color: var(--text-muted);
		}
		.forms-empty h3 {
			color: var(--text-main);
			margin-bottom: 10px;
		}
		.form-card {
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			padding: 20px;
			margin-bottom: 15px;
			transition: all 0.3s;
			background: white;
		}
		.form-card:hover {
			box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);
			border-color: var(--primary-color);
			transform: translateY(-2px);
		}
		.form-card-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 15px;
		}
		.form-card-title {
			font-size: 16px;
			font-weight: 600;
			color: var(--text-main);
		}
		.status-badge {
			display: inline-block;
			padding: 6px 12px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 600;
			text-transform: capitalize;
		}
		.status-completed {
			background: #dcfce7;
			color: #166534;
		}
		.status-pending {
			background: #fef3c7;
			color: #92400e;
		}
		.status-rejected {
			background: #fee2e2;
			color: #991b1b;
		}
		.form-card-details {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 15px;
			margin-bottom: 15px;
		}
		.form-detail {
			display: flex;
			flex-direction: column;
		}
		.form-detail-label {
			font-size: 12px;
			font-weight: 600;
			color: var(--text-muted);
			text-transform: uppercase;
			margin-bottom: 4px;
		}
		.form-detail-value {
			font-size: 14px;
			color: var(--text-main);
		}
		.form-card-actions {
			display: flex;
			justify-content: space-between;
			align-items: stretch;
			margin-top: 15px;
			border-top: 1px solid #e2e8f0;
			padding-top: 15px;
			gap: 0;
		}
		.form-card-actions form {
			margin: 0;
		}
		.btn-action.btn-view {
			flex: 1 1 auto;
			margin-right: 10px;
		}
		.btn-action.btn-delete {
			flex: 0 0 auto;
			margin-left: auto;
		}
		.btn-view {
			flex: 1;
			padding: 10px 16px;
			background: var(--primary-color);
			color: white;
			border: none;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: background 0.2s;
			text-decoration: none;
			text-align: center;
		}
		.btn-view:hover {
			background: var(--primary-hover);
			text-decoration: none;
			color: white;
		}
		.btn-edit {
			flex: 1;
			padding: 10px 16px;
			background: #f3f4f6;
			color: var(--text-main);
			border: 1px solid #e5e7eb;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.2s;
			text-decoration: none;
			text-align: center;
		}
		.btn-edit:hover {
			background: #e5e7eb;
			text-decoration: none;
			color: var(--text-main);
		}
		.btn-action {
			flex: 1;
			padding: 10px 16px;
			border: none;
			border-radius: 8px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			text-decoration: none;
			text-align: center;
			transition: background 0.2s, color 0.2s;
			margin: 0;
		}
		.btn-view {
			background: var(--primary-color);
			color: white;
		}
		.btn-view:hover {
			background: var(--primary-hover);
			color: white;
		}
		.btn-delete {
			background: #fee2e2;
			color: #991b1b;
		}
		.btn-delete:hover {
			background: #fecaca;
			color: #991b1b;
		}
		@media (max-width: 768px) {
			.page-header {
				padding: 20px;
			}
			.forms-container {
				padding: 20px;
			}
			.form-card-header {
				flex-direction: column;
				align-items: flex-start;
				gap: 10px;
			}
			.form-card-details {
				grid-template-columns: 1fr;
			}
			.form-card-actions {
				flex-direction: column;
			}
		}
	</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light">
	<div class="container-fluid">
		<a class="navbar-brand" href="admin.php">
			<img src="Image/company%20logo.png" alt="Logo" style="height: 30px; margin-right: 10px;">
			Admin Panel
		</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav ms-auto">
				<li class="nav-item">
					<span class="nav-link" style="color: var(--text-muted); margin-right: 20px;">
						Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
					</span>
				</li>
				<li class="nav-item">
					<a class="nav-link logout-link" href="logout.php">Logout</a>
				</li>
			</ul>
		</div>
	</div>
</nav>

<div class="container-main">
	<div class="page-header">
		<h1>Registration Forms for <?php echo htmlspecialchars($vendorUsername); ?></h1>
		<p>View and manage all registration forms submitted by this vendor</p>
		<a href="admin.php" class="btn btn-secondary mt-2">&larr; Back to Vendor List</a>
	</div>

	<div class="forms-container">
		<?php if (empty($forms)): ?>
			<div class="forms-empty">
				<h3>No Forms Yet</h3>
				<p>This vendor hasn't submitted any registration forms yet.</p>
			</div>
		<?php else: ?>
			<div class="form-list-column">
				<?php foreach ($forms as $form): ?>
					<form method="post" action="AdminViewPage.php" class="form-card" style="cursor:pointer;" onClick="this.submit();">
						<div class="form-card-header">
							<div class="form-card-title">
								<?php echo htmlspecialchars($form['CompanyName'] ?? 'Unnamed Company'); ?>
							</div>
							<span class="status-badge status-<?php echo strtolower($form['status'] ?? 'draft'); ?>">
								<?php echo htmlspecialchars($form['status'] ?? 'Pending'); ?>
							</span>
						</div>
						<input type="hidden" name="registrationFormID" value="<?php echo htmlspecialchars($form['registrationFormID']); ?>">

						<div class="form-card-details">
							<div class="form-detail">
								<span class="form-detail-label">Registration No</span>
								<span class="form-detail-value">
									<?php echo htmlspecialchars($form['newCompanyRegistrationNumber']); ?>
								</span>
							</div>
							<div class="form-detail">
								<span class="form-detail-label">Submitted On</span>
								<span class="form-detail-value">
									<?php echo date('d M Y', strtotime($form['formFirstSubmissionDate'])); ?>
								</span>
							</div>
						</div>

						<?php if (strtolower($form['status']) === 'rejected' && !empty($form['rejectionReason'])): ?>
							<div class="alert alert-danger mt-2" role="alert">
								<strong>Rejection Reason:</strong> <?php echo nl2br(htmlspecialchars($form['rejectionReason'])); ?>
							</div>
						<?php endif; ?>

						<div class="form-card-actions">
							<form method="post" action="APIDeleteRegistrationForm.php" onsubmit="return confirm('Are you sure you want to delete this form?');" style="margin-right:10px;" onclick="event.stopPropagation();">
								<input type="hidden" name="registrationFormID" value="<?php echo htmlspecialchars($form['registrationFormID']); ?>">
								<button type="submit" class="btn-action btn-delete">Delete</button>
							</form>
						</div>
					</form>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<style>
	.form-list-column {
		display: flex;
		flex-direction: column;
	}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
