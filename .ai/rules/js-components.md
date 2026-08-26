---
paths:
  - resources/js/pages/Http.vue
  - resources/js/components/HttpCheckLogsTable.vue
---

# Js Components

## Http page lists check logs in nb-table
The /http page renders HttpCheckLogsTable with a logs prop shaped like HttpCheckLogData (id, target, created_at, status_code, response_time_ms, dns/tcp/tls_time_ms, error_message) — no monitor_id or is_successful/result. Target is the monitor url or IP in one column. Backend sends only failed logs (is_successful=false) via HttpCheckLogController. Use NeoBrutalismCSS nb-table-container / nb-table orange bordered when logs exist, and nb-card empty state when empty — same pattern as monitors Index.
