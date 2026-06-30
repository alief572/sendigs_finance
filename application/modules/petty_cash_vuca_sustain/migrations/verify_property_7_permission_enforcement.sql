-- =============================================================================
-- Property 7: Permission Enforcement
-- =============================================================================
--
-- Validates: Requirements 8.1, 8.2, 8.3
--
-- Property Statement:
-- For any HTTP request to any endpoint in this module, if the requesting user
-- lacks the `Petty_Cash_Vuca_Sustain.View` permission, access to index/view/print
-- SHALL be denied; and if the user lacks `Petty_Cash_Vuca_Sustain.Manage`
-- permission, the Payment Hutang action SHALL be denied regardless of the
-- record's status.
--
-- Enforcement Mechanism:
-- Permissions are enforced at the CODE level (PHP controller) using
-- `$this->auth->restrict()` which redirects unauthorized users.
-- This is NOT a database-level constraint.
--
-- Therefore, this property test is primarily a CODE REVIEW CHECKLIST
-- with supplementary SQL to verify permission strings exist in the system.
--
-- =============================================================================
-- CODE REVIEW CHECKLIST (Permission Enforcement)
-- =============================================================================
--
-- ✓ 1. Permission String Definitions (Controller class properties):
--       - $viewPermission   = 'Petty_Cash_Vuca_Sustain.View'
--       - $managePermission = 'Petty_Cash_Vuca_Sustain.Manage'
--       LOCATION: Petty_cash_vuca_sustain.php lines 22-23
--       CONCLUSION: Both permission strings are defined as class properties
--
-- ✓ 2. index() → restricts viewPermission ('Petty_Cash_Vuca_Sustain.View')
--       CODE: $this->auth->restrict($this->viewPermission);
--       LOCATION: First line inside index() method
--       BEHAVIOR: Unauthorized users are redirected before any data is loaded
--       CONCLUSION: Server-side enforcement on EVERY request (not just UI hide)
--
-- ✓ 3. get_data() → restricts viewPermission ('Petty_Cash_Vuca_Sustain.View')
--       CODE: $this->auth->restrict($this->viewPermission);
--       LOCATION: First line inside get_data() method
--       BEHAVIOR: AJAX DataTables requests also require permission
--       CONCLUSION: Even direct POST to get_data endpoint is protected
--
-- ✓ 4. view($id) → restricts viewPermission ('Petty_Cash_Vuca_Sustain.View')
--       CODE: $this->auth->restrict($this->viewPermission);
--       LOCATION: First line inside view() method
--       BEHAVIOR: Detail page requires View permission
--       CONCLUSION: Cannot access detail page by guessing URL with record ID
--
-- ✓ 5. print_pdf($id) → restricts viewPermission ('Petty_Cash_Vuca_Sustain.View')
--       CODE: $this->auth->restrict($this->viewPermission);
--       LOCATION: First line inside print_pdf() method
--       BEHAVIOR: PDF generation requires View permission
--       CONCLUSION: Cannot generate PDF by directly hitting print_pdf URL
--
-- ✓ 6. payment_hutang($id) → restricts managePermission ('Petty_Cash_Vuca_Sustain.Manage')
--       CODE: $this->auth->restrict($this->managePermission);
--       LOCATION: First line inside payment_hutang() method
--       BEHAVIOR: Payment action requires Manage permission (higher privilege)
--       CONCLUSION: Even with View permission, user cannot execute Payment Hutang
--                   without explicit Manage permission
--
-- =============================================================================
-- ENFORCEMENT MECHANISM NOTES
-- =============================================================================
--
-- ✓ A. $this->auth->restrict() behavior:
--       - Checks if current logged-in user has the specified permission
--       - If NOT authorized: redirects user to unauthorized/access-denied page
--       - This happens BEFORE any business logic executes
--       - The redirect terminates the request — no further code runs
--       - This is a hard block, not a soft check
--
-- ✓ B. Server-Side Enforcement (Requirement 8.4):
--       - Permission is checked on EVERY HTTP request at the controller level
--       - This is NOT just a client-side UI hide (e.g., hiding buttons in JS)
--       - Even if a user manually crafts a URL or AJAX request, the server
--         will reject it if they lack the required permission
--       - All 5 public methods have auth->restrict() as their FIRST action
--
-- ✓ C. Dual Permission Model:
--       - View permission: required for read-only operations (index, get_data, view, print_pdf)
--       - Manage permission: required for write operations (payment_hutang)
--       - A user with only View can see data but cannot process payments
--       - A user with Manage but no View cannot even access the module
--         (payment_hutang would technically pass, but they can't reach the UI)
--       - The UI also conditionally shows the Payment Hutang button based on
--         has_permission($this->managePermission) — defense in depth
--
-- ✓ D. Client-Side Permission Hiding (Defense in Depth):
--       - index.php view receives `has_manage` flag from controller
--       - Payment Hutang button is only rendered if has_manage is true
--       - This is a UX enhancement, NOT the security boundary
--       - Server-side restrict() is the actual enforcement mechanism
--
-- ✓ E. Independence from expense_petty_cash permissions (Requirement 8.5):
--       - This module uses 'Petty_Cash_Vuca_Sustain.View' and 'Petty_Cash_Vuca_Sustain.Manage'
--       - expense_petty_cash uses its own separate permission strings
--       - A user can have access to one module without the other
--       - No cross-module permission dependency exists
--
-- =============================================================================
-- COMPLETE METHOD-TO-PERMISSION MAPPING
-- =============================================================================
--
-- | Method              | HTTP     | Permission Required                    | Enforcement          |
-- |---------------------|----------|----------------------------------------|----------------------|
-- | index()             | GET      | Petty_Cash_Vuca_Sustain.View           | auth->restrict()     |
-- | get_data()          | POST     | Petty_Cash_Vuca_Sustain.View           | auth->restrict()     |
-- | view($id)           | GET      | Petty_Cash_Vuca_Sustain.View           | auth->restrict()     |
-- | print_pdf($id)      | GET      | Petty_Cash_Vuca_Sustain.View           | auth->restrict()     |
-- | payment_hutang($id) | POST     | Petty_Cash_Vuca_Sustain.Manage         | auth->restrict()     |
--
-- ALL public methods have server-side permission enforcement ✓
-- No public method is accessible without authentication ✓
-- No public method is accessible without proper authorization ✓
--
-- =============================================================================


-- =============================================================================
-- SQL VERIFICATION: Check Permission Strings Exist in System Tables
-- =============================================================================
-- The following queries verify that the permission strings used by this module
-- are registered in the system's permission/role tables.
-- These queries are optional — they verify configuration, not code logic.
-- If the permission tables don't exist or use a different schema, these
-- queries may need adjustment based on the actual Bonfire auth schema.
-- =============================================================================


-- =============================================================================
-- CHECK 1: Verify 'Petty_Cash_Vuca_Sustain.View' permission exists
-- Checks the permissions table (Bonfire standard) for the View permission.
-- Expected: At least 1 row returned (permission is registered)
-- If empty: Permission needs to be added to the system via admin panel
-- =============================================================================
SELECT
    permission_id,
    name,
    description,
    status
FROM permissions
WHERE name = 'Petty_Cash_Vuca_Sustain.View';


-- =============================================================================
-- CHECK 2: Verify 'Petty_Cash_Vuca_Sustain.Manage' permission exists
-- Checks the permissions table (Bonfire standard) for the Manage permission.
-- Expected: At least 1 row returned (permission is registered)
-- If empty: Permission needs to be added to the system via admin panel
-- =============================================================================
SELECT
    permission_id,
    name,
    description,
    status
FROM permissions
WHERE name = 'Petty_Cash_Vuca_Sustain.Manage';


-- =============================================================================
-- CHECK 3: Verify permissions are assigned to at least one role
-- A permission that exists but is not assigned to any role means
-- NO user can access the module (which may or may not be intended).
-- Expected: At least 1 row per permission (assigned to at least one role)
-- =============================================================================
SELECT
    p.name AS permission_name,
    r.role_name,
    rp.role_id,
    rp.permission_id
FROM role_permissions rp
INNER JOIN permissions p ON p.permission_id = rp.permission_id
INNER JOIN roles r ON r.role_id = rp.role_id
WHERE p.name IN ('Petty_Cash_Vuca_Sustain.View', 'Petty_Cash_Vuca_Sustain.Manage')
ORDER BY p.name, r.role_name;


-- =============================================================================
-- CHECK 4: Verify no user has Manage without View
-- A user with Manage but without View would be an unusual configuration.
-- They could technically call payment_hutang() directly but cannot see the
-- index page. This check flags such configurations for review.
-- Note: This uses Bonfire's standard role_permissions + user roles schema.
-- Expected: Empty result (all users with Manage also have View)
-- =============================================================================
SELECT
    u.id AS user_id,
    u.username,
    r.role_name,
    'HAS_MANAGE_WITHOUT_VIEW' AS warning_type,
    'User role has Manage permission but lacks View permission' AS description
FROM users u
INNER JOIN roles r ON r.role_id = u.role_id
INNER JOIN role_permissions rp_manage ON rp_manage.role_id = r.role_id
INNER JOIN permissions p_manage ON p_manage.permission_id = rp_manage.permission_id
    AND p_manage.name = 'Petty_Cash_Vuca_Sustain.Manage'
LEFT JOIN role_permissions rp_view ON rp_view.role_id = r.role_id
LEFT JOIN permissions p_view ON p_view.permission_id = rp_view.permission_id
    AND p_view.name = 'Petty_Cash_Vuca_Sustain.View'
WHERE p_view.permission_id IS NULL;


-- =============================================================================
-- SUMMARY: Permission Configuration Status
-- Quick overview of permission setup.
-- =============================================================================
SELECT
    'Petty_Cash_Vuca_Sustain.View' AS permission,
    CASE WHEN COUNT(*) > 0 THEN 'REGISTERED' ELSE 'MISSING' END AS status,
    COUNT(*) AS record_count
FROM permissions
WHERE name = 'Petty_Cash_Vuca_Sustain.View'

UNION ALL

SELECT
    'Petty_Cash_Vuca_Sustain.Manage' AS permission,
    CASE WHEN COUNT(*) > 0 THEN 'REGISTERED' ELSE 'MISSING' END AS status,
    COUNT(*) AS record_count
FROM permissions
WHERE name = 'Petty_Cash_Vuca_Sustain.Manage';
