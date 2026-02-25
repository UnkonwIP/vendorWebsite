<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/config.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

// Protect page (vendor only)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor') {
    header("Location: index.php");
    exit();
}

// Get vendor's company registration number from vendoraccount
$vendorAccountID = $_SESSION['accountID'] ?? '';
$vendorNewCompanyRegistration = '';

$formRenewalStatus = '';
if (empty($vendorAccountID)) {
    echo "Error: Vendor account ID not found in session.";
} else {
    $stmtAcc = $conn->prepare("SELECT newCompanyRegistrationNumber, formRenewalStatus FROM vendoraccount WHERE accountID = ?");
    $stmtAcc->bind_param("s", $vendorAccountID);
    $stmtAcc->execute();
    $accResult = $stmtAcc->get_result();
    if ($accRow = $accResult->fetch_assoc()) {
        $vendorNewCompanyRegistration = $accRow['newCompanyRegistrationNumber'];
        $formRenewalStatus = $accRow['formRenewalStatus'] ?? '';
    }
}

$isFirstTimeUser = true;
if (!empty($vendorNewCompanyRegistration)) {
    $stmt = $conn->prepare(
        "SELECT registrationFormID FROM registrationform WHERE newCompanyRegistrationNumber = ? LIMIT 1"
    );
    $stmt->bind_param("s", $vendorNewCompanyRegistration);
    $stmt->execute();
    $formsResult = $stmt->get_result();
    $isFirstTimeUser = ($formsResult->num_rows === 0);
}

// Redirect compulsory for first-time user
if ($isFirstTimeUser) {
    echo '<script>alert("Welcome! You must fill up the registration form before accessing the dashboard."); window.location.href = "registration.php";</script>';
    exit();
}

// Popup for existing user if formRenewalStatus is not 'done'
if (isset($formRenewalStatus) && strtolower($formRenewalStatus) !== 'done') {
    echo '<script>
        setTimeout(function() {
            if (confirm("Your registration form renewal is required. Would you like to fill it now?")) {
                window.location.href = "registration.php";
            }
        }, 300);
    </script>';
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
    <title>Vendor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #059669;
            --primary-hover: #047857;
            --bg-gradient: linear-gradient(135deg, #064e3b, #065f46);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            cursor: pointer;
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

        .new-form-btn {
            display: inline-block;
            padding: 12px 24px;
            background: var(--primary-color);
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

        .new-form-btn:hover {
            background: var(--primary-hover);
            text-decoration: none;
            color: white;
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
        <a class="navbar-brand" href="VendorHomepage.php">
            <img src="Image/company%20logo.png" alt="Logo" style="height: 30px; margin-right: 10px;">
            Vendor Dashboard
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link" style="color: var(--text-muted); margin-right: 20px;">
                        Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Vendor'); ?>
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
        <h1>My Registrations</h1>
        <p>View and manage your submitted registration forms</p>
    </div>

    <div class="forms-container">
        <?php if (strtolower($formRenewalStatus ?? '') !== 'done'): ?>
            <a href="registration.php" class="new-form-btn">+ New Registration Form</a>
        <?php endif; ?>
        <!-- Export PDF button -->
        <a href="export_vendor_pdf.php" class="new-form-btn" style="margin-left:12px;">Export My Info (PDF)</a>

        <?php if (empty($forms)): ?>
            <div class="forms-empty">
                <h3>No Forms Yet</h3>
                <p>You haven't submitted any registration forms yet. Click the button above to get started.</p>
            </div>
        <?php else: ?>
            <?php foreach ($forms as $form): ?>
                <div class="form-card" data-regid="<?php echo htmlspecialchars($form['registrationFormID']); ?>">
                    <div class="form-card-header">
                        <div>
                            <div class="form-card-title">
                                <?php echo htmlspecialchars($form['CompanyName'] ?? 'Unnamed Company'); ?>
                            </div>
                            <div class="text-muted" style="font-size:12px;">Click to view details</div>
                        </div>
                        <span class="status-badge status-<?php echo strtolower($form['status'] ?? 'draft'); ?>">
                            <?php echo htmlspecialchars($form['status'] ?? 'Pending'); ?>
                        </span>
                    </div>

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
                        <form method="get" action="export_vendor_pdf.php" class="export-form">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($form['registrationFormID']); ?>">
                            <button type="submit" class="btn-action btn-view" title="Export this form as PDF">Export PDF</button>
                        </form>

                        <form method="post" action="APIDeleteRegistrationForm.php" onsubmit="return confirm('Are you sure you want to delete this form?');">
                            <input type="hidden" name="registrationFormID" value="<?php echo htmlspecialchars($form['registrationFormID']); ?>">
                            <button type="submit" class="btn-action btn-delete">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Make whole card clickable to open VendorUpdatePage via POST (but ignore clicks on buttons/links inside the card)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.form-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // If clicked element is a button or inside .form-card-actions, ignore
                    if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.form-card-actions')) return;
                    const regId = this.getAttribute('data-regid');
                    if (!regId) return;
                    // create form and submit via POST
                    const form = document.createElement('form');
                    form.method = 'post';
                    form.action = 'VendorUpdatePage.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'registrationFormID';
                    input.value = regId;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                });
            });
        });
    </script>

</body>
</html>
