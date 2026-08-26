---
paths:
  - resources/js/pages/Http.vue
  - resources/js/components/HttpCheckLogsTable.vue
  - resources/js/components/Pagination.vue
---

# Js Components

## Http page lists check logs in nb-table
The /http page renders HttpCheckLogsTable with a logs prop shaped like HttpCheckLogData (id, target, created_at, status_code, response_time_ms, dns/tcp/tls_time_ms, error_message, response_headers) — no monitor_id or is_successful/result. Target is the monitor url or IP in one column. Timing column header is "Total" (response_time_ms). The Error column shows a plain text anchor (`font-medium text-black underline underline-offset-2`, same as monitors Edit) labeled "Response" when error_message or response_headers has content (otherwise —) that opens an nb-dialog blue with Message (error_message) and Headers (response_headers JSON); constrain dialog to viewport height (`max-h-[calc(100vh-2rem)]`) and put `overflow-y-auto` on the body. Backend sends only failed logs via HttpCheckLogController. Use NeoBrutalismCSS nb-table-container / nb-table blue bordered when logs exist, and nb-card empty state when empty.

## Http page uses shared Pagination component
Http.vue renders Pagination with current-page/last-page from pagination meta, pageHref from usePageHref(http), aria-label, and test-id="http-check-logs". Do not inline Previous/Next/page-number markup or pageHref in Http.vue.

## Pagination component numbered Links window
Pagination.vue owns Previous/Next nb-button blue controls plus Inertia Link page numbers. Window is Laravel UrlWindow-style (onEachSide=1): all pages if lastPage < 10; near start 1..6 … last; near end 1 … (last-5)..last; middle 1 … (current±1) … last. Current page is a disabled nb-button with aria-current="page"; gaps use an ellipsis. Hide the nav when lastPage <= 1. Use nb-button blue disabled for unavailable Previous/Next. Accept pageHref, ariaLabel, and testId (data-test prefix) so callers stay route-agnostic.
