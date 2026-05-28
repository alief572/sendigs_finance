# Implementation Plan: Laporan Kunjungan Konsultan

## Overview

Implementasi modul HMVC `laporan_kunjungan` secara inkremental, dimulai dari fondasi (database schema, model), kemudian controller methods, views, dan fitur lanjutan (PDF, email). Setiap task membangun di atas task sebelumnya sehingga tidak ada kode yang orphan.

## Tasks

- [x] 1. Setup module structure dan database schema
  - [x] 1.1 Create module directory structure and migration SQL
    - Create directories: `application/modules/laporan_kunjungan/controllers/`, `models/`, `views/`
    - Create SQL migration file with CREATE TABLE statements for `lk_visit_header`, `lk_visit_kegiatan`, `lk_visit_action_plan` on database `db_consultant_new` (DBCNL)
    - Include proper indexes on `id_spk_budgeting`, `visit_id`, `kegiatan_id`
    - Include ENUM fields for status columns
    - _Requirements: 12.1, 12.3_

  - [x] 1.2 Create Model with database connection and base CRUD methods
    - Create `application/modules/laporan_kunjungan/models/Laporan_kunjungan_model.php`
    - Extend `BF_Model`, set `$table_name = 'lk_visit_header'`, `$key = 'id'`
    - Set `$db_con = DBCNL` for database connection to `db_consultant_new`
    - Implement `get_spk_list($konsultan_id)` — SELECT from `kons_tr_spk_budgeting` JOIN `kons_tr_spk_penawaran` JOIN `kons_master_konsultan` WHERE konsultan matches and sts_spk = 1
    - Implement `get_spk_detail($id_spk)` — SELECT detail SPK with all joins
    - Implement `get_kegiatan_spk($id_spk)` — SELECT from `kons_tr_spk_budgeting_aktifitas`
    - Implement `get_mandays_allocated($id_spk)` — SUM mandays_subcont_final from SPK aktifitas
    - _Requirements: 1.2, 1.4, 3.1, 9.1, 12.2_

  - [x] 1.3 Implement Model visit CRUD methods
    - Implement `create_visit($data)` — INSERT into `lk_visit_header` with transaction
    - Implement `update_visit($id, $data)` — UPDATE `lk_visit_header`
    - Implement `save_kegiatan($visit_id, $kegiatan_data)` — batch INSERT into `lk_visit_kegiatan`
    - Implement `save_action_plans($kegiatan_id, $plans)` — batch INSERT into `lk_visit_action_plan`
    - Implement `get_visit($id)` — SELECT visit with JOINs to kegiatan and action plans
    - Implement `get_visits_by_project($id_spk)` — SELECT all final visits for project
    - Implement `get_mandays_used($id_spk)` — SUM mandays_used from all visits for project
    - Implement `get_previous_action_plans($id_spk)` — SELECT action plans from previous visits
    - Implement `update_action_plan_status($id, $status)` — UPDATE action plan status
    - Implement `get_client_email($id_spk)` — SELECT client email from SPK data
    - Use `$this->db->trans_begin()` / `trans_commit()` / `trans_rollback()` for multi-table operations
    - _Requirements: 4.1, 4.4, 5.1, 5.3, 5.4, 8.1, 8.5, 9.2, 10.1, 11.2, 12.2_

- [x] 2. Implement Controller foundation and SPK listing
  - [x] 2.1 Create Controller with permissions and index method
    - Create `application/modules/laporan_kunjungan/controllers/Laporan_kunjungan.php`
    - Extend `Admin_Controller`, define permission properties (`$viewPermission`, `$addPermission`, `$managePermission`, `$deletePermission`)
    - Implement `__construct()` — call `parent::__construct()`, load model
    - Implement `index()` — restrict with viewPermission, set template title, render `index` view
    - Implement `get_data_spk()` — AJAX endpoint for DataTables server-side processing, return JSON with SPK list filtered by logged-in konsultan
    - _Requirements: 1.1, 1.2, 1.3, 12.4, 12.5_

  - [x] 2.2 Create index view with DataTables
    - Create `application/modules/laporan_kunjungan/views/index.php`
    - HTML table with columns: No SPK, Perusahaan, Project, Project Leader, Konsultan, Target Selesai, Action
    - Action buttons: View, Visit, Edit (conditional on draft existence), Report
    - Initialize DataTables with server-side processing, AJAX source pointing to `get_data_spk()`
    - Include empty state handling when no data
    - _Requirements: 1.1, 1.3, 1.5, 1.6, 1.7_

  - [x]* 2.3 Write property test for mandays calculation (Property 1)
    - **Property 1: Mandays calculation consistency**
    - For any valid start_time and finish_time where finish > start, verify `mandays_used = ROUND((finish - start) / 60 / 8, 2)`
    - Use PHP PBT library (Eris) with minimum 100 iterations
    - **Validates: Requirements 7.3, 9.2**

