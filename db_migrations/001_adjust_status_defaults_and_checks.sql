-- Migration: adjust registrationform.status default and add CHECK constraints
-- Run this after restoring the dump (or include before creating the table in a DROP/CREATE flow).
-- Test in a staging environment first.

START TRANSACTION;

-- 1) Normalize legacy main status values to canonical tokens
UPDATE registrationform
SET status = 'not review'
WHERE LOWER(status) IN ('draft', '', 'not reviewed', 'not_review');

-- Add any other mappings you discover from SELECT DISTINCT LOWER(status) FROM registrationform;

-- 2) Ensure department status fields are canonical (fill NULL/empty)
UPDATE registrationform
SET planDepartmentStatus = 'not review'
WHERE planDepartmentStatus IS NULL OR TRIM(planDepartmentStatus) = '';

UPDATE registrationform
SET financeDepartmentStatus = 'not review'
WHERE financeDepartmentStatus IS NULL OR TRIM(financeDepartmentStatus) = '';

UPDATE registrationform
SET legalDepartmentStatus = 'not review'
WHERE legalDepartmentStatus IS NULL OR TRIM(legalDepartmentStatus) = '';

UPDATE registrationform
SET projectDepartmentStatus = 'not review'
WHERE projectDepartmentStatus IS NULL OR TRIM(projectDepartmentStatus) = '';

-- 3) Change default for main status to canonical initial token
ALTER TABLE registrationform
  MODIFY COLUMN `status` varchar(20) NOT NULL DEFAULT 'not review';

-- 4) Add CHECK constraints to enforce allowed tokens (MariaDB/MySQL 8+)
ALTER TABLE registrationform
  ADD CONSTRAINT chk_registration_status CHECK (status IN ('not review','pending approval','approved','rejected')),
  ADD CONSTRAINT chk_plan_dept_status CHECK (planDepartmentStatus IN ('not review','pending approval','approved','rejected')),
  ADD CONSTRAINT chk_finance_dept_status CHECK (financeDepartmentStatus IN ('not review','pending approval','approved','rejected')),
  ADD CONSTRAINT chk_legal_dept_status CHECK (legalDepartmentStatus IN ('not review','pending approval','approved','rejected')),
  ADD CONSTRAINT chk_project_dept_status CHECK (projectDepartmentStatus IN ('not review','pending approval','approved','rejected'));

-- 5) Optional: index for faster admin queries
CREATE INDEX IF NOT EXISTS idx_registration_status ON registrationform (status);

COMMIT;

-- Notes:
-- - If your MariaDB/MySQL version ignores CHECK constraints, consider enforcing these via triggers or application-level checks.
-- - Run `SELECT DISTINCT LOWER(status) FROM registrationform;` before the migration to confirm any other legacy tokens to map.
-- - Do not add UNIQUE constraints (e.g., on username) until duplicates are resolved.
