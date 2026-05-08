# Requirements Document

## Introduction

Modul Laporan Kunjungan Konsultan adalah modul baru dalam aplikasi Sendigs Finance yang memungkinkan konsultan membuat laporan saat melakukan kunjungan (visit) ke client atau melakukan konsultasi via Zoom. Modul ini mengambil data dari daftar project (SPK) yang sudah ada dan mencatat kegiatan, action plan, serta potensi improvement selama sesi konsultasi berlangsung. Modul ini mengikuti pola HMVC yang sudah ada di aplikasi.

## Glossary

- **Modul_Laporan_Kunjungan**: Modul HMVC baru yang mengelola pencatatan laporan kunjungan konsultan
- **SPK**: Surat Perintah Kerja, dokumen project yang sudah ada dalam database sistem
- **Konsultan**: Pengguna sistem yang berperan sebagai konsultan dan melakukan kunjungan ke client
- **Kegiatan**: Aktivitas konsultasi yang diambil dari daftar kegiatan SPK atau diinput secara free text
- **Action_Plan**: Rencana tindak lanjut yang dihasilkan dari setiap kegiatan konsultasi
- **PIC**: Person In Charge, orang yang bertanggung jawab atas pelaksanaan action plan
- **Mandays**: Satuan hari kerja yang dialokasikan untuk project, diambil dari database SPK
- **Mandays_Terpakai**: Jumlah mandays yang sudah digunakan, dihitung otomatis berdasarkan durasi kunjungan
- **Potensi_Improvement**: Ide perbaikan yang diidentifikasi konsultan selama kunjungan (tidak wajib diisi)
- **Hasil_Improvement**: Hasil nyata dari implementasi potensi improvement (tidak wajib diisi)
- **Visit_Session**: Satu sesi kunjungan konsultan yang dimulai dari klik Start hingga klik Finish
- **Draft**: Status laporan yang belum final dan masih bisa diedit
- **Report**: Dokumen laporan kunjungan yang bisa di-download atau dikirim via email ke client

## Requirements

### Requirement 1: Menampilkan Daftar Project SPK

**User Story:** As a Konsultan, I want to see a list of SPK projects assigned to me, so that I can select a project to create a visit report.

#### Acceptance Criteria

1. WHEN the Konsultan opens the Modul_Laporan_Kunjungan, THE Modul_Laporan_Kunjungan SHALL display a header table containing columns: No SPK, Perusahaan, Project, Project Leader, Konsultan, Target Selesai, and Action buttons (View, Edit, Report).
2. WHEN the Konsultan opens the Modul_Laporan_Kunjungan, THE Modul_Laporan_Kunjungan SHALL retrieve and display only the SPK projects where the logged-in Konsultan is assigned as the Konsultan, filtered to active SPK records (sts_spk = 1) from the existing SPK database.
3. IF no SPK projects are assigned to the logged-in Konsultan, THEN THE Modul_Laporan_Kunjungan SHALL display the table header with an empty state message indicating no projects are available.
4. WHEN the Konsultan clicks the "View" action button, THE Modul_Laporan_Kunjungan SHALL display the detail of the selected SPK project in read-only mode, showing: No SPK, Perusahaan, Project name, Project Leader, Konsultan, Target Selesai, and Mandays allocation.
5. WHEN the Konsultan clicks the "Edit" action button on a project that has an existing visit report with "Draft" status, THE Modul_Laporan_Kunjungan SHALL open the visit report form for editing that draft report.
6. IF the Konsultan clicks the "Edit" action button on a project that has no existing draft visit report, THEN THE Modul_Laporan_Kunjungan SHALL disable or hide the "Edit" button for that project row.
7. WHEN the Konsultan clicks the "Report" action button, THE Modul_Laporan_Kunjungan SHALL display the cumulative visit report for the selected project.

### Requirement 2: Memulai Sesi Kunjungan

**User Story:** As a Konsultan, I want to start a visit session with automatic time recording, so that the consultation duration is tracked accurately.

#### Acceptance Criteria

1. WHEN the Konsultan clicks the "Visit" button on a project, THE Modul_Laporan_Kunjungan SHALL navigate to the visit activity form for the selected project.
2. WHEN the visit activity form is loaded, THE Modul_Laporan_Kunjungan SHALL pre-populate the project information (Perusahaan, Project name) as read-only fields from the selected SPK data.
3. WHEN the Konsultan clicks the "Start" button, THE Modul_Laporan_Kunjungan SHALL record the server's current time as the start time and display it on the form in "HH:mm" format (24-hour).
4. WHILE a Visit_Session is active (started but not finished), THE Modul_Laporan_Kunjungan SHALL display the recorded start time on the form and disable the "Start" button to prevent duplicate session starts.
5. IF the system fails to record the start time due to a server or network error, THEN THE Modul_Laporan_Kunjungan SHALL display an error message indicating the session could not be started and keep the "Start" button enabled for retry.