- [x] 3. Implement View and Visit session features
  - [x] 3.1 Implement view() and visit() controller methods
    - Implement `view($id_spk)` — restrict viewPermission, load SPK detail, mandays info, render `view` view
    - Implement `visit($id_spk)` — restrict addPermission, load SPK detail, kegiatan list, previous action plans, render `visit` view
    - Implement `get_kegiatan($id_spk)` — AJAX endpoint returning kegiatan list as JSON
    - _Requirements: 1.4, 2.1, 2.2, 3.1, 5.1, 9.1, 9.2, 9.3_

  - [x] 3.2 Create view.php (SPK detail read-only)
    - Display project info: No SPK, Perusahaan, Project, Project Leader, Konsultan, Target Selesai
    - Display Mandays allocation, Mandays Terpakai, Sisa Mandays with visual indicator for exceeded budget
    - All fields read-only, back button to index
    - _Requirements: 1.4, 9.1, 9.2, 9.3, 9.4, 9.5_

  - [x] 3.3 Create visit.php (visit session form)
    - Project info section (read-only: Perusahaan, Project name)
    - Start/Finish buttons with time display (HH:mm format)
    - Duration display ("X jam Y menit") and Mandays Terpakai calculation
    - Kegiatan section: checkbox list from SPK + custom input field (max 500 chars)
    - Action Plan section per kegiatan: description (max 500), PIC (max 100), Due Date, Status
    - Dynamic add/remove action plan rows (min 1 per kegiatan, max 50)
    - Previous Action Plans section with status toggle (Progress/Done)
    - Potensi Improvement textarea (max 2000 chars) with character counter
    - Hasil Improvement textarea (max 2000 chars) with character counter
    - Save Draft and Save (Final) buttons
    - Client-side validation: whitespace-only rejection, due date >= visit date, finish > start
    - _Requirements: 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 5.1, 5.2, 5.3, 5.5, 6.1, 6.2, 6.3, 6.4, 6.6, 7.4, 7.5_

  - [x]* 3.4 Write property test for whitespace-only kegiatan rejection (Property 3)
    - **Property 3: Whitespace-only kegiatan rejection**
    - For any string composed entirely of whitespace characters, verify the validation rejects it
    - **Validates: Requirements 3.7**

  - [x]* 3.5 Write property test for action plan due date validation (Property 4)
    - **Property 4: Action plan due date validation**
    - For any due_date earlier than visit_date, verify the system rejects the entry
    - **Validates: Requirements 4.7**

- [x] 4. Checkpoint - Ensure module loads and basic flow works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Implement Start/Finish session and Save operations
  - [x] 5.1 Implement start_session() and finish_session() AJAX methods
    - `start_session()` — restrict addPermission, record `date('H:i:s')` as start_time, return JSON with time
    - `finish_session()` — restrict addPermission, record finish_time, calculate duration_minutes and mandays_used, return JSON with time/duration/mandays
    - Validate finish > start, handle errors with JSON error response
    - _Requirements: 2.3, 2.4, 2.5, 7.1, 7.2, 7.3, 7.4, 7.5_

  - [x] 5.2 Implement save_draft() and save_final() AJAX methods
    - `save_draft()` — restrict addPermission, save header + kegiatan + action plans with status='draft', no required field validation, use transaction
    - `save_final()` — restrict addPermission, validate all required fields (kegiatan, action plans, times), save with status='final', use transaction
    - Return JSON success/error response, retain form data on failure
    - Server-side validation: character limits (500/100/2000), due_date >= visit_date, start < finish, at least 1 kegiatan, at least 1 action plan per kegiatan
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 6.5, 6.6_

  - [x]* 5.3 Write property test for final save validation completeness (Property 6)
    - **Property 6: Final save validation completeness**
    - Verify save_final succeeds if and only if all required fields are valid
    - **Validates: Requirements 8.3, 8.4, 8.5**

  - [x]* 5.4 Write property test for character limit enforcement (Property 9)
    - **Property 9: Character limit enforcement**
    - For any string exceeding 2000 characters in improvement fields, verify rejection
    - **Validates: Requirements 6.6**

