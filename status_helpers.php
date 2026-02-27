<?php
// Canonical status tokens for registration workflow
if (!defined('STATUS_HELPERS_INCLUDED')) {
    define('STATUS_HELPERS_INCLUDED', true);

    $CANONICAL_STATUSES = ['not review', 'pending approval', 'approved', 'rejected'];

    function normalize_status($s) {
        $val = strtolower(trim((string)$s));
        // Map empty/legacy tokens to the canonical 'not review'
        if ($val === '') return 'not review';
        return $val;
    }

    function is_canonical_status($s) {
        global $CANONICAL_STATUSES;
        return in_array(normalize_status($s), $CANONICAL_STATUSES, true);
    }

    // Return canonical status or false
    function canonical_status_or_false($s) {
        $n = normalize_status($s);
        return is_canonical_status($n) ? $n : false;
    }

    // Determine allowed department transition. Returns the new status string or false if not allowed.
    function allowed_department_transition($currentStatus, $action, $role) {
        $c = normalize_status($currentStatus);
        $action = strtolower(trim((string)$action));
        $role = strtolower(trim((string)$role));

        if ($action === 'approve') {
            if ($role === 'admin') {
                // department admin approves their piece: only allowed from 'not review' -> 'pending approval'
                return $c === 'not review' ? 'pending approval' : false;
            } elseif ($role === 'admin_head') {
                // head can finalize department from 'pending approval' -> 'approved'
                return $c === 'pending approval' ? 'approved' : false;
            }
        } elseif ($action === 'reject') {
            // reject is allowed unless already rejected
            return $c === 'rejected' ? false : 'rejected';
        }
        return false;
    }

    // Determine allowed main-form transition by general admin.
    // Returns an array: [newStatus|false, initializeDepartments(bool)]
    function allowed_main_transition($currentStatus, $requestedStatus) {
        $c = normalize_status($currentStatus);
        $r = normalize_status($requestedStatus);

        if ($r === 'pending approval' || $r === 'approved') {
            // General admin action 'approved' means move from 'not review' -> 'pending approval'
            if ($c === 'not review') {
                return ['pending approval', true]; // initialize departments
            }
            return [false, false];
        }

        if ($r === 'rejected') {
            // Allow rejection unless already rejected
            return [$c === 'rejected' ? false : 'rejected', false];
        }

        return [false, false];
    }

    // Map vendorType to department column using an explicit whitelist.
    // Returns the column name (e.g. 'financeDepartmentStatus') or null if no mapping.
    function map_vendorType_to_dept_column($vendorType) {
        $map = [
            'finance' => 'financeDepartmentStatus',
            'project' => 'projectDepartmentStatus',
            'legal'   => 'legalDepartmentStatus',
            'plan'    => 'planDepartmentStatus',
        ];

        if ($vendorType === null) return null;
        $vt = strtolower(trim((string)$vendorType));

        foreach ($map as $key => $col) {
            if (strpos($vt, $key) !== false) return $col;
        }
        return null;
    }

    // Convenience: resolve a dept column for an accountID (returns column name or null)
    function get_dept_column_for_account($conn, $accountID) {
        $accountID = (int)$accountID;
        $stmt = mysqli_prepare($conn, "SELECT vendorType FROM vendoraccount WHERE accountID = ? LIMIT 1");
        if (!$stmt) return null;
        mysqli_stmt_bind_param($stmt, 'i', $accountID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $vendorType);
        $dept = null;
        if (mysqli_stmt_fetch($stmt)) {
            $dept = map_vendorType_to_dept_column($vendorType);
            // If we resolved a dept column, normalize the vendorType in the DB to a canonical label
            if ($dept !== null) {
                // derive canonical label from the mapping keys (e.g., 'finance' -> 'Finance')
                $map = [
                    'finance' => 'financeDepartmentStatus',
                    'project' => 'projectDepartmentStatus',
                    'legal'   => 'legalDepartmentStatus',
                    'plan'    => 'planDepartmentStatus',
                ];
                $canonical = null;
                foreach ($map as $k => $c) {
                    if ($c === $dept) { $canonical = ucfirst($k); break; }
                }
                if ($canonical !== null) {
                    $cur = trim((string)$vendorType);
                    if ($cur !== $canonical) {
                        $u = mysqli_prepare($conn, "UPDATE vendoraccount SET vendorType = ? WHERE accountID = ?");
                        if ($u) {
                            mysqli_stmt_bind_param($u, 'si', $canonical, $accountID);
                            mysqli_stmt_execute($u);
                            mysqli_stmt_close($u);
                        }
                    }
                }
            }
        }
        mysqli_stmt_close($stmt);
        return $dept;
    }
}
