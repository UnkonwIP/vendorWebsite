<?php
require_once "session_bootstrap.php";

require_once "config.php";

// Protect admin page (allow admin and admin_head)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','admin_head'], true)) {
    header("Location: index.php");
    exit();
}

// Get vendor list and filter parameters
$keyword = $_GET['keyword'] ?? '';
$selectedTrades = $_GET['trade'] ?? [];
$cidbGrade = $_GET['cidbGrade'] ?? '';
$stateFilter = $_GET['state'] ?? '';
$project_value_type = $_GET['project_value_type'] ?? ''; // 'min' or 'max'
$project_value = $_GET['project_value'] ?? '';
$project_value_max = $_GET['project_value_max'] ?? ''; // for range when 'min' is selected
$vendors = [];

// Use fixed specialties list
$fixedTrades = ['ISP', 'O&M', 'M&E', 'Others'];

// Use fixed list of Malaysian states/territories for Location filter
$fixedStates = [
    'Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Penang','Perak','Perlis',
    'Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Putrajaya','Labuan'
];

// Build WHERE clauses
$whereClauses = [];
$whereClauses[] = "va.role = 'vendor'";
if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $whereClauses[] = "(va.username LIKE '%$kw%' OR va.newCompanyRegistrationNumber LIKE '%$kw%' OR va.email LIKE '%$kw%' OR rf.companyName LIKE '%$kw%')";
}
if (!empty($selectedTrades) && is_array($selectedTrades)) {
    // Match vendors where trade or cidbSpecialization contains any selected term
    $tradeParts = [];
    foreach ($selectedTrades as $tval) {
        $t = mysqli_real_escape_string($conn, $tval);
        $tradeParts[] = "(rf.trade LIKE '%$t%' OR rf.cidbSpecialization LIKE '%$t%')";
    }
    if (!empty($tradeParts)) {
        $whereClauses[] = '(' . implode(' OR ', $tradeParts) . ')';
    }
}
if ($cidbGrade !== '') {
    $cg = mysqli_real_escape_string($conn, $cidbGrade);
    $whereClauses[] = "rf.cidbGrade = '$cg'";
}
if ($stateFilter !== '') {
    $sf = mysqli_real_escape_string($conn, $stateFilter);
    $whereClauses[] = "(TRIM(SUBSTRING_INDEX(rf.registeredAddress, ',', -1)) = '$sf' OR TRIM(SUBSTRING_INDEX(rf.correspondenceAddress, ',', -1)) = '$sf' OR TRIM(SUBSTRING_INDEX(rf.branch, ',', -1)) = '$sf')";
}

// Build HAVING clauses for aggregated project values
$havingClauses = [];
if ($project_value_type === 'min' && $project_value !== '' && is_numeric($project_value)) {
    $pv = (float)$project_value;
    $havingClauses[] = "MAX(ptr.projectValue) >= $pv";
    if ($project_value_max !== '' && is_numeric($project_value_max)) {
        $pv_max = (float)$project_value_max;
        $havingClauses[] = "MAX(ptr.projectValue) <= $pv_max";
    }
} elseif ($project_value_type === 'max' && $project_value !== '' && is_numeric($project_value)) {
    $pv = (float)$project_value;
    $havingClauses[] = "MIN(ptr.projectValue) <= $pv";
}

// Combine clauses
$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Fetch vendor accounts with registration info and project stats
$sql = "SELECT va.accountID, va.username, va.email, va.newCompanyRegistrationNumber, "
    . "rf.companyName, rf.trade, rf.cidbGrade, "
    . "MAX(ptr.projectValue) AS maxProjectValue, MIN(ptr.projectValue) AS minProjectValue "
    . "FROM vendoraccount va "
    . "LEFT JOIN registrationform rf ON va.newCompanyRegistrationNumber = rf.newCompanyRegistrationNumber "
    . "LEFT JOIN projecttrackrecord ptr ON rf.registrationFormID = ptr.registrationFormID "
    . $whereSql . " "
    . "GROUP BY va.accountID, va.username, va.email, va.newCompanyRegistrationNumber, rf.companyName, rf.trade, rf.cidbGrade ";

