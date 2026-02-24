<?php
require_once "session_bootstrap.php";
require_once "config.php";
//
date_default_timezone_set('Asia/Kuala_Lumpur');

// Protect page (admin only)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header("Location: index.php");
	exit();
}

$forms = [];
$stmt = $conn->prepare(
	"SELECT registrationFormID, newCompanyRegistrationNumber, companyName AS CompanyName, formFirstSubmissionDate, status, rejectionReason
	FROM registrationform
	ORDER BY registrationFormID DESC"
);
$stmt->execute();
$formsResult = $stmt->get_result();
while ($row = $formsResult->fetch_assoc()) {
	$forms[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin - Registration Forms</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		:root {
			--primary-color: #059669;
			--primary-hover: #047857;
			--bg-gradient: linear-gradient(135deg, #064e3b, #065f46);
			--text-main: #1e293b;
			--text-muted: #64748b;

			/* cp design vars (copied from AdminHome) */
			-webkit-text-size-adjust: 100%;
			-webkit-tap-highlight-color: rgba(0,0,0,0);
			--cp-blue: #003da6;
			--cp-indigo: #202654;
			--cp-purple: #31006f;
			--cp-red: #dc3545;
			--cp-orange: #de5c2e;
			--cp-yellow: #ffc107;
			--cp-green: #198754;
			--cp-teal: #20c997;
			--cp-cyan: #0dcaf0;
			--cp-black: #000;
			--cp-white: #fff;
			--cp-gray: #6d7983;
			--cp-primary: #003da6;
			--cp-success: #198754;
			--cp-info: #0dcaf0;
			--cp-warning: #ffc107;
			--cp-danger: #dc3545;
			--cp-light: #fafafa;
			--cp-dark: #243746;
			--cp-accent: #de5c2e;
			--cp-primary-text-emphasis: #001842;
			--cp-body-font-family: "Open Sans",system-ui,-apple-system,"Segoe UI",sans-serif;
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
			/* avoid overlapping the fixed sidebar on wide screens */
			margin-left: 260px;
			width: calc(100% - 260px);
			position: relative;
			z-index: 15;
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
		.nav-welcome-text { color: var(--text-main); font-weight:600; margin-right:8px; }
		.nav-welcome-sub { color: var(--text-muted); font-size:12px; }
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

			/* Layout & Sidebar (copied from AdminHome for consistent admin UX) */
			.layout {
				display: flex;
				gap: 24px;
				align-items: flex-start;
			}
			.sidebar {
				width: 260px;
				background: white;
				padding: 24px 18px;
				border-radius: 0;
				box-shadow: 0 2px 12px rgba(0,0,0,0.08);
				position: fixed;
				top: 0;
				left: 0;
				bottom: 0;
				overflow: auto;
				z-index: 10;
			}
			.cp-main-menu__container { display:flex; flex-direction:column; gap:16px; }
			.cp-main-menu__logo-container { padding-bottom:6px; border-bottom:1px solid var(--cp-secondary-bg, #e5e7e9); display:flex; align-items:center; }
			.sidebar-brand { display:flex; align-items:center; text-decoration:none; }
			.sidebar-brand-text { color: var(--primary-color); font-weight:700; font-size:18px; }
			.links { list-style:none; padding:0; margin:8px 0 0 0; }
			.list-item { margin:8px 0; }
			.list-item__link {
				display:flex; align-items:center; gap:12px; text-decoration:none; padding:8px 10px; border-radius:8px;
				color:var(--cp-white); background:transparent; transition:background .12s, color .12s;
			}
			.list-item__icon { width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; background:var(--cp-primary); border-radius:6px; }
			.list-item__text { font-weight:600; color:#000; }
			.list-item__link { color: inherit; }
			.list-item__link:hover { background: rgba(var(--cp-primary-rgb, 0,61,166), 0.08); color:var(--cp-primary-text-emphasis); }
			.sidebar h4 {
				margin-top: 0;
				margin-bottom: 12px;
				color: var(--text-main);
				font-size: 16px;
			}
			.sidebar .sidebar-link {
				display: block;
				padding: 8px 10px;
				color: var(--text-main);
				text-decoration: none;
				border-radius: 8px;
				font-weight: 600;
				margin-bottom: 6px;
			}
			.sidebar .sidebar-link:hover {
				background: #f0fdf4;
				color: var(--primary-color);
				text-decoration: none;
			}
			.main-content {
				flex: 1 1 auto;
				margin-left: 260px;
				padding: 20px;
			}

			@media (max-width: 768px) {
				.layout { flex-direction: column; }
				.sidebar {
					position: static;
					width: 100%;
					height: auto;
					margin-bottom: 16px;
				}
				.main-content { margin-left: 0; }
				.navbar { margin-left: 0; width: 100%; }
			}
	</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light">
	<div class="container-fluid">
		<div class="d-flex align-items-center">
			<div>
				<div class="nav-welcome-text">Registration Forms</div>
				<div class="nav-welcome-sub">View and manage all registration forms</div>
			</div>
		</div>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav ms-auto">
				<li class="nav-item">
					<a class="nav-link logout-link" href="logout.php">Logout</a>
				</li>
			</ul>
		</div>
	</div>
</nav>

<div class="layout">
	<aside class="sidebar">
		<div class="cp-main-menu__container">
			<div class="cp-main-menu__logo-container">
				<a href="AdminHome.php" title="Home" class="sidebar-brand">
					<img src="Image/company%20logo.png" alt="logo" style="height:34px; display:inline-block; margin-right:10px;">
					<span class="sidebar-brand-text">Admin Panel</span>
				</a>
			</div>
			<ul class="links" id="cp-main-menu__link-list">
				<li class="list-item">
					<a class="list-item__link" href="AdminHome.php">
						<span class="list-item__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 11L12 3l9 8v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-8z" fill="var(--cp-white)"></path></svg>
						</span>
						<span class="list-item__text">Home</span>
					</a>
				</li>
				<li class="list-item">
					<a class="list-item__link" href="AdminVendorManagement.php">
						<span class="list-item__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="7" r="4" fill="var(--cp-white)"></circle><path d="M4 21c0-4 4-6 8-6s8 2 8 6v1H4v-1z" fill="var(--cp-white)"></path></svg>
						</span>
						<span class="list-item__text">Vendor Management</span>
					</a>
				</li>
				<li class="list-item">
					<a class="list-item__link" href="AdminRegistrationManagement.php">
						<span class="list-item__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" fill="var(--cp-white)"></circle></svg>
						</span>
						<span class="list-item__text">Registration</span>
					</a>
				</li>
				<li class="list-item">
					<a class="list-item__link" href="#" onclick="alert('Development in progress'); return false;">
						<span class="list-item__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="6" rx="1" fill="var(--cp-white)"></rect><rect x="3" y="14" width="18" height="6" rx="1" fill="var(--cp-white)"></rect></svg>
						</span>
						<span class="list-item__text">Procurement</span>
					</a>
				</li>
				<li class="list-item">
					<a class="list-item__link" href="#" onclick="alert('Development in progress'); return false;">
						<span class="list-item__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 7h16v10H4z" fill="var(--cp-white)"></path></svg>
						</span>
						<span class="list-item__text">Contract</span>
					</a>
				</li>
			</ul>
		</div>
	</aside>

	<main class="main-content">

	<div class="container-main">

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
	</main>
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