### Requirement 3: Memilih dan Mencatat Kegiatan

**User Story:** As a Konsultan, I want to select activities from the SPK activity list or input custom activities, so that I can document all consultation activities performed.

#### Acceptance Criteria

1. THE Modul_Laporan_Kunjungan SHALL display a selectable list of Kegiatan retrieved from the SPK activity list for the selected project.
2. THE Modul_Laporan_Kunjungan SHALL allow the Konsultan to select one or more Kegiatan from the list, with no upper limit on the number of selections.
3. WHEN the Konsultan activates the custom activity input, THE Modul_Laporan_Kunjungan SHALL provide a free text input field for entering custom Kegiatan with a maximum length of 500 characters.
4. WHEN the Konsultan selects or adds a Kegiatan, THE Modul_Laporan_Kunjungan SHALL add the Kegiatan to the visit report form.
5. WHEN the Konsultan deselects a Kegiatan from the list or removes a custom Kegiatan, THE Modul_Laporan_Kunjungan SHALL remove that Kegiatan from the visit report form.
6. IF the SPK activity list for the selected project contains no Kegiatan, THEN THE Modul_Laporan_Kunjungan SHALL display a message indicating no activities are available and SHALL allow the Konsultan to enter custom Kegiatan only.
7. IF the Konsultan submits a custom Kegiatan with an empty or whitespace-only value, THEN THE Modul_Laporan_Kunjungan SHALL reject the entry and display a validation error indicating that the activity description is required.

### Requirement 4: Mencatat Action Plan

**User Story:** As a Konsultan, I want to create multiple action plans for each activity, so that I can document all follow-up tasks resulting from the consultation.

#### Acceptance Criteria

1. WHEN a Kegiatan is selected, THE Modul_Laporan_Kunjungan SHALL display input fields for Action_Plan description (maximum 500 characters), PIC (maximum 100 characters), and Due Date.
2. THE Modul_Laporan_Kunjungan SHALL allow the Konsultan to add between 1 and 50 Action_Plan entries for a single Kegiatan.
3. THE Modul_Laporan_Kunjungan SHALL require the Konsultan to fill in the Action_Plan description, PIC, and Due Date for each action plan entry before saving.
4. WHEN a new Action_Plan entry is added, THE Modul_Laporan_Kunjungan SHALL set the status of the entry to "Progress".
5. THE Modul_Laporan_Kunjungan SHALL allow the Konsultan to change the Action_Plan status to "Done" or "Progress".
6. THE Modul_Laporan_Kunjungan SHALL allow the Konsultan to remove an Action_Plan entry from a Kegiatan, provided at least one Action_Plan entry remains for that Kegiatan.
7. IF the Konsultan selects a Due Date earlier than the current visit date, THEN THE Modul_Laporan_Kunjungan SHALL display a validation error message indicating that the Due Date must be equal to or later than the current visit date.

### Requirement 5: Menampilkan Progress Action Plan Sebelumnya

**User Story:** As a Konsultan, I want to see the progress of action plans from previous visits, so that I can follow up on outstanding items during the current visit.

#### Acceptance Criteria

1. WHEN a project is selected for a new visit, THE Modul_Laporan_Kunjungan SHALL automatically display all Action_Plan entries from previous visits for the same project, showing for each entry: Kegiatan, Action_Plan description, PIC, Due Date, and Status.
2. THE Modul_Laporan_Kunjungan SHALL display the status of each previous Action_Plan as either "Done" or "Progress", with a visual distinction between the two statuses.
3. THE Modul_Laporan_Kunjungan SHALL allow the Konsultan to update the status of previous Action_Plan entries from "Progress" to "Done" or from "Done" back to "Progress" during the current visit.
4. WHEN the Konsultan updates the status of a previous Action_Plan entry, THE Modul_Laporan_Kunjungan SHALL persist the updated status so that subsequent visits reflect the change.
5. IF no previous Action_Plan entries exist for the selected project, THEN THE Modul_Laporan_Kunjungan SHALL display an informational message indicating that there are no previous action plans to follow up on.

### Requirement 6: Mencatat Potensi dan Hasil Improvement

