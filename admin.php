<?php
session_start();
include "config.php";

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
$sql = "SELECT accountID, username, email, newCompanyRegistrationNumber FROM vendoraccount $where ORDER BY accountID DESC";
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
        <h1>Vendor Accounts</h1>
        <p>View and manage all registered vendors</p>
    </div>

    <div class="vendor-container">
        <div class="d-flex flex-wrap mb-3">
            <a href="AccountCreation.php" class="btn-add">+ Add Account</a>
            <form method="post" action="ClearDatabase.php" style="display:inline" onsubmit="return confirm('Are you sure you want to clear the entire database?\nThis action cannot be undone.');">
                <button type="submit" name="clear_database" class="btn-clear">Clear Database</button>
            </form>
        </div>
        <form method="get" class="search-box mb-4 row g-3 align-items-center">
            <div class="col-md-8">
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Search by username, company, email..." class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Search</button>
                <a href="admin.php" class="btn btn-secondary">Reset</a>
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
                        <div class="vendor-card-title">
                            <?php echo htmlspecialchars($vendor['username']); ?>
                        </div>
                            <span class="text-muted" style="font-size:13px;">
                                Account ID: <?php echo htmlspecialchars($vendor['accountID']); ?>
                            </span>
                    </div>
                    <div class="vendor-card-details">
                        <div class="vendor-detail">
                            <span class="vendor-detail-label">Username</span>
                            <span class="vendor-detail-value"><?php echo htmlspecialchars($vendor['username']); ?></span>
                        </div>
                        <div class="vendor-detail">
                            <span class="vendor-detail-label">Email</span>
                            <span class="vendor-detail-value"><?php echo htmlspecialchars($vendor['email']); ?></span>
                        </div>
                        <div class="vendor-detail">
                            <span class="vendor-detail-label">Company Reg. No</span>
                            <span class="vendor-detail-value"><?php echo htmlspecialchars($vendor['newCompanyRegistrationNumber']); ?></span>
                        </div>
                        <!-- <div class="vendor-detail">
                            <span class="vendor-detail-label">Company Name</span>
                            <span class="vendor-detail-value"><?php echo htmlspecialchars($vendor['companyName']); ?></span>
                        </div> -->
                    </div>
                    <!-- You can add admin actions here if needed -->
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

