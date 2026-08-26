---
paths:
  - resources/js/pages/Http.vue
  - resources/js/components/HttpCheckLogsTable.vue
---

# Js Components

## Http page lists check logs in nb-table
The /http page renders HttpCheckLogsTable with a logs prop shaped like http_check_logs (id, monitor_id, created_at, status_code, is_successful, response_time_ms, dns/tcp/tls_time_ms, error_message). Use NeoBrutalismCSS nb-table-container / nb-table orange bordered when logs exist, and nb-card empty state when empty — same pattern as monitors Index.
