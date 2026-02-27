<?php
// Canonical status tokens for registration workflow
if (!defined('STATUS_HELPERS_INCLUDED')) {
    define('STATUS_HELPERS_INCLUDED', true);

    $CANONICAL_STATUSES = ['not review', 'pending approval', 'approved', 'rejected'];

    function normalize_status($s) {
        if ($s === null) return '';
        return strtolower(trim((string)$s));
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
}
