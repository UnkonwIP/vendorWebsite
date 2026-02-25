# Multi-Stage Approval Workflow Documentation

## Overview
The vendor registration form approval system now follows a multi-stage workflow involving:
1. General Admin (data completeness review)
2. Department Admins (4 departments: Finance, Project, Legal, Plan)
3. Department Heads (Head of each department)

## Workflow Steps

### Stage 1: Data Completeness Review
**Role:** General Admin (`role = 'admin'`)
**Form Status:** `pending`

- General admin reviews the submitted registration form for data completeness
- **If data is complete:**
  - Click "Approve Data (Send to Departments)"
  - Form status changes to: `pending approval`
  - All 4 department statuses are set to: `pending`
  - Form is now ready for department reviews
- **If data is incomplete:**
  - Click "Reject (Data Incomplete)"
  - Provide rejection reason
  - Form status changes to: `rejected`
  - Vendor must resubmit

### Stage 2: Department Admin Review
**Role:** Department Admin (`role = 'admin'` with `vendorType` = department name)
**Form Status:** `pending approval`
**Department Status:** `pending`

Each department admin (Finance, Project, Legal, Plan) reviews their specific section:
- Admin sees approval buttons only when:
  - Form status is `pending approval`
  - Their department's status is `pending`
- **If approved:**
  - Click "Approve ([Department Name])"
  - Their department status changes to: `pending approval` (waiting for head)
- **If rejected:**
  - Click "Reject"
  - Department status changes to: `rejected`

### Stage 3: Department Head Approval
**Role:** Department Head (`role = 'admin_head'` with `vendorType` = department name)
**Form Status:** `pending approval`
**Department Status:** `pending approval`

Each department head reviews and approves after their department admin:
- Head sees approval buttons only when:
  - Form status is `pending approval`
  - Their department's status is `pending approval`
- **If approved:**
  - Click "Approve (Head of [Department])"
  - Their department status changes to: `approved`
- **If rejected:**
  - Click "Reject"
  - Department status changes to: `rejected`

### Stage 4: Final Approval
**Automatic Trigger**

Once the last department head approves their section:
- System checks if all 4 departments are `approved`
- If YES: Main form status automatically changes to: `approved`
- Vendor is notified of approval

## Database Schema

### registrationform table columns used:
- `status` - Main form status: `pending`, `pending approval`, `approved`, `rejected`
- `financeDepartmentStatus` - Finance dept: `not reviewed`, `pending`, `pending approval`, `approved`, `rejected`
- `projectDepartmentStatus` - Project dept: `not reviewed`, `pending`, `pending approval`, `approved`, `rejected`
- `legalDepartmentStatus` - Legal dept: `not reviewed`, `pending`, `pending approval`, `approved`, `rejected`
- `planDepartmentStatus` - Plan dept: `not reviewed`, `pending`, `pending approval`, `approved`, `rejected`

### vendoraccount table columns used:
- `role` - User role: `admin`, `admin_head`, `vendor`
- `vendorType` - For admins: department name (e.g., "Finance", "Legal", "Project", "Plan", "General")

## API Endpoints

### APIUpdateFormStatus.php
**Purpose:** General admin data completeness approval/rejection
**Access:** `role = 'admin'` only
**Actions:**
- Approve: Sets form status to `pending approval`, initializes all dept statuses to `pending`
- Reject: Sets form status to `rejected`, requires rejection reason

### APIDepartmentApproval.php (NEW)
**Purpose:** Department admin and head approvals
**Access:** `role = 'admin'` or `role = 'admin_head'`
**Actions:**
- Department Admin Approve: Sets department status to `pending approval`
- Department Head Approve: Sets department status to `approved`, checks if all depts approved
- Reject: Sets department status to `rejected`

## Page Access Control

### AdminRegistrationManagement.php
- Shows ALL forms for general admins
- Used by general admins to review data completeness

### AdminHeadRegisrationManagement.php
- Restricted to `admin_head` only
- Filters to show only forms where their department status is `pending approval`
- Used by department heads for final approvals

### AdminViewPage.php
- Shows appropriate approval buttons based on:
  - User role (`admin` vs `admin_head`)
  - Form status (`pending` vs `pending approval`)
  - Department status for the user's department

## Status Flow Examples

### Example 1: Successful Full Approval
1. Form submitted → status: `pending`, all depts: `not reviewed`
2. General admin approves → status: `pending approval`, all depts: `pending`
3. Finance admin approves → financeDepartmentStatus: `pending approval`
4. Finance head approves → financeDepartmentStatus: `approved`
5. Project admin approves → projectDepartmentStatus: `pending approval`
6. Project head approves → projectDepartmentStatus: `approved`
7. Legal admin approves → legalDepartmentStatus: `pending approval`
8. Legal head approves → legalDepartmentStatus: `approved`
9. Plan admin approves → planDepartmentStatus: `pending approval`
10. Plan head approves → planDepartmentStatus: `approved`
11. **AUTO:** All depts approved → status: `approved` ✅

### Example 2: Rejection at Data Review
1. Form submitted → status: `pending`
2. General admin rejects → status: `rejected`, provides reason
3. Vendor must fix and resubmit

### Example 3: Rejection by Department
1. General admin approves → status: `pending approval`
2. Finance admin rejects → financeDepartmentStatus: `rejected`
3. Form cannot proceed to full approval

## Implementation Files Modified

1. **APIDepartmentApproval.php** (NEW)
   - Handles department admin and head approvals
   - Checks if all departments approved and updates main status

2. **APIUpdateFormStatus.php** (UPDATED)
   - Now initializes all department statuses when general admin approves
   - Sets form to `pending approval` instead of direct `approved`

3. **AdminViewPage.php** (UPDATED)
   - Shows role-appropriate approval buttons
   - Different button text based on approval stage
   - Routes to correct API endpoint

4. **AdminHeadRegisrationManagement.php** (UPDATED)
   - Filters forms by department head's department
   - Shows only forms requiring head's attention

## Testing Checklist

- [ ] General admin can approve data completeness
- [ ] General admin approval sets all depts to pending
- [ ] Department admin sees only their pending forms
- [ ] Department admin approval sets dept to "pending approval"
- [ ] Department head sees only their "pending approval" forms
- [ ] Department head approval sets dept to "approved"
- [ ] System auto-approves form when all 4 depts approved
- [ ] Rejection at any stage works correctly
- [ ] Role-based sidebar navigation works
- [ ] Approval buttons show only when appropriate
