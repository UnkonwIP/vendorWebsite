

<?php
session_start();
include "database.php";

// Protect admin page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get all tables
$tables = [];
$tablesResult = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_array($tablesResult)) {
    $tables[] = $row[0];
}

// Get search inputs
$selectedTables = $_GET['tables'] ?? [];
$keyword = $_GET['keyword'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .top-right {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .add-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .add-btn:hover {
            background-color: #0056b3;
        }
        .clear-btn {
            background-color: #dc3545;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            margin-left: 10px;
        }
        .clear-btn:hover {
            background-color: #a71d2a;
        }
        .search-box {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        .table-responsive {
            margin-bottom: 40px;
        }
    </style>
</head>
<body>

    <header class="mb-4">
        <h1 class="mb-3">Admin Panel</h1>
        <div class="top-right">
            <a href="create_vendor_account.php" class="add-btn">+ Add Account</a>
            <form method="post" action="ClearDatabase.php" style="display:inline" onsubmit="return confirm('Are you sure you want to clear the entire database?\nThis action cannot be undone.');">
                <button type="submit" name="clear_database" class="clear-btn">Clear Database</button>
            </form>
        </div>
        <p>Welcome, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></p>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
        <hr>
    </header>

    <!-- 🔍 SEARCH AREA -->
    <section class="search-box mb-4">
        <form method="get" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label"><b>Select Tables (Topics):</b></label>
                <select name="tables[]" multiple size="5" class="form-select">
                    <?php foreach ($tables as $table): ?>
                        <option value="<?php echo $table; ?>" <?php echo in_array($table, $selectedTables) ? 'selected' : ''; ?>><?php echo $table; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label"><b>Keyword Search:</b></label>
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Type keyword (e.g. bu)" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Search</button>
                <a href="admin.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <main>
    <?php
    // Decide which tables to search
    $tablesToShow = empty($selectedTables) ? $tables : $selectedTables;
    $foundAny = false;

    // Loop through tables
    foreach ($tablesToShow as $tableName) {
        // Get columns
        $columnsResult = mysqli_query($conn, "SHOW COLUMNS FROM `$tableName`");
        if (!$columnsResult) continue;

        $columns = [];
        while ($col = mysqli_fetch_assoc($columnsResult)) {
            $columns[] = $col['Field'];
        }

        // Build WHERE clause for partial search
        if ($keyword !== '') {
            $conditions = [];
            foreach ($columns as $col) {
                $conditions[] = "`$col` LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%'";
            }
            $where = "WHERE " . implode(" OR ", $conditions);
        } else {
            $where = "";
        }

        // Query table
        $query = "SELECT * FROM `$tableName` $where";
        $dataResult = mysqli_query($conn, $query);
        if (!$dataResult) continue;

        // 🚫 Skip tables with NO matching rows
        if ($keyword !== '' && mysqli_num_rows($dataResult) == 0) {
            continue;
        }

        // At least one result exists
        $foundAny = true;

        echo "<h2 class='mt-5 mb-3'>Table: " . htmlspecialchars($tableName) . "</h2>";
        echo "<div class='table-responsive'><table class='table table-bordered table-striped'><thead><tr>";
        foreach ($columns as $col) {
            echo "<th>" . htmlspecialchars($col) . "</th>";
        }
        echo "</tr></thead><tbody>";
        while ($row = mysqli_fetch_assoc($dataResult)) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table></div>";
    }

    // Show message if nothing found
    if ($keyword !== '' && !$foundAny) {
        echo "<p class='alert alert-warning'><b>No results found for &quot;" . htmlspecialchars($keyword) . "&quot;.</b></p>";
    }
    ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