if (!empty($havingClauses)) {
    $sql .= ' HAVING ' . implode(' AND ', $havingClauses) . ' ';
}

$sql .= "ORDER BY va.accountID DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $vendors[] = $row;
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
        .nav-page-header { display:flex; flex-direction:column; margin-left:8px; }
        .nav-page-title { font-size:18px; font-weight:700; color: var(--text-main); line-height:1; }
        .nav-page-subtitle { font-size:12px; color: var(--text-muted); margin-top:2px; }
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
        .vendor-divider { height:1px; background:#c9ced2; margin:12px 0; border-radius:1px; }
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
            .sidebar-brand-text { color: var(--primary-color) !important; font-weight:700; font-size:18px; }
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
        /* Filter card / search box styles (matches temp.php design) */
        .search-box, .filter-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.04);
            margin-bottom: 30px;
            max-width: 100%;
        }
        .search-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 15px;
            transition: border-color .15s, box-shadow .15s;
            margin-bottom: 25px;
        }
        .search-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 6px rgba(59,130,246,0.06); }
        .value-combined {
            display: flex;
            border: 1px solid #dcdcdc;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .value-type {
            border: none;
            padding: 10px 12px;
            background: #f3f4f6;
            font-size: 14px;
            cursor: pointer;
            outline: none;
            min-width: 80px;
        }
        .value-type:focus { outline: none; }
        .currency-wrapper {
            position: relative;
            flex: 1;
        }
        .currency-wrapper span {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #666;
        }
        .currency-wrapper input {
            width: 100%;
            padding: 10px 10px 10px 38px;
            border: none;
            font-size: 14px;
            outline: none;
        }
        .value-combined:focus-within {
            border-color: #1e6bd6;
            box-shadow: 0 0 0 3px rgba(30,107,214,0.1);
        }
        .project-filter-second-input {
            display: none;
            margin-top: 8px;
        }
        .project-filter-second-input.show {
            display: block;
        }
        .filter-row {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .filter-group { flex: 1; min-width: 180px; }
        .filter-label { display:block; font-weight:600; margin-bottom:8px; color: #334155; font-size:13px; }
        .checkbox-group label { display:block; margin-bottom:6px; font-size:14px; cursor:pointer; }
        .checkbox-group input { margin-right:8px; }
        .filter-actions { text-align: right; margin-top:12px; }
        .btn-primary { background: linear-gradient(135deg,#1e6bd6,#3b82f6); border:none; color:white; }
        .btn-secondary { background:#f1f1f1; color:#333; border:1px solid #e5e7eb; }
        @media (max-width:768px) {
            .filter-row { flex-direction: column; }
            .filter-actions { text-align:center; }
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
        <div class="nav-page-header d-none d-md-flex flex-column ms-3">
            <span class="nav-page-title">Vendor Accounts</span>
            <small class="nav-page-subtitle">View and manage all registered vendors</small>
        </div>
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

    <div class="layout">
    <?php include __DIR__ . '/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="container-main">
    

    <div class="vendor-container">
        <div class="d-flex flex-wrap mb-3">
            <a href="AccountCreation.php" class="btn-add" style="display:inline; margin-right: 15px;">+ Add Account</a>
            <div>
                <form method="post" action="APIClearDatabase.php" style="display:inline" onsubmit="return confirm('Are you sure you want to clear the entire database?\nThis action cannot be undone.');">
                    <button type="submit" name="clear_database" class="btn-clear">Clear Database</button>
                </form>
            </div>
        </div>
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <?php if (isset($_SESSION['form_renewal_message'])): ?>
                <div class="alert alert-info" role="alert" id="formRenewalMsg">
                    <?php echo htmlspecialchars($_SESSION['form_renewal_message']); unset($_SESSION['form_renewal_message']); ?>
                </div>
                <script>
                    setTimeout(function() {
                        var msg = document.getElementById('formRenewalMsg');
                        if (msg) { msg.style.display = 'none'; }
                    }, 5000);
                </script>
            <?php endif; ?>
            <form method="get" class="filter-card search-box mb-4">
                <input type="text" name="keyword" class="search-input" placeholder="🔍 Search by username, company, email" value="<?php echo htmlspecialchars($keyword); ?>">

                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label">Specialties</label>
                        <div class="checkbox-group" style="max-height:120px; overflow:auto; padding:8px; border:1px solid #eef2f7; border-radius:8px; background:#fff;">
                            <?php foreach ($fixedTrades as $t): ?>
                                <?php $tid = 'trade_' . md5($t); ?>
                                <label><input type="checkbox" name="trade[]" value="<?php echo htmlspecialchars($t); ?>" id="<?php echo $tid; ?>" <?php echo (is_array($selectedTrades) && in_array($t, $selectedTrades)) ? 'checked' : ''; ?>> <?php echo htmlspecialchars($t); ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">CIDB Grade</label>
                        <select name="cidbGrade" class="form-select">
                            <option value="">All Grades</option>
                            <?php for ($g = 1; $g <= 7; $g++): $val = 'G' . $g; ?>
                                <option value="<?php echo $val; ?>" <?php echo ($cidbGrade === $val) ? 'selected' : ''; ?>><?php echo $val; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">State / Location</label>
                        <select name="state" class="form-select">
                            <option value="">All States</option>
                            <?php foreach ($fixedStates as $st): ?>
                                <option value="<?php echo htmlspecialchars($st); ?>" <?php echo ($stateFilter === $st) ? 'selected' : ''; ?>><?php echo htmlspecialchars($st); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Project Value</label>
                        <div class="value-combined">
                            <select name="project_value_type" class="value-type" id="projectValueType">
                                <option value="">None</option>
                                <option value="min" <?php echo ($project_value_type === 'min') ? 'selected' : ''; ?>>Min</option>
                                <option value="max" <?php echo ($project_value_type === 'max') ? 'selected' : ''; ?>>Max</option>
                            </select>
                            <div class="currency-wrapper">
                                <span>RM</span>
                                <input type="text" name="project_value" id="projectValue" placeholder="Enter amount" value="<?php echo htmlspecialchars($project_value); ?>">
                            </div>
                        </div>
                        <div class="project-filter-second-input <?php echo ($project_value_type === 'min') ? 'show' : ''; ?>" id="projectValueMaxContainer">
                            <div class="value-combined" style="border:1px solid #dcdcdc; border-radius:8px;">
                                <select class="value-type" style="background:#f3f4f6; min-width:80px;" disabled>
                                    <option>Max</option>
                                </select>
                                <div class="currency-wrapper">
                                    <span>RM</span>
                                    <input type="text" name="project_value_max" id="projectValueMax" placeholder="Enter amount" value="<?php echo htmlspecialchars($project_value_max); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="AdminVendorManagement.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>

        <?php if (empty($vendors)): ?>
            <div class="vendor-empty">
                <h3>No Vendors Found</h3>
                <p>No vendor accounts match your search. Try a different keyword or add a new account.</p>
            </div>
        <?php else: ?>
            <?php foreach ($vendors as $vendor): ?>
                <?php
                    // Fetch the role for this vendor (if not already in $vendor)
                    // If not present, fallback to clickable for all except 'admin'
                    $isVendor = true;
                    if (isset($vendor['role'])) {
                        $isVendor = ($vendor['role'] === 'vendor');
                    } else {
                        // If role is not present, fetch it from DB
                        $roleResult = mysqli_query($conn, "SELECT role FROM vendoraccount WHERE accountID = '" . mysqli_real_escape_string($conn, $vendor['accountID']) . "' LIMIT 1");
                        if ($roleResult && $roleRow = mysqli_fetch_assoc($roleResult)) {
                            $isVendor = ($roleRow['role'] === 'vendor');
                        }
                    }
                ?>
                <div class="vendor-card<?php echo $isVendor ? ' clickable-vendor-card' : ''; ?>"<?php if ($isVendor): ?> onclick="window.location.href='AdminVendorFormList.php?accountID=<?php echo urlencode($vendor['accountID']); ?>'" style="cursor:pointer;"<?php endif; ?>>
                    <div class="vendor-card-header">
                        <div style="font-weight:700; color:var(--text-main);">
                            <?php echo htmlspecialchars($vendor['companyName'] ?: ($vendor['newCompanyRegistrationNumber'] ?? 'Untitled Company')); ?>
                        </div>
                        <div style="color:var(--text-muted); font-size:13px; text-align:right;">
                            Reg No: <?php echo htmlspecialchars($vendor['newCompanyRegistrationNumber'] ?? '-'); ?>
                            &nbsp;&middot;&nbsp;
                            Username: <?php echo htmlspecialchars($vendor['username'] ?? '-'); ?>
                            &nbsp;&middot;&nbsp;
                            Email: <?php echo htmlspecialchars($vendor['email'] ?? '-'); ?>
                            &nbsp;&middot;&nbsp;
                            ID: <?php echo htmlspecialchars($vendor['accountID'] ?? '-'); ?>
                        </div>
                    </div>
                    <div class="vendor-divider" aria-hidden="true"></div>
                    <div class="vendor-card-details">
                        <!-- username and email removed from card as requested -->
                        <div class="vendor-detail">
                            <span class="vendor-detail-label">Specialties</span>
                            <span class="vendor-detail-value"><?php echo htmlspecialchars($vendor['trade'] ?? '-'); ?></span>
                        </div>
                        <div class="vendor-detail">
                            <span class="vendor-detail-label">Largest Past Project</span>
                            <span class="vendor-detail-value"><?php echo isset($vendor['maxProjectValue']) && $vendor['maxProjectValue'] !== null ? number_format((float)$vendor['maxProjectValue'], 2) : '-'; ?></span>
                        </div>
                        <div class="vendor-detail">
                            <span class="vendor-detail-label">Smallest Past Project</span>
                            <span class="vendor-detail-value"><?php echo isset($vendor['minProjectValue']) && $vendor['minProjectValue'] !== null ? number_format((float)$vendor['minProjectValue'], 2) : '-'; ?></span>
                        </div>
                        <div class="vendor-detail">
                            <span class="vendor-detail-label">CIDB Grade</span>
                            <span class="vendor-detail-value"><?php echo htmlspecialchars($vendor['cidbGrade'] ?? '-'); ?></span>
                        </div>
                    </div>
                    <div style="margin-top:12px; display:flex; gap:8px;">
                        <form method="get" action="export_admin_vendor_pdf.php" style="margin:0;">
                            <input type="hidden" name="accountID" value="<?php echo htmlspecialchars($vendor['accountID']); ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Export PDF</button>
                        </form>
                        <a href="AdminVendorFormList.php?accountID=<?php echo urlencode($vendor['accountID']); ?>" class="btn btn-sm btn-outline-secondary">View Forms</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </main>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show/hide second input for project value range when "min" is selected
const projectValueTypeSelect = document.getElementById('projectValueType');
const projectValueMaxContainer = document.getElementById('projectValueMaxContainer');

if (projectValueTypeSelect) {
    projectValueTypeSelect.addEventListener('change', function() {
        if (this.value === 'min') {
            projectValueMaxContainer.classList.add('show');
        } else {
            projectValueMaxContainer.classList.remove('show');
        }
    });
}

// Format currency input with comma separators
function formatCurrency(input) {
    let value = input.value.replace(/,/g, '');
    if (!isNaN(value) && value !== '') {
        input.value = Number(value).toLocaleString('en-MY');
    }
}

const projectValueInput = document.getElementById('projectValue');
const projectValueMaxInput = document.getElementById('projectValueMax');

if (projectValueInput) {
    projectValueInput.addEventListener('blur', function() {
        formatCurrency(this);
    });
}

if (projectValueMaxInput) {
    projectValueMaxInput.addEventListener('blur', function() {
        formatCurrency(this);
    });
}
</script>
</body>
</html>

