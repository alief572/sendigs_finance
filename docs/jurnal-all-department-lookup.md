# Department names in Jurnal All and Report Jurnal All

Both routes share Jurnal_all_model. Report_jurnal_all inherits Jurnal_all and selects posted journals; listing, preview and Excel use the same department enrichment.

## Sources

- Non-consulting invoices: invoice offer reference -> consultant non-consulting offer department -> departments on the separate HRIS connection. Consulting invoice resolution is retained.
- Refill petty cash: report -> expense entries -> exact requester identity -> user department -> HRIS departments.
- Petty cash debt payments: debt document -> report -> expense requester. Group headers, company sections and export use the resolved departments without changing accounting fingerprints or posting guards.
- Periodic payments: submission detail budget -> budget department -> HRIS departments. Headers collect distinct departments; matching expense lines follow their budget department and credit lines use the combined names.
- Expense reports and cash returns: department codes resolve on HRIS. Numeric legacy identifiers first use the local department master; if absent, exact requester identities supply a fallback. Ambiguous or unresolved legacy identifiers display a dash. Existing meaningful journal names are retained.

Requester fallback reflects the current user department and is not proof of a historical legacy-code mapping. Missing budget relations do not invent a budget department. Cash DPM, ordinary kasbon, direct payment and recording petty cash paths retain their existing behavior unless covered by the sources above.

## Runtime configuration

Configure the existing `hris` database group to reach the authoritative `hr_sentral` server separately from the finance/consultant server. A same-named schema on the finance server is not an equivalent source. The required HRIS hostname is `206.84.97.106`.

Credentials belong in environment-specific, Git-ignored database configuration. They are deliberately absent from this change. No schema migration, data repair or posting is required. Deploying code alone does not update the ignored server configuration.

## Validation

Targeted PHP regression suites passed for department sources, pending and posted detail, export, duplicate or ambiguous identities, missing relations and HRIS lookup failures. Existing query, controller, dispatcher, payment mapping/history, native posting and audit suites passed. PHP syntax and scoped whitespace checks passed.

The dedicated disposable-MySQL debt posting suite could not run because its local test database refused the connection. SQLite debt tests verify unchanged financial fingerprints and posting guards; the unavailable MySQL suite remains a validation limitation. No browser testing was performed.

Financial inspection scripts and transaction evidence remain local and are not included in this publication.
