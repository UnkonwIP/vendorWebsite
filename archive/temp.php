<?php
$search = $_GET['search'] ?? '';
$minValue = $_GET['min_value'] ?? '';
$maxValue = $_GET['max_value'] ?? '';
?>

<style>
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f4f6f9;
}

.container {
    display: flex;
}

/* Sidebar */
.sidebar {
    width: 220px;
    background: #1f2937;
    color: white;
    height: 100vh;
    padding: 20px;
}

.sidebar .logo {
    margin-bottom: 30px;
}

.sidebar ul {
    list-style: none;
}

.sidebar ul li {
    padding: 12px;
    cursor: pointer;
    border-radius: 6px;
}

.sidebar ul li:hover,
.sidebar ul li.active {
    background: #374151;
}

/* Main */
.main-content {
    flex: 1;
    padding: 25px;
}

/* Topbar */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.topbar input {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.btn-primary {
    background: #2563eb;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
}

/* Cards */
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

/* Action Required */
.action-required {
    background: #fff3cd;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
}

/* Filters */
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
}

.value-range {
    display: flex;
    gap: 5px;
}

.btn-secondary {
    background: #374151;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
}

/* Table */
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
}

.badge {
    padding: 4px 8px;
    border-radius: 6px;
    color: white;
}

.badge.pending { background: #f59e0b; }
.badge.approved { background: #10b981; }
.badge.rejected { background: #ef4444; }

.btn-small {
    padding: 4px 8px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background: #6b7280;
    color: white;
}

.btn-small.approve { background: #10b981; }
.btn-small.reject { background: #ef4444; }

/* Activity */
.activity {
    background: white;
    padding: 15px;
    border-radius: 8px;
}
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Management</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <h2 class="logo">VendorSys</h2>
        <ul>
            <li class="active">Dashboard</li>
            <li>Registration Forms</li>
            <li>Approved Vendors</li>
            <li>Reports</li>
            <li>Settings</li>
            <li>Logout</li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Top Header -->
        <div class="topbar">
            <h1>Registration Management</h1>
            <div class="top-actions">
                <input type="text" placeholder="Search company...">
                <button class="btn-primary">+ New Registration</button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="cards">
            <div class="card total">
                <h3>150</h3>
                <p>Total Registrations</p>
            </div>
            <div class="card pending">
                <h3>25</h3>
                <p>Pending Review</p>
            </div>
            <div class="card approved">
                <h3>98</h3>
                <p>Approved</p>
            </div>
            <div class="card rejected">
                <h3>27</h3>
                <p>Rejected</p>
            </div>
        </div>

        <!-- Action Required Section -->
        <div class="action-required">
            <h2>⚠ Requires Your Attention</h2>
            <ul>
                <li>5 forms pending more than 7 days</li>
                <li>3 submissions missing documents</li>
                <li>2 high-value vendors awaiting approval</li>
            </ul>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h2>Advanced Filter</h2>
            <div class="filters">
                <input type="text" placeholder="Registration ID">
                <input type="text" placeholder="Company Name">
                <select>
                    <option>Status</option>
                    <option>Pending</option>
                    <option>Approved</option>
                    <option>Rejected</option>
                </select>
                <input type="date">
                <input type="date">
                <div class="value-range">
                    <input type="number" placeholder="Min RM">
                    <input type="number" placeholder="Max RM">
                </div>
                <button class="btn-secondary">Apply Filter</button>
            </div>
        </div>

        <!-- Registration Table -->
        <div class="table-section">
            <h2>Registration List</h2>

            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ID</th>
                        <th>Company</th>
                        <th>Date</th>
                        <th>Project Value</th>
                        <th>Status</th>
                        <th>Officer</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>#RF1001</td>
                        <td>ABC Sdn Bhd</td>
                        <td>2026-02-20</td>
                        <td>RM 120,000</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td>John</td>
                        <td>
                            <button class="btn-small">View</button>
                            <button class="btn-small approve">Approve</button>
                            <button class="btn-small reject">Reject</button>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <!-- Activity Panel -->
        <div class="activity">
            <h2>Recent Activity</h2>
            <ul>
                <li>John approved XYZ Sdn Bhd</li>
                <li>Vendor uploaded missing document</li>
                <li>System auto-archived expired form</li>
            </ul>
        </div>

    </main>
</div>

</body>
</html>