**User Story:** As a Konsultan, I want to optionally record potential improvements and their results, so that improvement ideas are documented for future reference.

#### Acceptance Criteria

1. THE Modul_Laporan_Kunjungan SHALL provide a free-text input field for Potensi_Improvement with a maximum length of 2000 characters.
2. THE Modul_Laporan_Kunjungan SHALL not require the Konsultan to fill in the Potensi_Improvement field.
3. THE Modul_Laporan_Kunjungan SHALL provide a free-text input field for Hasil_Improvement with a maximum length of 2000 characters.
4. THE Modul_Laporan_Kunjungan SHALL not require the Konsultan to fill in the Hasil_Improvement field.
5. WHEN the Konsultan submits the laporan kunjungan, THE Modul_Laporan_Kunjungan SHALL persist the Potensi_Improvement and Hasil_Improvement values (including empty values) and display them when the laporan is viewed subsequently.
6. IF the Konsultan enters text exceeding 2000 characters in Potensi_Improvement or Hasil_Improvement, THEN THE Modul_Laporan_Kunjungan SHALL prevent submission and display an error message indicating the maximum character limit has been exceeded.
7. WHEN the Konsultan views a previously saved laporan kunjungan, THE Modul_Laporan_Kunjungan SHALL display the saved Potensi_Improvement and Hasil_Improvement content in their respective fields.

### Requirement 7: Mengakhiri Sesi Kunjungan

**User Story:** As a Konsultan, I want to end the visit session with automatic time recording, so that the total consultation duration is calculated accurately.

#### Acceptance Criteria

1. WHEN the Konsultan clicks the "Finish" button, THE Modul_Laporan_Kunjungan SHALL record the server's current time as the finish time and display it on the form in "HH:mm" format (24-hour).
2. WHEN the finish time is recorded, THE Modul_Laporan_Kunjungan SHALL calculate and display the total duration of the Visit_Session in hours and minutes format (e.g., "6 jam 10 menit").
3. WHEN the finish time is recorded, THE Modul_Laporan_Kunjungan SHALL calculate the Mandays_Terpakai using the formula: duration in hours divided by 8, rounded to 2 decimal places (1 manday = 8 hours).
4. AFTER the Finish button is clicked, THE Modul_Laporan_Kunjungan SHALL disable the "Finish" button to prevent duplicate session endings.
5. IF no active Visit_Session exists (Start has not been clicked), THEN THE Modul_Laporan_Kunjungan SHALL disable the "Finish" button.

### Requirement 8: Menyimpan Laporan Kunjungan

**User Story:** As a Konsultan, I want to save the visit report as a draft or as a final report, so that I can complete the report at my convenience.

#### Acceptance Criteria

1. WHEN the Konsultan clicks "Save Draft", THE Modul_Laporan_Kunjungan SHALL save the current visit report with a "Draft" status regardless of whether required fields are empty, and SHALL display a success confirmation message within 2 seconds.
2. WHILE a report has "Draft" status, THE Modul_Laporan_Kunjungan SHALL allow the Konsultan to edit and update the report.
3. WHEN the Konsultan clicks "Save", THE Modul_Laporan_Kunjungan SHALL validate that all required fields (Kegiatan, Action_Plan, PIC, Due Date, Start time, Finish time) contain a non-empty value, that Due Date is a valid date not in the past, and that Start time is earlier than Finish time.
4. IF a required field is empty or fails validation when the Konsultan clicks "Save", THEN THE Modul_Laporan_Kunjungan SHALL display a validation error message indicating each field that is missing or invalid, and SHALL NOT change the report status.
5. WHEN all required fields are valid and the Konsultan clicks "Save", THE Modul_Laporan_Kunjungan SHALL save the report with a "Final" status and display a success confirmation message within 2 seconds.
6. WHILE a report has "Final" status, THE Modul_Laporan_Kunjungan SHALL prevent the Konsultan from editing the report content.
7. IF the save operation fails due to a system error, THEN THE Modul_Laporan_Kunjungan SHALL display an error message indicating the failure, SHALL retain all entered data in the form, and SHALL keep the report in its previous status.

### Requirement 9: Menampilkan Informasi Mandays

**User Story:** As a Konsultan, I want to see the mandays allocation and usage for a project, so that I can track the remaining consultation budget.

#### Acceptance Criteria

