<?php
require_once __DIR__ . '/../config.php';

// This script normalizes vendoraccount.vendorType values to the new keys:
// 'General', 'Finance', 'Legal', 'Project', 'Plan'

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// 1) Replace values like 'Head - Finance' -> 'Finance'
$queries = [
    "UPDATE vendoraccount SET vendorType = TRIM(REPLACE(vendorType, 'Head - ', '')) WHERE vendorType LIKE 'Head - %'",
    // 2) Normalize common variants to canonical keys
    "UPDATE vendoraccount SET vendorType = 'Finance' WHERE LOWER(TRIM(vendorType)) IN ('head finance','head - finance','finance','head -finance')",
    "UPDATE vendoraccount SET vendorType = 'Legal' WHERE LOWER(TRIM(vendorType)) IN ('head legal','head - legal','legal')",
    "UPDATE vendoraccount SET vendorType = 'Project' WHERE LOWER(TRIM(vendorType)) IN ('head project','head - project','project')",
    "UPDATE vendoraccount SET vendorType = 'Plan' WHERE LOWER(TRIM(vendorType)) IN ('head plan','head - plan','plan')",
    "UPDATE vendoraccount SET vendorType = 'General' WHERE LOWER(TRIM(vendorType)) IN ('head general','head - general','general')",
];

foreach ($queries as $q) {
    $res = $conn->query($q);
    if ($res === false) {
        echo "Query failed: " . $conn->error . "\n";
    } else {
        echo "Query OK: affected rows = " . $conn->affected_rows . "\n";
    }
}

echo "Migration complete.\n";

// Close connection
$conn->close();

?>