- [x] 6. Implement Edit functionality
  - [x] 6.1 Implement edit() and update methods in controller
    - `edit($id_visit)` — restrict managePermission, load visit data, verify status='draft', render `edit` view
    - `update_draft()` — restrict managePermission, update draft visit data with transaction
    - `update_final()` — restrict managePermission, validate all fields, update status to 'final' with transaction
    - Reject edit if status is already 'final' (return error/redirect)
    - _Requirements: 1.5, 1.6, 8.2, 8.6_

  - [x] 6.2 Create edit.php view
    - Pre-populate all fields from saved draft data
    - Same form structure as visit.php but with existing data loaded
    - Update Draft and Save Final buttons
    - Disable form if status is 'final' (fallback server-side protection)
    - _Requirements: 6.5, 6.7, 8.2, 8.6_

  - [x]* 6.3 Write property test for draft save round-trip (Property 5)
    - **Property 5: Draft save preserves all data round-trip**
    - Save data as draft, reload, verify all fields match original input
    - **Validates: Requirements 8.1, 6.5, 6.7**

  - [x]* 6.4 Write property test for final status prevents editing (Property 7)
    - **Property 7: Final status prevents editing**
    - For any visit with 'final' status, verify all edit operations are rejected
    - **Validates: Requirements 8.6**

- [x] 7. Checkpoint - Ensure CRUD operations work end-to-end
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Implement Report view and Action Plan status updates
  - [x] 8.1 Implement report() and get_report_data() controller methods
    - `report($id_spk)` — restrict viewPermission, render `report` view with project header info
    - `get_report_data($id_spk)` — AJAX endpoint returning all final visits with kegiatan and action plans, sorted by visit_date DESC
    - `update_action_plan_status()` — AJAX endpoint, restrict managePermission, toggle status Progress/Done, persist change
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 5.3, 5.4_

  - [x] 8.2 Create report.php view (cumulative report)
    - Header: Perusahaan, Project name, Mandays info
    - Table with columns: Date, Konsultan, Kegiatan, Action Plan, PIC, Due Date, Status
    - Multiple kegiatan/action plans per visit displayed as separate rows
    - Status toggle buttons (Progress ↔ Done) for action plan entries
    - Empty state message when no finalized reports exist
    - Download PDF and Send Email buttons
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 11.1, 11.2_

  - [x]* 8.3 Write property test for cumulative mandays calculation (Property 2)
    - **Property 2: Cumulative mandays is sum of individual visits**
    - For any project with multiple visits, verify cumulative equals sum of individual mandays_used
    - **Validates: Requirements 9.2, 9.3**

  - [x]* 8.4 Write property test for action plan status toggle persistence (Property 10)
    - **Property 10: Action plan status toggle persistence**
    - Toggle status from Progress to Done (or vice versa), verify persistence on reload
    - **Validates: Requirements 5.3, 5.4**

- [x] 9. Implement PDF generation and Email sending
  - [x] 9.1 Implement download_pdf() controller method
    - Restrict viewPermission
    - Load all final visits for project with kegiatan and action plans
    - Render `pdf_report` view to HTML string
    - Initialize mPDF (landscape A4), write HTML, output as download
    - Handle mPDF errors gracefully with user-friendly message
    - _Requirements: 11.1, 11.3, 11.6_

  - [x] 9.2 Create pdf_report.php view (HTML template for mPDF)
    - Styled HTML table layout for PDF rendering
    - Header: Perusahaan, Project name, report generation date
    - Table: Date, Konsultan, Kegiatan, Action Plan, PIC, Due Date, Status
    - Proper CSS for print layout (borders, fonts, page margins)
    - _Requirements: 11.1, 11.3_

  - [x] 9.3 Implement send_email() controller method
    - Restrict managePermission
    - Get client email from SPK data via model
    - Generate PDF (same as download_pdf logic)
    - Use CI Email library to send with PDF attachment
    - Handle missing email (return error JSON), handle send failure (return error with retry option)
    - Return success JSON on successful send
    - _Requirements: 11.2, 11.4, 11.5, 11.6_

- [x] 10. Final checkpoint - Ensure all features work together
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- All database operations on existing SPK tables are READ-ONLY (SELECT only)
- Transaction safety (trans_begin/commit/rollback) is required for all multi-table write operations
- The module is fully self-contained in `application/modules/laporan_kunjungan/` with no modifications to existing files
- mPDF library is already available at `application/libraries/Mpdf.php`
- Database connection uses constant `DBCNL` for `db_consultant_new`

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2"] },
    { "id": 2, "tasks": ["1.3", "2.1"] },
    { "id": 3, "tasks": ["2.2", "2.3", "3.1"] },
    { "id": 4, "tasks": ["3.2", "3.3", "3.4", "3.5"] },
    { "id": 5, "tasks": ["5.1", "5.2"] },
    { "id": 6, "tasks": ["5.3", "5.4", "6.1"] },
    { "id": 7, "tasks": ["6.2", "6.3", "6.4"] },
    { "id": 8, "tasks": ["8.1"] },
    { "id": 9, "tasks": ["8.2", "8.3", "8.4"] },
    { "id": 10, "tasks": ["9.1", "9.2"] },
    { "id": 11, "tasks": ["9.3"] }
  ]
}
```
