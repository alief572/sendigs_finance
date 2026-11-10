# Audit Trail

Read-only viewer for the existing `logs` table, with an autoloaded activity helper.
Requires PHP 7.4+, mysqli and mbstring (the current application runtime).

## Activation

Deploy the module, helper, writer, assets, and existing-file integrations together.
Run these SQL files **on the intended application database** after reviewing the
connection target:

1. `migrations/001_register_audit_trail.sql` registers `Audit_Trail.View` and places
   **Settings → Audit Trail** immediately after Permissions. It discovers menu IDs
   and preserves sibling order. Re-running it does not duplicate entries.
2. `migrations/002_add_logs_indexes.sql` adds time/module/status indexes. DDL commits
   independently; schedule this for a suitable maintenance window on large tables.

Administrator access is automatic. Grant `Audit_Trail.View` through the existing
group/user permission UI for other users; this includes CSV export. The menu and
all four endpoints require this permission. No additional export permission exists.

The implementation does not run either migration on production automatically.
No existing history is imported. Open `/audit_trail` after registration.

## Writing an activity

The helper is autoloaded. Use it after the operation's outcome is known, preferably
after the business transaction commits or rolls back:

```php
$sql = $this->db->last_query(); // Capture before further business queries.
$saved = $this->db->trans_complete();

audit_log('Menyimpan request payment', $saved ? 1 : 0, array(
    'module' => 'request_payment',
    'methode' => 'save',
    'data' => array('no_dokumen' => $no_dokumen),
    'sql' => $sql
));
```

`audit_log($message, $status = null, $context = array())` returns `true` when stored,
or `false` for invalid metadata or writer failure. Viewer activities are ignored
and return `true`. Status accepts `1`, `0`, their string equivalents, or `null`.
Never treat the logging result as the business operation's result.

Optional context keys are `module`, `methode`, `username`, `created_by`, `data`, and
`sql`. Routing and session identity supply the defaults. Use explicit identity
for authentication failures without a session. `data` accepts structured arrays
or valid JSON; arbitrary raw strings and objects are rejected. Text fields must
fit the existing schema; username is limited to 30 bytes, module/method to 50
characters, and TEXT values to 65,535 bytes.

Nested sensitive fields are masked before insertion, as are common credential
assignments in text. SQL retains its structure but removes literal values and
comments; unrecognized syntax is omitted. Do not pass raw passwords, OTPs, session
contents or secrets in free-form messages or payloads.

The mysqli writer opens an independent connection from the active application's
DB connection settings, using prepared inserts. It does not modify the main
connection's transaction, last query or insert ID. A failed writer logs only a
generic application error and returns `false`.

## Existing integrations and limitations

Phase 0 adds an opt-in request recorder; it does not automatically activate any
business controller. See `docs/audits/audit-rollout-checkpoint.md` at the project
root for phase/batch progress and activation instructions.

- `history($description)` still writes `histories`, then mirrors an unknown-status
  activity. `history($description, array('status' => 1, 'data' => ...))` supplies an
  explicit result. Legacy Login/Logout history remains but is not mirrored twice.
- `simpan_aktifitas()` retains its destination, arguments and return value. Its
  activity model also records the reported status, document reference and SQL.
- Auth records denied logins, completed non-2FA login, password acceptance pending
  2FA, OTP failure/success, and logout before session destruction.
- Controllers not using these helpers are not automatically instrumented. This
  release does not provide before/after value comparisons or historical backfill.
- Existing caller statuses are reported as supplied; the helper cannot prove a
  caller's transaction committed. Record outcomes after transaction completion.
- `logs.ip_address` is varchar(15): non-loopback IPv6 is stored as NULL, never
  truncated. `::1` becomes `127.0.0.1`. Supporting IPv6 fully needs a schema change.
- The table uses utf8mb3; supplementary Unicode in text is replaced with U+FFFD.

## Verification

Run each standalone PHP test separately from the project root:

```powershell
$php = 'C:\xampp74\php\php.exe'
foreach ($test in 'Logger','Recorder','Query','Auth','Legacy','Controller','Menu') {
    & $php "application/modules/audit_trail/tests/${test}_test.php"
    if ($LASTEXITCODE -ne 0) { throw "Failed: $test" }
}
```

Query tests use real CodeIgniter query builders with an in-memory SQLite database.
Controller tests also render the PHP view and roundtrip the CSV without a browser.
`Integration_test.php --isolated-local-db` uses ONLY a disposable MariaDB server
on `127.0.0.1:33318` with database `audit_trail_test` and test-only credentials.
It creates/reset fixtures, executes both migrations twice, and verifies the real
mysqli writer's isolation and failure behavior. Do not use a shared database on
that port. It never loads production database configuration.

The repository-wide PHPUnit suite needs PHP 8 because an existing property test
uses union return types; it cannot run under the available PHP 7.4 runtime.
