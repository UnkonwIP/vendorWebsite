<?php
// Cleanup pending vendor accounts whose reset expiry has passed.
// Place this file at: scripts/cleanup_pending_accounts.php
// Run from CLI: php archive/scripts/cleanup_pending_accounts.php

chdir(__DIR__ . '/..');
require_once __DIR__ . '/../config.php';

date_default_timezone_set('Asia/Kuala_Lumpur');
$now = date('Y-m-d H:i:s');

// This script expects $conn (mysqli) from config.php. If not present, try PDO fallback.
$useMysqli = (isset($conn) && $conn instanceof mysqli);
$usePdo = false;
if (!$useMysqli) {
    // try to build PDO using common constants from config.php
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $usePdo = true;
        } catch (Exception $e) {
            fwrite(STDERR, "DB connection failed: " . $e->getMessage() . PHP_EOL);
            exit(1);
        }
    } else {
        fwrite(STDERR, "No DB connection (\$conn) from config.php and no DB constants for PDO fallback." . PHP_EOL);
        exit(1);
    }
}

// Selection criteria - adjust these column names if your schema differs.
// This looks for accounts where `resetExpiry` is set and already passed.
$selectSql = "SELECT accountID FROM vendoraccount WHERE resetExpiry IS NOT NULL AND resetExpiry < ?";

$deleted = 0;
try {
    if ($useMysqli) {
        $conn->begin_transaction();
        $s = $conn->prepare($selectSql);
        $s->bind_param('s', $now);
        $s->execute();
        $res = $s->get_result();
        $ids = [];
        while ($r = $res->fetch_assoc()) $ids[] = $r['accountID'];

        if (count($ids)) {
            // prepare deletes for related tables; adjust table names if different
            $delRegistration = $conn->prepare('DELETE FROM registrationform WHERE accountID = ?');
            $delShareholders = $conn->prepare('DELETE FROM shareholders WHERE registrationFormID IN (SELECT registrationFormID FROM registrationform WHERE accountID = ?)');
            $delOtherRelated = $conn->prepare('DELETE FROM directorandsecretary WHERE registrationFormID IN (SELECT registrationFormID FROM registrationform WHERE accountID = ?)');
            $delAccount = $conn->prepare('DELETE FROM vendoraccount WHERE accountID = ?');

            foreach ($ids as $id) {
                // delete dependent rows first
                $delRegistration->bind_param('s', $id); $delRegistration->execute();
                // additional dependent deletes - best-effort (may not match every schema)
                $delShareholders->bind_param('s', $id); $delShareholders->execute();
                $delOtherRelated->bind_param('s', $id); $delOtherRelated->execute();
                // finally delete account
                $delAccount->bind_param('s', $id); $delAccount->execute();
                $deleted++;
            }
        }
        $conn->commit();
    } else { // PDO
        $pdo->beginTransaction();
        $stmt = $pdo->prepare($selectSql);
        $stmt->execute([$now]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (count($ids)) {
            $delRegistration = $pdo->prepare('DELETE FROM registrationform WHERE accountID = ?');
            $delShareholders = $pdo->prepare('DELETE FROM shareholders WHERE registrationFormID IN (SELECT registrationFormID FROM registrationform WHERE accountID = ?)');
            $delOtherRelated = $pdo->prepare('DELETE FROM directorandsecretary WHERE registrationFormID IN (SELECT registrationFormID FROM registrationform WHERE accountID = ?)');
            $delAccount = $pdo->prepare('DELETE FROM vendoraccount WHERE accountID = ?');
            foreach ($ids as $id) {
                $delRegistration->execute([$id]);
                $delShareholders->execute([$id]);
                $delOtherRelated->execute([$id]);
                $delAccount->execute([$id]);
                $deleted++;
            }
        }
        $pdo->commit();
    }
    echo "Deleted {$deleted} pending accounts\n";
    exit(0);
} catch (Exception $e) {
    if ($useMysqli && isset($conn)) $conn->rollback();
    if ($usePdo && isset($pdo)) $pdo->rollBack();
    fwrite(STDERR, "Error during cleanup: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
