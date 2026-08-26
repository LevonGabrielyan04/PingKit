---
paths:
  - resources/js/pages/Http.vue
  - resources/js/components/HttpCheckLogsTable.vue
---

# Js Components

## Http page lists check logs in nb-table
The /http page renders HttpCheckLogsTable with a logs prop shaped like HttpCheckLogData (id, target, created_at, status_code, response_time_ms, dns/tcp/tls_time_ms, error_message, response_headers) — no monitor_id or is_successful/result. Target is the monitor url or IP in one column. Timing column header is "Total" (response_time_ms). The Error column shows a plain text anchor (`font-medium text-black underline underline-offset-2`, same as monitors Edit) labeled "Response" when error_message or response_headers has content (otherwise —) that opens an nb-dialog blue with Message (error_message) and Headers (response_headers JSON); constrain dialog to viewport height (`max-h-[calc(100vh-2rem)]`) and put `overflow-y-auto` on the body. Backend sends only failed logs via HttpCheckLogController. Use NeoBrutalismCSS nb-table-container / nb-table blue bordered when logs exist, and nb-card empty state when empty.

## Http page shows Previous/Next when multi-page
When pagination.last_page > 1, Http.vue renders Previous/Next nb-button blue controls (Wayfinder http({ query: { page } }), page 1 via http()) plus "Page X of Y". Hide the nav when only one page. Use nb-button blue disabled for unavailable directions.
