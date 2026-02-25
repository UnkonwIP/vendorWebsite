<?php
require_once "session_bootstrap.php";

require_once "config.php";

// Protect admin page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get vendor list
$keyword = $_GET['keyword'] ?? '';
$vendors = [];
$where = '';
if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where = "WHERE username LIKE '%$kw%' OR newCompanyRegistrationNumber LIKE '%$kw%' OR email LIKE '%$kw%'";
}
$roleWhere = ($where ? "$where AND role = 'vendor'" : "WHERE role = 'vendor'");
$sql = "SELECT accountID, username, email, newCompanyRegistrationNumber FROM vendoraccount $roleWhere ORDER BY accountID DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $vendors[] = $row;
    }
}

// Count pending registration forms for the action notice
$pendingApprovals = 0;
$pendingRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM registrationform WHERE LOWER(status) = 'pending'");
if ($pendingRes) {
    $r = mysqli_fetch_assoc($pendingRes);
    $pendingApprovals = (int) ($r['cnt'] ?? 0);
}
// Load pending registration forms (id, reg number, submission date)
$pendingForms = [];
$pfRes = mysqli_query($conn, "SELECT registrationFormID, newCompanyRegistrationNumber, formFirstSubmissionDate, companyName FROM registrationform WHERE LOWER(status) = 'pending' ORDER BY formFirstSubmissionDate DESC");
if ($pfRes) {
    while ($pf = mysqli_fetch_assoc($pfRes)) {
        $pendingForms[] = $pf;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Vendor List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #059669;
            --primary-hover: #047857;
            --bg-gradient: linear-gradient(135deg, #064e3b, #065f46);
            --text-main: #1e293b;
            --text-muted: #64748b;

            /* cp design vars (partial set from provided theme) */
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
            background: #EDEDE9;
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
          .container-main {
              /* span the full width of the main-content area (no sidebar overlap) */
              max-width: none;
              width: 100%;
              margin: 0;
              box-sizing: border-box;
              /* fill remaining viewport height so content stretches */
              min-height: calc(100vh - 140px);
              display: flex;
              flex-direction: column;
              gap: 28px;
              padding-top: 20px;
              padding-bottom: 20px;
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
            .welcome-bar {
                background: white;
                padding: 18px 22px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 18px;
            }
            /* navbar-integrated welcome text */
            .nav-welcome-text { color: var(--text-main); font-weight:600; margin-right:8px; }
            .nav-welcome-sub { color: var(--text-muted); font-size:12px; }
            .notice-board {
                background: #F5EBE0;
                border: 1px solid #EADFCF;
                padding: 16px;
                border-radius: 10px;
                 margin-bottom: 28px;
            }
            .pending-list { display:flex; flex-direction:column; gap:12px; margin-top:8px; }
            .pending-card {
                background: #fcfcf2;
                padding: 12px 14px;
                border-radius: 8px;
                box-shadow: 0 1px 6px rgba(0,0,0,0.06);
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: pointer;
            }
            .pending-card .meta { color: var(--text-muted); font-size:13px; margin-top:6px; }
            .pending-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
                 margin-bottom: 28px;
            }
            .card-stat {
                padding: 16px;
                border-radius: 10px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.05);
                text-align: left;
                color: var(--text-main);
                width: 100%;
            }
            .card-stat.total { background: #D6CCC2; }
            .card-stat.pending { background: #F5EBE0; }
            .card-stat.active { background: #E3D5CA; }
            .activity-log {
                background: white;
                padding: 16px;
                border-radius: 10px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.05);
                    margin-bottom: 28px;

            }

            @media (max-width: 992px) {
                .stats-grid { grid-template-columns: repeat(2, 1fr); }
            }
            @media (max-width: 600px) {
                .stats-grid { grid-template-columns: 1fr; }
            }
        .vendor-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .vendor-empty {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-muted);
        }
        .vendor-empty h3 {
            color: var(--text-main);
            margin-bottom: 10px;
        }
        .vendor-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
            background: white;
        }
        .vendor-card:hover {
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        .vendor-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .vendor-card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-main);
        }
        .vendor-card-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 10px;
        }
        .vendor-detail {
            display: flex;
            flex-direction: column;
        }
        .vendor-detail-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .vendor-detail-value {
            font-size: 14px;
            color: var(--text-main);
        }
        .admin-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
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
        .btn-add {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .btn-add:hover {
            background: #0056b3;
            color: white;
        }
        .btn-clear {
            display: inline-block;
            padding: 12px 24px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            margin-bottom: 20px;
            margin-left: 10px;
        }
        .btn-clear:hover {
            background: #a71d2a;
            color: white;
        }
        .search-box {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        .clickable-vendor-card:hover {
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.22);
            border-color: var(--primary-hover);
            background: #f0fdf4;
        }
        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
            }
            .vendor-container {
                padding: 20px;
            }
            .vendor-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .vendor-card-details {
                grid-template-columns: 1fr;
            }
            .admin-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <div>
                <span class="nav-welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                <div class="nav-welcome-sub">Administrator Dashboard</div>
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
    <?php include __DIR__ . '/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="container-main">
    <!-- Welcome moved into navbar; duplicate block removed -->

    <div class="notice-board">
        <strong>Action Notice:</strong>
        <div style="margin-top:6px; font-weight:700">Pending Approval (<?php echo htmlspecialchars($pendingApprovals); ?>)</div>
        <?php if (empty($pendingForms)): ?>
            <div style="margin-top:6px; color:var(--text-muted)">No pending approvals.</div>
        <?php else: ?>
            <div class="pending-list">
                <?php foreach ($pendingForms as $pf): ?>
                    <div class="pending-card" role="button" tabindex="0" onclick="location.href='AdminViewPage.php?registrationFormID=<?php echo urlencode($pf['registrationFormID']); ?>'" onkeypress="if(event.key==='Enter') location.href='AdminViewPage.php?registrationFormID=<?php echo urlencode($pf['registrationFormID']); ?>'">
                        <div>
                            <div style="font-weight:700"><?php echo htmlspecialchars($pf['companyName'] ?: 'Untitled Company'); ?></div>
                            <div class="meta">
                                <?php echo 'Reg No: ' . htmlspecialchars($pf['newCompanyRegistrationNumber'] ?: '—'); ?>
                                &nbsp;•&nbsp;
                                <?php echo 'Submitted: ' . htmlspecialchars(!empty($pf['formFirstSubmissionDate']) ? date('d M Y', strtotime($pf['formFirstSubmissionDate'])) : 'Unknown'); ?>
                            </div>
                        </div>
                        <div>
                            <a href="AdminViewPage.php?registrationFormID=<?php echo urlencode($pf['registrationFormID']); ?>" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="stats-grid">
        <div class="card-stat total">
            <div style="font-size:12px; color:var(--text-muted)">Total Vendors</div>
            <div style="font-size:22px; font-weight:700"><?php echo count($vendors); ?></div>
        </div>
        <div class="card-stat pending">
            <div style="font-size:12px; color:var(--text-muted)">Pending Approvals</div>
            <div style="font-size:22px; font-weight:700"><?php echo htmlspecialchars($pendingApprovals); ?></div>
        </div>
        <div class="card-stat active">
            <div style="font-size:12px; color:var(--text-muted)">Active Contracts</div>
            <div style="font-size:22px; font-weight:700">0</div>
        </div>
    </div>

    <div class="activity-log">
        <strong>Recent Activity</strong>
        <ul style="margin-top:10px; padding-left:18px; color:var(--text-muted);">
            <li>No recent activity to display.</li>
        </ul>
    </div>
        <!-- Vendor list removed -->
</div>
    </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

