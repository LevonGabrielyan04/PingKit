---
paths:
  - 'app/Data/**/*.php'
---

# Data

## HttpCheckResult is constructed via factories
HttpCheckResult has a private constructor. Create instances only with fromResponse(monitorId, response, stats, request, errorMessage?) or fromFailure(...). Timing/header normalization lives on the DTO.

## HttpCheckLogData is a frontend DTO
HttpCheckLogData is a readonly Arrayable/JsonSerializable DTO with a private constructor. Build only via fromModel(HttpCheckLog). toArray() uses snake_case keys matching the Http Vue table props (id, target, created_at, status_code, response_time_ms, dns/tcp/tls_time_ms, error_message). `target` is monitor url_address ?? ip_address. Do not include monitor_id or is_successful.
