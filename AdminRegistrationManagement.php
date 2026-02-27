<?php
require_once "session_bootstrap.php";
require_once "config.php";
require_once "status_helpers.php";
//
date_default_timezone_set('Asia/Kuala_Lumpur');

// Protect page (allow admin and admin_head; redirect head users to their management page)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','admin_head'], true)) {
	header("Location: index.php");
	exit();
}
// If this page is accessed by a department head, send them to the head management view
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin_head') {
	header("Location: AdminHeadRegisrationManagement.php");
	exit();
}

// Get vendorType to determine if this is a department admin
$accountID = $_SESSION['accountID'] ?? '';
$vendorType = '';
$adminRoleType = 'general'; // default to general admin
$deptColumn = null;

if (!empty($accountID)) {
	// Resolve a whitelisted department column for this admin account (null if none)
	$deptColumn = get_dept_column_for_account($conn, $accountID);

	// Also fetch vendorType for display and existing status_pill_class usage
	$vtStmt = $conn->prepare("SELECT vendorType FROM vendoraccount WHERE accountID = ? LIMIT 1");
	if ($vtStmt) {
		$vtStmt->bind_param('s', $accountID);
		$vtStmt->execute();
		$vtRes = $vtStmt->get_result();
		if ($vtRes && ($vtRow = $vtRes->fetch_assoc())) {
			$vendorType = $vtRow['vendorType'] ?? '';
		}
		$vtStmt->close();
	}

	if ($deptColumn !== null) {
		$adminRoleType = 'department';
	}
}