1. WHEN a project is selected, THE Modul_Laporan_Kunjungan SHALL display the total Mandays allocated for the project, retrieved from the SPK database, as a numeric value with up to 2 decimal places.
2. WHEN a project is selected, THE Modul_Laporan_Kunjungan SHALL calculate and display the cumulative Mandays_Terpakai as the sum of mandays values from all visit sessions recorded for the selected project, displayed as a numeric value with up to 2 decimal places.
3. WHEN a project is selected, THE Modul_Laporan_Kunjungan SHALL display the Sisa Mandays calculated as total Mandays allocated minus cumulative Mandays_Terpakai, including when the result is zero or negative.
4. IF the SPK data for the selected project is unavailable or returns no mandays allocation, THEN THE Modul_Laporan_Kunjungan SHALL display a message indicating that mandays allocation data is not found and SHALL display Mandays_Terpakai and Sisa Mandays as 0.
5. IF the cumulative Mandays_Terpakai exceeds the total Mandays allocated, THEN THE Modul_Laporan_Kunjungan SHALL display the negative Sisa Mandays value and SHALL visually indicate that the mandays budget has been exceeded.

### Requirement 10: Menampilkan Kumulatif Laporan Kunjungan

**User Story:** As a Konsultan, I want to see the cumulative visit report history for a project, so that I can review all past consultation activities.

#### Acceptance Criteria

1. WHEN the Konsultan views a project report, THE Modul_Laporan_Kunjungan SHALL display the cumulative list of all visit reports with "Final" status for the selected project, sorted by visit date in descending order (most recent first).
2. THE Modul_Laporan_Kunjungan SHALL display each visit entry with: Date, Konsultan name, Kegiatan, Action_Plan, PIC, Due Date, and Status.
3. THE Modul_Laporan_Kunjungan SHALL display the report header containing Perusahaan and Project name.
4. IF a single visit contains multiple Kegiatan or multiple Action_Plan entries, THE Modul_Laporan_Kunjungan SHALL display each entry as a separate row in the report table.
5. IF no finalized visit reports exist for the selected project, THEN THE Modul_Laporan_Kunjungan SHALL display an informational message indicating that no visit reports are available yet.

### Requirement 11: Download dan Email Report

**User Story:** As a Konsultan, I want to download or email the visit report to the client, so that the client receives documentation of the consultation.

#### Acceptance Criteria

1. WHEN the Konsultan clicks the "Download" button, THE Modul_Laporan_Kunjungan SHALL generate a downloadable PDF document containing the cumulative visit report for the selected project.
2. WHEN the Konsultan clicks the "Email" button, THE Modul_Laporan_Kunjungan SHALL send the visit report PDF to the client email address associated with the project in the SPK database.
3. THE Modul_Laporan_Kunjungan SHALL include in the report: Perusahaan, Project name, and a table with columns Date, Konsultan, Kegiatan, Action Plan, PIC, Due Date, and Status.
4. IF the email sending fails, THEN THE Modul_Laporan_Kunjungan SHALL display an error message indicating the email could not be sent and SHALL allow the Konsultan to retry.
5. IF no client email address is associated with the project, THEN THE Modul_Laporan_Kunjungan SHALL disable the "Email" button and display a message indicating that no client email is configured for this project.
6. WHEN the PDF is generated or email is sent successfully, THE Modul_Laporan_Kunjungan SHALL display a success confirmation message.

### Requirement 12: Isolasi Modul

**User Story:** As a developer, I want the new module to be completely isolated from existing modules, so that no bugs or errors are introduced to other parts of the application.

#### Acceptance Criteria

1. THE Modul_Laporan_Kunjungan SHALL be implemented as a self-contained HMVC module within the application/modules directory, containing its own controllers, models, and views subdirectories with no file additions or modifications to other module directories.
2. THE Modul_Laporan_Kunjungan SHALL use only SELECT queries when accessing existing SPK database tables, with no INSERT, UPDATE, or DELETE operations performed on any pre-existing database table.
3. THE Modul_Laporan_Kunjungan SHALL store all new data in dedicated database tables that use a distinct prefix or naming convention separate from existing tables.
4. THE Modul_Laporan_Kunjungan SHALL define exactly four permission entries following the pattern: Laporan_Kunjungan.View, Laporan_Kunjungan.Add, Laporan_Kunjungan.Manage, and Laporan_Kunjungan.Delete.
5. THE Modul_Laporan_Kunjungan SHALL extend Admin_Controller, call parent::__construct(), use the Template library for rendering views, and enforce permission checks via $this->auth->restrict() on each controller method, consistent with the pattern used by existing modules.
6. THE Modul_Laporan_Kunjungan SHALL NOT modify, overwrite, or extend any existing controller, model, view, helper, library, or configuration file outside its own module directory.