$search = trim($_GET['search'] ?? '');
$regId = trim($_GET['reg_id'] ?? '');
$company = trim($_GET['company'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

// Summary counts
$counts = [
	'total' => 0,
	'not review' => 0,
	'approved' => 0,
	'rejected' => 0,
];
$totalRes = $conn->query("SELECT COUNT(*) AS cnt FROM registrationform");
if ($totalRes && ($trow = $totalRes->fetch_assoc())) {
	$counts['total'] = (int) ($trow['cnt'] ?? 0);
}
$statusRes = $conn->query("SELECT LOWER(status) AS status, COUNT(*) AS cnt FROM registrationform GROUP BY LOWER(status)");
if ($statusRes) {
	while ($srow = $statusRes->fetch_assoc()) {
		$key = strtolower($srow['status'] ?? '');
		if (isset($counts[$key])) {
			$counts[$key] = (int) ($srow['cnt'] ?? 0);
		}
	}
}
$pendingAttention = $counts['not review'] ?? 0;

// Build filtered query
$forms = [];
$conditions = [];
$params = [];
$types = '';

// Show all statuses by default; filters below will apply if provided by the user.
// Department-specific views are handled in the head/approval pages; do not restrict here.

if ($search !== '') {
	$like = "%{$search}%";
	$conditions[] = "(companyName LIKE ? OR CAST(registrationFormID AS CHAR) LIKE ? OR newCompanyRegistrationNumber LIKE ?)";
	$params[] = $like;
	$params[] = $like;
	$params[] = $like;
	$types .= 'sss';
}
if ($regId !== '') {
	$like = "%{$regId}%";
	$conditions[] = "CAST(registrationFormID AS CHAR) LIKE ?";
	$params[] = $like;
	$types .= 's';
}
if ($company !== '') {
	$like = "%{$company}%";
	$conditions[] = "companyName LIKE ?";
	$params[] = $like;
	$types .= 's';
}
if ($statusFilter !== '') {
	// For AdminRegistrationManagement, treat status as a main-status filter only.
	$conditions[] = "LOWER(status) = ?";
	$params[] = strtolower($statusFilter);
	$types .= 's';
}
if ($dateFrom !== '') {
	$conditions[] = "DATE(formFirstSubmissionDate) >= ?";
	$params[] = $dateFrom;
	$types .= 's';
}
if ($dateTo !== '') {
	$conditions[] = "DATE(formFirstSubmissionDate) <= ?";
	$params[] = $dateTo;
	$types .= 's';
}

// (status dropdown now supports department-aware filtering for 'pending approval')

$sql = "SELECT registrationFormID, newCompanyRegistrationNumber, companyName, formFirstSubmissionDate, status,
		financeDepartmentStatus, projectDepartmentStatus, legalDepartmentStatus, planDepartmentStatus
		FROM registrationform";
if (!empty($conditions)) {
	$sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY registrationFormID DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
	if ($types !== '') {
		$stmt->bind_param($types, ...$params);
	}
	$stmt->execute();
	$formsResult = $stmt->get_result();
	while ($row = $formsResult->fetch_assoc()) {
		$forms[] = $row;
	}
}

// Use normalize_status() from status_helpers.php (centralized canonical mapping)

function status_pill_class($status, $adminRole = 'general', $column = 'general', $adminVendorType = '') {
	$val = normalize_status($status);

	// Pending-approval (blue) is shown for both general and department contexts
	if ($val === 'pending approval') {
		return 'status-pill pending-approval';
	}

	if ($val === 'approved') return 'status-pill approved';
	if ($val === 'rejected') return 'status-pill rejected';

	// Data Compliance / overall column
	if ($column === 'general') {
		if ($val === 'not review') return 'status-pill not-review';
		return 'status-pill neutral';
	}

	// For department columns: determine whether this admin is from the same department
	$isOwnDept = false;
	if ($adminRole === 'department' && !empty($adminVendorType)) {
		$vtLower = strtolower($adminVendorType);
		if (($column === 'finance' && strpos($vtLower, 'finance') !== false) ||
			($column === 'project' && strpos($vtLower, 'project') !== false) ||
			($column === 'legal' && strpos($vtLower, 'legal') !== false) ||
			($column === 'plan' && strpos($vtLower, 'plan') !== false)) {
			$isOwnDept = true;
		}
	}

	if ($isOwnDept) {
		// Own department: 'not review' => yellow, others handled above
		if ($val === 'not review') return 'status-pill not-review';
		return 'status-pill neutral';
	} else {
		// Other departments: 'not review' => gray
		if ($val === 'not review') return 'status-pill not-review-department';
		return 'status-pill neutral';
	}
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

			/* Modern primary action button matching admin UI */
			.btn-add {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				padding: 8px 14px;
				background: linear-gradient(90deg, #f59e42 0%, #de5c2e 100%);
				color: #fff;
				border: none;
				border-radius: 10px;
				font-weight: 700;
				box-shadow: 0 8px 18px rgba(222,92,46,0.14);
				cursor: pointer;
				transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
			}

			.btn-add:hover {
				transform: translateY(-3px);
				box-shadow: 0 14px 26px rgba(222,92,46,0.18);
			}

			.btn-add:focus {
				outline: 2px solid rgba(13,110,253,0.12);
				outline-offset: 2px;
			}
		body {
			background: #f4f6f9;
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

			/* Registration management layout (based on temp.php) */
			.topbar {
				display: flex;
				justify-content: space-between;
				align-items: center;
				gap: 16px;
				margin-bottom: 24px;
				flex-wrap: wrap;
			}
			.topbar h1 { font-size: 24px; margin: 0; color: var(--text-main); }
			.topbar .top-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
			.topbar input,
			.topbar select {
				padding: 8px 10px;
				border-radius: 6px;
				border: 1px solid #ccc;
				font-size: 14px;
			}
			.btn-primary,
			.btn-secondary,
			.btn-logout {
				border: none;
				padding: 8px 14px;
				border-radius: 6px;
				cursor: pointer;
				font-size: 14px;
				text-decoration: none;
				display: inline-block;
			}
			.btn-primary { background: #2563eb; color: white; }
			.btn-secondary { background: #374151; color: white; }
			.btn-logout { background: #dc2626; color: white; }

			.cards {
				display: grid;
				grid-template-columns: repeat(4, 1fr);
				gap: 15px;
				margin-bottom: 25px;
			}
			.card {
				padding: 20px;
				border-radius: 10px;
				color: white;
			}
			.card.total { background: #4b5563; }
			.card.pending { background: #f59e0b; }
			.card.approved { background: #10b981; }
			.card.rejected { background: #ef4444; }

			.action-required {
				background: #fff3cd;
				padding: 15px;
				border-radius: 8px;
				margin-bottom: 25px;
			}

			.filter-section {
				background: white;
				padding: 15px;
				border-radius: 8px;
				margin-bottom: 25px;
			}
			.filters {
				display: flex;
				flex-wrap: wrap;
				gap: 10px;
				margin-top: 10px;
			}
			.filters input,
			.filters select {
				padding: 8px;
				border-radius: 6px;
				border: 1px solid #ccc;
				font-size: 14px;
			}

			.table-section {
				background: white;
				padding: 15px;
				border-radius: 8px;
				margin-bottom: 25px;
			}
			table {
				width: 100%;
				border-collapse: collapse;
				margin-top: 15px;
			}
			table th, table td {
				padding: 10px;
				border-bottom: 1px solid #eee;
				text-align: left;
				vertical-align: middle;
			}

			.status-pill {
				padding: 4px 8px;
				border-radius: 6px;
				color: white;
				font-size: 12px;
				text-transform: capitalize;
			}
			.status-pill.pending { background: #f59e0b; } /* Yellow for general admin */
			.status-pill.approved { background: #10b981; }
			.status-pill.rejected { background: #ef4444; }
			.status-pill.neutral { background: #6b7280; }
			.status-pill.not-review { background: #f59e0b; } /* Yellow for general admin */
			.status-pill.pending-department { background: #ef4444; } /* Red for department admin */
			.status-pill.not-review-department { background: #6b7280; } /* Gray for department admin */
			.status-pill.pending-approval { background: #1e88e5; } /* Blue for pending approval */

			.btn-small {
				padding: 6px 10px;
				border: none;
				border-radius: 4px;
				cursor: pointer;
				background: #6b7280;
				color: white;
				text-decoration: none;
			}

			.activity {
				background: white;
				padding: 15px;
				border-radius: 8px;
			}

			@media (max-width: 1100px) {
				.cards { grid-template-columns: repeat(2, 1fr); }
			}
			@media (max-width: 768px) {
				.cards { grid-template-columns: 1fr; }
				.topbar { align-items: flex-start; }
			}
	</style>
 </head>
<body>

<div class="layout">
	<?php include __DIR__ . '/admin_sidebar.php'; ?>

	<main class="main-content">

	<div class="container-main">
		<div class="topbar">
			<div>
				<h1>Registration Management</h1>
				<div style="color: var(--text-muted); font-size: 13px;">Manage all registration forms and approvals</div>
			</div>
			<div class="top-actions">
				<a href="logout.php" class="btn-logout">Logout</a>
			</div>
		</div>

		<div class="cards">
			<div class="card total">
				<h3><?php echo htmlspecialchars($counts['total']); ?></h3>
				<p>Total Registrations</p>
			</div>
			<div class="card pending">
				<h3><?php echo htmlspecialchars($counts['not review']); ?></h3>
				<p>Not Review</p>
			</div>
			<div class="card approved">
				<h3><?php echo htmlspecialchars($counts['approved']); ?></h3>
				<p>Approved</p>
			</div>
			<div class="card rejected">
				<h3><?php echo htmlspecialchars($counts['rejected']); ?></h3>
				<p>Rejected</p>
			</div>
		</div>

		<div class="action-required">
			<h2>⚠ Requires Your Attention</h2>
			<ul>
				<li><?php echo htmlspecialchars($pendingAttention); ?> forms pending review</li>
			</ul>
		</div>

		<form class="filter-section" method="get">
			<h2>Advanced Filter</h2>
			<div class="filters">
				<input type="text" name="reg_id" placeholder="Registration ID" value="<?php echo htmlspecialchars($regId); ?>">
				<input type="text" name="company" placeholder="Company Name" value="<?php echo htmlspecialchars($company); ?>">
				<select name="status">
					<option value="">Status</option>
					<option value="not review" <?php echo ($statusFilter === 'not review') ? 'selected' : ''; ?>>Not Review</option>
					<option value="pending approval" <?php echo ($statusFilter === 'pending approval') ? 'selected' : ''; ?>>Pending Approval</option>
					<option value="approved" <?php echo ($statusFilter === 'approved') ? 'selected' : ''; ?>>Approved</option>
					<option value="rejected" <?php echo ($statusFilter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
				</select>
				<!-- Status dropdown will apply department-aware filtering when appropriate -->
				<input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
				<input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
				<button type="submit" class="btn-secondary">Apply Filter</button>
				<a href="AdminRegistrationManagement.php" class="btn-secondary">Reset</a>
			</div>
		</form>

		<div class="table-section">
			<div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
				<h2 style="margin:0;">Registration List</h2>
				<form method="post" action="APIRequestFormRenewal.php" style="display:inline;margin-left:auto;" onsubmit="return confirm('Are you sure you want to request a new registration form from ALL vendors? This will reset their renewal status.');">
					<button type="submit" class="btn-add" style="background:#f59e42;">Request New Registration Form</button>
				</form>
			</div>
			<?php if (empty($forms)): ?>
				<div class="forms-empty">
					<h3>No Forms Found</h3>
					<p>No registration forms match your filters.</p>
				</div>
			<?php else: ?>
				<div class="table-responsive">
					<table>
						<thead>
							<tr>
								<th>Company</th>
								<th>Date Submitted</th>
								<th>Data Compliance</th>
								<th>Finance</th>
								<th>Project</th>
								<th>Legal</th>
								<th>Plan</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($forms as $form): ?>
								<?php
									$companyName = $form['companyName'] ?? '—';
									$dateSubmitted = !empty($form['formFirstSubmissionDate']) ? date('Y-m-d', strtotime($form['formFirstSubmissionDate'])) : '—';
									$generalStatus = $form['status'] ?? 'not review';
									$financeStatus = $form['financeDepartmentStatus'] ?? 'not review';
									$projectStatus = $form['projectDepartmentStatus'] ?? 'not review';
									$legalStatus = $form['legalDepartmentStatus'] ?? 'not review';
									$planStatus = $form['planDepartmentStatus'] ?? 'not review';
								?>
								<tr>
									<td><?php echo htmlspecialchars($companyName); ?></td>
									<td><?php echo htmlspecialchars($dateSubmitted); ?></td>
									<td><span class="<?php echo status_pill_class($generalStatus, $adminRoleType, 'general', $vendorType); ?>"><?php echo htmlspecialchars(normalize_status($generalStatus)); ?></span></td>
									<td><span class="<?php echo status_pill_class($financeStatus, $adminRoleType, 'finance', $vendorType); ?>"><?php echo htmlspecialchars(normalize_status($financeStatus)); ?></span></td>
									<td><span class="<?php echo status_pill_class($projectStatus, $adminRoleType, 'project', $vendorType); ?>"><?php echo htmlspecialchars(normalize_status($projectStatus)); ?></span></td>
									<td><span class="<?php echo status_pill_class($legalStatus, $adminRoleType, 'legal', $vendorType); ?>"><?php echo htmlspecialchars(normalize_status($legalStatus)); ?></span></td>
									<td><span class="<?php echo status_pill_class($planStatus, $adminRoleType, 'plan', $vendorType); ?>"><?php echo htmlspecialchars(normalize_status($planStatus)); ?></span></td>
									<td>
										<a class="btn-small" href="AdminViewPage.php?registrationFormID=<?php echo urlencode($form['registrationFormID']); ?>">View</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>

		<div class="activity">
			<h2>Recent Activity</h2>
			<p class="text-muted">No recent activity to display.</p>
		</div>
	</div>
		</main>
	</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
